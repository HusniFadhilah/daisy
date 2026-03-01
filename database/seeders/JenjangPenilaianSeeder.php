<?php

namespace Database\Seeders;

use App\Models\JenjangPenilaian;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenjangPenilaianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenjangPenilaian = [
            ['name' => JenjangPenilaian::LABEL_SKOR_0, 'skor' => 0, 'color' => JenjangPenilaian::COLOR_SKOR_0],
            ['name' => JenjangPenilaian::LABEL_SKOR_1, 'skor' => 1, 'color' => JenjangPenilaian::COLOR_SKOR_1],
            ['name' => JenjangPenilaian::LABEL_SKOR_2, 'skor' => 2, 'color' => JenjangPenilaian::COLOR_SKOR_2],
            ['name' => JenjangPenilaian::LABEL_SKOR_3, 'skor' => 3, 'color' => JenjangPenilaian::COLOR_SKOR_3],
            ['name' => JenjangPenilaian::LABEL_SKOR_4, 'skor' => 4, 'color' => JenjangPenilaian::COLOR_SKOR_4],
        ];

        $timestamp = Carbon::now();

        foreach ($jenjangPenilaian as &$jenjang) {
            $jenjang['created_at'] = $timestamp;
            $jenjang['updated_at'] = $timestamp;
        }

        DB::table('jenjang_penilaian')->insert($jenjangPenilaian);
    }
}
