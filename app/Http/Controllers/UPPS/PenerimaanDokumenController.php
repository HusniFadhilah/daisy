<?php
// app/Http/Controllers/UPPS/PenerimaanDokumenController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Jobs\ImportBorangExcelJob;
use App\Models\BorangImport;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'draft_borang',
                'data_kualitatif',
                'data_kuantitatif',
                'data_suplemen',
                'lembar_pengesahan',
                'borang_final'
            ])
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                // PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                // PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                // PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                // PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                // PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
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
                    // PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                    // PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                    // PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                    // PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    // PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
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
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'draft_borang',
                'data_kualitatif',
                'data_kuantitatif',
                'data_suplemen',
                'lembar_pengesahan',
                'borang_final'
            ])
                ->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        $needSuplemen = $pengajuan->need_suplemen;
        $uploadedDocuments = $pengajuan->getUploadedDocuments(
            $needSuplemen
                ? ['led', 'suplemen', 'lkps', 'pengesahan']
                : ['led', 'lkps', 'pengesahan']
        );

        return view('upps.penerimaan-dokumen.show', compact('pengajuan', 'uploadedDocuments', 'needSuplemen'));
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
        $canUploadDokumen = true;
        $allowedStatuses = [
            PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI, // Upload pertama kali
            PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED, // Upload ulang karena revisi
        ];

        if (!in_array($pengajuan->status, $allowedStatuses)) {
            $canUploadDokumen = false;
        }

        $needSuplemen = $pengajuan->need_suplemen;

        $uploadedDocuments = $pengajuan->getUploadedDocuments(
            $needSuplemen
                ? ['led', 'suplemen', 'lkps', 'pengesahan']
                : ['led', 'lkps', 'pengesahan']
        );

        return view('upps.penerimaan-dokumen.upload', compact('pengajuan', 'needSuplemen', 'canUploadDokumen', 'uploadedDocuments'));
    }

    public function uploadDokumen(Request $request, $id)
    {
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

            // Need suplemen?
            $needSuplemen = ($pengajuan->jenis_akreditasi === 'menuju_unggul');

            // Validate (after we know $needSuplemen)
            $rules = [
                'file_led'        => 'required|file|mimes:docx,doc|max:10240',
                'file_lkps'       => 'required|file|mimes:xlsx,xls|max:10240',
                'file_pengesahan' => 'required|file|mimes:pdf|max:10240',
                'catatan_upload'  => 'nullable|string|max:500',
            ];
            $messages = [
                'file_led.required' => 'File LED (DOCX) harus diupload.',
                'file_led.mimes'    => 'LED harus berformat DOCX/DOC.',
                'file_led.max'      => 'Ukuran file LED maksimal 10MB.',

                'file_lkps.required' => 'File LKPS (Excel) harus diupload.',
                'file_lkps.mimes'    => 'LKPS harus berformat XLSX/XLS.',
                'file_lkps.max'      => 'Ukuran file LKPS maksimal 10MB.',

                'file_pengesahan.required' => 'File Lembar Pengesahan (PDF) harus diupload.',
                'file_pengesahan.mimes'    => 'Lembar Pengesahan harus berformat PDF.',
                'file_pengesahan.max'      => 'Ukuran file Lembar Pengesahan maksimal 10MB.',
            ];

            if ($needSuplemen) {
                $rules['file_suplemen'] = 'required|file|mimes:pdf|max:10240';
                $messages['file_suplemen.required'] = 'File Suplemen (PDF) wajib diupload untuk jenis akreditasi menuju unggul.';
                $messages['file_suplemen.mimes'] = 'Suplemen harus berformat PDF.';
                $messages['file_suplemen.max'] = 'Ukuran file Suplemen maksimal 10MB.';
            } else {
                $rules['file_suplemen'] = 'nullable|file|mimes:pdf|max:10240';
            }

            $validated = $request->validate($rules, $messages);

            // Store LED
            $this->storePengajuanDokumen(
                $pengajuan,
                $request->file('file_led'),
                'data_kualitatif',
                'data-kualitatif',
                'LED',
                $validated['catatan_upload'] ?? null
            );

            // Store LKPS
            $this->storePengajuanDokumen(
                $pengajuan,
                $request->file('file_lkps'),
                'data_kuantitatif',
                'data-kuantitatif',
                'LKPS',
                $validated['catatan_upload'] ?? null
            );

            // Store Pengesahan
            $this->storePengajuanDokumen(
                $pengajuan,
                $request->file('file_pengesahan'),
                'lembar_pengesahan',
                'lembar-pengesahan',
                'PENGESAHAN',
                $validated['catatan_upload'] ?? null
            );

            // Store Suplemen (if exists / required)
            if ($request->hasFile('file_suplemen')) {
                $this->storePengajuanDokumen(
                    $pengajuan,
                    $request->file('file_suplemen'),
                    'data_suplemen',
                    'data-suplemen',
                    'SUPLEMEN',
                    $validated['catatan_upload'] ?? null
                );
            } else {
                // kalau menuju unggul tapi tidak ada file, harusnya sudah ke-block validation
            }

            // Update status pengajuan
            $newStatus = PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM;

            $keterangan = 'Dokumen LED (DOCX), LKPS (Excel), dan Lembar Pengesahan (PDF)'
                . ($needSuplemen ? ' serta Suplemen (PDF)' : '')
                . ' telah diupload oleh program studi, menunggu diterima oleh LAMDEPILAR';

            $pengajuan->updateStatusSafely($newStatus, $keterangan);

            $pengajuan->update([
                'tanggal_draft_borang' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('upps.penerimaan-dokumen.show', $id)
                ->with('success', 'Dokumen berhasil diupload. Menunggu diterima oleh LAMDEPILAR.');
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

        // if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
        //     abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        // }
        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            if (($dokumen->jenis_dokumen == 'data_kualitatif' || $dokumen->jenis_dokumen == 'draft_borang') && Str::startsWith($dokumen->nama_file, 'kualitatif_')) {
                return redirect()->route('pengajuan.borang.export-docx', $pengajuan->id);
            }
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

    private function storePengajuanDokumen(
        PengajuanAkreditasi $pengajuan,
        \Illuminate\Http\UploadedFile $file,
        string $jenisDokumen,
        string $folder,
        string $prefix,
        ?string $keterangan
    ): void {
        // Mark previous as not latest
        PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', $jenisDokumen)
            ->update(['is_latest' => false]);

        $nextVersion = (PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', $jenisDokumen)
            ->max('versi') ?? 0) + 1;

        $ext = $file->getClientOriginalExtension();
        $name = "{$prefix}_{$pengajuan->studyProgram->code}_" . time() . "_v{$nextVersion}.{$ext}";

        $path = $file->storeAs(
            "pengajuan/{$pengajuan->id}/{$folder}",
            $name,
            'public'
        );

        PengajuanDokumen::create([
            'id_pengajuan'      => $pengajuan->id,
            'jenis_dokumen'     => $jenisDokumen,
            'nama_file'         => $name,
            'path_file'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
            'uploaded_by'       => Auth::id(),
            'keterangan'        => $keterangan,
            'versi'             => $nextVersion,
            'is_latest'         => true,
        ]);
    }
}
