<?php

namespace Database\Factories;

use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyProgramFactory extends Factory
{
    protected $model = StudyProgram::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'name'               => $name,
            'full_name'          => 'Program Studi ' . ucwords($name),
            'code'               => $this->faker->unique()->bothify('SP-####'),
            'id_university'      => University::factory(),
            'id_degree_level'    => DegreeLevel::factory(),
            'peringkat_akreditasi' => $this->faker->randomElement(['Unggul', 'Baik Sekali', 'Baik', null]),
            'status_kedaluwarsa' => $this->faker->randomElement(['Aktif', 'Kedaluwarsa', 'Belum Terakreditasi']),
            'tanggal_kedaluwarsa' => $this->faker->dateTimeBetween('-2 years', '+5 years')->format('Y-m-d'),
            'is_active'          => true,
            'is_example'         => false,
        ];
    }

    public function active(): static
    {
        return $this->state([
            'status_kedaluwarsa'  => 'Aktif',
            'peringkat_akreditasi' => 'Unggul',
            'tanggal_kedaluwarsa' => now()->addYears(3)->format('Y-m-d'),
        ]);
    }

    public function expired(): static
    {
        return $this->state([
            'status_kedaluwarsa'  => 'Kedaluwarsa',
            'tanggal_kedaluwarsa' => now()->subYear()->format('Y-m-d'),
        ]);
    }

    public function belumTerakreditasi(): static
    {
        return $this->state([
            'status_kedaluwarsa'   => 'Belum Terakreditasi',
            'peringkat_akreditasi' => null,
            'tanggal_kedaluwarsa'  => null,
        ]);
    }

    public function example(): static
    {
        return $this->state(['is_example' => true]);
    }
}
