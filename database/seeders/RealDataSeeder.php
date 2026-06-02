<?php

namespace Database\Seeders;

use App\Models\PengajuanAkreditasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RealDataSeeder extends Seeder
{
    private const YEAR = 2026;

    public function run(): void
    {
        $actorId = $this->userIdByRole('sekretariat');
        $validatorId = $this->userIdByRole('validator');
        $studyProgramIds = DB::table('study_programs')
            ->where('is_active', true)
            ->whereNotIn('id', function ($query) {
                $query->select('id_program_studi')
                    ->from('pengajuan_akreditasi')
                    ->where('is_example', false);
            })
            ->orderBy('id')
            ->limit(4)
            ->pluck('id');

        if (! $actorId || ! $validatorId || $studyProgramIds->count() < 4) {
            $this->command?->warn('RealDataSeeder dilewati: data user/prodi master belum lengkap.');
            return;
        }

        $scenarios = [
            [
                'code' => 'BORANG-VALIDATED',
                'study_program_id' => $studyProgramIds[0],
                'jenis_akreditasi' => PengajuanAkreditasi::AKREDITASI_PERPANJANGAN,
                'status' => PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                'peringkat_awal' => 'B',
                'catatan' => 'Contoh prodi yang dokumen borangnya sudah divalidasi dan siap lanjut AK.',
            ],
            [
                'code' => 'AK-IN-PROGRESS',
                'study_program_id' => $studyProgramIds[1],
                'jenis_akreditasi' => PengajuanAkreditasi::AKREDITASI_TERAKREDITASI,
                'status' => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                'peringkat_awal' => 'Baik',
                'catatan' => 'Contoh prodi dengan penilaian AK sedang berjalan.',
            ],
            [
                'code' => 'AL-IN-PROGRESS',
                'study_program_id' => $studyProgramIds[2],
                'jenis_akreditasi' => PengajuanAkreditasi::AKREDITASI_MENUJU_UNGGUL,
                'status' => PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                'peringkat_awal' => 'Baik Sekali',
                'catatan' => 'Contoh prodi yang sudah melewati AK dan sedang asesmen lapangan.',
            ],
            [
                'code' => 'HASIL-DIUMUMKAN',
                'study_program_id' => $studyProgramIds[3],
                'jenis_akreditasi' => PengajuanAkreditasi::AKREDITASI_BARU,
                'status' => PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                'peringkat_awal' => null,
                'peringkat_hasil' => 'Baik Sekali',
                'skor_hasil' => 332.50,
                'catatan' => 'Contoh siklus akreditasi yang sudah sampai pengumuman hasil.',
            ],
        ];

        DB::transaction(function () use ($actorId, $validatorId, $scenarios) {
            foreach ($scenarios as $index => $scenario) {
                $this->seedScenario($scenario, $index, $actorId, $validatorId);
            }
        });

        $this->command?->info('RealDataSeeder selesai: 4 skenario akreditasi contoh dibuat/diperbarui.');
    }

    private function seedScenario(array $scenario, int $index, int $actorId, int $validatorId): void
    {
        $now = now();
        $baseDate = Carbon::create(self::YEAR, 2, 1)->addDays($index * 14);
        $nomor = "SIM/REAL/" . self::YEAR . "/{$scenario['code']}";

        $pengajuanId = DB::table('pengajuan_akreditasi')->updateOrInsert(
            ['nomor_pengajuan' => $nomor],
            [
                'nomor_permohonan' => "SIM-PERM/" . self::YEAR . '/' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'id_program_studi' => $scenario['study_program_id'],
                'id_user_pengaju' => $this->adminProdiIdFor($scenario['study_program_id']),
                'id_de_assigned' => $actorId,
                'id_validator_assigned' => $validatorId,
                'tahun_akreditasi' => self::YEAR,
                'pemohon_email' => DB::table('study_programs')->where('id', $scenario['study_program_id'])->value('email'),
                'pemohon_phone' => '021-5550-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'jenis_akreditasi' => $scenario['jenis_akreditasi'],
                'kelompok_akreditasi' => PengajuanAkreditasi::KELOMPOK_INDIVIDUAL,
                'tanggal_pengajuan' => $baseDate->toDateString(),
                'catatan_pengaju' => $scenario['catatan'],
                'status' => $scenario['status'],
                'tanggal_pengingat' => $baseDate,
                'tanggal_surat_permohonan_dikirim' => $baseDate->copy()->addDay(),
                'tanggal_surat_permohonan_diterima' => $baseDate->copy()->addDays(2),
                'tanggal_surat_penerimaan_dikirim' => $baseDate->copy()->addDays(3),
                'tanggal_template_led_dikirim' => $baseDate->copy()->addDays(4),
                'tanggal_pembayaran' => $baseDate->copy()->addDays(5),
                'tanggal_draft_borang' => $baseDate->copy()->addDays(7),
                'tanggal_borang_final' => $baseDate->copy()->addDays(9),
                'tanggal_validasi_borang_assigned' => $baseDate->copy()->addDays(10),
                'tanggal_validasi_borang_selesai' => $baseDate->copy()->addDays(12),
                'tanggal_lanjut_ak' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(13)),
                'tanggal_penugasan_asesor_ak' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(14)),
                'tanggal_ak_mulai' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(15)),
                'tanggal_ak_selesai' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(19)),
                'tanggal_penugasan_asesor_al' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(20)),
                'tanggal_al_mulai' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(21)),
                'tanggal_al_selesai' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(24)),
                'tanggal_hasil_akreditasi_dihitung' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(25)),
                'tanggal_hasil_akreditasi_dikirim' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(26)),
                'tanggal_pengumuman' => $this->whenReached($scenario['status'], [
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                ], $baseDate->copy()->addDays(28)),
                'tanggal_kedaluwarsa_awal' => $baseDate->copy()->addYears(1),
                'tanggal_kedaluwarsa_akhir' => $baseDate->copy()->addYears(5),
                'peringkat_awal' => $scenario['peringkat_awal'],
                'peringkat_hasil' => $scenario['peringkat_hasil'] ?? null,
                'skor_hasil' => $scenario['skor_hasil'] ?? null,
                'masa_berlaku_tahun' => isset($scenario['peringkat_hasil']) ? 5 : null,
                'is_active' => true,
                'is_example' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $pengajuanId = DB::table('pengajuan_akreditasi')->where('nomor_pengajuan', $nomor)->value('id');
        $this->seedPembayaran($pengajuanId, $index, $actorId, $baseDate);
        $this->seedPengajuanDocuments($pengajuanId, $scenario, $actorId);
        $this->seedStatusLogs($pengajuanId, $actorId, $baseDate, $scenario['status']);
        $asesmenId = $this->seedAsesmenShell($pengajuanId, $scenario, $baseDate);

        if ($asesmenId) {
            $this->seedAsesmenDocuments($asesmenId, $scenario, $actorId);
            $this->seedAssignmentsAndPenilaian($asesmenId, $scenario, $validatorId);
        }
    }

    private function seedPembayaran(int $pengajuanId, int $index, int $actorId, Carbon $baseDate): void
    {
        DB::table('pengajuan_pembayaran')->updateOrInsert(
            ['nomor_invoice' => 'SIM-INV-' . self::YEAR . '-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)],
            [
                'id_pengajuan' => $pengajuanId,
                'jenis_pembayaran' => 'akreditasi',
                'jumlah_pembayaran' => 15000000,
                'tanggal_jatuh_tempo' => $baseDate->copy()->addDays(14)->toDateString(),
                'tanggal_pembayaran' => $baseDate->copy()->addDays(5),
                'tanggal_verifikasi' => $baseDate->copy()->addDays(6),
                'status_pembayaran' => 'terverifikasi',
                'catatan_pembayaran' => 'Data pembayaran contoh dari RealDataSeeder.',
                'verified_by' => $actorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedStatusLogs(int $pengajuanId, int $actorId, Carbon $baseDate, string $targetStatus): void
    {
        DB::table('pengajuan_status_log')->where('id_pengajuan', $pengajuanId)->delete();

        $flow = [
            PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
            PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_SELESAI,
            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIHITUNG,
            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        ];

        $previous = PengajuanAkreditasi::STATUS_NEW;
        foreach ($flow as $index => $status) {
            DB::table('pengajuan_status_log')->insert([
                'id_pengajuan' => $pengajuanId,
                'status_from' => $previous,
                'status_to' => $status,
                'changed_by' => $actorId,
                'keterangan' => 'Log contoh RealDataSeeder.',
                'changed_at' => $baseDate->copy()->addDays($index),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($status === $targetStatus) {
                break;
            }

            $previous = $status;
        }
    }

    private function seedAsesmenShell(int $pengajuanId, array $scenario, Carbon $baseDate): ?int
    {
        if (! in_array($scenario['status'], [
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        ], true)) {
            return null;
        }

        $asesmenCode = "SIM-ASM-{$scenario['code']}";
        DB::table('asesmens')->updateOrInsert(
            ['code' => $asesmenCode],
            [
                'id_pengajuan' => $pengajuanId,
                'id_study_program' => $scenario['study_program_id'],
                'name' => "Asesmen {$scenario['code']}",
                'description' => $scenario['catatan'],
                'kode_panel' => 'SIM-' . str_pad((string) $pengajuanId, 3, '0', STR_PAD_LEFT),
                'tanggal_mulai' => $baseDate->copy()->addDays(14)->toDateString(),
                'tanggal_selesai' => $baseDate->copy()->addMonths(2)->toDateString(),
                'status' => $scenario['status'] === PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN ? 'completed' : 'active',
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $asesmenId = DB::table('asesmens')->where('code', $asesmenCode)->value('id');

        DB::table('asesmen_kecukupan')->updateOrInsert(
            ['code' => "SIM-AK-{$scenario['code']}"],
            [
                'id_asesmen' => $asesmenId,
                'tanggal_mulai' => $baseDate->copy()->addDays(15)->toDateString(),
                'tanggal_selesai' => $baseDate->copy()->addDays(20)->toDateString(),
                'status' => $scenario['status'] === PengajuanAkreditasi::STATUS_AK_IN_PROGRESS ? 'active' : 'completed',
                'catatan' => 'AK contoh RealDataSeeder.',
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if (in_array($scenario['status'], [
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        ], true)) {
            DB::table('asesmen_lapangan')->updateOrInsert(
                ['code' => "SIM-AL-{$scenario['code']}"],
                [
                    'id_asesmen' => $asesmenId,
                    'id_asesmen_kecukupan' => DB::table('asesmen_kecukupan')->where('code', "SIM-AK-{$scenario['code']}")->value('id'),
                    'tanggal_mulai' => $baseDate->copy()->addDays(21)->toDateString(),
                    'tanggal_selesai' => $baseDate->copy()->addDays(24)->toDateString(),
                    'lokasi' => 'Kampus program studi',
                    'status' => $scenario['status'] === PengajuanAkreditasi::STATUS_AL_IN_PROGRESS ? 'active' : 'completed',
                    'agenda' => 'Pembukaan, konfirmasi dokumen, wawancara, dan penutupan.',
                    'catatan' => 'AL contoh RealDataSeeder.',
                    'is_active' => true,
                    'is_example' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return $asesmenId;
    }

    private function seedPengajuanDocuments(int $pengajuanId, array $scenario, int $uploadedBy): void
    {
        $documents = [
            'surat_permohonan' => 'Surat Permohonan Akreditasi',
            'surat_penerimaan_de' => 'Surat Penerimaan Permohonan Akreditasi',
            'template_formulir_pembayaran' => 'Template Formulir Pembayaran',
            'formulir_pembayaran' => 'Formulir Pembayaran Terisi',
            'bukti_pembayaran' => 'Bukti Pembayaran',
            'data_kualitatif' => 'Laporan Evaluasi Diri',
            'data_kuantitatif' => 'Laporan Kinerja Program Studi',
            'data_suplemen' => 'Suplemen LED',
            'lembar_pengesahan' => 'Lembar Pengesahan',
            'borang_final' => 'Dokumen Final Borang',
        ];

        if ($this->statusAtLeast($scenario['status'], PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)) {
            $documents['surat_tugas_asesor_ak'] = 'Surat Tugas Asesor AK';
            $documents['surat_tugas_validator_ak'] = 'Surat Tugas Validator AK';
            $documents['laporan_ak'] = 'Laporan Penilaian Kecukupan';
        }

        if ($this->statusAtLeast($scenario['status'], PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)) {
            $documents['surat_tugas_asesor_al'] = 'Surat Tugas Asesor AL';
            $documents['surat_tugas_validator_al'] = 'Surat Tugas Validator AL';
            $documents['laporan_al'] = 'Laporan Asesmen Lapangan';
        }

        if ($this->statusAtLeast($scenario['status'], PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN)) {
            $documents['laporan_hasil'] = 'Laporan Hasil Akreditasi';
            $documents['sertifikat'] = 'Sertifikat Akreditasi';
        }

        foreach ($documents as $jenisDokumen => $title) {
            $path = $this->putPublicSimulationFile(
                "pengajuan-{$pengajuanId}",
                "{$jenisDokumen}.txt",
                "{$title}\nNomor pengajuan: {$pengajuanId}\nSkenario: {$scenario['code']}\n"
            );

            DB::table('pengajuan_dokumen')->updateOrInsert(
                [
                    'id_pengajuan' => $pengajuanId,
                    'jenis_dokumen' => $jenisDokumen,
                    'versi' => 1,
                ],
                [
                    'nama_file' => "{$jenisDokumen}.txt",
                    'path_file' => $path,
                    'original_filename' => "{$title}.txt",
                    'file_size' => Storage::disk('public')->size($path),
                    'mime_type' => 'text/plain',
                    'uploaded_by' => $uploadedBy,
                    'keterangan' => "Dokumen contoh RealDataSeeder untuk {$scenario['code']}.",
                    'template_link' => null,
                    'is_latest' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedAsesmenDocuments(int $asesmenId, array $scenario, int $uploadedBy): void
    {
        $documents = [
            'laporan_validasi_borang' => 'Laporan Validasi Borang',
        ];

        if ($this->statusAtLeast($scenario['status'], PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)) {
            $documents['laporan_validasi_ak'] = 'Laporan Validasi AK';
        }

        if ($this->statusAtLeast($scenario['status'], PengajuanAkreditasi::STATUS_AL_IN_PROGRESS)) {
            $documents['berita_acara_al'] = 'Berita Acara AL';
            $documents['lha_asesor'] = 'Laporan Hasil Asesmen Lapangan';
        }

        if ($this->statusAtLeast($scenario['status'], PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN)) {
            $documents['laporan_al'] = 'Laporan Rekap AL';
            $documents['berita_acara_penyampaian_hasil'] = 'Berita Acara Penyampaian Hasil';
            $documents['berita_acara_penetapan_hasil'] = 'Berita Acara Penetapan Hasil';
        }

        $sortOrder = 1;
        foreach ($documents as $type => $title) {
            $path = $this->putPublicSimulationFile(
                "asesmen-{$asesmenId}",
                "{$type}.txt",
                "{$title}\nAsesmen: {$asesmenId}\nSkenario: {$scenario['code']}\n"
            );

            DB::table('asesmen_documents')->updateOrInsert(
                [
                    'id_asesmen' => $asesmenId,
                    'type' => $type,
                    'title' => $title,
                ],
                [
                    'sort_order' => $sortOrder++,
                    'path' => $path,
                    'original_name' => "{$title}.txt",
                    'size' => Storage::disk('public')->size($path),
                    'mime' => 'text/plain',
                    'is_active' => true,
                    'version' => 1,
                    'keterangan' => "Dokumen asesmen contoh RealDataSeeder untuk {$scenario['code']}.",
                    'uploaded_by' => $uploadedBy,
                    'uploaded_at' => now(),
                    'status_persetujuan_prodi' => 'approved',
                    'approved_by_prodi' => $this->adminProdiIdFor($scenario['study_program_id']),
                    'approved_at_prodi' => now(),
                    'status_persetujuan_de' => 'approved',
                    'approved_by_de' => $uploadedBy,
                    'approved_at_de' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedAssignmentsAndPenilaian(int $asesmenId, array $scenario, int $validatorId): void
    {
        $roleAsesorId = DB::table('roles')->where('name', 'asesor')->value('id');
        $roleValidatorId = DB::table('roles')->where('name', 'validator')->value('id');
        $asesorIds = DB::table('users')->where('role_selected', 'asesor')->orderBy('id')->limit(2)->pluck('id');
        $elemenIds = DB::table('elemen_standar')->orderBy('id')->pluck('id');

        if (! $roleAsesorId || ! $roleValidatorId || $asesorIds->count() < 2 || $elemenIds->isEmpty()) {
            return;
        }

        $akId = DB::table('asesmen_kecukupan')->where('id_asesmen', $asesmenId)->value('id');
        $alId = DB::table('asesmen_lapangan')->where('id_asesmen', $asesmenId)->value('id');

        $akWorkStatus = match ($scenario['status']) {
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS => 'in_progress',
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN => 'approved',
            default => 'not_started',
        };

        foreach ($asesorIds as $index => $asesorId) {
            $this->seedAssignment($asesmenId, $asesorId, $roleAsesorId, 'ak', $akId, null, $index + 1, $akWorkStatus);
            $this->seedPenilaianAk($asesmenId, $asesorId, $validatorId, $elemenIds, $scenario, $index);
        }

        $this->seedAssignment($asesmenId, $validatorId, $roleValidatorId, 'ak', $akId, null, null, $akWorkStatus === 'approved' ? 'approved' : 'in_progress');

        if (! $alId) {
            return;
        }

        $alWorkStatus = $scenario['status'] === PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN
            ? 'approved'
            : 'in_progress';

        foreach ($asesorIds as $index => $asesorId) {
            $this->seedAssignment($asesmenId, $asesorId, $roleAsesorId, 'al', null, $alId, $index + 1, $alWorkStatus);
            $this->seedPenilaianAl($asesmenId, $asesorId, $elemenIds, $scenario, $index);
        }
    }

    private function seedAssignment(
        int $asesmenId,
        int $userId,
        int $roleId,
        string $jenisAsesmen,
        ?int $akId,
        ?int $alId,
        ?int $urutan,
        string $statusPekerjaan
    ): void {
        DB::table('asesmen_user_roles')->updateOrInsert(
            [
                'id_asesmen' => $asesmenId,
                'id_user' => $userId,
                'jenis_asesmen' => $jenisAsesmen,
                'id_role' => $roleId,
            ],
            [
                'id_asesmen_kecukupan' => $akId,
                'id_asesmen_lapangan' => $alId,
                'urutan_asesor' => $urutan,
                'status_penawaran' => 'accepted',
                'responded_at' => now()->subDays(3),
                'response_note' => 'Accepted otomatis oleh RealDataSeeder.',
                'status_pekerjaan' => $statusPekerjaan,
                'submitted_at' => in_array($statusPekerjaan, ['submitted', 'approved'], true) ? now()->subDays(1) : null,
                'approved_at' => $statusPekerjaan === 'approved' ? now()->subHours(12) : null,
                'approved_by' => $statusPekerjaan === 'approved' ? $this->userIdByRole('validator') : null,
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedPenilaianAk(int $asesmenId, int $asesorId, int $validatorId, $elemenIds, array $scenario, int $offset): void
    {
        $isFinal = $this->statusAtLeast($scenario['status'], PengajuanAkreditasi::STATUS_AL_IN_PROGRESS);
        $isSubmitted = $isFinal || $scenario['status'] === PengajuanAkreditasi::STATUS_AK_IN_PROGRESS;

        foreach ($elemenIds as $index => $elemenId) {
            $skor = 2 + (($index + $offset) % 2);
            DB::table('penilaian_elemen_ak')->updateOrInsert(
                [
                    'id_asesmen' => $asesmenId,
                    'id_asesor' => $asesorId,
                    'id_elemen' => $elemenId,
                ],
                [
                    'skor' => $skor,
                    'komentar' => "RealData AK {$scenario['code']}: bukti elemen {$elemenId} telah ditelaah asesor.",
                    'status' => $isSubmitted ? 'submitted' : 'draft',
                    'status_validasi' => $isFinal ? 'approved' : 'not_validated',
                    'preferensi_skor' => $isFinal ? $skor : null,
                    'skor_final' => $isFinal ? $skor : null,
                    'catatan_validator' => $isFinal ? 'Disetujui sebagai data contoh RealDataSeeder.' : null,
                    'validated_by' => $isFinal ? $validatorId : null,
                    'validated_at' => $isFinal ? now()->subDay() : null,
                    'validation_note' => $isFinal ? 'Validasi final contoh.' : null,
                    'revision_count' => 0,
                    'is_locked' => $isFinal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedPenilaianAl(int $asesmenId, int $asesorId, $elemenIds, array $scenario, int $offset): void
    {
        $isFinal = $scenario['status'] === PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN;

        foreach ($elemenIds as $index => $elemenId) {
            DB::table('penilaian_elemen_al')->updateOrInsert(
                [
                    'id_asesmen' => $asesmenId,
                    'id_asesor' => $asesorId,
                    'id_elemen' => $elemenId,
                ],
                [
                    'skor' => 2 + (($index + $offset + 1) % 2),
                    'komentar' => "RealData AL {$scenario['code']}: temuan lapangan elemen {$elemenId} telah dicatat.",
                    'status' => $isFinal ? 'approved' : 'draft',
                    'is_locked' => $isFinal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function putPublicSimulationFile(string $folder, string $filename, string $content): string
    {
        $path = "sim-real/{$folder}/{$filename}";
        Storage::disk('public')->put($path, $content);

        return $path;
    }

    private function statusAtLeast(string $actualStatus, string $expectedStatus): bool
    {
        $order = [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED => 1,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS => 2,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS => 3,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN => 4,
        ];

        return ($order[$actualStatus] ?? 0) >= ($order[$expectedStatus] ?? PHP_INT_MAX);
    }

    private function whenReached(string $status, array $statuses, Carbon $value): ?Carbon
    {
        return in_array($status, $statuses, true) ? $value : null;
    }

    private function userIdByRole(string $role): ?int
    {
        return DB::table('users')->where('role_selected', $role)->orderBy('id')->value('id');
    }

    private function adminProdiIdFor(int $studyProgramId): ?int
    {
        return DB::table('study_program_users')
            ->where('id_study_program', $studyProgramId)
            ->where('is_active', true)
            ->value('id_user')
            ?: $this->userIdByRole('admin_prodi');
    }
}
