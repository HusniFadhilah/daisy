<?php

namespace Database\Factories;

use App\Models\PengajuanAkreditasi;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengajuanAkreditasiFactory extends Factory
{
    protected $model = PengajuanAkreditasi::class;

    public function definition(): array
    {
        return [
            'nomor_pengajuan'     => 'AK/' . now()->year . '/' . str_pad($this->faker->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'id_program_studi'    => StudyProgram::factory(),
            'tahun_akreditasi'    => now()->year,
            'jenis_akreditasi'    => $this->faker->randomElement(['baru', 'terakreditasi', 'perpanjangan']),
            'kelompok_akreditasi' => 'individual',
            'status'              => PengajuanAkreditasi::STATUS_NEW,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(['status' => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS]);
    }

    public function draft(): static
    {
        return $this->state(['status' => PengajuanAkreditasi::STATUS_DRAFT]);
    }
}
