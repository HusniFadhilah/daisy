<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DegreeLevelSeeder::class,
            UniversitySeeder::class,
            StudyProgramSeeder::class,
            StudyProgramCategorySeeder::class,
            KriteriaSeeder::class,
            ElemenStandarSeeder::class,
            PernyataanSeeder::class,
            JenisIndikatorSeeder::class,
            IndikatorSeeder::class,
            JenjangPenilaianSeeder::class,
            IndikatorPenilaianElemenSeeder::class,
            AsesmenUserRoleSeeder::class,
            StudyProgramUserSeeder::class,
            AkreditasiSeeder::class,
            // PengajuanAkreditasiSeeder::class,
            DatasetBorangSeeder::class,
        ]);
    }
}
