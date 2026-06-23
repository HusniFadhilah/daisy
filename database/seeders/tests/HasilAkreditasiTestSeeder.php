<?php

namespace Database\Seeders\Tests;

use App\Models\Asesmen;
use App\Models\BobotPenilaian;
use App\Models\DegreeLevel;
use App\Models\ElemenStandar;
use App\Models\Kriteria;
use App\Models\PengajuanAkreditasi;
use App\Models\PenilaianElemenAk;
use App\Models\PenilaianElemenAl;
use App\Models\StatusAkreditasi;
use App\Models\StudyProgram;
use App\Models\StudyProgramCategory;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds minimal data required by HasilAkreditasiService tests.
 *
 * Creates:
 *  - 1 StudyProgramCategory
 *  - 1 DegreeLevel (linked to category)
 *  - 1 University + 1 StudyProgram (linked to category & degree_level)
 *  - 2 Kriteria (A, B)
 *  - 3 ElemenStandar (A1, A2, B1)
 *  - 3 BobotPenilaian (one per elemen × degree_level × category)
 *  - 4 StatusAkreditasi rows covering the full 0-400 range
 *  - 1 User (as asesor), 1 PengajuanAkreditasi, 1 Asesmen
 *  - 3 PenilaianElemenAk (one per elemen, status=submitted)
 *  - 3 PenilaianElemenAl (one per elemen, status=submitted)
 */
