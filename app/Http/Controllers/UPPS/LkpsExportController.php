<?php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\BorangDataExcel;
use App\Models\PengajuanAkreditasi;
use App\Services\BorangExport\LkpsTemplateExcelExportService;

class LkpsExportController extends Controller
{
    public function export(PengajuanAkreditasi $pengajuan, LkpsTemplateExcelExportService $svc)
    {
        $path = $svc->exportFromTemplate($pengajuan);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function preview(int $pengajuanId)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($pengajuanId);

        $data = BorangDataExcel::query()
            ->where('id_pengajuan', $pengajuanId)
            // ->orderBy('sheet_name')
            ->orderBy('table_index')
            ->get()
            ->groupBy('sheet_name');

        $activeSheet = request('sheet') ?? $data->keys()->first();
        // return $activeSheet;
        return view('lkps.preview', [
            'pengajuan' => $pengajuan,
            'data' => $data,
            'activeSheet' => $activeSheet,
        ]);
    }
}
