<?php

namespace App\Jobs;

use App\Models\ElemenStandar;
use App\Models\PenilaianElemen;
use App\Models\PenilaianImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportPenilaianExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries   = 3;

    protected $filePath;
    protected $asesmenId;
    protected $userId;
    protected $importLogId;

    public function __construct($filePath, $asesmenId, $userId, $importLogId)
    {
        $this->filePath    = $filePath;
        $this->asesmenId   = $asesmenId;
        $this->userId      = $userId;
        $this->importLogId = $importLogId;
    }

    public function handle(): void
    {
        $importLog = PenilaianImportLog::find($this->importLogId);

        if (!$importLog) {
            Log::error("Import log not found: {$this->importLogId}");
            return;
        }

        try {
            $importLog->update([
                'status'     => 'processing',
                'started_at' => now(),
            ]);

            $fullPath    = Storage::path($this->filePath);
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet   = $spreadsheet->getSheetByName('Kertas Kerja AK Asesor');

            if (!$worksheet) {
                throw new \Exception('Sheet "Kertas Kerja AK Asesor" tidak ditemukan');
            }

            $totalRows    = 0;
            $importedRows = 0;
            $failedRows   = 0;
            $errors       = [];

            $highestRow = $worksheet->getHighestRow();

            DB::beginTransaction();

            // ✅ FIX: Loop hanya baris ASESOR (9, 11, 13, ...) - baris GANJIL setelah row 8
            // Baris 8 = template pertama, 9 = asesor pertama
            // Baris 10 = template kedua, 11 = asesor kedua, dst

            for ($row = 9; $row <= $highestRow; $row += 2) {
                $totalRows++; // ✅ FIX: Increment di awal untuk tracking

                try {
                    // 1) Baca isi kolom penilaian (I–M) pada baris ASESOR ini
                    $penilaianData = [
                        0 => $worksheet->getCell("I{$row}")->getValue(),
                        1 => $worksheet->getCell("J{$row}")->getValue(),
                        2 => $worksheet->getCell("K{$row}")->getValue(),
                        3 => $worksheet->getCell("L{$row}")->getValue(),
                        4 => $worksheet->getCell("M{$row}")->getValue(),
                    ];

                    $skor     = null;
                    $komentar = null;

                    // Cari kolom mana yang diisi
                    foreach ($penilaianData as $score => $value) {
                        if (is_string($value)) {
                            $value = trim($value);
                        }

                        if ($value !== null && $value !== '' && !$this->isPlaceholderText($value)) {
                            $skor     = $score;
                            $komentar = $value;
                            break;
                        }
                    }

                    // Jika baris ini tidak punya penilaian valid, lewati
                    if ($skor === null || $komentar === null || $komentar === '') {
                        // Log::info("Row {$row}: Tidak ada penilaian, skip");
                        continue;
                    }

                    // 2) ✅ FIX: Ambil kode elemen dari TEMPLATE ROW (row - 1)
                    //    Karena format Excel: template di baris genap, asesor di baris ganjil
                    $templateRow = $row - 1;

                    // Coba baca dari template row (yang pasti punya nilai)
                    $kodeElemen = $this->getCellValue($worksheet, "E{$templateRow}");

                    // Fallback: coba baris asesor jika merged cell bisa dibaca
                    if ($kodeElemen === '') {
                        $kodeElemen = $this->getCellValue($worksheet, "E{$row}");
                    }

                    if ($kodeElemen === '') {
                        $errors[] = "Row {$row}: Kode elemen tidak ditemukan (template row: {$templateRow})";
                        $failedRows++;
                        Log::error("Row {$row}: Kode elemen kosong");
                        continue;
                    }

                    // Log::info("Row {$row}: Processing kode '{$kodeElemen}', skor {$skor}");

                    // 3) Cari elemen di database
                    $elemen = ElemenStandar::where('kode_elemen', $kodeElemen)->first();

                    if (!$elemen) {
                        $errors[] = "Row {$row}: Elemen dengan kode '{$kodeElemen}' tidak ditemukan di database";
                        $failedRows++;
                        // Log::error("Row {$row}: Elemen '{$kodeElemen}' not found in DB");
                        continue;
                    }

                    // 4) Simpan / update penilaian
                    PenilaianElemen::updateOrCreate(
                        [
                            'id_asesmen' => $this->asesmenId,
                            'id_user'    => $this->userId,
                            'id_elemen'  => $elemen->id_elemen,
                        ],
                        [
                            'skor'     => $skor,
                            'komentar' => $komentar,
                            'status'   => 'draft',
                        ]
                    );

                    $importedRows++;
                    // Log::info("Row {$row}: Successfully imported kode '{$kodeElemen}'");

                } catch (\Exception $e) {
                    $errors[] = "Row {$row}: " . $e->getMessage();
                    $failedRows++;
                    // Log::error("Error importing row {$row}: " . $e->getMessage());
                }
            }

            DB::commit();

            $importLog->update([
                'status'        => 'completed',
                'total_rows'    => $totalRows,
                'imported_rows' => $importedRows,
                'failed_rows'   => $failedRows,
                'errors'        => $errors,
                'completed_at'  => now(),
            ]);

            Storage::delete($this->filePath);

            // Log::info("Import completed: {$importedRows}/{$totalRows} rows imported");

        } catch (\Exception $e) {
            DB::rollBack();

            $importLog->update([
                'status'       => 'failed',
                'errors'       => ['General error: ' . $e->getMessage()],
                'completed_at' => now(),
            ]);

            Log::error('Import failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            Storage::delete($this->filePath);

            throw $e;
        }
    }

    /**
     * ✅ NEW: Helper untuk baca cell dengan berbagai method
     */
    private function getCellValue($worksheet, $coordinate): string
    {
        try {
            $cell = $worksheet->getCell($coordinate);

            // Coba getCalculatedValue() dulu (untuk formula)
            $value = $cell->getCalculatedValue();

            // Jika null/kosong, coba getValue()
            if ($value === null || $value === '') {
                $value = $cell->getValue();
            }

            // Jika masih kosong dan ini merged cell, coba ambil dari master cell
            if (($value === null || $value === '') && $cell->isInMergeRange()) {
                $mergeRange = $cell->getMergeRange();
                if ($mergeRange) {
                    // Ambil cell pertama dari merge range (master cell)
                    preg_match('/^([A-Z]+)(\d+):/', $mergeRange, $matches);
                    if (isset($matches[1]) && isset($matches[2])) {
                        $masterCell = $matches[1] . $matches[2];
                        $value = $worksheet->getCell($masterCell)->getValue();
                    }
                }
            }

            return trim((string) $value);

        } catch (\Exception $e) {
            Log::error("Error reading cell {$coordinate}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Check if text is placeholder/template instruction
     */
    private function isPlaceholderText($text): bool
    {
        if ($text === null) {
            return true;
        }

        $text = trim((string) $text);

        if ($text === '') {
            return true;
        }

        $placeholders = [
            'Tuliskan pernyataan penilaian',
            'tuliskan pernyataan penilaian',
            'Tidak ada dokumen legalitas',
            'Tidak ada',
            'tidak ada',
            'None',
            'null',
            'N/A',
            'n/a',
        ];

        foreach ($placeholders as $placeholder) {
            if (stripos($text, $placeholder) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        $importLog = PenilaianImportLog::find($this->importLogId);

        if ($importLog) {
            $importLog->update([
                'status'       => 'failed',
                'errors'       => [
                    'Job failed: ' . $exception->getMessage(),
                    'File: ' . $exception->getFile(),
                    'Line: ' . $exception->getLine(),
                ],
                'completed_at' => now(),
            ]);
        }

        Log::error('Import job failed: ' . $exception->getMessage());
        Log::error('Stack trace: ' . $exception->getTraceAsString());
    }
}
