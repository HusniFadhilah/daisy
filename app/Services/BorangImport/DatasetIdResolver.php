<?php

namespace App\Services\BorangImport;

use App\Models\DatasetBorang;
use App\Models\ElemenStandar;

class DatasetIdResolver
{
    private const SHEET_TO_ELEMEN_MAP = [
        'E.2'       => 'E.2',
        'E.3'       => 'E.3',
        'P.1'       => 'P.1',
        'L.1'       => 'L.1',
        'A.2'       => 'A.2',
        'A.5.1'     => 'A.5',
        'A.5.2'     => 'A.5',
        'A.5.3'     => 'A.5',
        'R.3.1.a'   => 'R.3',
        'R.3.2.1.a' => 'R.3',
        'R.6.1.a'   => 'R.6',
        'R.6.2.a'   => 'R.6',
    ];

    /**
     * Cache in-memory (menghindari query berulang)
     */
    private array $elemenIdByKode = [];      // ['E.2' => 12, ...]
    private array $datasetsByElemenId = [];  // [12 => [DatasetBorang, DatasetBorang, ...], ...]

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
     */
    public function getElemenKode(string $sheetName): string
    {
        if (isset(self::SHEET_TO_ELEMEN_MAP[$sheetName])) {
            return self::SHEET_TO_ELEMEN_MAP[$sheetName];
        }

        if (preg_match('/^([A-Z]\.\d+)/', $sheetName, $m)) {
            return $m[1];
        }

        return $sheetName;
    }

    /**
     * Preload elemen + dataset untuk semua sheet yang akan diproses.
     * Panggil SEKALI sebelum looping import agar tidak N+1 query.
     *
     * @param string[] $sheetNames
     */
    public function preload(array $sheetNames): void
    {
        // ambil semua kode elemen yang relevan
        $elemenKodes = array_values(array_unique(array_map(
            fn(string $s) => $this->getElemenKode($s),
            $sheetNames
        )));

        if (empty($elemenKodes)) {
            return;
        }

        // 1 query: ambil semua elemen
        $elemByKode = ElemenStandar::query()
            ->whereIn('kode_elemen', $elemenKodes)
            ->get(['id', 'kode_elemen'])
            ->keyBy('kode_elemen');

        $this->elemenIdByKode = [];
        $elemenIds = [];

        foreach ($elemByKode as $kode => $row) {
            $this->elemenIdByKode[$kode] = $row->id;
            $elemenIds[] = $row->id;
        }

        if (empty($elemenIds)) {
            $this->datasetsByElemenId = [];
            return;
        }

        // 1 query: ambil semua dataset table untuk elemen-elemen tsb
        // urutan penting karena pemilihan berdasarkan tableIndex
        $datasets = DatasetBorang::query()
            ->whereIn('id_elemen', $elemenIds)
            ->where('tipe_field', 'table')
            ->orderBy('id_elemen')
            ->orderBy('urutan')
            ->get();

        // group in-memory: [id_elemen => [dataset1, dataset2, ...]]
        $this->datasetsByElemenId = [];
        foreach ($datasets as $ds) {
            $this->datasetsByElemenId[$ds->id_elemen][] = $ds;
        }
    }

    /**
     * Cari DatasetBorang yang paling cocok untuk sheet ini
     * TANPA query setelah preload.
     */
    public function findDatasetBorang(string $sheetName, int $tableIndex): ?DatasetBorang
    {
        // tableIndex diasumsikan 1-based
        if ($tableIndex < 1) {
            return null;
        }

        $elemenKode = $this->getElemenKode($sheetName);
        $elemenId = $this->elemenIdByKode[$elemenKode] ?? null;

        if (!$elemenId) {
            // Jika preload belum dipanggil, fallback: load minimal sekali dan cache
            $elemen = ElemenStandar::where('kode_elemen', $elemenKode)->first(['id', 'kode_elemen']);
            if (!$elemen) return null;

            $elemenId = $elemen->id;
            $this->elemenIdByKode[$elemenKode] = $elemenId;

            $this->datasetsByElemenId[$elemenId] = DatasetBorang::query()
                ->where('id_elemen', $elemenId)
                ->where('tipe_field', 'table')
                ->orderBy('urutan')
                ->get()
                ->all();
        }

        $list = $this->datasetsByElemenId[$elemenId] ?? [];
        return $list[$tableIndex - 1] ?? null;
    }
}
