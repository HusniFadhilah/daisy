<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BobotPenilaian>
 */
class BobotPenilaianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_elemen' => \App\Models\ElemenStandar::inRandomOrder()->first()->id,
            'id_level' => \App\Models\DegreeLevel::inRandomOrder()->first()->id,
            'bobot' => fake()->numberBetween(1, 10),
        ];
    }
}
