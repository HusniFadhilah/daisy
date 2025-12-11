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
                'password' => Hash::make('password'),
                'role' => 'admin',
                'role_selected' => 'super_admin',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin Depilar',
                'email' => 'admin@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'role_selected' => 'asesi',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'asesi',
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
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'asesor',
            ],
            [
                'name' => 'Asesor 2',
                'email' => 'asesor2@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'asesor',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'asesor',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'asesor',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'asesor',
            ],
            [
                'name' => 'Validator 1',
                'email' => 'validator1@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'validator',
            ],
            [
                'name' => 'Validator 2',
                'email' => 'validator2@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'validator',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'validator',
            ],
            [
                'name' => 'Verifikator 1',
                'email' => 'verifikator1@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'verifikator',
            ],
            [
                'name' => 'Verifikator 2',
                'email' => 'verifikator2@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'verifikator',
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'verifikator',
            ],
            [
                'name' => 'Rina Wati',
                'email' => 'rina.wati@daisy.lamdepilar.or.id',
                'password' => Hash::make('password'),
                'role' => 'user',
                'role_selected' => 'verifikator',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
