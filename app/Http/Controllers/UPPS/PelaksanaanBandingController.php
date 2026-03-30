<?php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\AsesmenDocument;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PelaksanaanBandingController extends Controller
{
    // ============================================================
    // STATUS yang muncul di halaman pelaksanaan banding
    // Dimulai dari banding_diterima karena invoice dibuat DE saat ini
    // ============================================================
    private const STATUS_PELAKSANAAN = [
        PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN_BANDING,
        PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DITERIMA,
        PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN_BANDING,
        PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI,
        PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED,
        PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
        PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
        PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
        PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
        PengajuanAkreditasi::STATUS_ASESOR_AL_BANDING_ASSIGNED,
        PengajuanAkreditasi::STATUS_AL_BANDING_IN_PROGRESS,
        PengajuanAkreditasi::STATUS_AL_BANDING_SELESAI,
        PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
    ];

    // ============================================================
    // INDEX
    // ============================================================

    public function index(Request $request)
    {
        $user            = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            // ✅ Load invoice banding saja
            'pembayaranBanding',
            'statusLog' => fn($q) => $q
                ->whereIn('status_to', self::STATUS_PELAKSANAAN)
                ->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', self::STATUS_PELAKSANAAN);
            });

        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy('updated_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        $stats = $this->calculateStatistics($studyProgramIds);

        $tahunList = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.pelaksanaan-banding.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    // ============================================================
    // SHOW — dengan payment gate
    // ============================================================

    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'asesmen.hasil',
            // Semua dokumen banding (surat permohonan + formulir pembayaran)
            'dokumen' => fn($q) => $q
                ->whereIn('jenis_dokumen', [
                    'surat_permohonan_banding',
                    'formulir_pembayaran_banding',
                    'dokumen_banding',
                ])
                ->where('is_latest', true)
                ->orderBy('created_at', 'desc'),
            'pembayaranBanding',
            'statusLog' => fn($q) => $q
                ->orderBy('changed_at', 'asc'),
        ])->findOrFail($id);

        // ✅ Cek akses UPPS
        $studyProgramIds = Auth::user()->studyPrograms()->pluck('study_programs.id');
        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        $pembayaranBanding     = $pengajuan->pembayaranBanding;
        $formulirBanding       = $pengajuan->dokumen->firstWhere('jenis_dokumen', 'formulir_pembayaran_banding');
        $pembayaranLunas       = $pembayaranBanding && $pembayaranBanding->status_pembayaran === 'terverifikasi';

        return view('upps.pelaksanaan-banding.show', compact(
            'pengajuan',
            'pembayaranBanding',
            'formulirBanding',
            'pembayaranLunas'
        ));
    }

    // ============================================================
    // UPLOAD FORMULIR PEMBAYARAN BANDING
    // ============================================================

    /**
     * Form upload formulir & bukti pembayaran banding
     */
    public function showUploadForm($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pembayaranBanding',
        ])->findOrFail($id);

        // Cek akses
        $studyProgramIds = Auth::user()->studyPrograms()->pluck('study_programs.id');
        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403);
        }

        $pembayaranBanding = $pengajuan->pembayaranBanding;

        // Harus ada invoice dulu dari DE
        if (!$pembayaranBanding) {
            return redirect()
                ->route('upps.pelaksanaan-banding.show', $id)
                ->with('error', 'Invoice pembayaran banding belum dibuat oleh LAMDEPILAR.');
        }

        // Hanya bisa upload jika status menunggu_pembayaran atau upload_ulang
        if (!in_array($pembayaranBanding->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang'])) {
            return redirect()
                ->route('upps.pelaksanaan-banding.show', $id)
                ->with('error', 'Pembayaran tidak dapat diupload pada status saat ini.');
        }

        return view('upps.pelaksanaan-banding.upload-pembayaran', compact(
            'pengajuan',
            'pembayaranBanding'
        ));
    }

    /**
     * Proses upload formulir & bukti pembayaran banding
     */
    public function uploadPembayaran(Request $request, $id)
    {
        $request->validate([
            'file_formulir_pembayaran' => 'required|file|mimes:xlsx|max:5120',
            'tanggal_pembayaran'       => 'required|date_format:Y-m-d\TH:i',
            'catatan_pembayaran'       => 'nullable|string|max:500',
        ], [
            'file_formulir_pembayaran.required' => 'File formulir & bukti pembayaran banding wajib diupload.',
            'file_formulir_pembayaran.mimes'    => 'File harus berformat XLSX.',
            'file_formulir_pembayaran.max'      => 'Ukuran file maksimal 5 MB.',
            'tanggal_pembayaran.required'       => 'Tanggal pembayaran wajib diisi.',
        ]);

        $studyProgramIds = Auth::user()->studyPrograms()->pluck('study_programs.id');

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('pembayaranBanding')->findOrFail($id);

            if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
                abort(403);
            }

            $pembayaranBanding = $pengajuan->pembayaranBanding;

            if (!$pembayaranBanding) {
                throw new \Exception('Invoice banding belum tersedia.');
            }

            if (!in_array($pembayaranBanding->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang'])) {
                throw new \Exception('Status pembayaran tidak memperbolehkan upload saat ini.');
            }

            $file     = $request->file('file_formulir_pembayaran');
            $filename = 'formulir_pembayaran_banding_' . time() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs(
                "pengajuan/{$pengajuan->id}/formulir-pembayaran-banding",
                $filename,
                'public'
            );

            // ✅ Non-aktifkan dokumen lama
            $pengajuan->dokumen()
                ->where('jenis_dokumen', 'formulir_pembayaran_banding')
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // ✅ Simpan dokumen baru
            PengajuanDokumen::create([
                'id_pengajuan'      => $pengajuan->id,
                'jenis_dokumen'     => 'formulir_pembayaran_banding',
                'path_file'         => $path,
                'nama_file'         => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_size'         => $file->getSize(),
                'mime_type'         => $file->getMimeType(),
                'uploaded_by'       => auth()->id(),
                'is_latest'         => true,
                'versi'             => $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'formulir_pembayaran_banding')
                    ->max('versi') + 1,
            ]);

            // ✅ Update invoice banding
            $pembayaranBanding->update([
                'bukti_path'         => $path,
                'status_pembayaran'  => 'menunggu_verifikasi',
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'catatan_pembayaran' => $request->catatan_pembayaran,
            ]);

            $pembayaranBanding->pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN_BANDING,
                'Formulir & bukti pembayaran banding telah diupload, menunggu validasi bagian keuangan'
            );

            DB::commit();

            return redirect()
                ->route('upps.pelaksanaan-banding.show', $id)
                ->with('success', 'Formulir & bukti pembayaran banding berhasil diupload. Menunggu validasi dari LAMDEPILAR.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('uploadPembayaranBanding gagal', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);

            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal mengupload: ' . $e->getMessage());
        }
    }

    /**
     * Download formulir pembayaran banding yang sudah diupload UPPS
     */
    public function downloadFormulir($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        $studyProgramIds = Auth::user()->studyPrograms()->pluck('study_programs.id');
        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403);
        }

        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'formulir_pembayaran_banding')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->path_file, $dokumen->original_filename);
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function applyFilters($query, Request $request): void
    {
        $query->when(
            $request->filled('tahun'),
            fn($q) => $q->where('tahun_akreditasi', $request->tahun)
        );

        $query->when(
            $request->filled('status'),
            fn($q) => $q->where('status', $request->status)
        );

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', fn($ssq) =>
                    $ssq->where('name', 'like', "%{$search}%"));
            });
        });
    }

    private function calculateStatistics($studyProgramIds): array
    {
        $base = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds);
        $pendingApproval = AsesmenDocument::whereHas('asesmen.pengajuan', function ($q) use ($studyProgramIds) {
            $q->whereIn('id_program_studi', $studyProgramIds);
        })
            ->where('type', 'lha_asesor_banding')
            ->where('is_active', true)
            ->whereIn('status_persetujuan_prodi', ['pending', 'revision_required'])
            ->count();
        return [
            'total' => (clone $base)
                ->whereIn('status', self::STATUS_PELAKSANAAN)
                ->count(),

            // Menunggu bayar: ada invoice tapi belum verifikasi
            'menunggu_bayar' => (clone $base)
                ->whereIn('status', self::STATUS_PELAKSANAAN)
                ->whereHas('pembayaranBanding', fn($q) =>
                $q->whereIn('status_pembayaran', ['menunggu_pembayaran', 'menunggu_verifikasi', 'upload_ulang']))
                ->count(),

            // Sedang berlangsung: bayar lunas, pelaksanaan berjalan
            'sedang_berlangsung' => (clone $base)
                ->where('status', PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                ->count(),

            'selesai' => (clone $base)
                ->where('status', PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN)
                ->count(),

            'pending_approval' => $pendingApproval

        ];
    }

    /**
     * Process approval for LHA (Laporan Hasil Asesmen)
     */
    public function processLHABandingApproval(Request $request, $id, $docId)
    {
        $request->validate([
            'action' => 'required|in:approve,revision',
            'catatan_prodi' => 'nullable|string|max:2000',
        ], [
            'action.required' => 'Silakan pilih tindakan yang akan diambil',
            'action.in' => 'Tindakan tidak valid',
        ]);

        $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

        // Check access
        $user = Auth::user();
        $authId = $user->id;
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Get LHA document
        $lha = AsesmenDocument::where('id', $docId)
            ->where('id_asesmen', $pengajuan->asesmen->id)
            ->where('type', 'lha_asesor_banding')
            ->firstOrFail();

        // Check if already in final status
        if ($lha->isFinalStatus()) {
            return back()->with('error', 'Laporan hasil surveillance banding ini sudah dalam status final.');
        }

        DB::beginTransaction();
        try {
            // Map action to status
            $statusMap = [
                'approve' => 'approved',
                'revision' => 'revision_required',
            ];

            $newStatus = $statusMap[$request->action];

            // Update LHA document
            $lha->update([
                'status_persetujuan_prodi' => $newStatus,
                'approved_by_prodi' => $authId,
                'approved_at_prodi' => now(),
                'catatan_prodi' => $request->catatan_prodi,
            ]);

            // Create log message
            $logMessages = [
                'approved' => 'Laporan Hasil Surveillance Banding "' . $lha->title . '" disetujui oleh Program Studi',
                'revision_required' => 'Laporan Hasil Surveillance Banding "' . $lha->title . '" memerlukan revisi',
            ];

            $this->approveLHA($pengajuan, $authId, $logMessages, $newStatus);

            DB::commit();

            // Success messages
            $messages = [
                'approved' => 'Laporan hasil surveillance banding berhasil disetujui.',
                'revision_required' => 'Permintaan revisi laporan berhasil dikirim ke asesor banding.',
            ];

            return redirect()
                ->route('upps.pelaksanaan-banding.show', $pengajuan->id)
                ->with('success', $messages[$newStatus]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing LHA Banding approval: " . $e->getMessage(), [
                'lha_id' => $docId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Gagal memproses persetujuan Laporan Hasil Surveillance Banding');
        }
    }

    private function approveLHA($pengajuan, $authId, $logMessages, $newStatus)
    {
        $lhaAsesor = $pengajuan->asesmen->lhaAsesorBanding;
        $lhaDocument = $pengajuan->asesmen->lhaDocumentsBanding->first();
        if ($lhaAsesor) {
            $lhaAsesor->update([
                'status' => $newStatus == 'approved' ? 'finalized' : ($newStatus == 'revision_required' ? 'revision_required' : 'draft')
            ]);
        }
        if ($lhaDocument) {
            $lhaDocument->update([
                'status_persetujuan_prodi' => $newStatus,
                'approved_by_prodi' => $newStatus == 'approved' ? $authId : null,
                'approved_at_prodi' => $newStatus == 'approved' ? now() : null,
            ]);
        }

        if ($pengajuan && $newStatus == 'approved')
            $pengajuan->checkUpdateStatusAKAL('al_banding', 'status_asesor_selesai');

        $pengajuan->statusLog()->create([
            'status_from' => PengajuanAkreditasi::STATUS_AL_BANDING_IN_PROGRESS,
            'status_to' => $pengajuan->status,
            'changed_by' => $authId,
            'changed_at' => now(),
            'keterangan' => $logMessages[$newStatus],
        ]);   //
    }
}
