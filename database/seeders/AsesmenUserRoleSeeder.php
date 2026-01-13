<?php

namespace Database\Seeders;

use App\Models\Asesmen;
use App\Libraries\Fungsi;
use App\Models\StudyProgram;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use Illuminate\Database\Seeder;
use App\Models\AsesmenKecukupan;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AsesmenUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAsesor    = DB::table('roles')->whereName('asesor')->value('id');
        $roleValidator = DB::table('roles')->whereName('validator')->value('id');

        // ============================================
        // 1. ASESMEN MAGISTER ILMU LINGKUNGAN
        // ============================================
        $asesmenMil = Asesmen::insertGetId([
            'id_study_program' => StudyProgram::where('email', 'lamdepilar@contoh.ac.id')->firstOrFail()->id,
            'code' => 'ASM-' . Fungsi::uniqueCode(5),
            'kode_panel' => 'T01-P001',
            'name' => 'Penilaian Akreditasi Prodi LAMDEPILAR 2025',
            'description' => 'Penilaian akreditasi Prodi LAMDEPILAR untuk tahun 2025-2030',
            'tanggal_mulai'   => now(),
            'tanggal_selesai' => now()->addMonths(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Asesmen Kecukupan (AK) untuk MIL
        $akMil = DB::table('asesmen_kecukupan')->insertGetId([
            'id_asesmen' => $asesmenMil,
            'code' => 'AK-' . Fungsi::uniqueCode(5),
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonths(1),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Asesmen Lapangan (AL) untuk MIL
        $alMil = DB::table('asesmen_lapangan')->insertGetId([
            'id_asesmen' => $asesmenMil,
            'code' => 'AL-' . Fungsi::uniqueCode(5),
            'tanggal_mulai' => now()->addMonth(),
            'tanggal_selesai' => now()->addMonths(2),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Asesor untuk AK (Asesmen Kecukupan)
        $asesorsMilAK = [4, 5]; // User ID asesor
        foreach ($asesorsMilAK as $index => $userId) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenMil,
                'id_user' => $userId,
                'id_role' => $roleAsesor,
                'jenis_asesmen' => 'ak',
                'id_asesmen_kecukupan' => $akMil,
                'id_asesmen_lapangan' => null,
                'urutan_asesor' => $index + 1, // 1, 2
                'status_penawaran' => 'accepted', // Set as accepted untuk testing
                'status_pekerjaan' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign Validator untuk AK
        $validatorsMilAK = [9]; // User ID validator
        foreach ($validatorsMilAK as $userId) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenMil,
                'id_user' => $userId,
                'id_role' => $roleValidator,
                'jenis_asesmen' => 'ak',
                'id_asesmen_kecukupan' => $akMil,
                'id_asesmen_lapangan' => null,
                'urutan_asesor' => null, // Validator tidak punya urutan
                'status_penawaran' => 'accepted',
                'status_pekerjaan' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign Asesor untuk AL (Asesmen Lapangan) - bisa user berbeda
        $asesorsMilAL = [6, 7]; // User ID asesor berbeda untuk AL
        foreach ($asesorsMilAL as $index => $userId) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenMil,
                'id_user' => $userId,
                'id_role' => $roleAsesor,
                'jenis_asesmen' => 'al',
                'id_asesmen_kecukupan' => null,
                'id_asesmen_lapangan' => $alMil,
                'urutan_asesor' => $index + 1, // 1, 2
                'status_penawaran' => 'pending', // Pending karena AL biasanya setelah AK
                'status_pekerjaan' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ============================================
        // 2. ASESMEN TEKNIK INFORMATIKA
        // ============================================
        $asesmenTI = Asesmen::insertGetId([
            'id_study_program' => StudyProgram::where('email', 'testing@abcd.ac.id')->firstOrFail()->id,
            // 'id_study_program' => null, // Bisa null jika belum ada program studi
            'code' => 'ASM-' . Fungsi::uniqueCode(5),
            'kode_panel' => 'T01-P002',
            'name' => 'Penilaian Akreditasi Prodi ABCD 2025',
            'description' => 'Asesmen akreditasi Prodi ABCD untuk periode 2025-2030',
            'tanggal_mulai'   => now(),
            'tanggal_selesai' => now()->addMonths(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Asesmen Kecukupan (AK) untuk TI
        $akTI = DB::table('asesmen_kecukupan')->insertGetId([
            'id_asesmen' => $asesmenTI,
            'code' => 'AK-' . Fungsi::uniqueCode(5),
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonths(1),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Asesmen Lapangan (AL) untuk TI
        $alTI = DB::table('asesmen_lapangan')->insertGetId([
            'id_asesmen' => $asesmenTI,
            'code' => 'AL-' . Fungsi::uniqueCode(5),
            'tanggal_mulai' => now()->addMonth(),
            'tanggal_selesai' => now()->addMonths(2),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Asesor untuk AK
        $asesorsTIAK = [8, 12]; // 2 asesor untuk AK
        foreach ($asesorsTIAK as $index => $userId) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenTI,
                'id_user' => $userId,
                'id_role' => $roleAsesor,
                'jenis_asesmen' => 'ak',
                'id_asesmen_kecukupan' => $akTI,
                'id_asesmen_lapangan' => null,
                'urutan_asesor' => $index + 1,
                'status_penawaran' => 'accepted',
                'status_pekerjaan' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign Validator untuk AK
        $validatorsTIAK = [11];
        foreach ($validatorsTIAK as $userId) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenTI,
                'id_user' => $userId,
                'id_role' => $roleValidator,
                'jenis_asesmen' => 'ak',
                'id_asesmen_kecukupan' => $akTI,
                'id_asesmen_lapangan' => null,
                'urutan_asesor' => null,
                'status_penawaran' => 'accepted',
                'status_pekerjaan' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign Asesor untuk AL (bisa sama atau berbeda)
        $asesorsTIAL = [8, 12]; // Bisa user yang sama dengan AK atau berbeda
        foreach ($asesorsTIAL as $index => $userId) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenTI,
                'id_user' => $userId,
                'id_role' => $roleAsesor,
                'jenis_asesmen' => 'al',
                'id_asesmen_kecukupan' => null,
                'id_asesmen_lapangan' => $alTI,
                'urutan_asesor' => $index + 1,
                'status_penawaran' => 'pending',
                'status_pekerjaan' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ============================================
        // 3. EXAMPLE: ASESMEN DENGAN 3 ASESOR
        // ============================================
        $asesmenMulti = Asesmen::insertGetId([
            'id_study_program' => StudyProgram::where('email', 'depilar@abcd.ac.id')->firstOrFail()->id,
            // 'id_study_program' => null,
            'code' => 'ASM-' . Fungsi::uniqueCode(5),
            'kode_panel' => 'T01-P003',
            'name' => 'Asesmen dengan Multiple Asesor (Testing)',
            'description' => 'Contoh asesmen dengan 3 asesor untuk testing dynamic UI',
            'tanggal_mulai'   => now(),
            'tanggal_selesai' => now()->addMonths(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $akMulti = DB::table('asesmen_kecukupan')->insertGetId([
            'id_asesmen' => $asesmenMulti,
            'code' => 'AK-' . Fungsi::uniqueCode(5),
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonths(1),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign 3 Asesor untuk testing
        $asesorsMulti = [4, 5, 6]; // 3 asesor
        foreach ($asesorsMulti as $index => $userId) {
            AsesmenUserRole::create([
                'id_asesmen' => $asesmenMulti,
                'id_user' => $userId,
                'id_role' => $roleAsesor,
                'jenis_asesmen' => 'ak',
                'id_asesmen_kecukupan' => $akMulti,
                'id_asesmen_lapangan' => null,
                'urutan_asesor' => $index + 1, // 1, 2, 3
                'status_penawaran' => 'accepted',
                'status_pekerjaan' => 'not_started',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 1 Validator
        AsesmenUserRole::create([
            'id_asesmen' => $asesmenMulti,
            'id_user' => 9,
            'id_role' => $roleValidator,
            'jenis_asesmen' => 'ak',
            'id_asesmen_kecukupan' => $akMulti,
            'id_asesmen_lapangan' => null,
            'urutan_asesor' => null,
            'status_penawaran' => 'accepted',
            'status_pekerjaan' => 'not_started',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
