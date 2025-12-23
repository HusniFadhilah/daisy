<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$universities = [
    'Universitas Tunas Pembangunan Surakarta',
    'Universitas Aisyiyah Bandung',
    'Universitas Islam Negeri Syekh Ali Hasan Ahmad Addary Padangsidimpuan',
    'Universitas Maritim Raja Ali Haji'
];

$programs = DB::table('study_programs')
    ->join('universities', 'study_programs.id_univ', '=', 'universities.id')
    ->join('degree_levels', 'study_programs.id_level', '=', 'degree_levels.id')
    ->whereIn('universities.name', $universities)
    ->select(
        'universities.name as university',
        'study_programs.name as program',
        'degree_levels.name as level',
        'study_programs.peringkat_akreditasi'
    )
    ->get();

echo "=== 5 DATA YANG TADINYA GAGAL DIMUAT ===\n\n";
echo "Total: " . $programs->count() . " program studi\n\n";

foreach ($programs as $p) {
    echo "• {$p->program} ({$p->level})\n";
    echo "  Universitas: {$p->university}\n";
    echo "  Peringkat: {$p->peringkat_akreditasi}\n\n";
}
