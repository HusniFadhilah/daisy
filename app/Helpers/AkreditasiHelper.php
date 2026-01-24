<?php

namespace App\Helpers;

class AkreditasiHelper
{
    /**
     * ✅ Get requirement text for peringkat
     */
    public static function getPeringkatRequirements(string $peringkat): array
    {
        return match ($peringkat) {

            'Tidak Terakreditasi' => [
                'Skor AL 0–250',
            ],

            'Terakreditasi Sementara (2 Tahun)' => [
                'Skor AL 251–300',
            ],

            'Terakreditasi (5 Tahun)' => [
                'Skor AL 301–350',
                'Atau skor AL ≥ 351 tetapi tidak memenuhi syarat unggul',
            ],

            'Terakreditasi Unggul 2 Tahun (dengan Syarat)' => [
                'Skor AL 351–360',
                'Memenuhi syarat unggul',
                'Setiap kriteria (D, E, P, I, L, A, R) memiliki minimal 1 elemen dengan skor 4 (Pelampauan Standar)',
                'Semua elemen lain minimal skor 3 (Memenuhi)',
            ],

            'Terakreditasi Unggul (5 Tahun)' => [
                'Skor AL 361–400',
                'Memenuhi syarat unggul',
                'Setiap kriteria (D, E, P, I, L, A, R) memiliki minimal 1 elemen dengan skor 4 (Pelampauan Standar)',
                'Semua elemen lain minimal skor 3 (Memenuhi)',
            ],

            default => [],
        };
    }

    /**
     * ✅ Format validation message
     */
    public static function formatValidationMessage(array $validationSummary): string
    {
        $messages = [];

        if (!$validationSummary['dapat_unggul'] && $validationSummary['skor_memenuhi']) {
            $messages[] = "⚠️ Skor mencapai syarat Unggul (>= 361), namun tidak memenuhi syarat pelampauan standar.";
            $messages[] = "📋 Kriteria yang belum memiliki pelampauan: " .
                implode(', ', $validationSummary['missing_kriteria']);
            $messages[] = "🔽 Peringkat diturunkan menjadi: Baik Sekali";
        }

        return implode("\n", $messages);
    }
}
