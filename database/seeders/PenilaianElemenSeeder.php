<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenilaianElemenAK;
use App\Models\Asesmen;
use App\Models\ElemenStandar;

class PenilaianElemenSeeder extends Seeder
{
    /**
     * Seed dummy penilaian elemen data for testing
     */
    public function run(): void
    {
        // Ambil asesmen pertama
        $asesmen = Asesmen::first();

        if (!$asesmen) {
            echo "Tidak ada asesmen. Buat asesmen terlebih dahulu.\n";
            return;
        }

        // Ambil semua elemen
        $elemens = ElemenStandar::all();

        if ($elemens->isEmpty()) {
            echo "Tidak ada elemen standar.\n";
            return;
        }

        // Buat penilaian dummy dengan skor random 0-3
        foreach ($elemens as $elemen) {
            PenilaianElemenAK::updateOrCreate(
                [
                    'id_asesmen' => $asesmen->id,
                    'id_user' => 1, // Admin user
                    'id_elemen' => $elemen->id_elemen,
                ],
                [
                    'skor' => rand(0, 3), // Random skor 0-3 (Not Met, Not Met, Weakness, Met)
                    'komentar' => 'Data dummy untuk testing perhitungan bobot',
                    'status' => 'submitted',
                ]
            );
        }

        echo "Penilaian Elemen seeded successfully!\n";
        echo "Total records: " . $elemens->count() . "\n";
    }
}
