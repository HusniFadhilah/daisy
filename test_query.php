<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\StudyProgram;

echo "Testing query...\n\n";

// Test 1: Hard-coded query (no binding)
echo "=== Test 1: Hard-coded query ===\n";
try {
    $stats = DB::table('study_programs')->selectRaw("
        COUNT(*) as total,
        SUM(CASE WHEN status_kadaluwarsa = 'Aktif' THEN 1 ELSE 0 END) as aktif
    ")->first();

    echo "Query SUCCESS!\n";
    echo json_encode($stats, JSON_PRETTY_PRINT);
    echo "\n\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// Test 2: With backticks
echo "=== Test 2: With backticks ===\n";
try {
    $stats = DB::table('study_programs')->selectRaw("
        COUNT(*) as total,
        SUM(CASE WHEN `status_kadaluwarsa` = 'Aktif' THEN 1 ELSE 0 END) as aktif
    ")->first();

    echo "Query SUCCESS!\n";
    echo json_encode($stats, JSON_PRETTY_PRINT);
    echo "\n\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}

// Test 3: Using DB::raw
echo "=== Test 3: Using DB::raw ===\n";
try {
    $stats = DB::table('study_programs')
        ->select(DB::raw("COUNT(*) as total, SUM(CASE WHEN status_kadaluwarsa = 'Aktif' THEN 1 ELSE 0 END) as aktif"))
        ->first();

    echo "Query SUCCESS!\n";
    echo json_encode($stats, JSON_PRETTY_PRINT);
    echo "\n\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n\n";
}
