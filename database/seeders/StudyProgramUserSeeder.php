<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin prodi users
        $adminProdi1 = User::where('email', 'admin_prodi1@daisy.lamdepilar.or.id')->first();
        $adminProdi2 = User::where('email', 'admin_prodi2@daisy.lamdepilar.or.id')->first();

        // Get study programs
        $prodi1 = StudyProgram::where('email', 's1arsitektur@universitasdiponegoro.ac.id')->first();
        $prodi2 = StudyProgram::where('email', 's1arsitektur@universitasislamindonesia.ac.id')->first();

        if ($adminProdi1 && $prodi1) {
            // Admin Prodi 1 -> TI UGM & SI UGM
            $adminProdi1->studyPrograms()->attach($prodi1->id, [
                'role_in_prodi' => 'admin_prodi',
                'is_active' => true,
                'start_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($adminProdi2 && $prodi2) {
            // Admin Prodi 2 -> TK UGM & ILKOM UI
            $adminProdi2->studyPrograms()->attach($prodi2->id, [
                'role_in_prodi' => 'admin_prodi',
                'is_active' => true,
                'start_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Study program users assigned successfully!');
    }
}
