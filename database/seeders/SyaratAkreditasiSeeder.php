<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\JenjangPenilaian;
use App\Models\StatusAkreditasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyaratAkreditasiSeeder extends Seeder
{
    public function run(): void
    {
        $now   = now();
        $versi = '2026-v1';

        $degrees = DegreeLevel::pluck('id', 'code');

        // ============================================================
        // GRUP DEGREE LEVEL
        // ============================================================
        $diploma  = ['d1', 'd2', 'd3', 'd4'];
        $sarjana  = ['s1'];
        $magister = ['s2', 's2-terapan'];
        $doktor   = ['s3', 's3-terapan'];
        $profesi  = ['profesi', 'spesialis-1', 'spesialis-2'];

        // ============================================================
        // 1. SYARAT GLOBAL — id_degree_level NULL
        // ============================================================
        $globalRows = [
            [
                'kelompok'   => 'skor',
                'kunci'      => 'skor_minimum_unggul',
                'nilai'      => '281',
                'tipe'       => 'integer',
                'label'      => 'Skor Minimum untuk Status Unggul',
                'keterangan' => 'Skor final AL (0–400) yang harus dicapai agar program studi '
                    . 'berpeluang mendapatkan status Terakreditasi Unggul.',
            ],
            [
                'kelompok'   => 'pelampauan',
                'kunci'      => 'kriteria_required',
                'nilai'      => json_encode(['D', 'E', 'P', 'I', 'L', 'A', 'R']),
                'tipe'       => 'array',
                'label'      => 'Kriteria Wajib ' . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI,
                'keterangan' => 'Kode kriteria yang masing-masing harus memiliki minimal 1 '
                    . 'elemen dengan rata-rata skor ≥ 4 (' . JenjangPenilaian::LABEL_SYARAT_UNGGUL_MELAMPAUI . ') '
                    . 'agar syarat Unggul terpenuhi.',
            ],
            [
                'kelompok'   => 'rasio_dtps',
                'kunci'      => 'max_rasio_lingkungan',
                'nilai'      => '40',
                'tipe'       => 'integer',
                'label'      => 'Rasio Maksimal DTPS:Mahasiswa (Bidang Lingkungan)',
                'keterangan' => 'Batas 1:40 untuk rumpun lingkungan, berlaku semua jenjang.',
            ],
            [
                'kelompok'   => 'rasio_dtps',
                'kunci'      => 'max_rasio_default',
                'nilai'      => '30',
                'tipe'       => 'integer',
                'label'      => 'Rasio Maksimal DTPS:Mahasiswa (Bidang Desain, Perencanaan, Arsitektur)',
                'keterangan' => 'Batas 1:30 untuk rumpun selain lingkungan, berlaku semua jenjang.',
            ],
            [
                'kelompok'   => 'rasio_dtps',
                'kunci'      => 'rumpun_rasio_khusus',
                'nilai'      => json_encode(['lingkungan']),
                'tipe'       => 'array',
                'label'      => 'Daftar Rumpun dengan Rasio Khusus',
                'keterangan' => 'Rumpun yang menggunakan max_rasio_lingkungan.',
            ],
            ...self::buildRentangSkorFromDB(),
        ];

        foreach ($globalRows as $row) {
            $this->upsert($row, null, $versi, $now);
        }

        // ============================================================
        // 2. SYARAT JABATAN — DIPLOMA & SARJANA
        //    Lektor ke atas (Lektor, LK, GB) ≥ 50%
        // ============================================================
        $this->seedJabatan(
            codes: array_merge($diploma, $sarjana),
            degrees: $degrees,
            jabatanValid: ['lektor', 'lektor kepala', 'guru besar', 'professor'],
            label: 'Jabatan Valid — Lektor ke Atas (Diploma & Sarjana)',
            keterangan: 'Diploma & Sarjana: Lektor, Lektor Kepala, Guru Besar dihitung sebagai Lektor ke atas.',
            persen: 50.0,
            labelPersen: 'Persentase Minimum DTPS Jabatan Lektor ke Atas — Diploma & Sarjana (%)',
            versi: $versi,
            now: $now,
        );

        // ============================================================
        // 3. SYARAT JABATAN — MAGISTER
        //    Lektor Kepala ke atas (LK, GB) ≥ 50%
        // ============================================================
        $this->seedJabatan(
            codes: $magister,
            degrees: $degrees,
            jabatanValid: ['lektor kepala', 'guru besar', 'professor'],
            label: 'Jabatan Valid — Lektor Kepala ke Atas (Magister)',
            keterangan: 'Magister: hanya Lektor Kepala dan Guru Besar yang dihitung.',
            persen: 50.0,
            labelPersen: 'Persentase Minimum DTPS Jabatan LK ke Atas — Magister (%)',
            versi: $versi,
            now: $now,
        );

        // ============================================================
        // 4. SYARAT JABATAN — DOKTOR
        //    LK dan GB ≥ 50%
        // ============================================================
        $this->seedJabatan(
            codes: $doktor,
            degrees: $degrees,
            jabatanValid: ['lektor kepala', 'guru besar', 'professor'],
            label: 'Jabatan Valid — LK dan GB (Doktor)',
            keterangan: 'Doktor: minimal 50% DTPS harus Lektor Kepala atau Guru Besar.',
            persen: 50.0,
            labelPersen: 'Persentase Minimum DTPS Jabatan LK dan GB — Doktor (%)',
            versi: $versi,
            now: $now,
        );

        // ============================================================
        // 5. SYARAT SERTIFIKAT — PROFESI
        //    Kelompok 'sertifikat', bukan 'jabatan'
        // ============================================================
        foreach ($profesi as $code) {
            $dlId = $degrees[$code] ?? null;
            if (!$dlId) continue;

            $this->upsert([
                'kelompok'   => 'sertifikat',
                'kunci'      => 'persen_minimum_sertifikat_profesi',
                'nilai'      => '50',
                'tipe'       => 'float',
                'label'      => 'Persentase Minimum Dosen Bersertifikat Profesi (%)',
                'keterangan' => 'Profesi: minimal 50% dosen harus memiliki sertifikat kompetensi/profesi/industri.',
            ], $dlId, $versi, $now);
        }

        // ============================================================
        // 6. SYARAT CAPAIAN LULUSAN — per degree level
        //    Sumber data: R.3.1.c (kolaborasi) + R.3.1.e (mandiri)
        // ============================================================

        // Diploma & Sarjana — karya inovasi industri/masyarakat (semua skala, kolom Karya)
        $this->seedLulusan(
            codes: array_merge($diploma, $sarjana),
            degrees: $degrees,
            persen: 10.0,
            tipe: 'inovasi_industri',
            label: 'Capaian Lulusan — Karya Inovasi Industri/Masyarakat (Diploma & Sarjana)',
            keterangan: '10% lulusan memiliki karya inovasi yang dipakai industri atau masyarakat. '
                . 'Dibaca dari R.3.1.c + R.3.1.e sub-tabel .5 (rekapitulasi semua skala), kolom Karya/Inovasi.',
            versi: $versi,
            now: $now,
        );

        // Magister — publikasi SINTA atau karya inovatif setara (skala nasional, kolom Publikasi)
        $this->seedLulusan(
            codes: $magister,
            degrees: $degrees,
            persen: 10.0,
            tipe: 'publikasi_sinta',
            label: 'Capaian Lulusan — Publikasi SINTA atau Karya Inovatif (Magister)',
            keterangan: '10% lulusan memiliki publikasi terakreditasi nasional SINTA atau karya inovatif yang setara. '
                . 'Dibaca dari R.3.1.c + R.3.1.e sub-tabel .2 (skala nasional), kolom Publikasi Ilmiah.',
            versi: $versi,
            now: $now,
        );

        // Doktor — publikasi internasional bereputasi (skala internasional, kolom Publikasi)
        $this->seedLulusan(
            codes: $doktor,
            degrees: $degrees,
            persen: 5.0,
            tipe: 'publikasi_internasional',
            label: 'Capaian Lulusan — Publikasi Internasional Bereputasi (Doktor)',
            keterangan: '5% lulusan memiliki publikasi internasional bereputasi atau karya inovatif yang setara. '
                . 'Dibaca dari R.3.1.c + R.3.1.e sub-tabel .1 (skala internasional), kolom Publikasi Ilmiah.',
            versi: $versi,
            now: $now,
        );

        // Profesi — karya inovasi validasi profesi / ujian kompetensi (semua skala, kolom Karya)
        $this->seedLulusan(
            codes: $profesi,
            degrees: $degrees,
            persen: 10.0,
            tipe: 'inovasi_profesi',
            label: 'Capaian Lulusan — Inovasi Validasi Profesi atau Ujian Kompetensi (Profesi)',
            keterangan: '10% lulusan memiliki karya inovasi yang divalidasi oleh organisasi profesi '
                . 'atau lulus ujian kompetensi. '
                . 'Dibaca dari R.3.1.c + R.3.1.e sub-tabel .5 (rekapitulasi semua skala), kolom Karya/Inovasi.',
            versi: $versi,
            now: $now,
        );
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function seedJabatan(
        array  $codes,
        object $degrees,
        array  $jabatanValid,
        string $label,
        string $keterangan,
        float  $persen,
        string $labelPersen,
        string $versi,
        mixed  $now,
    ): void {
        foreach ($codes as $code) {
            $dlId = $degrees[$code] ?? null;
            if (!$dlId) continue;

            $this->upsert([
                'kelompok'   => 'jabatan',
                'kunci'      => 'jabatan_valid',
                'nilai'      => json_encode($jabatanValid),
                'tipe'       => 'array',
                'label'      => $label,
                'keterangan' => $keterangan,
            ], $dlId, $versi, $now);

            $this->upsert([
                'kelompok'   => 'jabatan',
                'kunci'      => 'persen_minimum',
                'nilai'      => (string)$persen,
                'tipe'       => 'float',
                'label'      => $labelPersen,
                'keterangan' => $keterangan,
            ], $dlId, $versi, $now);
        }
    }

    private function seedLulusan(
        array  $codes,
        object $degrees,
        float  $persen,
        string $tipe,
        string $label,
        string $keterangan,
        string $versi,
        mixed  $now,
    ): void {
        foreach ($codes as $code) {
            $dlId = $degrees[$code] ?? null;
            if (!$dlId) continue;

            // Persentase minimum lulusan yang memenuhi capaian
            $this->upsert([
                'kelompok'   => 'lulusan',
                'kunci'      => 'persen_minimum',
                'nilai'      => (string)$persen,
                'tipe'       => 'float',
                'label'      => $label,
                'keterangan' => $keterangan,
            ], $dlId, $versi, $now);

            // Tipe capaian — menentukan sheet + sub-tabel + kolom yang dibaca
            $this->upsert([
                'kelompok'   => 'lulusan',
                'kunci'      => 'tipe_capaian',
                'nilai'      => $tipe,
                'tipe'       => 'string',
                'label'      => "Tipe Capaian Lulusan — {$label}",
                'keterangan' => $keterangan,
            ], $dlId, $versi, $now);
        }
    }

    private function upsert(array $row, ?int $degreeLevelId, string $versi, mixed $now): void
    {
        DB::table('syarat_akreditasi')->updateOrInsert(
            [
                'kelompok'        => $row['kelompok'],
                'kunci'           => $row['kunci'],
                'versi'           => $versi,
                'id_degree_level' => $degreeLevelId,
            ],
            array_merge($row, [
                'id_degree_level' => $degreeLevelId,
                'versi'           => $versi,
                'berlaku_mulai'   => '2026-01-01',
                'berlaku_sampai'  => null,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ])
        );
    }

    private static function buildRentangSkorFromDB(): array
    {
        return StatusAkreditasi::orderBy('skor_min')->get()->map(function ($sa) {
            return [
                'kelompok'   => 'rentang_skor',
                'kunci'      => "{$sa->skor_min}_{$sa->skor_max}",
                'nilai'      => json_encode([
                    'skor_min'     => $sa->skor_min,
                    'skor_max'     => $sa->skor_max,
                    'persen_min'   => $sa->persen_min,
                    'persen_max'   => $sa->persen_max,
                    'status'       => $sa->status,
                    'makna'        => $sa->makna,
                    'siklus_tahun' => $sa->siklus_tahun,
                    'warna'        => $sa->warna,
                ]),
                'tipe'       => 'json',
                'label'      => "Rentang Skor {$sa->skor_min}–{$sa->skor_max}",
                'keterangan' => "{$sa->status}, siklus {$sa->siklus_tahun} tahun.",
            ];
        })->toArray();
    }
}
