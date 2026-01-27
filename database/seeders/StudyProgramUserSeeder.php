<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\University;
use Illuminate\Support\Str;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
            'EX-UNI-001' => ['name' => 'Universitas DEPILAR', 'logo' => 'logo.png'],
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

            // Update logo
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
         * 1.5 UPDATE ID_UNIVERSITY UNTUK USER UPPS DEPILAR
         * ======================================================
         */
        $this->command->info('🔄 Updating id_university for UPPS users...');

        $uppsEmails = [
            'upps.vokasi.desain@daisy.lamdepilar.or.id',
            'upps.vokasi.perencanaan@daisy.lamdepilar.or.id',
            'upps.vokasi.lingkungan@daisy.lamdepilar.or.id',
            'upps.vokasi.arsitektur@daisy.lamdepilar.or.id',
            'upps.akademik.desain@daisy.lamdepilar.or.id',
            'upps.akademik.perencanaan@daisy.lamdepilar.or.id',
            'upps.akademik.lingkungan@daisy.lamdepilar.or.id',
            'upps.akademik.arsitektur@daisy.lamdepilar.or.id',
        ];

        foreach ($uppsEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update(['id_university' => $uLamdepilar->id]);
                $this->command->info("✓ Updated: {$user->name} -> Universitas DEPILAR");
            } else {
                $this->command->warn("⚠️ User tidak ditemukan: {$email}");
            }
        }

        /**
         * ======================================================
         * 2. MAPPING DEGREE LEVEL IDS
         * ======================================================
         */
        $degreeLevelIds = [
            'd2' => 2,           // D2
            'd3' => 3,           // D3
            'd4' => 4,           // D4 / Sarjana Terapan
            's1' => 5,           // S1
            's2' => 6,           // S2
            's3' => 8,           // S3
            'profesi' => 7,      // Profesi
            's2-terapan' => 9,   // Magister Terapan (MT.r)
            's3-terapan' => 10,  // Doktor Terapan (DT.r)
        ];

        /**
         * ======================================================
         * 3. PRODI LAMA (WAJIB TETAP ADA — JANGAN DIHAPUS)
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
                'id_category' => 1,
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
                'id_category' => 2,
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
         * 4. PRODI DEPILAR (9 PRODI SESUAI REQUIREMENT)
         * ======================================================
         */
        $this->command->info('🎓 Creating 9 DEPILAR Study Programs...');

        $lamdepilarProdis = [
            // 1. D2 (VOKASI)
            [
                'email' => 'd2@lamdepilar.ac.id',
                'name' => 'DEPILAR D2',
                'full_name' => 'Program Studi Depillar Diploma Dua (D2)',
                'degree_code' => 'd2',
                'category' => 1, // Vokasi
            ],

            // 2. D3 (VOKASI)
            [
                'email' => 'd3@lamdepilar.ac.id',
                'name' => 'DEPILAR D3',
                'full_name' => 'Program Studi Depillar Diploma Tiga (D3)',
                'degree_code' => 'd3',
                'category' => 1, // Vokasi
            ],

            // 3. Sarjana Terapan (ST.r) -> D4 (VOKASI)
            [
                'email' => 'str@lamdepilar.ac.id',
                'name' => 'DEPILAR Sarjana Terapan',
                'full_name' => 'Program Studi Depilar Sarjana Terapan (ST.r)',
                'degree_code' => 'd4',
                'category' => 1, // Vokasi
            ],

            // 4. Magister Terapan (MT.r) (TERAPAN TINGKAT LANJUT)
            [
                'email' => 'mtr@lamdepilar.ac.id',
                'name' => 'DEPILAR Magister Terapan',
                'full_name' => 'Program Studi Depilar Magister Terapan (MT.r)',
                'degree_code' => 's2-terapan',
                'category' => 1, // Vokasi tingkat lanjut
            ],

            // 5. Doktor Terapan (DT.r) (TERAPAN TINGKAT LANJUT)
            [
                'email' => 'dtr@lamdepilar.ac.id',
                'name' => 'DEPILAR Doktor Terapan',
                'full_name' => 'Program Studi Depilar Doktor Terapan (DT.r)',
                'degree_code' => 's3-terapan',
                'category' => 1, // Vokasi tingkat lanjut
            ],

            // 6. Sarjana (S1) (AKADEMIK)
            [
                'email' => 's1@lamdepilar.ac.id',
                'name' => 'DEPILAR S1',
                'full_name' => 'Program Studi Depilar Sarjana (S1)',
                'degree_code' => 's1',
                'category' => 2, // Akademik
            ],

            // 7. Profesi (AKADEMIK) ✓
            [
                'email' => 'profesi@lamdepilar.ac.id',
                'name' => 'DEPILAR Profesi',
                'full_name' => 'Program Studi Depilar Profesi (Profesi)',
                'degree_code' => 'profesi',
                'category' => 2, // Akademik ✓
            ],

            // 8. Magister (S2) (AKADEMIK)
            [
                'email' => 's2@lamdepilar.ac.id',
                'name' => 'DEPILAR S2',
                'full_name' => 'Program Studi Depilar Magister (S2)',
                'degree_code' => 's2',
                'category' => 2, // Akademik
            ],

            // 9. Doktor (S3) (AKADEMIK)
            [
                'email' => 's3@lamdepilar.ac.id',
                'name' => 'DEPILAR S3',
                'full_name' => 'Program Studi Doktor (S3)',
                'degree_code' => 's3',
                'category' => 2, // Akademik
            ],
        ];

        foreach ($lamdepilarProdis as $prodi) {
            $degreeId = $degreeLevelIds[$prodi['degree_code']] ?? null;

            $studyProgram = StudyProgram::firstOrCreate(
                ['email' => $prodi['email']],
                [
                    'name' => $prodi['name'],
                    'full_name' => $prodi['full_name'],
                    'code' => 'DP-' . Str::upper($prodi['degree_code']) . '-' . rand(100, 999),
                    'id_university' => $uLamdepilar->id,
                    'id_degree_level' => $degreeId,
                    'id_category' => $prodi['category'],
                    'bentuk_pt' => 'Universitas',
                    'peringkat_akreditasi' => 'Unggul',
                    'tanggal_kedaluwarsa' => Carbon::create(2026, 8, 10),
                    'status_kedaluwarsa' => 'Aktif',
                    'is_active' => true,
                    'is_example' => true,
                ]
            );

            $this->command->info("✓ Created: {$studyProgram->name}");
        }

        $this->command->info('✅ Total DEPILAR Prodi: 9');

        /**
         * ======================================================
         * 5. PRODI UNIVERSITAS CONTOH ABCD (TETAP ADA)
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
                'id_category' => 2,
                'bentuk_pt' => 'Universitas',
                'peringkat_akreditasi' => null,
                'tanggal_kedaluwarsa' => null,
                'status_kedaluwarsa' => 'Belum Terakreditasi',
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
                'id_category' => 3,
                'bentuk_pt' => 'Universitas',
                'peringkat_akreditasi' => 'Unggul',
                'tanggal_kedaluwarsa' => Carbon::create(2026, 8, 11),
                'status_kedaluwarsa' => 'Aktif',
                'is_active' => true,
                'is_example' => false,
            ]
        );

        /**
         * ======================================================
         * 6. MAPPING USER UPPS ↔ PRODI DEPILAR (8 UPPS)
         * ======================================================
         *
         * Pembagian:
         * - UPPS Vokasi (4 rumpun) → D2, D3, D4/ST.r, MT.r, DT.r
         * - UPPS Akademik (4 rumpun) → S1, Profesi, S2, S3, MT.r, DT.r
         */
        $this->command->info('🔗 Mapping UPPS to Study Programs...');

        // Mapping: UPPS Email => Array Prodi Email
        $uppsProdiMappings = [
            // ========================================
            // UPPS VOKASI - Mengelola D2, D3, D4, MT.r, DT.r
            // ========================================
            'upps.vokasi.desain@daisy.lamdepilar.or.id' => [
                'd2@lamdepilar.ac.id',
                'd3@lamdepilar.ac.id',
                'str@lamdepilar.ac.id', // D4/ST.r
                'mtr@lamdepilar.ac.id', // MT.r
                'dtr@lamdepilar.ac.id', // DT.r
            ],
            'upps.vokasi.perencanaan@daisy.lamdepilar.or.id' => [
                'd2@lamdepilar.ac.id',
                'd3@lamdepilar.ac.id',
                'str@lamdepilar.ac.id',
                'mtr@lamdepilar.ac.id',
                'dtr@lamdepilar.ac.id',
            ],
            'upps.vokasi.lingkungan@daisy.lamdepilar.or.id' => [
                'd2@lamdepilar.ac.id',
                'd3@lamdepilar.ac.id',
                'str@lamdepilar.ac.id',
                'mtr@lamdepilar.ac.id',
                'dtr@lamdepilar.ac.id',
            ],
            'upps.vokasi.arsitektur@daisy.lamdepilar.or.id' => [
                'd2@lamdepilar.ac.id',
                'd3@lamdepilar.ac.id',
                'str@lamdepilar.ac.id',
                'mtr@lamdepilar.ac.id',
                'dtr@lamdepilar.ac.id',
            ],

            // ========================================
            // UPPS AKADEMIK - Mengelola S1, Profesi, S2, S3, MT.r, DT.r
            // ========================================
            'upps.akademik.desain@daisy.lamdepilar.or.id' => [
                's1@lamdepilar.ac.id',
                'profesi@lamdepilar.ac.id', // ✓ Profesi masuk Akademik
                's2@lamdepilar.ac.id',
                's3@lamdepilar.ac.id',
                'mtr@lamdepilar.ac.id', // MT.r
                'dtr@lamdepilar.ac.id', // DT.r
            ],
            'upps.akademik.perencanaan@daisy.lamdepilar.or.id' => [
                's1@lamdepilar.ac.id',
                'profesi@lamdepilar.ac.id',
                's2@lamdepilar.ac.id',
                's3@lamdepilar.ac.id',
                'mtr@lamdepilar.ac.id',
                'dtr@lamdepilar.ac.id',
            ],
            'upps.akademik.lingkungan@daisy.lamdepilar.or.id' => [
                's1@lamdepilar.ac.id',
                'profesi@lamdepilar.ac.id',
                's2@lamdepilar.ac.id',
                's3@lamdepilar.ac.id',
                'mtr@lamdepilar.ac.id',
                'dtr@lamdepilar.ac.id',
            ],
            'upps.akademik.arsitektur@daisy.lamdepilar.or.id' => [
                's1@lamdepilar.ac.id',
                'profesi@lamdepilar.ac.id',
                's2@lamdepilar.ac.id',
                's3@lamdepilar.ac.id',
                'mtr@lamdepilar.ac.id',
                'dtr@lamdepilar.ac.id',
            ],

            // ========================================
            // USER LAMA (TETAP ADA - TIDAK BERUBAH)
            // ========================================
            'remahankecil@gmail.com' => ['depilar@abcd.ac.id'],
        ];

        // Execute mapping
        foreach ($uppsProdiMappings as $userEmail => $prodiEmails) {
            $user = User::where('email', $userEmail)->first();

            if (!$user) {
                $this->command->warn("⚠️ User tidak ditemukan: {$userEmail}");
                continue;
            }

            foreach ($prodiEmails as $prodiEmail) {
                $prodi = StudyProgram::where('email', $prodiEmail)->first();

                if (!$prodi) {
                    $this->command->warn("⚠️ Prodi tidak ditemukan: {$prodiEmail}");
                    continue;
                }

                $user->studyPrograms()->syncWithoutDetaching([
                    $prodi->id => [
                        'role_in_prodi' => 'admin_prodi',
                        'is_active' => true,
                        'start_date' => now(),
                    ]
                ]);

                $this->command->info("  ✓ {$user->name} → {$prodi->name}");
            }
        }

        $this->command->info('');
        $this->command->info('═════════════════════════════════════');
        $this->command->info('✅ StudyProgramUserSeeder completed!');
        $this->command->info('═════════════════════════════════════');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Universities: 3');
        $this->command->info('   - DEPILAR Prodi: 9');
        $this->command->info('   - UPPS Users: 8');
        $this->command->info('   - Vokasi UPPS: 4 (D2, D3, D4, MT.r, DT.r)');
        $this->command->info('   - Akademik UPPS: 4 (S1, Profesi, S2, S3, MT.r, DT.r)');
        $this->command->info('═════════════════════════════════════');
    }
}
