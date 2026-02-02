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
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', ['draft_borang', 'data_kualitatif', 'data_kuantitatif', 'borang_final'])

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
        ])->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('pengajuan_status_log as l')
                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                ->whereIn('l.status_to', [
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

        $needSuplemen = $pengajuan->need_suplemen;

        return view('upps.penerimaan-dokumen.upload', compact('pengajuan', 'needSuplemen'));
    }

    /**
     * Upload dokumen LED (DOCX) + LKPS (XLSX) oleh UPPS
     */
    public function uploadDokumen(Request $request, $id)
    {
        $validated = $request->validate([
            'file_led'  => 'required|file|mimes:docx,doc|max:10240',     // 10MB
            'file_lkps' => 'required|file|mimes:xlsx,xls|max:10240',     // 10MB
            'catatan_upload' => 'nullable|string|max:500',
        ], [
            'file_led.required' => 'File LED (DOCX) harus diupload.',
            'file_led.mimes'    => 'LED harus berformat DOCX/DOC.',
            'file_led.max'      => 'Ukuran file LED maksimal 10MB.',

            'file_lkps.required' => 'File LKPS (Excel) harus diupload.',
            'file_lkps.mimes'    => 'LKPS harus berformat XLSX/XLS.',
            'file_lkps.max'      => 'Ukuran file LKPS maksimal 10MB.',
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

            if (!in_array($pengajuan->status, $allowedStatuses, true)) {
                return back()->with('error', 'Dokumen tidak dapat diupload pada status saat ini.');
            }

            // =========================
            // Upload LED (data_kualitatif)
            // =========================
            $ledFile = $request->file('file_led');
            $ledExt  = $ledFile->getClientOriginalExtension(); // docx/doc
            $ledName = 'LED_' . $pengajuan->studyProgram->code . '_' . time() . '.' . $ledExt;

            $ledPath = $ledFile->storeAs(
                "pengajuan/{$pengajuan->id}/data-kualitatif",
                $ledName,
                'public'
            );

            // Mark previous LED as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kualitatif')
                ->update(['is_latest' => false]);

            $nextLedVersion = (PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kualitatif')
                ->max('versi') ?? 0) + 1;

            PengajuanDokumen::create([
                'id_pengajuan'      => $pengajuan->id,
                'jenis_dokumen'     => 'data_kualitatif',
                'nama_file'         => $ledName,
                'path_file'         => $ledPath,
                'original_filename' => $ledFile->getClientOriginalName(),
                'file_size'         => $ledFile->getSize(),
                'mime_type'         => $ledFile->getMimeType(),
                'uploaded_by'       => Auth::id(),
                'keterangan'        => $validated['catatan_upload'],
                'versi'             => $nextLedVersion,
                'is_latest'         => true,
            ]);

            // =========================
            // Upload LKPS (data_kuantitatif)
            // =========================
            $lkpsFile = $request->file('file_lkps');
            $lkpsExt  = $lkpsFile->getClientOriginalExtension(); // xlsx/xls
            $lkpsName = 'LKPS_' . $pengajuan->studyProgram->code . '_' . time() . '.' . $lkpsExt;

            $lkpsPath = $lkpsFile->storeAs(
                "pengajuan/{$pengajuan->id}/data-kuantitatif",
                $lkpsName,
                'public'
            );

            // Mark previous LKPS as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kuantitatif')
                ->update(['is_latest' => false]);

            $nextLkpsVersion = (PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kuantitatif')
                ->max('versi') ?? 0) + 1;

            PengajuanDokumen::create([
                'id_pengajuan'      => $pengajuan->id,
                'jenis_dokumen'     => 'data_kuantitatif',
                'nama_file'         => $lkpsName,
                'path_file'         => $lkpsPath,
                'original_filename' => $lkpsFile->getClientOriginalName(),
                'file_size'         => $lkpsFile->getSize(),
                'mime_type'         => $lkpsFile->getMimeType(),
                'uploaded_by'       => Auth::id(),
                'keterangan'        => $validated['catatan_upload'],
                'versi'             => $nextLkpsVersion,
                'is_latest'         => true,
            ]);

            // Update status pengajuan
            $newStatus = PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM;
            $keterangan = 'Dokumen LED (DOCX) dan LKPS (Excel) telah diupload oleh program studi, menunggu diterima oleh LAMDEPILAR';

            $pengajuan->updateStatusSafely($newStatus, $keterangan);

            // tanggal draft borang tetap dipakai sebagai penanda dokumen dikirim
            $pengajuan->update([
                'tanggal_draft_borang' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('upps.penerimaan-dokumen.show', $id)
                ->with('success', 'Dokumen LED (DOCX) dan LKPS (Excel) berhasil diupload. Menunggu diterima oleh LAMDEPILAR.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupload dokumen: ' . $e->getMessage());
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
        // Subquery: last status_to per pengajuan (berdasarkan changed_at, fallback created_at kalau perlu)
        $lastLogSub = DB::table('pengajuan_status_log as psl')
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->whereRaw('psl.id = (
            SELECT psl2.id
            FROM pengajuan_status_log psl2
            WHERE psl2.id_pengajuan = psl.id_pengajuan
            ORDER BY psl2.changed_at DESC, psl2.id DESC
            LIMIT 1
        )');

        // Main query: agregasi 1x hit
        $row = DB::table('pengajuan_akreditasi as pa')
            ->leftJoinSub($lastLogSub, 'last_log', function ($join) {
                $join->on('last_log.id_pengajuan', '=', 'pa.id');
            })
            ->whereIn('pa.id_program_studi', $studyProgramIds)
            ->selectRaw('
            COUNT(*) as total_pengajuan,

            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM pengajuan_status_log x
                        WHERE x.id_pengajuan = pa.id
                          AND x.status_to = ?
                    )
                    AND NOT EXISTS (
                        SELECT 1 FROM pengajuan_status_log y
                        WHERE y.id_pengajuan = pa.id
                          AND y.status_to = ?
                    )
                THEN 1 ELSE 0 END
            ) as dokumen_harus_dikirim,

            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM pengajuan_status_log z
                        WHERE z.id_pengajuan = pa.id
                          AND z.status_to = ?
                    )
                THEN 1 ELSE 0 END
            ) as draft_dokumen,

            SUM(
                CASE
                    WHEN EXISTS (
                        SELECT 1 FROM pengajuan_status_log w
                        WHERE w.id_pengajuan = pa.id
                          AND w.status_to = ?
                    )
                THEN 1 ELSE 0 END
            ) as dokumen_dikirim
        ', [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            ])
            ->first();

        return [
            'dokumen_harus_dikirim' => (int) ($row->dokumen_harus_dikirim ?? 0),
            'draft_dokumen' => (int) ($row->draft_dokumen ?? 0),
            'dokumen_dikirim' => (int) ($row->dokumen_dikirim ?? 0),

            // opsional: kalau masih mau dipakai di tempat lain
            'total_pengajuan' => (int) ($row->total_pengajuan ?? 0),
        ];
    }
}
