<?php
// app/Http/Controllers/UPPS/SuratPermohonanController.php

namespace App\Http\Controllers\UPPS;

use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratPermohonanController extends Controller
{
    /**
     * Display list of surat permohonan yang dikirim oleh prodi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_permohonan')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', [
                        PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
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

        return view('upps.surat-permohonan.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail surat permohonan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_permohonan'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.surat-permohonan.show', compact('pengajuan'));
    }

    /**
     * Download surat permohonan
     */
    public function download($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_permohonan')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $dokumen->path_file,
            $dokumen->original_filename
        );
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
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
            ])
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->get();

        $stats = [
            'total' => 0,
            'menunggu' => 0,
            'dikirim' => 0,
            'diterima' => 0,
            'ditolak' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;
            // Menunggu: ada PENGINGAT_DIKIRIM, tapi belum SURAT_PERMOHONAN_DIKIRIM
            if (
                in_array(PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM, $statuses)
            ) {
                $stats['menunggu']++;
            }
            // Dikirim: ada SURAT_PERMOHONAN_DIKIRIM, tapi belum diterima / ditolak
            if (
                in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK, $statuses)
            ) {
                $stats['dikirim']++;
            }

            // Diterima
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses)) {
                $stats['diterima']++;
            }

            // Ditolak
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK, $statuses)) {
                $stats['ditolak']++;
            }
        }

        return $stats;
    }
}
