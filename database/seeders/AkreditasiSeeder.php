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
        $created = 0;
        $errors = [];

        while (($data = fgetcsv($file)) !== false) {
            try {
                $universitas = trim($data[0] ?? '');
                $programStudi = trim($data[1] ?? '');
                $jenjang = trim($data[2] ?? '');
                $email = trim($data[6] ?? '');
                $peringkatAkreditasi = trim($data[7] ?? '');
                $tanggalKedaluwarsa = trim($data[8] ?? '');
                $statusKedaluwarsa = trim($data[9] ?? '');

                // Use data as-is from CSV
                $peringkat = !empty($peringkatAkreditasi) && $peringkatAkreditasi !== '-' ? $peringkatAkreditasi : null;

                // Parse tanggal kedaluwarsa
                $tanggal = $this->parseTanggal($tanggalKedaluwarsa);

                // Use status as-is from CSV
                $status = !empty($statusKedaluwarsa) && $statusKedaluwarsa !== '-' ? $statusKedaluwarsa : null;
                // Konversi status dari CSV ke enum database
                if ($status === 'Masih Berlaku') {
                    $status = 'Aktif';
                } elseif (strpos($status, 'kedaluwarsa') !== false || strpos($status, 'kedaluwarsa') !== false || strpos($status, 'hari lagi') !== false) {
                    $status = 'Kedaluwarsa';
                } elseif ($status === 'Tidak Ada Data' || $status === '' || $status === '-') {
                    $status = 'Belum Terakreditasi';
                }

                // Map university name variations to exact database names
                $universitasOriginal = $universitas;
                $universitas = $this->mapUniversityName($universitas);

                // Map jenjang from CSV to DegreeLevel name
                $jenjangMapped = $this->mapJenjang($jenjang);

                // Find or create university
                $university = \App\Models\University::where('name', $universitas)->first();
                if (!$university) {
                    // Create university with auto-generated code
                    $code = $this->generateUniversityCode($universitas);
                    $university = \App\Models\University::create([
                        'code' => $code,
                        'name' => $universitas,
                    ]);
                    // $this->command->info("Created university: {$universitas} ({$code})");
                }

                // Find degree level
                $degreeLevel = \App\Models\DegreeLevel::where('code', $jenjangMapped)->first();
                if (!$degreeLevel) {
                    $errors[] = "Jenjang tidak ditemukan: {$jenjang} ({$jenjangMapped}) untuk {$programStudi}";
                    $notFound++;
                    continue;
                }

                // Find or create study program
                $studyProgram = StudyProgram::where('id_university', $university->id)
                    ->where('id_degree_level', $degreeLevel->id)
                    ->where('name', $programStudi)
                    ->first();

                if (!$studyProgram) {
                    // Create study program
                    $code = $this->generateProgramCode($university->code, $programStudi);
                    $studyProgram = StudyProgram::create([
                        'code' => $code,
                        'name' => $programStudi,
                        'full_name' => $degreeLevel->alias . ' - ' . $programStudi . ' ' . $university->name,
                        'id_university' => $university->id,
                        'id_degree_level' => $degreeLevel->id,
                        'email' => $email,
                        'peringkat_akreditasi' => $peringkat,
                        'tanggal_kedaluwarsa' => $tanggal,
                        'status_kedaluwarsa' => $status,
                    ]);
                    $created++;
                    // $this->command->info("Created program: {$programStudi} ({$jenjang}) - {$universitas}");
                } else {
                    // Update existing study program
                    $studyProgram->update([
                        'peringkat_akreditasi' => $peringkat,
                        'tanggal_kedaluwarsa' => $tanggal,
                        'status_kedaluwarsa' => $status,
                    ]);
                    $updated++;
                }
            } catch (\Exception $e) {
                $this->command->error("Error processing row: " . $e->getMessage());
            }
        }

        fclose($file);

        $this->command->info("Selesai!");
        $this->command->info("Updated: {$updated}");
        $this->command->info("Created: {$created}");
        $this->command->info("Not Found: {$notFound}");

        if (!empty($errors) && count($errors) <= 50) {
            $this->command->warn("\nBeberapa program studi tidak ditemukan:");
            foreach ($errors as $error) {
                $this->command->line($error);
            }
        } elseif (count($errors) > 50) {
            $this->command->warn("\n{$notFound} program studi tidak ditemukan (terlalu banyak untuk ditampilkan)");
        }
    }

    /**
     * Parse tanggal kedaluwarsa from various formats
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

    /**
     * Map jenjang from CSV format to DegreeLevel name in database
     */
    private function mapJenjang($jenjang): string
    {
        $mappings = [
            'S1' => 's1',
            'S2' => 's2',
            'S3' => 's3',
            'D-I' => 'd1',
            'D-II' => 'd2',
            'D-III' => 'd3',
            'D-IV' => 'd4',
            'D1' => 'd1',
            'D2' => 'd2',
            'D3' => 'd3',
            'D4' => 'd4',
            'STr' => 'd4',
            'S2 Terapan' => 's2-terapan',
            'S3 Terapan' => 's3-terapan',
        ];

        return $mappings[$jenjang] ?? $jenjang;
    }

    /**
     * Generate university code from name
     */
    private function generateUniversityCode($name): string
    {
        // Take first letters of each word, max 10 chars
        $words = explode(' ', $name);
        $code = '';
        foreach ($words as $word) {
            if (strlen($code) >= 10) break;
            $code .= strtoupper(substr($word, 0, 1));
        }
        return $code ?: strtoupper(substr($name, 0, 10));
    }

    /**
     * Generate program code from university code and program name
     */
    private function generateProgramCode($univCode, $programName): string
    {
        return $univCode;
    }
}
