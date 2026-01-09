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
        $kriteriaMap = \App\Models\Kriteria::pluck('id', 'kode_kriteria')->toArray();

        $elemenStandar = [
            // D - Diferensiasi Misi
            [
                'id_kriteria' => $kriteriaMap['D'],
                'kode_elemen' => 'D.1',
                'pernyataan_elemen' => 'Legalitas Program dan Tata Pamong',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['D'],
                'kode_elemen' => 'D.2',
                'pernyataan_elemen' => 'Visi, Misi, Tujuan, dan Strategi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['D'],
                'kode_elemen' => 'D.3',
                'pernyataan_elemen' => 'Kesesuaian Visi Keilmuan',
                'keterangan' => null,
            ],

            // E - Edukasi, Sistem Evaluasi, dan Capaian Pembelajaran
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E.1',
                'pernyataan_elemen' => 'Kurikulum',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E.2',
                'pernyataan_elemen' => 'Admisi Mahasiswa',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E.3',
                'pernyataan_elemen' => 'Proses dan Siklus Pembelajaran',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E.4',
                'pernyataan_elemen' => 'Penilaian dan Evaluasi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E.5',
                'pernyataan_elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran',
                'keterangan' => null,
            ],

            // P - Pengembangan Sumber Daya Manusia
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P.1',
                'pernyataan_elemen' => 'Dosen dan Tenaga Kependidikan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P.2',
                'pernyataan_elemen' => 'Sarana dan Prasarana Kerja',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P.3',
                'pernyataan_elemen' => 'Pengembangan Kapasitas',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P.4',
                'pernyataan_elemen' => 'Kesejahteraan Kerja',
                'keterangan' => null,
            ],

            // I - Internalisasi Penjaminan Mutu
            [
                'id_kriteria' => $kriteriaMap['I'],
                'kode_elemen' => 'I.1',
                'pernyataan_elemen' => 'Sistem Penjaminan Mutu Internal',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['I'],
                'kode_elemen' => 'I.2',
                'pernyataan_elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['I'],
                'kode_elemen' => 'I.3',
                'pernyataan_elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal',
                'keterangan' => null,
            ],

            // L - Lingkungan dan Sumber Belajar serta Pendukung Mahasiswa
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L.1',
                'pernyataan_elemen' => 'Sarana dan Prasarana Belajar',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L.2',
                'pernyataan_elemen' => 'Sumber Pengetahuan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L.3',
                'pernyataan_elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L.4',
                'pernyataan_elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna',
                'keterangan' => null,
            ],

            // A - Akuntabilitas, Tata Kelola, dan Kerjasama
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A.1',
                'pernyataan_elemen' => 'Organisasi dan Tata Kelola',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A.2',
                'pernyataan_elemen' => 'Kerja Sama dan Kemitraan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A.3',
                'pernyataan_elemen' => 'Sistem dan Manajemen Informasi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A.4',
                'pernyataan_elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A.5',
                'pernyataan_elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko',
                'keterangan' => null,
            ],

            // R - Riset, Pengabdian, dan Suasana Ilmiah
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R.1',
                'pernyataan_elemen' => 'Kebijakan Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R.2',
                'pernyataan_elemen' => 'Proses Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R.3',
                'pernyataan_elemen' => 'Luaran dan Dampak Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R.4',
                'pernyataan_elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R.5',
                'pernyataan_elemen' => 'Proses Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R.6',
                'pernyataan_elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
        ];

        foreach ($elemenStandar as $elemen) {
            ElemenStandar::create($elemen);
        }
    }
}
