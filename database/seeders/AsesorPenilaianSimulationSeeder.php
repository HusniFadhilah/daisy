<?php

namespace Database\Seeders;

use App\Models\PengajuanAkreditasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AsesorPenilaianSimulationSeeder extends Seeder
{
    private const YEAR = 2026;
    private const NOMOR_PENGAJUAN = 'SIM/ASESOR/2026/AK-AL-READY';

    public function run(): void
    {
        $roleAsesorId = DB::table('roles')->where('name', 'asesor')->value('id');
        $roleValidatorId = DB::table('roles')->where('name', 'validator')->value('id');
        $asesorIds = DB::table('users')->where('role_selected', 'asesor')->orderBy('id')->limit(2)->pluck('id');
        $validatorId = DB::table('users')->where('role_selected', 'validator')->orderBy('id')->value('id');
        $deId = DB::table('users')->where('role_selected', 'sekretariat')->orderBy('id')->value('id');
        $studyProgramId = DB::table('study_programs')->where('is_active', true)->orderBy('id')->value('id');
        $elemenIds = DB::table('elemen_standar')->orderBy('id')->pluck('id');

        if (! $roleAsesorId || ! $roleValidatorId || $asesorIds->count() < 2 || ! $validatorId || ! $deId || ! $studyProgramId || $elemenIds->isEmpty()) {
            $this->command?->warn('AsesorPenilaianSimulationSeeder dilewati: master user/role/prodi/elemen belum lengkap.');
            return;
        }

        DB::transaction(function () use ($roleAsesorId, $roleValidatorId, $asesorIds, $validatorId, $deId, $studyProgramId, $elemenIds) {
            $baseDate = Carbon::create(self::YEAR, 4, 1);
            $pengajuanId = $this->seedPengajuan($studyProgramId, $deId, $validatorId, $baseDate);
            $asesmenId = $this->seedAsesmen($pengajuanId, $studyProgramId, $baseDate);
            $akId = $this->seedAk($asesmenId, $baseDate);
            $alId = $this->seedAl($asesmenId, $akId, $baseDate);

            foreach ($asesorIds as $index => $asesorId) {
                $this->seedAssignment($asesmenId, $asesorId, $roleAsesorId, 'ak', $akId, null, $index + 1, 'submitted');
                $this->seedAssignment($asesmenId, $asesorId, $roleAsesorId, 'al', null, $alId, $index + 1, 'in_progress');
                $this->seedPenilaianAk($asesmenId, $asesorId, $validatorId, $elemenIds, $index);
                $this->seedPenilaianAl($asesmenId, $asesorId, $elemenIds, $index);
            }

            $this->seedAssignment($asesmenId, $validatorId, $roleValidatorId, 'ak', $akId, null, null, 'in_progress');
            $this->seedStatusLogs($pengajuanId, $deId, $baseDate);
        });

        $this->command?->info('AsesorPenilaianSimulationSeeder selesai: asesor sudah accepted untuk AK dan AL, dengan data penilaian contoh.');
    }

    private function seedPengajuan(int $studyProgramId, int $deId, int $validatorId, Carbon $baseDate): int
    {
        DB::table('pengajuan_akreditasi')->updateOrInsert(
            ['nomor_pengajuan' => self::NOMOR_PENGAJUAN],
            [
                'nomor_permohonan' => 'SIM-ASESOR-PERM/2026/001',
                'id_program_studi' => $studyProgramId,
                'id_user_pengaju' => DB::table('study_program_users')->where('id_study_program', $studyProgramId)->value('id_user')
                    ?: DB::table('users')->where('role_selected', 'admin_prodi')->value('id'),
                'id_de_assigned' => $deId,
                'id_validator_assigned' => $validatorId,
                'tahun_akreditasi' => self::YEAR,
                'pemohon_email' => DB::table('study_programs')->where('id', $studyProgramId)->value('email'),
                'pemohon_phone' => '021-5550-777',
                'jenis_akreditasi' => PengajuanAkreditasi::AKREDITASI_MENUJU_UNGGUL,
                'kelompok_akreditasi' => PengajuanAkreditasi::KELOMPOK_INDIVIDUAL,
                'tanggal_pengajuan' => $baseDate->toDateString(),
                'catatan_pengaju' => 'Simulasi asesor: penawaran AK dan AL sudah diterima, siap mencoba penilaian.',
                'status' => PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                'tanggal_pengingat' => $baseDate,
                'tanggal_surat_permohonan_diterima' => $baseDate->copy()->addDay(),
                'tanggal_pembayaran' => $baseDate->copy()->addDays(2),
                'tanggal_borang_final' => $baseDate->copy()->addDays(5),
                'tanggal_validasi_borang_selesai' => $baseDate->copy()->addDays(6),
                'tanggal_lanjut_ak' => $baseDate->copy()->addDays(7),
                'tanggal_penugasan_asesor_ak' => $baseDate->copy()->addDays(8),
                'tanggal_ak_mulai' => $baseDate->copy()->addDays(9),
                'tanggal_ak_selesai' => $baseDate->copy()->addDays(12),
                'tanggal_penugasan_asesor_al' => $baseDate->copy()->addDays(13),
                'tanggal_al_mulai' => $baseDate->copy()->addDays(14),
                'tanggal_kedaluwarsa_awal' => $baseDate->copy()->addYear(),
                'tanggal_kedaluwarsa_akhir' => $baseDate->copy()->addYears(5),
                'peringkat_awal' => 'Baik Sekali',
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return DB::table('pengajuan_akreditasi')->where('nomor_pengajuan', self::NOMOR_PENGAJUAN)->value('id');
    }

    private function seedAsesmen(int $pengajuanId, int $studyProgramId, Carbon $baseDate): int
    {
        DB::table('asesmens')->updateOrInsert(
            ['code' => 'SIM-ASM-ASESOR-AK-AL-READY'],
            [
                'id_pengajuan' => $pengajuanId,
                'id_study_program' => $studyProgramId,
                'name' => 'Simulasi Penilaian AK dan AL untuk Asesor',
                'description' => 'Asesmen contoh agar asesor bisa langsung membuka penawaran accepted dan mengisi penilaian.',
                'kode_panel' => 'SIM-ASESOR',
                'tanggal_mulai' => $baseDate->copy()->addDays(8)->toDateString(),
                'tanggal_selesai' => $baseDate->copy()->addMonths(2)->toDateString(),
                'status' => 'active',
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return DB::table('asesmens')->where('code', 'SIM-ASM-ASESOR-AK-AL-READY')->value('id');
    }

    private function seedAk(int $asesmenId, Carbon $baseDate): int
    {
        DB::table('asesmen_kecukupan')->updateOrInsert(
            ['code' => 'SIM-AK-ASESOR-READY'],
            [
                'id_asesmen' => $asesmenId,
                'tanggal_mulai' => $baseDate->copy()->addDays(9)->toDateString(),
                'tanggal_selesai' => $baseDate->copy()->addDays(12)->toDateString(),
                'status' => 'completed',
                'catatan' => 'AK sudah tersedia untuk penilaian contoh.',
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return DB::table('asesmen_kecukupan')->where('code', 'SIM-AK-ASESOR-READY')->value('id');
    }

    private function seedAl(int $asesmenId, int $akId, Carbon $baseDate): int
    {
        DB::table('asesmen_lapangan')->updateOrInsert(
            ['code' => 'SIM-AL-ASESOR-READY'],
            [
                'id_asesmen' => $asesmenId,
                'id_asesmen_kecukupan' => $akId,
                'tanggal_mulai' => $baseDate->copy()->addDays(14)->toDateString(),
                'tanggal_selesai' => $baseDate->copy()->addDays(17)->toDateString(),
                'lokasi' => 'Kampus simulasi program studi',
                'alamat' => 'Alamat kampus simulasi',
                'status' => 'active',
                'agenda' => 'Pembukaan, wawancara, validasi lapangan, dan rapat penutup.',
                'catatan' => 'AL sudah accepted agar asesor bisa langsung menilai.',
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return DB::table('asesmen_lapangan')->where('code', 'SIM-AL-ASESOR-READY')->value('id');
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
                'responded_at' => now()->subDays(2),
                'response_note' => 'Accepted otomatis oleh AsesorPenilaianSimulationSeeder.',
                'status_pekerjaan' => $statusPekerjaan,
                'submitted_at' => $statusPekerjaan === 'submitted' ? now()->subDay() : null,
                'is_active' => true,
                'is_example' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedPenilaianAk(int $asesmenId, int $asesorId, int $validatorId, $elemenIds, int $offset): void
    {
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
                    'komentar' => "Simulasi AK: bukti elemen {$elemenId} dinilai cukup dengan catatan perbaikan terukur.",
                    'status' => 'submitted',
                    'status_validasi' => $index % 5 === 0 ? 'validated_diff' : 'validated',
                    'preferensi_skor' => $skor,
                    'skor_final' => $skor,
                    'catatan_validator' => $index % 5 === 0 ? 'Perlu penyelarasan narasi, skor tetap dapat diterima.' : 'Valid.',
                    'validated_by' => $validatorId,
                    'validated_at' => now()->subHours(12),
                    'validation_note' => 'Validasi contoh untuk simulasi asesor.',
                    'revision_count' => 0,
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedPenilaianAl(int $asesmenId, int $asesorId, $elemenIds, int $offset): void
    {
        foreach ($elemenIds as $index => $elemenId) {
            DB::table('penilaian_elemen_al')->updateOrInsert(
                [
                    'id_asesmen' => $asesmenId,
                    'id_asesor' => $asesorId,
                    'id_elemen' => $elemenId,
                ],
                [
                    'skor' => 2 + (($index + $offset + 1) % 2),
                    'komentar' => "Simulasi AL: temuan lapangan elemen {$elemenId} sudah dicatat untuk diskusi tim asesor.",
                    'status' => 'draft',
                    'is_locked' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function seedStatusLogs(int $pengajuanId, int $deId, Carbon $baseDate): void
    {
        DB::table('pengajuan_status_log')->where('id_pengajuan', $pengajuanId)->delete();

        $flow = [
            PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
            PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
            PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
        ];

        $previous = PengajuanAkreditasi::STATUS_NEW;
        foreach ($flow as $index => $status) {
            DB::table('pengajuan_status_log')->insert([
                'id_pengajuan' => $pengajuanId,
                'status_from' => $previous,
                'status_to' => $status,
                'changed_by' => $deId,
                'keterangan' => 'Log simulasi asesor accepted dan penilaian.',
                'changed_at' => $baseDate->copy()->addDays($index),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $previous = $status;
        }
    }
}
