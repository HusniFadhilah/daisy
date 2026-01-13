<?php

namespace Database\Seeders;

use App\Models\DegreeLevel;
use App\Models\DatasetBorang;
use App\Models\ElemenStandar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatasetBorangSeeder extends Seeder
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
        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'D.1.DESC',
        //     'nama' => 'Deskripsi legalitas program dan tata pamong',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => true,
        //     'urutan' => 1,
        // ]);
    }

    private function seedD2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.2')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'D.2.DESC',
        //     'nama' => 'Deskripsi visi, misi, tujuan, dan strategi',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedD3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'D.3')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'D.3.DESC',
        //     'nama' => 'Deskripsi kesesuaian visi keilmuan',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedE1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.1')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'E.1.DESC',
        //     'nama' => 'Deskripsi kurikulum',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    // ========================================
    // E.2 - Admisi Mahasiswa (FLAT VERSION)
    // ========================================
    private function seedE2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.2')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'E.2.DESC',
        //     'nama' => 'Deskripsi admisi mahasiswa',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    // ========================================
    // E.3 - Proses dan Siklus Pembelajaran
    // ========================================
    private function seedE3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.3')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'E.3.DESC',
        //     'nama' => 'Deskripsi proses dan siklus pembelajaran',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    // ========================================
    // E.4 - Penilaian dan Evaluasi
    // ========================================
    private function seedE4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.4')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'E.4.DESC',
        //     'nama' => 'Deskripsi penilaian dan evaluasi',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    // ========================================
    // E.5 - Kompetensi Lulusan
    // ========================================
    private function seedE5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'E.5')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'E.5.DESC',
        //     'nama' => 'Deskripsi kompetensi lulusan dan capaian pembelajaran',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    // P, I, L, A sections (simplified)
    private function seedP1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.1')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'P.1.DESC',
        //     'nama' => 'Deskripsi dosen dan tenaga kependidikan',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedP2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.2')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'P.2.DESC',
        //     'nama' => 'Deskripsi sarana dan prasarana kerja',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedP3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.3')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'P.3.DESC',
        //     'nama' => 'Deskripsi pengembangan kapasitas',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedP4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'P.4')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'P.4.DESC',
        //     'nama' => 'Deskripsi kesejahteraan kerja',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedI1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.1')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'I.1.DESC',
        //     'nama' => 'Deskripsi sistem penjaminan mutu internal',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedI2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.2')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'I.2.DESC',
        //     'nama' => 'Deskripsi implementasi perbaikan berkelanjutan',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedI3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'I.3')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'I.3.DESC',
        //     'nama' => 'Deskripsi keterlibatan pengampu kepentingan dan penjaminan mutu eksternal',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedL1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.1')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'L.1.DESC',
        //     'nama' => 'Deskripsi sarana dan prasarana belajar',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedL2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.2')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'L.2.DESC',
        //     'nama' => 'Deskripsi sumber pengetahuan',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedL3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.3')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'L.3.DESC',
        //     'nama' => 'Deskripsi kepuasan mahasiswa dan alumni',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedL4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'L.4')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'L.4.DESC',
        //     'nama' => 'Deskripsi lulusan, kajian telusur, dan kepuasan pengguna',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedA1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.1')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'A.1.DESC',
        //     'nama' => 'Deskripsi organisasi dan tata kelola',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedA2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.2')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'A.2.DESC',
        //     'nama' => 'Deskripsi kerja sama dan kemitraan',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedA3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.3')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'A.3.DESC',
        //     'nama' => 'Deskripsi sistem dan manajemen informasi',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedA4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.4')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'A.4.DESC',
        //     'nama' => 'Deskripsi keselamatan dan kesehatan kerja serta kelestarian lingkungan',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedA5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'A.5')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'A.5.DESC',
        //     'nama' => 'Deskripsi keuangan, keberlanjutan, dan mitigasi risiko',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedR1()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.1')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'R.1.DESC',
        //     'nama' => 'Deskripsi kebijakan penelitian',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedR2()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.2')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'R.2.DESC',
        //     'nama' => 'Deskripsi proses penelitian',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedR3()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.3')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'R.3.DESC',
        //     'nama' => 'Deskripsi luaran dan dampak penelitian',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedR4()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.4')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'R.4.DESC',
        //     'nama' => 'Deskripsi kebijakan pengabdian kepada masyarakat',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }

    private function seedR5()
    {
        $elemen = ElemenStandar::where('kode_elemen', 'R.5')->first();
        if (!$elemen) return;

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'R.5.DESC',
        //     'nama' => 'Deskripsi proses pengabdian kepada masyarakat',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
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

        // DatasetBorang::create([
        //     'id_elemen' => $elemen->id,
        //     'kode' => 'R.6.DESC',
        //     'nama' => 'Deskripsi luaran dan dampak pengabdian kepada masyarakat',
        //     'tipe_field' => 'narasi',
        //     'label_field' => 'Deskripsi',
        //     'placeholder' => '[Mohon isi deskripsi di sini sesuai dengan kondisi program studi (maksimal 1000 kata)...]',
        //     'is_required' => false,
        //     'urutan' => 1,
        // ]);
    }
}
