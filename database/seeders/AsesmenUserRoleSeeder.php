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
        $asesmen = Asesmen::insertGetId([
            'code' => 'Asesmen-' . Fungsi::uniqueCode(5),
            'name' => 'Penilaian Akreditasi Prodi Magister Ilmu Lingkungan Universitas Diponegoro 2025',
            'description' => 'Penilaian akreditasi Prodi Magister Ilmu Lingkungan Universitas Diponegoro untuk tahun 2025-2030',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $roleAsesor = DB::table('roles')->whereName('asesor')->value('id');
        $roleValidator = DB::table('roles')->whereName('validator')->value('id');
        AsesmenUserRole::create([
            'id_asesmen' => $asesmen,
            'id_user' => 5,
            'id_role' => $roleAsesor,
        ]);
        AsesmenUserRole::create([
            'id_asesmen' => $asesmen,
            'id_user' => 6,
            'id_role' => $roleValidator,
        ]);
    }
}
