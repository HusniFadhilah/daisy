<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ProgramStudi;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use App\Models\PengajuanAkreditasi;

class PengajuanAkreditasiSeeder extends Seeder
{
    public function run()
    {
        $de = User::where('role_selected', 'asesi')->first();

        $prodi = StudyProgram::first();
        $userProdi = User::where('role_selected', 'admin_prodi')->first();

        if (!$de || !$userProdi) {
            $this->command->error('Please seed users and program studi first!');
            return;
        }

        // Sample pengajuan with different statuses
        $statuses = [
            'pengingat_dikirim',
            'surat_permohonan_diterima',
            'borang_dikirim',
            'draft_borang_diterima',
            'review_kesiapan_siap',
            'menunggu_pembayaran',
        ];

        foreach ($statuses as $index => $status) {
            PengajuanAkreditasi::create([
                'nomor_pengajuan' => 'AK/2024/' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'id_program_studi' => $prodi->id,
                'id_user_pengaju' => $userProdi->id,
                'id_de_assigned' => $de->id,
                'id_validator_assigned' => $de->id,
                'tahun_akreditasi' => 2024,
                'jenis_akreditasi' => 'baru',
                'status' => $status,
                'tanggal_pengajuan' => now()->subDays($index),
            ]);
        }

        $this->command->info('Sample pengajuan created successfully!');
    }
}
