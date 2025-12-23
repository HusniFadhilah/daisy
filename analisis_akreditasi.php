<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ANALISIS STATUS AKREDITASI PROGRAM STUDI ===\n\n";

$total = App\Models\StudyProgram::count();
echo "Total Program Studi: {$total}\n\n";

// Peringkat Akreditasi
echo "Peringkat Akreditasi:\n";
$peringkatCounts = App\Models\StudyProgram::selectRaw('peringkat_akreditasi, COUNT(*) as count')
    ->groupBy('peringkat_akreditasi')
    ->orderByRaw('FIELD(peringkat_akreditasi, "Unggul", "Baik Sekali", "Baik", "C", "Tidak Terakreditasi")')
    ->get();

foreach ($peringkatCounts as $peringkat) {
    $label = $peringkat->peringkat_akreditasi ?? 'NULL/Kosong';
    $count = $peringkat->count;
    $percentage = round(($count / $total) * 100, 1);
    echo "  - {$label}: {$count} prodi ({$percentage}%)\n";
}

// Status Kadaluarsa
echo "\n\nStatus Kadaluarsa:\n";
$statusCounts = App\Models\StudyProgram::selectRaw('status_kadaluarsa, COUNT(*) as count')
    ->groupBy('status_kadaluarsa')
    ->get();

foreach ($statusCounts as $status) {
    $statusLabel = $status->status_kadaluarsa ?? 'NULL';
    $count = $status->count;
    $percentage = round(($count / $total) * 100, 1);
    echo "  - {$statusLabel}: {$count} prodi ({$percentage}%)\n";
}

// Detail prodi tanpa akreditasi
echo "\n\nProgram Studi Belum/Tidak Terakreditasi:\n";
$tidakTerakreditasi = App\Models\StudyProgram::where('status_kadaluarsa', 'Belum Terakreditasi')
    ->orWhereNull('peringkat_akreditasi')
    ->with(['university', 'degreeLevel'])
    ->get();

if ($tidakTerakreditasi->count() > 0) {
    foreach ($tidakTerakreditasi->take(10) as $prodi) {
        $univ = $prodi->university->name ?? 'N/A';
        $level = $prodi->degreeLevel->name ?? 'N/A';
        $peringkat = $prodi->peringkat_akreditasi ?? 'NULL';
        echo "  - {$prodi->name} ({$level}) - {$univ} [Peringkat: {$peringkat}]\n";
    }
    if ($tidakTerakreditasi->count() > 10) {
        echo "  ... dan " . ($tidakTerakreditasi->count() - 10) . " prodi lainnya\n";
    }
} else {
    echo "  Semua prodi sudah terakreditasi!\n";
}

// Detail prodi kadaluarsa
echo "\n\nProgram Studi Akreditasi Kadaluarsa:\n";
$kadaluarsaDetail = App\Models\StudyProgram::where('status_kadaluarsa', 'Kadaluarsa')
    ->with(['university', 'degreeLevel'])
    ->get();

if ($kadaluarsaDetail->count() > 0) {
    foreach ($kadaluarsaDetail->take(10) as $prodi) {
        $univ = $prodi->university->name ?? 'N/A';
        $level = $prodi->degreeLevel->name ?? 'N/A';
        $tanggal = $prodi->tanggal_kadaluarsa ?? 'N/A';
        echo "  - {$prodi->name} ({$level}) - {$univ} [Kadaluarsa: {$tanggal}]\n";
    }
    if ($kadaluarsaDetail->count() > 10) {
        echo "  ... dan " . ($kadaluarsaDetail->count() - 10) . " prodi lainnya\n";
    }
} else {
    echo "  Tidak ada prodi dengan akreditasi kadaluarsa!\n";
}

// Ringkasan per Jenjang
echo "\n\nStatus Akreditasi per Jenjang:\n";
$perJenjang = App\Models\StudyProgram::with('degreeLevel')
    ->get()
    ->groupBy('id_level');

foreach ($perJenjang as $levelId => $prodis) {
    $levelName = $prodis->first()->degreeLevel->name ?? 'N/A';
    $total = $prodis->count();
    $terakreditasi = $prodis->whereNotIn('status_kadaluarsa', ['Belum Terakreditasi'])->count();
    $percentage = $total > 0 ? round(($terakreditasi / $total) * 100, 1) : 0;
    echo "  - {$levelName}: {$terakreditasi}/{$total} terakreditasi ({$percentage}%)\n";
}

// DETAIL SEMUA PRODI DENGAN STATUS KADALUARSA
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "DETAIL SEMUA PROGRAM STUDI BERDASARKAN STATUS KADALUARSA\n";
echo str_repeat("=", 80) . "\n";

$allProdis = App\Models\StudyProgram::with(['university', 'degreeLevel'])
    ->orderBy('status_kadaluarsa')
    ->orderBy('name')
    ->get();

$byStatus = $allProdis->groupBy('status_kadaluarsa');

foreach ($byStatus as $status => $prodis) {
    echo "\n[{$status}] - Total: {$prodis->count()} prodi\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($prodis as $index => $prodi) {
        $num = $index + 1;
        $univ = $prodi->university->name ?? 'N/A';
        $level = $prodi->degreeLevel->code ?? 'N/A';
        $peringkat = $prodi->peringkat_akreditasi ?? 'NULL';
        $tanggal = $prodi->tanggal_kadaluarsa ?? 'N/A';
        
        echo sprintf("%3d. %-50s | %-4s | Peringkat: %-12s | Exp: %s\n", 
            $num, 
            substr($prodi->name, 0, 50),
            $level,
            $peringkat,
            $tanggal
        );
    }
}
