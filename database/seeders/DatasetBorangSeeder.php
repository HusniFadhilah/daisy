<?php
// database/seeders/DatasetBorangSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kriteria;
use App\Models\ElemenStandar;
use App\Models\DatasetBorang;

class DatasetBorangSeeder extends Seeder
{
    public function run()
    {
        // Cari atau buat Kriteria E (Mahasiswa)
        $kriteriaE = Kriteria::firstOrCreate(
            ['kode_kriteria' => 'E'],
            [
                'nama_kriteria' => 'Mahasiswa',
                'keterangan' => 'Kriteria tentang seleksi, admisi, dan mahasiswa'
            ]
        );

        // Cari atau buat Elemen E.1
        $elemenE1 = ElemenStandar::firstOrCreate(
            ['kode_elemen' => 'E.1'],
            [
                'id_kriteria' => $kriteriaE->id,
                'pernyataan_elemen' => 'Admisi Mahasiswa',
                'keterangan' => 'Elemen tentang proses admisi dan penerimaan mahasiswa'
            ]
        );

        // Dataset E.1.1
        DatasetBorang::firstOrCreate(
            ['kode' => 'E.1.1'],
            [
                'id_elemen' => $elemenE1->id,
                'nama' => 'Data Mahasiswa Penuh Waktu',
                'deskripsi' => 'Tabel data mahasiswa penuh waktu per tahun',
                'tipe' => 'tabel',
                'expected_columns' => ['Tahun Akademik', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
                'urutan' => 1,
            ]
        );

        // Dataset E.1.2
        DatasetBorang::firstOrCreate(
            ['kode' => 'E.1.2'],
            [
                'id_elemen' => $elemenE1->id,
                'nama' => 'Data Mahasiswa Asing',
                'deskripsi' => 'Tabel data mahasiswa asing',
                'tipe' => 'tabel',
                'expected_columns' => ['Tahun', 'Jumlah Mahasiswa Asing'],
                'urutan' => 2,
            ]
        );

        // Kriteria D
        $kriteriaD = Kriteria::firstOrCreate(
            ['kode_kriteria' => 'D'],
            ['nama_kriteria' => 'Diferensiasi']
        );

        $elemenD1 = ElemenStandar::firstOrCreate(
            ['kode_elemen' => 'D.1'],
            [
                'id_kriteria' => $kriteriaD->id,
                'pernyataan_elemen' => 'Legalitas Program dan Tata Pamong'
            ]
        );

        // Tambahkan lebih banyak sesuai kebutuhan...

        $this->command->info('Dataset Borang seeded successfully!');
    }
}
