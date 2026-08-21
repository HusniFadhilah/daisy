<?php
// app/Http/Controllers/UPPS/PenerimaanPermohonanController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenerimaanPermohonanController extends Controller
{
    /**
     * Display list of penerimaan permohonan
     */
    public function index(Request $request)
    {
        $statusLogs = [
            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
        ];
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_penerimaan_de')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q->whereIn('status_to', $statusLogs)->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) use ($statusLogs) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $statusLogs);
            });

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
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

        return view('upps.penerimaan-permohonan.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail penerimaan permohonan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_penerimaan_de'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.penerimaan-permohonan.show', compact('pengajuan'));
    }

    /**
     * Download surat penerimaan
     */
    public function download($id)
    {
        $pengajuanDokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_penerimaan_de')
            ->where('is_latest', true)
            ->firstOrFail();
        return $pengajuanDokumen->downloadDokumen();
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
     * Calculate statistics based on status log
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Ambil semua status log untuk pengajuan milik prodi ini
        $logs = DB::table('pengajuan_status_log as psl')
            ->join('pengajuan_akreditasi as pa', 'psl.id_pengajuan', '=', 'pa.id')
            ->whereIn('pa.id_program_studi', $studyProgramIds)
            ->whereIn('psl.status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            ])
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->get();

        $stats = [
            'total' => 0,
            'diterima' => 0,
            'surat_dikirim' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;

            // Diterima: permohonan diterima tapi belum ada surat penerimaan
            if (
                in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM, $statuses)
            ) {
                $stats['diterima']++;
            }

            // Surat Penerimaan Dikirim
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM, $statuses)) {
                $stats['surat_dikirim']++;
            }
        }

        return $stats;
    }
}
