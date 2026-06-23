<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function sekretariat(): static
    {
        return $this->state([
            'role_selected'   => 'sekretariat',
            'roles'           => ['sekretariat'],
            'is_multiple_role' => false,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state([
            'role_selected'   => 'super_admin',
            'roles'           => ['super_admin'],
            'is_multiple_role' => false,
        ]);
    }

    public function asesor(): static
    {
        return $this->state([
            'role_selected'   => 'asesor',
            'roles'           => ['asesor'],
            'is_multiple_role' => false,
        ]);
    }

    public function validator(): static
    {
        return $this->state([
            'role_selected'   => 'validator',
            'roles'           => ['validator'],
            'is_multiple_role' => false,
        ]);
    }

    public function adminProdi(): static
    {
        return $this->state([
            'role_selected'   => 'admin_prodi',
            'roles'           => ['admin_prodi'],
            'is_multiple_role' => false,
        ]);
    }

    public function keuangan(): static
    {
        return $this->state([
            'role_selected'   => 'keuangan_lamdepilar',
            'roles'           => ['keuangan_lamdepilar'],
            'is_multiple_role' => false,
        ]);
    }

    public function withRole(string $role): static
    {
        return $this->state([
            'role_selected'   => $role,
            'roles'           => [$role],
            'is_multiple_role' => false,
        ]);
    }
}
