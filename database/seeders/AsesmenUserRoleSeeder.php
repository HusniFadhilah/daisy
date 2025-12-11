<?php

namespace Database\Seeders;

use App\Models\Asesmen;
use App\Libraries\Fungsi;
use App\Models\AsesmenUserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AsesmenUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAsesor    = DB::table('roles')->whereName('asesor')->value('id');
        $roleValidator = DB::table('roles')->whereName('validator')->value('id');


        // === 1. Asesmen Magister Ilmu Lingkungan ===
        $asesmenMil = Asesmen::insertGetId([
            'code' => 'Asesmen-' . Fungsi::uniqueCode(5),
            'name' => 'Penilaian Akreditasi Prodi Magister Ilmu Lingkungan Universitas Diponegoro 2025',
            'description' => 'Penilaian akreditasi Prodi Magister Ilmu Lingkungan Universitas Diponegoro untuk tahun 2025-2030',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $asesorsMil = [4, 5];
        $validatorsMil = [9];

        foreach ($asesorsMil as $u) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenMil,
                'id_user' => $u,
                'id_role' => $roleAsesor,
            ]);
        }

        foreach ($validatorsMil as $u) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenMil,
                'id_user' => $u,
                'id_role' => $roleValidator,
            ]);
        }


        // === 2. Asesmen Teknik Informatika ===
        $asesmenTI = Asesmen::insertGetId([
            'code' => 'Asesmen-' . Fungsi::uniqueCode(5),
            'name' => 'Penilaian Akreditasi Prodi Teknik Informatika Universitas Diponegoro 2025',
            'description' => 'Asesmen akreditasi Prodi Teknik Informatika untuk periode 2025-2030',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $asesorsTI = [6, 7];
        $validatorsTI = [11];

        foreach ($asesorsTI as $u) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenTI,
                'id_user' => $u,
                'id_role' => $roleAsesor,
            ]);
        }

        foreach ($validatorsTI as $u) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenTI,
                'id_user' => $u,
                'id_role' => $roleValidator,
            ]);
        }
    }
}
