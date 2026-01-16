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
        $users = [
            [
                'name' => 'Superadmin',
                'email' => 'superadmin@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'admin',
                'role_selected' => 'super_admin',
                'roles' => ['super_admin'],
            ],
            [
                'name' => 'Sekretariat LAMDEPILAR',
                'email' => 'sekretariat@lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'admin',
                'role_selected' => 'asesi',
                'roles' => ['asesi'],
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesi',
                'roles' => ['asesi'],
            ],
            [
                'name' => 'Asesor 1',
                'email' => 'asesor1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
            ],
            [
                'name' => 'Asesor 2',
                'email' => 'asesor2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
            ],
            [
                'name' => 'Validator 1',
                'email' => 'validator1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'validator',
                'roles' => ['validator'],
            ],
            [
                'name' => 'Validator 2',
                'email' => 'validator2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'validator',
                'roles' => ['validator'],
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'validator',
                'roles' => ['validator'],
            ],
            [
                'name' => 'Verifikator 1',
                'email' => 'verifikator1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => ['verifikator'],
            ],
            [
                'name' => 'Verifikator 2',
                'email' => 'verifikator2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => ['verifikator'],
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => ['verifikator'],
            ],
            [
                'name' => 'Rina Wati',
                'email' => 'rina.wati@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => ['verifikator'],
            ],
            [
                'name' => 'Perguruan Tinggi 1',
                'email' => 'pt1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_univ',
                'roles' => ['admin_univ'],
            ],
            [
                'name' => 'Perguruan Tinggi 2',
                'email' => 'pt2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_univ',
                'roles' => ['admin_univ'],
            ],
            [
                'name' => 'PS/UPPS/PT 1',
                'email' => 'upps1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT 2',
                'email' => 'upps2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT 3',
                'email' => 'upps3@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT D3',
                'email' => 'uppsd3@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT S1',
                'email' => 'uppss1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT S2',
                'email' => 'uppss2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT S3',
                'email' => 'uppss3@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT Profesi',
                'email' => 'uppsprofesi@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'PS/UPPS/PT Spesialis',
                'email' => 'uppsspesialis@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
            ],
            [
                'name' => 'Keuangan LAMDEPILAR 1',
                'email' => 'keuangan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'keuangan_lamdepilar',
                'roles' => ['keuangan_lamdepilar'],
            ],
            [
                'name' => 'Default User',
                'email' => 'default@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'default',
                'roles' => ['default'],
            ],
            [
                'name' => 'Test User',
                'email' => 'remahankecil@gmail.com',
                'password' => Hash::make('=Secret1234'),
                'role' => 'admin',
                'role_selected' => 'super_admin',
                'roles' => ['super_admin', 'asesi', 'asesor', 'validator', 'verifikator', 'admin_univ', 'admin_prodi', 'default'],
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
