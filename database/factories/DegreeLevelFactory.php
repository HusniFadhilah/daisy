<?php

namespace Database\Factories;

use App\Models\DegreeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class DegreeLevelFactory extends Factory
{
    protected $model = DegreeLevel::class;

    public function definition(): array
    {
        $levels = [
            ['S1', 'S1', 'Sarjana'],
            ['S2', 'S2', 'Magister'],
            ['S3', 'S3', 'Doktor'],
        ];
        $level = $this->faker->randomElement($levels);

        return [
            'code'      => $this->faker->unique()->bothify($level[0] . '-##'),
            'alias'     => $level[1],
            'name'      => $level[2],
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
