<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisIndikator;

class JenisIndikatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisIndikator = [
            [
                'nama_jenis' => 'Kualitatif',
                'keterangan' => 'Indikator yang bersifat kualitatif atau deskriptif',
            ],
            [
                'nama_jenis' => 'Kuantitatif',
                'keterangan' => 'Indikator yang bersifat kuantitatif atau dapat diukur dengan angka',
            ],
        ];

        foreach ($jenisIndikator as $jenis) {
            JenisIndikator::create($jenis);
        }
    }
}
