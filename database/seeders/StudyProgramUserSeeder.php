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
                'is_example' => false,
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
         * 4. PRODI DEPILAR (36 PRODI = 9 JENJANG x 4 RUMPUN)
         * ======================================================
         */
        $this->command->info('🎓 Creating 36 DEPILAR Study Programs (9 jenjang x 4 rumpun)...');

        $now = Carbon::now()->startOfDay();

        /**
         * ======================================================
         * KUOTA PERINGKAT (total harus 36)
         * - Unggul dibuat dominan (mendekati 90% tapi 36 tidak bisa presisi)
         * - sisanya dibagi supaya kategori lain tetap muncul
         * ======================================================
         */
        $akreditasiQuota = [
            'Unggul' => 31,                 // ~86.1%
            'Baik' => 1,                    // ~2.8%
            'Baik Sekali' => 1,             // ~2.8%
            'B' => 1,                       // ~2.8%
            'A' => 1,                       // ~2.8%
            'Tidak Terakreditasi' => 1,     // ~2.8%
            'Terakreditasi Pertama' => 0,   // 0-1 karena total kecil
            'Terakreditasi Sementara' => 0, // 0-1 karena total kecil
        ];

        /**
         * Buat "deck" peringkat sesuai kuota, lalu shuffle.
         */
        $akreditasiDeck = [];
        foreach ($akreditasiQuota as $rank => $count) {
            for ($i = 0; $i < $count; $i++) $akreditasiDeck[] = $rank;
        }
        shuffle($akreditasiDeck);

        // 4 rumpun
        $rumpunList = [
            ['slug' => 'desain',      'label' => 'Desain'],
            ['slug' => 'perencanaan', 'label' => 'Perencanaan'],
            ['slug' => 'lingkungan',  'label' => 'Lingkungan'],
            ['slug' => 'arsitektur',  'label' => 'Arsitektur'],
        ];

        // 9 jenjang (pakai degree_code yang cocok dengan $degreeLevelIds kamu)
        $jenjangList = [
            // VOKASI (5)
            ['degree_code' => 'd2',         'alias' => 'D2',     'label' => 'Diploma Dua (D2)',            'type' => 'vokasi',   'category' => 1],
            ['degree_code' => 'd3',         'alias' => 'D3',     'label' => 'Diploma Tiga (D3)',           'type' => 'vokasi',   'category' => 1],
            ['degree_code' => 'd4',         'alias' => 'STr',    'label' => 'Sarjana Terapan (ST.r)',      'type' => 'vokasi',   'category' => 1],
            ['degree_code' => 's2-terapan', 'alias' => 'MTr',    'label' => 'Magister Terapan (MT.r)',     'type' => 'vokasi',   'category' => 1],
            ['degree_code' => 's3-terapan', 'alias' => 'DTr',    'label' => 'Doktor Terapan (DT.r)',       'type' => 'vokasi',   'category' => 1],

            // AKADEMIK (4)
            ['degree_code' => 's1',      'alias' => 'S1',      'label' => 'Sarjana (S1)',                 'type' => 'akademik', 'category' => 2],
            ['degree_code' => 'profesi', 'alias' => 'Prof',    'label' => 'Profesi',                        'type' => 'akademik', 'category' => 2],
            ['degree_code' => 's2',      'alias' => 'S2',      'label' => 'Magister (S2)',                 'type' => 'akademik', 'category' => 2],
            ['degree_code' => 's3',      'alias' => 'S3',      'label' => 'Doktor (S3)',                   'type' => 'akademik', 'category' => 2],
        ];

        // buat penampung email prodi per rumpun+type untuk mapping UPPS
        $depilarProdiEmailsByRumpunType = []; // [rumpunSlug][type] = [emails...]
        $depilarPrograms = [];
        $totalCreated = 0;

        foreach ($rumpunList as $rumpun) {
            foreach ($jenjangList as $j) {
                $degreeId = $degreeLevelIds[$j['degree_code']] ?? null;
                $email = "{$rumpun['slug']}-{$j['degree_code']}@lamdepilar.ac.id";

                $studyProgram = StudyProgram::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => "DEPILAR {$rumpun['label']} {$j['alias']}",
                        'full_name' => "Program Studi {$rumpun['label']} - {$j['label']} (DEPILAR)",
                        'code' => 'DP-' . Str::upper(substr($rumpun['slug'], 0, 3)) . '-' . Str::upper(Str::slug($j['degree_code'], '')) . '-' . rand(100, 999),
                        'id_university' => $uLamdepilar->id,
                        'id_degree_level' => $degreeId,
                        'id_category' => $j['category'],
                        'bentuk_pt' => 'Universitas',

                        // sementara kosong dulu, diisi setelah loop
                        'peringkat_akreditasi' => null,
                        'tanggal_kedaluwarsa' => null,
                        'status_kedaluwarsa' => null,

                        'is_active' => true,
                        'is_example' => true,
                    ]
                );

                $depilarPrograms[] = [
                    'model' => $studyProgram,
                    'rumpun' => $rumpun['slug'],
                    'type' => $j['type'],         // akademik / vokasi
                    'degree_code' => $j['degree_code'],
                ];

                $depilarProdiEmailsByRumpunType[$rumpun['slug']][$j['type']][] = $email;
                $totalCreated++;
            }
        }

        // ====== GROUP TANGGAL KEDALUWARSA (akademik 4 prodi per rumpun)
        $group6  = $depilarProdiEmailsByRumpunType['perencanaan']['akademik'] ?? [];
        $group7  = $depilarProdiEmailsByRumpunType['desain']['akademik'] ?? [];
        $group8  = $depilarProdiEmailsByRumpunType['lingkungan']['akademik'] ?? [];

        // Buat lookup email -> expiry
        $expiryByEmail = [];
        foreach ($group6 as $email) $expiryByEmail[$email] = $now->copy()->addMonths(6);
        foreach ($group7 as $email) $expiryByEmail[$email] = $now->copy()->addMonths(7);
        foreach ($group8 as $email) $expiryByEmail[$email] = $now->copy()->addMonths(8);

        // Kandidat untuk expired 1 bulan lalu = ambil 1 email dari yang belum punya expiry khusus
        $specialEmails = array_merge($group6, $group7, $group8);

        $remainingEmails = array_values(array_filter(
            array_map(fn($x) => $x['model']->email, $depilarPrograms),
            fn($email) => !in_array($email, $specialEmails, true)
        ));

        shuffle($remainingEmails);
        $expiredEmail = $remainingEmails[0] ?? null;
        if ($expiredEmail) {
            $expiryByEmail[$expiredEmail] = $now->copy()->subMonths(1);
        }

        foreach ($depilarPrograms as $item) {
            /** @var \App\Models\StudyProgram $sp */
            $sp = $item['model'];
            $email = $sp->email;

            // === peringkat dari deck
            $rank = array_pop($akreditasiDeck) ?? 'Unggul';

            // === tanggal kedaluwarsa
            $expiry = $expiryByEmail[$email] ?? null;

            // sisanya random: +1 tahun atau +2 tahun
            if (!$expiry) {
                $expiry = (mt_rand(1, 100) <= 60)
                    ? $now->copy()->addYear()
                    : $now->copy()->addYears(2);
            }

            // === status kedaluwarsa
            $status = $expiry->lt($now) ? 'Kedaluwarsa' : 'Aktif';

            // Kalau "Tidak Terakreditasi" kamu mau benar-benar tanpa tanggal:
            if ($rank === 'Tidak Terakreditasi') {
                $sp->update([
                    'peringkat_akreditasi' => null,
                    'tanggal_kedaluwarsa' => null,
                    'status_kedaluwarsa' => 'Belum Terakreditasi',
                ]);
                continue;
            }

            $sp->update([
                'peringkat_akreditasi' => $rank,
                'tanggal_kedaluwarsa' => $expiry,
                'status_kedaluwarsa' => $status,
            ]);
        }

        $this->command->info("✅ Total DEPILAR Prodi created/updated: {$totalCreated} (expected 36)");

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
         */
        $this->command->info('🔗 Mapping UPPS to DEPILAR Study Programs (by rumpun & type)...');

        $uppsProdiMappings = [
            // VOKASI
            'upps.vokasi.desain@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['desain']['vokasi'] ?? [],
            'upps.vokasi.perencanaan@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['perencanaan']['vokasi'] ?? [],
            'upps.vokasi.lingkungan@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['lingkungan']['vokasi'] ?? [],
            'upps.vokasi.arsitektur@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['arsitektur']['vokasi'] ?? [],

            // AKADEMIK
            'upps.akademik.desain@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['desain']['akademik'] ?? [],
            'upps.akademik.perencanaan@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['perencanaan']['akademik'] ?? [],
            'upps.akademik.lingkungan@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['lingkungan']['akademik'] ?? [],
            'upps.akademik.arsitektur@daisy.lamdepilar.or.id' => $depilarProdiEmailsByRumpunType['arsitektur']['akademik'] ?? [],

            // USER LAMA (TETAP)
            'remahankecil@gmail.com' => ['depilar@abcd.ac.id'],
        ];

        // Execute mapping (sama seperti punyamu)
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
            }
        }
    }
}
