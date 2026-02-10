<?php
// app/Http/Controllers/UPPS/MasaSanggahController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MasaSanggahController extends Controller
{
    /**
     * Display list of masa sanggah
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            ])->orderBy('created_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_masa_sanggah_mulai');
        // ->whereNotNull('tanggal_masa_sanggah_selesai');
        // dd($query->get());
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

        return view('upps.masa-sanggah.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail masa sanggah
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
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.masa-sanggah.show', compact('pengajuan'));
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

        // Filter by status masa sanggah
        $query->when($request->filled('status_sanggah'), function ($q) use ($request) {
            $now = now();
            if ($request->status_sanggah === 'aktif') {
                $q->where('tanggal_masa_sanggah_mulai', '<=', $now)
                    ->where('tanggal_masa_sanggah_selesai', '>=', $now);
            } elseif ($request->status_sanggah === 'selesai') {
                $q->where('tanggal_masa_sanggah_selesai', '<', $now);
            }
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        $now = now();

        // Total masa sanggah
        $total = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_masa_sanggah_mulai')
            ->whereNotNull('tanggal_masa_sanggah_selesai')
            ->count();

        // Masa sanggah aktif
        $aktif = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('tanggal_masa_sanggah_mulai', '<=', $now)
            ->where('tanggal_masa_sanggah_selesai', '>=', $now)
            ->count();

        // Masa sanggah selesai
        $selesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('tanggal_masa_sanggah_selesai', '<', $now)
            ->count();

        return [
            'total' => $total,
            'aktif' => $aktif,
            'selesai' => $selesai,
        ];
    }
}
