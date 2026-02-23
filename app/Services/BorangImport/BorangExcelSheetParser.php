<?php

namespace App\Services\BorangImport;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParsedTable
{
    public string  $sheetName;
    public int     $tableIndex;
    public string  $tableTitle;
    public array   $headers;     // string[][]  – bisa multi-row
    public array   $rows;        // mixed[][]
    public int     $startRow;
    public int     $endRow;
}

class BorangExcelSheetParser
{
    public function parse(Worksheet $ws, string $sheetName): array
    {
        $tableBlocks = $this->detectSubTables($ws);
        $result      = [];

        foreach ($tableBlocks as $index => $block) {
            $endRow = isset($tableBlocks[$index + 1])
                ? $tableBlocks[$index + 1]['start_row'] - 1
                : $ws->getHighestRow();

            $parsed             = new ParsedTable();
            $parsed->sheetName  = $sheetName;
            $parsed->tableIndex = $index + 1;
            $parsed->tableTitle = $block['title'];
            $parsed->startRow   = $block['start_row'];
            $parsed->endRow     = $endRow;

            $headerEndRow    = $this->findHeaderEndRow($ws, $block['start_row'], $endRow);
            $parsed->headers = $this->extractHeaders($ws, $block['start_row'] + 1, $headerEndRow);
            $parsed->rows    = $this->extractDataRows($ws, $headerEndRow + 1, $endRow);

            if (!empty($parsed->rows)) {
                $result[] = $parsed;
            }
        }

        return $result;
    }

    /**
     * Normalisasi teks cell: NBSP -> space, rapikan whitespace, trim
     */
    private function normalizeCellText($val): string
    {
        if ($val === null) return '';
        $s = is_string($val) ? $val : (string) $val;
        $s = str_replace("\xC2\xA0", ' ', $s);   // NBSP
        $s = preg_replace('/\s+/u', ' ', $s);    // collapse whitespace
        return trim($s);
    }

    /**
     * Deteksi semua sub-tabel di sheet.
     * Lebih fleksibel: cukup mulai dengan "Tabel" (case-insensitive),
     * boleh ada ":" / "-" / kode elemen setelahnya.
     * Fallback: kalau tidak ada judul tabel, anggap 1 tabel dimulai dari row pertama yang ada data.
     */
    private function detectSubTables(Worksheet $ws): array
    {
        $blocks  = [];
        $maxRow  = $ws->getHighestRow();
        $maxCol  = Coordinate::columnIndexFromString($ws->getHighestColumn());

        $scanCols = min($maxCol, 15);

        for ($row = 1; $row <= $maxRow; $row++) {
            for ($col = 1; $col <= $scanCols; $col++) {
                $val = $ws->getCellByColumnAndRow($col, $row)->getValue();
                if ($val === null || $val === '') continue;

                $text = $this->normalizeCellText($val);
                if ($text === '') continue;

                // fleksibel: asal diawali "Tabel" / "TABEL" / "Tabel:" / "Tabel E.2" dst
                if (preg_match('/^tabel\b[\s:.\-]*/i', $text)) {
                    $blocks[] = [
                        'title'     => $text,
                        'start_row' => $row,
                        'title_col' => $col,
                    ];
                    break; // satu tabel per row
                }
            }
        }

        // Fallback: kalau tidak ada judul "Tabel", tetapi sheet ada konten
        if (empty($blocks)) {
            $firstDataRow = null;
            $checkRowMax  = min($maxRow, 60);

            for ($row = 1; $row <= $checkRowMax; $row++) {
                $hasAny = false;
                for ($col = 1; $col <= min($maxCol, 10); $col++) {
                    $val = $ws->getCellByColumnAndRow($col, $row)->getValue();
                    $text = $this->normalizeCellText($val);
                    if ($text !== '') {
                        $hasAny = true;
                        break;
                    }
                }
                if ($hasAny) {
                    $firstDataRow = $row;
                    break;
                }
            }

            if ($firstDataRow !== null) {
                $blocks[] = [
                    'title'     => 'Tabel (auto-detected)',
                    'start_row' => $firstDataRow,
                    'title_col' => 1,
                ];
            }
        }

        return $blocks;
    }

    private function findHeaderEndRow(Worksheet $ws, int $titleRow, int $maxRow): int
    {
        $searchMax = min($titleRow + 10, $maxRow);

        // Pass 1 — cari number row (1,2,3,...) murni
        for ($row = $titleRow + 1; $row <= $searchMax; $row++) {
            if ($this->isColumnNumberRow($ws, $row)) {
                return $row;
            }
        }

        // Pass 2 — tidak ada number row
        for ($row = $titleRow + 1; $row <= $searchMax; $row++) {
            $firstVal = $this->getFirstNonNullInRow($ws, $row);
            if ($firstVal === null) continue;

            $rowText = $this->getRowAsText($ws, $row);
            if ($this->isInstructionRow(explode(' ', $rowText))) continue;

            // Baris pertama yang diawali 1 (boleh "1" string atau 1 numerik)
            if (is_numeric($firstVal) && (int)$firstVal === 1) {
                return $row - 1;
            }
        }

        return $titleRow + 2;
    }

