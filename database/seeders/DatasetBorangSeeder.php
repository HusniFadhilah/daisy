<?php
// database/seeders/DatasetBorangSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DatasetBorang;
use App\Models\ElemenStandar;

class DatasetBorangSeeder extends Seeder
{
    public function run()
    {
        // Get elemen E.2.1 (example)
        $elemen = ElemenStandar::where('kode_elemen', 'E.2.1')->first();

        if ($elemen) {
            // 1. Deskripsi field
            DatasetBorang::create([
                'id_elemen' => $elemen->id,
                'kode' => 'E.2.1.DESC',
                'nama' => 'Deskripsi Kesesuaian Visi Keilmuan',
                'tipe_field' => 'textarea',
                'label_field' => 'Deskripsi Kesesuaian Visi Keilmuan',
                'placeholder' => 'Tuliskan deskripsi lengkap mengenai kesesuaian visi keilmuan...',
                'is_required' => true,
                'keterangan' => 'Jelaskan bagaimana visi keilmuan program studi sesuai dengan standar',
                'urutan' => 1,
            ]);

            // 2. Table field
            DatasetBorang::create([
                'id_elemen' => $elemen->id,
                'kode' => 'E.2.1',
                'nama' => 'Tabel E.2.1 Rekapitulasi Rencana Pembelajaran Semester',
                'tipe_field' => 'table',
                'label_field' => 'Tabel E.2.1 Rekapitulasi Rencana Pembelajaran Semester',
                'is_required' => true,
                'expected_columns' => [
                    'Nama Mata Kuliah',
                    'Kode',
                    'Semester',
                    'Besar Kredit (sks)',
                    'Model Pembelajaran',
                    'Tatan Muka (Menit)',
                    'Sifat (Wajib/Pilihan)',
                    'Bukti Rencana Pembelajaran Semester'
                ],
                'keterangan' => 'Isi tabel dengan data mata kuliah yang ada',
                'urutan' => 2,
            ]);
        }
    }
}
