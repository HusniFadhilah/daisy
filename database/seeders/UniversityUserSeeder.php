<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

        $handle = $this->openCsvStream($csvPath);
        if ($handle === false) {
            $this->command->error("Gagal membuka CSV: {$csvPath}");
            return;
        }

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
            $email = $this->normalizeEmail(trim($row[6] ?? ''));

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
            if (
                $email !== null &&
                empty($programsByUniversity[$university->id]['email'])
            ) {
                $programsByUniversity[$university->id]['email'] = $email;
            }
            $programsByUniversity[$university->id]['program_ids'][$studyProgram->id] = $studyProgram->id;
        }

        fclose($handle);

        $created = 0;
        $updated = 0;
        $attached = 0;
        $aliasedEmails = 0;
        $protectedUsers = 0;
        $passwordHash = Hash::make(self::PASSWORD);
        $emailCounts = [];
        $emailIndexes = [];

        foreach ($programsByUniversity as $item) {
            if (!empty($item['email'])) {
                $emailCounts[$item['email']] = ($emailCounts[$item['email']] ?? 0) + 1;
            }
        }

        foreach ($programsByUniversity as $item) {
            /** @var \App\Models\University $university */
            $university = $item['university'];
            $programIds = array_values($item['program_ids'] ?? []);
            $sourceEmail = $item['email'] ?? null;
            $email = $sourceEmail ?? $this->makeEmail($university);
            if ($sourceEmail && ($emailCounts[$sourceEmail] ?? 0) > 1) {
                $emailIndexes[$sourceEmail] = ($emailIndexes[$sourceEmail] ?? 0) + 1;
                if ($emailIndexes[$sourceEmail] > 1) {
                    $email = $this->makeDuplicateEmailAlias($sourceEmail, $university);
                    $aliasedEmails++;
                }
            }

            if (empty($programIds)) {
                continue;
            }

            if ($sourceEmail && $university->email !== $sourceEmail) {
                $university->update(['email' => $sourceEmail]);
            }

            $existingUser = User::where('email', $email)->first();
            if (
                $existingUser &&
                $existingUser->id_university &&
                (int) $existingUser->id_university !== (int) $university->id
            ) {
                $email = $this->makeDuplicateEmailAlias($email, $university);
                $existingUser = User::where('email', $email)->first();
                $aliasedEmails++;
            }

            $user = $existingUser ?? new User(['email' => $email]);
            $isNewUser = !$user->exists;

            if ($isNewUser) {
                $user->fill([
                    'name' => 'Admin ' . $university->name,
                    'password' => $passwordHash,
                    'role' => 'user',
                    'role_selected' => 'admin_prodi',
                    'roles' => ['admin_prodi'],
                    'is_multiple_role' => true,
                    'must_change_password' => false,
                    'id_university' => $university->id,
                    'institution' => $university->name,
                    'position' => 'Admin Universitas',
                ]);
                $user->save();
                $created++;
            } else {
                $hasPengajuan = $this->userHasPengajuan($user);

                $updates = [
                    'id_university' => $user->id_university ?: $university->id,
                    'institution' => $user->institution ?: $university->name,
                    'position' => $user->position ?: 'Admin Universitas',
                ];

                if (!$hasPengajuan) {
                    $roles = $user->roles ?? [];
                    if (!in_array('admin_prodi', $roles, true)) {
                        $roles[] = 'admin_prodi';
                    }

                    $updates = array_merge($updates, [
                        'name' => 'Admin ' . $university->name,
                        'role' => 'user',
                        'role_selected' => 'admin_prodi',
                        'roles' => array_values(array_unique($roles)),
                        'is_multiple_role' => count(array_unique($roles)) > 1,
                        'must_change_password' => false,
                    ]);
                } else {
                    $protectedUsers++;
                }

                $user->fill($updates);
                if ($user->isDirty()) {
                    $user->save();
                }
                $updated++;
            }

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
        $this->command->info("Aliased duplicate emails: {$aliasedEmails}");
        $this->command->info("Protected existing users with pengajuan: {$protectedUsers}");
    }

    private function makeEmail(University $university): string
    {
        $slug = Str::slug($university->name);

        return "admin-{$university->id}-{$slug}@daisy.lamdepilar.or.id";
    }

    private function normalizeEmail(?string $email): ?string
    {
        if (!$email) {
            return null;
        }

        $email = strtolower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function makeDuplicateEmailAlias(string $email, University $university): string
    {
        [$local, $domain] = explode('@', $email, 2);

        return "{$local}+u{$university->id}@{$domain}";
    }

    private function userHasPengajuan(User $user): bool
    {
        return DB::table('pengajuan_akreditasi')
            ->where(function ($query) use ($user) {
                $query->where('id_user_pengaju', $user->id)
                    ->orWhere('id_de_assigned', $user->id)
                    ->orWhere('id_validator_assigned', $user->id);
            })
            ->exists();
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

    /**
     * @return resource|false
     */
    private function openCsvStream(string $path)
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            return false;
        }

        if (str_starts_with($contents, "\xFF\xFE")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($contents, "\xFE\xFF")) {
            $contents = mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16BE');
        } elseif (substr_count(substr($contents, 0, 512), "\0") > 10) {
            $contents = mb_convert_encoding($contents, 'UTF-8', 'UTF-16LE');
        } else {
            $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents) ?? $contents;
        }

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            return false;
        }

        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }
}
