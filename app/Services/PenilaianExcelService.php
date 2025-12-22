<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\ElemenStandar;
use App\Models\PenilaianElemen;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PenilaianExcelService
{
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
        $this->buildMenuSheet($spreadsheet);

        // Sheet 1 → Kertas Kerja
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Kertas Kerja AK Asesor');
        $spreadsheet->setActiveSheetIndex(1);

        $this->setColumnWidths($sheet);
        $this->buildHeaders($sheet, $asesmen);

        return [$spreadsheet, $sheet];
    }

    /**
     * Set column widths (dipakai dua mode)
     */
    private function setColumnWidths($sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(5);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(6);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(35);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(35);
        $sheet->getColumnDimension('J')->setWidth(35);
        $sheet->getColumnDimension('K')->setWidth(35);
        $sheet->getColumnDimension('L')->setWidth(35);
        $sheet->getColumnDimension('M')->setWidth(50);
    }

    /**
     * Render baris data (2 baris per elemen).
     * Jika $userId null => template mode
     * Jika $userId ada => withData mode
     */
    private function renderElemenRows($sheet, Asesmen $asesmen, ?int $userId): void
    {
        $currentRow = 8;

        $kriterias = Kriteria::with([
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
        ])->get();

        foreach ($kriterias as $kriteria) {
            $isFirstElemen = true;

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
                $this->applyRowStyling($sheet, $templateRow);
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
        $penilaian = PenilaianElemen::where('id_asesmen', $asesmen->id)
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
        $sheet->mergeCells("B{$templateRow}:B{$asesorRow}");
        $sheet->mergeCells("C{$templateRow}:C{$asesorRow}");
        $sheet->mergeCells("D{$templateRow}:D{$asesorRow}");
        $sheet->mergeCells("E{$templateRow}:E{$asesorRow}");
        $sheet->mergeCells("F{$templateRow}:F{$asesorRow}");
        $sheet->mergeCells("G{$templateRow}:G{$asesorRow}");
        $sheet->mergeCells("H{$templateRow}:H{$asesorRow}");
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
        $sheet->setCellValue('J6', 'Tidak Memenuhi (Not Met)');
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
    }

    /**
     * Apply styling to data row
     */
    private function applyRowStyling($sheet, $row): void
    {
        $range = "B{$row}:M{$row}";

        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle($range)->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height
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

    private function buildMenuSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Menu');

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
                'size' => 18,
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
                'size' => 16,
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
                'size' => 16,
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

        // ===== BACKGROUND TEAL BESAR (Row 6-35) =====
        $sheet->getStyle('A6:Y35')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF008080']
            ]
        ]);

        // ===== DATA PERGURUAN TINGGI (Kiri - Row 7, 9, 11, 13, 15) =====
        $leftData = [
            7  => ['label' => 'Nama Perguruan Tinggi', 'value' => 'Universitas Serasan'],
            9  => ['label' => 'Bentuk Perguruan Tinggi', 'value' => 'Universitas'],
            11 => ['label' => 'Jenis Pengelolaan', 'value' => 'PTS'],
            13 => ['label' => 'Kode Panel', 'value' => 'T01-P007'],
            15 => ['label' => 'TS *)', 'value' => '2017 / 2018'],
        ];

        foreach ($leftData as $row => $data) {
            // Label (B:E)
            $sheet->mergeCells("B{$row}:E{$row}");
            $sheet->setCellValue("B{$row}", $data['label']);
            $sheet->getStyle("B{$row}:E{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
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
                    'size' => 11,
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
                    'size' => 11,
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
        $sheet->getStyle('M6:M35')->applyFromArray([
            'borders' => [
                'right' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FFFBFD02']
                ]
            ]
        ]);

        // ===== ASESMEN KECUKUPAN HEADER (Row 8) =====
        $sheet->mergeCells('N8:Y8');
        $sheet->setCellValue('N8', 'ASESMEN KECUKUPAN');
        $sheet->getStyle('N8')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(8)->setRowHeight(25);

        // ===== (Penilaian Individual) (Row 9) =====
        $sheet->mergeCells('N9:Y9');
        $sheet->setCellValue('N9', '(Penilaian Individual)');
        $sheet->getStyle('N9')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 12,
                'color' => ['argb' => 'FF000000']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(9)->setRowHeight(20);

        // ===== DATA ASESOR (Kanan - Row 11, 13, 15) =====
        $rightData = [
            11 => ['label' => 'Nama Asesor', 'value' => Auth::user()->name ?? 'Maryono, Dr. Eng.'],
            13 => ['label' => 'Kota Penilaian', 'value' => 'Semarang'],
            15 => ['label' => 'Tanggal Penilaian', 'value' => date('d-M-Y')],
        ];

        foreach ($rightData as $row => $data) {
            // Label (O:R)
            $sheet->mergeCells("O{$row}:R{$row}");
            $sheet->setCellValue("O{$row}", $data['label']);
            $sheet->getStyle("O{$row}:R{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
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
                    'size' => 11,
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
                    'size' => 11,
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

        // ===== FOOTNOTE TS (Row 32) =====
        $sheet->mergeCells('B32:L32');
        $sheet->setCellValue('B32', '*) TS = Tahun akademik penuh terakhir saat pengajuan usulan akreditasi');
        $sheet->getStyle('B32')->applyFromArray([
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
        $sheet->getRowDimension(32)->setRowHeight(18);

        // ===== SOURCE (Row 34) =====
        $sheet->mergeCells('B34:L34');
        $sheet->setCellValue('B34', 'LAMDEPILAR      versi 1.0');
        $sheet->getStyle('B34')->applyFromArray([
            'font' => [
                'size' => 9,
                'color' => ['argb' => 'FFFBFD02'] // Yellow
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);
        $sheet->getRowDimension(34)->setRowHeight(16);
    }

    /**
     * Bangun teks instruksi default untuk cell penilaian.
     */
    private function buildPenilaianInstruction(?string $deskripsi, int $skor): string
    {
        if ($deskripsi) {
            return "Tuliskan pernyataan penilaian  pada kolom ini apabila:\n\n" . $deskripsi;
        }

        return "Tuliskan pernyataan penilaian  pada kolom ini apabila skor {$skor}";
    }
}
