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

        $baseQuery = request()->except('page');

        return view('upps.validasi-pembayaran.index', compact(
            'pembayarans',
            'stats',
            'tahunList',
            'baseQuery'
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
            ->where('jenis_dokumen', 'formulir_pembayaran')
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
            'file_formulir_pembayaran' => 'required|file|mimes:xlsx,pdf,jpg,jpeg,png|max:5120',
            'tanggal_pembayaran' => 'required|date|before_or_equal:today',
            'catatan_pembayaran' => 'nullable|string|max:500',
        ], [
            'file_formulir_pembayaran.required' => 'File formulir & bukti pembayaran harus diupload.',
            'file_formulir_pembayaran.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
            'file_formulir_pembayaran.max' => 'Ukuran file maksimal 5MB.',
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
            $file = $request->file('file_formulir_pembayaran');
            $fileName = 'formulir_pembayaran_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs(
                'pengajuan/' . $pembayaran->id_pengajuan . '/formulir-pembayaran',
                $fileName,
                'public'
            );

            // Mark previous dokumen as not latest
            PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
                ->where('jenis_dokumen', 'formulir_pembayaran')
                ->update(['is_latest' => false]);

            // Create new dokumen record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pembayaran->id_pengajuan,
                'jenis_dokumen' => 'formulir_pembayaran',
                'path_file' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
                'versi' => PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
                    ->where('jenis_dokumen', 'formulir_pembayaran')
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
                'Formulir & bukti pembayaran telah diupload, menunggu validasi bagian keuangan'
            );

            DB::commit();

            return redirect()
                ->route('upps.validasi-pembayaran.show', $id)
                ->with('success', 'Formulir & Bukti pembayaran berhasil diupload. Menunggu validasi dari bagian keuangan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengupload formulir & bukti pembayaran: ' . $e->getMessage());
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

        // Filter cepat dari card (gabungan status)
        $query->when($request->filled('quick'), function ($q) use ($request) {
            switch ($request->quick) {
                case 'menunggu_pembayaran':
                    $q->where('status_pembayaran', 'menunggu_pembayaran');
                    break;

                case 'dibayar': // pernah submit bukti bayar
                    $q->whereIn('status_pembayaran', [
                        'menunggu_verifikasi',
                        'terverifikasi',
                        'upload_ulang',
                        'ditolak',
                    ]);
                    break;

                case 'belum_tervalidasi':
                    $q->whereIn('status_pembayaran', [
                        'menunggu_verifikasi',
                        'upload_ulang',
                    ]);
                    break;

                case 'tervalidasi':
                    $q->where('status_pembayaran', 'terverifikasi');
                    break;

                case 'all':
                default:
                    // tidak memfilter apa-apa
                    break;
            }
        });

        // Filter by status pembayaran (termasuk ringkasan)
        $query->when($request->filled('status'), function ($q) use ($request) {
            $status = $request->status;

            if ($status === '__dibayar__') {
                // pernah submit bukti bayar
                $q->whereIn('status_pembayaran', [
                    'menunggu_verifikasi',
                    'terverifikasi',
                    'upload_ulang',
                    'ditolak',
                ]);
                return;
            }

            if ($status === '__belum_tervalidasi__') {
                // menunggu validasi atau diminta upload ulang
                $q->whereIn('status_pembayaran', [
                    'menunggu_verifikasi',
                    'upload_ulang',
                ]);
                return;
            }

            // status normal (1 status)
            $q->where('status_pembayaran', $status);
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        $row = PengajuanPembayaran::whereHas('pengajuan', function ($q) use ($studyProgramIds) {
            $q->whereIn('id_program_studi', $studyProgramIds);
        })
            ->selectRaw('
            COUNT(*) AS total,

            SUM(CASE WHEN status_pembayaran = "menunggu_pembayaran" THEN 1 ELSE 0 END) AS menunggu_pembayaran,
            SUM(CASE WHEN status_pembayaran = "menunggu_verifikasi" THEN 1 ELSE 0 END) AS menunggu_verifikasi,
            SUM(CASE WHEN status_pembayaran = "terverifikasi" THEN 1 ELSE 0 END) AS terverifikasi,
            SUM(CASE WHEN status_pembayaran = "upload_ulang" THEN 1 ELSE 0 END) AS upload_ulang,
            SUM(CASE WHEN status_pembayaran = "ditolak" THEN 1 ELSE 0 END) AS ditolak
        ')
            ->first();

        $totalInvoice = (int) $row->total;

        // ✅ sudah dibayar = pernah submit bukti bayar
        $totalInvoiceDibayar =
            $row->menunggu_verifikasi +
            $row->terverifikasi +
            $row->upload_ulang +
            $row->ditolak; // hapus ini kalau ditolak TIDAK dihitung dibayar

        // ✅ belum tervalidasi
        $totalInvoiceBelumTervalidasi =
            $row->menunggu_verifikasi +
            $row->upload_ulang;

        return [
            'total_invoice' => $totalInvoice,
            'total_invoice_dibayar' => (int) $totalInvoiceDibayar,
            'total_invoice_belum_tervalidasi' => (int) $totalInvoiceBelumTervalidasi,
            'total_invoice_tervalidasi' => (int) $row->terverifikasi,

            // optional: kalau masih dipakai di tempat lain
            'menunggu_pembayaran' => (int) $row->menunggu_pembayaran,
            'menunggu_verifikasi' => (int) $row->menunggu_verifikasi,
            'terverifikasi' => (int) $row->terverifikasi,
            'upload_ulang' => (int) $row->upload_ulang,
            'ditolak' => (int) $row->ditolak,
        ];
    }
}
