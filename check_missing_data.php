<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudyProgram;
use App\Models\University;
use App\Models\DegreeLevel;

$csvFile = __DIR__ . '/database/seeders/data/data_akreditasi_lengkap.csv';
$file = fopen($csvFile, 'r');

// Skip header
fgetcsv($file);

$notFoundUniversities = [];
$notFoundPrograms = [];
$matched = 0;

while (($data = fgetcsv($file)) !== false) {
    $universitas = $data[0];
    $programStudi = $data[1];
    $jenjang = $data[2];
    
    // Map university name
    $mappings = [
        "Universitas Tunas Pembangunan Surakarta (UTP)" => "Universitas Tunas Pembangunan Surakarta",
        "Universitas Islam Negeri Syekh AIi Hasan Ahmad Addary Padangsidimpuan" => "Universitas Islam Negeri Syekh Ali Hasan Ahmad Addary Padangsidimpuan",
        "Universitas 'Aisyiyah Bandung" => "Universitas Aisyiyah Bandung",
        "Universitas Maritim Raja Ali Haji (UMRAH)" => "Universitas Maritim Raja Ali Haji",
    ];
    $universitas = $mappings[$universitas] ?? $universitas;
    
    // Map jenjang
    $jenjangMappings = [
        'S1' => 'Sarjana (Strata 1)',
        'S2' => 'Magister (Strata 2)',
        'S3' => 'Doktor (Strata 3)',
        'D-III' => 'Diploma III',
        'D-IV' => 'Diploma IV',
        'D3' => 'Diploma III',
        'D4' => 'Diploma IV',
        'STr' => 'Diploma IV',
    ];
    $jenjangMapped = $jenjangMappings[$jenjang] ?? $jenjang;
    
    // Check if university exists
    $univ = University::where('name', $universitas)->first();
    if (!$univ) {
        if (!in_array($universitas, $notFoundUniversities)) {
            $notFoundUniversities[] = $universitas;
        }
        continue;
    }
    
    // Check if degree level exists
    $degree = DegreeLevel::where('name', $jenjangMapped)->first();
    if (!$degree) {
        $key = "Jenjang: {$jenjang} ({$jenjangMapped})";
        $notFoundPrograms[$key] = "{$programStudi} ({$jenjang}) - {$universitas}";
        continue;
    }
    
    // Check if study program exists
    $program = StudyProgram::where('id_univ', $univ->id)
        ->where('id_level', $degree->id)
        ->where('name', $programStudi)
        ->first();
    
    if (!$program) {
        $key = "{$programStudi}|{$jenjang}|{$universitas}";
        $notFoundPrograms[$key] = "{$programStudi} ({$jenjang}) - {$universitas}";
    } else {
        $matched++;
    }
}

fclose($file);

echo "=== HASIL PENCOCOKAN DATA CSV DAN DATABASE ===\n\n";
echo "Total data CSV: 800\n";
echo "Total data matched: {$matched}\n";
echo "Total data tidak match: " . (800 - $matched) . "\n\n";

if (!empty($notFoundUniversities)) {
    echo "=== UNIVERSITAS TIDAK DITEMUKAN (" . count($notFoundUniversities) . ") ===\n";
    foreach ($notFoundUniversities as $univ) {
        echo "- {$univ}\n";
    }
    echo "\n";
}

if (!empty($notFoundPrograms)) {
    echo "=== PROGRAM STUDI TIDAK DITEMUKAN (" . count($notFoundPrograms) . ") ===\n";
    $counter = 1;
    foreach ($notFoundPrograms as $program) {
        echo "{$counter}. {$program}\n";
        $counter++;
    }
}
