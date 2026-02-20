<?php

namespace App\Services\BorangExport;

use App\Models\BorangDataExcel;
use App\Models\PengajuanAkreditasi;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LkpsTemplateExcelExportService
{
    // Sheet non-data yang boleh di-skip (opsional)
    private const SKIP_SHEETS = ['Daftar Isi', 'Petunjuk', 'Cover'];

    public function exportFromTemplate(PengajuanAkreditasi $pengajuan): string
    {
        $templatePath = public_path('assets/excel/Template LKPS Akreditasi LAMDEPILAR..xlsx');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template tidak ditemukan: {$templatePath}");
        }

        // Load template (jangan setReadDataOnly(true) karena kita butuh style template)
        /** @var Spreadsheet $spreadsheet */
        $spreadsheet = IOFactory::load($templatePath);

        // Ambil data dari DB, kelompokkan per sheet & table_index
        $dataBySheet = $this->loadData($pengajuan->id);

        foreach ($dataBySheet as $sheetName => $tablesByIndex) {
            if (in_array($sheetName, self::SKIP_SHEETS, true)) {
                continue;
            }

            $ws = $spreadsheet->getSheetByName($sheetName);
            if (!$ws) {
                // Kalau template tidak punya sheet tsb, skip saja (atau throw kalau mau strict)
                continue;
            }

            // Deteksi posisi semua tabel pada sheet template: tableIndex -> meta
            $templateTables = $this->detectTemplateTables($ws);

            foreach ($tablesByIndex as $tableIndex => $record) {
                if (!isset($templateTables[$tableIndex])) {
                    // Jika urutan tabel di template tidak match, kamu bisa fallback cari by judul.
                    // Untuk sekarang: skip.
                    continue;
                }

                $meta = $templateTables[$tableIndex];
                $rows = $this->filterInputRows($sheetName, $tableIndex, $record['rows'] ?? []);
                $this->fillTable($ws, $meta, $rows);
            }
        }

        // Simpan hasil export ke storage temp
        $filename = 'LKPS_LAMDEPILAR_' . $pengajuan->nomor_pengajuan . '_' . now()->format('Ymd_His') . '.xlsx';
        $path = storage_path('app/temp/exports/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function loadData(int $pengajuanId): array
    {
        $rows = BorangDataExcel::query()
            ->where('id_pengajuan', $pengajuanId)
            ->whereIn('status_review', ['raw', 'reviewed', 'approved']) // sesuaikan kebijakanmu
            ->orderBy('sheet_name')
            ->orderBy('table_index')
            ->get(['sheet_name', 'table_index', 'rows']);

        $out = [];
        foreach ($rows as $r) {
            $out[$r->sheet_name][(int)$r->table_index] = [
                'rows' => $r->rows ?? [],
            ];
        }
        return $out;
    }

    /**
     * Deteksi tabel pada sheet template:
     * - tabel dimulai dari baris yang kolom A berisi "Tabel ..."
     * - header selesai di baris yang berisi nomor kolom (1,2,3,...)
     * - data mulai setelah baris nomor kolom
     *
     * Return:
     * [
     *   1 => ['titleRow'=>.., 'colNumberRow'=>.., 'dataStartRow'=>.., 'colCount'=>.., 'sampleRow'=>..],
     *   2 => ...
     * ]
     */
    private function detectTemplateTables(Worksheet $ws): array
    {
        $maxRow = $ws->getHighestRow();
        $tables = [];
        $tableIdx = 0;

        for ($r = 1; $r <= $maxRow; $r++) {
            // biasanya judul tabel ada di kolom A
            $v = $ws->getCell("A{$r}")->getValue();
            if (is_string($v) && preg_match('/^Tabel\s+/i', trim($v))) {
                $tableIdx++;

                // cari baris nomor kolom dalam 6 baris setelah title
                $colNumberRow = $this->findColumnNumberRow($ws, $r + 1, min($r + 10, $maxRow));

                // fallback kalau tidak ketemu: anggap header 2 baris + 1 baris nomor
                if ($colNumberRow === null) {
                    $colNumberRow = min($r + 3, $maxRow);
                }

                $colCount = $this->detectColCountFromNumberRow($ws, $colNumberRow);

                $tables[$tableIdx] = [
                    'titleRow'      => $r,
                    'colNumberRow'  => $colNumberRow,
                    'dataStartRow'  => $colNumberRow + 1,
                    'colCount'      => $colCount,
                    // baris contoh pertama di template untuk dijadikan sumber style
                    'sampleRow'     => $colNumberRow + 1,
                ];
            }
        }

        return $tables;
    }

    private function findColumnNumberRow(Worksheet $ws, int $from, int $to): ?int
    {
        for ($r = $from; $r <= $to; $r++) {
            if ($this->isColumnNumberRow($ws, $r)) {
                return $r;
            }
        }
        return null;
    }

    /**
     * Anggap "baris nomor kolom" jika di 15 kolom pertama ada angka 1 di awal.
     */
    private function isColumnNumberRow(Worksheet $ws, int $row): bool
    {
        $vals = [];
        for ($col = 1; $col <= 15; $col++) {
            $v = $ws->getCellByColumnAndRow($col, $row)->getValue();
            if ($v !== null && $v !== '') $vals[] = $v;
        }
        if (count($vals) < 2) return false;

        $firstNumeric = null;
        foreach ($vals as $v) {
            if (is_numeric($v)) {
                $firstNumeric = (int)$v;
                break;
            }
        }
        return $firstNumeric === 1;
    }

    /**
     * Deteksi jumlah kolom tabel dari baris nomor kolom:
     * ambil angka terbesar berurutan dari 1..N (atau hitung sel terisi).
     */
    private function detectColCountFromNumberRow(Worksheet $ws, int $row): int
    {
        $max = 0;
        for ($col = 1; $col <= 200; $col++) { // batasi biar tidak liar
            $v = $ws->getCellByColumnAndRow($col, $row)->getValue();
            if ($v === null || $v === '') break;
            if (is_numeric($v)) $max = max($max, (int)$v);
        }
        return $max > 0 ? $max : 15;
    }

    private function filterInputRows(string $sheetName, int $tableIndex, array $rows): array
    {
        // contoh khusus E.2 tabel 1 (sesuaikan rule per tabel bila perlu)
        if ($sheetName === 'E.2' && $tableIndex === 1) {
            return array_values(array_filter($rows, function ($r) {
                $a = isset($r[0]) ? trim((string)$r[0]) : '';
                if ($a === '') return false;

                // ambil TS-x atau TS
                if (preg_match('/^(TS-\d+|TS)$/i', $a)) return true;

                // atau tahun akademik/angkatan (optional)
                // if (preg_match('/^\d{4}\//', $a)) return true;

                return false; // skip "Jumlah", "Rekapitulasi", "Keterangan", dst
            }));
        }

        return $rows;
    }

    /**
     * Isi data ke area tabel:
     * - mulai dari dataStartRow, kolom 1..colCount
     * - jika data lebih banyak dari baris contoh: insert row + duplicate style dari sampleRow
     * - set value dengan aman (formula jika string diawali '=')
     */
    private function fillTable(Worksheet $ws, array $meta, array $rows): void
    {
        $startRow = (int)$meta['dataStartRow'];
        $colCount = (int)$meta['colCount'];
        $sampleRow = (int)$meta['sampleRow'];

        if (empty($rows)) {
            // Tidak ada data, biarkan template apa adanya
            return;
        }

        $rowCount = count($rows);

        // 1) Pastikan ada cukup row di template.
        // Asumsi template minimal punya 1 sample row (sampleRow).
        // Jika data > 1, insert row sebelum baris setelah sampleRow.
        if ($rowCount > 1) {
            $insertAt = $sampleRow + 1;
            $toInsert = $rowCount - 1;
            $ws->insertNewRowBefore($insertAt, $toInsert);

            // Duplicate style dari sampleRow ke row-row baru
            $lastColLetter = Coordinate::stringFromColumnIndex($colCount);
            $sourceRange = "A{$sampleRow}:{$lastColLetter}{$sampleRow}";
            $targetRange = "A{$sampleRow}:{$lastColLetter}" . ($sampleRow + $toInsert);

            $ws->duplicateStyle($ws->getStyle($sourceRange), $targetRange);

            // Duplicate row height juga (opsional)
            $h = $ws->getRowDimension($sampleRow)->getRowHeight();
            for ($r = $sampleRow; $r <= $sampleRow + $toInsert; $r++) {
                $ws->getRowDimension($r)->setRowHeight($h);
            }
        }

        // 2) Tulis nilai
        for ($i = 0; $i < $rowCount; $i++) {
            $excelRow = $startRow + $i;
            $rowData = $rows[$i] ?? [];

            for ($c = 1; $c <= $colCount; $c++) {
                $val = $rowData[$c - 1] ?? null;
                $coord = Coordinate::stringFromColumnIndex($c) . $excelRow;

                if ($val === null) {
                    $ws->setCellValue($coord, null);
                    continue;
                }

                // Jika string formula, set sebagai formula
                if (is_string($val)) {
                    $trim = trim($val);
                    if ($trim !== '' && str_starts_with($trim, '=')) {
                        $ws->setCellValue($coord, $trim); // formula
                    } else {
                        $ws->setCellValueExplicit($coord, $trim, DataType::TYPE_STRING);
                    }
                    continue;
                }

                // numeric/bool/dll
                $ws->setCellValue($coord, $val);
            }
        }
    }
}
