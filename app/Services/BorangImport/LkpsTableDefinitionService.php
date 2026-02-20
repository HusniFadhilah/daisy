<?php

namespace App\Services\BorangImport;

use App\Models\DatasetBorang;
use App\Models\ElemenStandar;

class LkpsTableDefinitionService
{
    /**
     * Mapping: sheet_name → [ [table_index, table_title, headers[]] ]
     * Diambil dari DatasetBorang (expected_columns) sesuai urutan
     */
    public function getDefinitions(?int $degreeLevelId = null): array
    {
        // Kode elemen yang masuk LKPS (bukan LED)
        $lkpsElemenKodes = [
            'E.2',
            'E.3',
            'P.1',
            'L.1',
            'A.2',
            'A.5',
            'R.3',
            'R.6',
        ];

        $result = [];

        foreach ($lkpsElemenKodes as $kode) {
            $elemen = ElemenStandar::where('kode_elemen', $kode)->first();
            if (!$elemen) continue;

            $datasets = DatasetBorang::where('id_elemen', $elemen->id)
                ->where('tipe_field', 'table')
                ->where('is_active', true)
                ->forDegreeLevel($degreeLevelId)
                ->ordered()
                ->get();

            if ($datasets->isEmpty()) continue;

            $tables = [];
            foreach ($datasets as $idx => $dataset) {
                $template = $dataset->getTemplateForDegree($degreeLevelId);

                $tables[] = [
                    'table_index' => $idx + 1,
                    'table_title' => $dataset->nama,
                    'dataset_borang_id' => $dataset->id,
                    'dataset_kode' => $dataset->kode,
                    'headers' => [$template['expected_columns'] ?? []],
                    'keterangan' => $dataset->keterangan,
                ];
            }

            $result[$kode] = [
                'elemen_kode' => $kode,
                'sheet_name' => $kode, // online mode pakai kode elemen sebagai "sheet"
                'tables' => $tables,
            ];
        }

        return $result;
    }

    /**
     * Get definisi satu tabel spesifik
     */
    public function getTableDefinition(int $datasetBorangId, ?int $degreeLevelId = null): ?array
    {
        $dataset = DatasetBorang::find($datasetBorangId);
        if (!$dataset) return null;

        $template = $dataset->getTemplateForDegree($degreeLevelId);

        return [
            'dataset_borang_id' => $dataset->id,
            'dataset_kode' => $dataset->kode,
            'table_title' => $dataset->nama,
            'headers' => [$template['expected_columns'] ?? []],
            'keterangan' => $dataset->keterangan,
        ];
    }
}
