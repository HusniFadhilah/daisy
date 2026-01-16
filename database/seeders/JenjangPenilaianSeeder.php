<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JenjangPenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenjangPenilaian = [
            ['name' => 'Tidak Memenuhi (Not Met)', 'skor' => 0, 'color' => '#f5c6cb'],
            ['name' => 'Belum Memenuhi (Not Met)', 'skor' => 1, 'color' => '#ffe0b2'],
            ['name' => 'Lemah (Weakness/Cause of Concern)', 'skor' => 2, 'color' => '#fff9c4'],
            ['name' => 'Memenuhi (Met)', 'skor' => 3, 'color' => '#dcedc8'],
            ['name' => 'Pelampauan Standar', 'skor' => 4, 'color' => '#c8e6c9'],
        ];

        $timestamp = Carbon::now();

        foreach ($jenjangPenilaian as &$jenjang) {
            $jenjang['created_at'] = $timestamp;
            $jenjang['updated_at'] = $timestamp;
        }

        DB::table('jenjang_penilaian')->insert($jenjangPenilaian);
    }
}
