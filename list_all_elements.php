<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$elements = App\Models\ElemenStandar::orderBy('kode_elemen')->get();
echo "Total: " . $elements->count() . " elemen\n\n";

$byKriteria = $elements->groupBy('id_kriteria');
foreach ($byKriteria as $kriteriaId => $elems) {
    $kriteria = App\Models\Kriteria::find($kriteriaId);
    echo "{$kriteria->kode_kriteria} - {$kriteria->nama_kriteria}: " . $elems->count() . " elemen\n";
    foreach ($elems as $elem) {
        echo "  - {$elem->kode_elemen}: {$elem->pernyataan_elemen}\n";
    }
    echo "\n";
}
