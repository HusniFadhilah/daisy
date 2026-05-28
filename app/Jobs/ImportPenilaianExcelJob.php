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

    protected $modelPenilaianElemen;
    protected $penilaianName;
    protected $filePath;
    protected $idAsesmen;
    protected $userId;
    protected $importLogId;

    public function __construct($modelPenilaianElemen, $filePath, $idAsesmen, $userId, $importLogId)
    {
        $this->modelPenilaianElemen    = $modelPenilaianElemen;
        $map = [
            \App\Models\PenilaianElemenAl::class => 'AL',
            \App\Models\PenilaianElemenAk::class => 'AK',
            \App\Models\PenilaianElemenAlBanding::class => 'AL Banding',
            \App\Models\PenilaianElemenAkBanding::class => 'AK Banding',
        ];
        $this->penilaianName = $map[$modelPenilaianElemen] ?? null;
        $this->filePath    = $filePath;
        $this->idAsesmen   = $idAsesmen;
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

            $allowedSheets = [
                'Kertas Kerja AK Asesor',
                'Kertas Kerja AL Asesor',
                'Kertas Kerja Asesor AL',
                'Kertas Kerja Asesor AK',
                'Kertas Kerja Asesor AK Banding',
                'Kertas Kerja Asesor AL Banding',
                'Kertas Kerja AK Banding Asesor',
                'Kertas Kerja AL Banding Asesor',
            ];

            // kandidat nama sheet berdasarkan jenis penilaian saat ini
            $possibleTargets = array_values(array_intersect([
                'Kertas Kerja ' . $this->penilaianName . ' Asesor',
                'Kertas Kerja Asesor ' . $this->penilaianName,
            ], $allowedSheets));

            // semua nama sheet yang ada di file
            $existingSheetNames = array_map(
                fn($sheet) => trim($sheet->getTitle()),
                $spreadsheet->getAllSheets()
            );

            // cari sheet pertama yang cocok
            $foundSheetName = null;
            foreach ($possibleTargets as $candidate) {
                foreach ($existingSheetNames as $existing) {
                    if (strcasecmp(trim($candidate), trim($existing)) === 0) {
                        $foundSheetName = $existing;
                        break 2;
                    }
                }
            }

            // fallback: kalau nama target tidak ketemu, cari sheet mana pun yang termasuk whitelist
            if ($foundSheetName === null) {
                foreach ($existingSheetNames as $existing) {
                    foreach ($allowedSheets as $allowed) {
                        if (strcasecmp(trim($allowed), trim($existing)) === 0) {
                            $foundSheetName = $existing;
                            break 2;
                        }
                    }
                }
            }

            if ($foundSheetName === null) {
                Log::error(
                    'Tidak ditemukan sheet yang valid. Kandidat: ' . implode(', ', $possibleTargets) .
                        '. Sheet tersedia: ' . implode(', ', $existingSheetNames)
                );

                throw new \Exception(
                    'Sheet valid tidak ditemukan. ' .
                        'Kandidat yang dicari: ' . implode(', ', $possibleTargets) . '. ' .
                        'Sheet tersedia di file: ' . implode(', ', $existingSheetNames)
                );
            }

            $worksheet = $spreadsheet->getSheetByName($foundSheetName);

            if (!$worksheet) {
                throw new \Exception('Sheet "' . $foundSheetName . '" gagal dibuka.');
            }

            $totalRows    = 0;
            $importedRows = 0;
            $failedRows   = 0;
            $errors       = [];
            $errorsMessage       = [];

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
                    //    Karena format Excel: templat di baris genap, asesor di baris ganjil
                    $templateRow = $row - 1;

                    // Coba baca dari template row (yang pasti punya nilai)
                    $kodeElemen = $this->getCellValue($worksheet, "E{$templateRow}");

                    // Fallback: coba baris asesor jika merged cell bisa dibaca
                    if ($kodeElemen === '') {
                        $kodeElemen = $this->getCellValue($worksheet, "E{$row}");
                    }

                    if ($kodeElemen === '') {
                        $errorsMessage[] = "Row {$row}: Kode elemen tidak ditemukan (baris templat: {$templateRow})";
                        $errors[] = "Row {$row}: Kode elemen tidak ditemukan (baris templat: {$templateRow})";
                        $failedRows++;
                        Log::error("Row {$row}: Kode elemen kosong");
                        continue;
                    }

                    // Log::info("Row {$row}: Processing kode '{$kodeElemen}', skor {$skor}");

                    // 3) Cari elemen di database
                    $elemen = ElemenStandar::where('kode_elemen', $kodeElemen)->first();

                    if (!$elemen) {
                        $errorsMessage[] = "Row {$row}: Elemen dengan kode '{$kodeElemen}' tidak ditemukan di database";
                        $errors[] = "Row {$row}: Elemen dengan kode '{$kodeElemen}' tidak ditemukan di database";
                        $failedRows++;
                        // Log::error("Row {$row}: Elemen '{$kodeElemen}' not found in DB");
                        continue;
                    }

                    // 4) Simpan / update penilaian
                    $modelPenilaianElemen = $this->modelPenilaianElemen;
                    $modelPenilaianElemen::updateOrCreate(
                        [
                            'id_asesmen' => $this->idAsesmen,
                            'id_asesor'    => $this->userId,
                            'id_elemen'  => $elemen->id,
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
                    $errorsMessage[] = "Terjadi kegagalan membaca baris ke-{$row}";
                    $failedRows++;
                    Log::error("Error importing row {$row}: " . $e->getMessage());
                }
            }

            DB::commit();

            $importLog->update([
                'status'        => 'completed',
                'total_rows'    => $totalRows,
                'imported_rows' => $importedRows,
                'failed_rows'   => $failedRows,
                'errors'        => $errors,
                'errors_message'        => $errorsMessage,
                'completed_at'  => now(),
            ]);

            if (empty($errors) && empty($errorsMessage)) {
                Storage::delete($this->filePath);
            }

            // Log::info("Import completed: {$importedRows}/{$totalRows} rows imported");

        } catch (\Exception $e) {
            DB::rollBack();

            $importLog->update([
                'status'       => 'failed',
                'errors_message' => 'Terjadi kegagalan, silahkan coba lagi atau hubungi administrator',
                'errors'       => ['General error: ' . $e->getMessage()],
                'completed_at' => now(),
            ]);

            Log::error($e);

            //Storage::delete($this->filePath);

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
                'errors_message' => 'Terjadi kegagalan, silahkan coba lagi atau hubungi administrator',
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
