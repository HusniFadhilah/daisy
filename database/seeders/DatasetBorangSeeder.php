<?php

namespace Database\Seeders;

use App\Models\DatasetBorang;
use App\Models\ElemenStandar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatasetBorangSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('dataset_borang')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // Elemen yang kamu minta
        $targetKodeElemen = ['D.1', 'E.1', 'E.2', 'E.3', 'E.4', 'L.4', 'A.1'];

        $elemens = ElemenStandar::query()
            ->whereIn('kode_elemen', $targetKodeElemen)
            ->orderBy('kode_elemen')
            ->get();

        foreach ($elemens as $elemen) {
            $this->seedOneElemen($elemen);
        }
    }

    private function seedOneElemen(ElemenStandar $elemen): void
    {
        // Replace dataset ringkas lama (DESC + TBL)
        DatasetBorang::query()
            ->where('id_elemen', $elemen->id)
            ->whereIn('kode', [
                $elemen->kode_elemen . '.DESC',
                $elemen->kode_elemen . '.TBL',
            ])
            ->delete();

        DatasetBorang::create([
            'id_elemen'    => $elemen->id,
            'kode'         => $elemen->kode_elemen . '.DESC',
            'nama'         => 'Deskripsi ' . $elemen->kode_elemen,
            'tipe_field'   => 'narasi',
            'label_field'  => 'Deskripsi',
            'placeholder'  => 'Tuliskan deskripsi sesuai kondisi program studi...',
            'is_required'  => true,
            'keterangan'   => 'Isi narasi sesuai indikator pada elemen ' . $elemen->kode_elemen,
            'urutan'       => 1,
        ]);

        $cfg = $this->tableConfig($elemen->kode_elemen);

        DatasetBorang::create([
            'id_elemen'          => $elemen->id,
            'kode'              => $elemen->kode_elemen . '.TBL',
            'nama'              => $cfg['title'],
            'tipe_field'        => 'table',
            'label_field'       => $cfg['title'],
            'is_required'       => true,
            'expected_columns'  => $cfg['columns'],
            'template_html'     => $this->makeTemplateHtml($cfg['columns'], $cfg['sample_rows'] ?? []),
            'keterangan'        => $cfg['note'] ?? ('Isi data tabel untuk elemen ' . $elemen->kode_elemen),
            'urutan'            => 2,
        ]);
    }

    private function tableConfig(string $kodeElemen): array
    {
        $map = [

            // D.1 Tabel Daftar Program Studi di UPPS
            'D.1' => [
                'title' => 'D.1 Tabel Daftar Program Studi di UPPS',
                'columns' => [
                    'No',
                    'Jenis Program',
                    'Nama Program Studi',
                    'No. SK Pendirian',
                    'Lembaga Akreditasi',
                    'Status/Peringkat',
                    'No. dan Tgl. SK',
                    'Tgl. Kadaluarsa (HH/BB/TTTT)',
                ],
                // kosongkan baris contoh biar user isi; atau kasih 12 baris kosong otomatis (template sudah bikin 3, kamu bisa tambah kalau mau)
            ],

            // E.1 pakai Tabel E.1.1 (Mahasiswa Penuh Waktu)
            'E.1' => [
                'title' => 'E.1.1 Tabel Mahasiswa Penuh Waktu',
                'columns' => [
                    'Tahun Akademik (Angkatan)',
                    'Daya Tampung',
                    'Pendaftar',
                    'Lulus Seleksi',
                    'Afirmasi',
                    'Transfer',
                    'Asing',
                    'Aktif',
                    'Tidak Aktif',
                    'Lulus',
                    'Gagal',
                ],
                'sample_rows' => [
                    ['TS-4', '25', '100', '20', '1', '1', '1', '2', '1', '19', '1'],
                    ['TS-3', '25', '200', '20', '1', '1', '1', '10', '1', '10', '1'],
                    ['TS-2', '25', '239', '20', '1', '1', '1', '20', '1', '0', '1'],
                    ['TS-1', '25', '333', '20', '2', '2', '2', '20', '2', '0', '2'],
                    ['TS',   '25', '588', '20', '2', '2', '2', '20', '2', '0', '2'],
                    ['Jumlah', '',  '1460', '100', '7', '7', '7', '72', '7', '29', '7'],
                ],
                'note' => 'Sumber: format E.1.1 (Admisi & registrasi mahasiswa penuh waktu).',
            ],

            // E.2 pakai Tabel E.2.1 (Rekapitulasi RPS)
            'E.2' => [
                'title' => 'E.2.1 Rekapitulasi Rencana Pembelajaran Semester',
                'columns' => [
                    'Nama Mata Kuliah',
                    'Kode',
                    'Semester',
                    'Besar Kredit (sks)',
                    'Model Pembelajaran',
                    'Lama Tatap Muka (Menit)',
                    'Sifat (Wajib/Pilihan)',
                    'Bukti RPS (Tautan)',
                ],
            ],

            // E.3 pakai Tabel E.3.1 (Kinerja Perkuliahan)
            'E.3' => [
                'title' => 'E.3.1 Rekapitulasi Kinerja Perkuliahan (TS)',
                'columns' => [
                    'Nama Mata Kuliah',
                    'Kode',
                    'Jumlah Peserta',
                    'Pengambil Ulang (retaker)',
                    'Rerata Kehadiran (%)',
                    'Rasio Kelulusan (%)',
                    'Evaluasi',
                    'Tindak Lanjut',
                ],
                'sample_rows' => [
                    [
                        'Konstruksi Bangunan Gedung',
                        'AR123',
                        '30',
                        '10',
                        '60%',
                        '75%',
                        '25% ketidaklulusan karena membolos & tabrakan jadwal; 10 retaker lulus 50%',
                        'Perbaikan penjadwalan & pendampingan asisten untuk retaker',
                    ],
                ],
            ],

            // E.4 pakai Tabel E.4.2 (Matriks CPL & MK)
            'E.4' => [
                'title' => 'E.4.2 Matriks Capaian Pembelajaran dan Mata Kuliah',
                'columns' => [
                    'Nama Mata Kuliah',
                    'Kode',
                    'CPL 01',
                    'CPL 02',
                    'CPL 03',
                    'CPL 04',
                    'CPL 05 dst.',
                    'Keterangan',
                ],
                'sample_rows' => [
                    ['Desain Produk 1', 'DP111', '', '40%', '', '', '20%', ''],
                    ['Desain Produk 2', 'DP112', '', '30%', '', '', '20%', ''],
                    ['Komunikasi Desain', 'DP234', '', '20%', '', '20%', '', ''],
                    ['Teori Perencanaan Wilayah', 'PL111', '30%', '', '', '', '', ''],
                    ['Proyek Perencanaan Wilayah 1', 'PL321', '', '40%', '', '', '', ''],
                ],
            ],

            // L.4 pakai L.4.1 (Waktu tunggu)
            'L.4' => [
                'title' => 'L.4.1 Waktu Tunggu Lulusan',
                'columns' => [
                    'Tahun Lulus',
                    'Jumlah Lulusan',
                    'Jumlah Lulusan yang Terlacak',
                    'WT < 6 bulan',
                    '6 ≤ WT ≤ 18 bulan',
                    'WT > 18 bulan',
                ],
                'sample_rows' => [
                    ['TS-4', '', '', '', '', ''],
                    ['TS-3', '', '', '', '', ''],
                    ['TS-2', '', '', '', '', ''],
                    ['Jumlah', '0', '0', '0', '0', '0'],
                ],
            ],

            // A.1 Tabel Kemitraan & Kerjasama
            'A.1' => [
                'title' => 'A.1 Kemitraan dan Kerjasama',
                'columns' => [
                    'No',
                    'Lembaga Mitra',
                    'Tingkat Internasional',
                    'Tingkat Nasional',
                    'Tingkat Lokal/Wilayah',
                    'Judul Kegiatan Kerjasama',
                    'Manfaat bagi PS yang Diakreditasi',
                    'Tanggal Awal (HH/BB/TTTT)',
                    'Tanggal Akhir (HH/BB/TTTT)',
                    'Durasi (tahun)',
                    'Status Kerjasama',
                    'Bukti Kerjasama',
                ],
            ],
        ];

        return $map[$kodeElemen] ?? [
            'title' => 'Tabel ' . $kodeElemen,
            'columns' => ['No', 'Keterangan', 'Data', 'Bukti/Link'],
        ];
    }

    private function makeTemplateHtml(array $columns, array $sampleRows = []): string
    {
        $ths = '';
        foreach ($columns as $col) {
            $ths .= '<th>' . e($col) . '</th>';
        }

        $rows = '';
        if (!empty($sampleRows)) {
            foreach ($sampleRows as $r) {
                $rows .= '<tr>';
                foreach ($columns as $i => $_) {
                    $val = $r[$i] ?? '';
                    $rows .= '<td>' . e($val) . '</td>';
                }
                $rows .= '</tr>';
            }
        } else {
            // default 3 baris kosong
            for ($i = 1; $i <= 3; $i++) {
                $rows .= '<tr>';
                foreach ($columns as $idx => $_) {
                    $rows .= $idx === 0 ? '<td>' . $i . '</td>' : '<td></td>';
                }
                $rows .= '</tr>';
            }
        }

        return '<table border="1" cellpadding="4" cellspacing="0"><thead><tr>'
            . $ths
            . '</tr></thead><tbody>'
            . $rows
            . '</tbody></table>';
    }
}
