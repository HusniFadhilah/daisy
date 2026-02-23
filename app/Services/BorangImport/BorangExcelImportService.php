<?php

namespace App\Services\BorangImport;

use App\Models\BorangDataExcel;
use App\Models\ElemenStandar;
use Illuminate\Support\Facades\Log;

class BorangExcelImportService
{
    public function __construct(
        private BorangExcelSheetParser $parser,
        private DatasetIdResolver $resolver,
    ) {}

    /**
     * Proses satu sheet dan simpan ke borang_data
     */
    public function processSheet(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        string $sheetName,
        int $pengajuanId,
        int $importId,
        ?int $degreeLevelId = null
    ): array {
        $stats = ['tables' => 0, 'rows' => 0, 'errors' => []];

        try {
            $parsedTables = $this->parser->parse($ws, $sheetName);
            // Kalau sheet kosong/tidak ada tabel, stop cepat
            if (empty($parsedTables)) {
                return $stats;
            }

            // ✅ 1) ElemenStandar: query SEKALI per sheet (bukan per tabel)
            $elemenKode = trim($this->resolver->getElemenKode($sheetName));
            $elemen = ElemenStandar::where('kode_elemen', $elemenKode)->firstOrFail();

            // ✅ 2) DatasetBorang: preload SEKALI (menghindari query per tabel)
            // (asumsi resolver kamu punya method preload seperti yang aku kasih sebelumnya)
            $this->resolver->preload([$sheetName]);

            foreach ($parsedTables as $parsedTable) {
                $datasetId = $this->resolver->resolve($sheetName, $parsedTable->tableIndex);

                // setelah preload, ini harusnya no-query (in-memory)
                $datasetBorang = $this->resolver->findDatasetBorang($sheetName, $parsedTable->tableIndex);

                BorangDataExcel::updateOrCreate(
                    [
                        'id_pengajuan' => $pengajuanId,
                        'sheet_name'   => $sheetName,
                        'table_index'  => $parsedTable->tableIndex,
                    ],
                    [
                        'id_borang_import' => $importId,
                        'id_pengajuan'      => $pengajuanId,
                        'id_degree_level'   => $degreeLevelId,
                        'id_elemen'         => $elemen->id,
                        'elemen_kode'       => $elemen->kode_elemen,
                        'table_title'       => $parsedTable->tableTitle,
                        'id_dataset_borang' => $datasetBorang?->id,
                        'headers'           => $parsedTable->headers,
                        'rows'              => $parsedTable->rows,
                        'status_review'     => 'raw',
                    ]
                );

                $stats['tables']++;
                $stats['rows'] += count($parsedTable->rows);
            }
        } catch (\Exception $e) {
            $stats['errors'][] = "[{$sheetName}] {$e->getMessage()}";
            Log::error("BorangExcelImportService error on sheet {$sheetName}: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Generate HTML preview dari ParsedTable (untuk tampilan di UI)
     */
    public function toHtml(array $parsedData): string
    {
        $headers = $parsedData['headers'] ?? [];
        $rows = $parsedData['rows'] ?? [];

        $html = '<table class="table table-bordered table-sm">';

        // Render header rows
        $html .= '<thead class="table-light">';
        foreach ($headers as $headerRow) {
            $html .= '<tr>';
            foreach ($headerRow as $cell) {
                if ($cell !== null && $cell !== '') {
                    $html .= '<th>' . htmlspecialchars((string)$cell) . '</th>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</thead>';

        // Render data rows
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)($cell ?? '')) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>
</table>';

        return $html;
    }
}
