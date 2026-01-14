<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ElemenStandar;

echo "=== CEK FORMAT KODE ELEMEN ===\n\n";

$elemenStandars = ElemenStandar::limit(10)->get(['id', 'kode_elemen']);

if ($elemenStandars->isEmpty()) {
    echo "Tidak ada data elemen standar!\n";
} else {
    foreach ($elemenStandars as $elemen) {
        echo "ID: {$elemen->id} - Kode: {$elemen->kode_elemen}\n";
    }
}
