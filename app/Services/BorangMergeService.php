<?php
// app/Services/BorangMergeService.php

namespace App\Services;

use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as ExcelIOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Html as HtmlWriter;

class BorangMergeService
{
    /**
     * Merge semua file borang menjadi satu PDF
     */
    public function mergeBorangFiles(PengajuanAkreditasi $pengajuan): array
    {
        try {
            Log::info("Starting merge for pengajuan: {$pengajuan->id}");

            // ✅ Get uploaded files
            $pengesahan = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'pengesahan')
                ->latest()
                ->first();

            $kualitatif = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'file_kualitatif')
                ->latest()
                ->first();

            $kuantitatif = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'file_kuantitatif')
                ->latest()
                ->first();

            // ✅ Validate all files exist
            if (!$pengesahan || !$kualitatif || !$kuantitatif) {
                throw new \Exception('File belum lengkap');
            }

            // ✅ Create temp directory
            $tempDir = storage_path('app/temp/merge_' . $pengajuan->id . '_' . time());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $pdfFiles = [];

            // ✅ 1. PDF Pengesahan (already PDF)
            $pengesahanPath = Storage::disk('public')->path($pengesahan->path_file);
            if (file_exists($pengesahanPath)) {
                $pdfFiles[] = [
                    'path' => $pengesahanPath,
                    'title' => 'Lembar Pengesahan'
                ];
                Log::info("Added pengesahan PDF: {$pengesahanPath}");
            }

            // ✅ 2. DOCX → PDF (Laporan Evaluasi Diri)
            $kualitatifPath = Storage::disk('public')->path($kualitatif->path_file);
            if (file_exists($kualitatifPath)) {
                $kualitatifPdf = $this->convertDocxToPdf($kualitatifPath, $tempDir);
                if ($kualitatifPdf) {
                    $pdfFiles[] = [
                        'path' => $kualitatifPdf,
                        'title' => 'Laporan Evaluasi Diri'
                    ];
                    Log::info("Converted kualitatif DOCX to PDF: {$kualitatifPdf}");
                }
            }

            // ✅ 3. Excel → PDF (LKPS)
            $kuantitatifPath = Storage::disk('public')->path($kuantitatif->path_file);
            if (file_exists($kuantitatifPath)) {
                $kuantitatifPdf = $this->convertExcelToPdf($kuantitatifPath, $tempDir);
                if ($kuantitatifPdf) {
                    $pdfFiles[] = [
                        'path' => $kuantitatifPdf,
                        'title' => 'LKPS'
                    ];
                    Log::info("Converted kuantitatif Excel to PDF: {$kuantitatifPdf}");
                }
            }

            // ✅ 4. Merge all PDFs
            $mergedPdf = $this->mergePdfs($pdfFiles, $tempDir);

            // ✅ 5. Save to storage
            $finalFilename = 'BORANG_LENGKAP_' . $pengajuan->nomor_pengajuan . '_' . date('YmdHis') . '.pdf';
            $finalPath = 'borang_merged/' . $finalFilename;

            // Ensure directory exists
            $mergedDir = storage_path('app/public/borang_merged');
            if (!file_exists($mergedDir)) {
                mkdir($mergedDir, 0755, true);
            }

            Storage::disk('public')->put(
                $finalPath,
                file_get_contents($mergedPdf)
            );

            $fileSize = filesize($mergedPdf);

            // ✅ 6. Create dokumen record
            PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'borang_merged',
                'path_file' => $finalPath,
                'original_filename' => $finalFilename,
                'file_size' => $fileSize,
                'uploaded_by' => auth()->id(),
            ]);

            // ✅ Cleanup temp files
            $this->cleanupTempDir($tempDir);

            Log::info("Merge completed: {$finalPath}");

            return [
                'success' => true,
                'file_path' => $finalPath,
                'filename' => $finalFilename,
                'file_size' => $fileSize,
                'url' => Storage::disk('public')->url($finalPath),
            ];
        } catch (\Exception $e) {
            Log::error('Merge failed: ' . $e->getMessage(), [
                'pengajuan_id' => $pengajuan->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Cleanup on error
            if (isset($tempDir) && file_exists($tempDir)) {
                $this->cleanupTempDir($tempDir);
            }

            throw $e;
        }
    }

    /**
     * ✅ Convert DOCX to PDF using DomPDF
     */
    private function convertDocxToPdf(string $docxPath, string $outputDir): ?string
    {
        try {
            Log::info("Converting DOCX to PDF: {$docxPath}");

            // ✅ Load DOCX using PhpWord
            $phpWord = WordIOFactory::load($docxPath);

            // ✅ Convert to HTML
            $htmlPath = $outputDir . '/' . basename($docxPath, '.docx') . '.html';
            $htmlWriter = WordIOFactory::createWriter($phpWord, 'HTML');
            $htmlWriter->save($htmlPath);

            // ✅ Load HTML content
            $htmlContent = file_get_contents($htmlPath);

            // ✅ Clean up HTML (remove problematic elements)
            $htmlContent = $this->cleanHtmlForPdf($htmlContent);

            // ✅ Convert HTML to PDF using DomPDF
            $outputPdf = $outputDir . '/' . basename($docxPath, '.docx') . '.pdf';

            $pdf = Pdf::loadHTML($htmlContent)
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true)
                ->setOption('defaultFont', 'sans-serif');

            $pdf->save($outputPdf);

            // Cleanup HTML file
            if (file_exists($htmlPath)) {
                unlink($htmlPath);
            }

            Log::info("DOCX converted successfully: {$outputPdf}");
            return $outputPdf;
        } catch (\Exception $e) {
            Log::error("DOCX to PDF conversion failed: " . $e->getMessage(), [
                'file' => $docxPath,
                'trace' => $e->getTraceAsString()
            ]);

            // ✅ Fallback: Create placeholder PDF
            return $this->createPlaceholderPdf(
                'Laporan Evaluasi Diri',
                basename($docxPath),
                $outputDir . '/laporan_evaluasi_diri.pdf'
            );
        }
    }

    /**
     * ✅ Convert Excel to PDF using DomPDF
     */
    private function convertExcelToPdf(string $excelPath, string $outputDir): ?string
    {
        try {
            Log::info("Converting Excel to PDF: {$excelPath}");

            // ✅ Load Excel using PhpSpreadsheet
            $spreadsheet = ExcelIOFactory::load($excelPath);

            // ✅ Convert to HTML
            $htmlWriter = new HtmlWriter($spreadsheet);
            $htmlContent = $htmlWriter->generateHTMLAll();

            // ✅ Clean up HTML
            $htmlContent = $this->cleanHtmlForPdf($htmlContent);

            // ✅ Convert HTML to PDF using DomPDF
            $outputPdf = $outputDir . '/' . basename($excelPath, '.xlsx') . '.pdf';

            $pdf = Pdf::loadHTML($htmlContent)
                ->setPaper('a4', 'landscape') // Excel biasanya landscape
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', true);

            $pdf->save($outputPdf);

            Log::info("Excel converted successfully: {$outputPdf}");
            return $outputPdf;
        } catch (\Exception $e) {
            Log::error("Excel to PDF conversion failed: " . $e->getMessage(), [
                'file' => $excelPath,
                'trace' => $e->getTraceAsString()
            ]);

            // ✅ Fallback: Create placeholder PDF
            return $this->createPlaceholderPdf(
                'LKPS (Data Kuantitatif)',
                basename($excelPath),
                $outputDir . '/lkps.pdf'
            );
        }
    }

    /**
     * ✅ Clean HTML for better PDF conversion
     */
    private function cleanHtmlForPdf(string $html): string
    {
        // Remove problematic CSS
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

        // Add basic styling
        $css = '<style>
            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 11pt;
                line-height: 1.5;
            }
            table {
                border-collapse: collapse;
                width: 100%;
                margin: 10px 0;
                page-break-inside: auto;
            }
            td, th {
                border: 1px solid #000;
                padding: 8px;
                page-break-inside: avoid;
            }
            th {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            tr {
                page-break-inside: avoid;
            }
            p {
                margin: 5px 0;
            }
        </style>';

        // Inject CSS at the beginning of body or html
        if (strpos($html, '<body') !== false) {
            $html = preg_replace('/<body[^>]*>/', '$0' . $css, $html);
        } else {
            $html = $css . $html;
        }

        return $html;
    }

    /**
     * ✅ Merge multiple PDFs into one using FPDI
     */
    private function mergePdfs(array $pdfFiles, string $outputDir): string
    {
        $pdf = new Fpdi();

        foreach ($pdfFiles as $fileInfo) {
            $file = $fileInfo['path'];

            if (!file_exists($file)) {
                Log::warning("PDF file not found: {$file}");
                continue;
            }

            try {
                $pageCount = $pdf->setSourceFile($file);

                Log::info("Processing {$fileInfo['title']}: {$pageCount} pages from {$file}");

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    // Get page dimensions
                    $template = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($template);

                    // Add page with same orientation as source
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);

                    // Use imported page
                    $pdf->useTemplate($template);

                    Log::info("Added page {$pageNo}/{$pageCount} from {$fileInfo['title']}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to add PDF {$fileInfo['title']}: " . $e->getMessage(), [
                    'file' => $file,
                    'trace' => $e->getTraceAsString()
                ]);

                // Add error page
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 16);
                $pdf->Cell(0, 10, 'Error: Gagal memproses ' . $fileInfo['title'], 0, 1);
                $pdf->SetFont('Arial', '', 12);
                $pdf->MultiCell(0, 10, 'File: ' . basename($file) . "\n\nSilakan cek file asli.");
            }
        }

        $outputFile = $outputDir . '/merged.pdf';
        $pdf->Output('F', $outputFile);

        Log::info("Merged PDF created: {$outputFile}");

        return $outputFile;
    }

    /**
     * ✅ Create placeholder PDF when conversion fails
     */
    private function createPlaceholderPdf(string $title, string $filename, string $outputPath): string
    {
        $html = "
            <div style='text-align: center; margin-top: 100px;'>
                <h1>{$title}</h1>
                <p style='margin-top: 50px;'>File: <strong>{$filename}</strong></p>
                <p style='margin-top: 20px; color: #666;'>
                    Catatan: File tidak dapat dikonversi otomatis.<br>
                    Silakan lihat file asli untuk detail lengkap.
                </p>
            </div>
        ";

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $pdf->save($outputPath);

        return $outputPath;
    }

    /**
     * ✅ Cleanup temporary directory
     */
    private function cleanupTempDir(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        try {
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($dir);
            Log::info("Cleaned up temp directory: {$dir}");
        } catch (\Exception $e) {
            Log::warning("Failed to cleanup temp directory: " . $e->getMessage());
        }
    }

    public function checkFilesComplete(PengajuanAkreditasi $pengajuan): array
    {
        // ✅ KONSISTEN: Gunakan jenis_dokumen yang sama
        $pengesahan = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'pengesahan') // ✅ Konsisten
            ->where('is_latest', true) // ✅ Tambahkan check is_latest
            ->latest()
            ->first();

        $kualitatif = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'file_kualitatif') // ✅ Konsisten
            ->where('is_latest', true)
            ->latest()
            ->first();

        $kuantitatif = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'file_kuantitatif') // ✅ Konsisten
            ->where('is_latest', true)
            ->latest()
            ->first();

        $uploaded = [];
        $missing = [];

        if ($pengesahan) {
            $uploaded[] = 'pengesahan';
        } else {
            $missing[] = 'pengesahan';
        }

        if ($kualitatif) {
            $uploaded[] = 'kualitatif';
        } else {
            $missing[] = 'kualitatif';
        }

        if ($kuantitatif) {
            $uploaded[] = 'kuantitatif';
        } else {
            $missing[] = 'kuantitatif';
        }

        return [
            'complete' => count($missing) === 0,
            'uploaded' => $uploaded,
            'missing' => $missing,
            'files' => [
                'pengesahan' => $pengesahan ? [
                    'id' => $pengesahan->id,
                    'filename' => $pengesahan->original_filename,
                    'path' => $pengesahan->path_file, // ✅ Konsisten: path_file
                    'size' => $pengesahan->file_size,
                ] : null,
                'kualitatif' => $kualitatif ? [
                    'id' => $kualitatif->id,
                    'filename' => $kualitatif->original_filename,
                    'path' => $kualitatif->path_file, // ✅ Konsisten
                    'size' => $kualitatif->file_size,
                ] : null,
                'kuantitatif' => $kuantitatif ? [
                    'id' => $kuantitatif->id,
                    'filename' => $kuantitatif->original_filename,
                    'path' => $kuantitatif->path_file, // ✅ Konsisten
                    'size' => $kuantitatif->file_size,
                ] : null,
            ]
        ];
    }
}