    private function getRowAsText(Worksheet $ws, int $row): string
    {
        $maxColIndex = Coordinate::columnIndexFromString($ws->getHighestColumn());
        $parts = [];

        for ($col = 1; $col <= min($maxColIndex, 10); $col++) {
            $val = $ws->getCellByColumnAndRow($col, $row)->getValue();
            $text = $this->normalizeCellText($val);
            if ($text !== '') $parts[] = $text;
        }

        return implode(' ', $parts);
    }

    /**
     * Row berisi nomor kolom (1,2,3,...) — penanda akhir header
     * FIX: string numerik "1" dianggap valid (sebelumnya langsung false).
     */
    private function isColumnNumberRow(Worksheet $ws, int $row): bool
    {
        $maxColIndex = Coordinate::columnIndexFromString($ws->getHighestColumn());
        $allValues   = [];

        for ($col = 1; $col <= min($maxColIndex, 20); $col++) {
            $val = $ws->getCellByColumnAndRow($col, $row)->getValue();
            if ($val === null || $val === '') continue;

            // kalau string, coba parse numeric
            if (is_string($val)) {
                $v = $this->normalizeCellText($val);
                if ($v === '' || !is_numeric($v)) return false;
                $val = $v;
            }

            if (!is_numeric($val)) return false;

            // harus integer (bukan float)
            if ((int)$val != $val) return false;
            if ((int)$val <= 0) return false;

            $allValues[] = (int)$val;
        }

        if (count($allValues) < 2) return false;
        if ($allValues[0] !== 1) return false;
        if (max($allValues) > 30) return false;

        for ($i = 1; $i < count($allValues); $i++) {
            if ($allValues[$i] < $allValues[$i - 1]) return false;
        }

        return true;
    }

    private function extractHeaders(Worksheet $ws, int $fromRow, int $toRow): array
    {
        $headers = [];
        $maxColIndex = Coordinate::columnIndexFromString($ws->getHighestColumn());

        for ($row = $fromRow; $row < $toRow; $row++) {
            $rowData = [];
            for ($col = 1; $col <= $maxColIndex; $col++) {
                $val = $ws->getCellByColumnAndRow($col, $row)->getValue();
                $text = $this->normalizeCellText($val);
                $rowData[] = $text !== '' ? $text : null;
            }

            if (array_filter($rowData) !== []) {
                $headers[] = $rowData;
            }
        }

        return $headers;
    }

    private function extractDataRows(Worksheet $ws, int $fromRow, int $toRow): array
    {
        $rows = [];
        $maxColIndex = Coordinate::columnIndexFromString($ws->getHighestColumn());

        for ($row = $fromRow; $row <= $toRow; $row++) {
            $rowData = [];
            $hasData = false;

            for ($col = 1; $col <= $maxColIndex; $col++) {
                $val = $ws->getCellByColumnAndRow($col, $row)->getValue();

                if ($val instanceof \DateTime) {
                    $val = $val->format('d/m/Y');
                } elseif (is_string($val)) {
                    $val = $this->normalizeCellText($val);
                    if ($val === '') $val = null;
                }

                $rowData[] = $val;
                if ($val !== null) $hasData = true;
            }

            if (!$hasData || $this->isInstructionRow($rowData)) {
                continue;
            }

            $rowData = $this->trimTrailingNulls($rowData);

            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    private function trimTrailingNulls(array $row): array
    {
        while (!empty($row) && end($row) === null) {
            array_pop($row);
        }
        return $row;
    }

    private function isInstructionRow(array $row): bool
    {
        $text = implode(' ', array_filter($row, 'is_string'));

        return strlen($text) > 200
            || stripos($text, 'diisi oleh') !== false
            || stripos($text, 'catatan:') !== false
            || stripos($text, 'keterangan:') !== false;
    }

    private function getFirstNonNullInRow(Worksheet $ws, int $row): mixed
    {
        $maxColIndex = Coordinate::columnIndexFromString($ws->getHighestColumn());

        for ($col = 1; $col <= $maxColIndex; $col++) {
            $val = $ws->getCellByColumnAndRow($col, $row)->getValue();
            $text = $this->normalizeCellText($val);
            if ($text !== '') return is_numeric($text) ? (0 + $text) : $text;
        }

        return null;
    }
}
