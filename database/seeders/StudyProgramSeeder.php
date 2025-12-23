<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/data/data_akreditasi_lengkap.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan: {$csvPath}");
            return;
        }

        $this->command->info("Membaca file CSV...");

        $timestamp = Carbon::now();

        // Ambil data universitas untuk mapping (gunakan name sebagai key)
        $universities = DB::table('universities')->pluck('id', 'name')->toArray();

        // Ambil data degree level untuk mapping (gunakan code sebagai key)
        $degreeLevels = DB::table('degree_levels')->pluck('id', 'code')->toArray();

        // Ambil data category untuk mapping berdasarkan jenjang
        $categoryMapping = [
            'S1' => 'AK',
            'S2' => 'MAG',
            'S3' => 'DOK',
            'D3' => 'VOK',
            'D-III' => 'VOK',
            'D4' => 'VOK',
            'D-IV' => 'VOK',
            'S2 Terapan' => 'MT',
            'S3 Terapan' => 'MT',
            'Profesi' => 'PRO',
        ];
        $categories = DB::table('study_program_categories')->pluck('id', 'code')->toArray();

        $insertData = [];
        $skipped = 0;
        $processed = 0;

        // Buka dan baca file CSV
        if (($handle = fopen($csvPath, 'r')) !== false) {
            // Baca header
            $header = fgetcsv($handle, 0, ',');

            $this->command->info("Header CSV: " . implode(', ', $header));

            // Proses setiap baris
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $processed++;

                // Asumsikan format CSV: nama_prodi, kode_prodi, nama_universitas, jenjang, email
                // Sesuaikan index berdasarkan struktur CSV sebenarnya
                if (count($row) < 3) {
                    $skipped++;
                    continue;
                }

                // Mapping kolom CSV (sesuaikan dengan struktur CSV yang sebenarnya)
                $data = array_combine($header, $row);

                // Ambil data dari CSV
                $universityName = $data['Universitas'] ?? null;
                $degreeLevelCode = $data['Jenjang'] ?? null;

                // Normalisasi nama universitas (hapus tanda kurung dan isinya, fix typo)
                if ($universityName) {
                    $universityName = preg_replace('/\s*\([^)]*\)/', '', $universityName); // Hapus (UTP), (UMRAH), dll
                    $universityName = str_replace("'", '', $universityName); // Hapus tanda petik
                    $universityName = str_replace('AIi', 'Ali', $universityName); // Fix typo AIi -> Ali
                    $universityName = trim($universityName);
                }

                // Normalisasi jenjang D-III/D-IV menjadi D3/D4
                if ($degreeLevelCode === 'D-III') {
                    $degreeLevelCode = 'D3';
                } elseif ($degreeLevelCode === 'D-IV') {
                    $degreeLevelCode = 'D4';
                }

                $programName = $data['Program Studi'] ?? null;
                $programCode = null; // Tidak ada kode di CSV akreditasi
                $email = $data['email'] ?? null;

                // Deteksi bentuk_pt berdasarkan nama universitas
                $bentukPT = null;
                if ($universityName) {
                    if (strpos($universityName, 'Universitas') === 0) {
                        $bentukPT = 'Universitas';
                    } elseif (strpos($universityName, 'Institut') === 0) {
                        $bentukPT = 'Institut';
                    } elseif (strpos($universityName, 'Sekolah Tinggi') === 0) {
                        $bentukPT = 'Sekolah Tinggi';
                    } elseif (strpos($universityName, 'Politeknik') === 0) {
                        $bentukPT = 'Politeknik';
                    } elseif (strpos($universityName, 'Akademi') === 0) {
                        $bentukPT = 'Akademi';
                    } elseif (
                        strpos($universityName, 'STMIK') === 0 ||
                        strpos($universityName, 'STIKI') === 0 ||
                        strpos($universityName, 'STKIP') === 0
                    ) {
                        $bentukPT = 'Sekolah Tinggi';
                    }
                }

                // Data akreditasi
                $peringkatAkreditasi = $data['Peringkat_Akreditasi'] ?? null;
                $tanggalKedaluwarsa = $data['Tanggal_Kedaluwarsa'] ?? null;
                $statusKedaluwarsa = $data['Status_Kedaluwarsa'] ?? 'Belum Terakreditasi';

                // Konversi status dari CSV ke enum database
                if ($statusKedaluwarsa === 'Masih Berlaku') {
                    $statusKedaluwarsa = 'Aktif';
                } elseif (strpos($statusKedaluwarsa, 'kedaluwarsa') !== false || strpos($statusKedaluwarsa, 'hari lagi') !== false) {
                    $statusKedaluwarsa = 'Kedaluwarsa';
                }

                // Parse tanggal kedaluwarsa
                if ($tanggalKedaluwarsa && $tanggalKedaluwarsa !== '-' && $tanggalKedaluwarsa !== '') {
                    try {
                        $tanggalKedaluwarsa = Carbon::createFromFormat('Y-m-d', $tanggalKedaluwarsa)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tanggalKedaluwarsa = null;
                    }
                } else {
                    $tanggalKedaluwarsa = null;
                }

                if (!$programName || !$universityName || !$degreeLevelCode) {
                    $skipped++;
                    continue;
                }

                // Cari ID universitas
                $universityId = $universities[$universityName] ?? null;

                // Cari ID degree level
                $degreeLevelId = $degreeLevels[$degreeLevelCode] ?? null;

                // Cari category ID berdasarkan jenjang
                $categoryCode = $categoryMapping[$degreeLevelCode] ?? 'AK';
                $categoryId = $categories[$categoryCode] ?? null;

                if (!$universityId || !$degreeLevelId) {
                    $skipped++;
                    if (!$universityId) {
                        $this->command->warn("Universitas tidak ditemukan: {$universityName}");
                    }
                    if (!$degreeLevelId) {
                        $this->command->warn("Jenjang tidak ditemukan: {$degreeLevelCode}");
                    }
                    continue;
                }

                $insertData[] = [
                    'name' => trim($programName),
                    'full_name' => $degreeLevelCode . ' - ' . trim($programName) . ' ' . $universityName,
                    'code' => trim($programCode ?: 'N/A'),
                    'id_univ' => $universityId,
                    'id_level' => $degreeLevelId,
                    'category_id' => $categoryId,
                    'bentuk_pt' => $bentukPT,
                    'email' => $email ? trim($email) : null,
                    'peringkat_akreditasi' => $peringkatAkreditasi,
                    'tanggal_kedaluwarsa' => $tanggalKedaluwarsa,
                    'status_kedaluwarsa' => $statusKedaluwarsa,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                // Insert dalam batch setiap 500 records untuk performa
                if (count($insertData) >= 500) {
                    DB::table('study_programs')->insert($insertData);
                    $this->command->info("Inserted " . count($insertData) . " records...");
                    $insertData = [];
                }
            }

            fclose($handle);

            // Insert sisa data
            if (!empty($insertData)) {
                DB::table('study_programs')->insert($insertData);
                $this->command->info("Inserted " . count($insertData) . " records...");
            }
        }

        $this->command->info("Selesai! Total diproses: {$processed}, Berhasil: " . ($processed - $skipped) . ", Dilewati: {$skipped}");
    }
}
