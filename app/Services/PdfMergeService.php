<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PdfMergeService
{
    public function merge(array $inputFiles, string $outputFile, array $options = []): string
    {
        $ctx = [
            'output' => $outputFile,
            'count'  => count($inputFiles),
        ];

        try {
            $pdf = new Fpdi();

            foreach ($inputFiles as $file) {
                $this->assertReadablePdf($file);

                // OPTIONAL: kalau memang sering gagal karena PDF versi tinggi / compressed
                if (($options['force_gs_compat'] ?? false) === true) {
                    $file = $this->convertWithGhostscript($file);
                }

                $pageCount = $pdf->setSourceFile($file);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $tplId = $pdf->importPage($i);
                    $size  = $pdf->getTemplateSize($tplId);
                    $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

                    $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                    $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);
                }
            }

            $this->ensureDir(dirname($outputFile));
            $pdf->Output('F', $outputFile);

            return $outputFile;
        } catch (\Throwable $e) {
            Log::error('PDF merge failed', $ctx + [
                'error' => $e->getMessage(),
            ]);
            throw $e; // biar queue bisa retry
        }
    }

    private function assertReadablePdf(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            throw new \RuntimeException("PDF not readable: {$file}");
        }
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }

    private function convertWithGhostscript(string $file): string
    {
        $gs = config('pdf.gs_bin', 'gs');
        $outDir = dirname($file) . DIRECTORY_SEPARATOR . 'processed';
        $this->ensureDir($outDir);

        $outFile = $outDir . DIRECTORY_SEPARATOR . basename($file);

        // Pakai Symfony Process (lebih aman dari exec/shell injection)
        $process = new Process([
            $gs,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=' . config('pdf.compat_level', '1.4'),
            '-dPDFSETTINGS=/default',
            '-dNOPAUSE',
            '-dBATCH',
            '-dQUIET',
            '-sOutputFile=' . $outFile,
            $file,
        ]);

        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Ghostscript failed: " . $process->getErrorOutput());
        }

        return $outFile;
    }
}
