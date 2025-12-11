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
            ['nama_jenjang' => 'Tidak Memenuhi (Not Met)', 'skor' => 0, 'color' => 'ff0000'],
            ['nama_jenjang' => 'Belum Memenuhi (Not Met)', 'skor' => 1, 'color' => 'ffc000'],
            ['nama_jenjang' => 'Lemah (Weakness/Cause of Concern)', 'skor' => 2, 'color' => 'daf2d0'],
            ['nama_jenjang' => 'Memenuhi (Met)', 'skor' => 3, 'color' => '00b050'],
            ['nama_jenjang' => 'Pelampauan Standar', 'skor' => 4, 'color' => '3c7d22'],
        ];

        $timestamp = Carbon::now();

        foreach ($jenjangPenilaian as &$jenjang) {
            $jenjang['created_at'] = $timestamp;
            $jenjang['updated_at'] = $timestamp;
        }

        DB::table('jenjang_penilaian')->insert($jenjangPenilaian);
    }
}
