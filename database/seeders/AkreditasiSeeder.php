<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AkreditasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/data/data_akreditasi_lengkap.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan: {$csvFile}");
            return;
        }

        $file = fopen($csvFile, 'r');
        
        // Skip header row
        fgetcsv($file);
        
        $updated = 0;
        $notFound = 0;
        $errors = [];
        
        while (($data = fgetcsv($file)) !== false) {
            try {
                $universitas = $data[0];
                $programStudi = $data[1];
                $jenjang = $data[2];
                $email = $data[6];
                $peringkatAkreditasi = $data[7];
                $tanggalKadaluarsa = $data[8];
                $statusKadaluarsa = $data[9];
                
                // Use data as-is from CSV
                $peringkat = !empty($peringkatAkreditasi) && $peringkatAkreditasi !== '-' ? $peringkatAkreditasi : null;
                
                // Parse tanggal kadaluarsa
                $tanggal = $this->parseTanggal($tanggalKadaluarsa);
                
                // Use status as-is from CSV
                $status = !empty($statusKadaluarsa) && $statusKadaluarsa !== '-' ? $statusKadaluarsa : 'Belum Terakreditasi';
                
                // Map university name variations to exact database names
                $universitas = $this->mapUniversityName($universitas);
                
                // Find study program by name and university name
                $studyProgram = StudyProgram::whereHas('university', function($query) use ($universitas) {
                    $query->where('name', $universitas);
                })
                ->where('name', $programStudi)
                ->first();
                
                if ($studyProgram) {
                    $studyProgram->update([
                        'peringkat_akreditasi' => $peringkat,
                        'tanggal_kadaluarsa' => $tanggal,
                        'status_kadaluarsa' => $status,
                    ]);
                    $updated++;
                } else {
                    $notFound++;
                    $errors[] = "Program Studi tidak ditemukan: {$programStudi} - {$universitas}";
                }
                
            } catch (\Exception $e) {
                $this->command->error("Error processing row: " . $e->getMessage());
            }
        }
        
        fclose($file);
        
        $this->command->info("Selesai!");
        $this->command->info("Updated: {$updated}");
        $this->command->info("Not Found: {$notFound}");
        
        if (!empty($errors) && count($errors) <= 10) {
            $this->command->warn("\nBeberapa email tidak ditemukan:");
            foreach ($errors as $error) {
                $this->command->line($error);
            }
        } elseif (count($errors) > 10) {
            $this->command->warn("\n{$notFound} email tidak ditemukan (terlalu banyak untuk ditampilkan)");
        }
    }
    
    /**
     * Parse tanggal kadaluarsa from various formats
     */
    private function parseTanggal($tanggal): ?string
    {
        if (empty($tanggal) || $tanggal === '-') {
            return null;
        }
        
        try {
            // Try to parse date in format Y-m-d
            $date = Carbon::createFromFormat('Y-m-d', $tanggal);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Map university name variations from CSV to exact database names
     */
    private function mapUniversityName($name): string
    {
        // Exact mappings for variations
        $mappings = [
            "Universitas Tunas Pembangunan Surakarta (UTP)" => "Universitas Tunas Pembangunan Surakarta",
            "Universitas Islam Negeri Syekh AIi Hasan Ahmad Addary Padangsidimpuan" => "Universitas Islam Negeri Syekh Ali Hasan Ahmad Addary Padangsidimpuan",
            "Universitas 'Aisyiyah Bandung" => "Universitas Aisyiyah Bandung",
            "Universitas Maritim Raja Ali Haji (UMRAH)" => "Universitas Maritim Raja Ali Haji",
        ];
        
        return $mappings[$name] ?? $name;
    }
}
