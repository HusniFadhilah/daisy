<?php

namespace App\Services\BorangExport;

use App\Models\BorangDataExcel;
use App\Models\PengajuanAkreditasi;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LkpsExcelExportService
{
    // =========================================================
    // WARNA
    // =========================================================
    private const COLOR_HEADER_DARK  = '1F3864'; // navy   → judul sheet
    private const COLOR_HEADER_MED   = '2E75B6'; // biru   → judul tabel
    private const COLOR_HEADER_LIGHT = '4472C4'; // biru muda → row header kolom
    private const COLOR_COL_NUMBER   = 'D6E4F0'; // biru pucat → row nomor kolom
    private const COLOR_INSTRUCTION  = 'FFF9C4'; // kuning muda → instruksi
    private const COLOR_SECTION      = 'E8F4FD'; // biru sangat muda → row section (A, B, C)
    private const COLOR_WHITE        = 'FFFFFF';
    private const COLOR_FONT_WHITE   = 'FFFFFF';
    private const COLOR_FONT_DARK    = '1A1A2E';

    // =========================================================
    // DEFINISI SEMUA SHEET
    // =========================================================

    /**
     * Kembalikan konfigurasi lengkap semua sheet LKPS S1.
     * Setiap sheet berisi: title, tables[].
     * Setiap table berisi: title, notes?, headers[][], col_widths[].
     */
    private function getSheetDefinitions(): array
    {
        return [

            // --------------------------------------------------
            // E.2 — Admisi Mahasiswa
            // --------------------------------------------------
            'E.2' => [
                'title'  => 'E.2 Admisi Mahasiswa',
                'tables' => [
                    [
                        'title'      => 'Tabel E.2.1 Tabel Mahasiswa Penuh Waktu',
                        'notes'      => 'Isi data 5 tahun terakhir (TS-4 s.d. TS). TS = Tahun akademik penuh terakhir saat pengajuan.',
                        'headers'    => [
                            ['Tahun Akademik (Angkatan)', 'Admisi Mahasiswa', null, 'Jumlah Mahasiswa Teregistrasi', null, null, null, 'Jumlah Mahasiswa Teregistrasi', null, null, null],
                            [null, 'Daya Tampung', 'Pendaftar', 'Lulus Seleksi', 'Afirmasi', 'Transfer', 'Asing', 'Aktif', 'Tidak Aktif', 'Lulus', 'Gagal'],
                        ],
                        'col_widths' => [20, 14, 14, 14, 12, 12, 10, 12, 14, 10, 10],
                        'merges'     => [
                            // [r1,c1,r2,c2] relative to header start
                            [0, 0, 1, 0], // Tahun Akademik
                            [0, 1, 0, 2], // Admisi Mahasiswa (span 2)
                            [0, 3, 0, 6], // Jumlah Mahasiswa Teregistrasi (span 4)
                            [0, 7, 0, 10], // Jumlah Mahasiswa Teregistrasi (span 4)
                        ],
                        'sample_rows' => 5,
                    ],
                    [
                        'title'      => 'Tabel E.2.2 Tabel Mahasiswa Asing',
                        'headers'    => [
                            ['Tahun Akademik (Angkatan)', 'Jumlah Mahasiswa Teregistrasi', 'Penuh Waktu', null, 'Tidak Penuh Waktu', null],
                            [null, 'Jumlah mahasiswa penuh waktu (tabel E.2.1)', 'Jumlah mahasiswa berdurasi 1 semester atau lebih', 'Jumlah mahasiswa asing periode pendek dengan kredit', 'Mahasiswa asing dengan aktivitas tanpa kredit', null],
                        ],
                        'col_widths' => [22, 30, 30, 30, 30, 20],
                        'sample_rows' => 5,
                    ],
                    [
                        'title'      => 'Tabel E.2.3 Tabel Analisis Admisi',
                        'notes'      => 'Isi rasio keketatan, inklusifitas, dan keterlibatan mahasiswa asing/internasional.',
                        'headers'    => [
                            ['Indikator', 'TS-2', 'TS-1', 'TS', 'Keterangan'],
                        ],
                        'col_widths' => [40, 14, 14, 14, 30],
                        'sample_rows' => 4,
                    ],
                ],
            ],

            // --------------------------------------------------
            // E.3 — Proses dan Siklus Pembelajaran
            // --------------------------------------------------
            'E.3' => [
                'title'  => 'E.3 Proses dan Siklus Pembelajaran',
                'tables' => [
                    [
                        'title'      => 'Tabel E.3.1 Rekapitulasi Rencana Pembelajaran Semester',
                        'headers'    => [
                            ['Nama Mata Kuliah', 'Kode', 'Semester', 'Besar Kredit (sks)', 'Model Pembelajaran', 'Lama Tatap Muka (Menit)', 'Sifat (Wajib/Pilihan)', 'Bukti Rencana Pembelajaran Semester'],
                        ],
                        'col_widths' => [30, 12, 12, 16, 20, 22, 20, 30],
                        'sample_rows' => 8,
                    ],
                    [
                        'title'      => 'Tabel E.3.2 Masa Studi Lulusan',
                        'notes'      => 'Diisi oleh pengusul dari Program Studi pada Program Sarjana/Sarjana Terapan.',
                        'headers'    => [
                            ['Tahun Masuk', 'Jumlah Mahasiswa Reguler per Angkatan pada Tahun', null, null, null, null, null, null, 'Jumlah Lulusan s.d. TS (dari Mahasiswa Reguler)'],
                            [null, 'TS-6', 'TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS', null],
                        ],
                        'col_widths' => [14, 12, 12, 12, 12, 12, 12, 12, 22],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 0, 7],
                            [0, 8, 1, 8],
                        ],
                        'sample_rows' => 7,
                    ],
                    [
                        'title'      => 'Tabel E.3.3 Indeks Prestasi Kumulatif',
                        'headers'    => [
                            ['No.', 'Tahun Lulus', 'Jumlah Lulusan', 'Indeks Prestasi Kumulatif', null, null],
                            [null, null, null, 'Min.', 'Rata-rata', 'Maks.'],
                        ],
                        'col_widths' => [8, 16, 16, 14, 14, 14],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 0, 5],
                        ],
                        'sample_rows' => 3,
                    ],
                    [
                        'title'      => 'Tabel E.3.4 Prestasi Akademik Mahasiswa',
                        'headers'    => [
                            ['No.', 'Nama Kegiatan', 'Waktu Perolehan (HH/BB/TTTT)', 'Tingkat', null, null, 'Prestasi yang Dicapai'],
                            [null, null, null, 'Lokal/Wilayah', 'Nasional', 'Internasional', null],
                        ],
                        'col_widths' => [8, 36, 26, 14, 12, 14, 24],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 0, 5],
                            [0, 6, 1, 6],
                        ],
                        'sample_rows' => 8,
                    ],
                ],
            ],

            // --------------------------------------------------
            // P.1 — Dosen dan Tenaga Kependidikan
            // --------------------------------------------------
            'P.1' => [
                'title'  => 'P.1 Dosen dan Tenaga Kependidikan',
                'tables' => [
                    [
                        'title'      => 'Tabel P.1.1 Dosen Tetap Perguruan Tinggi (DTPS)',
                        'headers'    => [
                            ['No.', 'Nama Dosen', 'NIDN/NIDK', 'Pendidikan Pascasarjana', null, null, null, null, null, null, 'Mata Kuliah yang Diampu', null],
                            [null, null, null, 'Magister/Spesialis', 'Doktor/Spesialis', 'Bidang Keahlian', 'Kesesuaian dengan Kompetensi Inti PS', 'Jabatan Akademik', 'Sertifikat Pendidik', 'Sertifikat Kompetensi/Profesi', 'Pada PS yang Diakreditasi', 'Kesesuaian Bidang Keahlian'],
                        ],
                        'col_widths' => [6, 28, 16, 20, 20, 22, 22, 16, 16, 22, 28, 22],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 0, 9],
                            [0, 10, 0, 11],
                        ],
                        'sample_rows' => 8,
                    ],
                    [
                        'title'      => 'Tabel P.1.2 Dosen Pembimbing Utama Tugas Akhir',
                        'headers'    => [
                            ['No.', 'Nama Dosen', 'NIDN/NIDK', 'Jumlah Mahasiswa yang Dibimbing', null, null, null],
                            [null, null, null, 'TS-2', 'TS-1', 'TS', 'Rata-rata'],
                        ],
                        'col_widths' => [6, 30, 16, 12, 12, 12, 14],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 0, 6],
                        ],
                        'sample_rows' => 8,
                    ],
                    [
                        'title'      => 'Tabel P.1.3 Ekuivalen Waktu Mengajar Penuh (EWMP) DTPS',
                        'headers'    => [
                            ['No.', 'Nama Dosen', 'NIDN/NIDK', 'EWMP (sks)', null, null, null, null, null],
                            [null, null, null, 'Pendidikan & Pengajaran', 'Penelitian', 'PkM', 'Tugas Tambahan/Penunjang', 'Total', 'Keterangan'],
                        ],
                        'col_widths' => [6, 28, 16, 22, 14, 12, 26, 12, 20],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 0, 7],
                            [0, 8, 1, 8],
                        ],
                        'sample_rows' => 8,
                    ],
                    [
                        'title'      => 'Tabel P.1.4 Dosen Tidak Tetap (DTT)',
                        'headers'    => [
                            ['No.', 'Nama Dosen', 'NIDN/NIDK', 'Bidang Keahlian', 'Mata Kuliah yang Diampu', 'Kesesuaian Bidang Keahlian', 'Keterangan'],
                        ],
                        'col_widths' => [6, 28, 16, 22, 28, 22, 20],
                        'sample_rows' => 5,
                    ],
                    [
                        'title'      => 'Tabel P.1.5 Dosen Industri/Praktisi',
                        'headers'    => [
                            ['No.', 'Nama', 'Instansi', 'Bidang Keahlian', 'Mata Kuliah', 'Jumlah Pertemuan per Semester', 'Bukti'],
                        ],
                        'col_widths' => [6, 28, 28, 22, 24, 28, 20],
                        'sample_rows' => 5,
                    ],
                    [
                        'title'      => 'Tabel P.1.6 Pengakuan/Rekognisi DTPS',
                        'headers'    => [
                            ['No.', 'Nama Dosen', 'NIDN/NIDK', 'Rekognisi', 'Tahun', 'Tingkat', 'Bukti'],
                        ],
                        'col_widths' => [6, 28, 16, 36, 10, 16, 20],
                        'sample_rows' => 5,
                    ],
                    [
                        'title'      => 'Tabel P.1.7 Tenaga Kependidikan',
                        'headers'    => [
                            ['No.', 'Nama', 'Jenis Tenaga Kependidikan', 'Jumlah', 'Sertifikat Keahlian/Fungsional', 'Unit Kerja', 'Keterangan'],
                        ],
                        'col_widths' => [6, 28, 28, 10, 28, 22, 20],
                        'sample_rows' => 5,
                    ],
                ],
            ],

            // --------------------------------------------------
            // L.1 — Sarana dan Prasarana Belajar
            // --------------------------------------------------
            'L.1' => [
                'title'  => 'L.1 Sarana dan Prasarana Belajar',
                'tables' => [
                    [
                        'title'      => 'Tabel L.1.1 Sarana dan Prasarana Belajar (Kelas)',
                        'headers'    => [
                            ['NO', 'Nama Sarana dan Prasarana', 'Lokasi', 'Ukuran', 'Acuan Standar', null, 'Kondisi Eksisting', null],
                            [null, null, null, null, 'Ukuran', 'Occupancy', 'Ukuran', 'Occupancy'],
                        ],
                        'col_widths' => [6, 32, 20, 14, 14, 14, 14, 14],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 1, 3],
                            [0, 4, 0, 5],
                            [0, 6, 0, 7],
                        ],
                        'sample_rows' => 6,
                    ],
                    [
                        'title'      => 'Tabel L.1.2 Sarana dan Prasarana Belajar (Ruang Kerja Mahasiswa, Studio, Diskusi)',
                        'headers'    => [
                            ['NO', 'Nama Sarana dan Prasarana', 'Lokasi', 'Ukuran', 'Acuan Standar', null, 'Kondisi Eksisting', null],
                            [null, null, null, null, 'Ukuran', 'Occupancy', 'Ukuran', 'Occupancy'],
                        ],
                        'col_widths' => [6, 32, 20, 14, 14, 14, 14, 14],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 1, 3],
                            [0, 4, 0, 5],
                            [0, 6, 0, 7],
                        ],
                        'sample_rows' => 6,
                    ],
                    [
                        'title'      => 'Tabel L.1.3 Sarana dan Prasarana Belajar (Ruang Deseminasi Produk/Karya, Conference, Aula)',
                        'headers'    => [
                            ['NO', 'Nama Sarana dan Prasarana', 'Lokasi', 'Ukuran', 'Acuan Standar', null, 'Kondisi Eksisting', null],
                            [null, null, null, null, 'Ukuran', 'Occupancy', 'Ukuran', 'Occupancy'],
                        ],
                        'col_widths' => [6, 32, 20, 14, 14, 14, 14, 14],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 1, 3],
                            [0, 4, 0, 5],
                            [0, 6, 0, 7],
                        ],
                        'sample_rows' => 5,
                    ],
                    [
                        'title'      => 'Tabel L.1.4 Sarana dan Prasarana Belajar (Ruang K3 dan Ruang Bimbingan)',
                        'headers'    => [
                            ['NO', 'Nama Sarana dan Prasarana', 'Lokasi', 'Ukuran', 'Acuan Standar', null, 'Kondisi Eksisting', null],
                            [null, null, null, null, 'Ukuran', 'Occupancy', 'Ukuran', 'Occupancy'],
                        ],
                        'col_widths' => [6, 32, 20, 14, 14, 14, 14, 14],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 1, 2],
                            [0, 3, 1, 3],
                            [0, 4, 0, 5],
                            [0, 6, 0, 7],
                        ],
                        'sample_rows' => 5,
                    ],
                ],
            ],

            // --------------------------------------------------
            // A.2 — Kerja Sama dan Kemitraan
            // --------------------------------------------------
            'A.2' => [
                'title'  => 'A.2 Organisasi, Tata Kelola, dan Kerjasama',
                'tables' => [
                    [
                        'title'      => 'Tabel A.2 Kemitraan dan Kerjasama',
                        'headers'    => [
                            ['No.', 'Lembaga Mitra', 'Tingkat', null, null, 'Judul Kegiatan Kerjasama', 'Manfaat bagi PS', 'Tanggal Awal (HH/BB/TTTT)', 'Tanggal Akhir (HH/BB/TTTT)', 'Durasi (tahun)', 'Status Kerjasama', 'Bukti Kerjasama'],
                            [null, null, 'Internasional', 'Nasional', 'Lokal/Wilayah', null, null, null, null, null, null, null],
                        ],
                        'col_widths' => [6, 28, 14, 12, 14, 36, 28, 22, 22, 14, 16, 20],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 0, 4],
                            [0, 5, 1, 5],
                            [0, 6, 1, 6],
                            [0, 7, 1, 7],
                            [0, 8, 1, 8],
                            [0, 9, 1, 9],
                            [0, 10, 1, 10],
                            [0, 11, 1, 11],
                        ],
                        'sample_rows' => 10,
                    ],
                ],
            ],

            // --------------------------------------------------
            // A.5.1 — Keuangan: Pemasukan
            // --------------------------------------------------
            'A.5.1' => [
                'title'  => 'A.5 Keuangan, Keberlanjutan, dan Mitigasi Risiko',
                'tables' => [
                    [
                        'title'      => 'Tabel 1.1 Realisasi Pemasukan Dana Program Studi Sarjana S1',
                        'notes'      => 'Satuan: IDR x Juta',
                        'headers'    => [
                            ['No.', 'Pemasukan Dana', 'Unit Pengelola Program Studi (IDR x Juta)', null, null, null, 'Program Studi (IDR x Juta)', null, null, null, 'Ratio Pemasukan (PS dgn Kelolaan RKAT UPPS)'],
                            [null, null, 'TS-2', 'TS-1', 'TS', 'Rata-rata', 'TS-2', 'TS-1', 'TS', 'Rata-rata', null],
                        ],
                        'col_widths' => [6, 44, 14, 14, 14, 14, 14, 14, 14, 14, 22],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 0, 5],
                            [0, 6, 0, 9],
                            [0, 10, 1, 10],
                        ],
                        'sample_rows' => 12,
                        'has_sections' => true,
                    ],
                ],
            ],

            // --------------------------------------------------
            // A.5.2 — Keuangan: Pengeluaran
            // --------------------------------------------------
            'A.5.2' => [
                'title'  => 'A.5 Keuangan, Keberlanjutan, dan Mitigasi Risiko',
                'tables' => [
                    [
                        'title'      => 'Tabel 2.1 Realisasi Pembiayaan Program Studi Sarjana S1',
                        'notes'      => 'Satuan: IDR x Juta',
                        'headers'    => [
                            ['No.', 'Jenis Biaya', 'Unit Pengelola Program Studi (IDR x Juta)', null, null, null, 'Program Studi (IDR x Juta)', null, null, null, 'Ratio Biaya (PS dgn Kelolaan UPPS)'],
                            [null, null, 'TS-2', 'TS-1', 'TS', 'Rata-rata', 'TS-2', 'TS-1', 'TS', 'Rata-rata', null],
                        ],
                        'col_widths' => [6, 44, 14, 14, 14, 14, 14, 14, 14, 14, 22],
                        'merges'     => [
                            [0, 0, 1, 0],
                            [0, 1, 1, 1],
                            [0, 2, 0, 5],
                            [0, 6, 0, 9],
                            [0, 10, 1, 10],
                        ],
                        'sample_rows' => 12,
                        'has_sections' => true,
                    ],
                ],
            ],

            // --------------------------------------------------
            // A.5.3 — Keuangan: Neraca
            // --------------------------------------------------
            'A.5.3' => [
                'title'  => 'A.5 Keuangan, Keberlanjutan, dan Mitigasi Risiko',
                'tables' => [
                    [
                        'title'      => 'Tabel 1.1 Neraca Pemasukan-Pembiayaan (TS-2 s/d TS) Program Sarjana S1',
                        'headers'    => [
                            ['Uraian', 'TS-2', 'TS-1', 'TS', 'Rata-rata', 'Keterangan'],
                        ],
                        'col_widths' => [40, 16, 16, 16, 16, 24],
                        'sample_rows' => 8,
                    ],
                ],
            ],

            // --------------------------------------------------
            // R.3.1.a — Ratio Penelitian DTPS Skala Internasional
            // --------------------------------------------------
            'R.3.1.a' => [
                'title'       => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle'    => 'Ratio Penelitian/Pengembangan Karya/Inovasi DTPS Skala Internasional',
                'instruction' => "Penelitian/Pengembangan Karya/Inovasi Skala Internasional DTPS adalah penelitian yang dilakukan dengan melibatkan Satu Orang DTPS atau beberapa DTPS yang dituangkan dalam MOU, MOA dan/atau Kontrak Penugasan.\n* MOU/MOA disahkan oleh Pimpinan UPPS atau lembaga yang bertanggung jawab.\n* Lokasi Pelaksanaan minimal berada di wilayah/kota pada Dua Negara.",
                'tables'      => [
                    $this->makeResearchRatioTable('Tabel 1.1 Ratio Penelitian/Pengembangan Karya/Inovasi DTPS Skala Internasional'),
                    $this->makeResearchRatioTable('Tabel 1.2 Ratio Penelitian/Pengembangan Karya/Inovasi DTPS Skala Nasional'),
                    $this->makeResearchRatioTable('Tabel 1.3 Ratio Penelitian/Pengembangan Karya/Inovasi DTPS Skala Wilayah'),
                    $this->makeResearchRatioTable('Tabel 1.4 Ratio Penelitian/Pengembangan Karya/Inovasi DTPS Skala Lokal'),
                    $this->makeResearchRatioSummary('Tabel 1.5 Ratio Keseluruhan Penelitian/Pengembangan Karya/Inovasi DTPS'),
                ],
            ],
            'R.3.1.b' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Ratio Penelitian DTPS Skala Internasional Mandiri',
                'instruction' => "Penelitian Mandiri adalah penelitian yang dilakukan oleh DTPS tanpa kolaborasi dengan pihak luar, didanai sendiri atau melalui dana institusi.",
                'tables' => [$this->makeResearchRatioTable('Tabel 1.1 Ratio Penelitian DTPS Skala Internasional Mandiri'), $this->makeResearchRatioTable('Tabel 1.2 Ratio Penelitian DTPS Skala Nasional Mandiri'), $this->makeResearchRatioTable('Tabel 1.3 Ratio Penelitian DTPS Skala Wilayah Mandiri'), $this->makeResearchRatioTable('Tabel 1.4 Ratio Penelitian DTPS Skala Lokal Mandiri'), $this->makeResearchRatioSummary('Tabel 1.5 Ratio Keseluruhan DTPS Mandiri')],
            ],
            'R.3.1.c' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Ratio Penelitian Kolaborasi DTPS dan Mahasiswa',
                'instruction' => "Penelitian Kolaborasi adalah penelitian yang melibatkan DTPS bersama mahasiswa.",
                'tables' => [$this->makeResearchRatioTable('Tabel 1.1 Ratio Penelitian Kolaborasi DTPS dan Mahasiswa Skala Internasional'), $this->makeResearchRatioTable('Tabel 1.2 Ratio Penelitian Kolaborasi DTPS dan Mahasiswa Skala Nasional'), $this->makeResearchRatioTable('Tabel 1.3 Ratio Penelitian Kolaborasi DTPS dan Mahasiswa Skala Wilayah'), $this->makeResearchRatioTable('Tabel 1.4 Ratio Penelitian Kolaborasi DTPS dan Mahasiswa Skala Lokal'), $this->makeResearchRatioSummary('Tabel 1.5 Ratio Keseluruhan Kolaborasi DTPS dan Mahasiswa')],
            ],
            'R.3.1.d' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Ratio Penelitian Kolaborasi DTPS',
                'instruction' => "Penelitian Kolaborasi DTPS adalah penelitian yang dilakukan oleh beberapa DTPS.",
                'tables' => [$this->makeResearchRatioTable('Tabel 1.1 Ratio Penelitian Kolaborasi DTPS Skala Internasional'), $this->makeResearchRatioTable('Tabel 1.2 Ratio Penelitian Kolaborasi DTPS Skala Nasional'), $this->makeResearchRatioTable('Tabel 1.3 Ratio Penelitian Kolaborasi DTPS Skala Wilayah'), $this->makeResearchRatioTable('Tabel 1.4 Ratio Penelitian Kolaborasi DTPS Skala Lokal'), $this->makeResearchRatioSummary('Tabel 1.5 Ratio Keseluruhan Kolaborasi DTPS')],
            ],
            'R.3.1.e' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Ratio Penelitian Mahasiswa',
                'instruction' => "Penelitian Mahasiswa adalah penelitian mandiri atau kolaborasi yang dipimpin oleh mahasiswa.",
                'tables' => [$this->makeResearchRatioTable('Tabel 1.1 Ratio Penelitian Mahasiswa Skala Internasional'), $this->makeResearchRatioTable('Tabel 1.2 Ratio Penelitian Mahasiswa Skala Nasional'), $this->makeResearchRatioTable('Tabel 1.3 Ratio Penelitian Mahasiswa Skala Wilayah'), $this->makeResearchRatioTable('Tabel 1.4 Ratio Penelitian Mahasiswa Skala Lokal'), $this->makeResearchRatioSummary('Tabel 1.5 Ratio Keseluruhan Penelitian Mahasiswa')],
            ],
            'R.3.2.1.a' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian DTPS Skala Internasional dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian DTPS Skala Internasional dalam Bentuk Publikasi')],
            ],
            'R.3.2.1.b' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian DTPS Skala Nasional dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian DTPS Skala Nasional dalam Bentuk Publikasi')],
            ],
            'R.3.2.1.c' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian DTPS Skala Wilayah dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian DTPS Skala Wilayah dalam Bentuk Publikasi')],
            ],
            'R.3.2.1.d' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian DTPS Skala Lokal dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian DTPS Skala Lokal dalam Bentuk Publikasi')],
            ],
            'R.3.2.2.a' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Internasional',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Internasional')],
            ],
            'R.3.2.2.b' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Nasional',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Nasional')],
            ],
            'R.3.2.2.c' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Wilayah',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Wilayah')],
            ],
            'R.3.2.2.d' => [
                'title' => 'R.3 Luaran dan Dampak Penelitian',
                'subtitle' => 'Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Lokal',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran Penelitian Kolaborasi Skala Lokal')],
            ],

            // --------------------------------------------------
            // R.6.* — PkM (struktur sama persis dengan R.3.*)
            // --------------------------------------------------
            'R.6.1.a' => [
                'title' => 'R.6 Luaran dan Dampak Pengabdian kepada Masyarakat',
                'subtitle' => 'Ratio PkM DTPS Skala Internasional',
                'instruction' => "Pengabdian kepada Masyarakat (PkM) Skala Internasional adalah kegiatan PkM yang dilakukan di wilayah/kota pada minimal Dua Negara.",
                'tables' => [$this->makePkmRatioTable('Tabel 1.1 Ratio PkM DTPS Skala Internasional'), $this->makePkmRatioTable('Tabel 1.2 Ratio PkM DTPS Skala Nasional'), $this->makePkmRatioTable('Tabel 1.3 Ratio PkM DTPS Skala Wilayah'), $this->makePkmRatioTable('Tabel 1.4 Ratio PkM DTPS Skala Lokal'), $this->makePkmRatioSummary('Tabel 1.5 Ratio Keseluruhan PkM DTPS')],
            ],
            'R.6.1.b' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Ratio PkM DTPS Skala Internasional Mandiri',
                'instruction' => "PkM Mandiri dilakukan oleh DTPS tanpa kolaborasi institusi luar.",
                'tables' => [$this->makePkmRatioTable('Tabel 1.1 Ratio PkM DTPS Internasional Mandiri'), $this->makePkmRatioTable('Tabel 1.2 Ratio PkM DTPS Nasional Mandiri'), $this->makePkmRatioTable('Tabel 1.3 Ratio PkM DTPS Wilayah Mandiri'), $this->makePkmRatioTable('Tabel 1.4 Ratio PkM DTPS Lokal Mandiri'), $this->makePkmRatioSummary('Tabel 1.5 Ratio Keseluruhan PkM DTPS Mandiri')],
            ],
            'R.6.1.c' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Ratio PkM Kolaborasi DTPS dan Mahasiswa',
                'instruction' => "PkM Kolaborasi adalah kegiatan PkM yang melibatkan DTPS bersama mahasiswa.",
                'tables' => [$this->makePkmRatioTable('Tabel 1.1 Ratio PkM Kolaborasi DTPS dan Mahasiswa Internasional'), $this->makePkmRatioTable('Tabel 1.2 Ratio PkM Kolaborasi DTPS dan Mahasiswa Nasional'), $this->makePkmRatioTable('Tabel 1.3 Ratio PkM Kolaborasi DTPS dan Mahasiswa Wilayah'), $this->makePkmRatioTable('Tabel 1.4 Ratio PkM Kolaborasi DTPS dan Mahasiswa Lokal'), $this->makePkmRatioSummary('Tabel 1.5 Ratio Keseluruhan PkM Kolaborasi DTPS dan Mahasiswa')],
            ],
            'R.6.1.d' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Ratio PkM Kolaborasi DTPS',
                'instruction' => "PkM Kolaborasi DTPS adalah kegiatan yang dilakukan oleh beberapa DTPS.",
                'tables' => [$this->makePkmRatioTable('Tabel 1.1 Ratio PkM Kolaborasi DTPS Internasional'), $this->makePkmRatioTable('Tabel 1.2 Ratio PkM Kolaborasi DTPS Nasional'), $this->makePkmRatioTable('Tabel 1.3 Ratio PkM Kolaborasi DTPS Wilayah'), $this->makePkmRatioTable('Tabel 1.4 Ratio PkM Kolaborasi DTPS Lokal'), $this->makePkmRatioSummary('Tabel 1.5 Ratio Keseluruhan PkM Kolaborasi DTPS')],
            ],
            'R.6.1.e' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Ratio PkM Mahasiswa',
                'instruction' => "PkM Mahasiswa adalah kegiatan PkM yang dipimpin atau dilakukan oleh mahasiswa.",
                'tables' => [$this->makePkmRatioTable('Tabel 1.1 Ratio PkM Mahasiswa Internasional'), $this->makePkmRatioTable('Tabel 1.2 Ratio PkM Mahasiswa Nasional'), $this->makePkmRatioTable('Tabel 1.3 Ratio PkM Mahasiswa Wilayah'), $this->makePkmRatioTable('Tabel 1.4 Ratio PkM Mahasiswa Lokal'), $this->makePkmRatioSummary('Tabel 1.5 Ratio Keseluruhan PkM Mahasiswa')],
            ],
            'R.6.2.a' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Bobot dan Dampak Luaran PkM Skala Internasional dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran PkM Skala Internasional dalam Bentuk Publikasi')],
            ],
            'R.6.2.b' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Bobot dan Dampak Luaran PkM Skala Nasional dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran PkM Skala Nasional dalam Bentuk Publikasi')],
            ],
            'R.6.2.c' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Bobot dan Dampak Luaran PkM Skala Wilayah dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran PkM Skala Wilayah dalam Bentuk Publikasi')],
            ],
            'R.6.2.d' => [
                'title' => 'R.6 Luaran dan Dampak PkM',
                'subtitle' => 'Bobot dan Dampak Luaran PkM Skala Lokal dalam Bentuk Publikasi',
                'tables' => [$this->makeBoboLuaranTable('Tabel 1.1 Bobot dan Dampak Luaran PkM Skala Lokal dalam Bentuk Publikasi')],
            ],
        ];
    }

    // =========================================================
    // HELPER: DEFINISI TABEL BERULANG
    // =========================================================

    private function makeResearchRatioTable(string $title): array
    {
        return [
            'title'      => $title,
            'headers'    => [
                ['NO', 'Judul Penelitian/Pengembangan Karya/Inovasi', 'DTPS yang Terlibat', 'Bukti (Link MOU/Kontrak)', 'Luaran', null, null, null, null, null],
                [null, null, null, null, 'Publikasi Ilmiah', 'Karya/Inovasi', 'Laporan/Naskah Akademik', 'Pameran/Galeri', 'Media Masa/Sosial', 'Luaran Lainnya'],
            ],
            'col_widths' => [6, 40, 20, 24, 16, 16, 24, 20, 20, 18],
            'merges'     => [
                [0, 0, 1, 0],
                [0, 1, 1, 1],
                [0, 2, 1, 2],
                [0, 3, 1, 3],
                [0, 4, 0, 9],
            ],
            'sample_rows' => 5,
        ];
    }

    private function makeResearchRatioSummary(string $title): array
    {
        return [
            'title'      => $title,
            'headers'    => [
                ['Uraian', 'Jumlah Penelitian', 'Jumlah DTPS', 'Ratio'],
            ],
            'col_widths' => [40, 20, 16, 16],
            'sample_rows' => 3,
        ];
    }

    private function makePkmRatioTable(string $title): array
    {
        return [
            'title'      => $title,
            'headers'    => [
                ['NO', 'Judul Kegiatan PkM', 'DTPS yang Terlibat', 'Bukti (Link MOU/Kontrak)', 'Luaran', null, null, null, null, null],
                [null, null, null, null, 'Publikasi', 'Karya/Inovasi', 'Laporan/Naskah', 'Pameran/Media', 'Media Masa/Sosial', 'Luaran Lainnya'],
            ],
            'col_widths' => [6, 40, 20, 24, 16, 16, 22, 20, 20, 18],
            'merges'     => [
                [0, 0, 1, 0],
                [0, 1, 1, 1],
                [0, 2, 1, 2],
                [0, 3, 1, 3],
                [0, 4, 0, 9],
            ],
            'sample_rows' => 5,
        ];
    }

    private function makePkmRatioSummary(string $title): array
    {
        return [
            'title'      => $title,
            'headers'    => [
                ['Uraian', 'Jumlah Kegiatan PkM', 'Jumlah DTPS', 'Ratio'],
            ],
            'col_widths' => [40, 22, 16, 16],
            'sample_rows' => 3,
        ];
    }

    private function makeBoboLuaranTable(string $title): array
    {
        return [
            'title'      => $title,
            'headers'    => [
                ['NO', 'Jumlah Penelitian/PkM dan Luaran Berupa Publikasi Ilmiah', 'Link/Akses Publikasi', 'Bobot/Dampak (Jumlah Sitasi)', null, null, null, null, null, null, 'Link Bukti Indeksasi'],
                [null, null, null, 'Jurnal Nasional Tidak Terakreditasi', 'Nasional Terakreditasi', 'Internasional', 'Internasional Bereputasi', 'Prosiding Wilayah/Lokal', 'Prosiding Nasional', 'Prosiding Internasional Bereputasi', null],
            ],
            'col_widths' => [6, 44, 24, 18, 18, 16, 20, 20, 18, 26, 24],
            'merges'     => [
                [0, 0, 1, 0],
                [0, 1, 1, 1],
                [0, 2, 1, 2],
                [0, 3, 0, 9],
                [0, 10, 1, 10],
            ],
            'sample_rows' => 6,
        ];
    }

    // =========================================================
    // ENTRY POINT
    // =========================================================

    /**
     * Generate file Excel LKPS.
     *
     * @param  PengajuanAkreditasi  $pengajuan  — sumber data existing
     * @param  bool                 $fillData   — true = isi dari borang_data_excel
     * @return string  path file sementara
     */
    public function generate(PengajuanAkreditasi $pengajuan, bool $fillData = true): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('LKPS S1 — ' . $pengajuan->studyProgram->name)
            ->setCreator('Sistem Akreditasi')
            ->setDescription('Lembar Kinerja Program Studi (LKPS) S1');

        // Hapus sheet default
        $spreadsheet->removeSheetByIndex(0);

        // Ambil existing data jika diperlukan
        $existingData = $fillData
            ? $this->loadExistingData($pengajuan->id)
            : [];

        // Daftar Isi
        $this->buildDaftarIsiSheet($spreadsheet);

        // Build setiap sheet
        foreach ($this->getSheetDefinitions() as $sheetName => $sheetDef) {
            $ws = $spreadsheet->createSheet();
            $ws->setTitle($sheetName);
            $this->buildSheet($ws, $sheetDef, $existingData[$sheetName] ?? []);
        }

        // Aktifkan sheet pertama (Daftar Isi)
        $spreadsheet->setActiveSheetIndex(0);

        // Simpan ke temp
        $filename = 'LKPS_S1_' . $pengajuan->nomor_pengajuan . '_' . now()->format('Ymd_His') . '.xlsx';
        $path     = storage_path('app/temp/exports/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    // =========================================================
    // LOAD DATA EXISTING
    // =========================================================

    /**
     * Load semua BorangDataExcel untuk pengajuan ini,
     * dikelompokkan: [sheet_name][table_index] => rows[][]
     */
    private function loadExistingData(int $pengajuanId): array
    {
        $records = BorangDataExcel::where('id_pengajuan', $pengajuanId)
            ->whereIn('status_review', ['raw', 'reviewed', 'approved'])
            ->orderBy('sheet_name')
            ->orderBy('table_index')
            ->get();

        $result = [];
        foreach ($records as $record) {
            $result[$record->sheet_name][$record->table_index] = $record->rows ?? [];
        }

        return $result;
    }

    // =========================================================
    // BUILD DAFTAR ISI
    // =========================================================

    private function buildDaftarIsiSheet(Spreadsheet $spreadsheet): void
    {
        $ws = $spreadsheet->createSheet();
        $ws->setTitle('Daftar Isi');
        $ws->setShowGridlines(false);

        // Title
        $ws->mergeCells('A1:E1');
        $c = $ws->getCell('A1');
        $c->setValue('LEMBAR KINERJA PROGRAM STUDI (LKPS) — PROGRAM SARJANA (S1)');
        $this->styleCell($ws, 'A1', self::COLOR_HEADER_DARK, self::COLOR_FONT_WHITE, 14, true, 'center');
        $ws->getRowDimension(1)->setRowHeight(36);

        // Spacer
        $ws->getRowDimension(2)->setRowHeight(8);

        // Header tabel
        $headers = ['Kriteria', 'Kode Elemen', 'Nama Elemen', 'Nama Sheet', 'Keterangan'];
        foreach ($headers as $ci => $h) {
            $col  = chr(65 + $ci);
            $cell = $ws->getCell($col . '3');
            $cell->setValue($h);
            $this->styleCell($ws, $col . '3', self::COLOR_HEADER_DARK, self::COLOR_FONT_WHITE, 10, true, 'center');
        }
        $ws->getRowDimension(3)->setRowHeight(20);

        // Data
        $rows = [
            ['E – Edukasi',            'E.2',     'Admisi Mahasiswa',                            'E.2',      '3 tabel'],
            ['E – Edukasi',            'E.3',     'Proses dan Siklus Pembelajaran',              'E.3',      '4 tabel'],
            ['P – Pengembangan SDM',   'P.1',     'Dosen dan Tenaga Kependidikan',               'P.1',      '7 tabel'],
            ['L – Lingkungan Belajar', 'L.1',     'Sarana dan Prasarana Belajar',                'L.1',      '4 tabel'],
            ['A – Akuntabilitas',      'A.2',     'Kerja Sama dan Kemitraan',                    'A.2',      '1 tabel'],
            ['A – Akuntabilitas',      'A.5',     'Keuangan (Pemasukan)',                        'A.5.1',    '1 tabel'],
            ['A – Akuntabilitas',      'A.5',     'Keuangan (Pengeluaran)',                      'A.5.2',    '1 tabel'],
            ['A – Akuntabilitas',      'A.5',     'Keuangan (Neraca)',                           'A.5.3',    '1 tabel'],
            ['R – Riset',              'R.3',     'Ratio Penelitian DTPS (Kolaborasi Int\'l)',   'R.3.1.a',  '5 tabel'],
            ['R – Riset',              'R.3',     'Ratio Penelitian DTPS (Mandiri Int\'l)',      'R.3.1.b',  '5 tabel'],
            ['R – Riset',              'R.3',     'Ratio Penelitian Kolaborasi DTPS+Mhs',        'R.3.1.c',  '5 tabel'],
            ['R – Riset',              'R.3',     'Ratio Penelitian Kolaborasi DTPS',            'R.3.1.d',  '5 tabel'],
            ['R – Riset',              'R.3',     'Ratio Penelitian Mahasiswa',                  'R.3.1.e',  '5 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran DTPS Int\'l (Publikasi)',        'R.3.2.1.a', '1 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran DTPS Nasional (Publikasi)',      'R.3.2.1.b', '1 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran DTPS Wilayah (Publikasi)',       'R.3.2.1.c', '1 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran DTPS Lokal (Publikasi)',         'R.3.2.1.d', '1 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran Kolaborasi Int\'l',              'R.3.2.2.a', '1 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran Kolaborasi Nasional',            'R.3.2.2.b', '1 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran Kolaborasi Wilayah',             'R.3.2.2.c', '1 tabel'],
            ['R – Riset',              'R.3',     'Bobot Luaran Kolaborasi Lokal',               'R.3.2.2.d', '1 tabel'],
            ['R – PkM',                'R.6',     'Ratio PkM DTPS (Kolaborasi Int\'l)',          'R.6.1.a',  '5 tabel'],
            ['R – PkM',                'R.6',     'Ratio PkM DTPS (Mandiri Int\'l)',             'R.6.1.b',  '5 tabel'],
            ['R – PkM',                'R.6',     'Ratio PkM Kolaborasi DTPS+Mhs',              'R.6.1.c',  '5 tabel'],
            ['R – PkM',                'R.6',     'Ratio PkM Kolaborasi DTPS',                  'R.6.1.d',  '5 tabel'],
            ['R – PkM',                'R.6',     'Ratio PkM Mahasiswa',                        'R.6.1.e',  '5 tabel'],
            ['R – PkM',                'R.6',     'Bobot Luaran PkM Int\'l (Publikasi)',         'R.6.2.a',  '1 tabel'],
            ['R – PkM',                'R.6',     'Bobot Luaran PkM Nasional (Publikasi)',       'R.6.2.b',  '1 tabel'],
            ['R – PkM',                'R.6',     'Bobot Luaran PkM Wilayah (Publikasi)',        'R.6.2.c',  '1 tabel'],
            ['R – PkM',                'R.6',     'Bobot Luaran PkM Lokal (Publikasi)',          'R.6.2.d',  '1 tabel'],
        ];

        foreach ($rows as $ri => $rowData) {
            $excelRow = $ri + 4;
            $bgColor  = $ri % 2 === 0 ? 'EBF3FB' : 'FFFFFF';
            foreach ($rowData as $ci => $val) {
                $col  = chr(65 + $ci);
                $cell = $ws->getCell($col . $excelRow);
                $cell->setValue($val);
                $this->styleCell($ws, $col . $excelRow, $bgColor, self::COLOR_FONT_DARK, 10, false, 'left');
            }
            $ws->getRowDimension($excelRow)->setRowHeight(18);
        }

        $ws->getColumnDimension('A')->setWidth(28);
        $ws->getColumnDimension('B')->setWidth(14);
        $ws->getColumnDimension('C')->setWidth(50);
        $ws->getColumnDimension('D')->setWidth(14);
        $ws->getColumnDimension('E')->setWidth(12);
    }

    // =========================================================
    // BUILD SHEET
    // =========================================================

    private function buildSheet(Worksheet $ws, array $def, array $existingByTableIndex): void
    {
        $ws->setShowGridLines(false);
        $ws->getPageSetup()->setFitToPage(true)->setFitToWidth(1);

        $row = 1;

        // ── Judul sheet ──
        $maxCol = $this->getSheetMaxCol($def);
        $lastLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxCol);
        $ws->mergeCells("A{$row}:{$lastLetter}{$row}");
        $ws->getCell("A{$row}")->setValue($def['title']);
        $this->styleCell($ws, "A{$row}", self::COLOR_HEADER_DARK, self::COLOR_FONT_WHITE, 13, true, 'left');
        $ws->getRowDimension($row)->setRowHeight(28);
        $row++;

        // ── Subtitle (opsional) ──
        if (!empty($def['subtitle'])) {
            $ws->mergeCells("A{$row}:{$lastLetter}{$row}");
            $ws->getCell("A{$row}")->setValue($def['subtitle']);
            $this->styleCell($ws, "A{$row}", 'D6E4F0', self::COLOR_FONT_DARK, 11, true, 'left');
            $ws->getRowDimension($row)->setRowHeight(22);
            $row++;
        }

        // ── Instruksi (opsional) ──
        if (!empty($def['instruction'])) {
            $ws->mergeCells("A{$row}:{$lastLetter}{$row}");
            $ws->getCell("A{$row}")->setValue($def['instruction']);
            $this->styleCell($ws, "A{$row}", self::COLOR_INSTRUCTION, self::COLOR_FONT_DARK, 9, false, 'left');
            $ws->getCell("A{$row}")->getStyle()->getAlignment()->setWrapText(true);
            $ws->getRowDimension($row)->setRowHeight(60);
            $row++;
        }

        $row++; // spacer

        // ── Setiap tabel ──
        foreach ($def['tables'] as $tableIdx => $tableDef) {
            $tableNumber = $tableIdx + 1;
            $dataRows    = $existingByTableIndex[$tableNumber] ?? null;

            $row = $this->buildTableBlock($ws, $row, $tableDef, $dataRows, $maxCol);
            $row += 2; // spacer antar tabel
        }

        // ── Set lebar kolom dari tabel pertama (asumsi semua tabel 1 sheet = lebar sama) ──
        if (!empty($def['tables'][0]['col_widths'])) {
            foreach ($def['tables'][0]['col_widths'] as $ci => $width) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $ws->getColumnDimension($colLetter)->setWidth($width);
            }
        }
    }

    // =========================================================
    // BUILD TABEL BLOCK
    // =========================================================

    private function buildTableBlock(
        Worksheet $ws,
        int       $startRow,
        array     $tableDef,
        ?array    $dataRows,
        int       $maxCol
    ): int {
        $row        = $startRow;
        $headers    = $tableDef['headers'];
        $colCount   = max(array_map('count', $headers));
        $lastLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // ── Judul tabel ──
        $ws->mergeCells("A{$row}:{$lastLetter}{$row}");
        $ws->getCell("A{$row}")->setValue($tableDef['title']);
        $this->styleCell($ws, "A{$row}", self::COLOR_HEADER_MED, self::COLOR_FONT_WHITE, 10, true, 'left');
        $ws->getRowDimension($row)->setRowHeight(22);
        $row++;

        // ── Notes (opsional) ──
        if (!empty($tableDef['notes'])) {
            $ws->mergeCells("A{$row}:{$lastLetter}{$row}");
            $ws->getCell("A{$row}")->setValue($tableDef['notes']);
            $this->styleCell($ws, "A{$row}", self::COLOR_INSTRUCTION, self::COLOR_FONT_DARK, 9, false, 'left');
            $ws->getRowDimension($row)->setRowHeight(16);
            $row++;
        }

        // ── Baris header (mungkin multi-row) ──
        $headerStartRow = $row;

        foreach ($headers as $hi => $headerRow) {
            $ws->getRowDimension($row)->setRowHeight(30);
            foreach ($headerRow as $ci => $val) {
                if ($val === null) {
                    $row_row = $row; // akan diisi merge
                    $row++;
                    $row--;
                    continue;
                }
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $cell      = $ws->getCell($colLetter . $row);
                $cell->setValue($val);
                $this->styleCell($ws, $colLetter . $row, self::COLOR_HEADER_LIGHT, self::COLOR_FONT_WHITE, 10, true, 'center');
                $ws->getCell($colLetter . $row)->getStyle()->getAlignment()->setWrapText(true);
            }
            $row++;
        }

        // ── Terapkan merge cells pada header ──
        if (!empty($tableDef['merges'])) {
            foreach ($tableDef['merges'] as [$r1, $c1, $r2, $c2]) {
                $absR1 = $headerStartRow + $r1;
                $absR2 = $headerStartRow + $r2;
                $l1    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c1 + 1);
                $l2    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c2 + 1);
                if ($absR1 !== $absR2 || $l1 !== $l2) {
                    $ws->mergeCells("{$l1}{$absR1}:{$l2}{$absR2}");
                    // Re-apply style ke merged cell
                    $this->styleCell($ws, "{$l1}{$absR1}", self::COLOR_HEADER_LIGHT, self::COLOR_FONT_WHITE, 10, true, 'center');
                }
            }
        }

        // ── Baris nomor kolom (1, 2, 3, ...) ──
        $ws->getRowDimension($row)->setRowHeight(16);
        for ($ci = 1; $ci <= $colCount; $ci++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
            $cell      = $ws->getCell($colLetter . $row);
            $cell->setValue($ci);
            $this->styleCell($ws, $colLetter . $row, self::COLOR_COL_NUMBER, self::COLOR_FONT_DARK, 9, true, 'center');
        }
        $row++;

        // ── Baris data ──
        $sampleCount = $tableDef['sample_rows'] ?? 5;

        if ($dataRows && count($dataRows) > 0) {
            // Ada data existing → isi
            foreach ($dataRows as $dataRow) {
                $ws->getRowDimension($row)->setRowHeight(18);
                for ($ci = 1; $ci <= $colCount; $ci++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
                    $val       = $dataRow[$ci - 1] ?? null;
                    $cell      = $ws->getCell($colLetter . $row);
                    $cell->setValue($val);
                    $this->styleCellData($ws, $colLetter . $row, $row);
                }
                $row++;
            }
        } else {
            // Kosong → isi dengan baris template
            for ($i = 0; $i < $sampleCount; $i++) {
                $ws->getRowDimension($row)->setRowHeight(18);
                for ($ci = 1; $ci <= $colCount; $ci++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
                    $this->styleCellData($ws, $colLetter . $row, $row);
                }
                $row++;
            }
        }

        return $row;
    }

    // =========================================================
    // STYLE HELPERS
    // =========================================================

    private function styleCell(
        Worksheet $ws,
        string    $coord,
        string    $bgColor,
        string    $fontColor,
        int       $fontSize,
        bool      $bold,
        string    $hAlign
    ): void {
        $style = $ws->getStyle($coord);

        $style->getFont()
            ->setName('Arial')
            ->setSize($fontSize)
            ->setBold($bold)
            ->getColor()->setRGB($fontColor);

        $style->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($bgColor);

        $style->getAlignment()
            ->setHorizontal($hAlign === 'center' ? Alignment::HORIZONTAL_CENTER : Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(false);

        $style->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('CCCCCC');
    }

    private function styleCellData(Worksheet $ws, string $coord, int $row): void
    {
        $bgColor = ($row % 2 === 0) ? 'F5F9FF' : 'FFFFFF';
        $style   = $ws->getStyle($coord);

        $style->getFont()->setName('Arial')->setSize(10)->setBold(false);

        $style->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB($bgColor);

        $style->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $style->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('DDDDDD');
    }

    // =========================================================
    // UTIL
    // =========================================================

    private function getSheetMaxCol(array $def): int
    {
        $max = 0;
        foreach ($def['tables'] as $table) {
            foreach ($table['headers'] as $header) {
                $max = max($max, count($header));
            }
        }
        return max($max, 6);
    }
}
