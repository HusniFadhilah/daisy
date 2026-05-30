<?php

namespace App\Services\Banpt;

use App\Models\BanptAccreditationChange;
use App\Models\BanptSyncRun;
use App\Models\HasilAkreditasi;
use App\Models\PengajuanAkreditasi;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BanptAccreditationSyncService
{
    public function __construct(private readonly BanptClient $client) {}

    // ──────────────────────────────────────────────────────────────
    // Sync utama
    // ──────────────────────────────────────────────────────────────

    public function syncAll(): BanptSyncRun
    {
        $run = BanptSyncRun::create([
            'started_at' => now(),
            'status'     => BanptSyncRun::STATUS_RUNNING,
        ]);

        $programs = StudyProgram::query()
            ->whereNotNull('rumpun')
            ->where('is_active', true)
            ->where('is_example', false)
            ->with(['university', 'degreeLevel'])
            ->get();

        $errors = [];

        foreach ($programs as $program) {
            try {
                $this->syncStudyProgram($program, $run);
            } catch (\Throwable $e) {
                Log::error("BanptSync: error prodi #{$program->id} — {$e->getMessage()}");
                $errors[$program->id] = $e->getMessage();
                $run->increment('error_count');
            }

            $run->increment('checked_count');
        }

        $run->update([
            'finished_at' => now(),
            'status'      => empty($errors) ? BanptSyncRun::STATUS_SUCCESS : BanptSyncRun::STATUS_FAILED,
            'message'     => empty($errors) ? 'Selesai.' : 'Selesai dengan beberapa error.',
            'metadata'    => ['errors' => $errors],
        ]);

        return $run->refresh();
    }

    public function syncStudyProgram(StudyProgram $studyProgram, BanptSyncRun $run): ?BanptAccreditationChange
    {
        $banptData = $this->client->findActiveAccreditation($studyProgram);

        // Update last_banpt_checked_at meski tidak ada perubahan
        $studyProgram->update([
            'last_banpt_checked_at' => now(),
            'last_banpt_payload'    => $banptData,
        ]);

        if (!$banptData) {
            return null;
        }

        $newPeringkat  = $banptData['peringkat'] ?: null;
        $newTanggal    = $banptData['tanggal_kedaluwarsa'];
        $newStatus     = $this->resolveStatusKedaluwarsa($newTanggal);
        $newUnivName   = $banptData['nama_pt'] ?: null;
        $newPsName     = $banptData['nama_ps'] ?: null;
        $newJenjang    = $banptData['jenjang'] ?: null;

        $oldPeringkat  = $studyProgram->peringkat_akreditasi;
        $oldTanggal    = $studyProgram->tanggal_kedaluwarsa
            ? Carbon::parse($studyProgram->tanggal_kedaluwarsa)->format('Y-m-d')
            : null;
        $oldStatus     = $studyProgram->status_kedaluwarsa;

        // Bandingkan: hanya fields yang relevan
        if (!$this->hasRelevantChange($oldPeringkat, $newPeringkat, $oldTanggal, $newTanggal)) {
            return null;
        }

        $oldData = [
            'peringkat'  => $oldPeringkat,
            'kedaluwarsa' => $oldTanggal,
            'status'     => $oldStatus,
        ];
        $newData = [
            'peringkat'  => $newPeringkat,
            'kedaluwarsa' => $newTanggal,
            'status'     => $newStatus,
        ];

        $hash = $this->buildChangeHash($oldData, $newData);

        // Cegah duplicate pending
        $exists = BanptAccreditationChange::where('id_study_program', $studyProgram->id)
            ->where('change_hash', $hash)
            ->where('status', BanptAccreditationChange::STATUS_PENDING)
            ->exists();

        if ($exists) {
            return null;
        }

        $run->increment('changed_count');

        return BanptAccreditationChange::create([
            'id_sync_run'            => $run->id,
            'id_study_program'       => $studyProgram->id,
            'old_university_name'    => $studyProgram->university?->name,
            'old_program_studi'      => $studyProgram->name,
            'old_jenjang'            => $studyProgram->degreeLevel?->alias,
            'old_peringkat_akreditasi' => $oldPeringkat,
            'old_tanggal_kedaluwarsa'  => $oldTanggal,
            'old_status_kedaluwarsa'   => $oldStatus,
            'new_university_name'    => $newUnivName,
            'new_program_studi'      => $newPsName,
            'new_jenjang'            => $newJenjang,
            'new_peringkat_akreditasi' => $newPeringkat,
            'new_tanggal_kedaluwarsa'  => $newTanggal,
            'new_status_kedaluwarsa'   => $newStatus,
            'banpt_pt_label'         => $banptData['pt_label'],
            'banpt_ps_label'         => $banptData['ps_label'],
            'banpt_payload'          => $banptData,
            'change_hash'            => $hash,
            'status'                 => BanptAccreditationChange::STATUS_PENDING,
            'detected_at'            => now(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Apply / Ignore / Conflict
    // ──────────────────────────────────────────────────────────────

    public function applyChange(BanptAccreditationChange $change, User $user): void
    {
        DB::transaction(function () use ($change, $user) {
            $studyProgram = StudyProgram::lockForUpdate()->findOrFail($change->id_study_program);

            // Validasi: masih pending?
            $change->refresh();
            if (!$change->isPending()) {
                throw new \RuntimeException('Perubahan ini sudah diproses sebelumnya.');
            }

            // Cek blocking DAISY
            $blockReason = $this->hasBlockingDaisyProcess($studyProgram);
            if ($blockReason) {
                $change->update([
                    'status'          => BanptAccreditationChange::STATUS_CONFLICT,
                    'conflict_reason' => $blockReason,
                ]);
                return;
            }

            // Validasi data lokal belum berubah sejak change dibuat
            $currentHash = $this->buildChangeHash(
                [
                    'peringkat'   => $studyProgram->peringkat_akreditasi,
                    'kedaluwarsa' => $studyProgram->tanggal_kedaluwarsa
                        ? Carbon::parse($studyProgram->tanggal_kedaluwarsa)->format('Y-m-d')
                        : null,
                    'status'      => $studyProgram->status_kedaluwarsa,
                ],
                [
                    'peringkat'   => $change->new_peringkat_akreditasi,
                    'kedaluwarsa' => $change->new_tanggal_kedaluwarsa
                        ? $change->new_tanggal_kedaluwarsa->format('Y-m-d')
                        : null,
                    'status'      => $change->new_status_kedaluwarsa,
                ]
            );

            if ($currentHash !== $change->change_hash) {
                // Data lokal sudah berubah, jadi conflict
                $change->update([
                    'status'          => BanptAccreditationChange::STATUS_CONFLICT,
                    'conflict_reason' => 'Data lokal sudah berubah sejak perubahan ini terdeteksi.',
                ]);
                return;
            }

            // Terapkan ke study_programs
            $studyProgram->update([
                'peringkat_akreditasi'  => $change->new_peringkat_akreditasi,
                'tanggal_kedaluwarsa'   => $change->new_tanggal_kedaluwarsa,
                'status_kedaluwarsa'    => $change->new_status_kedaluwarsa,
                'last_banpt_checked_at' => now(),
                'last_banpt_payload'    => $change->banpt_payload,
                'akreditasi_source'     => 'banpt',
            ]);

            $change->update([
                'status'     => BanptAccreditationChange::STATUS_APPLIED,
                'applied_at' => now(),
                'applied_by' => $user->id,
            ]);
        });
    }

    public function ignoreChange(BanptAccreditationChange $change, User $user): void
    {
        if (!$change->isPending()) {
            throw new \RuntimeException('Perubahan ini sudah diproses sebelumnya.');
        }

        $change->update([
            'status'     => BanptAccreditationChange::STATUS_IGNORED,
            'ignored_at' => now(),
            'ignored_by' => $user->id,
        ]);
    }

    public function markConflict(BanptAccreditationChange $change, User $user, ?string $reason = null): void
    {
        if (!$change->isPending()) {
            throw new \RuntimeException('Perubahan ini sudah diproses sebelumnya.');
        }

        $change->update([
            'status'          => BanptAccreditationChange::STATUS_CONFLICT,
            'conflict_reason' => $reason ?? 'Ditandai konflik oleh superadmin.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    public function buildChangeHash(array $oldData, array $newData): string
    {
        return md5(json_encode([
            'old_peringkat'   => mb_strtolower(trim($oldData['peringkat'] ?? '')),
            'new_peringkat'   => mb_strtolower(trim($newData['peringkat'] ?? '')),
            'old_kedaluwarsa' => $oldData['kedaluwarsa'] ?? null,
            'new_kedaluwarsa' => $newData['kedaluwarsa'] ?? null,
            'old_status'      => mb_strtolower(trim($oldData['status'] ?? '')),
            'new_status'      => mb_strtolower(trim($newData['status'] ?? '')),
        ]));
    }

    /**
     * Return null jika aman untuk apply, atau string alasan jika ada blocking process.
     */
    public function hasBlockingDaisyProcess(StudyProgram $studyProgram): ?string
    {
        // akreditasi_source = lamdepilar → tidak boleh di-overwrite
        if ($studyProgram->akreditasi_source === 'lamdepilar') {
            return 'Prodi sudah dikelola oleh DAISY/LAMDEPILAR (akreditasi_source = lamdepilar).';
        }

        // Pengajuan aktif (bukan selesai atau ditolak)
        $terminalStatuses = [
            PengajuanAkreditasi::STATUS_SELESAI,
            PengajuanAkreditasi::STATUS_DITOLAK,
        ];

        $hasActivePengajuan = PengajuanAkreditasi::where('id_program_studi', $studyProgram->id)
            ->whereNotIn('status', $terminalStatuses)
            ->exists();

        if ($hasActivePengajuan) {
            return 'Prodi memiliki pengajuan akreditasi yang sedang aktif di DAISY.';
        }

        // Hasil final DAISY
        $hasFinalHasil = HasilAkreditasi::where('id_study_program', $studyProgram->id)
            ->whereIn('status', ['final_penetapan', 'published'])
            ->exists();

        if ($hasFinalHasil) {
            return 'Prodi sudah memiliki hasil penetapan final dari DAISY/LAMDEPILAR.';
        }

        return null;
    }

    public function resolveStatusKedaluwarsa(?string $tanggal): string
    {
        if (!$tanggal) {
            return 'Belum Terakreditasi';
        }
        $date = Carbon::parse($tanggal);
        return $date->isPast() ? 'Kedaluwarsa' : 'Aktif';
    }

    private function hasRelevantChange(
        ?string $oldPeringkat,
        ?string $newPeringkat,
        ?string $oldTanggal,
        ?string $newTanggal
    ): bool {
        $peringkatChanged = mb_strtolower(trim($oldPeringkat ?? '')) !== mb_strtolower(trim($newPeringkat ?? ''));
        $tanggalChanged   = $oldTanggal !== $newTanggal;

        return $peringkatChanged || $tanggalChanged;
    }
}
