<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\ElemenStandar;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class PenilaianExcelService
{
    protected $modelPenilaianElemen;

    public function __construct($modelPenilaianElemen)
    {
        $this->modelPenilaianElemen = $modelPenilaianElemen;
    }
    /**
     * Generate template Excel file (format kosong)
     */
    public function generateTemplate(Asesmen $asesmen): string
    {
        [$spreadsheet, $sheet] = $this->createSheetBase($asesmen);

        // mode = template (tanpa data penilaian)
        $this->renderElemenRows($sheet, $asesmen, null);

        return $this->saveSpreadsheet($spreadsheet, 'Template_Penilaian_AK');
    }

    /**
     * Generate Excel file dengan hasil penilaian
     */
    public function generateWithData(Asesmen $asesmen, $userId): string
    {
        [$spreadsheet, $sheet] = $this->createSheetBase($asesmen);

        // mode = withData (isi komentar di kolom sesuai skor)
        $this->renderElemenRows($sheet, $asesmen, (int) $userId);

        return $this->saveSpreadsheet($spreadsheet, 'Penilaian_AK_', $asesmen->code);
    }

    /**
     * Buat spreadsheet + sheet, set title, column widths, dan header.
     * Mengembalikan array: [Spreadsheet $spreadsheet, Worksheet $sheet]
     */
    private function createSheetBase(Asesmen $asesmen): array
    {
        $spreadsheet = new Spreadsheet();
        // Sheet 0 → MENU
        $this->buildMenuSheet($spreadsheet, $asesmen);

        // Sheet 1 → Kertas Kerja
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Kertas Kerja AK Asesor');
        $spreadsheet->setActiveSheetIndex(1);
        $sheet->getSheetView()->setZoomScale(60);

        $this->setColumnWidths($sheet);
        $this->buildHeaders($sheet, $asesmen);

        $this->buildPenilaianAKSheet($spreadsheet, $asesmen);
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('lamdepilar'); // opsional
        $sheet->getProtection()->setSort(true);
        $sheet->getProtection()->setInsertRows(true);
        $sheet->getProtection()->setFormatCells(true);

        return [$spreadsheet, $sheet];
    }

    /**
     * Set column widths (dipakai dua mode)
     */
    private function setColumnWidths($sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(37);
        $sheet->getColumnDimension('J')->setWidth(37);
        $sheet->getColumnDimension('K')->setWidth(37);
        $sheet->getColumnDimension('L')->setWidth(37);
        $sheet->getColumnDimension('M')->setWidth(70);
    }

    /**
     * Render baris data (2 baris per elemen).
     * Jika $userId null => template mode
     * Jika $userId ada => withData mode
     */
    private function renderElemenRows($sheet, Asesmen $asesmen, ?int $userId): void
    {
        $currentRow = 8;
        $sheet->getStyle($sheet->calculateWorksheetDimension())
            ->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED);

        $kriterias = Kriteria::with([
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
        ])->get();

        foreach ($kriterias as $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();
            if ($elemenCount > 1) {
                $blockStartRow = $currentRow;
                $blockEndRow   = $currentRow + ($elemenCount * 2) - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }
            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $templateRow = $currentRow;
                $asesorRow   = $currentRow + 1;

                // ========== 1) ROW TEMPLATE: I–M berisi instruksi ==========
                $penilaianMap = $this->getPenilaianMapBySkor($elemen);

                // NOTE: file Anda pakai skor 0–4 (I..M). Jika sistem Anda hanya 0–3, silakan sesuaikan.
                $sheet->setCellValue("I{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[0] ?? null, 0));
                $sheet->setCellValue("J{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[1] ?? null, 1));
                $sheet->setCellValue("K{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[2] ?? null, 2));
                $sheet->setCellValue("L{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[3] ?? null, 3));
                $sheet->setCellValue("M{$templateRow}", $this->buildPenilaianInstruction($penilaianMap[4] ?? null, 4));

                // ========== 2) Isi identitas kriteria (hanya sekali per grup kriteria) ==========
                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$templateRow}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$templateRow}", $kriteria->nama_kriteria);
                }

                // ========== 3) Isi elemen + indikator ==========
                $indikatorKualitatif = $elemen->indikator
                    ->filter(fn($ind) => $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kualitatif') !== false)
                    ->pluck('deskripsi_indikator')
                    ->implode("\n\n");

                $indikatorKuantitatif = $elemen->indikator
                    ->filter(fn($ind) => $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kuantitatif') !== false)
                    ->pluck('deskripsi_indikator')
                    ->implode("\n\n");

                $sheet->setCellValue("D{$templateRow}", $index + 1);
                $sheet->setCellValue("E{$templateRow}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$templateRow}", $elemen->pernyataan_elemen);
                $sheet->setCellValue("G{$templateRow}", $indikatorKualitatif ?: 'Tidak ada');
                $sheet->setCellValue("H{$templateRow}", $indikatorKuantitatif ?: 'Tidak ada');

                // ========== 4) Baris asesor: kosongkan I–M ==========
                foreach (['I', 'J', 'K', 'L', 'M'] as $col) {
                    $sheet->setCellValue("{$col}{$asesorRow}", '');
                    $sheet->getStyle("{$col}{$asesorRow}")
                        ->getProtection()
                        ->setLocked(Protection::PROTECTION_UNPROTECTED);
                }

                // ===== Data Validation: hanya boleh 1 kolom terisi =====
                foreach (range('I', 'M') as $col) {
                    $cell = $sheet->getCell("{$col}{$asesorRow}");
                    $validation = $cell->getDataValidation();
                    $validation->setType(DataValidation::TYPE_CUSTOM);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Input Salah');
                    $validation->setError('Hanya boleh mengisi satu kolom per baris I-M ini.');
                    $validation->setFormula1("=COUNTA(I{$asesorRow}:M{$asesorRow})<=1");
                }

                // ========== 5) Jika withData mode, isi komentar pada kolom sesuai skor ==========
                if ($userId !== null) {
                    $this->applyPenilaianDataToAsesorRow($sheet, $asesmen, $userId, $elemen, $asesorRow);
                }

                // ========== 6) Merge B–H antar 2 baris (rowspan effect) ==========
                $this->mergeTwoRowBlock($sheet, $templateRow, $asesorRow);

                // align B–H gabungan 2 baris
                $sheet->getStyle("B{$templateRow}:H{$asesorRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // ========== 7) Border & wrap untuk kedua baris ==========
                $this->applyRowStyling($sheet, $templateRow, 200);
                $this->applyRowStyling($sheet, $asesorRow);

                // ========== 8) Style baris asesor (kuning + tinggi) ==========
                $this->applyAsesorRowStyle($sheet, $asesorRow);

                $currentRow += 2;
                $isFirstElemen = false;
            }
        }
    }

    /**
     * Isi data penilaian (komentar) ke baris asesor sesuai skor (I..M).
     */
    private function applyPenilaianDataToAsesorRow($sheet, Asesmen $asesmen, int $userId, ElemenStandar $elemen, int $asesorRow): void
    {
        $modelPenilaianElemen = $this->modelPenilaianElemen;
        $penilaian = $modelPenilaianElemen::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $userId)
            ->where('id_elemen', $elemen->id)
            ->first();

        if (!$penilaian || $penilaian->skor === null) {
            return;
        }

        // I=0, J=1, K=2, L=3, M=4
        $scoreColumn = chr(73 + (int) $penilaian->skor);
        if (!in_array($scoreColumn, ['I', 'J', 'K', 'L', 'M'], true)) {
            return; // safety
        }

        $sheet->setCellValue("{$scoreColumn}{$asesorRow}", $penilaian->komentar);

        // highlight cell yang terisi
        $sheet->getStyle("{$scoreColumn}{$asesorRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFEB3B');
    }

    /**
     * Merge cells B–H untuk 2 baris (templateRow + asesorRow)
     */
    private function mergeTwoRowBlock($sheet, int $templateRow, int $asesorRow): void
    {
        $sheet->mergeCells("D{$templateRow}:D{$asesorRow}");
        $sheet->mergeCells("E{$templateRow}:E{$asesorRow}");
        $sheet->mergeCells("F{$templateRow}:F{$asesorRow}");
        $sheet->mergeCells("G{$templateRow}:G{$asesorRow}");
        $sheet->mergeCells("H{$templateRow}:H{$asesorRow}");
    }

    private function mergeBlock($sheet, $blockStart, $blockEnd, int $rowStart, int $rowEnd): void
    {
        if ($rowStart <= $rowEnd) {
            $sheet->mergeCells("$blockStart{$rowStart}:$blockEnd{$rowEnd}");
        }
    }

    /**
     * Style khusus baris asesor (kuning) + tinggi baris
     */
    private function applyAsesorRowStyle($sheet, int $asesorRow): void
    {
        $sheet->getStyle("B{$asesorRow}:M{$asesorRow}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ffff99');

        $sheet->getRowDimension($asesorRow)->setRowHeight(150);
    }

    /**
     * Simpan spreadsheet ke storage/app/temp
     */
    private function saveSpreadsheet(Spreadsheet $spreadsheet, string $prefix, $code = null): string
    {
        if ($code)
            $filename = $prefix . $code . '_' . date('YmdHis') . '.xlsx';
        else
            $filename = $prefix . '.xlsx';
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
     * Build Excel headers (warna + rowspan sesuai permintaan)
     */
    private function buildHeaders($sheet, $asesmen): void
    {
        // ===== Title =====
        $sheet->mergeCells('B2:M2');
        $sheet->setCellValue('B2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur | LAMDEPILAR');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('B3:M3');
        $sheet->setCellValue('B3', 'Tabel 2. Kertas Kerja Asesor - ' . $asesmen->name);
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ===== Header row 5-7 dengan rowspan =====
        // Kriteria (B:C) rowspan sampai row 7
        $sheet->mergeCells('B5:C7');
        $sheet->setCellValue('B5', 'Kriteria');

        // Kode Elemen (D:E) rowspan sampai row 7
        $sheet->mergeCells('D5:E7');
        $sheet->setCellValue('D5', 'Kode Elemen');

        // Elemen Standar (F) rowspan sampai row 7
        $sheet->mergeCells('F5:F7');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // Indikator (G:H) merge row 5, lalu subheader di row 6-7
        $sheet->mergeCells('G5:H5');
        $sheet->setCellValue('G5', 'Indikator');

        // Penilaian (I:M) merge row 5
        $sheet->mergeCells('I5:M5');
        $sheet->setCellValue('I5', 'Penilaian');

        // Subheader indikator (row 6-7 dibuat rowspan per kolom)
        $sheet->setCellValue('G6', 'Kualitatif');
        $sheet->setCellValue('H6', 'Kuantitatif');
        $sheet->mergeCells('G6:G7');
        $sheet->mergeCells('H6:H7');

        // Subheader penilaian row 6
        $sheet->setCellValue('I6', 'Tidak Memenuhi (Not Met)');
        $sheet->setCellValue('J6', 'Belum Memenuhi (Not Met)');
        $sheet->setCellValue('K6', 'Lemah (Weakness/Cause of Concern)');
        $sheet->setCellValue('L6', 'Memenuhi (Met)');
        $sheet->setCellValue('M6', 'Pelampauan Standar');

        // Skor row 7
        $sheet->setCellValue('I7', '0');
        $sheet->setCellValue('J7', '1');
        $sheet->setCellValue('K7', '2');
        $sheet->setCellValue('L7', '3');
        $sheet->setCellValue('M7', '4');

        // ===== Styling umum header =====
        $headerRange = 'B5:M7';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Border
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // ===== Warna sesuai permintaan =====
        // B5:F7 putih
        $sheet->getStyle('B5:F7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFFFFF');

        // G5:H7 indikator #D0E0E3
        $sheet->getStyle('G5:H7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD0E0E3');

        // I5:M7 penilaian #D9EAD3
        $sheet->getStyle('I5:M7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9EAD3');

        // Tinggi baris header
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(40);
        $sheet->getRowDimension(7)->setRowHeight(20);

        // Petunjuk isi
        $sheet->mergeCells('B4:M4');
        $sheet->setCellValue('B4', 'Silahkan isi di bagian cell berwarna kuning. Masing-masing baris, hanya 1 kolom yang dapat diisi');
        $sheet->getStyle('B4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFF0000'], // merah
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);
    }

    /**
     * Apply styling to data row
     */
    private function applyRowStyling($sheet, $row, ?int $maxHeight = null, $startColumn = 'B', $endColumn = 'M'): void
    {
        $range = "{$startColumn}{$row}:{$endColumn}{$row}";

        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle($range)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height

        // Hanya set maxHeight jika diberikan
        if ($maxHeight !== null) {
            $sheet->getRowDimension($row)->setRowHeight($maxHeight);
        }
    }

    /**
     * Ambil deskripsi penilaian per skor (0–4) untuk satu elemen.
     */
    private function getPenilaianMapBySkor(ElemenStandar $elemen): array
    {
        $map = [];

        $elemen->loadMissing('indikatorPenilaian.jenjangPenilaian');

        foreach ($elemen->indikatorPenilaian as $indikator) {
            if ($indikator->jenjangPenilaian) {
                $skor = $indikator->jenjangPenilaian->skor;
                $map[$skor] = $indikator->deskripsi_penilaian;
            }
        }

        return $map;
    }

    private function buildMenuSheet(Spreadsheet $spreadsheet, Asesmen $asesmen): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Menu');
        $sheet->getSheetView()->setZoomScale(80);

        // Hide gridlines
        $sheet->setShowGridlines(false);

        // ===== COLUMN WIDTH (sesuaikan dengan gambar) =====
        foreach (range('A', 'Y') as $col) {
            $sheet->getColumnDimension($col)->setWidth(8);
        }

        // ===== ROW 1: AKREDITASI PERGURUAN TINGGI (Orange) =====
        $sheet->mergeCells('A1:Y1');
        $sheet->setCellValue('A1', 'AKREDITASI PERGURUAN TINGGI');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 24,
                'color' => ['argb' => 'FF000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF79646']
            ]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ===== ROW 2: BADAN AKREDITASI NASIONAL (Peach) =====
        $sheet->mergeCells('A2:Y2');
        $sheet->setCellValue('A2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur | LAMDEPILAR');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => 'FF000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFBD4B4']
            ]
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        // ===== ROW 3: Empty =====
        $sheet->getRowDimension(3)->setRowHeight(5);

        // ===== ROW 4: PERGURUAN TINGGI AKADEMIK (Light Green) =====
        $sheet->mergeCells('A4:Y4');
        $sheet->setCellValue('A4', 'PERGURUAN TINGGI AKADEMIK');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => 'FF000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFEAF1DD']
            ]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // ===== ROW 5: Empty =====
        $sheet->getRowDimension(5)->setRowHeight(5);

        // ===== BACKGROUND TEAL BESAR (Row 6-27) =====
        $fillTeal = [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF008080']
            ]
        ];

        foreach (['A3:Y3', 'A5:Y5', 'A6:Y27'] as $range) {
            $sheet->getStyle($range)->applyFromArray($fillTeal);
        }

        // ===== DATA PERGURUAN TINGGI (Kiri - Row 7, 9, 11, 13, 15) =====
        $leftData = [
            7  => ['label' => 'Nama Perguruan Tinggi', 'value' => $asesmen->studyProgram->university->name ?? '-'],
            9  => ['label' => 'Bentuk Perguruan Tinggi', 'value' => 'Universitas'],
            11 => ['label' => 'Jenis Pengelolaan (PTN/PTS)', 'value' => ''],
            13 => ['label' => 'Kode Panel', 'value' => $asesmen->kode_panel],
            15 => ['label' => 'TS *)', 'value' => ''],
        ];

        foreach ($leftData as $row => $data) {
            // Label (B:E)
            $sheet->mergeCells("B{$row}:E{$row}");
            $sheet->setCellValue("B{$row}", $data['label']);
            $sheet->getStyle("B{$row}:E{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FF000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Colon (F)
            $sheet->setCellValue("F{$row}", ':');
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FF000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Value (G:L) - White box
            $sheet->mergeCells("G{$row}:L{$row}");
            $sheet->setCellValue("G{$row}", $data['value']);
            $sheet->getStyle("G{$row}:L{$row}")->applyFromArray([
                'font' => [
                    'size' => 14,
                    'color' => ['argb' => 'FF000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFFFF']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000']
                    ]
                ]
            ]);

            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        // ===== VERTICAL YELLOW LINE (Column M) =====
        $sheet->getStyle('M6:M27')->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FFFBFD02']
                ]
            ]
        ]);

        // ===== ASESMEN KECUKUPAN HEADER (Row 18) =====
        $sheet->mergeCells('N18:Y18');
        $sheet->setCellValue('N18', 'ASESMEN KECUKUPAN');
        $sheet->getStyle('N18')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 20,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(18)->setRowHeight(25);

        // ===== (Penilaian Individual) (Row 19) =====
        $sheet->mergeCells('N19:Y19');
        $sheet->setCellValue('N19', '(Penilaian Individual)');
        $sheet->getStyle('N19')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 14,
                'color' => ['argb' => 'FF000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(19)->setRowHeight(20);

        // ===== DATA ASESOR (Kanan - Row 21, 23, 25) =====
        $rightData = [
            21 => ['label' => 'Nama Asesor', 'value' => Auth::user()->name ?? 'Maryono, Dr. Eng.'],
            23 => ['label' => 'Kota Penilaian', 'value' => 'Semarang'],
            25 => ['label' => 'Tanggal Penilaian', 'value' => date('d-M-Y')],
        ];

        foreach ($rightData as $row => $data) {
            // Label (O:R)
            $sheet->mergeCells("O{$row}:R{$row}");
            $sheet->setCellValue("O{$row}", $data['label']);
            $sheet->getStyle("O{$row}:R{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FF000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Colon (S)
            $sheet->setCellValue("S{$row}", ':');
            $sheet->getStyle("S{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['argb' => 'FF000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Value (T:Y) - White box
            $sheet->mergeCells("T{$row}:X{$row}");
            $sheet->setCellValue("T{$row}", $data['value']);
            $sheet->getStyle("T{$row}:X{$row}")->applyFromArray([
                'font' => [
                    'size' => 14,
                    'color' => ['argb' => 'FF000000']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFFFFFF']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000']
                    ]
                ]
            ]);

            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        // ===== FOOTNOTE TS (Row 23) =====
        $sheet->mergeCells('B23:L23');
        $sheet->setCellValue('B23', '*) TS = Tahun akademik penuh terakhir saat pengajuan usulan akreditasi');
        $sheet->getStyle('B23')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 9,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(23)->setRowHeight(18);

        // ===== SOURCE (Row 25) =====
        $sheet->mergeCells('B25:L25');
        $sheet->setCellValue('B25', 'LAMDEPILAR      versi 1.0');
        $sheet->getStyle('B25')->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(25)->setRowHeight(16);

        // Perpendek tinggi baris tertentu
        foreach ([8, 10, 12, 14, 22, 24] as $row) {
            $sheet->getRowDimension($row)->setRowHeight(6);
        }
    }

    /**
     * Bangun teks instruksi default untuk cell penilaian.
     */
    private function buildPenilaianInstruction(?string $deskripsi, int $skor): RichText
    {
        $richText = new RichText();

        // Bagian italic
        $italicText = $richText->createTextRun("Tuliskan pernyataan penilaian pada kolom ini apabila:\n\n");
        $italicText->getFont()->setItalic(true);

        // Bagian deskripsi (normal)
        if ($deskripsi) {
            $richText->createText($deskripsi);
        } else {
            $richText->createText("skor {$skor}");
        }

        return $richText;
    }

    /**
     * Build sheet "Penilaian (AK)" setelah sheet Kertas Kerja
     */
    private function buildPenilaianAKSheet(Spreadsheet $spreadsheet, Asesmen $asesmen): void
    {
        // Buat sheet baru
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Penilaian (AK)');
        $sheet->getSheetView()->setZoomScale(60);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(40);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(80);
        $sheet->getColumnDimension('J')->setWidth(80);

        // Build headers
        $this->buildPenilaianAKHeaders($sheet, $asesmen);

        // Build data rows
        $this->renderPenilaianAKRows($sheet, $asesmen);

        // 🔒 LOCK SEMUA CELL
        $sheet->getStyle($sheet->calculateWorksheetDimension())
            ->getProtection()
            ->setLocked(Protection::PROTECTION_PROTECTED);

        // 🔐 AKTIFKAN SHEET PROTECTION
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword('lamdepilar'); // opsional
        $sheet->getProtection()->setSelectLockedCells(false);
        $sheet->getProtection()->setSelectUnlockedCells(false);
    }

    /**
     * Build headers untuk sheet Penilaian (AK)
     */
    private function buildPenilaianAKHeaders($sheet, $asesmen): void
    {
        // ===== Title =====
        $sheet->mergeCells('B2:J2');
        $sheet->setCellValue('B2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur | LAMDEPILAR');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('B3:J3');
        $sheet->setCellValue('B3', 'Tabel 3. Penilaian AK');
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ===== Header row 5-6 dengan rowspan (mengikuti pola Kertas Kerja) =====

        // Kriteria (B-C) rowspan sampai row 6
        $sheet->mergeCells('B5:C6');
        $sheet->setCellValue('B5', 'Kriteria');

        // Kode Elemen (D-E) rowspan sampai row 6
        $sheet->mergeCells('D5:E6');
        $sheet->setCellValue('D5', 'Kode Elemen');

        // Elemen Standar (F) rowspan sampai row 6
        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // Indikator (G-H) merge row 5, subheader di row 6
        $sheet->mergeCells('G5:H5');
        $sheet->setCellValue('G5', 'Indikator');
        $sheet->setCellValue('G6', 'Kualitatif');
        $sheet->setCellValue('H6', 'Kuantitatif');

        // Penilaian (F:G) merge row 5
        $sheet->mergeCells('I5:J5');
        $sheet->setCellValue('I5', 'Penilaian');

        // Subheader penilaian row 6
        $sheet->setCellValue('I6', 'Pemenuhan Standar');
        $sheet->setCellValue('J6', 'Pelampauan Standar');

        // ===== Styling umum header =====
        $headerRange = 'B5:J6';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        // Border
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // ===== Warna sesuai pola Kertas Kerja =====
        // B5:F6 putih (Kriteria, Kode Elemen, Elemen Standar)
        $sheet->getStyle('B5:F6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFFFFFF');

        // G5:H6 indikator #D0E0E3 (sama dengan sheet Kertas Kerja)
        $sheet->getStyle('G5:H6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD0E0E3');

        // I5:J6 penilaian #D9EAD3 (sama dengan sheet Kertas Kerja)
        $sheet->getStyle('I5:J6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9EAD3');

        // Tinggi baris header
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Petunjuk pengisian (opsional, sesuaikan jika diperlukan)
        $sheet->mergeCells('B4:J4');
        $sheet->setCellValue('B4', 'Hasil Penilaian (Auto-filled dari Kertas Kerja Asesor)');
        $sheet->getStyle('B4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF0000FF'], // biru
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);
    }

    /**
     * Render data rows untuk sheet Penilaian (AK)
     */
    /**
     * Render data rows untuk sheet Penilaian (AK)
     */
    private function renderPenilaianAKRows($sheet, Asesmen $asesmen): void
    {
        $currentRow = 7;
        $penilaianAsesorRow = $currentRow + 2;
        $kriterias = Kriteria::with([
            'elemenStandar.indikator.jenisIndikator',
        ])->get();

        foreach ($kriterias as $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();

            if ($elemenCount > 1) {
                $blockStartRow = $currentRow;
                $blockEndRow   = $currentRow + $elemenCount - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }

            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $templateRow = $currentRow;
                // Isi kriteria (hanya sekali per grup)
                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$templateRow}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$templateRow}", $kriteria->nama_kriteria);
                }

                // ========== 3) Isi elemen + indikator ==========
                $indikatorKualitatif = $elemen->indikator
                    ->filter(fn($ind) => $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kualitatif') !== false)
                    ->pluck('deskripsi_indikator')
                    ->implode("\n\n");

                $indikatorKuantitatif = $elemen->indikator
                    ->filter(fn($ind) => $ind->jenisIndikator &&
                        stripos($ind->jenisIndikator->nama_jenis, 'kuantitatif') !== false)
                    ->pluck('deskripsi_indikator')
                    ->implode("\n\n");

                $sheet->setCellValue("D{$templateRow}", $index + 1);
                $sheet->setCellValue("E{$templateRow}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$templateRow}", $elemen->pernyataan_elemen);
                $sheet->setCellValue("G{$templateRow}", $indikatorKualitatif ?: 'Tidak ada');
                $sheet->setCellValue("H{$templateRow}", $indikatorKuantitatif ?: 'Tidak ada');

                // Formula Pemenuhan Standar - Menggunakan CONCATENATE untuk kompatibilitas
                // Menggabungkan nilai dari kolom I, J, K, L di baris yang sesuai
                // Pemenuhan Standar (kolom I-L digabung), anti #N/A
                $formulaPemenuhan = "=IFERROR(CONCATENATE(" .
                    "IFERROR(INDEX('Kertas Kerja AK Asesor'!\$I:\$I,MATCH(E{$templateRow},'Kertas Kerja AK Asesor'!\$E:\$E,0)+1),\"\")," .
                    "IFERROR(INDEX('Kertas Kerja AK Asesor'!\$J:\$J,MATCH(E{$templateRow},'Kertas Kerja AK Asesor'!\$E:\$E,0)+1),\"\")," .
                    "IFERROR(INDEX('Kertas Kerja AK Asesor'!\$K:\$K,MATCH(E{$templateRow},'Kertas Kerja AK Asesor'!\$E:\$E,0)+1),\"\")," .
                    "IFERROR(INDEX('Kertas Kerja AK Asesor'!\$L:\$L,MATCH(E{$templateRow},'Kertas Kerja AK Asesor'!\$E:\$E,0)+1),\"\")" .
                    "),\"\")";
                $sheet->setCellValue("I{$templateRow}", $formulaPemenuhan);

                // Formula Pelampauan Standar
                $formulaPelampauan = "=IF('Kertas Kerja AK Asesor'!M{$penilaianAsesorRow}=\"\", \"\", 'Kertas Kerja AK Asesor'!M{$penilaianAsesorRow})";

                $sheet->setCellValue("J{$templateRow}", $formulaPelampauan);

                // Apply styling
                $this->applyRowStyling($sheet, $currentRow, null, 'B', 'J');

                // Alignment dan wrap text
                $sheet->getStyle("B{$currentRow}:J{$currentRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)
                    ->setWrapText(true);

                $currentRow++;
                $penilaianAsesorRow += 2;
                $isFirstElemen = false;
            }
        }

        // Apply conditional formatting setelah semua data dirender
        $this->applyPenilaianAKConditionalFormatting($sheet, 7, $currentRow - 1);
    }

    /**
     * Apply conditional formatting untuk sheet Penilaian (AK)
     */
    private function applyPenilaianAKConditionalFormatting($sheet, int $startRow, int $endRow): void
    {
        $rulesPemenuhan = [
            // kolom sumber => [warna bg, warna font]
            'L' => ['FF00B050', Color::COLOR_WHITE], // hijau tua
            'K' => ['FFFFC000', Color::COLOR_BLACK], // kuning
            'J' => ['FF92D050', Color::COLOR_BLACK], // hijau muda
            'I' => ['FFFF0000', Color::COLOR_WHITE], // merah
        ];

        $conditionals = [];

        foreach ($rulesPemenuhan as $col => [$bg, $font]) {
            $conditional = new Conditional();
            $conditional->setConditionType(Conditional::CONDITION_EXPRESSION);
            $conditional->addCondition(
                "=IFERROR(
                INDEX('Kertas Kerja AK Asesor'!\${$col}:\${$col},
                MATCH(\$E{$startRow}, 'Kertas Kerja AK Asesor'!\$E:\$E, 0)+1),
            \"\")<>\"\""
            );

            $conditional->getStyle()->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($bg);

            $conditional->getStyle()->getFont()
                ->setBold(true)
                ->getColor()->setARGB($font);

            $conditionals[] = $conditional;
        }

        // Urutan prioritas: atas → bawah
        $sheet->getStyle("I{$startRow}:I{$endRow}")
            ->setConditionalStyles($conditionals);

        // ===== Pelampauan Standar (kolom J) =====
        $pelampauan = new Conditional();
        $pelampauan->setConditionType(Conditional::CONDITION_EXPRESSION);
        $pelampauan->addCondition(
            "=IFERROR(
            INDEX('Kertas Kerja AK Asesor'!\$M:\$M,
            MATCH(\$E{$startRow}, 'Kertas Kerja AK Asesor'!\$E:\$E, 0)+1),
        \"\")<>\"\""
        );

        $pelampauan->getStyle()->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF006100');

        $pelampauan->getStyle()->getFont()
            ->setBold(true)
            ->getColor()->setARGB(Color::COLOR_WHITE);

        $sheet->getStyle("J{$startRow}:J{$endRow}")
            ->setConditionalStyles([$pelampauan]);
    }
}
