<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudyProgramCategory;

echo "=== CEK KATEGORI PROGRAM STUDI ===\n\n";

$categories = StudyProgramCategory::all(['id', 'code', 'name']);

echo "Total categories: " . $categories->count() . "\n\n";

if ($categories->isNotEmpty()) {
    foreach ($categories as $cat) {
        echo "ID: {$cat->id} - Code: {$cat->code} - Name: {$cat->name}\n";
    }
}
