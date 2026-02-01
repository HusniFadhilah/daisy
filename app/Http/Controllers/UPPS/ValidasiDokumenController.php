<?php
// app/Http/Controllers/UPPS/ValidasiDokumenController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\BorangValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ValidasiDokumenController extends Controller
{
    /**
     * Display list of validasi dokumen
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'validator',
            'borangValidation',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ])->orderBy('changed_at', 'desc'),
        ])->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('pengajuan_status_log as l')
                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                ->whereIn('l.status_to', [
                    PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                    PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                    PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                ]);
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

        return view('upps.validasi-dokumen.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail validasi dokumen
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
            'validator',
            'borangValidation.validator',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', ['draft_borang', 'borang_final'])
                ->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.validasi-dokumen.show', compact('pengajuan'));
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
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ])
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->get();

        $stats = [
            'total' => 0,
            'menunggu_validasi' => 0,
            'sedang_validasi' => 0,
            'perlu_revisi' => 0,
            'tervalidasi' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;

            // Menunggu Validasi
            if (
                (in_array(PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI, $statuses) ||
                    in_array(PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING, $statuses)) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_VALIDATED, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED, $statuses)
            ) {
                $stats['menunggu_validasi']++;
            }

            // Sedang Validasi
            if (
                in_array(PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_VALIDATED, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED, $statuses)
            ) {
                $stats['sedang_validasi']++;
            }

            // Perlu Revisi (status terakhir)
            if (in_array(PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED, $statuses)) {
                $stats['perlu_revisi']++;
            }

            // Tervalidasi
            if (
                in_array(PengajuanAkreditasi::STATUS_BORANG_VALIDATED, $statuses) ||
                in_array(PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA, $statuses)
            ) {
                $stats['tervalidasi']++;
            }
        }

        return $stats;
    }
}
