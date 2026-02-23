<?php

namespace Database\Seeders;

use App\Models\StatusAkreditasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyaratAkreditasiSeeder extends Seeder
{
    public function run(): void
    {
        $now   = now();
        $versi = '2026-v1';

        $data = [
            // ============================================================
            // KELOMPOK: skor
            // ============================================================
            [
                'kelompok'      => 'skor',
                'kunci'         => 'skor_minimum_unggul',
                'nilai'         => '281', // ✅ sesuai tabel regulasi: Unggul mulai dari 281
                'tipe'          => 'integer',
                'label'         => 'Skor Minimum untuk Status Unggul',
                'keterangan'    => 'Skor final AL (0–400) yang harus dicapai agar program studi '
                    . 'berpeluang mendapatkan status Terakreditasi Unggul.',
            ],

            // ============================================================
            // KELOMPOK: pelampauan
            // ============================================================
            [
                'kelompok'      => 'pelampauan',
                'kunci'         => 'kriteria_required',
                'nilai'         => json_encode(['D', 'E', 'P', 'I', 'L', 'A', 'R']),
                'tipe'          => 'array',
                'label'         => 'Kriteria Wajib Melampaui Standar',
                'keterangan'    => 'Kode kriteria yang masing-masing harus memiliki minimal 1 '
                    . 'elemen dengan rata-rata skor ≥ 4 (Melampaui Standar) '
                    . 'agar syarat Unggul terpenuhi.',
            ],

            // ============================================================
            // KELOMPOK: rasio_dtps
            // ============================================================
            [
                'kelompok'      => 'rasio_dtps',
                'kunci'         => 'max_rasio_lingkungan',
                'nilai'         => '40',
                'tipe'          => 'integer',
                'label'         => 'Rasio Maksimal DTPS:Mahasiswa (Bidang Lingkungan)',
                'keterangan'    => 'Jumlah mahasiswa aktif maksimal per 1 DTPS untuk rumpun '
                    . 'lingkungan. Contoh: nilai 40 berarti batas 1:40.',
            ],
            [
                'kelompok'      => 'rasio_dtps',
                'kunci'         => 'max_rasio_default',
                'nilai'         => '30',
                'tipe'          => 'integer',
                'label'         => 'Rasio Maksimal DTPS:Mahasiswa (Bidang Desain, Perencanaan, Arsitektur)',
                'keterangan'    => 'Jumlah mahasiswa aktif maksimal per 1 DTPS untuk rumpun selain '
                    . 'lingkungan (desain, perencanaan, arsitektur). '
                    . 'Contoh: nilai 30 berarti batas 1:30.',
            ],
            [
                'kelompok'      => 'rasio_dtps',
                'kunci'         => 'rumpun_rasio_khusus',
                'nilai'         => json_encode(['lingkungan']),
                'tipe'          => 'array',
                'label'         => 'Daftar Rumpun dengan Rasio Khusus',
                'keterangan'    => 'Rumpun yang menggunakan max_rasio_lingkungan. '
                    . 'Rumpun lain menggunakan max_rasio_default.',
            ],

            // ============================================================
            // KELOMPOK: jabatan
            // ============================================================
            [
                'kelompok'      => 'jabatan',
                'kunci'         => 'jabatan_lektor_ke_atas',
                'nilai'         => json_encode(['lektor', 'lektor kepala', 'guru besar', 'professor']),
                'tipe'          => 'array',
                'label'         => 'Jabatan yang Dihitung sebagai Lektor ke Atas',
                'keterangan'    => 'Daftar nama jabatan (lowercase) yang dianggap memenuhi syarat '
                    . '"Lektor ke atas" sesuai regulasi. '
                    . 'Perubahan nama jabatan di masa depan cukup update nilai ini.',
            ],
            [
                'kelompok'   => 'jabatan',
                'kunci'      => 'persen_minimum_lektor',
                'nilai'      => '50',
                'tipe'       => 'float',
                'label'      => 'Persentase Minimum DTPS Jabatan Lektor ke Atas (%)',
                'keterangan' => 'Persentase minimum DTPS yang harus memiliki jabatan Lektor '
                    . 'ke atas dari total DTPS. Contoh: 50 berarti minimal 50%.',
            ],

            // ============================================================
            // KELOMPOK: rentang_skor
            // Nilai diambil dinamis dari tabel status_akreditasi
            // agar skor_min, skor_max, status, warna, siklus selalu sinkron.
            // Seeder ini harus dijalankan SETELAH StatusAkreditasiSeeder.
            // ============================================================
            ...self::buildRentangSkorFromDB(),
        ];

        foreach ($data as $row) {
            DB::table('syarat_akreditasi')->updateOrInsert(
                [
                    'kelompok' => $row['kelompok'],
                    'kunci'    => $row['kunci'],
                    'versi'    => $versi,
                ],
                array_merge($row, [
                    'versi'          => $versi,
                    'berlaku_mulai'  => '2026-01-01',
                    'berlaku_sampai' => null,
                    'is_active'      => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ])
            );
        }
    }

    private static function buildRentangSkorFromDB(): array
    {
        return StatusAkreditasi::orderBy('skor_min')->get()->map(function ($sa) {
            $kunci = "{$sa->skor_min}_{$sa->skor_max}";

            return [
                'kelompok'   => 'rentang_skor',
                'kunci'      => $kunci,
                'nilai'      => json_encode([
                    'skor_min'     => $sa->skor_min,
                    'skor_max'     => $sa->skor_max,
                    'persen_min'   => $sa->persen_min,
                    'persen_max'   => $sa->persen_max,
                    'status'       => $sa->status,
                    'makna'        => $sa->makna,   // string atau array, diambil apa adanya
                    'siklus_tahun' => $sa->siklus_tahun,
                    'warna'        => $sa->warna,   // ← diambil dari StatusAkreditasi
                ]),
                'tipe'       => 'json',
                'label'      => "Rentang Skor {$sa->skor_min}–{$sa->skor_max}",
                'keterangan' => "{$sa->status}, siklus {$sa->siklus_tahun} tahun.",
            ];
        })->toArray();
    }
}
