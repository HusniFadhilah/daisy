<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\University;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudyProgramUserSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * ======================================================
         * 1. UNIVERSITIES (3 SAJA, JANGAN DIHAPUS)
         * ======================================================
         */
        $universities = [
            'EX-UNI-001' => ['name' => 'Universitas LAMDEPILAR', 'logo' => 'logo.png'],
            'EX-UNI-002' => ['name' => 'Universitas Contoh ABCD', 'logo' => 'contoh.png'],
            'EX-UNI-003' => ['name' => 'Universitas Testing', 'logo' => 'testing.png'],
        ];

        foreach ($universities as $code => $data) {
            $university = University::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $data['name'],
                    'is_active' => true,
                    'is_example' => true,
                ]
            );

            // Update logo (tidak menghapus universitas)
            $fileName = $university->id . '_' . Str::slug($university->name) . '.png';

            Storage::disk('public')->putFileAs(
                'universities/logo',
                public_path('assets/images/' . $data['logo']),
                $fileName
            );

            $university->update([
                'logo_path' => 'universities/logo/' . $fileName
            ]);
        }

        // Ambil universitas
        $uLamdepilar = University::where('code', 'EX-UNI-001')->first();
        $uABCD       = University::where('code', 'EX-UNI-002')->first();
        $uTesting    = University::where('code', 'EX-UNI-003')->first();

        /**
         * ======================================================
         * 2. PRODI LAMA (WAJIB TETAP ADA — JANGAN DIHAPUS)
         * ======================================================
         */

        // S3 ABCDE (Contoh) — AKTIF
        StudyProgram::firstOrCreate(
            ['email' => 'abcde@contoh.ac.id'],
            [
                'name' => 'ABCDE (Contoh)',
                'full_name' => 'Program Studi S3 ABCDE (Contoh)',
                'code' => 'SI-EX-001',
                'id_university' => $uLamdepilar->id,
                'id_degree_level' => 8, // S3
                'category_id' => 1,
                'bentuk_pt' => 'Universitas',
                'peringkat_akreditasi' => 'B',
                'tanggal_kedaluwarsa' => now()->addYears(4),
                'status_kedaluwarsa' => 'Aktif',
                'is_active' => true,
                'is_example' => true,
            ]
        );

        // Manajemen — NON AKTIF (EDGE CASE TESTING)
        StudyProgram::firstOrCreate(
            ['email' => 'manajemen@contoh.ac.id'],
            [
                'name' => 'Manajemen',
                'full_name' => 'Program Studi Manajemen',
                'code' => 'MNJ-EX-001',
                'id_university' => $uLamdepilar->id,
                'id_degree_level' => 5, // S1
                'category_id' => 2,
                'bentuk_pt' => 'Universitas',
                'peringkat_akreditasi' => null,
                'tanggal_kedaluwarsa' => null,
                'status_kedaluwarsa' => 'Belum Terakreditasi',
                'is_active' => false, // ❗ sengaja NON-AKTIF
                'is_example' => true,
            ]
        );

        /**
         * ======================================================
         * 3. PRODI TAMBAHAN LAMDEPILAR (SEMUA JENJANG)
         * ======================================================
         */
        $lamdepilarProdis = [
            ['email' => 'd3@lamdepilar.ac.id', 'name' => 'LAMDEPILAR D3 (Contoh)', 'level' => 3],
            ['email' => 's1@lamdepilar.ac.id', 'name' => 'LAMDEPILAR (Contoh)', 'level' => 5],
            ['email' => 's2@lamdepilar.ac.id', 'name' => 'LAMDEPILAR S2 (Contoh)', 'level' => 6],
            ['email' => 's3@lamdepilar.ac.id', 'name' => 'LAMDEPILAR S3 (Contoh)', 'level' => 8],
            ['email' => 'profesi@lamdepilar.ac.id', 'name' => 'LAMDEPILAR Profesi (Contoh)', 'level' => 10],
            ['email' => 'spesialis@lamdepilar.ac.id', 'name' => 'LAMDEPILAR Spesialis (Contoh)', 'level' => 11],
        ];

        foreach ($lamdepilarProdis as $prodi) {
            StudyProgram::firstOrCreate(
                ['email' => $prodi['email']],
                [
                    'name' => $prodi['name'],
                    'full_name' => 'Program Studi ' . $prodi['name'],
                    'code' => Str::upper(Str::random(8)),
                    'id_university' => $uLamdepilar->id,
                    'id_degree_level' => $prodi['level'],
                    'category_id' => 1,
                    'bentuk_pt' => 'Universitas',
                    'is_active' => true,
                    'is_example' => true,
                ]
            );
        }

        /**
         * ======================================================
         * 4. PRODI UNIVERSITAS CONTOH ABCD (TETAP ADA)
         * ======================================================
         */
        StudyProgram::firstOrCreate(
            ['email' => 'depilar@abcd.ac.id'],
            [
                'name' => 'DEPILAR (Contoh)',
                'full_name' => 'Program Studi DEPILAR (Contoh)',
                'code' => 'DP-EX-002',
                'id_university' => $uABCD->id,
                'id_degree_level' => 5,
                'category_id' => 2,
                'bentuk_pt' => 'Universitas',
                'is_active' => true,
                'is_example' => true,
            ]
        );

        StudyProgram::firstOrCreate(
            ['email' => 'testing@abcd.ac.id'],
            [
                'name' => 'Testing (Contoh)',
                'full_name' => 'Program Studi S2 Testing (Contoh)',
                'code' => 'TEST-EX-002',
                'id_university' => $uABCD->id,
                'id_degree_level' => 6,
                'category_id' => 3,
                'bentuk_pt' => 'Universitas',
                'is_active' => true,
                'is_example' => true,
            ]
        );

        /**
         * ======================================================
         * 5. USER ↔ PRODI (ADMIN PRODI)
         * ======================================================
         */
        $mappings = [
            'uppsd3@daisy.lamdepilar.or.id'        => 'd3@lamdepilar.ac.id',
            'uppss1@daisy.lamdepilar.or.id'        => 's1@lamdepilar.ac.id',
            'uppss2@daisy.lamdepilar.or.id'        => 's2@lamdepilar.ac.id',
            'uppss3@daisy.lamdepilar.or.id'        => 's3@lamdepilar.ac.id',
            'uppsprofesi@daisy.lamdepilar.or.id'   => 'profesi@lamdepilar.ac.id',
            'uppsspesialis@daisy.lamdepilar.or.id' => 'spesialis@lamdepilar.ac.id',
        ];

        foreach ($mappings as $userEmail => $prodiEmail) {
            $user  = User::where('email', $userEmail)->first();
            $prodi = StudyProgram::where('email', $prodiEmail)->first();

            if ($user && $prodi) {
                $user->studyPrograms()->syncWithoutDetaching([
                    $prodi->id => [
                        'role_in_prodi' => 'admin_prodi',
                        'is_active' => true,
                        'start_date' => now(),
                    ]
                ]);
            }
        }

        $this->command->info('✅ StudyProgramUserSeeder completed successfully.');
    }
}
