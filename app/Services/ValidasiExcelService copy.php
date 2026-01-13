<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\Kriteria;
use Illuminate\Support\Str;
use App\Models\ElemenStandar;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ValidasiExcelService
{
    protected $penilaianName, $penilaianFullName, $mode;

    /**
     * @param string $jenisAsesmen 'ak' atau 'al'
     * @param string $mode 'split' (2 kolom: Pemenuhan + Pelampauan) atau 'merged' (1 kolom gabungan)
     */
    public function __construct($jenisAsesmen = 'ak', $mode = 'split')
    {
        $this->penilaianName = strtoupper($jenisAsesmen);
        $this->penilaianFullName = $this->penilaianName == 'AL' ? 'Asesmen Lapangan' : 'Asesmen Kecukupan';
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
        $sheet->setTitle('Validasi Penilaian ' . strtoupper($jenisAsesmen));
        $sheet->getSheetView()->setZoomScale(60);

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
            'Validasi_Penilaian_' . strtoupper($jenisAsesmen) . '_Lengkap_',
            $asesmen->code . '_' . date('Ymd')
        );

        $filename = 'Validasi_Penilaian_' . strtoupper($jenisAsesmen) . '_Lengkap_' . $asesmen->code . '_' . date('Ymd') . '.xlsx';

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
        $sheet->getColumnDimension($validasiStartCol)->setWidth(20); // Status
        $sheet->getColumnDimension(chr(ord($validasiStartCol) + 1))->setWidth(15); // Skor Final
        $sheet->getColumnDimension(chr(ord($validasiStartCol) + 2))->setWidth(80); // Catatan
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
        $sheet->setCellValue('B3', 'Tabel Validasi Penilaian ' . $this->penilaianName . ' - ' . $asesmen->name . $modeText);
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

                $sheet->setCellValue("{$col1}6", 'Pemenuhan Standar');
                $sheet->setCellValue("{$col2}6", 'Pelampauan Standar');
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
        $validasiEndCol = chr(ord($validasiStartCol) + 2);

        $sheet->mergeCells("{$validasiStartCol}5:{$validasiEndCol}5");
        $sheet->setCellValue("{$validasiStartCol}5", "Hasil Validasi");
        $sheet->getStyle("{$validasiStartCol}5:{$validasiEndCol}5")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE8F5E9');

        $sheet->setCellValue("{$validasiStartCol}6", 'Status');
        $sheet->setCellValue(chr(ord($validasiStartCol) + 1) . "6", 'Kategori Final');
        $sheet->setCellValue(chr(ord($validasiStartCol) + 2) . "6", 'Catatan Validator');

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

        $sheet->getStyle('B5:F6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFFFFF');

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
        $jenisAsesmen = strtolower($this->penilaianName);
        $relationName = $jenisAsesmen == 'al' ? 'penilaianElemenAl' : 'penilaianElemenAk';

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
                    $sheet->setCellValue("C{$row}", $kriteria->nama_kriteria);
                }

                // Elemen
                $sheet->setCellValue("D{$row}", $index + 1);
                $sheet->setCellValue("E{$row}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$row}", $elemen->pernyataan_elemen);

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
                    ->where('status_validasi', '!=', 'not_validated')
                    ->first();

                if ($validasi) {
                    // Status
                    $status = $this->getStatusText($validasi->status_validasi);
                    $sheet->setCellValue("{$validasiStartCol}{$row}", $status);
                    $this->applyValidasiStatusStyle($sheet, "{$validasiStartCol}{$row}", $validasi->status_validasi);

                    // Skor Final
                    $skorFinalCol = chr(ord($validasiStartCol) + 1);
                    if ($validasi->skor_final !== null) {
                        $sheet->setCellValue("{$skorFinalCol}{$row}", $validasi->skor_final);
                        $this->applySkorStyle($sheet, "{$skorFinalCol}{$row}", $validasi->skor_final);
                    } else {
                        $sheet->setCellValue("{$skorFinalCol}{$row}", '-');
                    }

                    // Catatan
                    $catatanCol = chr(ord($validasiStartCol) + 2);
                    $sheet->setCellValue("{$catatanCol}{$row}", $validasi->catatan_validator ?? '-');
                } else {
                    // Check for differences
                    $hasDifference = count(array_unique($skors)) > 1;
                    $statusText = $hasDifference ? 'Terdapat Perbedaan' : 'Belum Divalidasi';

                    $sheet->setCellValue("{$validasiStartCol}{$row}", $statusText);
                    $bgColor = $hasDifference ? 'FFFFE0B2' : 'FFE0E0E0';
                    $sheet->getStyle("{$validasiStartCol}{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($bgColor);

                    $sheet->getStyle("{$validasiStartCol}{$row}")->getFont()->setBold(true);

                    // Empty skor final and catatan
                    $skorFinalCol = chr(ord($validasiStartCol) + 1);
                    $catatanCol = chr(ord($validasiStartCol) + 2);
                    $sheet->setCellValue("{$skorFinalCol}{$row}", '-');
                    $sheet->setCellValue("{$catatanCol}{$row}", '-');
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

        $sheet->getStyle($cell)->getFont()
            ->setBold(true)
            ->getColor()->setARGB($this->hexToArgb($textColor));
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

    /**
     * Get status text
     */
    private function getStatusText(string $status): string
    {
        return match ($status) {
            'validated' => 'Disetujui',
            'validated_diff' => 'Disetujui dengan perbedaan nilai',
            'approved' => 'Disetujui',
            'revision_required' => 'Perlu Revisi',
            default => 'Belum Divalidasi'
        };
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
            // B-F (5 fixed cols) + (asesorCount * 2) + 2 (validasi cols, zero-indexed offset)
            $offset = 5 + ($asesorCount * 2) + 2;
        } else {
            // B-F (5 fixed cols) + asesorCount + 2 (validasi cols)
            $offset = 5 + $asesorCount + 2;
        }

        return chr(ord('B') + $offset);
    }

    /**
     * Save spreadsheet
     */
    private function saveSpreadsheet(Spreadsheet $spreadsheet, string $prefix, string $code): string
    {
        $filename = $prefix . $code . '.xlsx';
        $tempDir = storage_path('app/temp');
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

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
}
