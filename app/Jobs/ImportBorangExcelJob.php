<?php

namespace App\Jobs;

use App\Models\BorangImport;
use App\Services\BorangImport\BorangExcelImportService;
use App\Services\BorangImport\BorangExcelSheetParser;
use App\Services\BorangImport\DatasetIdResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportBorangExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 3;

    // Sheet yang di-skip (bukan data, hanya info)
    private const SKIP_SHEETS = ['Daftar Isi', 'Petunjuk', 'Cover'];

    public function __construct(
        protected int $borangImportId,
        protected string $filePath,
        protected int $pengajuanId,
        protected int $userId,
        protected ?int $degreeLevelId = null,
    ) {}

    public function handle(): void
    {
        $import = BorangImport::findOrFail($this->borangImportId);
        $import->markAsProcessing();

        try {
            $fullPath = Storage::path($this->filePath);
            $spreadsheet = IOFactory::load($fullPath);

            $service = new BorangExcelImportService(
                new BorangExcelSheetParser(),
                new DatasetIdResolver()
            );

            $totalSheets = 0;
            $totalTables = 0;
            $totalRows = 0;
            $allErrors = [];

            DB::beginTransaction();

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                if (in_array($sheetName, self::SKIP_SHEETS)) continue;

                $ws = $spreadsheet->getSheetByName($sheetName);
                $stats = $service->processSheet(
                    $ws,
                    $sheetName,
                    $this->pengajuanId,
                    $this->borangImportId,
                    $this->degreeLevelId
                );

                $totalSheets++;
                $totalTables += $stats['tables'];
                $totalRows += $stats['rows'];

                if (!empty($stats['errors'])) {
                    $allErrors = array_merge($allErrors, $stats['errors']);
                }

                // Update progress realtime (opsional: via event/broadcast)
                $import->update([
                    'parsed_sections' => $totalSheets,
                    'parsed_tables' => $totalTables,
                ]);
            }

            DB::commit();

            $import->update([
                'status' => 'completed',
                'total_sections' => $totalSheets,
                'total_tables' => $totalTables,
                'parsed_sections' => $totalSheets,
                'parsed_tables' => $totalTables,
                'parsing_errors' => $allErrors ?: null,
                'parsing_notes' => "Berhasil import {$totalTables} tabel dari {$totalSheets} sheet.",
                'completed_at' => now(),
            ]);

            Storage::delete($this->filePath);
        } catch (\Exception $e) {
            DB::rollBack();
            $import->markAsFailed(['Fatal: ' . $e->getMessage()]);
            Log::error('ImportBorangExcelJob failed: ' . $e->getMessage());
            Storage::delete($this->filePath);
            throw $e;
        }
    }

    public function failed(\Throwable $e): void
    {
        BorangImport::find($this->borangImportId)?->markAsFailed([
            'Job failed: ' . $e->getMessage(),
        ]);
    }
}
