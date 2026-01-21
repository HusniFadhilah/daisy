<?php
// app/Services/BorangValidationExcelService.php

namespace App\Services;

use App\Models\{
    AsesmenUserRole,
    BorangValidation,
    Kriteria,
    DatasetSuplemen
};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class BorangValidationExcelService
{
    /**
     * Generate Excel file (template or with data)
     */
    public function generateExcel(AsesmenUserRole $assignment, bool $withData = false): array
    {
        try {
            $pengajuan = $assignment->asesmen->pengajuan;
            $validation = $assignment->borangValidation;
            $degreeCode = $this->mapDegreeCode($pengajuan->studyProgram->degreeLevel);

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            // Create sheets
            $this->createLedSheet($spreadsheet, $pengajuan, $validation, $withData);
            $this->createSuplemenSheet($spreadsheet, $degreeCode, $validation, $withData);
            $this->createLkpsSheet($spreadsheet, $pengajuan, $validation, $withData);
            $this->createPetunjukSheet($spreadsheet);

            // Set active sheet
            $spreadsheet->setActiveSheetIndex(0);

            // Generate filename
            $type = $withData ? 'Review' : 'Template';
            $filename = $type . '_LED-Suplemen_LKPS_Lengkap_' . Str::slug($pengajuan->nomor_pengajuan) . '_' . date('Ymd') . '.xlsx';

            // Save to temp file
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempFile);

            return [
                'success' => true,
                'file' => $tempFile,
                'filename' => $filename,
            ];
        } catch (\Exception $e) {
            Log::error('Generate Excel failed', [
                'assignment_id' => $assignment->id,
                'with_data' => $withData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal generate Excel: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Import and process Excel file
     */
    public function importExcel(AsesmenUserRole $assignment, string $filePath): array
    {
        try {
            $validation = $assignment->borangValidation;

            if (!$validation) {
                throw new \Exception('Validation record tidak ditemukan');
            }

            // Load Excel file
            $spreadsheet = IOFactory::load($filePath);

            // Process each sheet
            $reviewLed = $this->processLedSheet($spreadsheet->getSheetByName('LED'));
            $reviewSuplemen = $this->processSuplemenSheet(
                $spreadsheet->getSheetByName('Suplemen'),
                $assignment
            );
            $reviewLkps = $this->processLkpsSheet($spreadsheet->getSheetByName('LKPS'));

            // Merge with existing data
            $currentLed = $validation->review_led ?? [];
            $currentSuplemen = $validation->review_suplemen ?? [];
            $currentLkps = $validation->review_lkps ?? [];

            $mergedLed = $this->mergeReviewById($currentLed, $reviewLed);
            $mergedSuplemen = $this->mergeReviewById($currentSuplemen, $reviewSuplemen);
            $mergedLkps = $this->mergeReviewById($currentLkps, $reviewLkps);

            // Count reviewed items
            $reviewedLed = count(array_filter($mergedLed, fn($item) => !empty($item['grade'])));
            $reviewedSuplemen = count(array_filter($mergedSuplemen, fn($item) => !empty($item['grade'])));
            $reviewedLkps = count(array_filter($mergedLkps, fn($item) => !empty($item['grade'])));

            // Update validation
            $validation->update([
                'review_led' => $mergedLed,
                'review_suplemen' => $mergedSuplemen,
                'review_lkps' => $mergedLkps,
                'reviewed_led' => $reviewedLed,
                'reviewed_suplemen' => $reviewedSuplemen,
                'reviewed_lkps' => $reviewedLkps,
            ]);

            return [
                'success' => true,
                'message' => sprintf(
                    'Excel validasi berhasil diupload. LED: %d/%d, Suplemen: %d/%d, LKPS: %d/%d',
                    $reviewedLed,
                    $validation->total_elemen_led,
                    $reviewedSuplemen,
                    $validation->total_elemen_suplemen,
                    $reviewedLkps,
                    $validation->total_indikator_lkps
                ),
                'stats' => [
                    'led' => ['reviewed' => $reviewedLed, 'total' => $validation->total_elemen_led],
                    'suplemen' => ['reviewed' => $reviewedSuplemen, 'total' => $validation->total_elemen_suplemen],
                    'lkps' => ['reviewed' => $reviewedLkps, 'total' => $validation->total_indikator_lkps],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Import Excel failed', [
                'assignment_id' => $assignment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal import Excel: ' . $e->getMessage(),
            ];
        }
    }

    // ========================================
    // SHEET CREATION METHODS
    // ========================================

    /**
     * Create LED sheet
     */
    private function createLedSheet(Spreadsheet $spreadsheet, $pengajuan, $validation, bool $withData): void
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'LED');
        $spreadsheet->addSheet($sheet);
        $this->addLogoAndZoom($sheet);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);  // Kosong
        $sheet->getColumnDimension('B')->setWidth(5);  // No Kriteria
        $sheet->getColumnDimension('C')->setWidth(20); // Kriteria
        $sheet->getColumnDimension('D')->setWidth(6);  // No Elemen
        $sheet->getColumnDimension('E')->setWidth(6);  // Kode Elemen
        $sheet->getColumnDimension('F')->setWidth(25); // Elemen Standar
        $sheet->getColumnDimension('G')->setWidth(40); // Indikator Kualitatif
        $sheet->getColumnDimension('H')->setWidth(40); // Indikator Kuantitatif
        $sheet->getColumnDimension('I')->setWidth(50); // Kategori Review
        $sheet->getColumnDimension('J')->setWidth(60); // Catatan

        // Header
        $sheet->mergeCells('D2:J2');
        $sheet->setCellValue('D2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('D3:J3');
        $sheet->setCellValue('D3', 'Tabel Validasi LED+Suplemen dan LKPS Prodi ' . ($pengajuan->studyProgram->name ?? ''));
        $sheet->getStyle('D3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column headers - Row 5
        $sheet->mergeCells('B5:C6');
        $sheet->setCellValue('B5', 'Kriteria');

        $sheet->mergeCells('D5:E6');
        $sheet->setCellValue('D5', 'Kode Elemen');

        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // Indikator (G:H) merge row 5
        $sheet->mergeCells('G5:H5');
        $sheet->setCellValue('G5', 'Indikator');

        $sheet->mergeCells('I5:I6');
        $sheet->setCellValue('I5', 'Kategori Validasi');

        $sheet->mergeCells('J5:J6');
        $sheet->setCellValue('J5', 'Catatan Validasi');

        $sheet->setCellValue('G6', 'Kualitatif');
        $sheet->setCellValue('H6', 'Kuantitatif');

        // base: border + bold + align
        $this->styleHeaderBase($sheet, 'B5:J6');

        // 1) Kriteria + Kode Elemen + Elemen Standar => background putih
        $this->fillRange($sheet, 'B5:F6', 'FFFFFFFF');

        // 2) Indikator header (G:H) => background abu2 muda D0E0E3
        $this->fillRange($sheet, 'G5:H6', 'FFD0E0E3');

        // 3) Kategori Review & Catatan Review => hijau muda D9EAD3
        $this->fillRange($sheet, 'I5:J6', 'FFD9EAD3');

        // Row heights
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Petunjuk
        $sheet->mergeCells('D4:J4');
        $sheet->setCellValue('D4', 'Silahkan isi Kategori Validasi dan Catatan Validasi untuk setiap elemen');
        $sheet->getStyle('D4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1F4E79'], 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Data rows
        $row = 7;
        $noKriteria = 1;

        // ✅ Hapus orderBy - tampilkan sesuai urutan di database
        $kriterias = Kriteria::with([
            'elemenStandar.indikator.jenisIndikator'
        ])->get();

        $reviewData = $withData ? ($validation->review_led ?? []) : [];

        foreach ($kriterias as $kriteria) {
            $isFirstElemen = true;
            $elemenCount = $kriteria->elemenStandar->count();

            if ($elemenCount > 1) {
                $blockStartRow = $row;
                $blockEndRow = $row + $elemenCount - 1;
                $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
            }

            foreach ($kriteria->elemenStandar as $index => $elemen) {
                // Kriteria (hanya sekali per grup)
                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$row}", $noKriteria);
                    $sheet->setCellValue("C{$row}", $kriteria->nama_kriteria);
                }

                // ✅ Elemen - menggunakan index + 1
                $sheet->setCellValue("D{$row}", $index + 1);
                $sheet->setCellValue("E{$row}", $elemen->kode_elemen);
                $sheet->setCellValue("F{$row}", $elemen->pernyataan_elemen);

                // Indikator Kualitatif dan Kuantitatif
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

                $sheet->setCellValue("G{$row}", $indikatorKualitatif ?: 'Tidak ada');
                $sheet->setCellValue("H{$row}", $indikatorKuantitatif ?: 'Tidak ada');

                // Kategori Review & Catatan
                $gradeCell = "I{$row}";
                if ($withData && isset($reviewData[$elemen->id])) {
                    $sheet->setCellValue($gradeCell, $this->gradeToText($reviewData[$elemen->id]['grade'] ?? '', 'led'));
                    $sheet->setCellValue("J{$row}", $reviewData[$elemen->id]['catatan'] ?? '');
                } else {
                    $sheet->setCellValue($gradeCell, '');
                    $sheet->setCellValue("J{$row}", '');
                }

                // ✅ Dropdown dengan deskripsi lengkap untuk LED
                $this->addKategoriValidation($sheet, $gradeCell, 'led');
                $this->styleDataRow($sheet, "B{$row}:J{$row}");

                $row++;
                $isFirstElemen = false;
            }

            $noKriteria++;
        }
    }

    /**
     * Create Suplemen sheet
     */
    private function createSuplemenSheet(Spreadsheet $spreadsheet, string $degreeCode, $validation, bool $withData): void
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Suplemen');
        $spreadsheet->addSheet($sheet);
        $this->addLogoAndZoom($sheet);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(6);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(60);
        $sheet->getColumnDimension('E')->setWidth(50);
        $sheet->getColumnDimension('F')->setWidth(60);

        // Header
        $sheet->mergeCells('D2:F2');
        $sheet->setCellValue('D2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('D3:F3');
        $sheet->setCellValue('D3', 'Tabel Validasi LKPS Prodi ' . ($pengajuan->studyProgram->name ?? ''));
        $sheet->getStyle('D3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column headers
        $row = 5;
        $headers = ['No', 'Section', 'Konten', 'Kategori Validasi', 'Catatan Validasi'];
        $cols = ['B', 'C', 'D', 'E', 'F'];

        foreach ($headers as $i => $header) {
            $sheet->setCellValue($cols[$i] . $row, $header);
        }

        $this->styleHeaderBase($sheet, 'B5:F5');

        // B5:D5 (No, Section, Konten) => abu2 muda
        $this->fillRange($sheet, 'B5:D5', 'FFD0E0E3');

        // E5:F5 (Kategori Review, Catatan Review) => hijau muda (biar konsisten)
        $this->fillRange($sheet, 'E5:F5', 'FFD9EAD3');
        $sheet->getRowDimension($row)->setRowHeight(30);

        // Petunjuk
        $sheet->mergeCells('D4:F4');
        $sheet->setCellValue('D4', 'Silahkan isi Kategori Review dan Catatan Validasi untuk setiap item suplemen');
        $sheet->getStyle('D4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1F4E79'], 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Data
        $row = 6;
        $no = 1;

        $suplemenItems = DatasetSuplemen::where('degree_level_code', $degreeCode)
            ->where('content_type', 'list_item')
            ->where('section_key', '!=', 'header')
            ->orderBy('urutan')
            ->get();

        $reviewData = $withData ? ($validation->review_suplemen ?? []) : [];

        foreach ($suplemenItems as $item) {
            $sheet->setCellValue('B' . $row, $no++);
            $sheet->setCellValue('C' . $row, \Illuminate\Support\Str::headline($item->section_key));
            $sheet->setCellValue('D' . $row, $item->text_content);

            // Kategori Review
            $gradeCell = 'E' . $row;
            if ($withData && isset($reviewData[$item->id])) {
                $sheet->setCellValue($gradeCell, $this->gradeToText($reviewData[$item->id]['grade'] ?? '', 'suplemen'));
                $sheet->setCellValue('F' . $row, $reviewData[$item->id]['catatan'] ?? '');
            } else {
                $sheet->setCellValue($gradeCell, '');
                $sheet->setCellValue('F' . $row, '');
            }

            // ✅ Dropdown dengan deskripsi lengkap untuk Suplemen
            $this->addKategoriValidation($sheet, $gradeCell, 'suplemen');
            $this->styleDataRow($sheet, 'B' . $row . ':F' . $row);

            $row++;
        }
    }

    /**
     * Create LKPS sheet
     */
    private function createLkpsSheet(Spreadsheet $spreadsheet, $pengajuan, $validation, bool $withData): void
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'LKPS');
        $spreadsheet->addSheet($sheet);
        $this->addLogoAndZoom($sheet);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);  // Kosong
        $sheet->getColumnDimension('B')->setWidth(5);  // No Kriteria
        $sheet->getColumnDimension('C')->setWidth(20); // Kriteria
        $sheet->getColumnDimension('D')->setWidth(6);  // No Elemen
        $sheet->getColumnDimension('E')->setWidth(6);  // Kode Elemen
        $sheet->getColumnDimension('F')->setWidth(25); // Elemen Standar
        $sheet->getColumnDimension('G')->setWidth(50); // Indikator Kuantitatif
        $sheet->getColumnDimension('H')->setWidth(50); // Kategori Review
        $sheet->getColumnDimension('I')->setWidth(60); // Catatan

        // Header
        $sheet->mergeCells('D2:I2');
        $sheet->setCellValue('D2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur (LAMDEPILAR)');
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('D3:I3');
        $sheet->setCellValue('D3', 'Tabel Validasi LKPS Prodi ' . ($pengajuan->studyProgram->name ?? ''));
        $sheet->getStyle('D3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column headers - Row 5
        $sheet->mergeCells('B5:C6');
        $sheet->setCellValue('B5', 'Kriteria');

        $sheet->mergeCells('D5:E6');
        $sheet->setCellValue('D5', 'Kode Elemen');

        $sheet->mergeCells('F5:F6');
        $sheet->setCellValue('F5', 'Elemen Standar');

        // ✅ Indikator Kuantitatif (G5:G6) - langsung merge
        $sheet->mergeCells('G5:G6');
        $sheet->setCellValue('G5', 'Indikator Kuantitatif');

        $sheet->mergeCells('H5:H6');
        $sheet->setCellValue('H5', 'Kategori Validasi');

        $sheet->mergeCells('I5:I6');
        $sheet->setCellValue('I5', 'Catatan Validasi');

        // Style headers
        $this->styleHeaderBase($sheet, 'B5:I6');

        // putih untuk kiri (Kriteria, Kode Elemen, Elemen Standar)
        $this->fillRange($sheet, 'B5:F6', 'FFFFFFFF');

        // indikator kuantitatif (G)
        $this->fillRange($sheet, 'G5:G6', 'FFD0E0E3');

        // kategori & catatan (H:I)
        $this->fillRange($sheet, 'H5:I6', 'FFD9EAD3');

        // Row heights
        $sheet->getRowDimension(5)->setRowHeight(30);
        $sheet->getRowDimension(6)->setRowHeight(30);

        // Petunjuk
        $sheet->mergeCells('D4:I4');
        $sheet->setCellValue('D4', 'Silahkan isi Kategori Review dan Catatan untuk setiap indikator LKPS');
        $sheet->getStyle('D4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1F4E79'], 'size' => 11],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Data rows
        $row = 7;
        $noKriteria = 1;

        // ✅ Hapus orderBy
        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator' => function ($q) {
                $q->where('id_jenis', 2); // Hanya kuantitatif
            },
        ])->get();

        $reviewData = $withData ? ($validation->review_lkps ?? []) : [];

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemenIndex => $elemen) {
                $indikators = $elemen->indikator;

                if ($indikators->isEmpty()) {
                    continue;
                }

                $isFirstIndikator = true;
                $indikatorCount = $indikators->count();

                // Merge blocks for kriteria dan elemen
                if ($indikatorCount > 1) {
                    $blockStartRow = $row;
                    $blockEndRow = $row + $indikatorCount - 1;

                    $this->mergeBlock($sheet, 'B', 'B', $blockStartRow, $blockEndRow);
                    $this->mergeBlock($sheet, 'C', 'C', $blockStartRow, $blockEndRow);
                    $this->mergeBlock($sheet, 'D', 'D', $blockStartRow, $blockEndRow);
                    $this->mergeBlock($sheet, 'E', 'E', $blockStartRow, $blockEndRow);
                    $this->mergeBlock($sheet, 'F', 'F', $blockStartRow, $blockEndRow);
                }

                foreach ($indikators as $indikator) {
                    // Kriteria dan Elemen (hanya sekali per grup indikator)
                    if ($isFirstIndikator) {
                        $sheet->setCellValue("B{$row}", $noKriteria);
                        $sheet->setCellValue("C{$row}", $kriteria->nama_kriteria);

                        // ✅ FIX: kolom D = index elemen (mulai 1)
                        $sheet->setCellValue("D{$row}", $elemenIndex + 1);

                        // ✅ kolom E = kode elemen (E.2, E.3, dst)
                        $sheet->setCellValue("E{$row}", $elemen->kode_elemen);

                        // ✅ kolom F = nama elemen
                        $sheet->setCellValue("F{$row}", $elemen->pernyataan_elemen);
                    }

                    // ✅ Indikator Kuantitatif (langsung deskripsi, tanpa kode)
                    $sheet->setCellValue("G{$row}", $indikator->deskripsi_indikator);

                    // Kategori Review
                    $gradeCell = "H{$row}";
                    if ($withData && isset($reviewData[$indikator->id])) {
                        $sheet->setCellValue($gradeCell, $this->gradeToText($reviewData[$indikator->id]['grade'] ?? '', 'lkps'));
                        $sheet->setCellValue("I{$row}", $reviewData[$indikator->id]['catatan'] ?? '');
                    } else {
                        $sheet->setCellValue($gradeCell, '');
                        $sheet->setCellValue("I{$row}", '');
                    }

                    // ✅ Dropdown dengan deskripsi lengkap untuk LKPS
                    $this->addKategoriValidation($sheet, $gradeCell, 'lkps');
                    $this->styleDataRow($sheet, "B{$row}:I{$row}");

                    $row++;
                    $isFirstIndikator = false;
                }

                $noKriteria++;
            }
        }
    }

    /**
     * Create Petunjuk sheet
     */
    private function createPetunjukSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Petunjuk');
        $spreadsheet->addSheet($sheet);

        $sheet->setCellValue('A1', 'PETUNJUK PENGGUNAAN');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $row = 3;
        $instructions = [
            'KATEGORI REVIEW:',
            '',
            'LED:',
            'A = Prodi telah mengisi deskripsi LED dengan tepat',
            'B = Prodi telah mengisi deskripsi LED, tetapi kurang lengkap dan perlu melengkapi',
            'C = Prodi perlu memperbaiki isian LED',
            '',
            'Suplemen:',
            'A = Prodi telah mengisi deskripsi Suplemen dengan tepat',
            'B = Prodi telah mengisi deskripsi Suplemen, tetapi kurang lengkap dan perlu melengkapi',
            'C = Prodi perlu memperbaiki isian Suplemen',
            '',
            'LKPS:',
            'A = Prodi telah mengisi deskripsi LKPS dengan tepat',
            'B = Prodi telah mengisi deskripsi LKPS, tetapi kurang lengkap dan perlu melengkapi',
            'C = Prodi perlu memperbaiki isian LKPS',
            '',
            'CARA PENGISIAN:',
            '1. Pilih Kategori Validasi pada kolom Kategori Validasi menggunakan dropdown',
            '2. Dropdown akan menampilkan deskripsi lengkap sesuai jenis dokumen',
            '3. Isi catatan validasi pada kolom Catatan',
            '4. Simpan file Excel',
            '5. Upload kembali ke sistem menggunakan tombol Upload',
            '',
            'CATATAN PENTING:',
            '- Jangan mengubah struktur tabel atau menghapus kolom',
            '- Jangan mengubah nilai pada kolom No, Kode, dan Deskripsi',
            '- Pastikan semua item sudah diisi kategori review-nya',
            '- Data yang diupload akan digabungkan dengan review online yang sudah ada',
        ];

        foreach ($instructions as $instruction) {
            $sheet->setCellValue('A' . $row, $instruction);
            $sheet->mergeCells('A' . $row . ':B' . $row);

            if (str_contains($instruction, ':') && !empty(trim($instruction, ':'))) {
                $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            }

            $sheet->getRowDimension($row)->setRowHeight(-1);
            $sheet->getStyle('A' . $row)->getAlignment()->setWrapText(true);

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(100);

        // === LIST DROPDOWN (dipakai DataValidation) ===
        $sheet->setCellValue('D1', "Prodi telah mengisi deskripsi LED dengan tepat");
        $sheet->setCellValue('D2', "Prodi telah mengisi deskripsi LED, tetapi kurang lengkap dan perlu melengkapi");
        $sheet->setCellValue('D3', "Prodi perlu memperbaiki isian LED");

        $sheet->setCellValue('E1', "Prodi telah mengisi deskripsi Suplemen dengan tepat");
        $sheet->setCellValue('E2', "Prodi telah mengisi deskripsi Suplemen, tetapi kurang lengkap dan perlu melengkapi");
        $sheet->setCellValue('E3', "Prodi perlu memperbaiki isian Suplemen");

        $sheet->setCellValue('F1', "Prodi telah mengisi deskripsi LKPS dengan tepat");
        $sheet->setCellValue('F2', "Prodi telah mengisi deskripsi LKPS, tetapi kurang lengkap dan perlu melengkapi");
        $sheet->setCellValue('F3', "Prodi perlu memperbaiki isian LKPS");

        // sembunyikan kolom D-F supaya tidak mengganggu user
        $sheet->getColumnDimension('D')->setVisible(false);
        $sheet->getColumnDimension('E')->setVisible(false);
        $sheet->getColumnDimension('F')->setVisible(false);
    }

    // ========================================
    // SHEET PROCESSING METHODS
    // ========================================

    /**
     * Process LED sheet from Excel
     */
    private function processLedSheet($sheet): array
    {
        if (!$sheet) {
            throw new \Exception('Sheet LED tidak ditemukan');
        }

        $reviewData = [];
        $row = 7;

        $kriterias = Kriteria::with('elemenStandar')->get();

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemen) {
                $gradeText = trim((string) $sheet->getCell('I' . $row)->getValue());
                $catatan = trim((string) $sheet->getCell('J' . $row)->getValue());

                // ✅ Convert deskripsi lengkap ke grade (A/B/C)
                $grade = $this->extractGradeFromText($gradeText, 'led');

                if (!empty($grade)) {
                    $reviewData[$elemen->id] = [
                        'grade' => $grade,
                        'catatan' => $catatan ?? '',
                        'reviewed_at' => now()->toDateTimeString(),
                        'reviewed_by' => Auth::user()->name,
                    ];
                }

                $row++;
            }
        }

        return $reviewData;
    }

    /**
     * Process Suplemen sheet from Excel
     */
    private function processSuplemenSheet($sheet, AsesmenUserRole $assignment): array
    {
        if (!$sheet) {
            throw new \Exception('Sheet Suplemen tidak ditemukan');
        }

        $reviewData = [];
        $row = 6;

        $pengajuan = $assignment->asesmen->pengajuan;
        $degreeCode = $this->mapDegreeCode($pengajuan->studyProgram->degreeLevel);

        $suplemenItems = DatasetSuplemen::where('degree_level_code', $degreeCode)
            ->where('content_type', 'list_item')
            ->where('section_key', '!=', 'header')
            ->orderBy('urutan')
            ->get();

        foreach ($suplemenItems as $item) {
            $gradeText = trim((string) $sheet->getCell('E' . $row)->getValue());
            $catatan = trim((string) $sheet->getCell('F' . $row)->getValue());

            // ✅ Convert deskripsi lengkap ke grade
            $grade = $this->extractGradeFromText($gradeText, 'suplemen');

            if (!empty($grade)) {
                $reviewData[$item->id] = [
                    'grade' => $grade,
                    'catatan' => $catatan ?? '',
                    'reviewed_at' => now()->toDateTimeString(),
                    'reviewed_by' => Auth::user()->name,
                ];
            }

            $row++;
        }

        return $reviewData;
    }

    /**
     * Process LKPS sheet from Excel
     */
    private function processLkpsSheet($sheet): array
    {
        if (!$sheet) {
            throw new \Exception('Sheet LKPS tidak ditemukan');
        }

        $reviewData = [];
        $row = 7;

        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator' => function ($q) {
                $q->where('id_jenis', 2);
            },
        ])->get();

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemen) {
                foreach ($elemen->indikator as $indikator) {
                    $gradeText = trim((string) $sheet->getCell('H' . $row)->getValue());
                    $catatan = trim((string) $sheet->getCell('I' . $row)->getValue());

                    // ✅ Convert deskripsi lengkap ke grade
                    $grade = $this->extractGradeFromText($gradeText, 'lkps');

                    if (!empty($grade)) {
                        $reviewData[$indikator->id] = [
                            'grade' => $grade,
                            'catatan' => $catatan ?? '',
                            'reviewed_at' => now()->toDateTimeString(),
                            'reviewed_by' => Auth::user()->name,
                        ];
                    }

                    $row++;
                }
            }
        }

        return $reviewData;
    }

    // ========================================
    // STYLING HELPER METHODS
    // ========================================
    /**
     * Style data row - sesuai PenilaianExcelService
     */
    private function styleDataRow($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
                'wrapText' => true,
            ],
        ]);
    }

    /**
     * Add kategori review dropdown validation dengan deskripsi lengkap
     */
    private function addKategoriValidation($sheet, string $cell, string $type): void
    {
        $validation = $sheet->getCell($cell)->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Kategori Tidak Valid');
        $validation->setError('Pilih kategori dari dropdown');

        // ✅ Deskripsi lengkap sesuai jenis dokumen
        $options = $this->getKategoriOptions($type);
        $validation->setPromptTitle('Pilih Kategori Review');
        $validation->setPrompt($options['prompt']);

        // pakai range di sheet Petunjuk
        $range = match ($type) {
            'led' => "Petunjuk!\$D\$1:\$D\$3",
            'suplemen' => "Petunjuk!\$E\$1:\$E\$3",
            'lkps' => "Petunjuk!\$F\$1:\$F\$3",
            default => "Petunjuk!\$D\$1:\$D\$3",
        };

        $validation->setFormula1($range);
    }

    /**
     * Get kategori options based on document type
     */
    private function getKategoriOptions(string $type): array
    {
        $selected = $this->getKategoriMap($type);

        return [
            'prompt' => implode("\n", array_values($selected)),
            'formula' => implode(',', array_values($selected)),
        ];
    }

    /**
     * Extract grade (A/B/C) from full text description
     */
    private function extractGradeFromText(string $text, string $type): string
    {
        $text = trim((string) $text);
        if ($text === '') return '';

        // ✅ kalau user isi A/B/C langsung
        $upper = strtoupper($text);
        if (in_array($upper, ['A', 'B', 'C'], true)) {
            return $upper;
        }

        // exact match dengan opsi dropdown
        $map = $this->getKategoriMap($type);
        foreach ($map as $grade => $desc) {
            if (trim($text) === trim($desc)) {
                return $grade;
            }
        }

        // fallback keyword
        if (stripos($text, 'dengan tepat') !== false) return 'A';
        if (stripos($text, 'kurang lengkap') !== false) return 'B';
        if (stripos($text, 'perlu memperbaiki') !== false) return 'C';

        return '';
    }

    /**
     * Merge blocks helper
     */
    private function mergeBlock($sheet, string $startCol, string $endCol, int $startRow, int $endRow): void
    {
        if ($startRow <= $endRow) {
            $sheet->mergeCells("{$startCol}{$startRow}:{$endCol}{$endRow}");
        }
    }

    /**
     * Map degree level to code
     */
    private function mapDegreeCode($degreeLevel): string
    {
        $raw = strtolower((string) ($degreeLevel->code ?? $degreeLevel->name ?? ''));
        $raw = str_replace([' ', '_'], '-', $raw);

        return match ($raw) {
            'sarjana-terapan', 's1-terapan' => 'd4',
            default => $raw,
        };
    }

    private function getKategoriMap(string $type): array
    {
        return match ($type) {
            'led' => [
                'A' => "Prodi telah mengisi deskripsi LED dengan tepat",
                'B' => "Prodi telah mengisi deskripsi LED, tetapi kurang lengkap dan perlu melengkapi",
                'C' => "Prodi perlu memperbaiki isian LED",
            ],
            'suplemen' => [
                'A' => "Prodi telah mengisi deskripsi Suplemen dengan tepat",
                'B' => "Prodi telah mengisi deskripsi Suplemen, tetapi kurang lengkap dan perlu melengkapi",
                'C' => "Prodi perlu memperbaiki isian Suplemen",
            ],
            'lkps' => [
                'A' => "Prodi telah mengisi deskripsi LKPS dengan tepat",
                'B' => "Prodi telah mengisi deskripsi LKPS, tetapi kurang lengkap dan perlu melengkapi",
                'C' => "Prodi perlu memperbaiki isian LKPS",
            ],
            default => [
                'A' => "Prodi telah mengisi deskripsi LED dengan tepat",
                'B' => "Prodi telah mengisi deskripsi LED, tetapi kurang lengkap dan perlu melengkapi",
                'C' => "Prodi perlu memperbaiki isian LED",
            ],
        };
    }

    private function gradeToText(?string $grade, string $type): string
    {
        $grade = strtoupper(trim((string) $grade));
        $map = $this->getKategoriMap($type);
        return $map[$grade] ?? '';
    }

    private function addLogoAndZoom($sheet): void
    {
        // zoom 80%
        $sheet->getSheetView()->setZoomScale(80);

        // logo di B2:C3
        $path = public_path('assets/images/logo.png');
        if (file_exists($path)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo');
            $drawing->setPath($path);
            $drawing->setCoordinates('B2');

            // kira-kira agar muat B2:C3 (silakan tweak)
            $drawing->setHeight(40);
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(2);

            $drawing->setWorksheet($sheet);
        }
    }

    private function styleHeaderBase($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000'], // header kamu minta putih/hijau/abu2, teks enaknya hitam
                'size' => 11,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
    }

    private function fillRange($sheet, string $range, string $argb): void
    {
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($argb);
    }

    private function mergeReviewById(array $current, array $incoming): array
    {
        // paksa key jadi string biar konsisten (mencegah numeric append behavior)
        $normalize = function (array $arr): array {
            $out = [];
            foreach ($arr as $k => $v) {
                // kalau ada data model lama yang bentuknya list tanpa key,
                // skip saja (atau kamu bisa handle lebih kompleks)
                if (is_int($k) && isset($v['id'])) {
                    $out[(string)$v['id']] = $v;
                } else {
                    $out[(string)$k] = $v;
                }
            }
            return $out;
        };

        $currentN  = $normalize($current);
        $incomingN = $normalize($incoming);

        // overwrite incoming ke current (upsert)
        foreach ($incomingN as $id => $val) {
            $currentN[$id] = $val;
        }

        return $currentN;
    }
}
