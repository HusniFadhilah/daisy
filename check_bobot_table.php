<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CEK TABEL BOBOT_PENILAIAN ===\n\n";

try {
    $exists = DB::select("SHOW TABLES LIKE 'bobot_penilaian'");
    
    if (!empty($exists)) {
        echo "✓ Tabel 'bobot_penilaian' ADA di database\n\n";
        
        $count = DB::table('bobot_penilaian')->count();
        echo "Jumlah data: {$count}\n";
        
        if ($count > 0) {
            $sample = DB::table('bobot_penilaian')->limit(3)->get();
            echo "\nContoh data:\n";
            foreach ($sample as $row) {
                echo "- ID: {$row->id}, Elemen: {$row->id_elemen}, Category: {$row->id_category}, Bobot: {$row->bobot}\n";
            }
        }
    } else {
        echo "✗ Tabel 'bobot_penilaian' TIDAK ADA di database\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
