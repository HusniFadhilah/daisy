<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UniversityUserSeeder extends Seeder
{
    private const PASSWORD = '=Lamdepilar2026';

    public function run(): void
    {
        $csvPath = database_path('seeders/data/data_akreditasi_lengkap.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("File CSV tidak ditemukan: {$csvPath}");
            return;
        }

        $degreeLevels = DegreeLevel::pluck('id', 'code')->toArray();
        $programsByUniversity = [];

        $handle = fopen($csvPath, 'r');
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            $this->command->error('Header CSV tidak valid.');
            return;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $universityName = $this->mapUniversityName(trim($row[0] ?? ''));
            $programName = trim($row[1] ?? '');
            $degreeCode = $this->mapJenjang(trim($row[2] ?? ''));

            if ($universityName === '' || $programName === '' || $degreeCode === '') {
                continue;
            }

            $university = University::where('name', $universityName)->first();
            $degreeLevelId = $degreeLevels[$degreeCode] ?? null;

            if (!$university || !$degreeLevelId) {
                continue;
            }

            $studyProgram = StudyProgram::where('id_university', $university->id)
                ->where('id_degree_level', $degreeLevelId)
                ->where('name', $programName)
                ->first();

            if (!$studyProgram) {
                continue;
            }

            $programsByUniversity[$university->id]['university'] = $university;
            $programsByUniversity[$university->id]['program_ids'][$studyProgram->id] = $studyProgram->id;
        }

        fclose($handle);

        $created = 0;
        $updated = 0;
        $attached = 0;
        $passwordHash = Hash::make(self::PASSWORD);

        foreach ($programsByUniversity as $item) {
            /** @var \App\Models\University $university */
            $university = $item['university'];
            $programIds = array_values($item['program_ids'] ?? []);

            if (empty($programIds)) {
                continue;
            }

            $email = $this->makeEmail($university);

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin ' . $university->name,
                    'password' => $passwordHash,
                    'role' => 'user',
                    'role_selected' => 'admin_univ',
                    'roles' => ['admin_univ', 'admin_prodi'],
                    'is_multiple_role' => true,
                    'must_change_password' => false,
                    'id_university' => $university->id,
                    'institution' => $university->name,
                    'position' => 'Admin Universitas',
                ]
            );

            $user->wasRecentlyCreated ? $created++ : $updated++;

            foreach ($programIds as $programId) {
                $user->studyPrograms()->syncWithoutDetaching([
                    $programId => [
                        'role_in_prodi' => 'admin_prodi',
                        'is_active' => true,
                        'start_date' => now(),
                    ],
                ]);

                $attached++;
            }
        }

        $this->command->info('University users seeded successfully!');
        $this->command->info("Created users: {$created}");
        $this->command->info("Updated users: {$updated}");
        $this->command->info("Attached study programs: {$attached}");
    }

    private function makeEmail(University $university): string
    {
        $slug = Str::slug($university->name);

        return "admin-{$university->id}-{$slug}@daisy.lamdepilar.or.id";
    }

    private function mapUniversityName(string $name): string
    {
        $mappings = [
            "Universitas Tunas Pembangunan Surakarta (UTP)" => "Universitas Tunas Pembangunan Surakarta",
            "Universitas Islam Negeri Syekh AIi Hasan Ahmad Addary Padangsidimpuan" => "Universitas Islam Negeri Syekh Ali Hasan Ahmad Addary Padangsidimpuan",
            "Universitas 'Aisyiyah Bandung" => "Universitas Aisyiyah Bandung",
            "Universitas Maritim Raja Ali Haji (UMRAH)" => "Universitas Maritim Raja Ali Haji",
        ];

        return $mappings[$name] ?? $name;
    }

    private function mapJenjang(string $jenjang): string
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
            'Profesi' => 'profesi',
            'PROFESI' => 'profesi',
        ];

        return $mappings[$jenjang] ?? $jenjang;
    }
}
