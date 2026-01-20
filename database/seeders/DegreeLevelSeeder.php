<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StudyProgramCategory;

class DegreeLevelSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DegreeLevel::truncate(); // hapus semua data lama
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil category ID
        $categories = StudyProgramCategory::pluck('id', 'code');

        $degreeLevels = [
            // VOKASI
            ['code' => 'd1', 'alias' => 'D1', 'name' => 'Diploma I', 'category' => 'VOK'],
            ['code' => 'd2', 'alias' => 'D2', 'name' => 'Diploma II', 'category' => 'VOK'],
            ['code' => 'd3', 'alias' => 'D3', 'name' => 'Diploma III', 'category' => 'VOK'],
            ['code' => 'd4', 'alias' => 'D4', 'name' => 'Diploma IV / Sarjana Terapan', 'category' => 'VOK'],

            // AKADEMIK
            ['code' => 's1', 'alias' => 'S1', 'name' => 'Sarjana (Strata 1)', 'category' => 'AK'],
            ['code' => 's2', 'alias' => 'S2', 'name' => 'Magister (Strata 2)', 'category' => 'MAG'],
            ['code' => 's3', 'alias' => 'S3', 'name' => 'Doktor (Strata 3)', 'category' => 'DOK'],

            // TERAPAN
            ['code' => 's2-terapan', 'alias' => 'S2 Terapan', 'name' => 'Magister Terapan', 'category' => 'MT'],
            ['code' => 's3-terapan', 'alias' => 'S3 Terapan', 'name' => 'Doktor Terapan', 'category' => 'MT'],

            // PROFESI
            ['code' => 'profesi', 'alias' => 'Profesi', 'name' => 'Pendidikan Profesi', 'category' => 'PRO'],
            ['code' => 'spesialis-1', 'alias' => 'Spesialis 1', 'name' => 'Pendidikan Spesialis 1', 'category' => 'PRO'],
            ['code' => 'spesialis-2', 'alias' => 'Spesialis 2', 'name' => 'Pendidikan Spesialis 2', 'category' => 'PRO'],
        ];

        foreach ($degreeLevels as $level) {
            DegreeLevel::updateOrCreate(
                ['code' => $level['code']],
                [
                    'alias'       => $level['alias'],
                    'name'        => $level['name'],
                    'id_category' => $categories[$level['category']] ?? null,
                    'is_active'   => true,
                ]
            );
        }

        $this->command->info('Degree levels seeded successfully!');
    }
}
