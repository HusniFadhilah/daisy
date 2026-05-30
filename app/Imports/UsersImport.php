<?php

namespace App\Imports;

use App\Models\StudyProgram;
use App\Models\University;
use App\Models\User;
use App\Models\UserEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use SkipsErrors, SkipsFailures;

    /**
     * @param array $row
     * @return User|null
     */
    public function model(array $row)
    {
        $roleSelected = $this->blankToNull($row['role_aktif'] ?? null) ?? 'default';

        // Parse roles - bisa comma separated atau array
        $roles = [];
        if (!empty($row['semua_roles'])) {
            if (is_string($row['semua_roles'])) {
                $roles = array_map('trim', explode(',', $row['semua_roles']));
            } elseif (is_array($row['semua_roles'])) {
                $roles = $row['semua_roles'];
            }
        }

        // Jika roles kosong, gunakan role_selected sebagai default
        if (empty($roles) && $roleSelected !== 'default') {
            $roles = [$roleSelected];
        }

        // Pastikan role_selected ada di dalam roles
        if ($roleSelected !== 'default' && !in_array($roleSelected, $roles)) {
            $roles[] = $roleSelected;
        }

        $roles = array_values(array_unique(array_filter($roles)));
        $isUniversityAccount = $this->isUniversityAccount($roleSelected, $roles);
        $university = $this->findUniversity($row);
        $email = $this->resolveLoginEmail($row, $university, $isUniversityAccount);

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $row['nama'],
                'email' => $email,
                'password' => !empty($row['password']) ? Hash::make($row['password']) : Hash::make('password123'),
                'role' => $row['role'] ?? 'user',
                'role_selected' => $roleSelected,
                'roles' => $roles,
                'is_multiple_role' => count($roles) > 1,
                'must_change_password' => true,
                'phone' => $row['no_telepon'] ?? null,
                'address' => $row['alamat'] ?? null,
                'institution' => $row['institusi'] ?? null,
                'id_university' => $university?->id,
                'id_study_program' => $this->blankToNull($row['id_program_studi'] ?? null),
                'position' => $row['jabatan'] ?? null,
            ]
        );

        $this->syncPrimaryEmail($user, $email);

        if ($isUniversityAccount) {
            $studyPrograms = $this->resolveStudyPrograms($row, $university);
            $this->attachStudyPrograms($user, $studyPrograms);
            $this->syncStudyProgramEmails($user, $studyPrograms);
        }

        return $user;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email',
            'email_universitas' => 'nullable|email',
            'role' => 'nullable|in:admin,user',
            'role_aktif' => 'nullable|in:super_admin,sekretariat,asesor,validator,verifikator,admin_univ,admin_prodi,keuangan_lamdepilar,asesor_banding,default',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'institusi' => 'nullable|string|max:255',
            'id_universitas' => 'nullable|exists:universities,id',
            'id_program_studi' => 'nullable|exists:study_programs,id',
            'id_program_studi_list' => 'nullable',
            'email_prodi_list' => 'nullable',
            'jabatan' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email_universitas.email' => 'Format email universitas tidak valid',
        ];
    }

    private function isUniversityAccount(string $roleSelected, array $roles): bool
    {
        return in_array($roleSelected, ['admin_univ', 'admin_prodi'], true)
            || !empty(array_intersect($roles, ['admin_univ', 'admin_prodi']));
    }

    private function findUniversity(array $row): ?University
    {
        $id = $this->blankToNull($row['id_universitas'] ?? null);

        return $id ? University::find($id) : null;
    }

    private function resolveLoginEmail(array $row, ?University $university, bool $isUniversityAccount): string
    {
        $email = $this->blankToNull($row['email'] ?? null);

        if ($isUniversityAccount) {
            $email = $this->blankToNull($row['email_universitas'] ?? null)
                ?? $this->blankToNull($university?->email)
                ?? $email;
        }

        if (!$email || $email === '-' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => 'Email login wajib valid. Untuk admin universitas/prodi gunakan email_universitas atau pastikan universities.email terisi.',
            ]);
        }

        return strtolower($email);
    }

    private function resolveStudyPrograms(array $row, ?University $university)
    {
        $studyProgramIds = collect($this->parseList($row['id_program_studi_list'] ?? null));
        $singleStudyProgramId = $this->blankToNull($row['id_program_studi'] ?? null);

        if ($singleStudyProgramId) {
            $studyProgramIds->prepend($singleStudyProgramId);
        }

        $studyPrograms = collect();

        if ($studyProgramIds->isNotEmpty()) {
            $studyPrograms = $studyPrograms->merge(
                StudyProgram::whereIn('id', $studyProgramIds->unique()->values())->get()
            );

            $foundIds = $studyPrograms->pluck('id')->map(fn ($id) => (string) $id)->all();
            $missingIds = $studyProgramIds->map(fn ($id) => (string) $id)->diff($foundIds);

            if ($missingIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'id_program_studi_list' => 'Program studi tidak ditemukan: ' . $missingIds->implode(', '),
                ]);
            }
        }

        $emailList = collect($this->parseList($row['email_prodi_list'] ?? null))
            ->map(fn ($email) => strtolower($email));

        if ($emailList->isNotEmpty()) {
            $invalidEmails = $emailList->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

            if ($invalidEmails->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'email_prodi_list' => 'Format email prodi tidak valid: ' . $invalidEmails->implode(', '),
                ]);
            }

            $studyPrograms = $studyPrograms->merge(
                StudyProgram::whereIn('email', $emailList)
                    ->orWhereIn('akreditasi_email', $emailList)
                    ->get()
            );

            $foundEmails = $studyPrograms
                ->flatMap(fn ($studyProgram) => [$studyProgram->email, $studyProgram->akreditasi_email])
                ->filter()
                ->map(fn ($email) => strtolower($email))
                ->all();
            $missingEmails = $emailList->diff($foundEmails);

            if ($missingEmails->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'email_prodi_list' => 'Email prodi tidak ditemukan: ' . $missingEmails->implode(', '),
                ]);
            }
        }

        if ($studyPrograms->isEmpty() && $university) {
            $studyPrograms = StudyProgram::where('id_university', $university->id)->get();
        }

        if ($university) {
            $invalid = $studyPrograms->first(fn ($studyProgram) => (int) $studyProgram->id_university !== (int) $university->id);

            if ($invalid) {
                throw ValidationException::withMessages([
                    'id_program_studi' => "Program studi {$invalid->name} tidak berada pada universitas yang dipilih.",
                ]);
            }
        }

        return $studyPrograms->unique('id')->values();
    }

    private function attachStudyPrograms(User $user, $studyPrograms): void
    {
        foreach ($studyPrograms as $studyProgram) {
            $user->studyPrograms()->syncWithoutDetaching([
                $studyProgram->id => [
                    'role_in_prodi' => 'admin_prodi',
                    'is_active' => true,
                    'start_date' => now(),
                ],
            ]);
        }
    }

    private function syncPrimaryEmail(User $user, string $email): void
    {
        UserEmail::where('user_id', $user->id)->update(['is_primary' => false]);

        UserEmail::updateOrCreate(
            ['user_id' => $user->id, 'email' => $email],
            ['is_primary' => true, 'is_active' => true]
        );
    }

    private function syncStudyProgramEmails(User $user, $studyPrograms): void
    {
        foreach ($studyPrograms as $studyProgram) {
            foreach ([$studyProgram->email, $studyProgram->akreditasi_email] as $email) {
                $email = $this->blankToNull($email);

                if (!$email || $email === '-' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }

                UserEmail::updateOrCreate(
                    ['user_id' => $user->id, 'email' => strtolower($email)],
                    ['is_primary' => false, 'is_active' => true]
                );
            }
        }
    }

    private function parseList($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(fn ($item) => $this->blankToNull($item), $value)));
        }

        $value = $this->blankToNull($value);

        if (!$value) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => $this->blankToNull($item),
            preg_split('/[,;|]/', $value)
        )));
    }

    private function blankToNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
