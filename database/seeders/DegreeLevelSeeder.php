<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DegreeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $degreeLevels = [
            ['code' => 'd1', 'alias' => 'D1', 'name' => 'Diploma I'],
            ['code' => 'd2', 'alias' => 'D2', 'name' => 'Diploma II'],
            ['code' => 'd3', 'alias' => 'D3', 'name' => 'Diploma III'],
            ['code' => 'd4', 'alias' => 'D4', 'name' => 'Diploma IV / Sarjana Terapan'],
            ['code' => 's1', 'alias' => 'S1', 'name' => 'Sarjana (Strata 1)'],
            ['code' => 's2', 'alias' => 'S2', 'name' => 'Magister (Strata 2)'],
            ['code' => 's2-terapan', 'alias' => 'S2 Terapan', 'name' => 'Magister Terapan (Strata 2)'],
            ['code' => 's3', 'alias' => 'S3', 'name' => 'Doktor (Strata 3)'],
            ['code' => 's3-terapan', 'alias' => 'S3 Terapan', 'name' => 'Doktor Terapan (Strata 3)'],
            ['code' => 'profesi', 'alias' => 'Profesi', 'name' => 'Pendidikan Profesi'],
            ['code' => 'spesialis', 'alias' => 'Spesialis', 'name' => 'Pendidikan Spesialis'],
        ];

        $timestamp = Carbon::now();

        foreach ($degreeLevels as &$level) {
            $level['created_at'] = $timestamp;
            $level['updated_at'] = $timestamp;
        }

        DB::table('degree_levels')->insert($degreeLevels);
    }
}
