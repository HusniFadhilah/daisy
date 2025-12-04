<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@daisy.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Regular users
        $users = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@daisy.ac.id',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@daisy.ac.id',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@daisy.ac.id',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@daisy.ac.id',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            [
                'name' => 'Rudi Hermawan',
                'email' => 'rudi.hermawan@daisy.ac.id',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            [
                'name' => 'Rina Wati',
                'email' => 'rina.wati@daisy.ac.id',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@daisy.ac.id',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
