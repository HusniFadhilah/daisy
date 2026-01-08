<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\DatasetBorang;
use App\Models\ElemenStandar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatasetBorangSeeder3 extends Seeder
{
    /**
     * DatasetBorangSeeder4 - Simplified Version
     *
     * Meminimalisir penggunaan colspan dan rowspan untuk:
     * - Frontend rendering yang lebih mudah
     * - Export Excel/PDF yang lebih simple
     * - Form input yang lebih straightforward
     * - Database handling yang lebih clean
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('dataset_borang')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed all elements
        $this->seedD1();
        $this->seedD2();
        $this->seedD3();

        $this->seedE1();
        $this->seedE2();
        $this->seedE3();
        $this->seedE4();
        $this->seedE5();

        $this->seedP1();
        $this->seedP2();
        $this->seedP3();
        $this->seedP4();

        $this->seedI1();
        $this->seedI2();
        $this->seedI3();

        $this->seedL1();
        $this->seedL2();
        $this->seedL3();
        $this->seedL4();

        $this->seedA1();
        $this->seedA2();
        $this->seedA3();
        $this->seedA4();
        $this->seedA5();

        $this->seedR1();
        $this->seedR2();
        $this->seedR3();
        $this->seedR4();
        $this->seedR5();
        $this->seedR6();
    }

    // ========================================
    // D.1 - Legalitas Program dan Tata Pamong
    // ========================================
    private function seedD1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.1')->first();
        if (!$elemen) return;

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.1.DESC',
            'nama' => 'Deskripsi legalitas program dan tata pamong',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel D.1 - FLAT VERSION (8 kolom, no merged cells)
        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'D.1',
        //     'nama' => 'Tabel D.1. Daftar Program Studi di Unit Pengelola Program Studi (UPPS)',
        //     'tipe_field' => 'table',
        //     'label_field' => 'Tabel D.1',
        //     'is_required' => true,
        //     'expected_columns' => [
        //         'No',
        //         'Jenis Program',
        //         'Nama Program Studi',
        //         'No. SK Pendirian',
        //         'Akreditasi - Lembaga',
        //         'Akreditasi - Status/Peringkat',
        //         'Akreditasi - No. dan Tgl. SK',
        //         'Akreditasi - Tgl. Kedaluwarsa (HH/BB/TTTT)'
        //     ],
        //     'template_html' => $this->buildFlatTable([
        //         'No',
        //         'Jenis Program',
        //         'Nama Program Studi',
        //         'No. SK Pendirian',
        //         'Akreditasi - Lembaga',
        //         'Akreditasi - Status/Peringkat',
        //         'Akreditasi - No. dan Tgl. SK',
        //         'Akreditasi - Tgl. Kedaluwarsa (HH/BB/TTTT)'
        //     ], 12),
        //     'keterangan' => 'Daftar semua program studi di bawah UPPS. Kolom akreditasi menggunakan prefix untuk clarity.',
        //     'urutan' => 2,
        // ]);
    }

    private function seedD2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.2.DESC',
            'nama' => 'Deskripsi visi, misi, tujuan, dan strategi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedD3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.3.DESC',
            'nama' => 'Deskripsi kesesuaian visi keilmuan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedE1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.1.DESC',
            'nama' => 'Deskripsi kurikulum',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    // ========================================
    // E.2 - Admisi Mahasiswa (FLAT VERSION)
    // ========================================
    private function seedE2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.DESC',
            'nama' => 'Deskripsi admisi mahasiswa',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        // E.2.a - FLAT VERSION (11 kolom, no colspan)
        // Original: Row 1 memiliki "Admisi Mahasiswa" (colspan=2) dan "Jumlah Mahasiswa Teregistrasi" (colspan=8)
        // Flat: Gabungkan parent-child menjadi explicit column names
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.a',
            'nama' => 'Tabel E.2.a Mahasiswa Penuh Waktu',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.a',
            'is_required' => true,
            'expected_columns' => [
                'Tahun Akademik (Angkatan)',
                'Admisi - Daya Tampung',
                'Admisi - Pendaftar',
                'Jumlah Mahasiswa Teregistrasi - Lulus Seleksi',
                'Jumlah Mahasiswa Teregistrasi - Afirmasi',
                'Jumlah Mahasiswa Teregistrasi - Transfer',
                'Jumlah Mahasiswa Teregistrasi - Asing',
                'Jumlah Mahasiswa Teregistrasi - Aktif',
                'Jumlah Mahasiswa Teregistrasi - Tidak Aktif',
                'Jumlah Mahasiswa Teregistrasi - Lulus',
                'Jumlah Mahasiswa Teregistrasi - Gagal'
            ],
            'template_html' => $this->getTableWithTSRows([
                'Tahun Akademik (Angkatan)',
                'Admisi - Daya Tampung',
                'Admisi - Pendaftar',
                'Jumlah Mahasiswa Teregistrasi - Lulus Seleksi',
                'Jumlah Mahasiswa Teregistrasi - Afirmasi',
                'Jumlah Mahasiswa Teregistrasi - Transfer',
                'Jumlah Mahasiswa Teregistrasi - Asing',
                'Jumlah Mahasiswa Teregistrasi - Aktif',
                'Jumlah Mahasiswa Teregistrasi - Tidak Aktif',
                'Jumlah Mahasiswa Teregistrasi - Lulus',
                'Jumlah Mahasiswa Teregistrasi - Gagal'
            ], ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS', 'Jumlah', 'Rekapitulasi'], true),
            'expected_rows' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS', 'Jumlah', 'Rekapitulasi'],
            'keterangan' => 'Data mahasiswa reguler penuh waktu 5 tahun terakhir (TS-4 s.d TS) dengan baris Rekapitulasi',
            'urutan' => 2,
        ]);

        // E.2.b - FLAT VERSION
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.b',
            'nama' => 'Tabel E.2.b Mahasiswa Asing',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.b',
            'is_required' => true,
            'expected_columns' => [
                'Tahun Akademik (Angkatan)',
                'Jumlah Mahasiswa Teregistrasi',
                'Jumlah Mahasiswa Penuh Waktu',
                'Jumlah Mahasiswa Berdurasi 1 Semester atau Lebih',
                'Jumlah Mahasiswa Asing Periode Pendek dengan Kredit',
                'Jumlah Mahasiswa Asing dengan Aktivitas tanpa Kredit'
            ],
            'template_html' => $this->getTableWithTSRows([
                'Tahun Akademik (Angkatan)',
                'Jumlah Mahasiswa Teregistrasi',
                'Jumlah Mahasiswa Penuh Waktu',
                'Jumlah Mahasiswa Berdurasi 1 Semester atau Lebih',
                'Jumlah Mahasiswa Asing Periode Pendek dengan Kredit',
                'Jumlah Mahasiswa Asing dengan Aktivitas tanpa Kredit'
            ], ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS', 'Jumlah'], true),
            'expected_rows' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS', 'Jumlah'],
            'keterangan' => 'Data mahasiswa asing dan program internasional (TS-4 s.d TS) dengan Rekapitulasi',
            'urutan' => 3,
        ]);

        // E.2.c - FLAT VERSION (no number column)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.c',
            'nama' => 'Tabel E.2.c Analisis Rasio',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.c',
            'is_required' => true,
            'expected_columns' => [
                'Tahun',
                'Rasio Keketatan (%)',
                'Rasio Inklusifitas (%)',
                'Rasio Keberhasilan Studi (%)',
                'Rasio Efektifitas Studi (%)',
                'Rasio Rekognisi Internasional (%)'
            ],
            'template_html' => $this->getTableWithTSRows([
                'Tahun',
                'Rasio Keketatan (%)',
                'Rasio Inklusifitas (%)',
                'Rasio Keberhasilan Studi (%)',
                'Rasio Efektifitas Studi (%)',
                'Rasio Rekognisi Internasional (%)'
            ], ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'], false),
            'expected_rows' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            'keterangan' => 'Analisis rasio penerimaan dan keberhasilan mahasiswa (TS-4 s.d TS)',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // E.3 - Proses dan Siklus Pembelajaran
    // ========================================
    private function seedE3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.DESC',
            'nama' => 'Deskripsi proses dan siklus pembelajaran',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        // E.3.a - RPS
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.a',
            'nama' => 'Tabel E.3.a Rekapitulasi Rencana Pembelajaran Semester',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.a',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mata Kuliah',
                'Kode',
                'Semester',
                'Besar Kredit (SKS)',
                'Model Pembelajaran',
                'Lama Tatap Muka (Menit)',
                'Sifat (Wajib/Pilihan)',
                'Link Bukti Rencana Pembelajaran Semester'
            ],
            'template_html' => $this->buildFlatTable([
                'Nama Mata Kuliah',
                'Kode',
                'Semester',
                'Besar Kredit (SKS)',
                'Model Pembelajaran',
                'Lama Tatap Muka (Menit)',
                'Sifat (Wajib/Pilihan)',
                'Link Bukti Rencana Pembelajaran Semester'
            ], 5, false),
            'keterangan' => 'Daftar semua RPS mata kuliah. Model pembelajaran: Kelas/Praktikum/Studio',
            'urutan' => 2,
        ]);

        // E.3.b - Masa Studi (FLAT - gunakan nama lengkap)
        $dataset = DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.b',
            'nama' => 'Tabel E.3.b Masa Studi Lulusan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.b',
            'is_required' => true,
            'has_degree_variants' => true,   // ✅ penting
            'expected_columns' => [],         // default kosong, nanti override per jenjang
            'expected_rows' => null,
            'template_html' => null,
            'keterangan' => 'Struktur tabel menyesuaikan degree level (D1, D2, D3, D4/S1, S2, S3, dll).',
            'urutan' => 99,
        ]);
        $this->seedMasaStudiDegreeVariants($dataset);

        // E.3.c - IPK (FLAT - IPK di nama kolom)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.c',
            'nama' => 'Tabel E.3.c Indeks Prestasi Kumulatif',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.c',
            'is_required' => true,
            'expected_columns' => [
                'No',
                'Tahun Lulus',
                'Jumlah Lulusan',
                'IPK Minimum',
                'IPK Rata-rata',
                'IPK Maksimum'
            ],
            'template_html' => $this->getTableWithTSRows([
                'No',
                'Tahun Lulus',
                'Jumlah Lulusan',
                'IPK Minimum',
                'IPK Rata-rata',
                'IPK Maksimum'
            ], ['TS-2', 'TS-1', 'TS'], false),
            'expected_rows' => ['TS-2', 'TS-1', 'TS'],
            'keterangan' => 'Distribusi IPK lulusan 3 tahun terakhir (TS-2, TS-1, TS)',
            'urutan' => 4,
        ]);

        // E.3.d - Prestasi (FLAT - Tingkat di nama kolom)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.d',
            'nama' => 'Tabel E.3.d Prestasi Akademik Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.d',
            'is_required' => true,
            'expected_columns' => [
                'No',
                'Nama Kegiatan',
                'Waktu Perolehan (HH/BB/TTTT)',
                'Tingkat - Lokal / Wilayah',
                'Tingkat - Nasional',
                'Tingkat - Internasional',
                'Prestasi yang Dicapai'
            ],
            'template_html' => $this->buildFlatTable([
                'No',
                'Nama Kegiatan',
                'Waktu Perolehan (HH/BB/TTTT)',
                'Tingkat - Lokal / Wilayah',
                'Tingkat - Nasional',
                'Tingkat - Internasional',
                'Prestasi yang Dicapai'
            ], 5),
            'keterangan' => 'Prestasi mahasiswa dalam kompetisi. Tingkat: pilih Lokal/Nasional/Internasional',
            'urutan' => 5,
        ]);
    }

    // ========================================
    // E.4 - Penilaian dan Evaluasi
    // ========================================
    private function seedE4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.4.DESC',
            'nama' => 'Deskripsi penilaian dan evaluasi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        // E.4.a - FLAT VERSION
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.4.a',
            'nama' => 'Tabel E.4.a Rekapitulasi Kinerja Perkuliahan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.4.a',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mata Kuliah',
                'Kode',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Jumlah Peserta',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Pengambil Ulang',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Rerata Kehadiran (%)',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Rasio Kelulusan (%)',
                'Evaluasi',
                'Tindak Lanjut'
            ],
            'template_html' => $this->buildFlatTable([
                'Nama Mata Kuliah',
                'Kode',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Jumlah Peserta',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Pengambil Ulang',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Rerata Kehadiran (%)',
                'Rekaman Transaksi Perkuliahan (Hanya untuk TS) - Rasio Kelulusan (%)',
                'Evaluasi',
                'Tindak Lanjut'
            ], 5, false),
            'keterangan' => 'Evaluasi kinerja pembelajaran setiap mata kuliah (data untuk Tahun Semester berjalan)',
            'urutan' => 2,
        ]);
    }

    // ========================================
    // E.5 - Kompetensi Lulusan
    // ========================================
    private function seedE5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.5')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.DESC',
            'nama' => 'Deskripsi kompetensi lulusan dan capaian pembelajaran',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        // E.5.a - CPL
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.a',
            'nama' => 'Tabel E.5.a Kesesuaian Capaian Pembelajaran Lulusan dengan Kebutuhan Masyarakat',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.a',
            'is_required' => true,
            'expected_columns' => [
                'Deskripsi Capaian Pembelajaran Lulusan (CPL)',
                'Kode CPL',
                'Indikator Capaian',
                'Rujukan Kesetaraan dan Rasionalisasi'
            ],
            'template_html' => $this->buildFlatTable([
                'Deskripsi Capaian Pembelajaran Lulusan (CPL)',
                'Kode CPL',
                'Indikator Capaian',
                'Rujukan Kesetaraan dan Rasionalisasi'
            ], 5, false),
            'keterangan' => 'Daftar CPL dengan rujukan organisasi profesi/industri',
            'urutan' => 2,
        ]);

        // E.5.b - Matriks CPL (FLAT - semua CPL sebagai kolom terpisah)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.b',
            'nama' => 'Tabel E.5.b Matriks Capaian Pembelajaran dan Mata Kuliah',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.b',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mata Kuliah',
                'Kode',
                'CPL 01',
                'CPL 02',
                'CPL 03',
                'CPL 04',
                'CPL 05',
                'Keterangan'
            ],
            'template_html' => $this->buildFlatTable([
                'Nama Mata Kuliah',
                'Kode',
                'CPL 01',
                'CPL 02',
                'CPL 03',
                'CPL 04',
                'CPL 05',
                'Keterangan'
            ], 5, false),
            'keterangan' => 'Matriks pemetaan CPL pada setiap mata kuliah. Tambahkan kolom CPL sesuai kebutuhan program studi.',
            'urutan' => 3,
        ]);

        // E.5.c - Penghargaan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.c',
            'nama' => 'Tabel E.5.c Penghargaan Eksternal Karya Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.c',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mahasiswa',
                'No. Mahasiswa',
                'Deskripsi Penghargaan',
                'Pemberi Penghargaan',
                'Regional / Nasional / Internasional',
                'Peringkat',
                'Kompetitif/Tidak Kompetitif'
            ],
            'template_html' => $this->buildFlatTable([
                'Nama Mahasiswa',
                'No. Mahasiswa',
                'Deskripsi Penghargaan',
                'Pemberi Penghargaan',
                'Regional / Nasional / Internasional',
                'Peringkat',
                'Kompetitif/Tidak Kompetitif'
            ], 5, false),
            'keterangan' => 'Penghargaan yang diterima mahasiswa dari pihak eksternal',
            'urutan' => 4,
        ]);
    }

    // P, I, L, A sections (simplified)
    private function seedP1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.1.DESC',
            'nama' => 'Deskripsi dosen dan tenaga kependidikan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedP2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.2.DESC',
            'nama' => 'Deskripsi sarana dan prasarana kerja',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedP3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.3.DESC',
            'nama' => 'Deskripsi pengembangan kapasitas',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedP4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.4.DESC',
            'nama' => 'Deskripsi kesejahteraan kerja',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedI1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.1.DESC',
            'nama' => 'Deskripsi sistem penjaminan mutu internal',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedI2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.2.DESC',
            'nama' => 'Deskripsi implementasi perbaikan berkelanjutan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedI3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.3.DESC',
            'nama' => 'Deskripsi keterlibatan pengampu kepentingan dan penjaminan mutu eksternal',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedL1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.1.DESC',
            'nama' => 'Deskripsi sarana dan prasarana belajar',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedL2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.2.DESC',
            'nama' => 'Deskripsi sumber pengetahuan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedL3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.3.DESC',
            'nama' => 'Deskripsi kepuasan mahasiswa dan alumni',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedL4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.4.DESC',
            'nama' => 'Deskripsi lulusan, kajian telusur, dan kepuasan pengguna',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        // L.4.a - Waktu Tunggu (FLAT)
        $dataset = DatasetBorang::create(
            [
                'id_elemen' => $elemen->id,
                'kode' => 'L.4.a',
                'nama' => 'Tabel L.4.a Waktu Tunggu Lulusan',
                'tipe_field' => 'table',
                'label_field' => 'Tabel L.4.a',
                'is_required' => true,
                'has_degree_variants' => true, // ✅ penting
                'expected_columns' => [],       // default kosong (override per jenjang)
                'expected_rows' => null,
                'template_html' => null,
                'keterangan' => 'Struktur tabel menyesuaikan degree level (D1, D2, D3, D4/Sarjana Terapan, S1).',
                'urutan' => 2,
            ]
        );

        $this->seedWaktuTungguDegreeVariants($dataset);
    }

    private function seedA1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.1.DESC',
            'nama' => 'Deskripsi organisasi dan tata kelola',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        // A.1.a - Kemitraan (FLAT - pisahkan Tingkat dengan checkbox)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.1.a',
            'nama' => 'Tabel A.1.a Kemitraan dan Kerjasama',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.1.a',
            'is_required' => true,
            'expected_columns' => [
                'No',
                'Lembaga Mitra',
                'Tingkat - Internasional',
                'Tingkat - Nasional',
                'Tingkat - Lokal / Wilayah',
                'Judul Kegiatan Kerja sama',
                'Manfaat bagi PS',
                'Tanggal Awal Kerja sama',
                'Tanggal Akhir Kerja sama',
                'Durasi (tahun)',
                'Status Kerja sama',
                'Bukti Kerja sama'
            ],
            'template_html' => $this->buildFlatTable([
                'No',
                'Lembaga Mitra',
                'Tingkat - Internasional',
                'Tingkat - Nasional',
                'Tingkat - Lokal / Wilayah',
                'Judul Kegiatan Kerja sama',
                'Manfaat bagi PS',
                'Tanggal Awal Kerja sama',
                'Tanggal Akhir Kerja sama',
                'Durasi (tahun)',
                'Status Kerja sama',
                'Bukti Kerja sama'
            ], 5),
            'keterangan' => 'Daftar semua kerja sama yang aktif. Tingkat: pilih Internasional/Nasional/Lokal',
            'urutan' => 2,
        ]);
    }

    private function seedA2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.2.DESC',
            'nama' => 'Deskripsi kerja sama dan kemitraan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedA3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.3.DESC',
            'nama' => 'Deskripsi sistem dan manajemen informasi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedA4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.4.DESC',
            'nama' => 'Deskripsi keselamatan dan kesehatan kerja serta kelestarian lingkungan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedA5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.5')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.5.DESC',
            'nama' => 'Deskripsi keuangan, keberlanjutan, dan mitigasi risiko',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedR1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.1.DESC',
            'nama' => 'Deskripsi kebijakan penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedR2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.2.DESC',
            'nama' => 'Deskripsi proses penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedR3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.DESC',
            'nama' => 'Deskripsi luaran dan dampak penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        $cols = [
            'No',
            'Judul Penelitian',
            'Jumlah Mahasiswa Terlibat',
            'Bukti MOU / MOA / Kontrak',
            'Jml Luaran - Publikasi Ilmiah',
            'Jml Luaran - Karya / Inovasi',
            'Jml Luaran - Laporan / Dokumen / Naskah Akademik / Policy Brief',
            'Jml Luaran - Pameran / Gallery / Photo, Video, Rekaman',
            'Jml Luaran - Media Masa dan atau Media Sosial (Podcast / Vlog)',
            'Jml Luaran - Bukti Lainnya',
            'Link Bukti Luaran'
        ];

        // R.3.a - Penelitian Internasional (FLAT - Luaran sebagai kolom terpisah)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.1.a',
            'nama' => 'Tabel R.3.1.a Ratio Penelitian/Pengembangan Karya/Inovasi Skala Internasional',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.1.a',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 5),
            'keterangan' => 'Penelitian kolaborasi internasional (DTPS + Mahasiswa). Luaran: isi jumlah untuk setiap kategori.',
            'urutan' => 2,
        ]);

        // R.3.b, c, d dengan struktur yang sama
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.1.b',
            'nama' => 'Tabel R.3.1.b Ratio Penelitian/Pengembangan Karya/Inovasi Skala Nasional',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.1.b',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 5),
            'urutan' => 3,
        ]);

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.1.c',
            'nama' => 'Tabel R.3.1.c Ratio Penelitian/Pengembangan Karya/Inovasi Skala Wilayah',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.1.c',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 5),
            'urutan' => 4,
        ]);

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.1.d',
            'nama' => 'Tabel R.3.1.d Ratio Penelitian/Pengembangan Karya/Inovasi Skala Lokal',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.1.d',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 5),
            'urutan' => 5,
        ]);


        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.1.e',
            'nama' => 'Tabel R.3.1.e Ratio Penelitian/Pengembangan Karya/Inovasi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.1.e',
            'is_required' => true,
            'expected_columns' => [
                'No',
                'Skala Penelitian / Pengembangan Karya / Inovasi',
                'Jumlah Penelitian / Pengembangan Karya / Inovasi Kolaborasi DTPS dan Mahasiswa',
                'Ratio Jumlah Mahasiswa Terlibat',
                'Ratio Jml Luaran - Publikasi Ilmiah',
                'Ratio Jml Luaran - Karya / Inovasi',
                'Ratio Jml Luaran - Laporan / Dokumen / Naskah Akademik / Policy Brief',
                'Ratio Jml Luaran - Pameran / Gallery / Photo, Video, Rekaman',
                'Ratio Jml Luaran - Media Masa dan atau Media Sosial (Podcast / Vlog)',
                'Jml Luaran - Bukti Lainnya',
            ],
            'template_html' => $this->getTableWithTSRows([
                'No',
                'Skala Penelitian / Pengembangan Karya / Inovasi',
                'Jumlah Penelitian / Pengembangan Karya / Inovasi Kolaborasi DTPS dan Mahasiswa',
                'Ratio Jumlah Mahasiswa Terlibat',
                'Ratio Jml Luaran - Publikasi Ilmiah',
                'Ratio Jml Luaran - Karya / Inovasi',
                'Ratio Jml Luaran - Laporan / Dokumen / Naskah Akademik / Policy Brief',
                'Ratio Jml Luaran - Pameran / Gallery / Photo, Video, Rekaman',
                'Ratio Jml Luaran - Media Masa dan atau Media Sosial (Podcast / Vlog)',
                'Jml Luaran - Bukti Lainnya',
            ], ['Internasional', 'Nasional', 'Wilayah', 'Lokal'], false),
            'expected_rows' => ['Internasional', 'Nasional', 'Wilayah', 'Lokal'],
            'keterangan' => 'Data tracer study lulusan 3 tahun terakhir (TS-2, TS-1, TS)',
            'urutan' => 6,
        ]);

        // Kolom “flat” (tanpa rowspan/colspan)
        $cols = [
            'No',
            'Jenis Luaran / Publikasi (DTPS + Mahasiswa)',
            'Bobot',
            'Link / Akses Publikasi',
            'Bobot / Dampak (Sitasi) - Jurnal Nasional Tidak Terakreditasi',
            'Bobot / Dampak (Sitasi) - Nasional Terakreditasi',
            'Bobot / Dampak (Sitasi) - Internasional',
            'Bobot / Dampak (Sitasi) - Internasional Bereputasi',
            'Bobot / Dampak (Sitasi) - Prosiding Wilayah / Lokal',
            'Bobot / Dampak (Sitasi) - Prosiding Nasional',
            'Bobot / Dampak (Sitasi) - Prosiding Internasional Terindeks Bereputasi',
            'Link Bukti Indeksasi (Scopus / WoS / Sinta / GS dll)',
        ];

        // R.3.2.a - Sheet “Bobot dampak Riset Inter”
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.2.a',
            'nama' => 'Tabel R.3.2.a (Internasional) Bobot dan Dampak Luaran Publikasi Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.2.a (Internasional)',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 8, true),
            'keterangan' => 'Isi sesuai kategori publikasi/prosiding dan bukti indeksasi. Bobot/dampak diisi dalam bentuk jumlah/sitasi sesuai instrumen LKPS.',
            'urutan' => 30,
        ]);

        // R.3.2.b - Sheet “bobot dampak riset Nasional”
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.2.b',
            'nama' => 'Tabel R.3.2.b (Nasional) Bobot dan Dampak Luaran Publikasi Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.2.b (Nasional)',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 8, true),
            'keterangan' => 'Isi sesuai kategori jurnal/prosiding dan bukti indeksasi.',
            'urutan' => 31,
        ]);

        // R.3.2.c - Sheet “bobot dampk riset wilayah”
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.2.c',
            'nama' => 'Tabel R.3.2.c (Wilayah) Bobot dan Dampak Luaran Publikasi Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.2.c (Wilayah)',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 8, true),
            'keterangan' => 'Isi sesuai kategori jurnal/prosiding dan bukti indeksasi.',
            'urutan' => 32,
        ]);

        // R.3.2.d - Sheet “Bobot Dampak Riset Lokal”
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.2.d',
            'nama' => 'Tabel R.3.2.d (Lokal) Bobot dan Dampak Luaran Publikasi Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.2.d (Lokal)',
            'is_required' => true,
            'expected_columns' => $cols,
            'template_html' => $this->buildFlatTable($cols, 8, true),
            'keterangan' => 'Isi sesuai kategori jurnal/prosiding dan bukti indeksasi.',
            'urutan' => 33,
        ]);
    }

    private function seedR4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.4.DESC',
            'nama' => 'Deskripsi kebijakan pengabdian kepada masyarakat',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    private function seedR5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.5')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.5.DESC',
            'nama' => 'Deskripsi proses pengabdian kepada masyarakat',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);
    }

    // ========================================
    // R.6 - PENGABDIAN KEPADA MASYARAKAT (PkM)
    // R.6.1.a - R.6.1.e (Ratio PkM)
    // R.6.2.a - R.6.2.d (Bobot & Dampak Luaran PkM - Publikasi)
    // ========================================
    private function seedR6()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.6')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.DESC',
            'nama' => 'Deskripsi luaran dan dampak pengabdian kepada masyarakat',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
            'is_required' => false,
            'urutan' => 1,
        ]);

        // =========================================================
        // R.6.1.* - RATIO PkM (FLAT)
        // =========================================================

        $colsRatio = [
            'No',
            'Judul Pengabdian kepada Masyarakat',
            'Jumlah Mahasiswa Terlibat',
            'DTPS yang Terlibat',
            'Bukti MOU / MOA / Kontrak PkM',
            'Jml Luaran - Publikasi Ilmiah',
            'Jml Luaran - Karya / Inovasi',
            'Jml Luaran - Laporan / Dokumen / Naskah Akademik / Policy Brief',
            'Jml Luaran - Pameran / Gallery / Photo, Video, Rekaman',
            'Jml Luaran - Media Massa dan/atau Media Sosial (Podcast / Vlog)',
            'Jml Luaran - Bukti Lainnya',
            'Link Bukti Luaran',
        ];

        // R.6.1.a - Internasional
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.1.a',
            'nama' => 'Tabel R.6.1.a Ratio Pengabdian kepada Masyarakat Skala Internasional (Kolaborasi DTPS dan Mahasiswa)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.1.a',
            'is_required' => true,
            'expected_columns' => $colsRatio,
            'template_html' => $this->buildFlatTable($colsRatio, 5),
            'keterangan' => 'PkM kolaborasi internasional (DTPS + Mahasiswa). Isi jumlah luaran per kategori dan tautkan bukti.',
            'urutan' => 2,
        ]);

        // R.6.1.b - Nasional
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.1.b',
            'nama' => 'Tabel R.6.1.b Ratio Pengabdian kepada Masyarakat Skala Nasional (Kolaborasi DTPS dan Mahasiswa)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.1.b',
            'is_required' => true,
            'expected_columns' => $colsRatio,
            'template_html' => $this->buildFlatTable($colsRatio, 5),
            'keterangan' => 'PkM kolaborasi nasional (DTPS + Mahasiswa).',
            'urutan' => 3,
        ]);

        // R.6.1.c - Wilayah
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.1.c',
            'nama' => 'Tabel R.6.1.c Ratio Pengabdian kepada Masyarakat Skala Wilayah (Kolaborasi DTPS dan Mahasiswa)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.1.c',
            'is_required' => true,
            'expected_columns' => $colsRatio,
            'template_html' => $this->buildFlatTable($colsRatio, 5),
            'keterangan' => 'PkM kolaborasi wilayah (DTPS + Mahasiswa).',
            'urutan' => 4,
        ]);

        // R.6.1.d - Lokal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.1.d',
            'nama' => 'Tabel R.6.1.d Ratio Pengabdian kepada Masyarakat Skala Lokal (Kolaborasi DTPS dan Mahasiswa)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.1.d',
            'is_required' => true,
            'expected_columns' => $colsRatio,
            'template_html' => $this->buildFlatTable($colsRatio, 5),
            'keterangan' => 'PkM kolaborasi lokal (DTPS + Mahasiswa).',
            'urutan' => 5,
        ]);

        // R.6.1.e - Rekap Rasio per Skala
        $colsRatioRekap = [
            'No',
            'Skala Pengabdian kepada Masyarakat',
            'Jumlah PkM Kolaborasi DTPS dan Mahasiswa',
            'Rasio Mahasiswa Terlibat',
            'Rasio DTPS Terlibat',
            'Rasio Jml Luaran - Publikasi Ilmiah',
            'Rasio Jml Luaran - Karya / Inovasi',
            'Rasio Jml Luaran - Laporan / Dokumen / Naskah Akademik / Policy Brief',
            'Rasio Jml Luaran - Pameran / Gallery / Photo, Video, Rekaman',
            'Rasio Jml Luaran - Media Massa dan/atau Media Sosial (Podcast / Vlog)',
            'Rasio Jml Luaran - Bukti Lainnya',
        ];

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.1.e',
            'nama' => 'Tabel R.6.1.e Rekap Rasio Pengabdian kepada Masyarakat per Skala',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.1.e',
            'is_required' => true,
            'expected_columns' => $colsRatioRekap,
            'template_html' => $this->getTableWithTSRows(
                $colsRatioRekap,
                ['Internasional', 'Nasional', 'Wilayah', 'Lokal'],
                false
            ),
            'expected_rows' => ['Internasional', 'Nasional', 'Wilayah', 'Lokal'],
            'keterangan' => 'Rekap rasio PkM berdasarkan skala. Nilai rasio dapat dihitung dari total kegiatan dan keterlibatan.',
            'urutan' => 6,
        ]);

        // =========================================================
        // R.6.2.* - BOBOT & DAMPAK LUARAN PkM (Publikasi) (FLAT)
        // Mengacu pola file: R6b (Publikasi + Sitasi + Indeksasi)
        // =========================================================

        $colsBobotPkM = [
            'No',
            'Jenis Luaran / Publikasi (DTPS + Mahasiswa)',
            'Bobot',
            'Link / Akses Publikasi',
            'Bobot / Dampak (Sitasi) - Jurnal Nasional Tidak Terakreditasi',
            'Bobot / Dampak (Sitasi) - Nasional Terakreditasi',
            'Bobot / Dampak (Sitasi) - Internasional',
            'Bobot / Dampak (Sitasi) - Internasional Bereputasi',
            'Bobot / Dampak (Sitasi) - Prosiding Wilayah / Lokal',
            'Bobot / Dampak (Sitasi) - Prosiding Nasional',
            'Bobot / Dampak (Sitasi) - Prosiding Internasional Terindeks Bereputasi',
            'Link Bukti Indeksasi (Scopus / WoS / Sinta / GS dll)',
        ];

        // R.6.2.a - Internasional
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.2.a',
            'nama' => 'Tabel R.6.2.a (Internasional) Bobot dan Dampak Luaran Publikasi PkM Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.2.a (Internasional)',
            'is_required' => true,
            'expected_columns' => $colsBobotPkM,
            'template_html' => $this->buildFlatTable($colsBobotPkM, 8, true),
            'keterangan' => 'Isi kategori jurnal/prosiding dan jumlah sitasi, serta sertakan bukti indeksasi.',
            'urutan' => 30,
        ]);

        // R.6.2.b - Nasional
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.2.b',
            'nama' => 'Tabel R.6.2.b (Nasional) Bobot dan Dampak Luaran Publikasi PkM Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.2.b (Nasional)',
            'is_required' => true,
            'expected_columns' => $colsBobotPkM,
            'template_html' => $this->buildFlatTable($colsBobotPkM, 8, true),
            'keterangan' => 'Isi kategori jurnal/prosiding dan jumlah sitasi, serta sertakan bukti indeksasi.',
            'urutan' => 31,
        ]);

        // R.6.2.c - Wilayah
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.2.c',
            'nama' => 'Tabel R.6.2.c (Wilayah) Bobot dan Dampak Luaran Publikasi PkM Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.2.c (Wilayah)',
            'is_required' => true,
            'expected_columns' => $colsBobotPkM,
            'template_html' => $this->buildFlatTable($colsBobotPkM, 8, true),
            'keterangan' => 'Isi kategori jurnal/prosiding dan jumlah sitasi, serta sertakan bukti indeksasi.',
            'urutan' => 32,
        ]);

        // R.6.2.d - Lokal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.2.d',
            'nama' => 'Tabel R.6.2.d (Lokal) Bobot dan Dampak Luaran Publikasi PkM Dosen Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.2.d (Lokal)',
            'is_required' => true,
            'expected_columns' => $colsBobotPkM,
            'template_html' => $this->buildFlatTable($colsBobotPkM, 8, true),
            'keterangan' => 'Isi kategori jurnal/prosiding dan jumlah sitasi, serta sertakan bukti indeksasi.',
            'urutan' => 33,
        ]);
    }

    // ========================================
    // FLAT TABLE BUILDER - NO COLSPAN/ROWSPAN
    // ========================================

    /**
     * Build completely flat HTML table without any merged cells
     *
     * @param array $columns Column headers (single level)
     * @param int $rows Number of data rows
     * @param bool $numbered Auto-number first column if it's "No"
     * @return string HTML table
     */
    private function buildFlatTable(array $columns, int $rows = 5, bool $numbered = true): string
    {
        $colCount = count($columns);
        $colWidth = floor(100 / $colCount);

        $html = '<table class="table table-bordered" style="width: 100%; border-collapse: collapse;">';

        // ===== SINGLE HEADER ROW (NO COLSPAN) =====
        $html .= '<thead>';
        $html .= '<tr>';

        foreach ($columns as $col) {
            $html .= sprintf(
                '<th style="width: %d%%; border: 1px solid #000; padding: 8px; text-align: center; background-color: #e8e8e8; font-weight: bold; vertical-align: middle;">%s</th>',
                $colWidth,
                htmlspecialchars($col)
            );
        }

        $html .= '</tr>';
        $html .= '</thead>';

        // ===== DATA ROWS =====
        $html .= '<tbody>';

        for ($i = 1; $i <= $rows; $i++) {
            $html .= '<tr>';

            foreach ($columns as $idx => $col) {
                $isNumberColumn = $numbered && $idx === 0 && in_array($col, ['No', 'No.', 'NO']);
                $cellContent = $isNumberColumn ? $i : '&nbsp;';
                $textAlign = $isNumberColumn ? 'center' : 'left';

                $html .= sprintf(
                    '<td style="border: 1px solid #000; padding: 8px; text-align: %s; vertical-align: top;">%s</td>',
                    $textAlign,
                    $cellContent
                );
            }

            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Generate simple HTML table without colspan/rowspan
     *
     * @param array $columns Column headers
     * @param int $rows Number of empty rows
     * @return string HTML table
     */
    private function getSimpleTable(array $columns, int $rows = 3)
    {
        $html = '<table class="table table-bordered" style="width: 100%;">' . "\n";
        $html .= '    <thead class="table-light">' . "\n";
        $html .= '        <tr>' . "\n";

        foreach ($columns as $column) {
            $html .= '            <th>' . $column . '</th>' . "\n";
        }

        $html .= '        </tr>' . "\n";
        $html .= '    </thead>' . "\n";
        $html .= '    <tbody>' . "\n";

        // Generate empty rows
        for ($i = 1; $i <= $rows; $i++) {
            $html .= '        <tr>' . "\n";

            foreach ($columns as $index => $column) {
                // First column gets row number
                if ($index === 0 && ($column === 'No' || $column === 'No.')) {
                    $html .= '            <td>' . $i . '</td>' . "\n";
                } else {
                    $html .= '            <td>&nbsp;</td>' . "\n";
                }
            }

            $html .= '        </tr>' . "\n";
        }

        $html .= '    </tbody>' . "\n";
        $html .= '</table>';

        return $html;
    }

    /**
     * Generate table with predefined TS rows (TS-4, TS-3, TS-2, TS-1, TS)
     * and optional Rekapitulasi row
     *
     * @param array $columns Column headers
     * @param array $tsLabels TS row labels (default: ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'])
     * @param bool $includeRekapitulasi Include summary row
     * @return string HTML table
     */
    private function getTableWithTSRows(array $columns, array $tsLabels = ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'], bool $includeRekapitulasi = true)
    {
        $html = '<table class="table table-bordered" style="width: 100%;">' . "\n";
        $html .= '    <thead class="table-light">' . "\n";
        $html .= '        <tr>' . "\n";

        foreach ($columns as $column) {
            $html .= '            <th>' . $column . '</th>' . "\n";
        }

        $html .= '        </tr>' . "\n";
        $html .= '    </thead>' . "\n";
        $html .= '    <tbody>' . "\n";

        // Generate TS rows
        foreach ($tsLabels as $tsLabel) {
            $html .= '        <tr>' . "\n";

            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $html .= '            <td><strong>' . $tsLabel . '</strong></td>' . "\n";
                } else {
                    if (strtolower($tsLabel) === 'jumlah') {
                        $html .= '            <td class="text-center">0</td>' . "\n";
                    } else {
                        $html .= '            <td>&nbsp;</td>' . "\n";
                    }
                }
            }

            $html .= '        </tr>' . "\n";
        }

        // Add Rekapitulasi row if requested
        if ($includeRekapitulasi) {
            $html .= '        <tr class="table-warning">' . "\n";

            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $html .= '            <td><strong>Rekapitulasi</strong></td>' . "\n";
                } else {
                    // For rekapitulasi, show formula placeholder
                    $html .= '            <td class="text-center"><em>Σ</em></td>' . "\n";
                }
            }

            $html .= '        </tr>' . "\n";
        }

        $html .= '    </tbody>' . "\n";
        $html .= '</table>';

        return $html;
    }

    private function seedMasaStudiDegreeVariants(DatasetBorang $dataset): void
    {
        $variants = $this->masaStudiVariantConfig();

        foreach ($variants as $degreeCode => $cfg) {
            $degree = DegreeLevel::where('alias', $degreeCode)->first();
            if (!$degree) continue;

            DB::table('dataset_borang_degree_level')->updateOrInsert(
                [
                    'id_dataset_borang' => $dataset->id,
                    'id_degree_level' => $degree->id,
                ],
                [
                    'expected_columns' => json_encode($cfg['columns']),
                    'template_html' => $this->getTableWithTSRows($cfg['columns'], $cfg['rows'], false),
                    'validation_rules' => null,
                    'keterangan' => 'Template Masa Studi disesuaikan untuk ' . $degreeCode,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    private function masaStudiVariantConfig(): array
    {
        return [
            'D1' => [
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Reguler)',
                ],
                'rows' => ['TS-1', 'TS'],
            ],
            'D2' => [
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Reguler)',
                ],
                'rows' => ['TS-2', 'TS-1', 'TS'],
            ],
            'D3' => [
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-4',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-3',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Reguler)',
                ],
                'rows' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            ],
            'D4' => [
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-6',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-5',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-4',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-3',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Reguler)',
                ],
                'rows' => ['TS-6', 'TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            ],
            'S1' => [ // sama seperti D4
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-6',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-5',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-4',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-3',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Reguler per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Reguler)',
                ],
                'rows' => ['TS-6', 'TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            ],
            'S2' => [
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-4',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-3',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Bukan Transfer)',
                ],
                'rows' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            ],
            'S2 Terapan' => [ // samakan dengan S2
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-4',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-3',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Bukan Transfer)',
                ],
                'rows' => ['TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            ],
            'S3' => [
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-5',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-4',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-3',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Bukan Transfer)',
                ],
                'rows' => ['TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            ],
            'S3 Terapan' => [ // samakan dengan S3
                'columns' => [
                    'Tahun Masuk',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-5',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-4',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-3',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-2',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS-1',
                    'Jumlah Mahasiswa Bukan Transfer per Angkatan pada Tahun TS',
                    'Jumlah Lulusan s.d TS (dari Mahasiswa Bukan Transfer)',
                ],
                'rows' => ['TS-5', 'TS-4', 'TS-3', 'TS-2', 'TS-1', 'TS'],
            ],
        ];
    }

    private function waktuTungguVariantConfig(): array
    {
        // Diploma: ada kolom "Dipesan Sebelum Lulus", interval kurang dari 3, 3 sampai 6, lebih dari 6
        $colsDiploma = [
            'Tahun Lulus',
            'Jumlah Lulusan',
            'Jumlah Lulusan yang Terlacak',
            'Jumlah Lulusan yang Dipesan Sebelum Lulus',
            'WT kurang dari 3 bulan',
            'WT 3 s.d 6 bulan',
            'WT lebih dari 6 bulan',
        ];

        // Sarjana Terapan (D4): TANPA "Dipesan", interval kurang dari 3, 3 sampai 6, lebih dari 6
        $colsSarjanaTerapan = [
            'Tahun Lulus',
            'Jumlah Lulusan',
            'Jumlah Lulusan yang Terlacak',
            'WT kurang dari 3 bulan',
            'WT 3 s.d 6 bulan',
            'WT lebih dari 6 bulan',
        ];

        // Sarjana (S1): TANPA "Dipesan", interval kurang dari 6, 6 sampai 18, lebih dari 18
        $colsSarjana = [
            'Tahun Lulus',
            'Jumlah Lulusan',
            'Jumlah Lulusan yang Terlacak',
            'WT kurang dari 6 bulan',
            'WT 6 s.d 18 bulan',
            'WT lebih dari 18 bulan',
        ];

        return [
            'D1' => [
                'columns' => $colsDiploma,
                'rows' => ['TS-3', 'TS-2', 'Jumlah'],
            ],
            'D2' => [
                'columns' => $colsDiploma,
                'rows' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            ],
            'D3' => [
                'columns' => $colsDiploma,
                'rows' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            ],
            'D4' => [
                'columns' => $colsSarjanaTerapan,
                'rows' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            ],
            'S1' => [
                'columns' => $colsSarjana,
                'rows' => ['TS-4', 'TS-3', 'TS-2', 'Jumlah'],
            ],
        ];
    }

    private function seedWaktuTungguDegreeVariants(DatasetBorang $dataset): void
    {
        $variants = $this->waktuTungguVariantConfig();

        foreach ($variants as $degreeCode => $cfg) {
            $degree = DegreeLevel::where('code', $degreeCode)->first();
            if (!$degree) continue;

            DB::table('dataset_borang_degree_level')->updateOrInsert(
                [
                    'id_dataset_borang' => $dataset->id,
                    'id_degree_level' => $degree->id,
                ],
                [
                    'expected_columns' => json_encode($cfg['columns']),
                    // pakai TS rows builder Anda, dan jangan include rekapitulasi otomatis
                    // karena Anda sudah punya baris "Jumlah" di rows
                    'template_html' => $this->getTableWithTSRows($cfg['columns'], $cfg['rows'], false),
                    'validation_rules' => null,
                    'keterangan' => 'Template Waktu Tunggu disesuaikan untuk ' . $degreeCode,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
