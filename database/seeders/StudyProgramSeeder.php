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
        $csvPath = database_path('seeders/data/dataProdi_dengan_email_COMPLETE.csv');
        
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
                
                // Cari universitas (coba berbagai kemungkinan nama kolom)
                $universityName = $data['Universitas'] ?? $data['universitas'] ?? $data['nama_universitas'] ?? $data['university'] ?? null;
                $degreeLevelCode = $data['Jenjang'] ?? $data['jenjang'] ?? $data['degree_level'] ?? $data['level'] ?? null;
                $programName = $data['Program Studi'] ?? $data['program_studi'] ?? $data['nama_prodi'] ?? $data['prodi'] ?? null;
                $programCode = $data['Singkatan Umum/Resmi'] ?? $data['kode_prodi'] ?? $data['kode'] ?? $data['code'] ?? '';
                $email = $data['Email'] ?? $data['email'] ?? null;
                
                if (!$programName || !$universityName || !$degreeLevelCode) {
                    $skipped++;
                    continue;
                }
                
                // Cari ID universitas
                $universityId = $universities[$universityName] ?? null;
                
                // Cari ID degree level
                $degreeLevelId = $degreeLevels[$degreeLevelCode] ?? null;
                
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
                    'code' => trim($programCode ?: 'N/A'),
                    'id_univ' => $universityId,
                    'id_level' => $degreeLevelId,
                    'email' => $email ? trim($email) : null,
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
