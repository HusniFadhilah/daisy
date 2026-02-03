<?php
// app/Http/Controllers/UPPS/PelaporanALController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\AsesmenDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelaporanALController extends Controller
{
    /**
     * Display list of pelaporan AL
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesorAL',
            'asesmen.beritaAcaraAL' => function ($q) {
                $q->where('type', 'berita_acara_al')
                    ->where('is_active', true)
                    ->latest();
            },
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_AL_SELESAI,
                PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ])->orderBy('changed_at', 'desc'),
        ])->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('pengajuan_status_log as l')
                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                ->whereIn('l.status_to', [
                    PengajuanAkreditasi::STATUS_AL_SELESAI,
                    PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
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

        return view('upps.pelaporan-al.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail pelaporan AL
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'asesmen.asesorAL',
            'asesmen.beritaAcaraAL' => function ($q) {
                $q->where('type', 'berita_acara_al')
                    ->where('is_active', true)
                    ->with(['uploader', 'prodiApprover'])
                    ->latest();
            },
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'draft_borang',
                'borang_final',
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

        return view('upps.pelaporan-al.show', compact('pengajuan'));
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
        // Total berita acara AL (yang sudah diupload dan approved)
        $totalBeritaAcara = AsesmenDocument::whereHas('asesmen.pengajuan', function ($q) use ($studyProgramIds) {
            $q->whereIn('id_program_studi', $studyProgramIds);
        })
            ->where('type', 'berita_acara_al')
            ->where('is_active', true)
            ->where('status_persetujuan_prodi', 'approved')
            ->count();

        // Pelaporan AL (yang sudah dilaporkan)
        $pelaporanAL = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
            ->count();

        return [
            'total' => $totalBeritaAcara,
            'pelaporan_al' => $pelaporanAL,
        ];
    }
}
