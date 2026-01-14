<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\University;
use Illuminate\Support\Str;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StudyProgramUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin prodi users
        $adminProdi1 = User::where('email', 'upps1@daisy.lamdepilar.or.id')->first();
        $adminProdi2 = User::where('email', 'upps2@daisy.lamdepilar.or.id')->first();

        $universities = [
            ['code' => 'EX-UNI-001', 'name' => 'Universitas LAMDEPILAR', 'is_active' => true, 'is_example' => true],
            ['code' => 'EX-UNI-002', 'name' => 'Universitas Contoh ABCD', 'is_active' => true, 'is_example' => true],
        ];

        foreach ($universities as $data) {
            $university = University::create($data);
            $fileName = $university->id . '_' . Str::slug($university->name) . '.png';
            Storage::disk('public')->putFileAs('universities/logo', public_path('assets/images/logo.png'), $fileName);
            $university->update(['logo_path' => 'universities/logo/' . $fileName]);
        }

        $u1 = University::withoutGlobalScopes()->where('code', 'EX-UNI-001')->first();
        $u2 = University::withoutGlobalScopes()->where('code', 'EX-UNI-002')->first();

        $studyPrograms = [
            // =======================
            // Universitas LAMDEPILAR
            // =======================
            [
                'name'             => 'LAMDEPILAR (Contoh)',
                'full_name'        => 'Program Studi LAMDEPILAR (Contoh)',
                'code'             => 'LD-EX-001',
                'id_university' => $u1->id,
                'id_degree_level'  => 5, // S1
                'category_id'      => 1,
                'bentuk_pt'        => 'Universitas',
                'email'            => 'lamdepilar@contoh.ac.id',
                'peringkat_akreditasi' => 'A',
                'tanggal_kedaluwarsa'  => now()->addYears(5),
                'status_kedaluwarsa'   => 'Aktif',
                'is_active'        => true,
                'is_example'       => true,
            ],
            [
                'name'             => 'ABCDE (Contoh)',
                'full_name'        => 'Program Studi ABCDE (Contoh)',
                'code'             => 'SI-EX-001',
                'id_university' => $u1->id,
                'id_degree_level'  => 5,
                'category_id'      => 1,
                'bentuk_pt'        => 'Universitas',
                'email'            => 'abcde@contoh.ac.id',
                'peringkat_akreditasi' => 'B',
                'tanggal_kedaluwarsa'  => now()->addYears(4),
                'status_kedaluwarsa'   => 'Aktif',
                'is_active'        => true,
                'is_example'       => true,
            ],
            [
                // ❌ prodi NON-AKTIF
                'name'             => 'Manajemen',
                'full_name'        => 'Program Studi Manajemen',
                'code'             => 'MNJ-EX-001',
                'id_university' => $u1->id,
                'id_degree_level'  => 5,
                'category_id'      => 2,
                'bentuk_pt'        => 'Universitas',
                'email'            => 'manajemen@contoh.ac.id',
                'peringkat_akreditasi' => null,
                'tanggal_kedaluwarsa'  => null,
                'status_kedaluwarsa'   => 'Belum Terakreditasi',
                'is_active'        => false,
                'is_example'       => true,
            ],

            // =======================
            // Universitas Contoh ABCD
            // =======================
            [
                'name'             => 'DEPILAR (Contoh)',
                'full_name'        => 'Program Studi DEPILAR (Contoh)',
                'code'             => 'DP-EX-002',
                'id_university' => $u2->id,
                'id_degree_level'  => 5,
                'category_id'      => 2,
                'bentuk_pt'        => 'Universitas',
                'email'            => 'depilar@abcd.ac.id',
                'peringkat_akreditasi' => 'A',
                'tanggal_kedaluwarsa'  => now()->addYears(5),
                'status_kedaluwarsa'   => 'Aktif',
                'is_active'        => true,
                'is_example'       => true,
            ],
            [
                'name'             => 'Testing (Contoh)',
                'full_name'        => 'Program Studi Testing (Contoh)',
                'code'             => 'TEST-EX-002',
                'id_university' => $u2->id,
                'id_degree_level'  => 5,
                'category_id'      => 3,
                'bentuk_pt'        => 'Universitas',
                'email'            => 'testing@abcd.ac.id',
                'peringkat_akreditasi' => 'B',
                'tanggal_kedaluwarsa'  => now(),
                'status_kedaluwarsa'   => 'Kedaluwarsa',
                'is_active'        => true,
                'is_example'       => true,
            ],
        ];

        foreach ($studyPrograms as $program) {
            StudyProgram::create($program);
        }

        // Get study programs
        $prodi1 = StudyProgram::where('email', 'lamdepilar@contoh.ac.id')->first();
        $prodi2 = StudyProgram::where('email', 'testing@abcd.ac.id')->first();

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
