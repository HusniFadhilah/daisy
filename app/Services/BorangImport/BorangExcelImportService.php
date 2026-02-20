<?php

namespace App\Services\BorangImport;

use App\Models\BorangDataExcel;
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

            foreach ($parsedTables as $parsedTable) {
                $datasetId = $this->resolver->resolve($sheetName, $parsedTable->tableIndex);
                $datasetBorang = $this->resolver->findDatasetBorang($sheetName, $parsedTable->tableIndex);

                $nilai = json_encode([
                    'sheet' => $sheetName,
                    'table_index' => $parsedTable->tableIndex,
                    'table_title' => $parsedTable->tableTitle,
                    'headers' => $parsedTable->headers,
                    'rows' => $parsedTable->rows,
                ], JSON_UNESCAPED_UNICODE);

                BorangDataExcel::updateOrCreate(
                    [
                        'id_borang_import' => $importId,
                        'sheet_name'       => $sheetName,
                        'table_index'      => $parsedTable->tableIndex,
                    ],
                    [
                        'id_pengajuan'      => $pengajuanId,
                        'id_degree_level'   => $degreeLevelId,
                        'elemen_kode'       => $this->resolver->getElemenKode($sheetName),
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