class HasilAkreditasiTestSeeder extends Seeder
{
    public function run(): array
    {
        // ── Category ──────────────────────────────────────────────
        $category = StudyProgramCategory::create([
            'code' => 'TEST',
            'name' => 'Test Category',
        ]);

        // ── Degree Level ──────────────────────────────────────────
        $degreeLevel = DegreeLevel::create([
            'id_category' => $category->id,
            'code'        => 'S1-TEST',
            'alias'       => 'S1',
            'name'        => 'Sarjana',
            'is_active'   => true,
        ]);

        // ── University + Study Program ─────────────────────────────
        $university = University::create([
            'code'       => 'UNIV-TEST',
            'name'       => 'Universitas Test',
            'is_active'  => true,
            'is_example' => false,
        ]);

        $studyProgram = StudyProgram::create([
            'name'            => 'Arsitektur Test',
            'full_name'       => 'Program Studi Arsitektur Test',
            'code'            => 'SP-TEST-001',
            'id_university'   => $university->id,
            'id_degree_level' => $degreeLevel->id,
            'id_category'     => $category->id,
            'rumpun'          => 'arsitektur',
            'is_active'       => true,
            'is_example'      => false,
        ]);

        // ── Kriteria ──────────────────────────────────────────────
        $kriteriaA = Kriteria::create(['kode_kriteria' => 'A', 'nama_kriteria' => 'Kriteria A']);
        $kriteriaB = Kriteria::create(['kode_kriteria' => 'B', 'nama_kriteria' => 'Kriteria B']);

        // ── Elemen Standar ────────────────────────────────────────
        $elemenA1 = ElemenStandar::create([
            'id_kriteria'        => $kriteriaA->id,
            'kode_elemen'        => 'A.1',
            'pernyataan_elemen'  => 'Elemen A1',
        ]);
        $elemenA2 = ElemenStandar::create([
            'id_kriteria'        => $kriteriaA->id,
            'kode_elemen'        => 'A.2',
            'pernyataan_elemen'  => 'Elemen A2',
        ]);
        $elemenB1 = ElemenStandar::create([
            'id_kriteria'        => $kriteriaB->id,
            'kode_elemen'        => 'B.1',
            'pernyataan_elemen'  => 'Elemen B1',
        ]);

        // ── Bobot Penilaian ───────────────────────────────────────
        foreach ([$elemenA1, $elemenA2, $elemenB1] as $elemen) {
            BobotPenilaian::create([
                'id_elemen'       => $elemen->id,
                'id_category'     => $category->id,
                'id_degree_level' => $degreeLevel->id,
                'bobot'           => 10.00,
                'is_active'       => true,
            ]);
        }

        // ── Status Akreditasi ─────────────────────────────────────
        StatusAkreditasi::create([
            'skor_min'     => 0,   'skor_max'     => 140,
            'persen_min'   => 0,   'persen_max'   => 35,
            'status'       => 'Tidak Terakreditasi',
            'makna'        => 'Tidak memenuhi syarat',
            'warna'        => 'red',
            'siklus_tahun' => 0,
            'urutan'       => 1,
        ]);
        StatusAkreditasi::create([
            'skor_min'     => 141, 'skor_max'     => 200,
            'persen_min'   => 36,  'persen_max'   => 50,
            'status'       => 'Baik',
            'makna'        => 'Memenuhi syarat dasar',
            'warna'        => 'yellow',
            'siklus_tahun' => 4,
            'urutan'       => 2,
        ]);
        StatusAkreditasi::create([
            'skor_min'     => 201, 'skor_max'     => 280,
            'persen_min'   => 51,  'persen_max'   => 70,
            'status'       => 'Baik Sekali',
            'makna'        => 'Melebihi syarat dasar',
            'warna'        => 'blue',
            'siklus_tahun' => 5,
            'urutan'       => 3,
        ]);
        StatusAkreditasi::create([
            'skor_min'     => 281, 'skor_max'     => 400,
            'persen_min'   => 71,  'persen_max'   => 100,
            'status'       => 'Unggul',
            'makna'        => 'Melampaui semua syarat',
            'warna'        => 'green',
            'siklus_tahun' => 5,
            'urutan'       => 4,
        ]);

        // ── Users ─────────────────────────────────────────────────
        $asesor = User::factory()->withRole('asesor')->create([
            'email' => 'asesor-test@daisy.test',
        ]);
        $admin = User::factory()->sekretariat()->create([
            'email' => 'admin-test@daisy.test',
        ]);

        // ── Pengajuan & Asesmen ───────────────────────────────────
        $pengajuan = PengajuanAkreditasi::create([
            'nomor_pengajuan'   => 'AK/TEST/001',
            'id_program_studi'  => $studyProgram->id,
            'id_user_pengaju'   => $admin->id,
            'tahun_akreditasi'  => now()->year,
            'jenis_akreditasi'  => 'terakreditasi',
            'kelompok_akreditasi' => 'individual',
            'status'            => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
        ]);

        $asesmen = Asesmen::create([
            'id_pengajuan'    => $pengajuan->id,
            'id_study_program' => $studyProgram->id,
            'code'            => 'ASESMEN-TEST-001',
            'name'            => 'Asesmen Test',
            'description'     => 'Asesmen untuk pengujian',
            'status'          => 'active',
        ]);

        // ── Penilaian Elemen AK (skor avg = 3.0 per elemen) ──────
        foreach ([$elemenA1, $elemenA2, $elemenB1] as $elemen) {
            PenilaianElemenAk::create([
                'id_asesmen' => $asesmen->id,
                'id_asesor'  => $asesor->id,
                'id_elemen'  => $elemen->id,
                'skor'       => 3,
                'skor_final' => 3,
                'status'     => 'submitted',
                'status_validasi' => 'approved',
            ]);
        }

        // ── Penilaian Elemen AL (skor = 3.0 per elemen) ──────────
        foreach ([$elemenA1, $elemenA2, $elemenB1] as $elemen) {
            PenilaianElemenAl::create([
                'id_asesmen' => $asesmen->id,
                'id_asesor'  => $asesor->id,
                'id_elemen'  => $elemen->id,
                'skor'       => 3,
                'status'     => 'submitted',
            ]);
        }

        return compact(
            'category', 'degreeLevel', 'university', 'studyProgram',
            'kriteriaA', 'kriteriaB', 'elemenA1', 'elemenA2', 'elemenB1',
            'asesor', 'admin', 'pengajuan', 'asesmen'
        );
    }
}
