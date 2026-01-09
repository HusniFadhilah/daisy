<?php
// database/seeders/DatasetBorangSeeder.php

namespace Database\Seeders;

use App\Models\DatasetBorang;
use App\Models\ElemenStandar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExampleDatasetBorangSeeder extends Seeder
{
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
    // IK-1.1.1: (1) Legalitas lengkap (2) Struktur organisasi (3) Dokumen tata pamong (4) Pelaporan PDDIKTI
    // ========================================
    private function seedD1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.1')->first();
        if (!$elemen) return;

        // 1. Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.1.DESC',
            'nama' => 'Deskripsi Legalitas Program dan Tata Pamong',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan legalitas program studi dan sistem tata pamong yang menunjukkan Good University Governance (GUG)...',
            'is_required' => true,
            'keterangan' => 'Jelaskan bagaimana institusi menerapkan GUG dalam tata pamong',
            'urutan' => 1,
        ]);

        // 2. Tabel D.1.a - Dokumen Legalitas
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.1.a',
            'nama' => 'Tabel D.1.a Dokumen Legalitas Program Studi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.1.a - Dokumen Legalitas',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Dokumen', 'Nomor SK/Dokumen', 'Tanggal Penetapan', 'Masa Berlaku', 'Bukti Dokumen'],
            'template_html' => $this->getTemplateD1a(),
            'keterangan' => 'SK Pendirian, SK Operasional, SK Akreditasi, Kerjasama dalam/luar negeri, dll',
            'urutan' => 2,
        ]);

        // 3. Tabel D.1.b - Struktur Organisasi dan Tata Kelola
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.1.b',
            'nama' => 'Tabel D.1.b Struktur Organisasi Program Studi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.1.b - Struktur Organisasi',
            'is_required' => true,
            'expected_columns' => ['No', 'Jabatan', 'Nama', 'NIP/NIDN', 'Masa Jabatan', 'SK Pengangkatan'],
            'template_html' => $this->getTemplateD1b(),
            'keterangan' => 'Lengkapi struktur organisasi program studi',
            'urutan' => 3,
        ]);

        // 4. Tabel D.1.c - Dokumen Tata Pamong
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.1.c',
            'nama' => 'Tabel D.1.c Dokumen Tata Pamong',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.1.c - Dokumen Tata Pamong',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Dokumen', 'Nama Dokumen', 'Nomor Dokumen', 'Tanggal Penetapan', 'Bukti'],
            'template_html' => $this->getTemplateD1c(),
            'keterangan' => 'Statuta, Renstra, Renop, SOP, Pedoman, dll',
            'urutan' => 4,
        ]);

        // 5. Tabel D.1.d - Pelaporan PDDIKTI
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.1.d',
            'nama' => 'Tabel D.1.d Pelaporan PDDIKTI',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.1.d - Data PDDIKTI',
            'is_required' => true,
            'expected_columns' => ['Tahun Akademik', 'Status Pelaporan', 'Tanggal Pelaporan', 'Persentase Kelengkapan', 'Keterangan'],
            'template_html' => $this->getTemplateD1d(),
            'keterangan' => 'Riwayat pelaporan ke PDDIKTI',
            'urutan' => 5,
        ]);
    }

    // ========================================
    // D.2 - Visi, Misi, Tujuan, dan Strategi
    // IK-1.2.1: (1) VMTS jelas (2) Terukur (3) Implementasi perencanaan (4) Evaluasi VMTS
    // ========================================
    private function seedD2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.2')->first();
        if (!$elemen) return;

        // 1. Deskripsi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.2.DESC',
            'nama' => 'Deskripsi Visi, Misi, Tujuan, dan Strategi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan bagaimana VMTS disusun, disosialisasikan, dan dievaluasi...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // 2. Tabel D.2.a - VMTS Program Studi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.2.a',
            'nama' => 'Tabel D.2.a Visi, Misi, Tujuan, dan Strategi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.2.a - VMTS',
            'is_required' => true,
            'expected_columns' => ['Aspek', 'Uraian'],
            'template_html' => $this->getTemplateD2a(),
            'urutan' => 2,
        ]);

        // 3. Tabel D.2.b - Indikator Kinerja Strategis
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.2.b',
            'nama' => 'Tabel D.2.b Indikator Kinerja Strategis',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.2.b - IKU/IKT',
            'is_required' => true,
            'expected_columns' => ['No', 'Indikator', 'Target', 'Metode Pengukuran', 'Baseline', 'Capaian Saat Ini'],
            'template_html' => $this->getTemplateD2b(),
            'keterangan' => 'IKU/IKT yang terukur untuk mencapai VMTS',
            'urutan' => 3,
        ]);

        // 4. Tabel D.2.c - Rencana Strategis
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.2.c',
            'nama' => 'Tabel D.2.c Rencana Strategis (Renstra)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.2.c - Renstra',
            'is_required' => true,
            'expected_columns' => ['No', 'Program Strategis', 'Tujuan', 'Tahun Pelaksanaan', 'PIC', 'Status'],
            'template_html' => $this->getTemplateD2c(),
            'urutan' => 4,
        ]);

        // 5. Tabel D.2.d - Evaluasi VMTS
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.2.d',
            'nama' => 'Tabel D.2.d Evaluasi VMTS',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.2.d - Evaluasi VMTS',
            'is_required' => true,
            'expected_columns' => ['Tahun Evaluasi', 'Aspek yang Dievaluasi', 'Metode Evaluasi', 'Hasil Evaluasi', 'Tindak Lanjut', 'Bukti'],
            'template_html' => $this->getTemplateD2d(),
            'keterangan' => 'Riwayat evaluasi dan revisi VMTS',
            'urutan' => 5,
        ]);
    }

    // ========================================
    // D.3 - Kesesuaian Visi Keilmuan
    // IK-1.3.1: (1) Diferensiasi (2) Metode pengukuran (3) Dukungan sumber daya (4) Strategi daya saing
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
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan fokus dan diferensiasi visi keilmuan program studi...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // Tabel D.3.a - Visi Keilmuan dan Diferensiasi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.3.a',
            'nama' => 'Tabel D.3.a Visi Keilmuan dan Diferensiasi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.3.a',
            'is_required' => true,
            'expected_columns' => ['Aspek', 'Visi Keilmuan Program Studi', 'Diferensiasi/Keunggulan', 'Bukti'],
            'template_html' => $this->getTemplateD3a(),
            'urutan' => 2,
        ]);

        // Tabel D.3.b - Analisis Dukungan Sumber Daya
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.3.b',
            'nama' => 'Tabel D.3.b Analisis Dukungan Sumber Daya',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.3.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Sumber Daya', 'Ketersediaan', 'Kecukupan', 'Rencana Pengembangan'],
            'template_html' => $this->getTemplateD3b(),
            'keterangan' => 'SDM, Sarana, Dana, dll',
            'urutan' => 3,
        ]);

        // Tabel D.3.c - Strategi Daya Saing
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'D.3.c',
            'nama' => 'Tabel D.3.c Strategi Pencapaian Daya Saing',
            'tipe_field' => 'table',
            'label_field' => 'Tabel D.3.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Strategi', 'Skala (Regional/Nasional/Internasional)', 'Target Capaian', 'Tahun', 'Status'],
            'template_html' => $this->getTemplateD3c(),
            'urutan' => 4,
        ]);
    }

    // ========================================
    // E.1 - Kurikulum
    // IK-2.1.1: (1) OBE (2) Keterlibatan eksternal (3) Implementasi (4) Evaluasi kurikulum
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
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan desain kurikulum berbasis OBE dan proses pengembangannya...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // E.1.a - CPL
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.1.a',
            'nama' => 'Tabel E.1.a Capaian Pembelajaran Lulusan (CPL)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.1.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Kode CPL', 'Deskripsi CPL', 'Kategori (Sikap/Pengetahuan/Keterampilan Umum/Keterampilan Khusus)'],
            'template_html' => $this->getTemplateE1a(),
            'urutan' => 2,
        ]);

        // E.1.b - Struktur Kurikulum
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.1.b',
            'nama' => 'Tabel E.1.b Struktur Kurikulum',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.1.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Sifat (Wajib/Pilihan)', 'CPL yang Dibebankan'],
            'template_html' => $this->getTemplateE1b(),
            'urutan' => 3,
        ]);

        // E.1.c - Keterlibatan Eksternal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.1.c',
            'nama' => 'Tabel E.1.c Keterlibatan Pihak Eksternal dalam Pengembangan Kurikulum',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.1.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama/Institusi', 'Jenis Pihak Eksternal', 'Bentuk Keterlibatan', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateE1c(),
            'keterangan' => 'Organisasi profesi, industri, alumni, ahli, dll',
            'urutan' => 4,
        ]);

        // E.1.d - RPS
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.1.d',
            'nama' => 'Tabel E.1.d Rencana Pembelajaran Semester (RPS)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.1.d',
            'is_required' => true,
            'expected_columns' => ['No', 'Kode MK', 'Nama Mata Kuliah', 'Tahun Penyusunan/Revisi', 'Bukti RPS'],
            'template_html' => $this->getTemplateE1d(),
            'urutan' => 5,
        ]);

        // E.1.e - Evaluasi Kurikulum
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.1.e',
            'nama' => 'Tabel E.1.e Evaluasi dan Revisi Kurikulum',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.1.e',
            'is_required' => true,
            'expected_columns' => ['Tahun Evaluasi', 'Metode Evaluasi', 'Hasil Evaluasi', 'Tindak Lanjut/Revisi', 'Bukti'],
            'template_html' => $this->getTemplateE1e(),
            'keterangan' => 'Siklus evaluasi kurikulum',
            'urutan' => 6,
        ]);
    }

    // ========================================
    // E.2 - Admisi Mahasiswa
    // IK-2.2.1 & IKn-2.2.2: (1) Kebijakan (2) Afirmasi (3) Transparan (4) Rasio (5) Mahasiswa asing
    // ========================================
    private function seedE2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.DESC',
            'nama' => 'Deskripsi Sistem Admisi Mahasiswa',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan kebijakan dan proses admisi mahasiswa...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // E.2.a - Data Penerimaan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.a',
            'nama' => 'Tabel E.2.a Data Penerimaan Mahasiswa Baru',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.a',
            'is_required' => true,
            'expected_columns' => ['Tahun Akademik', 'Daya Tampung', 'Pendaftar', 'Lulus Seleksi', 'Registrasi', 'Rasio Pendaftar/Diterima'],
            'template_html' => $this->getTemplateE2a(),
            'keterangan' => 'Data 3 tahun terakhir',
            'urutan' => 2,
        ]);

        // E.2.b - Mahasiswa Afirmasi & Beasiswa
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.b',
            'nama' => 'Tabel E.2.b Mahasiswa Afirmasi dan Beasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.b',
            'is_required' => true,
            'expected_columns' => ['Tahun Akademik', 'Mahasiswa Reguler', 'Mahasiswa Afirmasi', 'Jenis Afirmasi', 'Penerima Beasiswa', 'Sumber Beasiswa'],
            'template_html' => $this->getTemplateE2b(),
            'keterangan' => 'Data inklusivitas pendidikan',
            'urutan' => 3,
        ]);

        // E.2.c - Mahasiswa Asing & Internasionalisasi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.2.c',
            'nama' => 'Tabel E.2.c Mahasiswa Asing dan Kegiatan Internasional',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.2.c',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Jumlah Mahasiswa Asing', 'Asal Negara', 'Jenis Program', 'Durasi', 'Keterangan'],
            'template_html' => $this->getTemplateE2c(),
            'keterangan' => 'Termasuk student exchange, credit earning, dll',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // E.3 - Proses dan Siklus Pembelajaran
    // IK-2.3.1 & IKn-2.3.2: (1) Pelaksanaan tertib (2) Dokumentasi (3) Evaluasi (4) Masa studi (5) Prestasi
    // ========================================
    private function seedE3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.DESC',
            'nama' => 'Deskripsi Proses dan Siklus Pembelajaran',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan pelaksanaan dan dokumentasi siklus pembelajaran...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // E.3.a - Metode Pembelajaran
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.a',
            'nama' => 'Tabel E.3.a Metode Pembelajaran',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Kode MK', 'Nama Mata Kuliah', 'Metode Pembelajaran', 'Bentuk Tugas', 'Bentuk Evaluasi'],
            'template_html' => $this->getTemplateE3a(),
            'urutan' => 2,
        ]);

        // E.3.b - Data Masa Studi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.b',
            'nama' => 'Tabel E.3.b Data Masa Studi Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.b',
            'is_required' => true,
            'expected_columns' => ['Tahun Lulus', 'Jumlah Lulusan', 'Masa Studi ≤4 Tahun', 'Masa Studi 4-5 Tahun', 'Masa Studi >5 Tahun', 'Rata-rata Masa Studi (Bulan)'],
            'template_html' => $this->getTemplateE3b(),
            'keterangan' => 'Efektivitas masa studi',
            'urutan' => 3,
        ]);

        // E.3.c - Prestasi Akademik Mahasiswa
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.3.c',
            'nama' => 'Tabel E.3.c Prestasi Akademik Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.3.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Mahasiswa', 'Jenis Prestasi', 'Tingkat (Lokal/Nasional/Internasional)', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateE3c(),
            'keterangan' => 'Kompetisi, olimpiade, lomba karya ilmiah, dll',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // E.4 - Penilaian dan Evaluasi
    // IK-2.4.1: (1) Kebijakan berbasis capaian (2) Dokumentasi (3) Keterlibatan profesi (4) Evaluasi
    // ========================================
    private function seedE4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.4.DESC',
            'nama' => 'Deskripsi Sistem Penilaian dan Evaluasi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan sistem penilaian berbasis capaian pembelajaran...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // E.4.a - Sistem Penilaian
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.4.a',
            'nama' => 'Tabel E.4.a Sistem Penilaian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.4.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Kode MK', 'Aspek Penilaian', 'Metode', 'Bobot (%)', 'Instrumen', 'CPL yang Diukur'],
            'template_html' => $this->getTemplateE4a(),
            'urutan' => 2,
        ]);

        // E.4.b - Keterlibatan Organisasi Profesi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.4.b',
            'nama' => 'Tabel E.4.b Keterlibatan Organisasi Profesi dalam Penilaian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.4.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Organisasi Profesi', 'Bentuk Keterlibatan', 'Jenis Penilaian/Uji Kompetensi', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateE4b(),
            'keterangan' => 'Uji kompetensi, sertifikasi, dll',
            'urutan' => 3,
        ]);
    }

    // ========================================
    // E.5 - Kompetensi Lulusan dan Capaian Pembelajaran
    // IK-2.5.1: (1) Sistem pengukuran (2) Matriks MK-CPL (3) Keterlibatan profesi (4) Unit evaluasi
    // ========================================
    private function seedE5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.5')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.DESC',
            'nama' => 'Deskripsi Kompetensi Lulusan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan sistem pengukuran capaian pembelajaran lulusan...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // E.5.a - Data Lulusan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.a',
            'nama' => 'Tabel E.5.a Data Lulusan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.a',
            'is_required' => true,
            'expected_columns' => ['Tahun Lulus', 'Jumlah Lulusan', 'IPK Rata-rata', 'Masa Studi Rata-rata (Bulan)', 'Predikat Kelulusan'],
            'template_html' => $this->getTemplateE5a(),
            'urutan' => 2,
        ]);

        // E.5.b - Matriks MK-CPL
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.b',
            'nama' => 'Tabel E.5.b Matriks Kesesuaian Mata Kuliah dan CPL',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Kode MK', 'Nama Mata Kuliah', 'CPL yang Dibebankan', 'Model Pembelajaran', 'Keterangan'],
            'template_html' => $this->getTemplateE5b(),
            'urutan' => 3,
        ]);

        // E.5.c - Prestasi Mahasiswa
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'E.5.c',
            'nama' => 'Tabel E.5.c Prestasi Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel E.5.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Mahasiswa', 'Jenis Prestasi', 'Tingkat', 'Tahun', 'Penyelenggara', 'Bukti'],
            'template_html' => $this->getTemplateE5c(),
            'urutan' => 4,
        ]);
    }

    // ========================================
    // P.1 - Dosen dan Tenaga Kependidikan
    // IK-3.1.1 & IKn-3.1.1: (1) Ketercukupan dosen (2) Tenaga kependidikan (3) Pengembangan (4) Sertifikasi
    // ========================================
    private function seedP1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.1.DESC',
            'nama' => 'Deskripsi Dosen dan Tenaga Kependidikan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan profil dan ketercukupan SDM...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // P.1.a - Dosen Tetap
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.1.a',
            'nama' => 'Tabel P.1.a Dosen Tetap Program Studi (DTPS)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.1.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Dosen', 'NIDN/NIDK', 'Pendidikan Terakhir', 'Bidang Keahlian', 'Jabatan Akademik', 'Sertifikat Pendidik', 'Sertifikat Keahlian/Profesi'],
            'template_html' => $this->getTemplateP1a(),
            'urutan' => 2,
        ]);

        // P.1.b - Dosen Tidak Tetap & Praktisi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.1.b',
            'nama' => 'Tabel P.1.b Dosen Tidak Tetap dan Praktisi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.1.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama', 'Pendidikan', 'Bidang Keahlian', 'Jenis (DTT/Praktisi)', 'Institusi Asal', 'Mata Kuliah yang Diampu'],
            'template_html' => $this->getTemplateP1b(),
            'urutan' => 3,
        ]);

        // P.1.c - Tenaga Kependidikan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.1.c',
            'nama' => 'Tabel P.1.c Tenaga Kependidikan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.1.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama', 'Jabatan', 'Pendidikan Terakhir', 'Unit Kerja', 'Sertifikat Keahlian/Fungsional'],
            'template_html' => $this->getTemplateP1c(),
            'urutan' => 4,
        ]);

        // P.1.d - Keterlibatan Asosiasi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.1.d',
            'nama' => 'Tabel P.1.d Keterlibatan Asosiasi/Organisasi Profesi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.1.d',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Asosiasi', 'Bentuk Keterlibatan', 'Peran Dosen/Prodi', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateP1d(),
            'keterangan' => 'Keanggotaan, kontribusi dalam pengembangan kurikulum, dll',
            'urutan' => 5,
        ]);
    }

    // ========================================
    // P.2 - Sarana dan Prasarana Kerja
    // IK-3.2.1: (1) Ruang & lab dosen (2) Fasilitas tendik (3) Fasilitas umum (4) Program pemeliharaan
    // ========================================
    private function seedP2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.2.DESC',
            'nama' => 'Deskripsi Sarana dan Prasarana Kerja',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan ketersediaan dan kecukupan sarana prasarana kerja...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // P.2.a - Ruang Kerja Dosen
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.2.a',
            'nama' => 'Tabel P.2.a Ruang Kerja Dosen',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.2.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Ruang', 'Luas (m²)', 'Jumlah', 'Kapasitas', 'Kondisi', 'Utilisasi'],
            'template_html' => $this->getTemplateP2a(),
            'keterangan' => 'Ruang dosen, ruang rapat, ruang penelitian, dll',
            'urutan' => 2,
        ]);

        // P.2.b - Laboratorium & Fasilitas Riset
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.2.b',
            'nama' => 'Tabel P.2.b Laboratorium dan Fasilitas Riset',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.2.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Laboratorium', 'Luas (m²)', 'Jenis Alat Utama', 'Jumlah Alat', 'Kondisi', 'Penanggung Jawab'],
            'template_html' => $this->getTemplateP2b(),
            'urutan' => 3,
        ]);

        // P.2.c - Fasilitas Tenaga Kependidikan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.2.c',
            'nama' => 'Tabel P.2.c Fasilitas Tenaga Kependidikan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.2.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Unit Kerja', 'Jenis Fasilitas', 'Jumlah', 'Kondisi', 'Kecukupan'],
            'template_html' => $this->getTemplateP2c(),
            'keterangan' => 'Komputer, meja kerja, alat kantor, dll',
            'urutan' => 4,
        ]);

        // P.2.d - Fasilitas Umum & Pendukung
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.2.d',
            'nama' => 'Tabel P.2.d Fasilitas Umum',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.2.d',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Fasilitas', 'Luas/Kapasitas', 'Jumlah', 'Kondisi', 'Aksesibilitas'],
            'template_html' => $this->getTemplateP2d(),
            'keterangan' => 'Toilet, mushola, kantin, parkir, akses diffabel, dll',
            'urutan' => 5,
        ]);

        // P.2.e - Program Pemeliharaan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.2.e',
            'nama' => 'Tabel P.2.e Program Pemeliharaan Sarana Prasarana',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.2.e',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Kegiatan', 'Frekuensi', 'Penanggung Jawab', 'Anggaran', 'Bukti Pelaksanaan'],
            'template_html' => $this->getTemplateP2e(),
            'urutan' => 6,
        ]);
    }

    // ========================================
    // P.3 - Pengembangan Kapasitas
    // IK-3.3.1: (1) Rekrutasi merit (2) Pengembangan kapasitas (3) Reward & punishment (4) Manajerial
    // ========================================
    private function seedP3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.3.DESC',
            'nama' => 'Deskripsi Pengembangan Kapasitas',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan kebijakan dan program pengembangan kapasitas SDM...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // P.3.a - Kebijakan Rekrutmen
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.3.a',
            'nama' => 'Tabel P.3.a Kebijakan dan Proses Rekrutmen',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.3.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Aspek Rekrutmen', 'Kebijakan/Prosedur', 'Dokumen', 'Implementasi', 'Bukti'],
            'template_html' => $this->getTemplateP3a(),
            'keterangan' => 'Merit system, berkeadilan, non-diskriminatif',
            'urutan' => 2,
        ]);

        // P.3.b - Program Pengembangan Dosen
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.3.b',
            'nama' => 'Tabel P.3.b Program Pengembangan Dosen',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.3.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Dosen', 'Jenis Pengembangan', 'Tahun', 'Penyelenggara', 'Sumber Dana', 'Bukti'],
            'template_html' => $this->getTemplateP3b(),
            'keterangan' => 'Pelatihan, workshop, studi lanjut, sertifikasi, dll',
            'urutan' => 3,
        ]);

        // P.3.c - Program Pengembangan Tendik
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.3.c',
            'nama' => 'Tabel P.3.c Program Pengembangan Tenaga Kependidikan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.3.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama', 'Jenis Pengembangan', 'Tahun', 'Penyelenggara', 'Sumber Dana', 'Bukti'],
            'template_html' => $this->getTemplateP3c(),
            'urutan' => 4,
        ]);

        // P.3.d - Sistem Penghargaan & Sanksi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.3.d',
            'nama' => 'Tabel P.3.d Sistem Penghargaan dan Sanksi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.3.d',
            'is_required' => true,
            'expected_columns' => ['Aspek', 'Kebijakan/Dokumen', 'Kriteria', 'Implementasi', 'Bukti'],
            'template_html' => $this->getTemplateP3d(),
            'keterangan' => 'Reward dan punishment',
            'urutan' => 5,
        ]);

        // P.3.e - Pengembangan Manajerial
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.3.e',
            'nama' => 'Tabel P.3.e Pengembangan Kapasitas Manajerial',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.3.e',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama', 'Jabatan', 'Jenis Pelatihan', 'Tahun', 'Penyelenggara', 'Bukti'],
            'template_html' => $this->getTemplateP3e(),
            'keterangan' => 'Pelatihan kepemimpinan, manajemen, dll',
            'urutan' => 6,
        ]);
    }

    // ========================================
    // P.4 - Kesejahteraan Kerja
    // IK-3.4.1: (1) Remunerasi (2) K3 (3) Kekerasan seksual (4) Survei kepuasan
    // ========================================
    private function seedP4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.4.DESC',
            'nama' => 'Deskripsi Kesejahteraan Kerja',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan kebijakan dan program kesejahteraan...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // P.4.a - Sistem Remunerasi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.4.a',
            'nama' => 'Tabel P.4.a Sistem Remunerasi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.4.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis SDM', 'Komponen Remunerasi', 'Rata-rata Nominal', 'Dasar Penetapan', 'Keterangan'],
            'template_html' => $this->getTemplateP4a(),
            'keterangan' => 'Gaji, tunjangan, insentif untuk dosen, tendik, dosen tidak tetap, tutor, dll',
            'urutan' => 2,
        ]);

        // P.4.b - Program K3
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.4.b',
            'nama' => 'Tabel P.4.b Program Kesehatan, Keamanan, dan Keselamatan Kerja',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.4.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Program', 'Deskripsi', 'Frekuensi', 'Penanggung Jawab', 'Bukti'],
            'template_html' => $this->getTemplateP4b(),
            'keterangan' => 'BPJS, medical check-up, asuransi, APD, dll',
            'urutan' => 3,
        ]);

        // P.4.c - Pencegahan Kekerasan Seksual
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.4.c',
            'nama' => 'Tabel P.4.c Pencegahan dan Penanganan Kekerasan Seksual',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.4.c',
            'is_required' => true,
            'expected_columns' => ['Aspek', 'Kebijakan/Dokumen', 'Unit Pelaksana', 'Program/Kegiatan', 'Bukti'],
            'template_html' => $this->getTemplateP4c(),
            'keterangan' => 'Sesuai Permendikbudristek No. 30 Tahun 2021',
            'urutan' => 4,
        ]);

        // P.4.d - Survei Kepuasan & Tindak Lanjut
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'P.4.d',
            'nama' => 'Tabel P.4.d Survei Kepuasan dan Program Peningkatan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel P.4.d',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Aspek yang Disurvei', 'Skor Kepuasan', 'Responden', 'Tindak Lanjut', 'Bukti'],
            'template_html' => $this->getTemplateP4d(),
            'urutan' => 5,
        ]);
    }

    // ========================================
    // I.1 - Sistem Penjaminan Mutu Internal
    // IK-4.1.1: (1) Unit PJM (2) Dokumen SPMI (3) Audit internal (4) Survei
    // ========================================
    private function seedI1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.1.DESC',
            'nama' => 'Deskripsi Sistem Penjaminan Mutu Internal',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan sistem dan budaya mutu yang diterapkan...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // I.1.a - Unit Penjaminan Mutu
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.1.a',
            'nama' => 'Tabel I.1.a Struktur Unit Penjaminan Mutu',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.1.a',
            'is_required' => true,
            'expected_columns' => ['Tingkat', 'Nama Unit', 'Penanggung Jawab', 'Tugas Pokok', 'SK Pembentukan'],
            'template_html' => $this->getTemplateI1a(),
            'keterangan' => 'UPM/LPM Universitas, GJM/KJM Fakultas, dll',
            'urutan' => 2,
        ]);

        // I.1.b - Dokumen SPMI
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.1.b',
            'nama' => 'Tabel I.1.b Dokumen SPMI',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.1.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Dokumen', 'Nama Dokumen', 'Nomor Dokumen', 'Tanggal Penetapan', 'Bukti'],
            'template_html' => $this->getTemplateI1b(),
            'keterangan' => 'Kebijakan mutu, manual mutu, standar, SOP, instrumen, dll',
            'urutan' => 3,
        ]);

        // I.1.c - Audit Mutu Internal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.1.c',
            'nama' => 'Tabel I.1.c Pelaksanaan Audit Mutu Internal',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.1.c',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Periode AMI', 'Auditor', 'Temuan', 'Rekomendasi', 'Bukti'],
            'template_html' => $this->getTemplateI1c(),
            'urutan' => 4,
        ]);

        // I.1.d - RTM (Rapat Tinjauan Manajemen)
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.1.d',
            'nama' => 'Tabel I.1.d Rapat Tinjauan Manajemen',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.1.d',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Tanggal RTM', 'Agenda', 'Keputusan', 'Tindak Lanjut', 'Bukti'],
            'template_html' => $this->getTemplateI1d(),
            'urutan' => 5,
        ]);

        // I.1.e - Survei Stakeholder
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.1.e',
            'nama' => 'Tabel I.1.e Survei dan Umpan Balik Stakeholder',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.1.e',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Jenis Survei', 'Responden', 'Metode', 'Hasil', 'Tindak Lanjut'],
            'template_html' => $this->getTemplateI1e(),
            'keterangan' => 'Survei mahasiswa, dosen, alumni, pengguna, dll',
            'urutan' => 6,
        ]);
    }

    // ========================================
    // I.2 - Implementasi Perbaikan Berkelanjutan
    // IK-4.2.1: (1) IKU/IKT (2) Siklus PPEPP (3) Efektivitas (4) Peningkatan standar
    // ========================================
    private function seedI2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.2.DESC',
            'nama' => 'Deskripsi Perbaikan Berkelanjutan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan implementasi siklus PPEPP dan perbaikan berkelanjutan...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // I.2.a - Indikator Kinerja
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.2.a',
            'nama' => 'Tabel I.2.a Indikator Kinerja Utama dan Tambahan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.2.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Aspek/Elemen', 'Indikator', 'Target', 'Capaian TS-2', 'Capaian TS-1', 'Capaian TS'],
            'template_html' => $this->getTemplateI2a(),
            'keterangan' => 'Mencakup tata pamong, SDM, keuangan, sarpras, pendidikan, riset, PkM, dll',
            'urutan' => 2,
        ]);

        // I.2.b - Siklus PPEPP
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.2.b',
            'nama' => 'Tabel I.2.b Pelaksanaan Siklus PPEPP',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.2.b',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Penetapan', 'Pelaksanaan', 'Evaluasi', 'Pengendalian', 'Peningkatan', 'Bukti'],
            'template_html' => $this->getTemplateI2b(),
            'keterangan' => 'Siklus Penetapan-Pelaksanaan-Evaluasi-Pengendalian-Peningkatan',
            'urutan' => 3,
        ]);

        // I.2.c - Temuan & Tindak Lanjut
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.2.c',
            'nama' => 'Tabel I.2.c Temuan dan Tindak Lanjut Perbaikan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.2.c',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Sumber Temuan', 'Temuan', 'Tindakan Perbaikan', 'PIC', 'Status', 'Bukti'],
            'template_html' => $this->getTemplateI2c(),
            'keterangan' => 'Dari AMI, survei, RTM, akreditasi, dll',
            'urutan' => 4,
        ]);

        // I.2.d - Peningkatan Standar
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.2.d',
            'nama' => 'Tabel I.2.d Peningkatan Standar Mutu',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.2.d',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Nama Standar', 'Standar Lama', 'Standar Baru', 'Dasar Peningkatan', 'Bukti'],
            'template_html' => $this->getTemplateI2d(),
            'keterangan' => 'Bukti continuous improvement',
            'urutan' => 5,
        ]);
    }

    // ========================================
    // I.3 - Keterlibatan Pengampu Kepentingan
    // IK-4.3.1: (1) Kajian eksternal (2) Pelatihan eksternal (3) Kaji ulang (4) Pengakuan eksternal
    // ========================================
    private function seedI3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.3.DESC',
            'nama' => 'Deskripsi Keterlibatan Stakeholder Eksternal',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan keterlibatan pihak eksternal dalam penjaminan mutu...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // I.3.a - Kajian dengan Ahli Eksternal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.3.a',
            'nama' => 'Tabel I.3.a Kajian dengan Ahli Eksternal',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.3.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Aspek yang Dikaji', 'Ahli/Institusi', 'Tahun', 'Hasil Kajian', 'Tindak Lanjut', 'Bukti'],
            'template_html' => $this->getTemplateI3a(),
            'keterangan' => 'Review SPMI, kurikulum, manajemen risiko, dll',
            'urutan' => 2,
        ]);

        // I.3.b - Benchmarking
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.3.b',
            'nama' => 'Tabel I.3.b Studi Banding (Benchmarking)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.3.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Institusi Tujuan', 'Aspek', 'Tahun', 'Peserta', 'Hasil', 'Tindak Lanjut'],
            'template_html' => $this->getTemplateI3b(),
            'urutan' => 3,
        ]);

        // I.3.c - Pelatihan Eksternal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.3.c',
            'nama' => 'Tabel I.3.c Pelatihan Eksternal',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.3.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Peserta', 'Jenis Pelatihan', 'Penyelenggara', 'Tahun', 'Sertifikat', 'Tindak Lanjut'],
            'template_html' => $this->getTemplateI3c(),
            'keterangan' => 'Pelatihan SPMI, auditor, penyusunan kurikulum, dll',
            'urutan' => 4,
        ]);

        // I.3.d - Pengakuan Eksternal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'I.3.d',
            'nama' => 'Tabel I.3.d Pengakuan Mutu Eksternal',
            'tipe_field' => 'table',
            'label_field' => 'Tabel I.3.d',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Pengakuan', 'Lembaga Pemberi', 'Tahun', 'Peringkat/Hasil', 'Masa Berlaku', 'Bukti'],
            'template_html' => $this->getTemplateI3d(),
            'keterangan' => 'Akreditasi, sertifikasi ISO, ranking, award, dll',
            'urutan' => 5,
        ]);
    }

    // ========================================
    // L.1 - Sarana dan Prasarana Belajar
    // IK-5.1.1 & IKn-5.1.2: (1) Ruang & lab (2) LMS (3) Ruang informal (4) Program pemeliharaan (5) Rasio
    // ========================================
    private function seedL1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.1.DESC',
            'nama' => 'Deskripsi Sarana dan Prasarana Belajar',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan ketersediaan dan kecukupan sarana prasarana pembelajaran...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // L.1.a - Ruang Kuliah
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.1.a',
            'nama' => 'Tabel L.1.a Ruang Kuliah dan Perkuliahan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.1.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Ruang', 'Luas (m²)', 'Kapasitas', 'Fasilitas', 'Kondisi', 'Utilisasi (%)'],
            'template_html' => $this->getTemplateL1a(),
            'urutan' => 2,
        ]);

        // L.1.b - Laboratorium Pembelajaran
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.1.b',
            'nama' => 'Tabel L.1.b Laboratorium/Studio Pembelajaran',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.1.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Lab/Studio', 'Luas (m²)', 'Kapasitas', 'Alat Utama', 'Jumlah Alat', 'Kondisi', 'PJ'],
            'template_html' => $this->getTemplateL1b(),
            'urutan' => 3,
        ]);

        // L.1.c - Learning Management System
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.1.c',
            'nama' => 'Tabel L.1.c Learning Management System (LMS)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.1.c',
            'is_required' => true,
            'expected_columns' => ['Aspek', 'Nama/Platform LMS', 'Fitur Utama', 'Jumlah Pengguna', 'Tingkat Utilisasi', 'Keterangan'],
            'template_html' => $this->getTemplateL1c(),
            'urutan' => 4,
        ]);

        // L.1.d - Ruang Informal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.1.d',
            'nama' => 'Tabel L.1.d Ruang Belajar Informal',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.1.d',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Ruang', 'Luas (m²)', 'Kapasitas', 'Fasilitas', 'Kondisi', 'Aksesibilitas'],
            'template_html' => $this->getTemplateL1d(),
            'keterangan' => 'Perpustakaan, ruang diskusi, taman belajar, dll',
            'urutan' => 5,
        ]);

        // L.1.e - Data Rasio
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.1.e',
            'nama' => 'Tabel L.1.e Rasio Sarana Pembelajaran',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.1.e',
            'is_required' => true,
            'expected_columns' => ['Jenis Sarana', 'Jumlah', 'Jumlah Mahasiswa', 'Rasio', 'Standar', 'Keterangan'],
            'template_html' => $this->getTemplateL1e(),
            'keterangan' => 'Rasio ruang:mahasiswa, lab:mahasiswa, alat:mahasiswa',
            'urutan' => 6,
        ]);
    }

    // ========================================
    // L.2 - Sumber Pengetahuan
    // IK-5.2.1: (1) Buku referensi (2) Jurnal (3) Software (4) Program pemeliharaan
    // ========================================
    private function seedL2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.2.DESC',
            'nama' => 'Deskripsi Sumber Pengetahuan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan ketersediaan dan aksesibilitas sumber belajar...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // L.2.a - Buku Referensi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.2.a',
            'nama' => 'Tabel L.2.a Buku Referensi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.2.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Kode MK', 'Nama Mata Kuliah', 'Judul Buku', 'Pengarang', 'Tahun Terbit', 'Jumlah Eksemplar'],
            'template_html' => $this->getTemplateL2a(),
            'keterangan' => 'Buku wajib untuk setiap RPS',
            'urutan' => 2,
        ]);

        // L.2.b - Jurnal Ilmiah
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.2.b',
            'nama' => 'Tabel L.2.b Akses Jurnal Ilmiah',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.2.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Database/Platform', 'Jenis Akses', 'Cakupan Jurnal', 'Tahun Berlangganan', 'Status'],
            'template_html' => $this->getTemplateL2b(),
            'keterangan' => 'Scopus, IEEE, ProQuest, Garuda, dll',
            'urutan' => 3,
        ]);

        // L.2.c - Software & Aplikasi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.2.c',
            'nama' => 'Tabel L.2.c Perangkat Lunak Pembelajaran',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.2.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Software', 'Fungsi/Kegunaan', 'Jenis Lisensi', 'Jumlah Lisensi', 'Aksesibilitas'],
            'template_html' => $this->getTemplateL2c(),
            'keterangan' => 'Software untuk praktikum, simulasi, analisis data, dll',
            'urutan' => 4,
        ]);

        // L.2.d - Program Peningkatan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.2.d',
            'nama' => 'Tabel L.2.d Program Peningkatan Sumber Belajar',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.2.d',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Program', 'Anggaran', 'Realisasi', 'Hasil Survei Kepuasan', 'Tindak Lanjut'],
            'template_html' => $this->getTemplateL2d(),
            'urutan' => 5,
        ]);
    }

    // ========================================
    // L.3 - Kepuasan Mahasiswa dan Alumni
    // IK-5.3.1: (1) Survei mahasiswa (2) Survei alumni (3) Instrumen valid (4) Program tindak lanjut
    // ========================================
    private function seedL3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.3.DESC',
            'nama' => 'Deskripsi Sistem Umpan Balik',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan sistem pengukuran kepuasan mahasiswa dan alumni...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // L.3.a - Survei Kepuasan Mahasiswa
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.3.a',
            'nama' => 'Tabel L.3.a Survei Kepuasan Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.3.a',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Aspek Penilaian', 'Skor (1-5)', 'Jumlah Responden', 'Response Rate (%)', 'Tindak Lanjut'],
            'template_html' => $this->getTemplateL3a(),
            'keterangan' => 'Proses belajar mengajar, fasilitas, layanan, dll',
            'urutan' => 2,
        ]);

        // L.3.b - Survei Alumni
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.3.b',
            'nama' => 'Tabel L.3.b Survei Kepuasan Alumni',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.3.b',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Aspek Penilaian', 'Skor (1-5)', 'Jumlah Responden', 'Masa Tunggu Kerja', 'Kesesuaian Bidang'],
            'template_html' => $this->getTemplateL3b(),
            'keterangan' => 'Kesesuaian materi dengan pekerjaan, kompetensi, dll',
            'urutan' => 3,
        ]);

        // L.3.c - Validitas Instrumen
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.3.c',
            'nama' => 'Tabel L.3.c Validitas Instrumen Survei',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.3.c',
            'is_required' => true,
            'expected_columns' => ['Jenis Survei', 'Metode Validasi', 'Nilai Validitas', 'Nilai Reliabilitas', 'Tahun Validasi', 'Bukti'],
            'template_html' => $this->getTemplateL3c(),
            'keterangan' => 'Uji validitas dan reliabilitas instrumen',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // L.4 - Lulusan, Kajian Telusur
    // IK-5.4.1: (1) Survei pengguna (2) Konsisten (3) Instrumen valid (4) Program tindak lanjut
    // ========================================
    private function seedL4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.4.DESC',
            'nama' => 'Deskripsi Tracer Study',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan sistem kajian telusur lulusan...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // L.4.a - Data Lulusan & Pekerjaan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.4.a',
            'nama' => 'Tabel L.4.a Data Lulusan dan Status Pekerjaan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.4.a',
            'is_required' => true,
            'expected_columns' => ['Tahun Lulus', 'Jumlah Lulusan', 'Bekerja', 'Wirausaha', 'Melanjutkan Studi', 'Belum Bekerja', 'Masa Tunggu (Bulan)'],
            'template_html' => $this->getTemplateL4a(),
            'keterangan' => 'Data 3 tahun terakhir',
            'urutan' => 2,
        ]);

        // L.4.b - Survei Pengguna Lulusan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.4.b',
            'nama' => 'Tabel L.4.b Survei Kepuasan Pengguna Lulusan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.4.b',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Aspek Kompetensi', 'Skor (1-5)', 'Jumlah Responden', 'Response Rate (%)', 'Tindak Lanjut'],
            'template_html' => $this->getTemplateL4b(),
            'keterangan' => 'Sikap, pengetahuan, keterampilan, kompetensi pendukung',
            'urutan' => 3,
        ]);

        // L.4.c - Daftar Pengguna Lulusan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'L.4.c',
            'nama' => 'Tabel L.4.c Daftar Institusi Pengguna Lulusan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel L.4.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Institusi/Perusahaan', 'Jenis Institusi', 'Jumlah Alumni', 'Posisi/Jabatan', 'Kesesuaian Bidang'],
            'template_html' => $this->getTemplateL4c(),
            'urutan' => 4,
        ]);
    }

    // ========================================
    // A.1 - Organisasi dan Tata Kelola
    // IK-6.1.1: (1) Struktur jelas (2) Penugasan berbasis kompetensi (3) Kepemimpinan efektif (4) Prosedur lengkap
    // ========================================
    private function seedA1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.1.DESC',
            'nama' => 'Deskripsi Organisasi dan Tata Kelola',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan struktur organisasi dan tata kelola yang akuntabel...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // A.1.a - Struktur Organisasi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.1.a',
            'nama' => 'Tabel A.1.a Struktur Organisasi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.1.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Jabatan', 'Nama', 'NIP/NIDN', 'Tugas Pokok', 'Masa Jabatan', 'SK'],
            'template_html' => $this->getTemplateA1a(),
            'urutan' => 2,
        ]);

        // A.1.b - Mekanisme Penugasan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.1.b',
            'nama' => 'Tabel A.1.b Mekanisme Penugasan dan Pengisian Jabatan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.1.b',
            'is_required' => true,
            'expected_columns' => ['Jabatan', 'Kriteria Kompetensi', 'Mekanisme Seleksi', 'Dokumen/SOP', 'Bukti'],
            'template_html' => $this->getTemplateA1b(),
            'keterangan' => 'Berbasis kompetensi, transparansi, akuntabilitas',
            'urutan' => 3,
        ]);

        // A.1.c - Prosedur Operasional
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.1.c',
            'nama' => 'Tabel A.1.c Dokumen Prosedur Operasional',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.1.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama SOP', 'Cakupan', 'Nomor Dokumen', 'Tanggal Penetapan', 'Bukti'],
            'template_html' => $this->getTemplateA1c(),
            'keterangan' => 'SOP Tridarma dan operasional',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // A.2 - Kerja Sama dan Kemitraan
    // IK-6.2.1 & IKn-6.2.2: (1) Kebijakan (2) Legalitas (3) Implementasi (4) Kemitraan resiprokal
    // ========================================
    private function seedA2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.2.DESC',
            'nama' => 'Deskripsi Kerja Sama dan Kemitraan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan kebijakan dan sistem kerjasama...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // A.2.a - Daftar Kerjasama
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.2.a',
            'nama' => 'Tabel A.2.a Daftar Kerja Sama',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.2.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Mitra', 'Jenis Mitra', 'Lingkup', 'Bidang', 'Tahun', 'Durasi', 'Status', 'Bukti MoU'],
            'template_html' => $this->getTemplateA2a(),
            'keterangan' => 'Dalam/luar negeri, pemerintah/PT/industri/komunitas, pendidikan/riset/PkM/kelembagaan',
            'urutan' => 2,
        ]);

        // A.2.b - Realisasi Kerjasama
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.2.b',
            'nama' => 'Tabel A.2.b Realisasi Kerja Sama',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.2.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Mitra', 'Kegiatan/Program', 'Tahun', 'Peserta/Penerima Manfaat', 'Hasil/Luaran', 'Bukti'],
            'template_html' => $this->getTemplateA2b(),
            'urutan' => 3,
        ]);

        // A.2.c - Kemitraan Resiprokal
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.2.c',
            'nama' => 'Tabel A.2.c Kemitraan Resiprokal (Joint/Dual Degree)',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.2.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Institusi Mitra', 'Negara', 'Jenis Program', 'Tahun', 'Jumlah Mahasiswa', 'Status', 'Bukti'],
            'template_html' => $this->getTemplateA2c(),
            'keterangan' => 'Gelar ganda, credit transfer, student exchange, dll',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // A.3 - Sistem dan Manajemen Informasi
    // IK-6.3.1: (1) Sistem akademik (2) Sistem pengelolaan (3) Keandalan (4) Program pengembangan
    // ========================================
    private function seedA3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.3.DESC',
            'nama' => 'Deskripsi Sistem Informasi',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan sistem manajemen data dan informasi...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // A.3.a - Sistem Informasi
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.3.a',
            'nama' => 'Tabel A.3.a Sistem Informasi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.3.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Sistem', 'Fungsi/Tujuan', 'Platform', 'Pengguna', 'Integrasi', 'Status'],
            'template_html' => $this->getTemplateA3a(),
            'keterangan' => 'SIAKAD, SIMKEU, SIMPEG, Portal, dll',
            'urutan' => 2,
        ]);

        // A.3.b - Keandalan Sistem
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.3.b',
            'nama' => 'Tabel A.3.b Keandalan dan Efektivitas Sistem',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.3.b',
            'is_required' => true,
            'expected_columns' => ['Aspek', 'Indikator', 'Target', 'Capaian', 'Bukti', 'Tindak Lanjut'],
            'template_html' => $this->getTemplateA3b(),
            'keterangan' => 'Uptime, response time, integritas data, keamanan, dll',
            'urutan' => 3,
        ]);

        // A.3.c - Program Pengembangan SI
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.3.c',
            'nama' => 'Tabel A.3.c Program Pengembangan Sistem Informasi',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.3.c',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Program Pengembangan', 'Tujuan', 'Anggaran', 'Status', 'Hasil'],
            'template_html' => $this->getTemplateA3c(),
            'urutan' => 4,
        ]);
    }

    // ========================================
    // A.4 - Keselamatan dan Kesehatan Kerja
    // IK-6.4.1: (1) Kebijakan K3 (2) Kelestarian lingkungan (3) Efektivitas (4) Program berkelanjutan
    // ========================================
    private function seedA4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.4.DESC',
            'nama' => 'Deskripsi K3 dan Kelestarian Lingkungan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan kebijakan dan implementasi K3L...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // A.4.a - Program K3
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.4.a',
            'nama' => 'Tabel A.4.a Program Keselamatan, Kesehatan, dan Keamanan Kerja',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.4.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Program', 'Deskripsi', 'Frekuensi', 'PIC', 'Anggaran', 'Bukti'],
            'template_html' => $this->getTemplateA4a(),
            'keterangan' => 'APAR, P3K, evakuasi, simulasi, APD, dll',
            'urutan' => 2,
        ]);

        // A.4.b - Program Kelestarian Lingkungan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.4.b',
            'nama' => 'Tabel A.4.b Program Kelestarian Lingkungan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.4.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Program', 'Deskripsi', 'Target', 'Capaian', 'Bukti'],
            'template_html' => $this->getTemplateA4b(),
            'keterangan' => 'Pengelolaan sampah, energi, air, emisi, penghijauan, dll',
            'urutan' => 3,
        ]);

        // A.4.c - Sertifikasi & Pengakuan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.4.c',
            'nama' => 'Tabel A.4.c Sertifikasi dan Pengakuan K3L',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.4.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Sertifikasi/Award', 'Lembaga Pemberi', 'Tahun', 'Masa Berlaku', 'Bukti'],
            'template_html' => $this->getTemplateA4c(),
            'keterangan' => 'ISO 14001, Green Campus, dll',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // A.5 - Keuangan, Keberlanjutan
    // IK-6.5.1: (1) Kebijakan keuangan (2) Efektivitas (3) Audit (4) Mitigasi risiko
    // ========================================
    private function seedA5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.5')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.5.DESC',
            'nama' => 'Deskripsi Pengelolaan Keuangan',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan sistem pengelolaan keuangan dan keberlanjutan...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // A.5.a - Laporan Keuangan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.5.a',
            'nama' => 'Tabel A.5.a Laporan Keuangan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.5.a',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Sumber Dana', 'Penerimaan (Rp)', 'Pengeluaran (Rp)', 'Saldo (Rp)', 'Persentase Utilisasi'],
            'template_html' => $this->getTemplateA5a(),
            'keterangan' => 'Data 3 tahun terakhir',
            'urutan' => 2,
        ]);

        // A.5.b - Alokasi Anggaran
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.5.b',
            'nama' => 'Tabel A.5.b Alokasi Anggaran per Kegiatan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.5.b',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Jenis Kegiatan', 'Anggaran (Rp)', 'Realisasi (Rp)', 'Persentase (%)', 'Keterangan'],
            'template_html' => $this->getTemplateA5b(),
            'keterangan' => 'Pendidikan, penelitian, PkM, operasional, dll',
            'urutan' => 3,
        ]);

        // A.5.c - Audit Keuangan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.5.c',
            'nama' => 'Tabel A.5.c Audit Keuangan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.5.c',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Jenis Audit', 'Auditor', 'Hasil Audit', 'Temuan', 'Tindak Lanjut', 'Bukti'],
            'template_html' => $this->getTemplateA5c(),
            'keterangan' => 'Internal audit, eksternal audit, BPK, dll',
            'urutan' => 4,
        ]);

        // A.5.d - Manajemen Risiko
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'A.5.d',
            'nama' => 'Tabel A.5.d Manajemen Risiko Keuangan',
            'tipe_field' => 'table',
            'label_field' => 'Tabel A.5.d',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Risiko', 'Probabilitas', 'Dampak', 'Mitigasi', 'PIC', 'Status'],
            'template_html' => $this->getTemplateA5d(),
            'keterangan' => 'Analisis dan mitigasi risiko keuangan',
            'urutan' => 5,
        ]);
    }

    // ========================================
    // R.1 - Kebijakan Penelitian
    // IK-7.1.1: (1) Roadmap (2) Sosialisasi (3) Pendanaan (4) Review
    // ========================================
    private function seedR1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.1')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.1.DESC',
            'nama' => 'Deskripsi Kebijakan Penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan roadmap dan kebijakan penelitian...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // R.1.a - Roadmap Penelitian
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.1.a',
            'nama' => 'Tabel R.1.a Roadmap Penelitian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.1.a',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Tema Penelitian', 'Target Luaran', 'Anggaran', 'Penanggung Jawab', 'Status'],
            'template_html' => $this->getTemplateR1a(),
            'keterangan' => 'Peta jalan penelitian program studi',
            'urutan' => 2,
        ]);

        // R.1.b - Dokumen Kebijakan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.1.b',
            'nama' => 'Tabel R.1.b Dokumen Kebijakan Penelitian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.1.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Dokumen', 'Nama Dokumen', 'Nomor', 'Tanggal Penetapan', 'Bukti'],
            'template_html' => $this->getTemplateR1b(),
            'keterangan' => 'Pedoman, SOP, panduan penelitian',
            'urutan' => 3,
        ]);

        // R.1.c - Pendanaan Penelitian
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.1.c',
            'nama' => 'Tabel R.1.c Pendanaan Penelitian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.1.c',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Sumber Dana', 'Jumlah Proposal', 'Jumlah Didanai', 'Total Dana (Rp)', 'Keterangan'],
            'template_html' => $this->getTemplateR1c(),
            'keterangan' => 'Internal, eksternal (Kemendikbud, LPDP, industri, dll)',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // R.2 - Proses Penelitian
    // IK-7.2.1: (1) Dokumen penelitian dosen (2) Kolaborasi dosen-mahasiswa (3) Manfaat (4) Diseminasi
    // ========================================
    private function seedR2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.2')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.2.DESC',
            'nama' => 'Deskripsi Proses Penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan proses pelaksanaan penelitian...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // R.2.a - Penelitian Dosen
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.2.a',
            'nama' => 'Tabel R.2.a Daftar Penelitian Dosen',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.2.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Dosen', 'Judul Penelitian', 'Tahun', 'Sumber Dana', 'Jumlah Dana (Rp)', 'Status', 'Luaran'],
            'template_html' => $this->getTemplateR2a(),
            'keterangan' => 'Sesuai roadmap penelitian',
            'urutan' => 2,
        ]);

        // R.2.b - Kolaborasi Dosen-Mahasiswa
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.2.b',
            'nama' => 'Tabel R.2.b Penelitian Kolaboratif Dosen-Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.2.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Judul Penelitian', 'Dosen', 'Mahasiswa', 'Tahun', 'Sumber Dana', 'Luaran', 'Bukti'],
            'template_html' => $this->getTemplateR2b(),
            'urutan' => 3,
        ]);

        // R.2.c - Diseminasi Hasil Penelitian
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.2.c',
            'nama' => 'Tabel R.2.c Diseminasi Hasil Penelitian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.2.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Kegiatan Diseminasi', 'Judul Penelitian', 'Pemapar', 'Tahun', 'Tingkat', 'Bukti'],
            'template_html' => $this->getTemplateR2c(),
            'keterangan' => 'Seminar, workshop, FGD, publikasi, dll',
            'urutan' => 4,
        ]);
    }

    // ========================================
    // R.3 - Luaran dan Dampak Penelitian
    // IK-7.3.1 & IKn-7.3.2: (1) Publikasi (2) Adopsi industri (3) Kewirausahaan (4) Rasio
    // ========================================
    private function seedR3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.3')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.DESC',
            'nama' => 'Deskripsi Luaran Penelitian',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan luaran dan dampak penelitian...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // R.3.a - Publikasi Ilmiah
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.a',
            'nama' => 'Tabel R.3.a Publikasi Ilmiah',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Penulis', 'Judul', 'Nama Jurnal/Prosiding', 'Tahun', 'Tingkat', 'Indexing', 'Sitasi', 'Bukti'],
            'template_html' => $this->getTemplateR3a(),
            'keterangan' => 'Lokal/Nasional/Internasional, Scopus/WoS/Sinta/dll',
            'urutan' => 2,
        ]);

        // R.3.b - Karya Inovatif/HKI
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.b',
            'nama' => 'Tabel R.3.b Karya Inovatif dan HKI',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Inventor', 'Judul Karya', 'Jenis HKI', 'Nomor Paten/Sertifikat', 'Tahun', 'Status', 'Bukti'],
            'template_html' => $this->getTemplateR3b(),
            'keterangan' => 'Paten, hak cipta, desain industri, merek, dll',
            'urutan' => 3,
        ]);

        // R.3.c - Adopsi Hasil Penelitian
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.c',
            'nama' => 'Tabel R.3.c Adopsi Hasil Penelitian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Judul Penelitian', 'Bentuk Adopsi', 'Pengadopsi', 'Tahun', 'Dampak/Manfaat', 'Bukti'],
            'template_html' => $this->getTemplateR3c(),
            'keterangan' => 'Adopsi oleh industri, masyarakat, pemerintah, dll',
            'urutan' => 4,
        ]);

        // R.3.d - Prestasi/Penghargaan
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.3.d',
            'nama' => 'Tabel R.3.d Prestasi dan Penghargaan Penelitian',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.3.d',
            'is_required' => true,
            'expected_columns' => ['No', 'Penerima', 'Judul Karya', 'Jenis Penghargaan', 'Pemberi', 'Tingkat', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateR3d(),
            'urutan' => 5,
        ]);
    }

    // ========================================
    // R.4 - Kebijakan Pengabdian kepada Masyarakat
    // IK-7.4.1: (1) Roadmap PkM (2) Sosialisasi (3) Pendanaan (4) Review
    // ========================================
    private function seedR4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.4')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.4.DESC',
            'nama' => 'Deskripsi Kebijakan PkM',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan roadmap dan kebijakan pengabdian kepada masyarakat...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // R.4.a - Roadmap PkM
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.4.a',
            'nama' => 'Tabel R.4.a Roadmap Pengabdian kepada Masyarakat',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.4.a',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Tema PkM', 'Target Luaran', 'Mitra/Lokasi', 'Anggaran', 'Status'],
            'template_html' => $this->getTemplateR4a(),
            'urutan' => 2,
        ]);

        // R.4.b - Dokumen Kebijakan PkM
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.4.b',
            'nama' => 'Tabel R.4.b Dokumen Kebijakan PkM',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.4.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Jenis Dokumen', 'Nama Dokumen', 'Nomor', 'Tanggal', 'Bukti'],
            'template_html' => $this->getTemplateR4b(),
            'urutan' => 3,
        ]);

        // R.4.c - Pendanaan PkM
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.4.c',
            'nama' => 'Tabel R.4.c Pendanaan PkM',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.4.c',
            'is_required' => true,
            'expected_columns' => ['Tahun', 'Sumber Dana', 'Jumlah Proposal', 'Didanai', 'Total Dana (Rp)', 'Keterangan'],
            'template_html' => $this->getTemplateR4c(),
            'urutan' => 4,
        ]);
    }

    // ========================================
    // R.5 - Proses Pengabdian kepada Masyarakat
    // IK-7.5.1: (1) Kegiatan PkM (2) Kolaborasi (3) Manfaat (4) Diseminasi
    // ========================================
    private function seedR5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.5')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.5.DESC',
            'nama' => 'Deskripsi Proses PkM',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan proses pelaksanaan pengabdian kepada masyarakat...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // R.5.a - Kegiatan PkM Dosen
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.5.a',
            'nama' => 'Tabel R.5.a Daftar Kegiatan PkM Dosen',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.5.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Nama Dosen', 'Judul Kegiatan', 'Tahun', 'Lokasi', 'Sumber Dana', 'Mitra', 'Luaran'],
            'template_html' => $this->getTemplateR5a(),
            'urutan' => 2,
        ]);

        // R.5.b - PkM Kolaboratif
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.5.b',
            'nama' => 'Tabel R.5.b PkM Kolaboratif Dosen-Mahasiswa',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.5.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Judul', 'Dosen', 'Mahasiswa', 'Tahun', 'Lokasi', 'Penerima Manfaat', 'Bukti'],
            'template_html' => $this->getTemplateR5b(),
            'urutan' => 3,
        ]);
    }

    // ========================================
    // R.6 - Luaran dan Dampak Pengabdian
    // IK-7.6.1 & IKn-7.6.2: (1) Dampak (2) Replikasi (3) Wirausaha sosial (4) Rasio
    // ========================================
    private function seedR6()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.6')->first();
        if (!$elemen) return;

        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.DESC',
            'nama' => 'Deskripsi Luaran dan Dampak PkM',
            'tipe_field' => 'narasi',
            'label_field' => 'Deskripsi',
            'placeholder' => 'Jelaskan luaran dan dampak pengabdian kepada masyarakat...',
            'is_required' => true,
            'urutan' => 1,
        ]);

        // R.6.a - Dampak PkM
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.a',
            'nama' => 'Tabel R.6.a Dampak dan Manfaat PkM',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.a',
            'is_required' => true,
            'expected_columns' => ['No', 'Judul Kegiatan', 'Dampak/Manfaat', 'Penerima Manfaat', 'Jumlah', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateR6a(),
            'keterangan' => 'Sosial, ekonomi, kesejahteraan masyarakat',
            'urutan' => 2,
        ]);

        // R.6.b - Replikasi Program
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.b',
            'nama' => 'Tabel R.6.b Replikasi Program PkM',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.b',
            'is_required' => true,
            'expected_columns' => ['No', 'Program Asal', 'Institusi Pereplikasi', 'Lokasi', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateR6b(),
            'keterangan' => 'Program yang diadopsi oleh institusi/lembaga lain',
            'urutan' => 3,
        ]);

        // R.6.c - Penghargaan PkM
        DatasetBorang::create([
            'id_elemen' => $elemen->id,
            'kode' => 'R.6.c',
            'nama' => 'Tabel R.6.c Penghargaan PkM',
            'tipe_field' => 'table',
            'label_field' => 'Tabel R.6.c',
            'is_required' => true,
            'expected_columns' => ['No', 'Penerima', 'Judul Kegiatan', 'Jenis Penghargaan', 'Pemberi', 'Tingkat', 'Tahun', 'Bukti'],
            'template_html' => $this->getTemplateR6c(),
            'urutan' => 4,
        ]);
    }

    // ========================================
    // HTML TEMPLATES
    // ========================================

    private function getTemplateD1a()
    {
        return <<<HTML
<table class="table table-bordered" style="width: 100%;">
    <thead class="table-light">
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 20%;">Jenis Dokumen</th>
            <th style="width: 20%;">Nomor SK/Dokumen</th>
            <th style="width: 15%;">Tanggal Penetapan</th>
            <th style="width: 15%;">Masa Berlaku</th>
            <th style="width: 25%;">Bukti Dokumen</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>SK Pendirian</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>2</td><td>SK Operasional</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>3</td><td>SK Akreditasi</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>4</td><td>MoU Kerjasama</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>5</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    </tbody>
</table>
HTML;
    }

    private function getTemplateD1b()
    {
        return <<<HTML
<table class="table table-bordered" style="width: 100%;">
    <thead class="table-light">
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 25%;">Jabatan</th>
            <th style="width: 25%;">Nama</th>
            <th style="width: 15%;">NIP/NIDN</th>
            <th style="width: 15%;">Masa Jabatan</th>
            <th style="width: 15%;">SK Pengangkatan</th>
        </tr>
    </thead>
    <tbody>
        <tr><td>1</td><td>Ketua Program Studi</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>2</td><td>Sekretaris Program Studi</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>3</td><td>Koordinator Lab/Studio</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
        <tr><td>4</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    </tbody>
</table>
HTML;
    }

    // Continue dengan template lainnya menggunakan helper function generik
    private function getTemplateD1c()
    {
        return $this->getGenericTemplate(['No', 'Jenis Dokumen', 'Nama Dokumen', 'Nomor', 'Tanggal', 'Bukti']);
    }
    private function getTemplateD1d()
    {
        return $this->getGenericTemplate(['Tahun Akademik', 'Status', 'Tanggal Pelaporan', 'Kelengkapan', 'Keterangan']);
    }
    private function getTemplateD2a()
    {
        return $this->get2ColumnTemplate(['Aspek', 'Uraian'], [['Visi'], ['Misi'], ['Tujuan'], ['Strategi']]);
    }
    private function getTemplateD2b()
    {
        return $this->getGenericTemplate(['No', 'Indikator', 'Target', 'Metode', 'Baseline', 'Capaian']);
    }
    private function getTemplateD2c()
    {
        return $this->getGenericTemplate(['No', 'Program', 'Tujuan', 'Tahun', 'PIC', 'Status']);
    }
    private function getTemplateD2d()
    {
        return $this->getGenericTemplate(['Tahun', 'Aspek', 'Metode', 'Hasil', 'Tindak Lanjut', 'Bukti']);
    }
    private function getTemplateD3a()
    {
        return $this->getGenericTemplate(['Aspek', 'Visi Keilmuan', 'Diferensiasi', 'Bukti']);
    }
    private function getTemplateD3b()
    {
        return $this->getGenericTemplate(['No', 'Jenis SDM/Sumber Daya', 'Ketersediaan', 'Kecukupan', 'Rencana']);
    }
    private function getTemplateD3c()
    {
        return $this->getGenericTemplate(['No', 'Strategi', 'Skala', 'Target', 'Tahun', 'Status']);
    }

    // Templates untuk E (Edukasi)
    private function getTemplateE1a()
    {
        return $this->getGenericTemplate(['No', 'Kode CPL', 'Deskripsi CPL', 'Kategori']);
    }
    private function getTemplateE1b()
    {
        return $this->getGenericTemplate(['No', 'Kode MK', 'Nama MK', 'SKS', 'Semester', 'Sifat', 'CPL']);
    }
    private function getTemplateE1c()
    {
        return $this->getGenericTemplate(['No', 'Nama/Institusi', 'Jenis Eksternal', 'Bentuk Keterlibatan', 'Tahun', 'Bukti']);
    }
    private function getTemplateE1d()
    {
        return $this->getGenericTemplate(['No', 'Kode MK', 'Nama MK', 'Tahun Penyusunan', 'Bukti RPS']);
    }
    private function getTemplateE1e()
    {
        return $this->getGenericTemplate(['Tahun', 'Metode', 'Hasil', 'Tindak Lanjut', 'Bukti']);
    }
    private function getTemplateE2a()
    {
        return $this->getGenericTemplate(['Tahun', 'Daya Tampung', 'Pendaftar', 'Lulus', 'Registrasi', 'Rasio']);
    }
    private function getTemplateE2b()
    {
        return $this->getGenericTemplate(['Tahun', 'Reguler', 'Afirmasi', 'Jenis Afirmasi', 'Beasiswa', 'Sumber']);
    }
    private function getTemplateE2c()
    {
        return $this->getGenericTemplate(['Tahun', 'Jumlah Mhs Asing', 'Negara', 'Jenis Program', 'Durasi', 'Keterangan']);
    }
    private function getTemplateE3a()
    {
        return $this->getGenericTemplate(['No', 'Kode MK', 'Nama MK', 'Metode', 'Tugas', 'Evaluasi']);
    }
    private function getTemplateE3b()
    {
        return $this->getGenericTemplate(['Tahun', 'Jumlah', '≤4 Thn', '4-5 Thn', '>5 Thn', 'Rata-rata']);
    }
    private function getTemplateE3c()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Prestasi', 'Tingkat', 'Tahun', 'Bukti']);
    }
    private function getTemplateE4a()
    {
        return $this->getGenericTemplate(['No', 'Kode MK', 'Aspek', 'Metode', 'Bobot', 'Instrumen', 'CPL']);
    }
    private function getTemplateE4b()
    {
        return $this->getGenericTemplate(['No', 'Organisasi', 'Keterlibatan', 'Jenis Uji', 'Tahun', 'Bukti']);
    }
    private function getTemplateE5a()
    {
        return $this->getGenericTemplate(['Tahun', 'Jumlah', 'IPK', 'Masa Studi', 'Predikat']);
    }
    private function getTemplateE5b()
    {
        return $this->getGenericTemplate(['No', 'Kode MK', 'Nama MK', 'CPL', 'Model', 'Keterangan']);
    }
    private function getTemplateE5c()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Prestasi', 'Tingkat', 'Tahun', 'Penyelenggara', 'Bukti']);
    }

    // Templates untuk P (Pengembangan SDM)
    private function getTemplateP1a()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'NIDN', 'Pendidikan', 'Keahlian', 'Jabatan', 'Serdos', 'Sertifikat']);
    }
    private function getTemplateP1b()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Pendidikan', 'Keahlian', 'Jenis', 'Institusi', 'MK Diampu']);
    }
    private function getTemplateP1c()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Jabatan', 'Pendidikan', 'Unit', 'Sertifikat']);
    }
    private function getTemplateP1d()
    {
        return $this->getGenericTemplate(['No', 'Asosiasi', 'Keterlibatan', 'Peran', 'Tahun', 'Bukti']);
    }
    private function getTemplateP2a()
    {
        return $this->getGenericTemplate(['No', 'Jenis Ruang', 'Luas (m²)', 'Jumlah', 'Kapasitas', 'Kondisi', 'Utilisasi']);
    }
    private function getTemplateP2b()
    {
        return $this->getGenericTemplate(['No', 'Nama Lab', 'Luas (m²)', 'Alat Utama', 'Jumlah', 'Kondisi', 'PJ']);
    }
    private function getTemplateP2c()
    {
        return $this->getGenericTemplate(['No', 'Unit', 'Jenis Fasilitas', 'Jumlah', 'Kondisi', 'Kecukupan']);
    }
    private function getTemplateP2d()
    {
        return $this->getGenericTemplate(['No', 'Jenis', 'Luas/Kapasitas', 'Jumlah', 'Kondisi', 'Aksesibilitas']);
    }
    private function getTemplateP2e()
    {
        return $this->getGenericTemplate(['No', 'Kegiatan', 'Frekuensi', 'PJ', 'Anggaran', 'Bukti']);
    }

    private function getTemplateP3a()
    {
        return $this->getGenericTemplate(['No', 'Aspek', 'Kebijakan', 'Dokumen', 'Implementasi', 'Bukti']);
    }
    private function getTemplateP3b()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Jenis', 'Tahun', 'Penyelenggara', 'Dana', 'Bukti']);
    }
    private function getTemplateP3c()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Jenis', 'Tahun', 'Penyelenggara', 'Dana', 'Bukti']);
    }
    private function getTemplateP3d()
    {
        return $this->getGenericTemplate(['Aspek', 'Dokumen', 'Kriteria', 'Implementasi', 'Bukti']);
    }
    private function getTemplateP3e()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Jabatan', 'Pelatihan', 'Tahun', 'Penyelenggara', 'Bukti']);
    }

    private function getTemplateP4a()
    {
        return $this->getGenericTemplate(['No', 'Jenis SDM', 'Komponen', 'Nominal', 'Dasar', 'Keterangan']);
    }
    private function getTemplateP4b()
    {
        return $this->getGenericTemplate(['No', 'Program', 'Deskripsi', 'Frekuensi', 'PJ', 'Bukti']);
    }
    private function getTemplateP4c()
    {
        return $this->getGenericTemplate(['Aspek', 'Kebijakan', 'Unit', 'Program', 'Bukti']);
    }
    private function getTemplateP4d()
    {
        return $this->getGenericTemplate(['Tahun', 'Aspek', 'Skor', 'Responden', 'Tindak Lanjut', 'Bukti']);
    }

    // I, L, A, R Templates...
    private function getTemplateI1a()
    {
        return $this->getGenericTemplate(['Tingkat', 'Nama Unit', 'PJ', 'Tugas', 'SK']);
    }
    private function getTemplateI1b()
    {
        return $this->getGenericTemplate(['No', 'Jenis', 'Nama', 'Nomor', 'Tanggal', 'Bukti']);
    }
    private function getTemplateI1c()
    {
        return $this->getGenericTemplate(['Tahun', 'Periode', 'Auditor', 'Temuan', 'Rekomendasi', 'Bukti']);
    }
    private function getTemplateI1d()
    {
        return $this->getGenericTemplate(['Tahun', 'Tanggal', 'Agenda', 'Keputusan', 'Tindak Lanjut', 'Bukti']);
    }
    private function getTemplateI1e()
    {
        return $this->getGenericTemplate(['Tahun', 'Jenis', 'Responden', 'Metode', 'Hasil', 'Tindak Lanjut']);
    }

    private function getTemplateI2a()
    {
        return $this->getGenericTemplate(['No', 'Aspek', 'Indikator', 'Target', 'TS-2', 'TS-1', 'TS']);
    }
    private function getTemplateI2b()
    {
        return $this->getGenericTemplate(['Tahun', 'Penetapan', 'Pelaksanaan', 'Evaluasi', 'Pengendalian', 'Peningkatan', 'Bukti']);
    }
    private function getTemplateI2c()
    {
        return $this->getGenericTemplate(['Tahun', 'Sumber', 'Temuan', 'Tindakan', 'PIC', 'Status', 'Bukti']);
    }
    private function getTemplateI2d()
    {
        return $this->getGenericTemplate(['Tahun', 'Standar', 'Lama', 'Baru', 'Dasar', 'Bukti']);
    }

    private function getTemplateI3a()
    {
        return $this->getGenericTemplate(['No', 'Aspek', 'Ahli', 'Tahun', 'Hasil', 'Tindak Lanjut', 'Bukti']);
    }
    private function getTemplateI3b()
    {
        return $this->getGenericTemplate(['No', 'Institusi', 'Aspek', 'Tahun', 'Peserta', 'Hasil', 'Tindak Lanjut']);
    }
    private function getTemplateI3c()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Pelatihan', 'Penyelenggara', 'Tahun', 'Sertifikat', 'Tindak Lanjut']);
    }
    private function getTemplateI3d()
    {
        return $this->getGenericTemplate(['No', 'Jenis', 'Lembaga', 'Tahun', 'Peringkat', 'Masa Berlaku', 'Bukti']);
    }

    private function getTemplateL1a()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Luas (m²)', 'Kapasitas', 'Fasilitas', 'Kondisi', 'Utilisasi']);
    }
    private function getTemplateL1b()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Luas', 'Kapasitas', 'Alat', 'Jumlah', 'Kondisi', 'PJ']);
    }
    private function getTemplateL1c()
    {
        return $this->getGenericTemplate(['Aspek', 'Platform', 'Fitur', 'Pengguna', 'Utilisasi', 'Keterangan']);
    }
    private function getTemplateL1d()
    {
        return $this->getGenericTemplate(['No', 'Jenis', 'Luas', 'Kapasitas', 'Fasilitas', 'Kondisi', 'Akses']);
    }
    private function getTemplateL1e()
    {
        return $this->getGenericTemplate(['Jenis', 'Jumlah', 'Mahasiswa', 'Rasio', 'Standar', 'Keterangan']);
    }

    private function getTemplateL2a()
    {
        return $this->getGenericTemplate(['No', 'Kode MK', 'Nama MK', 'Judul Buku', 'Pengarang', 'Tahun', 'Eksemplar']);
    }
    private function getTemplateL2b()
    {
        return $this->getGenericTemplate(['No', 'Database', 'Akses', 'Cakupan', 'Tahun', 'Status']);
    }
    private function getTemplateL2c()
    {
        return $this->getGenericTemplate(['No', 'Software', 'Fungsi', 'Lisensi', 'Jumlah', 'Aksesibilitas']);
    }
    private function getTemplateL2d()
    {
        return $this->getGenericTemplate(['Tahun', 'Program', 'Anggaran', 'Realisasi', 'Survei', 'Tindak Lanjut']);
    }

    private function getTemplateL3a()
    {
        return $this->getGenericTemplate(['Tahun', 'Aspek', 'Skor (1-5)', 'Responden', 'Response Rate', 'Tindak Lanjut']);
    }
    private function getTemplateL3b()
    {
        return $this->getGenericTemplate(['Tahun', 'Aspek', 'Skor (1-5)', 'Responden', 'Masa Tunggu', 'Kesesuaian']);
    }
    private function getTemplateL3c()
    {
        return $this->getGenericTemplate(['Jenis', 'Metode', 'Validitas', 'Reliabilitas', 'Tahun', 'Bukti']);
    }

    private function getTemplateL4a()
    {
        return $this->getGenericTemplate(['Tahun', 'Jumlah', 'Bekerja', 'Wirausaha', 'Studi', 'Belum', 'Masa Tunggu']);
    }
    private function getTemplateL4b()
    {
        return $this->getGenericTemplate(['Tahun', 'Aspek', 'Skor (1-5)', 'Responden', 'Response Rate', 'Tindak Lanjut']);
    }
    private function getTemplateL4c()
    {
        return $this->getGenericTemplate(['No', 'Institusi', 'Jenis', 'Jumlah Alumni', 'Posisi', 'Kesesuaian']);
    }

    private function getTemplateA1a()
    {
        return $this->getGenericTemplate(['No', 'Jabatan', 'Nama', 'NIP', 'Tugas', 'Masa Jabatan', 'SK']);
    }
    private function getTemplateA1b()
    {
        return $this->getGenericTemplate(['Jabatan', 'Kriteria', 'Mekanisme', 'Dokumen', 'Bukti']);
    }
    private function getTemplateA1c()
    {
        return $this->getGenericTemplate(['No', 'SOP', 'Cakupan', 'Nomor', 'Tanggal', 'Bukti']);
    }

    private function getTemplateA2a()
    {
        return $this->getGenericTemplate(['No', 'Mitra', 'Jenis', 'Lingkup', 'Bidang', 'Tahun', 'Durasi', 'Status', 'MoU']);
    }
    private function getTemplateA2b()
    {
        return $this->getGenericTemplate(['No', 'Mitra', 'Kegiatan', 'Tahun', 'Peserta', 'Hasil', 'Bukti']);
    }
    private function getTemplateA2c()
    {
        return $this->getGenericTemplate(['No', 'Institusi', 'Negara', 'Program', 'Tahun', 'Mahasiswa', 'Status', 'Bukti']);
    }

    private function getTemplateA3a()
    {
        return $this->getGenericTemplate(['No', 'Sistem', 'Fungsi', 'Platform', 'Pengguna', 'Integrasi', 'Status']);
    }
    private function getTemplateA3b()
    {
        return $this->getGenericTemplate(['Aspek', 'Indikator', 'Target', 'Capaian', 'Bukti', 'Tindak Lanjut']);
    }
    private function getTemplateA3c()
    {
        return $this->getGenericTemplate(['Tahun', 'Program', 'Tujuan', 'Anggaran', 'Status', 'Hasil']);
    }

    private function getTemplateA4a()
    {
        return $this->getGenericTemplate(['No', 'Program', 'Deskripsi', 'Frekuensi', 'PIC', 'Anggaran', 'Bukti']);
    }
    private function getTemplateA4b()
    {
        return $this->getGenericTemplate(['No', 'Program', 'Deskripsi', 'Target', 'Capaian', 'Bukti']);
    }
    private function getTemplateA4c()
    {
        return $this->getGenericTemplate(['No', 'Sertifikasi', 'Lembaga', 'Tahun', 'Masa Berlaku', 'Bukti']);
    }

    private function getTemplateA5a()
    {
        return $this->getGenericTemplate(['Tahun', 'Sumber', 'Penerimaan', 'Pengeluaran', 'Saldo', 'Utilisasi']);
    }
    private function getTemplateA5b()
    {
        return $this->getGenericTemplate(['Tahun', 'Kegiatan', 'Anggaran', 'Realisasi', 'Persentase', 'Keterangan']);
    }
    private function getTemplateA5c()
    {
        return $this->getGenericTemplate(['Tahun', 'Jenis', 'Auditor', 'Hasil', 'Temuan', 'Tindak Lanjut', 'Bukti']);
    }
    private function getTemplateA5d()
    {
        return $this->getGenericTemplate(['No', 'Risiko', 'Probabilitas', 'Dampak', 'Mitigasi', 'PIC', 'Status']);
    }

    private function getTemplateR1a()
    {
        return $this->getGenericTemplate(['Tahun', 'Tema', 'Target', 'Anggaran', 'PJ', 'Status']);
    }
    private function getTemplateR1b()
    {
        return $this->getGenericTemplate(['No', 'Jenis', 'Nama', 'Nomor', 'Tanggal', 'Bukti']);
    }
    private function getTemplateR1c()
    {
        return $this->getGenericTemplate(['Tahun', 'Sumber', 'Proposal', 'Didanai', 'Total Dana', 'Keterangan']);
    }

    private function getTemplateR2a()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Judul', 'Tahun', 'Sumber', 'Dana', 'Status', 'Luaran']);
    }
    private function getTemplateR2b()
    {
        return $this->getGenericTemplate(['No', 'Judul', 'Dosen', 'Mahasiswa', 'Tahun', 'Sumber', 'Luaran', 'Bukti']);
    }
    private function getTemplateR2c()
    {
        return $this->getGenericTemplate(['No', 'Kegiatan', 'Judul', 'Pemapar', 'Tahun', 'Tingkat', 'Bukti']);
    }

    private function getTemplateR3a()
    {
        return $this->getGenericTemplate(['No', 'Penulis', 'Judul', 'Jurnal', 'Tahun', 'Tingkat', 'Indexing', 'Sitasi', 'Bukti']);
    }
    private function getTemplateR3b()
    {
        return $this->getGenericTemplate(['No', 'Inventor', 'Judul', 'Jenis HKI', 'Nomor', 'Tahun', 'Status', 'Bukti']);
    }
    private function getTemplateR3c()
    {
        return $this->getGenericTemplate(['No', 'Judul', 'Bentuk', 'Pengadopsi', 'Tahun', 'Dampak', 'Bukti']);
    }
    private function getTemplateR3d()
    {
        return $this->getGenericTemplate(['No', 'Penerima', 'Judul', 'Penghargaan', 'Pemberi', 'Tingkat', 'Tahun', 'Bukti']);
    }

    private function getTemplateR4a()
    {
        return $this->getGenericTemplate(['Tahun', 'Tema', 'Target', 'Mitra', 'Anggaran', 'Status']);
    }
    private function getTemplateR4b()
    {
        return $this->getGenericTemplate(['No', 'Jenis', 'Nama', 'Nomor', 'Tanggal', 'Bukti']);
    }
    private function getTemplateR4c()
    {
        return $this->getGenericTemplate(['Tahun', 'Sumber', 'Proposal', 'Didanai', 'Total', 'Keterangan']);
    }

    private function getTemplateR5a()
    {
        return $this->getGenericTemplate(['No', 'Nama', 'Judul', 'Tahun', 'Lokasi', 'Sumber', 'Mitra', 'Luaran']);
    }
    private function getTemplateR5b()
    {
        return $this->getGenericTemplate(['No', 'Judul', 'Dosen', 'Mahasiswa', 'Tahun', 'Lokasi', 'Penerima', 'Bukti']);
    }

    private function getTemplateR6a()
    {
        return $this->getGenericTemplate(['No', 'Judul', 'Dampak', 'Penerima', 'Jumlah', 'Tahun', 'Bukti']);
    }
    private function getTemplateR6b()
    {
        return $this->getGenericTemplate(['No', 'Program', 'Institusi', 'Lokasi', 'Tahun', 'Bukti']);
    }
    private function getTemplateR6c()
    {
        return $this->getGenericTemplate(['No', 'Penerima', 'Judul', 'Penghargaan', 'Pemberi', 'Tingkat', 'Tahun', 'Bukti']);
    }

    /**
     * Helper: Generate generic table template
     */
    private function getGenericTemplate(array $columns)
    {
        $html = '<table class="table table-bordered" style="width: 100%;">';
        $html .= '<thead class="table-light"><tr>';

        foreach ($columns as $column) {
            $html .= "<th>{$column}</th>";
        }

        $html .= '</tr></thead><tbody>';

        // 3 empty rows
        for ($i = 0; $i < 3; $i++) {
            $html .= '<tr>';
            foreach ($columns as $index => $column) {
                $html .= $index === 0 && $column === 'No' ? '<td>' . ($i + 1) . '</td>' : '<td>&nbsp;</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Helper: 2-column template with predefined rows
     */
    private function get2ColumnTemplate(array $headers, array $rows)
    {
        $html = '<table class="table table-bordered" style="width: 100%;">';
        $html .= '<thead class="table-light"><tr>';
        $html .= "<th style='width:30%'>{$headers[0]}</th>";
        $html .= "<th style='width:70%'>{$headers[1]}</th>";
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td><strong>' . $row[0] . '</strong></td>';
            $html .= '<td>&nbsp;</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}
