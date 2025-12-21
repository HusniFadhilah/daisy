<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudyProgramCategory;

class StudyProgramCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'AK',
                'name' => 'Akademik',
                'description' => 'Program Studi Sarjana (S1)'
            ],
            [
                'code' => 'MAG',
                'name' => 'Magister',
                'description' => 'Program Studi Magister (S2)'
            ],
            [
                'code' => 'DOK',
                'name' => 'Doktor',
                'description' => 'Program Studi Doktor (S3)'
            ],
            [
                'code' => 'VOK',
                'name' => 'Vokasi',
                'description' => 'Program Studi Diploma II, Diploma III, Diploma IV, dan Sarjana Terapan'
            ],
            [
                'code' => 'MT',
                'name' => 'Magister Terapan',
                'description' => 'Program Studi Magister Terapan dan Doktor Terapan'
            ],
            [
                'code' => 'PRO',
                'name' => 'Profesi',
                'description' => 'Program Studi Profesi, Spesialis 1, dan Spesialis 2'
            ],
        ];

        foreach ($categories as $category) {
            StudyProgramCategory::updateOrCreate(
                ['code' => $category['code']],
                $category
            );
        }

        $this->command->info('Study Program Categories seeded successfully!');
    }
}
