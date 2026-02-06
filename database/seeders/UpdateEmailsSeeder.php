<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateEmailsSeeder extends Seeder
{
    /**
     * Run the database seeds - Update emails using hardcoded ID sequence
     */
    public function run(): void
    {
        $this->updateUniversityEmails();
        $this->updateStudyProgramEmails();
    }

    private function updateUniversityEmails(): void
    {
        $csvPath = database_path('seeders/data/DATA_UNIV_EMAIL_LENGKAP.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("File not found: {$csvPath}");
            return;
        }
        
        $handle = fopen($csvPath, 'r');
        fgetcsv($handle); // Skip header
        
        $updated = 0;
        $id = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $email = trim($row[2] ?? '');
            
            // If empty, use "-"
            if (empty($email) || $email === '-') {
                $email = '-';
            }
            
            DB::table('universities')
                ->where('id', $id)
                ->update(['email' => $email]);
            
            $this->command->info("✓ ID {$id}: {$email}");
            $updated++;
            
            $id++;
        }
        
        fclose($handle);
        
        $this->command->newLine();
        $this->command->info("=== University Emails Summary ===");
        $this->command->info("Updated: {$updated} universities");
    }

    private function updateStudyProgramEmails(): void
    {
        $csvPath = database_path('seeders/data/DATA_PRODI_EMAIL_LENGKAP.csv');
        
        if (!file_exists($csvPath)) {
            $this->command->error("File not found: {$csvPath}");
            return;
        }
        
        $handle = fopen($csvPath, 'r');
        fgetcsv($handle); // Skip header
        
        $updated = 0;
        $id = 1;
        
        while (($row = fgetcsv($handle)) !== false) {
            $email = trim($row[3] ?? '');
            
            // If empty or kosong, use "-"
            if (empty($email) || $email === '-' || strtoupper($email) === 'KOSONG') {
                $email = '-';
            }
            
            DB::table('study_programs')
                ->where('id', $id)
                ->update(['email' => $email]);
            
            $this->command->info("✓ ID {$id}: {$email}");
            $updated++;
            
            $id++;
        }
        
        fclose($handle);
        
        $this->command->newLine();
        $this->command->info("=== Study Program Emails Summary ===");
        $this->command->info("Updated: {$updated} programs");
    }
}
