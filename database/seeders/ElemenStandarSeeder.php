<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElemenStandar;

class ElemenStandarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get kriteria IDs by kode_kriteria
        $kriteriaMap = \App\Models\Kriteria::pluck('id_kriteria', 'kode_kriteria')->toArray();
        
        $elemenStandar = [
            // K1 - Diferensiasi Misi
            [
                'id_kriteria' => $kriteriaMap['K1'],
                'kode_elemen' => 'E1.1',
                'pernyataan_elemen' => 'Legalitas Program dan Tata Pamong',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K1'],
                'kode_elemen' => 'E1.2',
                'pernyataan_elemen' => 'Visi, Misi, Tujuan, dan Strategi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K1'],
                'kode_elemen' => 'E1.3',
                'pernyataan_elemen' => 'Kesesuaian Visi Keilmuan',
                'keterangan' => null,
            ],

            // K2 - Edukasi, Sistem Evaluasi, dan Capaian Pembelajaran
            [
                'id_kriteria' => $kriteriaMap['K2'],
                'kode_elemen' => 'E2.1',
                'pernyataan_elemen' => 'Kurikulum',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K2'],
                'kode_elemen' => 'E2.2',
                'pernyataan_elemen' => 'Admisi Mahasiswa',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K2'],
                'kode_elemen' => 'E2.3',
                'pernyataan_elemen' => 'Proses dan Siklus Pembelajaran',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K2'],
                'kode_elemen' => 'E2.4',
                'pernyataan_elemen' => 'Penilaian dan Evaluasi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K2'],
                'kode_elemen' => 'E2.5',
                'pernyataan_elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran',
                'keterangan' => null,
            ],

            // K3 - Pengembangan Sumber Daya Manusia
            [
                'id_kriteria' => $kriteriaMap['K3'],
                'kode_elemen' => 'E3.1',
                'pernyataan_elemen' => 'Dosen dan Tenaga Kependidikan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K3'],
                'kode_elemen' => 'E3.2',
                'pernyataan_elemen' => 'Sarana dan Prasarana Kerja',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K3'],
                'kode_elemen' => 'E3.3',
                'pernyataan_elemen' => 'Pengembangan Kapasitas',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K3'],
                'kode_elemen' => 'E3.4',
                'pernyataan_elemen' => 'Kesejahteraan Kerja',
                'keterangan' => null,
            ],

            // K4 - Internalisasi Penjaminan Mutu
            [
                'id_kriteria' => $kriteriaMap['K4'],
                'kode_elemen' => 'E4.1',
                'pernyataan_elemen' => 'Sistem Penjaminan Mutu Internal',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K4'],
                'kode_elemen' => 'E4.2',
                'pernyataan_elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K4'],
                'kode_elemen' => 'E4.3',
                'pernyataan_elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal',
                'keterangan' => null,
            ],

            // K5 - Lingkungan dan Sumber Belajar serta Pendukung Mahasiswa
            [
                'id_kriteria' => $kriteriaMap['K5'],
                'kode_elemen' => 'E5.1',
                'pernyataan_elemen' => 'Sarana dan Prasarana Belajar',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K5'],
                'kode_elemen' => 'E5.2',
                'pernyataan_elemen' => 'Sumber Pengetahuan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K5'],
                'kode_elemen' => 'E5.3',
                'pernyataan_elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K5'],
                'kode_elemen' => 'E5.4',
                'pernyataan_elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna',
                'keterangan' => null,
            ],

            // K6 - Akuntabilitas, Tata Kelola, dan Kerjasama
            [
                'id_kriteria' => $kriteriaMap['K6'],
                'kode_elemen' => 'E6.1',
                'pernyataan_elemen' => 'Organisasi dan Tata Kelola',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K6'],
                'kode_elemen' => 'E6.2',
                'pernyataan_elemen' => 'Kerja Sama dan Kemitraan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K6'],
                'kode_elemen' => 'E6.3',
                'pernyataan_elemen' => 'Sistem dan Manajemen Informasi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K6'],
                'kode_elemen' => 'E6.4',
                'pernyataan_elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K6'],
                'kode_elemen' => 'E6.5',
                'pernyataan_elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko',
                'keterangan' => null,
            ],

            // K7 - Riset, Pengabdian, dan Suasana Ilmiah
            [
                'id_kriteria' => $kriteriaMap['K7'],
                'kode_elemen' => 'E7.1',
                'pernyataan_elemen' => 'Kebijakan Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K7'],
                'kode_elemen' => 'E7.2',
                'pernyataan_elemen' => 'Proses Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K7'],
                'kode_elemen' => 'E7.3',
                'pernyataan_elemen' => 'Luaran dan Dampak Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K7'],
                'kode_elemen' => 'E7.4',
                'pernyataan_elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K7'],
                'kode_elemen' => 'E7.5',
                'pernyataan_elemen' => 'Proses Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['K7'],
                'kode_elemen' => 'E7.6',
                'pernyataan_elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
        ];

        foreach ($elemenStandar as $elemen) {
            ElemenStandar::create($elemen);
        }
    }
}
