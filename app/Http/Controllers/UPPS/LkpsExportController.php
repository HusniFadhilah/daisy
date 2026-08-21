<?php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\BorangDataExcel;
use App\Models\PengajuanAkreditasi;
use App\Repositories\SyaratAkreditasiRepository;
use App\Services\BorangExport\LkpsTemplateExcelExportService;
use App\Services\LkpsDataReaderService;
use Illuminate\Support\Facades\Auth;

class LkpsExportController extends Controller
{
    public function __construct(
        private readonly LkpsDataReaderService      $lkpsReader,
        private readonly SyaratAkreditasiRepository $syaratRepo,
    ) {}

    // =========================================================
    // EXPORT
    // =========================================================

    public function export(PengajuanAkreditasi $pengajuan, LkpsTemplateExcelExportService $svc)
    {
        $studyProgramIds = Auth::user()->studyPrograms()->pluck('study_programs.id');
        abort_unless($studyProgramIds->contains($pengajuan->id_program_studi), 403, 'Anda tidak memiliki akses ke pengajuan ini.');

        $path = $svc->exportFromTemplate($pengajuan);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    // =========================================================
    // PREVIEW
    // =========================================================

    public function preview(int $pengajuanId)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
        ])->findOrFail($pengajuanId);

        $data = BorangDataExcel::query()
            ->where('id_pengajuan', $pengajuanId)
            ->orderBy('id_elemen')
            ->get()
            ->groupBy('sheet_name');

        $activeSheet = request('sheet') ?? $data->keys()->first();

        // Cek syarat unggul jika LKPS sudah ada datanya
        $cekHasil = $this->jalankanCekSyarat($pengajuan);

        return view('lkps.preview', [
            'pengajuan'   => $pengajuan,
            'data'        => $data,
            'activeSheet' => $activeSheet,
            'cekHasil'    => $cekHasil,
            'rentangSkor' => $this->syaratRepo->getRentangSkor(),
        ]);
    }

    // =========================================================
    // CEK SYARAT (AJAX endpoint — dipanggil dari tombol refresh)
    // =========================================================

    public function cekSyarat(int $pengajuanId)
    {
        $pengajuan = PengajuanAkreditasi::with('studyProgram.degreeLevel')
            ->findOrFail($pengajuanId);

        $hasil = $this->jalankanCekSyarat($pengajuan);

        return response()->json([
            'success' => true,
            'data'    => $hasil,
        ]);
    }

    // =========================================================
    // TEST
    // =========================================================

    public function test()
    {
        return BorangDataExcel::first();
    }

    // =========================================================
    // PRIVATE
    // =========================================================

    /**
     * Jalankan cek syarat LKPS untuk pengajuan ini.
     * Mengembalikan array terstruktur atau null jika data belum ada.
     */
    private function jalankanCekSyarat(PengajuanAkreditasi $pengajuan): ?array
    {
        $studyProgram  = $pengajuan->studyProgram;
        $degreeLevelId = $studyProgram?->id_degree_level;
        $rumpun        = $studyProgram?->rumpun ?? 'arsitektur';

        if (!$degreeLevelId) {
            return null;
        }

        // Cek apakah ada data LKPS sama sekali
        $adaData = BorangDataExcel::where('id_pengajuan', $pengajuan->id)->exists();
        if (!$adaData) {
            return null;
        }

        try {
            $syarat = $this->lkpsReader->cekSemuaSyarat(
                $pengajuan->id,
                $rumpun,
                $degreeLevelId
            );

            // Tambahkan metadata untuk tampilan
            $syarat['degree_level'] = $studyProgram->degreeLevel?->name ?? '-';
            $syarat['rumpun']       = $rumpun;
            $syarat['checked_at']   = now()->toDateTimeString();

            return $syarat;
        } catch (\Throwable $e) {
            return [
                'semua_memenuhi' => false,
                'error'          => $e->getMessage(),
                'checked_at'     => now()->toDateTimeString(),
            ];
        }
    }
}
