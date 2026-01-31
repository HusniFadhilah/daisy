<?php
// app/Http/Controllers/UPPS/PenugasanALController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenugasanALController extends Controller
{
    /**
     * Display list of penugasan asesor AL
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesorAL',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AL_SELESAI,
                PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AL_SELESAI,
                PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ]);

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

        return view('upps.penugasan-al.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail penugasan asesor AL
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'asesorAL',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'draft_borang',
                'borang_final',
                'surat_tugas_asesor_ak',
                'surat_tugas_asesor_al'
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.penugasan-al.show', compact('pengajuan'));
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
                PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AL_SELESAI,
                PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ])
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->get();

        $stats = [
            'total' => 0,
            'ak_dilaporkan' => 0,
            'asesor_ditugaskan' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;

            // AK Dilaporkan: hasil AK sudah dilaporkan
            if (in_array(PengajuanAkreditasi::STATUS_AK_DILAPORKAN, $statuses)) {
                $stats['ak_dilaporkan']++;
            }

            // Asesor ditugaskan: sudah ada penugasan asesor AL
            if (
                in_array(PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED, $statuses) ||
                in_array(PengajuanAkreditasi::STATUS_AL_IN_PROGRESS, $statuses) ||
                in_array(PengajuanAkreditasi::STATUS_AL_SELESAI, $statuses) ||
                in_array(PengajuanAkreditasi::STATUS_AL_DILAPORKAN, $statuses)
            ) {
                $stats['asesor_ditugaskan']++;
            }
        }

        return $stats;
    }
}
