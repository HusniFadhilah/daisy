<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BobotPenilaian;
use App\Models\ElemenStandar;
use App\Models\StudyProgramCategory;

class BobotPenilaianSeederNew extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil categories dengan mapping
        $categoryMapping = [
            'AK' => 'Akademik',           // Sarjana (S1)
            'MAG' => 'Magister',          // Magister (S2)
            'DOK' => 'Doktor',            // Doktor (S3)
            'VOK' => 'Vokasi',            // Diploma II/III/IV, Sarjana Terapan
            'MT' => 'Magister Terapan',   // Magister Terapan, Doktor Terapan
            'PRO' => 'Profesi',           // Profesi, Spesialis 1, Spesialis 2
        ];

        // Data bobot lengkap sesuai tabel standar LAMEMBA (6 kategori)
        $bobotData = [
            // Kriteria D - Diferensiasi Misi
            ['kode' => 'D1', 'bobot' => ['AK' => 1, 'MAG' => 1, 'DOK' => 1, 'VOK' => 1, 'MT' => 1, 'PRO' => 1]],
            ['kode' => 'D2', 'bobot' => ['AK' => 4, 'MAG' => 4, 'DOK' => 4, 'VOK' => 4, 'MT' => 4, 'PRO' => 4]],
            ['kode' => 'D3', 'bobot' => ['AK' => 5, 'MAG' => 5, 'DOK' => 5, 'VOK' => 5, 'MT' => 5, 'PRO' => 5]],
            
            // Kriteria E - Edukasi, Sistem Evaluasi, dan Pembelajaran
            ['kode' => 'E1', 'bobot' => ['AK' => 3, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'E2', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'E3', 'bobot' => ['AK' => 10, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'E4', 'bobot' => ['AK' => 5, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'E5', 'bobot' => ['AK' => 10, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            
            // Kriteria P - Pengembangan Sumber Daya
            ['kode' => 'P1', 'bobot' => ['AK' => 5, 'MAG' => 5, 'DOK' => 5, 'VOK' => 5, 'MT' => 5, 'PRO' => 5]],
            ['kode' => 'P2', 'bobot' => ['AK' => 3, 'MAG' => 3, 'DOK' => 3, 'VOK' => 3, 'MT' => 3, 'PRO' => 3]],
            ['kode' => 'P3', 'bobot' => ['AK' => 5, 'MAG' => 5, 'DOK' => 4, 'VOK' => 5, 'MT' => 5, 'PRO' => 5]],
            ['kode' => 'P4', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            
            // Kriteria I - Internalisasi Penjaminan Mutu
            ['kode' => 'I1', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'I2', 'bobot' => ['AK' => 3, 'MAG' => 3, 'DOK' => 3, 'VOK' => 3, 'MT' => 3, 'PRO' => 3]],
            ['kode' => 'I3', 'bobot' => ['AK' => 5, 'MAG' => 5, 'DOK' => 5, 'VOK' => 5, 'MT' => 5, 'PRO' => 5]],
            
            // Kriteria L - Lingkungan dan Sumber Belajar
            ['kode' => 'L1', 'bobot' => ['AK' => 5, 'MAG' => 5, 'DOK' => 5, 'VOK' => 10, 'MT' => 10, 'PRO' => 10]],
            ['kode' => 'L2', 'bobot' => ['AK' => 4, 'MAG' => 4, 'DOK' => 4, 'VOK' => 3, 'MT' => 3, 'PRO' => 3]],
            ['kode' => 'L3', 'bobot' => ['AK' => 3, 'MAG' => 3, 'DOK' => 2, 'VOK' => 3, 'MT' => 3, 'PRO' => 3]],
            ['kode' => 'L4', 'bobot' => ['AK' => 3, 'MAG' => 3, 'DOK' => 2, 'VOK' => 5, 'MT' => 5, 'PRO' => 5]],
            
            // Kriteria A - Akuntabilitas dan Tata Kelola
            ['kode' => 'A1', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'A2', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 5, 'MT' => 5, 'PRO' => 5]],
            ['kode' => 'A3', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'A4', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'A5', 'bobot' => ['AK' => 2, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            
            // Kriteria R - Riset, Pengabdian, dan Inovasi
            ['kode' => 'R1', 'bobot' => ['AK' => 1, 'MAG' => 3, 'DOK' => 3, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'R2', 'bobot' => ['AK' => 1, 'MAG' => 6, 'DOK' => 5, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'R3', 'bobot' => ['AK' => 3, 'MAG' => 10, 'DOK' => 15, 'VOK' => 5, 'MT' => 7, 'PRO' => 5]],
            ['kode' => 'R4', 'bobot' => ['AK' => 1, 'MAG' => 2, 'DOK' => 2, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'R5', 'bobot' => ['AK' => 1, 'MAG' => 3, 'DOK' => 3, 'VOK' => 2, 'MT' => 2, 'PRO' => 2]],
            ['kode' => 'R6', 'bobot' => ['AK' => 3, 'MAG' => 6, 'DOK' => 5, 'VOK' => 5, 'MT' => 5, 'PRO' => 7]],
        ];

        $inserted = 0;
        $skipped = 0;

        foreach ($bobotData as $data) {
            // Konversi kode D1 -> D.1 untuk match dengan database
            $kodeElemen = preg_replace('/^([A-Z])(\d)$/', '$1.$2', $data['kode']);
            
            $elemen = ElemenStandar::where('kode_elemen', $kodeElemen)->first();
            
            if (!$elemen) {
                $this->command->warn("Elemen {$kodeElemen} tidak ditemukan, skip...");
                $skipped++;
                continue;
            }

            foreach ($categoryMapping as $categoryCode => $categoryName) {
                $category = StudyProgramCategory::where('code', $categoryCode)->first();
                
                if ($category && isset($data['bobot'][$categoryCode])) {
                    BobotPenilaian::updateOrCreate(
                        [
                            'id_elemen' => $elemen->id,
                            'id_category' => $category->id,
                        ],
                        [
                            'bobot' => $data['bobot'][$categoryCode],
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
