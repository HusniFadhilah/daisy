<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\DatasetBorang;
use App\Models\ElemenStandar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatasetBorangSeeder4 extends Seeder
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

        // D - Diferensiasi Misi
        $this->seedD1(); // Legalitas Program dan Tata Pamong
        $this->seedD2(); // Visi, Misi, Tujuan, dan Strategi
        $this->seedD3(); // Kesesuaian Visi Keilmuan

        // E - Edukasi, Sistem Evaluasi, dan Capaian Pembelajaran
        $this->seedE1(); // Kurikulum
        $this->seedE2(); // Admisi Mahasiswa
        $this->seedE3(); // Proses dan Siklus Pembelajaran
        $this->seedE4(); // Penilaian dan Evaluasi
        $this->seedE5(); // Kompetensi Lulusan dan Capaian Pembelajaran

        // P - Pengembangan Sumber Daya Manusia
        $this->seedP1(); // Dosen dan Tenaga Kependidikan
        $this->seedP2(); // Sarana dan Prasarana Kerja
        $this->seedP3(); // Pengembangan Kapasitas
        $this->seedP4(); // Kesejahteraan Kerja

        // I - Internalisasi Penjaminan Mutu
        $this->seedI1(); // Sistem Penjaminan Mutu Internal
        $this->seedI2(); // Implementasi Perbaikan Berkelanjutan
        $this->seedI3(); // Keterlibatan Pengampu Kepentingan

        // L - Lingkungan dan Sumber Belajar
        $this->seedL1(); // Sarana dan Prasarana Belajar
        $this->seedL2(); // Sumber Pengetahuan
        $this->seedL3(); // Kepuasan Mahasiswa dan Alumni
        $this->seedL4(); // Lulusan, Kajian Telusur

        // A - Akuntabilitas, Tata Kelola, dan Kerjasama
        $this->seedA1(); // Organisasi dan Tata Kelola
        $this->seedA2(); // Kerja Sama dan Kemitraan
        $this->seedA3(); // Sistem dan Manajemen Informasi
        $this->seedA4(); // Keselamatan dan Kesehatan Kerja
        $this->seedA5(); // Keuangan, Keberlanjutan

        // R - Riset, Pengabdian, dan Suasana Ilmiah
        $this->seedR1(); // Kebijakan Penelitian
        $this->seedR2(); // Proses Penelitian
        $this->seedR3(); // Luaran dan Dampak Penelitian
        $this->seedR4(); // Kebijakan Pengabdian kepada Masyarakat
        $this->seedR5(); // Proses Pengabdian kepada Masyarakat
        $this->seedR6(); // Luaran dan Dampak Pengabdian
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
            'nama' => 'Deskripsi Legalitas Program dan Tata Pamong',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi legalitas program dan tata pamong',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel D.1 - SIMPLIFIED (no merged headers)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.1',
            'nama' => 'Tabel D.1. Daftar Program Studi di Unit Pengelola Program Studi (UPPS)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.1. Daftar Program Studi di UPPS',
            'is_required' => true,
            'expected_columns' => [
                'No',
                'Jenis Program',
                'Nama Program Studi',
                'No. SK Pendirian',
                'Akreditasi - Lembaga',
                'Akreditasi - Status/Peringkat',
                'Akreditasi - No. dan Tgl. SK',
                'Akreditasi - Tgl. Kadaluarsa'
            ],
            'template_html' => $this->getSimpleTable([
                'No',
                'Jenis Program',
                'Nama Program Studi',
                'No. SK Pendirian',
                'Akreditasi - Lembaga',
                'Akreditasi - Status/Peringkat',
                'Akreditasi - No. dan Tgl. SK',
                'Akreditasi - Tgl. Kadaluarsa'
            ], 5),
            'keterangan' => 'Daftar semua program studi di bawah UPPS. Kolom akreditasi menggunakan prefix untuk clarity.',
            'urutan' => 2,
        ]);
    }

    // ========================================
    // D.2 - Visi, Misi, Tujuan, dan Strategi
    // ========================================
    private function seedD2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.2.DESC',
            'nama' => 'Deskripsi Visi, Misi, Tujuan, dan Strategi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi visi, misi, tujuan, dan strategi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    // ========================================
    // D.3 - Kesesuaian Visi Keilmuan
    // ========================================
    private function seedD3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.3.DESC',
            'nama' => 'Deskripsi Kesesuaian Visi Keilmuan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kesesuaian visi keilmuan',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    // ========================================
    // E.1 - Kurikulum
    // ========================================
    private function seedE1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.1.DESC',
            'nama' => 'Deskripsi Kurikulum',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kurikulum',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    // ========================================
    // E.2 - Admisi Mahasiswa
    // ========================================
    private function seedE2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.2')->first();
        if (!$elemen) return;

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.DESC',
            'nama' => 'Deskripsi Admisi Mahasiswa',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi admisi mahasiswa',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel E.2.a - SIMPLIFIED (flat structure)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.a',
            'nama' => 'Tabel E.2.a Mahasiswa Penuh Waktu',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.a Mahasiswa Penuh Waktu',
            'is_required' => true,
            'expected_columns' => [
                'Tahun Akademik',
                'Daya Tampung',
                'Pendaftar',
                'Lulus Seleksi',
                'Mahasiswa Afirmasi',
                'Mahasiswa Transfer',
                'Mahasiswa Asing',
                'Mahasiswa Aktif',
                'Mahasiswa Tidak Aktif',
                'Mahasiswa Lulus',
                'Mahasiswa Gagal'
            ],
            'template_html' => $this->getSimpleTable([
                'Tahun Akademik',
                'Daya Tampung',
                'Pendaftar',
                'Lulus Seleksi',
                'Mahasiswa Afirmasi',
                'Mahasiswa Transfer',
                'Mahasiswa Asing',
                'Mahasiswa Aktif',
                'Mahasiswa Tidak Aktif',
                'Mahasiswa Lulus',
                'Mahasiswa Gagal'
            ], 5),
            'keterangan' => 'Data mahasiswa reguler penuh waktu 5 tahun terakhir (TS-4 s.d TS)',
            'urutan' => 2,
        ]);

        // Tabel E.2.b - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.b',
            'nama' => 'Tabel E.2.b Mahasiswa Asing',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.b Mahasiswa Asing',
            'is_required' => true,
            'expected_columns' => [
                'Tahun Akademik',
                'Jumlah Mahasiswa Teregistrasi',
                'Mahasiswa Penuh Waktu',
                'Mahasiswa 1 Semester atau Lebih',
                'Mahasiswa Periode Pendek dengan Kredit',
                'Mahasiswa Tanpa Kredit'
            ],
            'template_html' => $this->getSimpleTable([
                'Tahun Akademik',
                'Jumlah Mahasiswa Teregistrasi',
                'Mahasiswa Penuh Waktu',
                'Mahasiswa 1 Semester atau Lebih',
                'Mahasiswa Periode Pendek dengan Kredit',
                'Mahasiswa Tanpa Kredit'
            ], 5),
            'keterangan' => 'Data mahasiswa asing dan program internasional',
            'urutan' => 3,
        ]);

        // Tabel E.2.c - SIMPLIFIED (no empty columns)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.c',
            'nama' => 'Tabel E.2.c Analisis Rasio',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.c Analisis Rasio',
            'is_required' => true,
            'expected_columns' => [
                'Tahun',
                'Rasio Keketatan (%)',
                'Rasio Inklusifitas (%)',
                'Rasio Keberhasilan Studi (%)',
                'Rasio Efektifitas Studi (%)',
                'Rasio Rekognisi Internasional (%)'
            ],
            'template_html' => $this->getSimpleTable([
                'Tahun',
                'Rasio Keketatan (%)',
                'Rasio Inklusifitas (%)',
                'Rasio Keberhasilan Studi (%)',
                'Rasio Efektifitas Studi (%)',
                'Rasio Rekognisi Internasional (%)'
            ], 5),
            'keterangan' => 'Analisis rasio penerimaan dan keberhasilan mahasiswa',
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

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.DESC',
            'nama' => 'Deskripsi Proses dan Siklus Pembelajaran',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi proses dan siklus pembelajaran',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel E.3.a - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.a',
            'nama' => 'Tabel E.3.a Rekapitulasi Rencana Pembelajaran Semester',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.a Rekapitulasi RPS',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mata Kuliah',
                'Kode MK',
                'Semester',
                'Besar Kredit (SKS)',
                'Model Pembelajaran',
                'Lama Tatap Muka (Menit)',
                'Sifat (Wajib/Pilihan)',
                'Link Bukti RPS'
            ],
            'template_html' => $this->getSimpleTable([
                'Nama Mata Kuliah',
                'Kode MK',
                'Semester',
                'Besar Kredit (SKS)',
                'Model Pembelajaran',
                'Lama Tatap Muka (Menit)',
                'Sifat (Wajib/Pilihan)',
                'Link Bukti RPS'
            ], 5),
            'keterangan' => 'Daftar semua RPS mata kuliah. Model pembelajaran: Kelas/Praktikum/Studio',
            'urutan' => 2,
        ]);

        // Tabel E.3.b - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.b',
            'nama' => 'Tabel E.3.b Masa Studi Lulusan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.b Masa Studi Lulusan',
            'is_required' => true,
            'expected_columns' => [
                'Tahun Masuk',
                'Jumlah Mahasiswa pada TS-1',
                'Jumlah Mahasiswa pada TS',
                'Jumlah Lulusan s.d TS'
            ],
            'template_html' => $this->getSimpleTable([
                'Tahun Masuk',
                'Jumlah Mahasiswa pada TS-1',
                'Jumlah Mahasiswa pada TS',
                'Jumlah Lulusan s.d TS'
            ], 3),
            'keterangan' => 'Data masa studi dan kelulusan. Variabel: a, b, c, d, e untuk perhitungan',
            'urutan' => 3,
        ]);

        // Tabel E.3.c - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.c',
            'nama' => 'Tabel E.3.c Indeks Prestasi Kumulatif',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.c IPK Lulusan',
            'is_required' => true,
            'expected_columns' => [
                'No.',
                'Tahun Lulus',
                'Jumlah Lulusan',
                'IPK Minimum',
                'IPK Rata-rata',
                'IPK Maksimum'
            ],
            'template_html' => $this->getSimpleTable([
                'No.',
                'Tahun Lulus',
                'Jumlah Lulusan',
                'IPK Minimum',
                'IPK Rata-rata',
                'IPK Maksimum'
            ], 3),
            'keterangan' => 'Distribusi IPK lulusan 3 tahun terakhir',
            'urutan' => 4,
        ]);

        // Tabel E.3.d - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.d',
            'nama' => 'Tabel E.3.d Prestasi Akademik Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.d Prestasi Mahasiswa',
            'is_required' => true,
            'expected_columns' => [
                'No.',
                'Nama Kegiatan',
                'Waktu Perolehan (HH/BB/TTTT)',
                'Tingkat (Lokal/Nasional/Internasional)',
                'Prestasi yang Dicapai'
            ],
            'template_html' => $this->getSimpleTable([
                'No.',
                'Nama Kegiatan',
                'Waktu Perolehan (HH/BB/TTTT)',
                'Tingkat (Lokal/Nasional/Internasional)',
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

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.4.DESC',
            'nama' => 'Deskripsi Penilaian dan Evaluasi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi penilaian dan evaluasi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel E.4.a - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.4.a',
            'nama' => 'Tabel E.4.a Rekapitulasi Kinerja Perkuliahan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.4.a Kinerja Perkuliahan',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mata Kuliah',
                'Kode MK',
                'Jumlah Peserta',
                'Pengambil Ulang (Retaker)',
                'Rerata Kehadiran (%)',
                'Rasio Kelulusan (%)',
                'Evaluasi',
                'Tindak Lanjut'
            ],
            'template_html' => $this->getSimpleTable([
                'Nama Mata Kuliah',
                'Kode MK',
                'Jumlah Peserta',
                'Pengambil Ulang (Retaker)',
                'Rerata Kehadiran (%)',
                'Rasio Kelulusan (%)',
                'Evaluasi',
                'Tindak Lanjut'
            ], 5),
            'keterangan' => 'Evaluasi kinerja pembelajaran setiap mata kuliah (data untuk Tahun Semester berjalan)',
            'urutan' => 2,
        ]);
    }

    // ========================================
    // E.5 - Kompetensi Lulusan dan Capaian Pembelajaran
    // ========================================
    private function seedE5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.5')->first();
        if (!$elemen) return;

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.DESC',
            'nama' => 'Deskripsi Kompetensi Lulusan dan Capaian Pembelajaran',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kompetensi lulusan dan capaian pembelajaran',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel E.5.a - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.a',
            'nama' => 'Tabel E.5.a Kesesuaian Capaian Pembelajaran Lulusan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.a Kesesuaian CPL',
            'is_required' => true,
            'expected_columns' => [
                'Deskripsi Capaian Pembelajaran Lulusan (CPL)',
                'Kode CPL',
                'Indikator Capaian',
                'Rujukan Kesetaraan dan Rasionalitas'
            ],
            'template_html' => $this->getSimpleTable([
                'Deskripsi CPL',
                'Kode CPL',
                'Indikator Capaian',
                'Rujukan Kesetaraan dan Rasionalitas'
            ], 5),
            'keterangan' => 'Daftar CPL dengan rujukan organisasi profesi/industri',
            'urutan' => 2,
        ]);

        // Tabel E.5.b - SIMPLIFIED (linear columns)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.b',
            'nama' => 'Tabel E.5.b Matriks Capaian Pembelajaran dan Mata Kuliah',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.b Matriks CPL-MK',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mata Kuliah',
                'Kode MK',
                'CPL 01',
                'CPL 02',
                'CPL 03',
                'CPL 04',
                'CPL 05',
                'Keterangan'
            ],
            'template_html' => $this->getSimpleTable([
                'Nama Mata Kuliah',
                'Kode MK',
                'CPL 01',
                'CPL 02',
                'CPL 03',
                'CPL 04',
                'CPL 05',
                'Keterangan'
            ], 5),
            'keterangan' => 'Matriks pemetaan CPL pada setiap mata kuliah. Tambahkan kolom CPL sesuai kebutuhan program studi.',
            'urutan' => 3,
        ]);

        // Tabel E.5.c - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.c',
            'nama' => 'Tabel E.5.c Penghargaan Eksternal Karya Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.c Penghargaan Eksternal',
            'is_required' => true,
            'expected_columns' => [
                'Nama Mahasiswa',
                'No. Mahasiswa',
                'Deskripsi Penghargaan',
                'Pemberi Penghargaan',
                'Tingkat (Regional/Nasional/Internasional)',
                'Peringkat',
                'Jenis (Kompetitif/Tidak Kompetitif)'
            ],
            'template_html' => $this->getSimpleTable([
                'Nama Mahasiswa',
                'No. Mahasiswa',
                'Deskripsi Penghargaan',
                'Pemberi Penghargaan',
                'Tingkat (Regional/Nasional/Internasional)',
                'Peringkat',
                'Jenis (Kompetitif/Tidak Kompetitif)'
            ], 5),
            'keterangan' => 'Penghargaan yang diterima mahasiswa dari pihak eksternal',
            'urutan' => 4,
        ]);
    }

    // Continue with P, I, L, A, R sections...

    private function seedP1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.1.DESC',
            'nama' => 'Deskripsi Dosen dan Tenaga Kependidikan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi dosen dan tenaga kependidikan',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Sarana dan Prasarana Kerja',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi sarana dan prasarana kerja',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Pengembangan Kapasitas',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi pengembangan kapasitas',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Kesejahteraan Kerja',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kesejahteraan kerja',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Sistem Penjaminan Mutu Internal',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi sistem penjaminan mutu internal',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Implementasi Perbaikan Berkelanjutan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi implementasi perbaikan berkelanjutan',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Keterlibatan Pengampu Kepentingan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi keterlibatan pengampu kepentingan',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Sarana dan Prasarana Belajar',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi sarana dan prasarana belajar',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Sumber Pengetahuan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi sumber pengetahuan',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Kepuasan Mahasiswa dan Alumni',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kepuasan mahasiswa dan alumni',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    private function seedL4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.4')->first();
        if (!$elemen) return;

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.4.DESC',
            'nama' => 'Deskripsi Lulusan, Kajian Telusur',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi lulusan dan kajian telusur',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel L.4.a - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.4.a',
            'nama' => 'Tabel L.4.a Data Lulusan dan Waktu Tunggu Kerja',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.4.a Data Lulusan',
            'is_required' => true,
            'expected_columns' => [
                'Tahun Lulus',
                'Jumlah Lulusan',
                'Jumlah Lulusan Terlacak',
                'Dipesan Sebelum Lulus',
                'Waktu Tunggu < 3 Bulan',
                'Waktu Tunggu 3-6 Bulan',
                'Waktu Tunggu > 6 Bulan'
            ],
            'template_html' => $this->getSimpleTable([
                'Tahun Lulus',
                'Jumlah Lulusan',
                'Jumlah Lulusan Terlacak',
                'Dipesan Sebelum Lulus',
                'Waktu Tunggu < 3 Bulan',
                'Waktu Tunggu 3-6 Bulan',
                'Waktu Tunggu > 6 Bulan'
            ], 3),
            'keterangan' => 'Data tracer study lulusan 3 tahun terakhir',
            'urutan' => 2,
        ]);
    }

    private function seedA1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.1.DESC',
            'nama' => 'Deskripsi Organisasi dan Tata Kelola',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi organisasi dan tata kelola',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    private function seedA2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.2')->first();
        if (!$elemen) return;

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.2.DESC',
            'nama' => 'Deskripsi Kerja Sama dan Kemitraan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kerja sama dan kemitraan',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel A.2.a - SIMPLIFIED (no merged tingkat)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.2.a',
            'nama' => 'Tabel A.2.a Daftar Kerja Sama',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.2.a Daftar Kerja Sama',
            'is_required' => true,
            'expected_columns' => [
                'No',
                'Lembaga Mitra',
                'Tingkat (Internasional/Nasional/Lokal)',
                'Judul Kegiatan Kerja Sama',
                'Manfaat bagi Program Studi',
                'Tanggal Awal (HH/BB/TTTT)',
                'Tanggal Akhir (HH/BB/TTTT)',
                'Durasi (Tahun)',
                'Status Kerja Sama',
                'Link Bukti Kerja Sama'
            ],
            'template_html' => $this->getSimpleTable([
                'No',
                'Lembaga Mitra',
                'Tingkat (Internasional/Nasional/Lokal)',
                'Judul Kegiatan Kerja Sama',
                'Manfaat bagi Program Studi',
                'Tanggal Awal (HH/BB/TTTT)',
                'Tanggal Akhir (HH/BB/TTTT)',
                'Durasi (Tahun)',
                'Status Kerja Sama',
                'Link Bukti Kerja Sama'
            ], 5),
            'keterangan' => 'Daftar semua kerja sama yang aktif. Tingkat: pilih Internasional/Nasional/Lokal',
            'urutan' => 2,
        ]);
    }

    private function seedA3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.3.DESC',
            'nama' => 'Deskripsi Sistem dan Manajemen Informasi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi sistem dan manajemen informasi',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Keselamatan dan Kesehatan Kerja',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi keselamatan dan kesehatan kerja',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Keuangan dan Keberlanjutan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi keuangan dan keberlanjutan',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Kebijakan Penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kebijakan penelitian',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    private function seedR2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.2')->first();
        if (!$elemen) return;

        // Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.2.DESC',
            'nama' => 'Deskripsi Proses Penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi proses penelitian',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel R.2.a - SIMPLIFIED
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.2.a',
            'nama' => 'Tabel R.2.a Penelitian Skala Internasional',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.2.a Penelitian Internasional',
            'is_required' => true,
            'expected_columns' => [
                'No',
                'Judul Penelitian/Inovasi',
                'Jumlah Mahasiswa Terlibat',
                'Link Bukti MOU/MOA',
                'Luaran - Publikasi Ilmiah',
                'Luaran - Karya/Inovasi',
                'Luaran - Laporan/Dokumen',
                'Luaran - Pameran/Galeri',
                'Luaran - Media Sosial',
                'Luaran - Lainnya',
                'Link Bukti Luaran'
            ],
            'template_html' => $this->getSimpleTable([
                'No',
                'Judul Penelitian/Inovasi',
                'Jumlah Mahasiswa Terlibat',
                'Link Bukti MOU/MOA',
                'Luaran - Publikasi Ilmiah',
                'Luaran - Karya/Inovasi',
                'Luaran - Laporan/Dokumen',
                'Luaran - Pameran/Galeri',
                'Luaran - Media Sosial',
                'Luaran - Lainnya',
                'Link Bukti Luaran'
            ], 5),
            'keterangan' => 'Penelitian kolaborasi internasional (DTPS + Mahasiswa). Luaran: isi jumlah untuk setiap kategori.',
            'urutan' => 2,
        ]);
    }

    private function seedR3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.DESC',
            'nama' => 'Deskripsi Luaran dan Dampak Penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi luaran dan dampak penelitian',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    private function seedR4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.4.DESC',
            'nama' => 'Deskripsi Kebijakan Pengabdian kepada Masyarakat',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi kebijakan pengabdian',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
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
            'nama' => 'Deskripsi Proses Pengabdian kepada Masyarakat',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi proses pengabdian',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    private function seedR6()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.6')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.DESC',
            'nama' => 'Deskripsi Luaran dan Dampak Pengabdian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi luaran dan dampak pengabdian',
            'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi...]',
            'is_required' => true,
            'urutan' => 1,
        ]);
    }

    // ========================================
    // HELPER FUNCTION - Simple Table Generator
    // ========================================

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
                if ($index === 0 && $column === 'No' || $column === 'No.') {
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
}
