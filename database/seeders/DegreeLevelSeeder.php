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
            ['code' => 'D1', 'name' => 'Diploma I'],
            ['code' => 'D2', 'name' => 'Diploma II'],
            ['code' => 'D3', 'name' => 'Diploma III'],
            ['code' => 'D4', 'name' => 'Diploma IV / Sarjana Terapan'],
            ['code' => 'S1', 'name' => 'Sarjana (Strata 1)'],
            ['code' => 'S2', 'name' => 'Magister (Strata 2)'],
            ['code' => 'S2 Terapan', 'name' => 'Magister Terapan (Strata 2)'],
            ['code' => 'S3', 'name' => 'Doktor (Strata 3)'],
            ['code' => 'S3 Terapan', 'name' => 'Doktor Terapan (Strata 3)'],
            ['code' => 'Profesi', 'name' => 'Pendidikan Profesi'],
            ['code' => 'Spesialis', 'name' => 'Pendidikan Spesialis'],
        ];

        $timestamp = Carbon::now();

        foreach ($degreeLevels as &$level) {
            $level['created_at'] = $timestamp;
            $level['updated_at'] = $timestamp;
        }

        DB::table('degree_levels')->insert($degreeLevels);
    }
}
