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
        $assessment = Asesmen::insertGetId([
            'code' => 'Asesmen-' . Fungsi::uniqueCode(5),
            'name' => 'Penilaian Akreditasi Universitas Serasan 2025',
            'description' => 'Penilaian akreditasi institusi perguruan tinggi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $roleAsesor = DB::table('roles')->whereName('asesor')->value('id');
        AsesmenUserRole::create([
            'id_asesmen' => $assessment,
            'id_user' => 5, // Replace dengan user ID yang login
            'id_role' => $roleAsesor,
        ]);
    }
}
