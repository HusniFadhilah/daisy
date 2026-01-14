<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
        if (empty($roles) && !empty($row['role_aktif'])) {
            $roles = [$row['role_aktif']];
        }

        // Pastikan role_selected ada di dalam roles
        if (!empty($row['role_aktif']) && !in_array($row['role_aktif'], $roles)) {
            $roles[] = $row['role_aktif'];
        }

        return User::updateOrCreate(
            ['email' => $row['email']],
            [
                'name' => $row['nama'],
                'email' => $row['email'],
                'password' => !empty($row['password']) ? Hash::make($row['password']) : Hash::make('password123'),
                'role' => $row['role'] ?? 'user',
                'role_selected' => $row['role_aktif'] ?? 'default',
                'roles' => array_values(array_unique($roles)),
                'is_multiple_role' => count($roles) > 1,
                'must_change_password' => true,
                'phone' => $row['no_telepon'] ?? null,
                'address' => $row['alamat'] ?? null,
                'institution' => $row['institusi'] ?? null,
                'id_university' => !empty($row['id_universitas']) ? $row['id_universitas'] : null,
                'id_study_program' => !empty($row['id_program_studi']) ? $row['id_program_studi'] : null,
                'position' => $row['jabatan'] ?? null,
            ]
        );
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'role' => 'nullable|in:admin,user',
            'role_aktif' => 'nullable|in:super_admin,asesi,asesor,validator,verifikator,admin_univ,admin_prodi,default',
            'no_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'institusi' => 'nullable|string|max:255',
            'id_universitas' => 'nullable|exists:universities,id',
            'id_program_studi' => 'nullable|exists:study_programs,id',
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
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
        ];
    }
}
