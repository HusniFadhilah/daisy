<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudyProgram;
use App\Models\University;

// Cek semua program studi Arsitektur di Universitas Hasanuddin
$programs = StudyProgram::whereHas('university', function($q) {
    $q->where('name', 'LIKE', '%Hasanuddin%');
})
->where('name', 'Arsitektur')
->with('university', 'degreeLevel')
->get();

echo "Total program ditemukan: " . $programs->count() . "\n\n";

foreach ($programs as $program) {
    echo "ID: " . $program->id . "\n";
    echo "Kode: " . $program->code . "\n";
    echo "Nama: " . $program->name . "\n";
    echo "Universitas: " . $program->university->name . "\n";
    echo "Jenjang: " . $program->degreeLevel->name . "\n";
    echo "Peringkat: " . ($program->peringkat_akreditasi ?? 'NULL') . "\n";
    echo "Tanggal Kadaluarsa: " . ($program->tanggal_kadaluarsa ?? 'NULL') . "\n";
    echo "Status: " . ($program->status_kadaluarsa ?? 'NULL') . "\n";
    echo "---\n\n";
}
