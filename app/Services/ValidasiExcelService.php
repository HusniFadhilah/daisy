<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\ElemenStandar;
use App\Models\JenjangPenilaian;
use App\Models\Kriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as SharedDrawing;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ValidasiExcelService
{
    protected $penilaianName, $penilaianFullName, $mode, $jenisAsesmen;

    /**
     * @param string $jenisAsesmen 'ak' atau 'al'
     * @param string $mode 'split' (2 kolom: Pemenuhan + Pelampauan) atau 'merged' (1 kolom gabungan)
     */
    public function __construct($jenisAsesmen = 'ak', $mode = 'split')
    {
        $this->jenisAsesmen = $jenisAsesmen;
        $this->penilaianName = Asesmen::formatJenisAsesmen($jenisAsesmen);
        $map = [
            'ak' => 'Asesmen Kecukupan',
            'ak_banding' => 'Asesmen Kecukupan Banding',
            'al' => 'Asesmen Lapangan',
            'al_banding' => 'Asesmen Lapangan Banding',
        ];

        $this->penilaianFullName = $map[$jenisAsesmen] ?? null;
        $this->mode = $mode; // 'split' atau 'merged'
    }

    /**
     * Generate Excel file dengan hasil validasi
     *
     * @param Asesmen $asesmen
     * @param Collection $asesors
     * @param string $jenisAsesmen 'ak' atau 'al'
     * @param string $mode 'split' (default) atau 'merged'
     * @return array [tempPath, filename]
     */
    public static function generateTemplate(
        Asesmen $asesmen,
        Collection $asesors,
        string $jenisAsesmen = 'ak',
        string $mode = 'split'
    ): array {
        $service = new self($jenisAsesmen, $mode);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $penilaianName = Asesmen::formatJenisAsesmen($jenisAsesmen);
        $sheet->setTitle('Validasi Penilaian ' . $penilaianName);
        self::addLogoAndZoom($sheet, 60);

        // Set column widths
        $service->setColumnWidths($sheet, $asesors->count());

        // Build headers
        $service->buildHeaders($sheet, $asesmen, $asesors);

        // Build data rows
        $lastRow = $service->renderDataRows($sheet, $asesmen, $asesors);

        // Set print area
        $lastCol = $service->getLastColumn($asesors->count());
        $printAreaLastCol = chr(ord($lastCol) + 1);
        $printAreaLastRow = $lastRow + 1;
        $sheet->getPageSetup()->setPrintArea("A1:{$printAreaLastCol}{$printAreaLastRow}");
        $sheet->getColumnDimension($printAreaLastCol)->setWidth(5);

        // Page setup
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Margins
        $sheet->getPageMargins()->setTop(0);
        $sheet->getPageMargins()->setRight(0);
        $sheet->getPageMargins()->setLeft(0);
        $sheet->getPageMargins()->setBottom(0);
        $sheet->getPageMargins()->setHeader(0);
        $sheet->getPageMargins()->setFooter(0);

        // Save spreadsheet
        $modeText = $mode === 'merged' ? 'Merged' : 'Split';
        $tempPath = $service->saveSpreadsheet(
            $spreadsheet,
            'Validasi_Penilaian_' . Str::slug($penilaianName) . '_Lengkap_',
            Str::slug($asesmen->code) . '_' . date('Ymd')
        );

        $filename = 'Validasi_Penilaian_' . Str::slug($penilaianName) . '_Lengkap_' . Str::slug($asesmen->code) . '_' . date('Ymd') . '.xlsx';

        return [$tempPath, $filename];
    }

    /**
     * Set column widths based on number of asesors and mode
     */
    private function setColumnWidths($sheet, int $asesorCount): void
    {
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);

        // Kolom asesor
        $startCol = 'G';

        if ($this->mode === 'split') {
            // 2 kolom per asesor (Pemenuhan + Pelampauan)
            for ($i = 0; $i < $asesorCount * 2; $i++) {
                $col = chr(ord($startCol) + $i);
                $sheet->getColumnDimension($col)->setWidth(80);
            }
            $validasiStartCol = chr(ord($startCol) + ($asesorCount * 2));
        } else {
            // 1 kolom per asesor (Merged)
            for ($i = 0; $i < $asesorCount; $i++) {
                $col = chr(ord($startCol) + $i);
                $sheet->getColumnDimension($col)->setWidth(80);
            }
            $validasiStartCol = chr(ord($startCol) + $asesorCount);
        }

        // Kolom validasi (3 kolom)
        $sheet->getColumnDimension($validasiStartCol)->setWidth(40); // Status / Kesimpulan
        $sheet->getColumnDimension(chr(ord($validasiStartCol) + 1))->setWidth(80); // Catatan
    }

    /**
     * Build headers
     */
    private function buildHeaders($sheet, Asesmen $asesmen, Collection $asesors): void
    {
        $lastCol = $this->getLastColumn($asesors->count());

        // Title
        $sheet->mergeCells("B2:{$lastCol}2");
        $sheet->setCellValue('B2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("B3:{$lastCol}3");
        $modeText = $this->mode === 'merged' ? ' (Kolom Gabungan)' : ' (Kolom Terpisah)';
        $sheet->setCellValue('B3', 'Tabel Validasi Penilaian ' . ucfirst($this->penilaianFullName) . ' Prodi ' . ($asesmen->studyProgram->name ?? ''));
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header row 5-6
        $sheet->mergeCells('B5:C6');
        $sheet->setCellValue('B5', 'Kriteria');

        $sheet->mergeCells('D5:E6');
        $sheet->setCellValue('D5', 'Kode Elemen');

        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // Penilaian Asesor columns
        $startCol = 'G';
        $colors = ['#e3f2fd', '#fff3e0', '#e8f5e9', '#f3e5f5', '#fce4ec'];

        if ($this->mode === 'split') {
            // MODE SPLIT: 2 kolom per asesor (Pemenuhan + Pelampauan)
            foreach ($asesors as $index => $asesor) {
                $col1 = chr(ord($startCol) + ($index * 2));
                $col2 = chr(ord($startCol) + ($index * 2) + 1);
                $bgColor = $colors[$index % count($colors)];

                $sheet->mergeCells("{$col1}5:{$col2}5");
                $asesorName = $asesor->user->name ?? "Asesor " . ($index + 1);
                $sheet->setCellValue("{$col1}5", "Penilaian Asesor {$asesor->urutan_asesor}\n({$asesorName})");
                $sheet->getStyle("{$col1}5:{$col2}5")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($this->hexToArgb($bgColor));

                $sheet->setCellValue("{$col1}6", JenjangPenilaian::PEMENUHAN_STANDAR);
                $sheet->setCellValue("{$col2}6", JenjangPenilaian::PELAMPAUAN_STANDAR);
                $sheet->getStyle("{$col1}6:{$col2}6")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($this->hexToArgb($bgColor));
            }
            $validasiStartCol = chr(ord($startCol) + ($asesors->count() * 2));
        } else {
            // MODE MERGED: 1 kolom per asesor (gabungan semua skor)
            foreach ($asesors as $index => $asesor) {
                $col = chr(ord($startCol) + $index);
                $bgColor = $colors[$index % count($colors)];

                $sheet->mergeCells("{$col}5:{$col}6");
                $asesorName = $asesor->user->name ?? "Asesor " . ($index + 1);
                $sheet->setCellValue("{$col}5", "Penilaian {$this->penilaianName}\nAsesor {$asesor->urutan_asesor}\n({$asesorName})");
                $sheet->getStyle("{$col}5:{$col}6")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($this->hexToArgb($bgColor));
            }
            $validasiStartCol = chr(ord($startCol) + $asesors->count());
        }

        // Validasi columns
        $validasiEndCol = chr(ord($validasiStartCol) + 1);

        $sheet->mergeCells("{$validasiStartCol}5:{$validasiEndCol}5");
        $sheet->setCellValue("{$validasiStartCol}5", "Hasil Validasi");
        $sheet->getStyle("{$validasiStartCol}5:{$validasiEndCol}5")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F5E9');

        $sheet->setCellValue("{$validasiStartCol}6", 'Status');
        $sheet->setCellValue(chr(ord($validasiStartCol) + 1) . "6", 'Catatan Validator');

        $sheet->getStyle("{$validasiStartCol}6:{$validasiEndCol}6")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F5E9');

        // Styling
        $headerRange = "B5:{$lastCol}6";
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("B5:{$lastCol}6")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9D9D9');

        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Petunjuk
        $sheet->mergeCells("B4:{$lastCol}4");
        // $instructionText = $this->mode === 'merged'
        //     ? 'Hasil Validasi Penilaian Asesor (Semua Kategori dalam 1 Kolom)'
        //     : 'Hasil Validasi Penilaian Asesor (Pemenuhan & Pelampauan Terpisah)';
        // $sheet->setCellValue('B4', $instructionText);
        $sheet->getStyle('B4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF0000FF'], 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);
    }

    /**
     * Render data rows
     */
    private function renderDataRows($sheet, Asesmen $asesmen, Collection $asesors): int
    {
        $currentRow = 7;
        $globalNo = 1;
        $jenisAsesmen = $this->jenisAsesmen;
        $map = [
            'ak' => 'penilaianElemenAk',
            'ak_banding' => 'penilaianElemenAkBanding',
            'al' => 'penilaianElemenAl',
            'al_banding' => 'penilaianElemenAlBanding',
        ];
        $relationName = $map[$jenisAsesmen] ?? null;

        // Load kriteria with penilaian data
        $kriterias = Kriteria::with([
            'elemenStandar' => function ($q) {
                $q->orderBy('kode_elemen');
            },
            "elemenStandar.{$relationName}" => function ($q) use ($asesors, $asesmen) {
                $q->where('id_asesmen', $asesmen->id)
                    ->whereIn('id_asesor', $asesors->pluck('id_user'));
            },
            "elemenStandar.{$relationName}.asesor",
        ])->get();

        foreach ($kriterias as $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();

            if ($elemenCount > 1) {
                $blockStartRow = $currentRow;
                $blockEndRow = $currentRow + $elemenCount - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }

            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $row = $currentRow;

                // Kriteria
                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$row}", $kriteria->kode_kriteria);
                    $this->setFontBlue($sheet, "B{$row}");

                    $sheet->setCellValue("C{$row}", $kriteria->nama_kriteria);
                    $this->setFontBlue($sheet, "C{$row}");
                }

                // Elemen
                $sheet->setCellValue("D{$row}", $globalNo);
                $this->setFontBlue($sheet, "D{$row}");
                $globalNo++;
                $sheet->setCellValue("E{$row}", $elemen->kode_elemen);
                $this->setFontBlue($sheet, "E{$row}");

                $sheet->setCellValue("F{$row}", $elemen->pernyataan_elemen);
                $this->setFontBlue($sheet, "F{$row}");

                // Penilaian asesors
                $startCol = 'G';
                $skors = [];

                if ($this->mode === 'split') {
                    // MODE SPLIT: 2 kolom per asesor
                    foreach ($asesors as $asesorIndex => $asesor) {
                        $col1 = chr(ord($startCol) + ($asesorIndex * 2)); // Pemenuhan
                        $col2 = chr(ord($startCol) + ($asesorIndex * 2) + 1); // Pelampauan

                        $penilaian = $elemen->{$relationName}->where('id_asesor', $asesor->id_user)->first();

                        if ($penilaian && $penilaian->skor !== null) {
                            $skor = (int) $penilaian->skor;
                            $skors[] = $skor;
                            $komentar = $penilaian->komentar ?? '';

                            if ($skor == 4) {
                                // Pelampauan Standar
                                $sheet->setCellValue("{$col2}{$row}", $komentar);
                                $this->applySkorStyle($sheet, "{$col2}{$row}", $skor);
                                // Kosongkan Pemenuhan
                                $sheet->getStyle("{$col1}{$row}")->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('FFE0E0E0');
                            } else {
                                // Pemenuhan Standar (skor 0-3)
                                $sheet->setCellValue("{$col1}{$row}", $komentar);
                                $this->applySkorStyle($sheet, "{$col1}{$row}", $skor);
                                // Kosongkan Pelampauan
                                $sheet->getStyle("{$col2}{$row}")->getFill()
                                    ->setFillType(Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('FFE0E0E0');
                            }
                        } else {
                            // Belum dinilai
                            $sheet->getStyle("{$col1}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFE0E0E0');
                            $sheet->getStyle("{$col2}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFE0E0E0');
                        }
                    }
                    $validasiStartCol = chr(ord($startCol) + ($asesors->count() * 2));
                } else {
                    // MODE MERGED: 1 kolom per asesor (gabungan semua skor)
                    foreach ($asesors as $asesorIndex => $asesor) {
                        $col = chr(ord($startCol) + $asesorIndex);

                        $penilaian = $elemen->{$relationName}->where('id_asesor', $asesor->id_user)->first();

                        if ($penilaian && $penilaian->skor !== null) {
                            $skor = (int) $penilaian->skor;
                            $skors[] = $skor;
                            $komentar = $penilaian->komentar ?? '';

                            $sheet->setCellValue("{$col}{$row}", $komentar);
                            $this->applySkorStyle($sheet, "{$col}{$row}", $skor);
                        } else {
                            // Belum dinilai
                            $sheet->getStyle("{$col}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFE0E0E0');
                        }
                    }
                    $validasiStartCol = chr(ord($startCol) + $asesors->count());
                }

                // Validasi data
                $validasi = $elemen->{$relationName}
                    ->where('id_asesmen', $asesmen->id)
                    ->whereNotIn('status_validasi', ['pending', 'not_validated'])
                    ->first();

                // ✅ hitung status dari skor para asesor
                $statusText = $this->getStatusText($skors, $asesors->count());
                $sheet->setCellValue("{$validasiStartCol}{$row}", $statusText);

                // Catatan
                $catatanCol = chr(ord($validasiStartCol) + 1);
                $sheet->setCellValue("{$catatanCol}{$row}", $validasi->catatan_validator ?? '-');

                // (opsional) styling status berdasarkan status_validasi tetap boleh dipakai
                if ($validasi) {
                    $this->applyValidasiStatusStyle($sheet, "{$validasiStartCol}{$row}", $validasi->status_validasi);
                } else {
                    // kalau belum ada validasi, kasih abu-abu saja
                    $sheet->getStyle("{$validasiStartCol}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFE0E0E0');
                }

                // Apply row styling
                $lastCol = $this->getLastColumn($asesors->count());
                $this->applyRowStyling($sheet, $row, 'B', $lastCol);

                $currentRow++;
                $isFirstElemen = false;
            }
        }

        return $currentRow - 1;
    }

    /**
     * Apply skor styling
     */
    private function applySkorStyle($sheet, string $cell, int $skor): void
    {
        $bgColor = $this->getSkorColor($skor);
        $textColor = $this->getTextColorByBg($bgColor);

        $sheet->getStyle($cell)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($this->hexToArgb($bgColor));

        // $sheet->getStyle($cell)->getFont()
        //     ->setBold(true)
        //     ->getColor()->setARGB($this->hexToArgb($textColor));
    }

    /**
     * Apply validasi status styling
     */
    private function applyValidasiStatusStyle($sheet, string $cell, string $status): void
    {
        $styles = [
            'validated' => ['bg' => '#e8f5e9', 'text' => '#000000'],
            'approved' => ['bg' => '#c8e6c9', 'text' => '#000000'],
            'revision_required' => ['bg' => '#fff3e0', 'text' => '#000000'],
        ];

        $style = $styles[$status] ?? ['bg' => '#e0e0e0', 'text' => '#000000'];

        $sheet->getStyle($cell)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($this->hexToArgb($style['bg']));

        $sheet->getStyle($cell)->getFont()
            ->setBold(true)
            ->getColor()->setARGB($this->hexToArgb($style['text']));
    }

    private function setFontBlue($sheet, string $cell): void
    {
        $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FF1F4E79');
    }

    /**
     * Get status text
     */
    private function getStatusText(array $skors, int $asesorCount): string
    {
        // buang null, pastikan integer
        $skors = array_values(array_filter($skors, fn($v) => $v !== null));
        $skors = array_map('intval', $skors);

        if (count($skors) === 0) {
            return 'Belum Dinilai';
        }

        // Kalau belum semua asesor memberi skor
        if (count($skors) < $asesorCount) {
            return 'Belum Semua Asesor Banding Menilai';
        }

        $freq = array_count_values($skors);
        $uniqueCount = count($freq);

        // semua sama
        if ($uniqueCount === 1) {
            return 'Masing-masing asesor banding telah memiliki pandangan yang sama';
        }

        // semua berbeda
        if ($uniqueCount === $asesorCount) {
            return 'Setiap Asesor banding memiliki pandangan yang berbeda';
        }

        // ada mayoritas, hitung berapa asesor yang "beda dari mayoritas"
        $maxFreq = max($freq);
        $diffCount = $asesorCount - $maxFreq;

        if ($diffCount === 1) {
            return '1 Asesor banding memiliki pandangan yang berbeda';
        }

        if ($diffCount === 2) {
            return '2 Asesor banding memiliki pandangan yang berbeda';
        }

        // fallback kalau asesornya lebih banyak
        return "{$diffCount} Asesor banding memiliki pandangan yang berbeda";
    }

    /**
     * Apply row styling
     */
    private function applyRowStyling($sheet, int $row, string $startCol, string $endCol): void
    {
        $range = "{$startCol}{$row}:{$endCol}{$row}";

        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle($range)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->getRowDimension($row)->setRowHeight(-1);
    }

    /**
     * Merge block
     */
    private function mergeBlock($sheet, string $blockStart, string $blockEnd, int $rowStart, int $rowEnd): void
    {
        if ($rowStart <= $rowEnd) {
            $sheet->mergeCells("$blockStart{$rowStart}:$blockEnd{$rowEnd}");
        }
    }

    /**
     * Get last column based on asesor count and mode
     */
    private function getLastColumn(int $asesorCount): string
    {
        if ($this->mode === 'split') {
            // B-F (5 fixed cols) + (asesorCount * 2) + 1 (validasi 2 kolom)
            $offset = 5 + ($asesorCount * 2) + 1;
        } else {
            // B-F (5 fixed cols) + asesorCount + 1 (validasi 2 kolom)
            $offset = 5 + $asesorCount + 1;
        }

        return chr(ord('B') + $offset);
    }

    /**
     * Save spreadsheet
     */
    private function saveSpreadsheet(Spreadsheet $spreadsheet, string $prefix, $code = null): string
    {
        // 1) sanitize code agar tidak jadi folder (LAMDEPILAR/2026/001 -> LAMDEPILAR-2026-001)
        $safeCode = null;
        if (!empty($code)) {
            $safeCode = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', (string) $code);
            $safeCode = preg_replace('/-+/', '-', $safeCode);
            $safeCode = trim($safeCode, '-');
        }

        // 2) pastikan prefix juga aman (jaga-jaga)
        $safePrefix = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '-', $prefix);

        // 3) bentuk filename
        $date = date('Ymd');
        $filename = $safeCode
            ? "{$safePrefix}{$safeCode}_{$date}.xlsx"
            : "{$safePrefix}.xlsx";

        // 4) base temp dir
        $baseDir = storage_path('app/temp');
        $tempPath = $baseDir . DIRECTORY_SEPARATOR . $filename;

        // 5) pastikan foldernya ada (recursive)
        $dir = dirname($tempPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // 6) save
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Helper methods
     */
    private function hexToArgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        return 'FF' . strtoupper($hex);
    }

    private function getSkorColor(int $skor): string
    {
        return \App\Models\JenjangPenilaian::getSkorColor($skor);
    }

    private function getTextColorByBg(string $hex): string
    {
        return \App\Models\JenjangPenilaian::textColorByBg($hex);
    }

    public static function addLogoAndZoom(
        $sheet,
        int $zoomScale = 60,
        string $coordinates = 'B2',
        ?string $endCell = null,
        int $logoHeight = 40
    ): void {
        $sheet->getSheetView()->setZoomScale($zoomScale);

        $path = public_path('assets/images/logo.png');
        if (!file_exists($path)) {
            return;
        }

        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath($path);

        $drawing->setResizeProportional(true);
        $drawing->setHeight($logoHeight);

        if ($endCell !== null) {
            [$colStart, $rowStart] = Coordinate::coordinateFromString($coordinates);
            [$colEnd,   $rowEnd]   = Coordinate::coordinateFromString($endCell);

            $startColIdx = Coordinate::columnIndexFromString($colStart);
            $endColIdx   = Coordinate::columnIndexFromString($colEnd);

            // default font (wajib untuk konversi width excel -> px)
            $defaultFont = $sheet->getParent()->getDefaultStyle()->getFont();

            // Total width area (px) - pakai width final kolom (fallback ke default)
            $totalWidthPx = 0;
            for ($col = $startColIdx; $col <= $endColIdx; $col++) {
                $letter = Coordinate::stringFromColumnIndex($col);

                $w = $sheet->getColumnDimension($letter)->getWidth();
                if ($w <= 0) {
                    $w = $sheet->getDefaultColumnDimension()->getWidth();
                }

                $totalWidthPx += SharedDrawing::cellDimensionToPixels($w, $defaultFont);
            }

            // Total height area (px) - pakai height final row (fallback ke default)
            $totalHeightPx = 0;
            for ($row = $rowStart; $row <= $rowEnd; $row++) {
                $h = $sheet->getRowDimension($row)->getRowHeight();
                if ($h <= 0) {
                    $h = $sheet->getDefaultRowDimension()->getRowHeight();
                    if ($h <= 0) $h = 15;
                }

                $totalHeightPx += SharedDrawing::pointsToPixels($h);
            }

            // Ukuran logo (px) dari file asli + target height $logoHeightpx
            $targetHeightPx = $logoHeight;
            $logoWidth = 120;
            $logoHeight = $logoHeight;

            $imgSize = @getimagesize($path);
            if ($imgSize !== false) {
                [$imgW, $imgH] = $imgSize;
                if ($imgH > 0) {
                    $scale = $targetHeightPx / $imgH;
                    $logoWidth  = (int) round($imgW * $scale);
                    $logoHeight = (int) round($imgH * $scale);
                }
            }

            // Anchor di startCell, offset ke tengah area
            $drawing->setCoordinates($coordinates);
            $drawing->setOffsetX((int) round(($totalWidthPx  - $logoWidth)  / 2));
            $drawing->setOffsetY((int) round(($totalHeightPx - $logoHeight) / 2));

            // Biar behave seperti "di-merge area" (bergerak & ikut ukuran cell)
            $drawing->setEditAs(\PhpOffice\PhpSpreadsheet\Worksheet\Drawing::EDIT_AS_ONECELL);
        } else {
            // JANGAN DIUBAH (sesuai request)
            $drawing->setCoordinates($coordinates);
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(2);
        }

        $drawing->setWorksheet($sheet);
    }
}
