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
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DegreeLevelSeeder::class);
        $this->call(UniversitySeeder::class);
        $this->call(StudyProgramSeeder::class);
        $this->call(KriteriaSeeder::class);
        $this->call(ElemenStandarSeeder::class);
        $this->call(PernyataanSeeder::class);
        $this->call(JenisIndikatorSeeder::class);
        $this->call(IndikatorSeeder::class);
        $this->call(JenjangPenilaianSeeder::class);
        $this->call(IndikatorPenilaianElemenSeeder::class);
        $this->call(AsesmenUserRoleSeeder::class);
    }
}
