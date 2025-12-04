<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kriteria;

class KriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kriteria = [
            [
                'kode_kriteria' => 'K1',
                'nama_kriteria' => 'Diferensiasi Misi',
                'keterangan' => 'Differentiation of the mission',
            ],
            [
                'kode_kriteria' => 'K2',
                'nama_kriteria' => 'Edukasi, Sistem Evaluasi, dan Capaian Pembelajaran',
                'keterangan' => 'Education, Evaluation System & Learning Outcomes',
            ],
            [
                'kode_kriteria' => 'K3',
                'nama_kriteria' => 'Pengembangan Sumber Daya Manusia',
                'keterangan' => 'Policies on Human Resources Development',
            ],
            [
                'kode_kriteria' => 'K4',
                'nama_kriteria' => 'Internalisasi Penjaminan Mutu',
                'keterangan' => 'Internal Quality Assurance',
            ],
            [
                'kode_kriteria' => 'K5',
                'nama_kriteria' => 'Lingkungan dan Sumber Belajar serta Pendukung Mahasiswa',
                'keterangan' => 'Learning Environment, Resource, and Students Support',
            ],
            [
                'kode_kriteria' => 'K6',
                'nama_kriteria' => 'Akuntabilitas, Tata Kelola, dan Kerjasama',
                'keterangan' => 'Accountability, Governance, Collaboration',
            ],
            [
                'kode_kriteria' => 'K7',
                'nama_kriteria' => 'Riset, Pengabdian, dan Suasana Ilmiah',
                'keterangan' => 'Research, Community Service, and Scientific Environment',
            ],
        ];

        foreach ($kriteria as $item) {
            Kriteria::create($item);
        }
    }
}
