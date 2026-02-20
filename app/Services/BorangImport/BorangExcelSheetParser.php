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
    /**
     * Entry point: parse satu sheet, return semua tabel di dalamnya
     */
    public function parse(Worksheet $ws, string $sheetName): array
    {
        $tableBlocks = $this->detectSubTables($ws);
        $result      = [];
        $totalBlocks = count($tableBlocks);

        foreach ($tableBlocks as $index => $block) {
            // ✅ Fix: gunakan isset, bukan ??
            $endRow = isset($tableBlocks[$index + 1])
                ? $tableBlocks[$index + 1]['start_row'] - 1
                : $ws->getHighestRow();

            $parsed              = new ParsedTable();
            $parsed->sheetName   = $sheetName;
            $parsed->tableIndex  = $index + 1;
            $parsed->tableTitle  = $block['title'];
            $parsed->startRow    = $block['start_row'];
            $parsed->endRow      = $endRow;

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
     * Deteksi semua sub-tabel di sheet
     * Tabel ditandai dengan cell pertama berisi "Tabel ..."
     */
    private function detectSubTables(Worksheet $ws): array
    {
        $blocks  = [];
        $maxRow  = $ws->getHighestRow();
        $maxCol  = Coordinate::columnIndexFromString($ws->getHighestColumn());

        for ($row = 1; $row <= $maxRow; $row++) {
            // Cek di setiap kolom, tidak hanya col1
            for ($col = 1; $col <= min($maxCol, 5); $col++) {
                $coord = Coordinate::stringFromColumnIndex($col) . $row;
                $val   = $ws->getCell($coord)->getValue();

                if (
                    $val !== null
                    && is_string($val)
                    && preg_match('/^Tabel\s+[\d\.]+/i', trim($val))
                ) {
                    $blocks[] = [
                        'title'      => trim($val),
                        'start_row'  => $row,
                        'title_col'  => $col, // simpan kolom judul untuk hint
                    ];
                    break; // satu tabel per row, lanjut ke row berikutnya
                }
            }
        }

        return $blocks;
    }

    /**
     * Cari row nomor kolom (1, 2, 3, ...) = akhir dari header section
     */
    private function findHeaderEndRow(Worksheet $ws, int $titleRow, int $maxRow): int
    {
        $searchMax = min($titleRow + 10, $maxRow);

        // Pass 1 — cari number row (1, 2, 3, ...) murni
        for ($row = $titleRow + 1; $row <= $searchMax; $row++) {
            if ($this->isColumnNumberRow($ws, $row)) {
                return $row; // number row IKUT di header (dipakai sebagai batas)
            }
        }

        // Pass 2 — tidak ada number row
        // Cari baris pertama yang diawali integer 1 → itu baris data pertama.
        for ($row = $titleRow + 1; $row <= $searchMax; $row++) {
            // Skip baris kosong
            $firstVal = $this->getFirstNonNullInRow($ws, $row);
            if ($firstVal === null) continue;

            // Skip baris instruksi (panjang, mengandung "*", "MOU", dll)
            $rowText = $this->getRowAsText($ws, $row);
            if ($this->isInstructionRow(explode(' ', $rowText))) continue;

            // Baris pertama yang diawali integer 1 = baris data pertama
            if (is_int($firstVal) && $firstVal === 1) {
                return $row - 1; // headerEndRow = satu baris sebelumnya
            }
        }

        // Fallback
        return $titleRow + 2;
    }

    // Helper tambahan untuk Pass 2
    private function getRowAsText(Worksheet $ws, int $row): string
    {
        $maxColIndex = Coordinate::columnIndexFromString($ws->getHighestColumn());
        $parts = [];
        for ($col = 1; $col <= min($maxColIndex, 10); $col++) {
            $coord = Coordinate::stringFromColumnIndex($col) . $row;
            $val   = $ws->getCell($coord)->getValue();
            if (is_string($val) && trim($val) !== '') {
                $parts[] = trim($val);
            }
        }
        return implode(' ', $parts);
    }

    /**
     * Row berisi nomor kolom (1, 2, 3, ...) — penanda akhir header
     */
    private function isColumnNumberRow(Worksheet $ws, int $row): bool
    {
        $maxColIndex = Coordinate::columnIndexFromString($ws->getHighestColumn());
        $allValues   = [];

        for ($col = 1; $col <= min($maxColIndex, 20); $col++) {
            $coord = Coordinate::stringFromColumnIndex($col) . $row;
            $val   = $ws->getCell($coord)->getValue();

            if ($val === null || $val === '') continue;

            // ✅ Satu string → langsung false, tidak perlu lanjut
            if (is_string($val)) return false;

            // Harus integer positif bulat
            if (!is_numeric($val))       return false;
            if ((int)$val != $val)       return false; // reject float seperti 1.5
            if ((int)$val <= 0)          return false; // reject 0 atau negatif

            $allValues[] = (int)$val;
        }

        if (count($allValues) < 2)      return false;
        if ($allValues[0] !== 1)        return false;
        if (max($allValues) > 30)       return false; // nomor kolom tidak mungkin > 30

        // Harus ascending (boleh ada lompatan karena merged cells)
        for ($i = 1; $i < count($allValues); $i++) {
            if ($allValues[$i] < $allValues[$i - 1]) return false;
        }

        return true;
    }

    /**
     * Extract header rows (bisa multi-row, handle merge cell)
     */
    private function extractHeaders(Worksheet $ws, int $fromRow, int $toRow): array
    {
        $headers = [];
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            $ws->getHighestColumn()
        );

        for ($row = $fromRow; $row < $toRow; $row++) {
            $rowData = [];
            for ($col = 1; $col <= $maxColIndex; $col++) {
                $val = $ws->getCell([$col, $row])->getValue();
                $rowData[] = $val !== null ? trim((string)$val) : null;
            }

            // Skip row kosong
            if (array_filter($rowData) !== []) {
                $headers[] = $rowData;
            }
        }

        return $headers;
    }

    /**
     * Extract baris data (setelah header)
     */
    private function extractDataRows(Worksheet $ws, int $fromRow, int $toRow): array
    {
        $rows = [];
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            $ws->getHighestColumn()
        );

        for ($row = $fromRow; $row <= $toRow; $row++) {
            $rowData = [];
            $hasData = false;

            for ($col = 1; $col <= $maxColIndex; $col++) {
                $val = $ws->getCellByColumnAndRow($col, $row)->getValue();

                if ($val instanceof \DateTime) {
                    $val = $val->format('d/m/Y');
                } elseif (is_string($val)) {
                    $val = trim($val);
                    if ($val === '') $val = null;
                }

                $rowData[] = $val;
                if ($val !== null) $hasData = true;
            }

            if (!$hasData || $this->isInstructionRow($rowData)) {
                continue;
            }

            // ✅ Trim trailing nulls dari kanan (ganti getSignificantColCount)
            $rowData = $this->trimTrailingNulls($rowData);

            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    /**
     * Hapus null/kosong dari ujung kanan array
     */
    private function trimTrailingNulls(array $row): array
    {
        // Dari kanan, buang semua null
        while (!empty($row) && end($row) === null) {
            array_pop($row);
        }
        return $row;
    }

    private function isInstructionRow(array $row): bool
    {
        // Gabung semua string dalam row
        $text = implode(' ', array_filter($row, 'is_string'));

        return strlen($text) > 200
            || stripos($text, 'diisi oleh') !== false
            || stripos($text, 'catatan:') !== false
            || stripos($text, 'keterangan:') !== false;
    }

    private function getFirstNonNullInRow(Worksheet $ws, int $row): mixed
    {
        $maxColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            $ws->getHighestColumn()
        );
        for ($col = 1; $col <= $maxColIndex; $col++) {
            $val = $ws->getCell([$col, $row])->getValue();
            if ($val !== null && trim((string)$val) !== '') return $val;
        }
        return null;
    }
}
