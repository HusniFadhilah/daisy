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
            'Unggul' => [
                'Skor AL minimal 361',
                'Setiap kriteria (D, E, P, I, L, A, R) memiliki minimal 1 elemen dengan skor 4 (Pelampauan Standar)',
                'Semua elemen lain minimal skor 3 (Memenuhi)',
            ],
            'Baik Sekali' => [
                'Skor AL antara 301-360',
                'Atau skor >= 361 tapi tidak memenuhi syarat pelampauan standar',
            ],
            'Baik' => [
                'Skor AL antara 200-300',
            ],
            'Tidak Terakreditasi' => [
                'Skor AL di bawah 200',
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
