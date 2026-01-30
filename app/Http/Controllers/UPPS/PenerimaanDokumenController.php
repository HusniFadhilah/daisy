<?php
// app/Http/Controllers/UPPS/PenerimaanDokumenController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenerimaanDokumenController extends Controller
{
    /**
     * Display list of penerimaan dokumen LED
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', ['draft_borang', 'borang_final'])
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
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

        return view('upps.penerimaan-dokumen.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail penerimaan dokumen
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
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

        return view('upps.penerimaan-dokumen.show', compact('pengajuan'));
    }

    /**
     * Show upload form
     */
    public function showUploadForm($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Validasi status pengajuan - bisa upload di status ini:
        $allowedStatuses = [
            PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI, // Upload pertama kali
            PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED, // Upload ulang karena revisi
        ];

        if (!in_array($pengajuan->status, $allowedStatuses)) {
            return redirect()
                ->route('upps.penerimaan-dokumen.show', $id)
                ->with('error', 'Dokumen tidak dapat diupload pada status saat ini.');
        }

        return view('upps.penerimaan-dokumen.upload', compact('pengajuan'));
    }

    /**
     * Upload dokumen LED (draft_borang)
     */
    public function uploadDokumen(Request $request, $id)
    {
        $validated = $request->validate([
            'file_dokumen' => 'required|file|mimes:pdf|max:10240', // 10MB
            'catatan_upload' => 'nullable|string|max:500',
        ], [
            'file_dokumen.required' => 'File dokumen harus diupload.',
            'file_dokumen.mimes' => 'File harus berformat PDF.',
            'file_dokumen.max' => 'Ukuran file maksimal 10MB.',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('studyProgram')->findOrFail($id);

            // Check access
            $user = Auth::user();
            $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

            if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }

            // Validasi status pengajuan
            $allowedStatuses = [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            ];

            if (!in_array($pengajuan->status, $allowedStatuses)) {
                return redirect()->back()->with('error', 'Dokumen tidak dapat diupload pada status saat ini.');
            }

            // Upload file
            $file = $request->file('file_dokumen');
            $fileName = 'Dokumen_' . $pengajuan->studyProgram->code . '_' . time() . '.pdf';
            $filePath = $file->storeAs(
                'pengajuan/' . $pengajuan->id . '/draft-borang',
                $fileName,
                'public'
            );

            // Mark previous dokumen as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->update(['is_latest' => false]);

            // Create new dokumen record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'draft_borang',
                'path_file' => $filePath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
                'keterangan' => $validated['catatan_upload'],
                'versi' => PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                    ->where('jenis_dokumen', 'draft_borang')
                    ->max('versi') + 1,
                'is_latest' => true,
            ]);

            // Update status pengajuan
            $newStatus = PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM;
            $keterangan = 'Draft dokumen telah diupload oleh program studi, menunggu diterima oleh LAMDEPILAR';

            $pengajuan->updateStatusSafely($newStatus, $keterangan);

            // Update tanggal draft borang
            $pengajuan->update([
                'tanggal_draft_borang' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('upps.penerimaan-dokumen.show', $id)
                ->with('success', 'Dokumen berhasil diupload. Menunggu diterima oleh LAMDEPILAR.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengupload dokumen: ' . $e->getMessage());
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
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
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
            'belum_upload' => 0,
            'draft_dikirim' => 0,
            'draft_diterima' => 0,
            'dalam_validasi' => 0,
            'perlu_revisi' => 0,
            'tervalidasi' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;

            // Belum Upload: pembayaran diverifikasi tapi belum upload draft
            if (
                in_array(PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM, $statuses)
            ) {
                $stats['belum_upload']++;
            }

            // Draft Dikirim: sudah upload tapi belum diterima DE
            if (
                in_array(PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI, $statuses)
            ) {
                $stats['draft_dikirim']++;
            }

            // Draft Diterima: DE sudah terima
            if (
                (in_array(PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA, $statuses) ||
                    in_array(PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI, $statuses)) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION, $statuses)
            ) {
                $stats['draft_diterima']++;
            }

            // Dalam Validasi
            if (
                (in_array(PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING, $statuses) ||
                    in_array(PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION, $statuses)) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_VALIDATED, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED, $statuses)
            ) {
                $stats['dalam_validasi']++;
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
