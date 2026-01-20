<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\ElemenStandar;
use App\Models\BobotPenilaian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StudyProgramCategory;

class BobotPenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BobotPenilaian::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Data bobot lengkap sesuai tabel standar LAMDEPILAR
        // Format: [kode_elemen, bobot untuk setiap jenjang]
        $bobotData = [
            // Kriteria D - Diferensiasi Misi
            ['kode' => 'D.1', 'bobot' => ['s1' => 1, 's2' => 1, 's3' => 1, 'd1' => 1, 'd2' => 1, 'd3' => 1, 'd4' => 1, 's2-terapan' => 1, 's3-terapan' => 1, 'profesi' => 1, 'spesialis-1' => 1, 'spesialis-2' => 1]],
            ['kode' => 'D.2', 'bobot' => ['s1' => 4, 's2' => 4, 's3' => 4, 'd1' => 4, 'd2' => 4, 'd3' => 4, 'd4' => 4, 's2-terapan' => 4, 's3-terapan' => 4, 'profesi' => 4, 'spesialis-1' => 4, 'spesialis-2' => 4]],
            ['kode' => 'D.3', 'bobot' => ['s1' => 5, 's2' => 5, 's3' => 5, 'd1' => 5, 'd2' => 5, 'd3' => 5, 'd4' => 5, 's2-terapan' => 5, 's3-terapan' => 5, 'profesi' => 5, 'spesialis-1' => 5, 'spesialis-2' => 5]],

            // Kriteria E - Edukasi, Sistem Evaluasi, dan Pembelajaran
            ['kode' => 'E.1', 'bobot' => ['s1' => 3, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'E.2', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'E.3', 'bobot' => ['s1' => 10, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'E.4', 'bobot' => ['s1' => 5, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'E.5', 'bobot' => ['s1' => 10, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],

            // Kriteria P - Pengembangan Sumber Daya
            ['kode' => 'P.1', 'bobot' => ['s1' => 5, 's2' => 5, 's3' => 5, 'd1' => 5, 'd2' => 5, 'd3' => 5, 'd4' => 5, 's2-terapan' => 5, 's3-terapan' => 5, 'profesi' => 5, 'spesialis-1' => 5, 'spesialis-2' => 5]],
            ['kode' => 'P.2', 'bobot' => ['s1' => 3, 's2' => 3, 's3' => 3, 'd1' => 3, 'd2' => 3, 'd3' => 3, 'd4' => 3, 's2-terapan' => 3, 's3-terapan' => 3, 'profesi' => 3, 'spesialis-1' => 3, 'spesialis-2' => 3]],
            ['kode' => 'P.3', 'bobot' => ['s1' => 5, 's2' => 5, 's3' => 4, 'd1' => 5, 'd2' => 5, 'd3' => 5, 'd4' => 5, 's2-terapan' => 5, 's3-terapan' => 5, 'profesi' => 5, 'spesialis-1' => 5, 'spesialis-2' => 5]],
            ['kode' => 'P.4', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],

            // Kriteria I - Internalisasi Penjaminan Mutu
            ['kode' => 'I.1', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'I.2', 'bobot' => ['s1' => 3, 's2' => 3, 's3' => 3, 'd1' => 3, 'd2' => 3, 'd3' => 3, 'd4' => 3, 's2-terapan' => 3, 's3-terapan' => 3, 'profesi' => 3, 'spesialis-1' => 3, 'spesialis-2' => 3]],
            ['kode' => 'I.3', 'bobot' => ['s1' => 5, 's2' => 5, 's3' => 5, 'd1' => 5, 'd2' => 5, 'd3' => 5, 'd4' => 5, 's2-terapan' => 5, 's3-terapan' => 5, 'profesi' => 5, 'spesialis-1' => 5, 'spesialis-2' => 5]],

            // Kriteria L - Lingkungan dan Sumber Belajar
            ['kode' => 'L.1', 'bobot' => ['s1' => 5, 's2' => 5, 's3' => 5, 'd1' => 10, 'd2' => 10, 'd3' => 10, 'd4' => 10,  's2-terapan' => 10, 's3-terapan' => 10, 'profesi' => 10, 'spesialis-1' => 10, 'spesialis-2' => 10]],
            ['kode' => 'L.2', 'bobot' => ['s1' => 4, 's2' => 4, 's3' => 4, 'd1' => 3, 'd2' => 3, 'd3' => 3, 'd4' => 3, 's2-terapan' => 3, 's3-terapan' => 3, 'profesi' => 3, 'spesialis-1' => 3, 'spesialis-2' => 3]],
            ['kode' => 'L.3', 'bobot' => ['s1' => 3, 's2' => 3, 's3' => 2, 'd1' => 3, 'd2' => 3, 'd3' => 3, 'd4' => 3, 's2-terapan' => 3, 's3-terapan' => 3, 'profesi' => 3, 'spesialis-1' => 3, 'spesialis-2' => 3]],
            ['kode' => 'L.4', 'bobot' => ['s1' => 3, 's2' => 3, 's3' => 2, 'd1' => 5, 'd2' => 5, 'd3' => 5, 'd4' => 5, 's2-terapan' => 5, 's3-terapan' => 5, 'profesi' => 5, 'spesialis-1' => 5, 'spesialis-2' => 5]],

            // Kriteria A - Akuntabilitas dan Tata Kelola
            ['kode' => 'A.1', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'A.2', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 5, 'd2' => 5, 'd3' => 5, 'd4' => 5, 's2-terapan' => 5, 's3-terapan' => 5, 'profesi' => 5, 'spesialis-1' => 5, 'spesialis-2' => 5]],
            ['kode' => 'A.3', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'A.4', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'A.5', 'bobot' => ['s1' => 2, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],

            // Kriteria R - Riset, Pengabdian, dan Inovasi
            ['kode' => 'R.1', 'bobot' => ['s1' => 1, 's2' => 3, 's3' => 3, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'R.2', 'bobot' => ['s1' => 1, 's2' => 6, 's3' => 5, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'R.3', 'bobot' => ['s1' => 3, 's2' => 10, 's3' => 15, 'd1' => 5, 'd2' => 5, 'd3' => 5, 'd4' => 5, 's2-terapan' => 7, 's3-terapan' => 7, 'profesi' => 5, 'spesialis-1' => 5, 'spesialis-2' => 5]],
            ['kode' => 'R.4', 'bobot' => ['s1' => 1, 's2' => 2, 's3' => 2, 'd1' => 2, 'd2' => 2, 'd3' => 2, 'd4' => 2, 's2-terapan' => 2, 's3-terapan' => 2, 'profesi' => 2, 'spesialis-1' => 2, 'spesialis-2' => 2]],
            ['kode' => 'R.5', 'bobot' => ['s1' => 1, 's2' => 3, 's3' => 3, 'd1' => 3, 'd2' => 3, 'd3' => 3, 'd4' => 3, 's2-terapan' => 3, 's3-terapan' => 3, 'profesi' => 3, 'spesialis-1' => 3, 'spesialis-2' => 2]],
            ['kode' => 'R.6', 'bobot' => ['s1' => 3, 's2' => 6, 's3' => 5, 'd1' => 7, 'd2' => 7, 'd3' => 7, 'd4' => 7, 's2-terapan' => 5, 's3-terapan' => 5, 'profesi' => 7, 'spesialis-1' => 7, 'spesialis-2' => 7]],
        ];

        $inserted = 0;
        $skipped  = 0;
        $degreeLevels = DegreeLevel::all()->keyBy('code');
        foreach ($bobotData as $data) {
            $elemen = ElemenStandar::where('kode_elemen', $data['kode'])->first();

            if (!$elemen) {
                $this->command->warn("Elemen {$data['kode']} tidak ditemukan, skip.");
                $skipped++;
                continue;
            }

            foreach ($data['bobot'] as $degreeCode => $bobot) {
                if (!isset($degreeLevels[$degreeCode])) {
                    $this->command->warn("Degree level {$degreeCode} tidak ditemukan.");
                    continue;
                }

                $level = $degreeLevels[$degreeCode];

                BobotPenilaian::updateOrCreate(
                    [
                        'id_elemen'       => $elemen->id,
                        'id_category'     => $level->id_category,
                        'id_degree_level' => $level->id,
                    ],
                    [
                        'bobot'     => $bobot,
                        'is_active' => true,
                    ]
                );

                $inserted++;
            }
        }

        $this->command->info("Bobot Penilaian seeded successfully!");
        $this->command->info("Total inserted/updated: {$inserted}");
        $this->command->info("Total skipped: {$skipped}");
    }
}
