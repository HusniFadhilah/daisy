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
        $emailCsvPath = database_path('seeders/data/DATA_PRODI_EMAIL_LENGKAP.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan: {$csvPath}");
            return;
        }

        $this->command->info("Menghapus data lama...");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('study_programs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info("Membaca file CSV akreditasi...");
        
        // Baca file email untuk mapping
        $emailMapping = [];
        if (file_exists($emailCsvPath)) {
            $this->command->info("Membaca file email...");
            if (($emailHandle = fopen($emailCsvPath, 'r')) !== false) {
                $emailHeader = fgetcsv($emailHandle, 0, ',');
                while (($emailRow = fgetcsv($emailHandle, 0, ',')) !== false) {
                    if (count($emailRow) >= 4) {
                        $emailData = array_combine($emailHeader, $emailRow);
                        $key = trim($emailData['Universitas']) . '|' . trim($emailData['Program Studi']) . '|' . trim($emailData['Jenjang']);
                        $emailValue = trim($emailData['Email'] ?? '');
                        $emailMapping[$key] = (!empty($emailValue)) ? $emailValue : '-';
                    }
                }
                fclose($emailHandle);
                $this->command->info("Email mapping loaded: " . count($emailMapping) . " entries");
            }
        }

        $timestamp = Carbon::now();

        // Ambil data universitas untuk mapping (gunakan name sebagai key)
        $universities = DB::table('universities')->pluck('id', 'name')->toArray();

        // Ambil data degree level untuk mapping (gunakan code sebagai key)
        $degreeLevels = DB::table('degree_levels')->pluck('id', 'alias')->toArray();

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

        // Buka dan baca file CSV akreditasi
        if (($handle = fopen($csvPath, 'r')) !== false) {
            // Baca header
            $header = fgetcsv($handle, 0, ',');

            $this->command->info("Header CSV: " . implode(', ', $header));

            // Proses setiap baris
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $processed++;

                if (count($row) < 3) {
                    $skipped++;
                    continue;
                }

                // Mapping kolom CSV
                $data = array_combine($header, $row);

                // Ambil data dari CSV
                $universityName = $data['Universitas'] ?? null;
                $degreeLevelCode = $data['Jenjang'] ?? null;
                $programName = $data['Program Studi'] ?? null;

                // Normalisasi nama universitas
                if ($universityName) {
                    $universityName = preg_replace('/\s*\([^)]*\)/', '', $universityName);
                    $universityName = str_replace("'", '', $universityName);
                    $universityName = str_replace('AIi', 'Ali', $universityName);
                    $universityName = trim($universityName);
                }

                // Normalisasi jenjang D-III/D-IV menjadi D3/D4
                if ($degreeLevelCode === 'D-III') {
                    $degreeLevelCode = 'D3';
                } elseif ($degreeLevelCode === 'D-IV') {
                    $degreeLevelCode = 'D4';
                }

                $programCode = null;

                // Deteksi bentuk_pt
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

                // Konversi status
                if ($statusKedaluwarsa === 'Masih Berlaku') {
                    $statusKedaluwarsa = 'Aktif';
                } elseif (strpos($statusKedaluwarsa, 'kadaluarsa') !== false || strpos($statusKedaluwarsa, 'kedaluwarsa') !== false || strpos($statusKedaluwarsa, 'hari lagi') !== false) {
                    $statusKedaluwarsa = 'Kedaluwarsa';
                }

                // Parse tanggal
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
                
                // Ambil email dari mapping
                $emailKey = $universityName . '|' . $programName . '|' . $degreeLevelCode;
                $email = $emailMapping[$emailKey] ?? '-';

                // Cari ID
                $universityId = $universities[$universityName] ?? null;
                $degreeLevelId = $degreeLevels[$degreeLevelCode] ?? null;
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
                    'id_university' => $universityId,
                    'id_degree_level' => $degreeLevelId,
                    'category_id' => $categoryId,
                    'bentuk_pt' => $bentukPT,
                    'email' => $email,
                    'peringkat_akreditasi' => $peringkatAkreditasi,
                    'tanggal_kedaluwarsa' => $tanggalKedaluwarsa,
                    'status_kedaluwarsa' => $statusKedaluwarsa,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                // Insert batch
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
