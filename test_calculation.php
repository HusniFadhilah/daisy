<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING CALCULATION ENDPOINT ===\n\n";

$asesmenId = 1; // First asesmen
$categoryId = 1; // First category (Akademik)

echo "Testing calculation for:\n";
echo "- Asesmen ID: {$asesmenId}\n";
echo "- Category ID: {$categoryId}\n\n";

try {
    $service = new App\Services\BobotPenilaianService();
    $hasil = $service->calculateTotalScore($asesmenId, $categoryId);
    
    echo "✓ Calculation SUCCESS!\n\n";
    echo "Results:\n";
    echo "- Total Kriteria: " . count($hasil['per_kriteria']) . "\n";
    echo "- Total Nilai Berbobot: " . $hasil['total_nilai_bobot'] . "\n";
    echo "- Total Bobot: " . $hasil['total_bobot'] . "\n";
    echo "- Nilai Akhir: " . $hasil['nilai_akhir'] . "\n\n";
    
    echo "Detail per Kriteria:\n";
    foreach ($hasil['per_kriteria'] as $kriteria) {
        echo "- {$kriteria['kriteria_kode']}: {$kriteria['total']} (dari " . count($kriteria['elemen']) . " elemen)\n";
    }
    
} catch (\Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
