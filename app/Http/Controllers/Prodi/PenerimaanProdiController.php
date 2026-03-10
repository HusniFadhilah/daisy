<?php
// app/Http/Controllers/Prodi/PenerimaanProdiController.php

namespace App\Http\Controllers\Prodi;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PenerimaanProdiController extends Controller
{
    /**
     * Display list of penerimaan permohonan akreditasi yang diterima prodi
     */
    public function index(Request $request)
    {
        $query = PengajuanAkreditasi::with([
            'studyProgram:id,name,full_name,id_university,id_degree_level',
            'studyProgram.university:id,name',
            'studyProgram.degreeLevel:id,name',
            'deAssigned:id,name,email',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_penerimaan_de')
                ->where('is_latest', true),
        ])
            ->where('id_user_pengaju', auth()->id())
            ->where('status', '>=', PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA);

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(20)
            ->appends($request->query());

        // Statistics
        $stats = $this->calculateStatistics();

        return view('prodi.penerimaan-permohonan.index', [
            'pengajuans' => $pengajuans,
            'stats' => $stats,
            'tahunList' => PengajuanAkreditasi::where('id_user_pengaju', auth()->id())
                ->distinct()
                ->pluck('tahun_akreditasi')
                ->sort()
                ->values(),
        ]);
    }

    /**
     * Show detail penerimaan permohonan akreditasi      */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'pengaju',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_penerimaan_de')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Authorization: only owner can view
        if ($pengajuan->id_user_pengaju !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat permohonan ini.');
        }

        return view('prodi.penerimaan-permohonan.show', compact('pengajuan'));
    }

    /**
     * Download penerimaan permohonan akreditasi      */
    public function download($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Authorization
        if ($pengajuan->id_user_pengaju !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        $pengajuanDokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_penerimaan_de')
            ->where('is_latest', true)
            ->firstOrFail();
        return $pengajuanDokumen->downloadDokumen();
    }

    /**
     * Preview penerimaan
permohonan akreditasi      */
    public function preview($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Authorization
        if ($pengajuan->id_user_pengaju !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat dokumen ini.');
        }

        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_penerimaan_de')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        $filePath = Storage::disk('public')->path($dokumen->path_file);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $dokumen->original_filename . '"'
        ]);
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

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
                        fn($ssq) => $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        // Filter by status penerimaan
        $query->when($request->filled('status_penerimaan'), function ($q) use ($request) {
            if ($request->status_penerimaan === 'diterima') {
                $q->whereHas(
                    'dokumen',
                    fn($dq) =>
                    $dq->where('jenis_dokumen', 'surat_penerimaan_de')
                        ->where('is_latest', true)
                );
            } elseif ($request->status_penerimaan === 'menunggu') {
                $q->whereDoesntHave(
                    'dokumen',
                    fn($dq) =>
                    $dq->where('jenis_dokumen', 'surat_penerimaan_de')
                        ->where('is_latest', true)
                );
            }
        });
    }

    private function calculateStatistics(): array
    {
        $baseQuery = PengajuanAkreditasi::where('id_user_pengaju', auth()->id())
            ->where('status', '>=', PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA);

        return [
            'total' => (clone $baseQuery)->count(),

            'diterima' => (clone $baseQuery)
                ->whereHas(
                    'dokumen',
                    fn($q) =>
                    $q->where('jenis_dokumen', 'surat_penerimaan_de')
                        ->where('is_latest', true)
                )
                ->count(),

            'menunggu' => (clone $baseQuery)
                ->whereDoesntHave(
                    'dokumen',
                    fn($q) =>
                    $q->where('jenis_dokumen', 'surat_penerimaan_de')
                        ->where('is_latest', true)
                )
                ->count(),
        ];
    }
}
