<?php

namespace App\Services\BorangExport;

use App\Models\BorangDataExcel;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
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
                $dbTitle = (string)($record['table_title'] ?? '');
                $code = $this->extractTableCode($dbTitle);

                if (!$code || !isset($templateTables[$code])) {
                    // fallback terakhir kalau mau: pakai index
                    Log::warning("Export skip table: sheet={$sheetName}, idx={$tableIndex}, title={$dbTitle}, code={$code}");
                    // if (isset($templateTablesByIndex[$tableIndex])) { ... }
                    continue;
                }

                $meta = $templateTables[$code];
                $rows = $this->filterInputRows($sheetName, $tableIndex, $record['rows'] ?? []);
                $this->fillTable($ws, $meta, $rows);
            }
        }

        // Simpan hasil export ke storage temp
        $filename = 'LKPS_LAMDEPILAR_' . $pengajuan->nomor_pengajuan . '_' . now()->format('Ymd') . '.xlsx';
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
            ->whereIn('status_review', ['raw', 'reviewed', 'approved'])
            ->orderBy('sheet_name')
            ->orderBy('table_index')
            ->get(['sheet_name', 'table_index', 'table_title', 'rows']); // ✅ tambah table_title

        $out = [];
        foreach ($rows as $r) {
            $out[$r->sheet_name][(int)$r->table_index] = [
                'table_title' => $r->table_title, // ✅ simpan juga
                'rows'        => $r->rows ?? [],
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

        for ($r = 1; $r <= $maxRow; $r++) {
            $v = $ws->getCell("A{$r}")->getValue();
            if ($v === null) continue;

            $title = trim(preg_replace('/\s+/', ' ', (string)$v));

            if (preg_match('/^Tabel\s+/i', $title)) {
                $code = $this->extractTableCode($title);
                if (!$code) continue;

                $colNumberRow = $this->findColumnNumberRow($ws, $r + 1, min($r + 10, $maxRow))
                    ?? min($r + 3, $maxRow);

                $colCount = $this->detectColCountFromNumberRow($ws, $colNumberRow);
                $dataStartRow = $colNumberRow + 1;
                $dataEndRow   = $this->findDataEndRow($ws, $dataStartRow, $maxRow);

                $tables[$code] = [
                    'title'        => $title,
                    'titleRow'     => $r,
                    'colNumberRow' => $colNumberRow,
                    'dataStartRow' => $colNumberRow + 1,
                    'dataEndRow'   => $dataEndRow,
                    'colCount'     => $colCount,
                    'sampleRow'    => $colNumberRow + 1,
                ];
            }
        }

        return $tables;
    }

    private function normalizeTitle(string $title): string
    {
        $title = strtolower($title);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);

        return $title;
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
        $startRow  = (int)$meta['dataStartRow'];
        $endRow    = (int)$meta['dataEndRow'];
        $colCount  = (int)$meta['colCount'];
        $sampleRow = (int)$meta['sampleRow'];

        if (empty($rows)) return;

        $rowCount  = count($rows);
        $slotCount = max(0, $endRow - $startRow + 1);

        // ✅ insert row hanya jika data melebihi slot yang tersedia
        if ($rowCount > $slotCount) {
            $need = $rowCount - $slotCount;

            // sisipkan sebelum baris setelah endRow (artinya sebelum "Jumlah")
            $insertAt = $endRow + 1;
            $ws->insertNewRowBefore($insertAt, $need);

            // duplicate style dari sampleRow ke baris baru yang ditambahkan
            $lastColLetter = Coordinate::stringFromColumnIndex($colCount);
            $sourceRange = "A{$sampleRow}:{$lastColLetter}{$sampleRow}";
            $targetRange = "A{$startRow}:{$lastColLetter}" . ($startRow + $rowCount - 1);

            $ws->duplicateStyle($ws->getStyle($sourceRange), $targetRange);

            // update endRow karena bertambah
            $endRow += $need;
        }

        // ✅ tulis data ke slot
        for ($i = 0; $i < $rowCount; $i++) {
            $excelRow = $startRow + $i;
            $rowData  = $rows[$i] ?? [];

            for ($c = 1; $c <= $colCount; $c++) {
                $val = $rowData[$c - 1] ?? null;
                $coord = Coordinate::stringFromColumnIndex($c) . $excelRow;

                if ($val === null) {
                    $ws->setCellValue($coord, null);
                    continue;
                }

                if (is_string($val)) {
                    $trim = trim($val);
                    if ($trim !== '' && str_starts_with($trim, '=')) {
                        $ws->setCellValue($coord, $trim); // formula
                    } else {
                        $ws->setCellValueExplicit($coord, $trim, DataType::TYPE_STRING);
                    }
                } else {
                    $ws->setCellValue($coord, $val);
                }
            }
        }

        // ✅ bersihkan sisa slot yang tidak terpakai (kalau data lebih sedikit dari slot)
        for ($r = $startRow + $rowCount; $r <= $endRow; $r++) {
            for ($c = 1; $c <= $colCount; $c++) {
                $coord = Coordinate::stringFromColumnIndex($c) . $r;
                $ws->setCellValue($coord, null);
            }
        }
    }

    private function extractTableCode(string $title): ?string
    {
        $title = trim((string)$title);

        // contoh: "Tabel E.2.1 Tabel Mahasiswa Penuh Waktu"
        if (preg_match('/^Tabel\s+([A-Z]\.\d+(?:\.\d+)*(?:\.[a-z])?)/i', $title, $m)) {
            return strtolower($m[1]); // "e.2.1"
        }
        return null;
    }

    private function findDataEndRow(Worksheet $ws, int $dataStartRow, int $maxRow): int
    {
        // cari baris yang kolom A = "Jumlah" atau "Rekapitulasi" atau "Keterangan" atau mulai tabel berikutnya
        for ($r = $dataStartRow; $r <= $maxRow; $r++) {
            $a = $ws->getCell("A{$r}")->getValue();
            if (!is_string($a)) continue;

            $t = strtolower(trim($a));

            if ($t === 'jumlah' || $t === 'rekapitulasi' || $t === 'keterangan' || str_starts_with($t, 'tabel ')) {
                return $r - 1; // baris sebelumnya adalah akhir area data
            }
        }

        return $maxRow;
    }
}
