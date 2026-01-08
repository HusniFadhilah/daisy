<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BobotPenilaian;
use App\Models\ElemenStandar;
use App\Models\StudyProgramCategory;

class BobotPenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil degree levels dengan mapping yang jelas
        $levelMapping = [
            'Sarjana (Strata 1)' => 'S1',
            'Magister (Strata 2)' => 'S2',
            'Doktor (Strata 3)' => 'S3',
            'Diploma II' => 'D2',
            'Diploma III' => 'D3',
            'Diploma IV' => 'D4',
            'Sarjana Terapan' => 'ST',
            'Magister Terapan' => 'MT',
            'Doktor Terapan' => 'DT',
            'Profesi' => 'PR',
            'Spesialis 1' => 'SP1',
            'Spesialis 2' => 'SP2',
        ];

        // Data bobot lengkap sesuai tabel standar LAMEMBA
        // Format: [kode_elemen, bobot untuk setiap jenjang]
        $bobotData = [
            // Kriteria D - Diferensiasi Misi
            ['kode' => 'D1', 'bobot' => ['S1' => 1, 'S2' => 1, 'S3' => 1, 'D2' => 1, 'D3' => 1, 'D4' => 1, 'ST' => 1, 'MT' => 1, 'DT' => 1, 'PR' => 1, 'SP1' => 1, 'SP2' => 1]],
            ['kode' => 'D2', 'bobot' => ['S1' => 4, 'S2' => 4, 'S3' => 4, 'D2' => 4, 'D3' => 4, 'D4' => 4, 'ST' => 4, 'MT' => 4, 'DT' => 4, 'PR' => 4, 'SP1' => 4, 'SP2' => 4]],
            ['kode' => 'D3', 'bobot' => ['S1' => 5, 'S2' => 5, 'S3' => 5, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 5, 'DT' => 5, 'PR' => 5, 'SP1' => 5, 'SP2' => 5]],

            // Kriteria E - Edukasi, Sistem Evaluasi, dan Pembelajaran
            ['kode' => 'E1', 'bobot' => ['S1' => 3, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'E2', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'E3', 'bobot' => ['S1' => 10, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'E4', 'bobot' => ['S1' => 5, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'E5', 'bobot' => ['S1' => 10, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],

            // Kriteria P - Pengembangan Sumber Daya
            ['kode' => 'P1', 'bobot' => ['S1' => 5, 'S2' => 5, 'S3' => 5, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 5, 'DT' => 5, 'PR' => 5, 'SP1' => 5, 'SP2' => 5]],
            ['kode' => 'P2', 'bobot' => ['S1' => 3, 'S2' => 3, 'S3' => 3, 'D2' => 3, 'D3' => 3, 'D4' => 3, 'ST' => 3, 'MT' => 3, 'DT' => 3, 'PR' => 3, 'SP1' => 3, 'SP2' => 3]],
            ['kode' => 'P3', 'bobot' => ['S1' => 5, 'S2' => 5, 'S3' => 4, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 5, 'DT' => 5, 'PR' => 5, 'SP1' => 5, 'SP2' => 5]],
            ['kode' => 'P4', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],

            // Kriteria I - Internalisasi Penjaminan Mutu
            ['kode' => 'I1', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'I2', 'bobot' => ['S1' => 3, 'S2' => 3, 'S3' => 3, 'D2' => 3, 'D3' => 3, 'D4' => 3, 'ST' => 3, 'MT' => 3, 'DT' => 3, 'PR' => 3, 'SP1' => 3, 'SP2' => 3]],
            ['kode' => 'I3', 'bobot' => ['S1' => 5, 'S2' => 5, 'S3' => 5, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 5, 'DT' => 5, 'PR' => 5, 'SP1' => 5, 'SP2' => 5]],

            // Kriteria L - Lingkungan dan Sumber Belajar
            ['kode' => 'L1', 'bobot' => ['S1' => 5, 'S2' => 5, 'S3' => 5, 'D2' => 10, 'D3' => 10, 'D4' => 10, 'ST' => 10, 'MT' => 10, 'DT' => 10, 'PR' => 10, 'SP1' => 10, 'SP2' => 10]],
            ['kode' => 'L2', 'bobot' => ['S1' => 4, 'S2' => 4, 'S3' => 4, 'D2' => 3, 'D3' => 3, 'D4' => 3, 'ST' => 3, 'MT' => 3, 'DT' => 3, 'PR' => 3, 'SP1' => 3, 'SP2' => 3]],
            ['kode' => 'L3', 'bobot' => ['S1' => 3, 'S2' => 3, 'S3' => 2, 'D2' => 3, 'D3' => 3, 'D4' => 3, 'ST' => 3, 'MT' => 3, 'DT' => 3, 'PR' => 3, 'SP1' => 3, 'SP2' => 3]],
            ['kode' => 'L4', 'bobot' => ['S1' => 3, 'S2' => 3, 'S3' => 2, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 5, 'DT' => 5, 'PR' => 5, 'SP1' => 5, 'SP2' => 5]],

            // Kriteria A - Akuntabilitas dan Tata Kelola
            ['kode' => 'A1', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'A2', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 5, 'DT' => 5, 'PR' => 5, 'SP1' => 5, 'SP2' => 5]],
            ['kode' => 'A3', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'A4', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'A5', 'bobot' => ['S1' => 2, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],

            // Kriteria R - Riset, Pengabdian, dan Inovasi
            ['kode' => 'R1', 'bobot' => ['S1' => 1, 'S2' => 3, 'S3' => 3, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'R2', 'bobot' => ['S1' => 1, 'S2' => 6, 'S3' => 5, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'R3', 'bobot' => ['S1' => 3, 'S2' => 10, 'S3' => 15, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 7, 'DT' => 7, 'PR' => 5, 'SP1' => 5, 'SP2' => 5]],
            ['kode' => 'R4', 'bobot' => ['S1' => 1, 'S2' => 2, 'S3' => 2, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'R5', 'bobot' => ['S1' => 1, 'S2' => 3, 'S3' => 3, 'D2' => 2, 'D3' => 2, 'D4' => 2, 'ST' => 2, 'MT' => 2, 'DT' => 2, 'PR' => 2, 'SP1' => 2, 'SP2' => 2]],
            ['kode' => 'R6', 'bobot' => ['S1' => 3, 'S2' => 6, 'S3' => 5, 'D2' => 5, 'D3' => 5, 'D4' => 5, 'ST' => 5, 'MT' => 5, 'DT' => 5, 'PR' => 7, 'SP1' => 7, 'SP2' => 7]],
        ];

        $inserted = 0;
        $skipped = 0;

        foreach ($bobotData as $data) {
            $elemen = ElemenStandar::where('kode_elemen', $data['kode'])->first();

            if (!$elemen) {
                $this->command->warn("Elemen {$data['kode']} tidak ditemukan, skip...");
                $skipped++;
                continue;
            }

            foreach ($levelMapping as $levelName => $levelCode) {
                $level = DegreeLevel::where('name', $levelName)->first();

                if ($level && isset($data['bobot'][$levelCode])) {
                    BobotPenilaian::updateOrCreate(
                        [
                            'id_elemen' => $elemen->id_elemen,
                            'id_degree_level' => $level->id,
                        ],
                        [
                            'bobot' => $data['bobot'][$levelCode],
                        ]
                    );
                    $inserted++;
                }
            }
        }

        $this->command->info("Bobot Penilaian seeded successfully!");
        $this->command->info("Total inserted/updated: {$inserted}");
        $this->command->info("Total skipped: {$skipped}");
    }
}
