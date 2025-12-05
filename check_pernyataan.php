<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pernyataan;

echo "=== CHECKING PERNYATAAN DATA ===\n\n";

$total = Pernyataan::count();
echo "Total Pernyataan: $total\n\n";

// Check P1.1.1 specifically
$p111 = Pernyataan::where('code', 'P1.1.1')->first();
if ($p111) {
    echo "✓ P1.1.1 EXISTS\n";
    echo "  Preview: " . substr($p111->pernyataan, 0, 100) . "...\n\n";
} else {
    echo "✗ P1.1.1 NOT FOUND\n\n";
}

// List all P1.x codes
echo "All P1.x codes:\n";
$p1List = Pernyataan::where('code', 'like', 'P1.%')
    ->orderBy('code')
    ->get(['code']);

foreach ($p1List as $p) {
    echo "  - {$p->code}\n";
}

echo "\n=== END ===\n";
