<?php
// app/Http/Controllers/UPPS/ValidasiPembayaranController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanPembayaran;
use App\Models\PengajuanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ValidasiPembayaranController extends Controller
{
    /**
     * Display list of pembayaran
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'verifier',
        ])
            ->whereHas('pengajuan', function ($q) use ($studyProgramIds) {
                $q->whereIn('id_program_studi', $studyProgramIds);
            });

        // Apply filters
        $this->applyFilters($query, $request);

        $pembayarans = $query
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

        return view('upps.validasi-pembayaran.index', compact(
            'pembayarans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail pembayaran
     */
    public function show($id)
    {
        $pembayaran = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
            'verifier',
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pembayaran->pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke pembayaran ini.');
        }

        // Get dokumen pembayaran
        $dokumenPembayaran = PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
            ->where('jenis_dokumen', 'bukti_pembayaran')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('upps.validasi-pembayaran.show', compact('pembayaran', 'dokumenPembayaran'));
    }

    /**
     * Show upload form
     */
    public function showUploadForm($id)
    {
        $pembayaran = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pembayaran->pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke pembayaran ini.');
        }

        // Validasi status pembayaran
        if (!in_array($pembayaran->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang'])) {
            return redirect()
                ->route('upps.validasi-pembayaran.show', $id)
                ->with('error', 'Pembayaran tidak dapat diupload pada status saat ini.');
        }

        return view('upps.validasi-pembayaran.upload', compact('pembayaran'));
    }

    /**
     * Upload bukti pembayaran
     */
    public function uploadBukti(Request $request, $id)
    {
        $validated = $request->validate([
            'file_bukti_pembayaran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tanggal_pembayaran' => 'required|date|before_or_equal:today',
            'catatan_pembayaran' => 'nullable|string|max:500',
        ], [
            'file_bukti_pembayaran.required' => 'File bukti pembayaran harus diupload.',
            'file_bukti_pembayaran.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_bukti_pembayaran.max' => 'Ukuran file maksimal 5MB.',
            'tanggal_pembayaran.required' => 'Tanggal pembayaran harus diisi.',
            'tanggal_pembayaran.before_or_equal' => 'Tanggal pembayaran tidak boleh lebih dari hari ini.',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran = PengajuanPembayaran::with('pengajuan')->findOrFail($id);

            // Check access
            $user = Auth::user();
            $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

            if (!$studyProgramIds->contains($pembayaran->pengajuan->id_program_studi)) {
                abort(403, 'Anda tidak memiliki akses ke pembayaran ini.');
            }

            // Validasi status pembayaran
            if (!in_array($pembayaran->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang'])) {
                return redirect()->back()->with('error', 'Pembayaran tidak dapat diupload pada status saat ini.');
            }

            // Upload file
            $file = $request->file('file_bukti_pembayaran');
            $fileName = 'bukti_pembayaran_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs(
                'pengajuan/' . $pembayaran->id_pengajuan . '/bukti-pembayaran',
                $fileName,
                'public'
            );

            // Mark previous dokumen as not latest
            PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
                ->where('jenis_dokumen', 'bukti_pembayaran')
                ->update(['is_latest' => false]);

            // Create new dokumen record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pembayaran->id_pengajuan,
                'jenis_dokumen' => 'bukti_pembayaran',
                'path_file' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
                'versi' => PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
                    ->where('jenis_dokumen', 'bukti_pembayaran')
                    ->max('versi') + 1,
                'is_latest' => true,
            ]);

            // Update pembayaran
            $pembayaran->update([
                'status_pembayaran' => 'menunggu_verifikasi',
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'],
                'catatan_pembayaran' => $validated['catatan_pembayaran'],
            ]);

            // Update status pengajuan
            $pembayaran->pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN,
                'Bukti pembayaran telah diupload, menunggu validasi bagian keuangan'
            );

            DB::commit();

            return redirect()
                ->route('upps.validasi-pembayaran.show', $id)
                ->with('success', 'Bukti pembayaran berhasil diupload. Menunggu validasi dari bagian keuangan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengupload bukti pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen
     */
    public function download($id)
    {
        $dokumen = PengajuanDokumen::findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $pengajuan = PengajuanAkreditasi::findOrFail($dokumen->id_pengajuan);

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

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
            fn($q) => $q->whereHas(
                'pengajuan',
                fn($sq) =>
                $sq->where('tahun_akreditasi', $request->tahun)
            )
        );

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('nomor_invoice', 'like', "%{$search}%")
                    ->orWhereHas(
                        'pengajuan',
                        fn($ssq) =>
                        $ssq->where('nomor_pengajuan', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'pengajuan.studyProgram',
                        fn($ssq) =>
                        $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        // Filter by status pembayaran
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status_pembayaran', $request->status);
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        $baseQuery = PengajuanPembayaran::whereHas('pengajuan', function ($q) use ($studyProgramIds) {
            $q->whereIn('id_program_studi', $studyProgramIds);
        });

        return [
            'total' => (clone $baseQuery)->count(),
            'menunggu_pembayaran' => (clone $baseQuery)
                ->where('status_pembayaran', 'menunggu_pembayaran')
                ->count(),
            'menunggu_verifikasi' => (clone $baseQuery)
                ->where('status_pembayaran', 'menunggu_verifikasi')
                ->count(),
            'terverifikasi' => (clone $baseQuery)
                ->where('status_pembayaran', 'terverifikasi')
                ->count(),
            'upload_ulang' => (clone $baseQuery)
                ->where('status_pembayaran', 'upload_ulang')
                ->count(),
            'ditolak' => (clone $baseQuery)
                ->where('status_pembayaran', 'ditolak')
                ->count(),
        ];
    }
}
