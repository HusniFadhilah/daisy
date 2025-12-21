<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== ANALISIS 88 PRODI KADALUARSA ===\n\n";

$prodiKadaluarsa = DB::table('study_programs')
    ->join('universities', 'study_programs.id_univ', '=', 'universities.id')
    ->join('degree_levels', 'study_programs.id_level', '=', 'degree_levels.id')
    ->where('study_programs.status_kadaluarsa', 'Kadaluarsa')
    ->select(
        'study_programs.name as program',
        'degree_levels.name as level',
        'universities.name as university',
        'study_programs.peringkat_akreditasi',
        'study_programs.tanggal_kadaluarsa',
        'study_programs.status_kadaluarsa'
    )
    ->orderBy('study_programs.tanggal_kadaluarsa')
    ->get();

echo "Total: " . $prodiKadaluarsa->count() . " program studi\n\n";

// Grup berdasarkan peringkat
$groupedByPeringkat = $prodiKadaluarsa->groupBy('peringkat_akreditasi');

echo "Distribusi Peringkat Akreditasi:\n";
foreach ($groupedByPeringkat as $peringkat => $items) {
    $percentage = ($items->count() / $prodiKadaluarsa->count()) * 100;
    echo sprintf("  - %s: %d prodi (%.1f%%)\n", $peringkat, $items->count(), $percentage);
}

echo "\n";

// Grup berdasarkan tahun kadaluarsa
echo "Distribusi Tahun Kadaluarsa:\n";
$byYear = [];
foreach ($prodiKadaluarsa as $prodi) {
    if ($prodi->tanggal_kadaluarsa) {
        $year = Carbon::parse($prodi->tanggal_kadaluarsa)->year;
        if (!isset($byYear[$year])) {
            $byYear[$year] = 0;
        }
        $byYear[$year]++;
    }
}
ksort($byYear);
foreach ($byYear as $year => $count) {
    echo "  - $year: $count prodi\n";
}

echo "\n";

// Cek apakah ada yang sudah lewat dari hari ini
$today = Carbon::now();
$sudahLewat = 0;
$akanKadaluarsa = 0;

foreach ($prodiKadaluarsa as $prodi) {
    if ($prodi->tanggal_kadaluarsa) {
        $tanggalExp = Carbon::parse($prodi->tanggal_kadaluarsa);
        if ($tanggalExp->isPast()) {
            $sudahLewat++;
        } else {
            $akanKadaluarsa++;
        }
    }
}

echo "Status Berdasarkan Tanggal Hari Ini (" . $today->format('Y-m-d') . "):\n";
echo "  - Sudah kadaluarsa (tanggal < hari ini): $sudahLewat prodi\n";
echo "  - Akan kadaluarsa (tanggal >= hari ini): $akanKadaluarsa prodi\n";

echo "\n";
echo "Detail 10 Prodi Pertama (diurutkan dari tanggal kadaluarsa terdekat):\n";
echo str_repeat("=", 80) . "\n";

foreach ($prodiKadaluarsa->take(10) as $i => $prodi) {
    $num = $i + 1;
    $exp = $prodi->tanggal_kadaluarsa ? Carbon::parse($prodi->tanggal_kadaluarsa)->format('Y-m-d') : 'N/A';
    
    if ($prodi->tanggal_kadaluarsa) {
        $daysLeft = Carbon::now()->diffInDays(Carbon::parse($prodi->tanggal_kadaluarsa), false);
        if ($daysLeft < 0) {
            $status = abs($daysLeft) . " hari sudah lewat";
        } else {
            $status = $daysLeft . " hari lagi";
        }
    } else {
        $status = "Tanggal tidak tersedia";
    }
    
    echo "$num. {$prodi->program} ({$prodi->level})\n";
    echo "   Universitas: {$prodi->university}\n";
    echo "   Peringkat: {$prodi->peringkat_akreditasi}\n";
    echo "   Kadaluarsa: $exp ($status)\n\n";
}

echo "\n... dan " . ($prodiKadaluarsa->count() - 10) . " prodi lainnya.\n";
