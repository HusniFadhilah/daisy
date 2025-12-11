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
        $roles = $this->getRoles();

        foreach ($roles as $role) {
            Role::create($role);
        }
    }

    public static function getRoles()
    {
        return collect([
            [
                'name' => 'super_admin',
                'alias' => 'Super Admin',
            ],
            [
                'name' => 'asesi',
                'alias' => 'Asesi',
            ],
            [
                'name' => 'asesor',
                'alias' => 'Asesor',
            ],
            [
                'name' => 'validator',
                'alias' => 'Validator',
            ],
            [
                'name' => 'verifikator',
                'alias' => 'Verifikator',
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
                'name' => 'default',
                'alias' => 'Default User',
            ],
        ]);
    }
}
