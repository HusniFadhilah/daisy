<?php
// app/Http/Controllers/UPPS/PermohonanBandingController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermohonanBandingController extends Controller
{
    /**
     * Display list of permohonan banding
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_MASA_SANGGAH,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
            ])->orderBy('changed_at', 'desc'),
        ])->whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_banding')->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', [
                        PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                        PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                        PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                    ]);
            });

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'tanggal_banding'), $request->get('sort_order', 'desc'))
            ->paginate(20)
            ->appends($request->query());

        // Statistics
        $stats = $this->calculateStatistics($studyProgramIds);

        // Get tahun list
        $tahunList = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.permohonan-banding.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail permohonan banding
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'sertifikat_akreditasi',
                'sk_akreditasi',
                'dokumen_banding',
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.permohonan-banding.show', compact('pengajuan'));
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        $query->when(
            $request->filled('tahun'),
            fn($q) => $q->where('tahun_akreditasi', $request->tahun)
        );

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas(
                        'studyProgram',
                        fn($ssq) =>
                        $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        // Filter by status
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Total banding diajukan
        $totalBanding = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_banding')
            ->count();

        // Banding dalam proses
        $dalamProses = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            ])
            ->count();

        // Banding selesai
        $selesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN)
            ->count();

        return [
            'total' => $totalBanding,
            'dalam_proses' => $dalamProses,
            'selesai' => $selesai,
        ];
    }
}
