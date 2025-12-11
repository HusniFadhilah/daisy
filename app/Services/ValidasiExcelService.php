<?php

namespace App\Services;

use App\Models\User;
use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\ElemenStandar;
use App\Models\PenilaianElemen;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ValidasiExcelService
{
    /**
     * Generate template Excel file (format kosong)
     */
    public static function generateTemplate(Asesmen $asesmen, $asesor1, $asesor2)
    {
        $idAsesmen = $asesmen->id;
        $asesor1Id = $asesor1->id;
        $asesor2Id = $asesor2->id;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Perbandingan Penilaian');

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);

        // Header
        $sheet->setCellValue('A1', 'KERTAS KERJA VALIDATOR');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Asesmen: ' . $asesmen->name);
        $sheet->mergeCells('A2:I2');

        $sheet->setCellValue('A3', 'Asesor 1: ' . $asesor1->name);
        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('E3', 'Asesor 2: ' . $asesor2->name);
        $sheet->mergeCells('E3:I3');

        // Table header
        $row = 5;
        $headers = [
            'Kriteria',
            'Kode Elemen',
            'Elemen Standar',
            'Indikator',
            'Asesor 1 Pemenuhan',
            'Asesor 1 Pelampauan',
            'Asesor 2 Pemenuhan',
            'Asesor 2 Pelampauan',
            'Status Validasi'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF932136');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($col . $row)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $col++;
        }

        // Data
        $row = 6;
        $kriterias = Kriteria::with([
            'elemenStandar.indikator',
            'elemenStandar.penilaian' => function ($query) use ($idAsesmen, $asesor1Id, $asesor2Id) {
                $query->where('id_asesmen', $idAsesmen)
                    ->whereIn('id_asesor', [$asesor1Id, $asesor2Id]);
            }
        ])->get();

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemen) {
                // Get penilaian
                $penilaian1 = $elemen->penilaian->where('id_asesor', $asesor1Id)->first();
                $penilaian2 = $elemen->penilaian->where('id_asesor', $asesor2Id)->first();

                // Indikator
                $indikatorText = $elemen->indikator->map(function ($ind) {
                    return $ind->kode_indikator . ': ' . $ind->deskripsi_indikator;
                })->implode("\n");

                $sheet->setCellValue('A' . $row, $kriteria->kode_kriteria);
                $sheet->setCellValue('B' . $row, $elemen->kode_elemen);
                $sheet->setCellValue('C' . $row, $elemen->pernyataan_elemen);
                $sheet->setCellValue('D' . $row, $indikatorText);

                // Asesor 1
                if ($penilaian1) {
                    if ($penilaian1->skor != 4) {
                        $sheet->setCellValue('E' . $row, $penilaian1->skor);
                        self::applySkorColor($sheet, 'E' . $row, $penilaian1->skor);
                    }
                    if ($penilaian1->skor == 4) {
                        $sheet->setCellValue('F' . $row, 4);
                        self::applySkorColor($sheet, 'F' . $row, 4);
                    }
                }

                // Asesor 2
                if ($penilaian2) {
                    if ($penilaian2->skor != 4) {
                        $sheet->setCellValue('G' . $row, $penilaian2->skor);
                        self::applySkorColor($sheet, 'G' . $row, $penilaian2->skor);
                    }
                    if ($penilaian2->skor == 4) {
                        $sheet->setCellValue('H' . $row, 4);
                        self::applySkorColor($sheet, 'H' . $row, 4);
                    }
                }

                // Status validasi
                $status = '-';
                if ($penilaian1 || $penilaian2) {
                    $validasi = $penilaian1 ?? $penilaian2;
                    if ($validasi->status_validasi == 'validated') {
                        $status = 'Disetujui';
                        $sheet->getStyle('I' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFE8F5E9');
                    } elseif ($validasi->status_validasi == 'revision_required') {
                        $status = 'Perlu Revisi';
                        $sheet->getStyle('I' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFFFF3E0');
                    }
                }
                $sheet->setCellValue('I' . $row, $status);

                // Text wrapping
                $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
                $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);

                $row++;
            }
        }

        // Borders
        $sheet->getStyle('A5:I' . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        // Create writer and download
        $writer = new Xlsx($spreadsheet);
        $filename = 'Perbandingan_Penilaian_' . date('YmdHis') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);

        $writer->save($tempFile);

        return [$tempFile, $filename];
    }

    /**
     * Helper: Apply color based on skor
     */
    private static function applySkorColor($sheet, $cell, $skor)
    {
        $colors = [
            0 => 'FFF44336', // Red
            1 => 'FFFF9800', // Orange
            2 => 'FFFFEB3B', // Yellow
            3 => 'FF8BC34A', // Light Green
            4 => 'FF4CAF50', // Dark Green
        ];

        $color = $colors[$skor] ?? 'FFE0E0E0';

        $sheet->getStyle($cell)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($color);

        $sheet->getStyle($cell)->getFont()->setBold(true);
        $sheet->getStyle($cell)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
    }
}
