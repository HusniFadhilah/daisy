<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;

class UniversityFactory extends Factory
{
    protected $model = University::class;

    public function definition(): array
    {
        return [
            'code'       => $this->faker->unique()->bothify('UNIV-####'),
            'name'       => $this->faker->company() . ' University',
            'is_active'  => true,
            'is_example' => false,
        ];
    }

    public function example(): static
    {
        return $this->state(['is_example' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
