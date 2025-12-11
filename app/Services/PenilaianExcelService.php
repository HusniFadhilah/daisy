<?php

namespace App\Services;

use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\ElemenStandar;
use App\Models\PenilaianElemen;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PenilaianExcelService
{
    /**
     * Generate template Excel file (format kosong)
     */
    public function generateTemplate(Asesmen $asesmen): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kertas Kerja AK Asesor');

        // Set column widths
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

        // Build headers
        $this->buildHeaders($sheet, $asesmen);

        // Build data rows: 2 baris per elemen (template + baris asesor)
        $currentRow = 8;
        $kriterias = Kriteria::with([
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
        ])->get();

        foreach ($kriterias as $kriteria) {
            $isFirstElemen = true;

            foreach ($kriteria->elemenStandar as $index => $elemen) {
                $templateRow = $currentRow;       // baris teks IndikatorPenilaianElemen (I–M)
                $asesorRow   = $currentRow + 1;   // baris yang akan diisi asesor

                // --- 1) ROW TEMPLATE (8,10,12,...) ---

                $penilaianMap = $this->getPenilaianMapBySkor($elemen);

                $sheet->setCellValue(
                    "I{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[0] ?? null, 0)
                );
                $sheet->setCellValue(
                    "J{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[1] ?? null, 1)
                );
                $sheet->setCellValue(
                    "K{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[2] ?? null, 2)
                );
                $sheet->setCellValue(
                    "L{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[3] ?? null, 3)
                );
                $sheet->setCellValue(
                    "M{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[4] ?? null, 4)
                );

                // --- 2) ROW ASESOR (9,11,13,...) ---

                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$templateRow}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$templateRow}", $kriteria->nama_kriteria);
                }

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

                // kolom I–M baris asesor dikosongkan (akan diisi asesor)
                $sheet->setCellValue("I{$asesorRow}", '');
                $sheet->setCellValue("J{$asesorRow}", '');
                $sheet->setCellValue("K{$asesorRow}", '');
                $sheet->setCellValue("L{$asesorRow}", '');
                $sheet->setCellValue("M{$asesorRow}", '');

                // --- MERGE B–H ANTARA templateRow & asesorRow (seperti file contoh) ---

                $sheet->mergeCells("B{$templateRow}:B{$asesorRow}");
                $sheet->mergeCells("C{$templateRow}:C{$asesorRow}");
                $sheet->mergeCells("D{$templateRow}:D{$asesorRow}");
                $sheet->mergeCells("E{$templateRow}:E{$asesorRow}");
                $sheet->mergeCells("F{$templateRow}:F{$asesorRow}");
                $sheet->mergeCells("G{$templateRow}:G{$asesorRow}");
                $sheet->mergeCells("H{$templateRow}:H{$asesorRow}");

                // styling gabungan B–H dua baris
                $sheet->getStyle("B{$templateRow}:H{$asesorRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // border & wrap untuk kedua baris
                $this->applyRowStyling($sheet, $templateRow);
                // styling baris asesor
                $this->applyRowStyling($sheet, $asesorRow);

                // beri warna kuning (opsional)
                $sheet->getStyle("B{$asesorRow}:M{$asesorRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFF99');

                // PERBESAR TINGGI BARIS ASESOR
                $sheet->getRowDimension($asesorRow)->setRowHeight(60);

                // baris asesor kuning (seperti screenshot)
                $sheet->getStyle("B{$asesorRow}:M{$asesorRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFFCC');

                $currentRow += 2;
                $isFirstElemen = false;
            }
        }

        // Save file
        $filename = 'Template_Penilaian_AK_' . $asesmen->code . '_' . date('YmdHis') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Generate Excel file dengan hasil penilaian
     */
    public function generateWithData(Asesmen $asesmen, $userId): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kertas Kerja AK Asesor');

        // Set column widths
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

        // Build headers
        $this->buildHeaders($sheet, $asesmen);

        // Build data rows with penilaian (juga 2 baris per elemen)
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

                // --- 1) ROW TEMPLATE ---

                $penilaianMap = $this->getPenilaianMapBySkor($elemen);

                $sheet->setCellValue(
                    "I{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[0] ?? null, 0)
                );
                $sheet->setCellValue(
                    "J{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[1] ?? null, 1)
                );
                $sheet->setCellValue(
                    "K{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[2] ?? null, 2)
                );
                $sheet->setCellValue(
                    "L{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[3] ?? null, 3)
                );
                $sheet->setCellValue(
                    "M{$templateRow}",
                    $this->buildPenilaianInstruction($penilaianMap[4] ?? null, 4)
                );

                // --- 2) ROW ASESOR ---

                if ($isFirstElemen) {
                    $sheet->setCellValue("B{$templateRow}", $kriteria->kode_kriteria);
                    $sheet->setCellValue("C{$templateRow}", $kriteria->nama_kriteria);
                }

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

                // Ambil penilaian asesor
                $penilaian = PenilaianElemen::where('id_asesmen', $asesmen->id)
                    ->where('id_asesor', $userId)
                    ->where('id_elemen', $elemen->id)
                    ->first();

                // kosongkan kolom skor
                $sheet->setCellValue("I{$asesorRow}", '');
                $sheet->setCellValue("J{$asesorRow}", '');
                $sheet->setCellValue("K{$asesorRow}", '');
                $sheet->setCellValue("L{$asesorRow}", '');
                $sheet->setCellValue("M{$asesorRow}", '');

                if ($penilaian && $penilaian->skor !== null) {
                    $scoreColumn = chr(73 + $penilaian->skor); // I=0, J=1, K=2, L=3, M=4
                    $sheet->setCellValue("{$scoreColumn}{$asesorRow}", $penilaian->komentar);

                    $sheet->getStyle("{$scoreColumn}{$asesorRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFFFEB3B');
                }

                // --- MERGE B–H ANTARA templateRow & asesorRow ---

                $sheet->mergeCells("B{$templateRow}:B{$asesorRow}");
                $sheet->mergeCells("C{$templateRow}:C{$asesorRow}");
                $sheet->mergeCells("D{$templateRow}:D{$asesorRow}");
                $sheet->mergeCells("E{$templateRow}:E{$asesorRow}");
                $sheet->mergeCells("F{$templateRow}:F{$asesorRow}");
                $sheet->mergeCells("G{$templateRow}:G{$asesorRow}");
                $sheet->mergeCells("H{$templateRow}:H{$asesorRow}");

                $sheet->getStyle("B{$templateRow}:H{$asesorRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $this->applyRowStyling($sheet, $templateRow);
                // styling baris asesor
                $this->applyRowStyling($sheet, $asesorRow);

                // beri warna kuning (opsional)
                $sheet->getStyle("B{$asesorRow}:M{$asesorRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFF99');

                // PERBESAR TINGGI BARIS ASESOR
                $sheet->getRowDimension($asesorRow)->setRowHeight(60);

                $sheet->getStyle("B{$asesorRow}:M{$asesorRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFFFCC');

                $currentRow += 2;
                $isFirstElemen = false;
            }
        }

        // Save file
        $filename = 'Penilaian_AK_' . $asesmen->code . '_' . date('YmdHis') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    /**
     * Build Excel headers
     */
    private function buildHeaders($sheet, $asesmen): void
    {
        $sheet->mergeCells('B2:M2');
        $sheet->setCellValue('B2', 'Lembaga Akreditasi Mandiri Desain Perencanaan Lingkungan Arsitektur | LAMDEPILAR');
        $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('B3:M3');
        $sheet->setCellValue('B3', 'Tabel 2. Kertas Kerja Asesor - ' . $asesmen->name);
        $sheet->getStyle('B3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('B5:C5');
        $sheet->setCellValue('B5', 'Kriteria');

        $sheet->mergeCells('D5:E5');
        $sheet->setCellValue('D5', 'Kode Elemen');

        $sheet->setCellValue('F5', 'Elemen Standar');

        $sheet->mergeCells('G5:H5');
        $sheet->setCellValue('G5', 'Indikator');

        $sheet->mergeCells('I5:M5');
        $sheet->setCellValue('I5', 'Penilaian');

        $sheet->setCellValue('G6', 'Kualitatif');
        $sheet->setCellValue('H6', 'Kuantitatif');
        $sheet->setCellValue('I6', 'Tidak Memenuhi (Not Met)');
        $sheet->setCellValue('J6', 'Tidak Memenuhi (Not Met)');
        $sheet->setCellValue('K6', 'Lemah (Weakness/Cause of Concern)');
        $sheet->setCellValue('L6', 'Memenuhi (Met)');
        $sheet->setCellValue('M6', 'Pelampauan Standar');

        $sheet->setCellValue('I7', '0');
        $sheet->setCellValue('J7', '1');
        $sheet->setCellValue('K7', '2');
        $sheet->setCellValue('L7', '3');
        $sheet->setCellValue('M7', '4');

        $headerRange = 'B5:M7';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

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
