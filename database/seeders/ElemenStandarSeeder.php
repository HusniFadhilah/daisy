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
            // D - Diferensiasi Misi
            [
                'id_kriteria' => $kriteriaMap['D'],
                'kode_elemen' => 'D1',
                'pernyataan_elemen' => 'Legalitas Program dan Tata Pamong',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['D'],
                'kode_elemen' => 'D2',
                'pernyataan_elemen' => 'Visi, Misi, Tujuan, dan Strategi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['D'],
                'kode_elemen' => 'D3',
                'pernyataan_elemen' => 'Kesesuaian Visi Keilmuan',
                'keterangan' => null,
            ],

            // E - Edukasi, Sistem Evaluasi, dan Capaian Pembelajaran
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E1',
                'pernyataan_elemen' => 'Kurikulum',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E2',
                'pernyataan_elemen' => 'Admisi Mahasiswa',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E3',
                'pernyataan_elemen' => 'Proses dan Siklus Pembelajaran',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E4',
                'pernyataan_elemen' => 'Penilaian dan Evaluasi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['E'],
                'kode_elemen' => 'E5',
                'pernyataan_elemen' => 'Kompetensi Lulusan dan Capaian Pembelajaran',
                'keterangan' => null,
            ],

            // P - Pengembangan Sumber Daya Manusia
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P1',
                'pernyataan_elemen' => 'Dosen dan Tenaga Kependidikan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P2',
                'pernyataan_elemen' => 'Sarana dan Prasarana Kerja',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P3',
                'pernyataan_elemen' => 'Pengembangan Kapasitas',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['P'],
                'kode_elemen' => 'P4',
                'pernyataan_elemen' => 'Kesejahteraan Kerja',
                'keterangan' => null,
            ],

            // I - Internalisasi Penjaminan Mutu
            [
                'id_kriteria' => $kriteriaMap['I'],
                'kode_elemen' => 'I1',
                'pernyataan_elemen' => 'Sistem Penjaminan Mutu Internal',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['I'],
                'kode_elemen' => 'I2',
                'pernyataan_elemen' => 'Implementasi Perbaikan Berkelanjutan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['I'],
                'kode_elemen' => 'I3',
                'pernyataan_elemen' => 'Keterlibatan Pengampu Kepentingan dan Penjaminan Mutu Eksternal',
                'keterangan' => null,
            ],

            // L - Lingkungan dan Sumber Belajar serta Pendukung Mahasiswa
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L1',
                'pernyataan_elemen' => 'Sarana dan Prasarana Belajar',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L2',
                'pernyataan_elemen' => 'Sumber Pengetahuan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L3',
                'pernyataan_elemen' => 'Kepuasan Mahasiswa dan Alumni',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['L'],
                'kode_elemen' => 'L4',
                'pernyataan_elemen' => 'Lulusan, Kajian Telusur, dan Kepuasan Pengguna',
                'keterangan' => null,
            ],

            // A - Akuntabilitas, Tata Kelola, dan Kerjasama
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A1',
                'pernyataan_elemen' => 'Organisasi dan Tata Kelola',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A2',
                'pernyataan_elemen' => 'Kerja Sama dan Kemitraan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A3',
                'pernyataan_elemen' => 'Sistem dan Manajemen Informasi',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A4',
                'pernyataan_elemen' => 'Keselamatan dan Kesehatan Kerja serta Kelestarian Lingkungan',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['A'],
                'kode_elemen' => 'A5',
                'pernyataan_elemen' => 'Keuangan, Keberlanjutan, dan Mitigasi Risiko',
                'keterangan' => null,
            ],

            // R - Riset, Pengabdian, dan Suasana Ilmiah
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R1',
                'pernyataan_elemen' => 'Kebijakan Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R2',
                'pernyataan_elemen' => 'Proses Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R3',
                'pernyataan_elemen' => 'Luaran dan Dampak Penelitian',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R4',
                'pernyataan_elemen' => 'Kebijakan Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R5',
                'pernyataan_elemen' => 'Proses Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
            [
                'id_kriteria' => $kriteriaMap['R'],
                'kode_elemen' => 'R6',
                'pernyataan_elemen' => 'Luaran dan Dampak Pengabdian kepada Masyarakat',
                'keterangan' => null,
            ],
        ];

        foreach ($elemenStandar as $elemen) {
            ElemenStandar::create($elemen);
        }
    }
}
