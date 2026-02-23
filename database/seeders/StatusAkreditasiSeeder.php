<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StatusAkreditasi;

class StatusAkreditasiSeeder extends Seeder
{
    public function run(): void
    {
        // StatusAkreditasi::truncate();

        StatusAkreditasi::insert([
            [
                'skor_min' => 0,
                'skor_max' => 200,
                'persen_min' => 0,
                'persen_max' => 50,
                'makna' => 'Tidak memenuhi',
                'status' => 'Tidak Terakreditasi',
                'warna' => '#f5c6cb',
                'siklus_tahun' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'skor_min' => 201,
                'skor_max' => 280,
                'persen_min' => 51,
                'persen_max' => 70,
                'makna' => 'Semua elemen memenuhi',
                'status' => 'Terakreditasi',
                'warna' => '#fff9c4',
                'siklus_tahun' => 5,
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'skor_min' => 281,
                'skor_max' => 320,
                'persen_min' => 71,
                'persen_max' => 80,
                'makna' => "Semua elemen memenuhi\n\nSebagian kriteria pelampauan terpenuhi (4-5 kriteria dari 7 kriteria)\n\nMemenuhi syarat perlu pada elemen yang disyaratkan",
                'status' => 'Terakreditasi Unggul',
                'warna' => '#dcedc8',
                'siklus_tahun' => 3,
                'urutan' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'skor_min' => 321,
                'skor_max' => 400,
                'persen_min' => 81,
                'persen_max' => 100,
                'makna' => "Semua elemen memenuhi\n\nSebagian besar kriteria pelampauan terpenuhi (6-7 kriteria dari 7 kriteria)\n\nMemenuhi syarat perlu pada elemen yang disyaratkan",
                'status' => 'Terakreditasi Unggul',
                'warna' => '#c8e6c9',
                'siklus_tahun' => 5,
                'urutan' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
