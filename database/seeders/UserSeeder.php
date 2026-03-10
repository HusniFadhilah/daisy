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
                'role_selected' => 'sekretariat',
                'roles' => ['sekretariat'],
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'sekretariat',
                'roles' => ['sekretariat'],
                'must_change_password' => true
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
                'name' => 'Asesor 3',
                'email' => 'asesor3@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
            ],
            [
                'name' => 'Asesor 4',
                'email' => 'asesor4@daisy.lamdepilar.or.id',
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
                'must_change_password' => true
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
                'must_change_password' => true
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor',
                'roles' => ['asesor'],
                'must_change_password' => true
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
                'must_change_password' => true
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
                'must_change_password' => true
            ],
            [
                'name' => 'Rina Wati',
                'email' => 'rina.wati@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'verifikator',
                'roles' => ['verifikator'],
                'must_change_password' => true
            ],
            [
                'name' => 'Perguruan Tinggi 1',
                'email' => 'pt1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_univ',
                'roles' => ['admin_univ'],
                'must_change_password' => true
            ],
            [
                'name' => 'Perguruan Tinggi 2',
                'email' => 'pt2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_univ',
                'roles' => ['admin_univ'],
                'must_change_password' => true
            ],
            // ========================================
            // 8 UPPS DEPILAR (4 Rumpun × 2 Jenis)
            // ========================================

            // UPPS VOKASI
            [
                'name' => 'UPPS Vokasi - Desain DEPILAR',
                'email' => 'upps.vokasi.desain@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],
            [
                'name' => 'UPPS Vokasi - Perencanaan DEPILAR',
                'email' => 'upps.vokasi.perencanaan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],
            [
                'name' => 'UPPS Vokasi - Lingkungan DEPILAR',
                'email' => 'upps.vokasi.lingkungan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],
            [
                'name' => 'UPPS Vokasi - Arsitektur DEPILAR',
                'email' => 'upps.vokasi.arsitektur@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],

            // UPPS AKADEMIK
            [
                'name' => 'UPPS Akademik - Desain DEPILAR',
                'email' => 'upps.akademik.desain@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],
            [
                'name' => 'UPPS Akademik - Perencanaan DEPILAR',
                'email' => 'upps.akademik.perencanaan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],
            [
                'name' => 'UPPS Akademik - Lingkungan DEPILAR',
                'email' => 'upps.akademik.lingkungan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],
            [
                'name' => 'UPPS Akademik - Arsitektur DEPILAR',
                'email' => 'upps.akademik.arsitektur@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'admin_prodi',
                'roles' => ['admin_prodi'],
                'must_change_password' => true
            ],
            [
                'name' => 'Keuangan LAMDEPILAR',
                'email' => 'keuangan@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'keuangan_lamdepilar',
                'roles' => ['keuangan_lamdepilar'],
                'must_change_password' => true
            ],
            [
                'name' => 'Asesor Banding 1',
                'email' => 'asesorbanding1@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor_banding',
                'roles' => ['asesor_banding'],
            ],
            [
                'name' => 'Asesor Banding 2',
                'email' => 'asesorbanding2@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor_banding',
                'roles' => ['asesor_banding'],
            ],
            [
                'name' => 'Asesor Banding 3',
                'email' => 'asesorbanding3@daisy.lamdepilar.or.id',
                'password' => Hash::make('=Secret1234'),
                'role' => 'user',
                'role_selected' => 'asesor_banding',
                'roles' => ['asesor_banding'],
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
                'roles' => ["super_admin", "sekretariat", "asesor", "asesor_banding", "validator", "validator", "verifikator", "admin_univ", "admin_prodi", "keuangan_lamdepilar", "default"],
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                array_merge($user, ['must_change_password' => false]) // Seeder set false, hanya Excel yang true
            );
        }
    }
}
