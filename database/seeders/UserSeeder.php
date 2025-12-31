<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::insert([
            [
                'name' => 'Superadmin',
                'email' => 'superadmin@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'admin',
                'role_selected' => 'super_admin',
                'roles' => json_encode(['super_admin']),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sekretariat LAMDEPILAR',
                'email' => 'sekretariat@lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'admin',
                'role_selected' => 'asesi',
                'roles' => json_encode(['asesi']),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesi',
                'roles' => json_encode(['asesi']),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Regular users
        $users = [
            [
                'name' => 'Asesor 1',
                'email' => 'asesor1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => json_encode(['asesor']),
            ],
            [
                'name' => 'Asesor 2',
                'email' => 'asesor2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => json_encode(['asesor']),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => json_encode(['asesor']),
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => json_encode(['asesor']),
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => json_encode(['asesor']),
            ],
            [
                'name' => 'Validator 1',
                'email' => 'validator1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'validator',
                'roles' => json_encode(['validator']),
            ],
            [
                'name' => 'Validator 2',
                'email' => 'validator2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'validator',
                'roles' => json_encode(['validator']),
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'validator',
                'roles' => json_encode(['validator']),
            ],
            [
                'name' => 'Verifikator 1',
                'email' => 'verifikator1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => json_encode(['verifikator']),
            ],
            [
                'name' => 'Verifikator 2',
                'email' => 'verifikator2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => json_encode(['verifikator']),
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => json_encode(['verifikator']),
            ],
            [
                'name' => 'Rina Wati',
                'email' => 'rina.wati@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => json_encode(['verifikator']),
            ],
            [
                'name' => 'Perguruan Tinggi 1',
                'email' => 'pt1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_univ',
                'roles' => json_encode(['admin_univ']),
            ],
            [
                'name' => 'Perguruan Tinggi 2',
                'email' => 'pt2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_univ',
                'roles' => json_encode(['admin_univ']),
            ],
            [
                'name' => 'PS/UPPS/PT 1',
                'email' => 'upps1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => json_encode(['admin_prodi']),
            ],
            [
                'name' => 'PS/UPPS/PT 2',
                'email' => 'upps2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => json_encode(['admin_prodi']),
            ],
            [
                'name' => 'Default User',
                'email' => 'default@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'default',
                'roles' => json_encode(['default']),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
