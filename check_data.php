<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Study Program Categories: " . App\Models\StudyProgramCategory::count() . "\n";
echo "Bobot Penilaian: " . App\Models\BobotPenilaian::count() . "\n";

echo "\nCategories:\n";
foreach (App\Models\StudyProgramCategory::all() as $cat) {
    echo "- {$cat->code}: {$cat->name}\n";
}

echo "\nBobot sample (first 5):\n";
foreach (App\Models\BobotPenilaian::with(['elemenStandar', 'category'])->take(5)->get() as $bobot) {
    echo "- Elemen {$bobot->elemenStandar->kode_elemen} × {$bobot->category->code} = {$bobot->bobot}\n";
}
