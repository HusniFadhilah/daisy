<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'alias' => 'Super Admin',
            ],
            [
                'name' => 'verifikator',
                'alias' => 'Verifikator',
            ],
            [
                'name' => 'validator',
                'alias' => 'Validator',
            ],
            [
                'name' => 'admin_univ',
                'alias' => 'Admin Universitas',
            ],
            [
                'name' => 'admin_prodi',
                'alias' => 'Admin Program Studi',
            ],
            [
                'name' => 'asesi',
                'alias' => 'Asesi',
            ],
            [
                'name' => 'default',
                'alias' => 'Default User',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
