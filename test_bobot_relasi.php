<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\BobotPenilaian;

echo "=== TEST BOBOT DENGAN RELASI ===\n\n";

$bobots = BobotPenilaian::with(['elemenStandar.kriteria', 'category'])->limit(3)->get();

echo "Total: " . $bobots->count() . "\n\n";

foreach ($bobots as $bobot) {
    echo "ID: {$bobot->id}\n";
    echo "Elemen: " . ($bobot->elemenStandar ? $bobot->elemenStandar->kode_elemen : 'NULL') . "\n";
    echo "Category: " . ($bobot->category ? $bobot->category->name : 'NULL') . "\n";
    echo "Bobot: {$bobot->bobot}\n";
    echo str_repeat("-", 50) . "\n";
}
