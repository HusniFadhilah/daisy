<?php

namespace App\Services\BorangImport;

use App\Models\DatasetBorang;
use App\Models\ElemenStandar;

class DatasetIdResolver
{
    // Dalam DatasetIdResolver
    private const SHEET_TO_ELEMEN_MAP = [
        'E.2'      => 'E.2',   // Admisi Mahasiswa
        'E.3'      => 'E.3',   // Proses Pembelajaran
        'P.1'      => 'P.1',   // Dosen & Tendik
        'L.1'      => 'L.1',   // Sarana Belajar
        'A.2'      => 'A.2',   // Kerjasama
        'A.5.1'    => 'A.5',   // Keuangan (Pemasukan)
        'A.5.2'    => 'A.5',   // Keuangan (Pengeluaran)
        'A.5.3'    => 'A.5',   // Keuangan (Neraca)
        'R.3.1.a'  => 'R.3',   // Luaran Penelitian
        'R.3.2.1.a' => 'R.3',   // Bobot Luaran
        'R.6.1.a'  => 'R.6',   // Luaran PkM
        'R.6.2.a'  => 'R.6',   // Bobot PkM
    ];

    /**
     * Primary key untuk borang_data.dataset_id
     * Format: LKPS_{sheetName}_T{index}
     */
    public function resolve(string $sheetName, int $tableIndex): string
    {
        $clean = str_replace(' ', '_', $sheetName);
        return "LKPS_{$clean}_T{$tableIndex}";
    }

    /**
     * Normalisasi sheet name ke kode elemen
     * "R.3.1.a" → "R.3", "A.5.1" → "A.5", "E.2" → "E.2"
     */
    public function getElemenKode(string $sheetName): string
    {
        // Pattern: huruf besar + titik + angka (+ sub-sheet opsional)
        if (preg_match('/^([A-Z]\.\d+)/', $sheetName, $m)) {
            return $m[1];
        }
        return $sheetName;
    }

    /**
     * Cari DatasetBorang yang paling cocok untuk sheet ini
     * (Opsional - untuk linking ke dataset_borang)
     */
    public function findDatasetBorang(string $sheetName, int $tableIndex): ?DatasetBorang
    {
        $elemenKode = $this->getElemenKode($sheetName);
        $elemen = ElemenStandar::where('kode_elemen', $elemenKode)->first();
        if (!$elemen) return null;

        return DatasetBorang::where('id_elemen', $elemen->id)
            ->where('tipe_field', 'table')
            ->orderBy('urutan')
            ->skip($tableIndex - 1)
            ->first();
    }
}
