<?php

// ============================================================
// app/Http/Controllers/BorangLkpsExportController.php
// ============================================================

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Services\BorangExport\LkpsExcelExportService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BorangLkpsExportController extends Controller
{
    use AuthorizesRequests;
    public function __construct(
        private LkpsExcelExportService $exportService
    ) {}

    /**
     * Download template LKPS kosong (tanpa data)
     */
    public function downloadTemplate(PengajuanAkreditasi $pengajuan)
    {
        // $this->authorize('view', $pengajuan);

        $path = $this->exportService->generate($pengajuan, fillData: false);
        return $path;

        $filename = 'Template_LKPS_S1_' . $pengajuan->studyProgram->kode_prodi . '.xlsx';
        // return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Export LKPS terisi dari data yang sudah diinput
     */
    public function exportFilled(PengajuanAkreditasi $pengajuan): BinaryFileResponse
    {
        $this->authorize('view', $pengajuan);

        $path = $this->exportService->generate($pengajuan, fillData: true);

        $filename = 'LKPS_S1_' . $pengajuan->studyProgram->kode_prodi
            . '_' . now()->format('Ymd') . '.xlsx';

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }
}
