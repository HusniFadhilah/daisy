<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING BOBOT CALCULATION ===\n\n";

// Check Asesmens
$asesmens = App\Models\Asesmen::count();
echo "Total Asesmen: {$asesmens}\n";

// Check PenilaianElemen
$penilaian = App\Models\PenilaianElemen::count();
echo "Total Penilaian Elemen: {$penilaian}\n\n";

if ($penilaian == 0) {
    echo "⚠️ MASALAH: Tidak ada data penilaian elemen!\n";
    echo "Tombol hitung tidak akan menghasilkan apa-apa karena tidak ada data untuk dihitung.\n\n";
    echo "Solusi: Buat data penilaian elemen terlebih dahulu melalui:\n";
    echo "- Menu Penugasan Asesmen\n";
    echo "- Atau seed data PenilaianElemen\n";
} else {
    // Show sample data
    echo "Sample Penilaian Elemen:\n";
    $samples = App\Models\PenilaianElemen::with(['elemenStandar', 'asesmen'])->take(5)->get();
    foreach ($samples as $s) {
        $asesmenName = $s->asesmen->name ?? 'N/A';
        $elemenKode = $s->elemenStandar->kode_elemen ?? 'N/A';
        echo "- Asesmen: {$asesmenName}, Elemen: {$elemenKode}, Skor: {$s->skor}\n";
    }
    echo "\n✓ Data penilaian tersedia! Tombol hitung seharusnya berfungsi.\n";
}
