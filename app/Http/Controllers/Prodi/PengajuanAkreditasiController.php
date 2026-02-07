<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Kriteria;
use Illuminate\Support\Str;
use App\Models\BorangImport;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use App\Jobs\ImportBorangDocxJob;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanPembayaran;
use App\Models\PengingatAkreditasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use App\Services\BorangMergeService;
use Illuminate\Support\Facades\Auth;
use App\Services\BorangExportService;
use App\Services\BorangParserService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PengajuanAkreditasiController extends Controller
{
    use AuthorizesRequests;
    protected $borangMergeService;

    public function __construct(BorangMergeService $borangMergeService)
    {
        $this->borangMergeService = $borangMergeService;
    }

    /**
     * Display a listing of pengajuan
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get study program IDs for current user
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        // Get pengingat yang belum direspon
        $pengingatBelumDirespon = PengingatAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengirim'
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengingatAkreditasi::STATUS_BELUM_DIRESPON)
            ->orderBy('tanggal_dikirim', 'desc')
            ->get();

        // Query pengajuan
        $query = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'pengaju',
            'deskEvaluator',
            'pembayaran'
        ])->whereIn('id_program_studi', $studyProgramIds);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_pengajuan', 'like', '%' . $request->search . '%')
                    ->orWhereHas('studyProgram', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $pengajuans = $query->latest()->paginate(10);

        return view('asesmen.pengajuan.index', compact('pengajuans', 'pengingatBelumDirespon'));
    }

    /**
     * Show the form for creating a new Permohonan akreditasi
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Cek apakah ada id_pengingat dari URL
        $pengingat = null;
        if ($request->filled('id_pengingat')) {
            $pengingat = PengingatAkreditasi::with(['studyProgram', 'pengirim'])
                ->findOrFail($request->id_pengingat);

            // Validasi user berhak akses prodi ini
            if (!$user->studyPrograms()->where('study_programs.id', $pengingat->id_program_studi)->exists()) {
                abort(403, 'Anda tidak memiliki akses ke program studi ini.');
            }

            // Validasi pengingat belum direspon
            if ($pengingat->status !== PengingatAkreditasi::STATUS_BELUM_DIRESPON) {
                return redirect()
                    ->route('pengajuan')
                    ->with('error', 'Pengingat ini telah direspon.');
            }
        }

        // Get study programs for dropdown
        $prodis = $user->studyPrograms()
            ->with(['university', 'degreeLevel'])
            ->get();

        // Jika hanya 1 prodi, auto-select
        $prodiUser = $prodis->count() === 1 ? $prodis->first() : null;

        return view('asesmen.pengajuan.create', compact('prodis', 'prodiUser', 'pengingat'));
    }

    /**
     * ✅ UNIFIED STORE METHOD - Handle both scenarios
     */
    public function store(Request $request)
    {
        // ✅ Check if responding to pengingat
        $pengingat = null;
        if ($request->filled('id_pengingat')) {
            $pengingat = PengingatAkreditasi::findOrFail($request->id_pengingat);

            // Validasi pengingat belum direspon
            if ($pengingat->status !== PengingatAkreditasi::STATUS_BELUM_DIRESPON) {
                return back()->with('error', 'Pengingat ini telah direspon.');
            }

            // Validasi user berhak akses prodi ini
            $user = auth()->user();
            if (!$user->studyPrograms()->where('study_programs.id', $pengingat->id_program_studi)->exists()) {
                abort(403, 'Anda tidak memiliki akses ke program studi ini.');
            }
        }

        // ✅ Validation rules (sama untuk kedua skenario)
        $validated = $request->validate([
            'id_program_studi' => 'required|exists:study_programs,id',
            'tahun_akreditasi' => 'required|integer|min:2024|max:' . (date('Y') + 2),
            'jenis_akreditasi' => 'required|in:baru,perpanjangan,menuju_unggul',
            'file_surat_permohonan' => 'required|file|mimes:pdf|max:5120',
            'catatan_pengaju' => 'nullable|string|max:2000',
        ], [
            'jenis_akreditasi.required' => 'Jenis akreditasi wajib dipilih',
            'file_surat_permohonan.required' => 'File surat permohonan wajib diupload',
            'file_surat_permohonan.mimes' => 'File harus berformat PDF',
            'file_surat_permohonan.max' => 'Ukuran file maksimal 5MB',
        ]);

        DB::beginTransaction();
        try {
            // ✅ Create pengajuan
            $pengajuan = PengajuanAkreditasi::create([
                'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan($validated['jenis_akreditasi']),
                'id_program_studi' => $validated['id_program_studi'],
                'id_user_pengaju' => auth()->id(),
                'id_de_assigned' => $pengingat?->id_de_pengirim,
                'tahun_akreditasi' => $validated['tahun_akreditasi'],
                'jenis_akreditasi' => $validated['jenis_akreditasi'],
                'status' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                'catatan_pengaju' => $validated['catatan_pengaju'],
                'tanggal_pengingat' => $pengingat?->tanggal_dikirim,
                'tanggal_surat_permohonan_dikirim' => now(),
            ]);

            // ✅ Upload file (extracted to helper method)
            $this->uploadSuratPermohonan($pengajuan, $request->file('file_surat_permohonan'));

            // ✅ Mark pengingat as responded if exists
            if ($pengingat) {
                $pengingat->markAsResponded($pengajuan);
            }

            // ✅ Log status
            $pengajuan->statusLog()->create([
                'status_from' => $pengingat
                    ? PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM
                    : PengajuanAkreditasi::STATUS_DRAFT,
                'status_to' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => $pengingat
                    ? 'Permohonan dikirim sebagai respon pengingat akreditasi'
                    : 'Permohonan akreditasi baru dibuat',
            ]);

            DB::commit();

            return redirect()
                ->route('pengajuan')
                ->with('success', 'Permohonan akreditasi berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating pengajuan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim permohonan: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Helper method untuk upload surat permohonan
     */
    private function uploadSuratPermohonan(PengajuanAkreditasi $pengajuan, $file)
    {
        // Sanitize filename
        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename = time() . '_' . $sanitizedName;

        $path = $file->storeAs('dokumen/surat-permohonan', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_permohonan',
            'nama_file' => $filename,
            'path_file' => $path,
            'original_filename' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'is_latest' => true,
        ]);
    }

    /**
     * Display the specified pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'pengaju' => function ($query) {
                $query->select('id', 'name', 'email', 'role');
            },
            'deskEvaluator' => function ($query) {
                $query->select('id', 'name', 'email', 'role');
            },
            'dokumen' => function ($query) {
                $query->with(['uploader' => function ($q) {
                    $q->select('id', 'name', 'email');
                }]);
            },
            'pembayaran',
            'statusLog' => function ($query) {
                $query->with(['changedBy' => function ($q) {
                    $q->select('id', 'name', 'email');
                }])->latest();
            },
            'borangValidators.user',
            'borangValidators.borangValidation',
        ])->findOrFail($id);

        // Check authorization
        $this->authorize('view', $pengajuan);

        return view('asesmen.pengajuan.show', compact(
            'pengajuan',
        ));
    }

    /**
     * Upload draft LED (DOCX only for processing)
     */
    public function uploadDraftBorang(Request $request, $id)
    {
        try {
            $request->validate([
                'draft_borang' => 'required|file|mimes:docx|max:10240',
                'keterangan' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            // ✅ UPDATED: Check status - remove review_kesiapan statuses
            if (!in_array($pengajuan->status, [
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status Permohonan akreditasi tidak sesuai untuk upload draft LED.'
                ], 422);
            }

            DB::beginTransaction();

            // Mark previous drafts as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->update(['is_latest' => false]);

            // Get next version number
            $lastVersion = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->max('versi') ?? 0;

            $newVersion = $lastVersion + 1;

            // Upload file
            $file = $request->file('draft_borang');
            $filename = 'draft_borang_v' . $newVersion . '_' . time() . '.docx';
            $path = $file->storeAs('permohonan-akreditasi' . $pengajuan->id . '/draft_borang', $filename, 'public');

            // Create document record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'draft_borang',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => $authId,
                'keterangan' => $request->keterangan ?? 'Upload draft LED versi ' . $newVersion,
                'versi' => $newVersion,
                'is_latest' => true,
            ]);

            // Update Permohonan akreditasi status (only if not already draft_borang_diterima)
            $oldStatus = $pengajuan->status;
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA) {
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                    'tanggal_draft_borang' => now(),
                ]);

                // Log status change
                $this->logStatus(
                    $pengajuan,
                    $oldStatus,
                    PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                    'Draft LED diupload (versi ' . $newVersion . ')'
                );
            } else {
                // Just log the re-upload
                $this->logStatus(
                    $pengajuan,
                    $oldStatus,
                    $oldStatus,
                    'Draft LED diupload ulang (versi ' . $newVersion . '): ' . ($request->keterangan ?? 'Revisi dokumen')
                );
            }

            DB::commit();

            // Return JSON for AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft LED berhasil diupload (Versi ' . $newVersion . ')',
                    'data' => [
                        'dokumen_id' => $dokumen->id,
                        'versi' => $dokumen->versi,
                        'filename' => $dokumen->original_filename,
                        'file_size' => $this->formatFileSize($dokumen->file_size),
                        'mime_type' => $dokumen->mime_type,
                        'status_changed' => $oldStatus !== $pengajuan->fresh()->status,
                    ]
                ]);
            }

            return back()->with('success', 'Draft LED berhasil diupload (Versi ' . $newVersion . ').');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload draft LED failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat upload: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show borang HTML preview
     */
    public function showBorangHTML($id)
    {
        $pengajuan = PengajuanAkreditasi::with('studyProgram.university')->findOrFail($id);
        $this->authorize('view', $pengajuan);

        // Get latest import
        $import = BorangImport::where('id_pengajuan', $pengajuan->id)
            ->with([
                'sections' => function ($q) {
                    $q->with(['elemen', 'tables.dataset'])->orderBy('position');
                }
            ])
            ->latest()
            ->firstOrFail();

        return view('asesmen.pengajuan.borang-preview', compact('pengajuan', 'import'));
    }

    /**
     * Finalize & Submit Borang Online
     */
    public function submitBorangOnline(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            // Get import
            $import = $this->getOrCreateBorangImport($pengajuan);

            DB::beginTransaction();

            // Update import status
            $import->update([
                'status' => 'completed',
                'imported_at' => now(),
            ]);

            // Update Permohonan akreditasi status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                'tanggal_draft_borang' => now(),
            ]);

            // Log status change
            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                'Laporan Evaluasi Diri online difinalisasi dan di-submit'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan Evaluasi Diri, Suplemen, dan LKPS berhasil disubmit!',
                'data' => [
                    'status' => $pengajuan->status,
                    'submitted_at' => now()->format('d M Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Submit borang online failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal submit borang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unsubmitBorangOnline(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            // Hanya boleh unsubmit kalau masih di tahap submit borang (belum validasi berjalan)
            $allowed = [
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            ];

            if (!in_array($pengajuan->status, $allowed, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa unsubmit pada status saat ini.'
                ], 422);
            }

            // Kalau sudah masuk validasi/revisi/validated dll, jangan boleh unsubmit
            $blocked = [
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
            ];

            if (in_array($pengajuan->status, $blocked, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa unsubmit karena telah masuk tahap validasi/lanjutan.'
                ], 422);
            }

            DB::beginTransaction();

            // rollback status pengajuan
            $oldStatus = $pengajuan->status;

            $pengajuan->update([
                // Balik ke tahap sebelum submit
                'status' => PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,

                // Pilih salah satu:
                // 1) null-kan supaya dianggap belum submit
                'tanggal_draft_borang' => null,

                // atau 2) kalau kamu mau tetap simpan histori submit pertama, comment baris atas
                // 'tanggal_draft_borang' => $pengajuan->tanggal_draft_borang,
            ]);

            $ket = $request->input('keterangan');
            $msg = 'Submit Dokumen dibatalkan (unsubmit).'
                . ($ket ? ' Catatan: ' . $ket : '');

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                $msg
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Submit Dokumen berhasil dibatalkan.',
                'data' => [
                    'status' => $pengajuan->status,
                    'unsubmitted_at' => now()->format('d M Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Unsubmit borang online failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal unsubmit borang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkBorangFiles($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('view', $pengajuan);

            $result = $this->borangMergeService->checkFilesComplete($pengajuan);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Check borang files failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengecek kelengkapan file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NEW: Show lembar pengesahan preview (PDF)
     */
    public function previewLembarPengesahan($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel'
        ])->findOrFail($id);

        $this->authorize('view', $pengajuan);

        // Get import
        $import = $this->getOrCreateBorangImport($pengajuan);

        // Get pengesahan data
        $pengesahanRecord = \App\Models\BorangData::where('id_borang_import', $import->id)
            ->where('dataset_id', 'lembar_pengesahan')
            ->first();

        $pengesahanData = $pengesahanRecord ? json_decode($pengesahanRecord->nilai, true) : null;

        return view('asesmen.pengajuan.lembar-pengesahan-preview', compact(
            'pengajuan',
            'pengesahanData'
        ));
    }

    /**
     * Helper: Format file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Show borang online form
     */
    public function showBorangOnline($id)
    {
        $pengajuan = PengajuanAkreditasi::with(['studyProgram'])->findOrFail($id);
        $this->authorize('update', $pengajuan);

        // Load kriteria with all relations
        $kriterias = Kriteria::with([
            'elemenStandar' => function ($q) {
                $q->orderBy('kode_elemen');
            },
            'elemenStandar.pernyataan',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.datasetBorang' => function ($q) {
                $q->where('is_active', true)->orderBy('urutan');
            }
        ])->get();

        // Get existing data
        $existingData = $this->getExistingBorangData($pengajuan);

        // Calculate detailed progress
        $progressData = $this->calculateBorangProgress($kriterias, $existingData);

        $lockBorang = in_array($pengajuan->status, [
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM,
            \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
        ]);

        return view('asesmen.pengajuan.borang-online', compact(
            'pengajuan',
            'kriterias',
            'existingData',
            'progressData',
            'lockBorang'
        ));
    }
    /**
     * ✅ Calculate comprehensive borang progress
     */
    private function calculateBorangProgress($kriterias, $existingData)
    {
        $totalElemen = 0;
        $completedElemen = 0;
        $kriteriaProgress = [];
        $elemenProgress = [];

        foreach ($kriterias as $kriteria) {
            $kriteriaElemenTotal = 0;
            $kriteriaElemenComplete = 0;

            foreach ($kriteria->elemenStandar as $elemen) {
                $totalElemen++;
                $kriteriaElemenTotal++;

                // Check description
                $descKey = 'desc_' . $elemen->id;
                $hasDesc = isset($existingData[$descKey])
                    && strlen(trim($existingData[$descKey])) > 0;

                // Check tables (if any)
                $hasAllTables = true;
                $tableCount = 0;

                foreach ($elemen->datasetBorang as $dataset) {
                    if ($dataset->tipe_field === 'table') {
                        $tableCount++;
                        if (
                            !isset($existingData[$dataset->kode]) ||
                            !$this->hasTableData($existingData[$dataset->kode])
                        ) {
                            $hasAllTables = false;
                        }
                    }
                }

                // Elemen complete if has desc AND all tables filled
                $isElemenComplete = $hasDesc && ($tableCount === 0 || $hasAllTables);

                if ($isElemenComplete) {
                    $completedElemen++;
                    $kriteriaElemenComplete++;
                }

                $elemenProgress[$elemen->id] = [
                    'is_complete' => $isElemenComplete,
                    'has_desc' => $hasDesc,
                    'table_count' => $tableCount,
                    'has_all_tables' => $hasAllTables
                ];
            }

            $kriteriaProgress[$kriteria->id] = [
                'total_elemen' => $kriteriaElemenTotal,
                'completed_elemen' => $kriteriaElemenComplete,
                'percentage' => $kriteriaElemenTotal > 0
                    ? round(($kriteriaElemenComplete / $kriteriaElemenTotal) * 100)
                    : 0,
            ];
        }

        return [
            'total_elemen' => $totalElemen,
            'completed_elemen' => $completedElemen,
            'remaining_elemen' => $totalElemen - $completedElemen,
            'elemen_percentage' => $totalElemen > 0
                ? round(($completedElemen / $totalElemen) * 100)
                : 0,
            'kriteria_progress' => $kriteriaProgress,
            'elemen_progress' => $elemenProgress,
        ];
    }

    /**
     * ✅ Check if table has real data (not just template)
     */
    private function hasTableData($tableHtml)
    {
        if (empty($tableHtml) || strlen($tableHtml) < 100) {
            return false;
        }

        // Check if contains table tag
        if (strpos($tableHtml, '<table') === false) {
            return false;
        }

        // Extract all cell contents (td tags only, skip th headers)
        preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $tableHtml, $cells);

        if (empty($cells[1])) {
            return false;
        }

        $nonEmptyCells = 0;
        $totalDataCells = 0;

        foreach ($cells[1] as $cellContent) {
            $totalDataCells++;

            // Clean the cell content
            $cleaned = strip_tags($cellContent);
            $cleaned = html_entity_decode($cleaned);
            $cleaned = trim($cleaned);
            $cleaned = str_replace(['&nbsp;', '\u00A0', ' '], '', $cleaned);

            // Skip numbering cells (1, 2, 3, etc)
            if (is_numeric($cleaned) && (int)$cleaned <= 10) {
                continue;
            }

            if (!empty($cleaned) && strlen($cleaned) > 0) {
                $nonEmptyCells++;
            }
        }

        // ✅ Table has data if:
        // 1. At least 3 non-empty cells (excluding numbering)
        // 2. OR more than 30% of cells are filled
        $threshold = max(3, ceil($totalDataCells * 0.3));

        return $nonEmptyCells >= $threshold;
    }

    /**
     * ✅ SIMPLIFIED: Get existing borang data
     */
    private function getExistingBorangData($pengajuan)
    {
        return \App\Models\BorangData::where('id_pengajuan', $pengajuan->id)
            ->pluck('nilai', 'dataset_id')
            ->toArray();
    }

    /**
     * Save single field
     */
    public function saveBorangField(Request $request, $id)
    {
        $request->validate([
            'dataset_id' => 'required|string',
            'value' => [
                'required',
                function ($attribute, $value, $fail) {
                    $wordCount = str_word_count(strip_tags($value));
                    if ($wordCount > 1000) {
                        $fail('Batas maksimal pengisian di bagian ini adalah 1000 kata. Saat ini: ' . $wordCount . ' kata.');
                    }
                },
            ],
        ]);

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            DB::beginTransaction();

            $import = $this->getOrCreateBorangImport($pengajuan);

            \App\Models\BorangData::updateOrCreate(
                [
                    'id_pengajuan' => $pengajuan->id,
                    'dataset_id' => $request->dataset_id
                ],
                [
                    'nilai' => $request->value,
                    'id_borang_import' => $import->id
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save borang field failed', [
                'pengajuan_id' => $id,
                'dataset_id' => $request->dataset_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ FIXED: Save all (route was wrong)
     */
    public function saveBorangOnline(Request $request, $id)
    {
        $request->validate([
            'id_elemen' => 'nullable|exists:elemen_standar,id',
            'data' => 'required|array'
        ]);

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            DB::beginTransaction();

            $import = $this->getOrCreateBorangImport($pengajuan);
            $savedCount = 0;

            foreach ($request->data as $datasetId => $value) {
                if ($value) {
                    \App\Models\BorangData::updateOrCreate(
                        [
                            'id_pengajuan' => $pengajuan->id,
                            'dataset_id' => $datasetId
                        ],
                        [
                            'nilai' => is_array($value) ? json_encode($value) : $value,
                            'id_borang_import' => $import->id
                        ]
                    );
                    $savedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'saved_count' => $savedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save borang online failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get existing data
     */
    public function getBorangData($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        $draftBorang = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'draft_borang')
            ->where('is_latest', true)
            ->first();

        if (!$draftBorang) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $import = \App\Models\BorangImport::where('id_dokumen', $draftBorang->id)
            ->where('status', 'manual_entry')
            ->first();

        if (!$import) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $borangData = \App\Models\BorangData::where('id_borang_import', $import->id)
            ->pluck('nilai', 'dataset_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $borangData
        ]);
    }

    /**
     * ✅ Reset borang data (clear all entries)
     */
    public function resetBorang(Request $request, $id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('resetBorang', $pengajuan);

            // ✅ UPDATED: Remove review_kesiapan statuses
            $allowedStatuses = [
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            ];

            if (!in_array($pengajuan->status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status Permohonan akreditasi tidak memungkinkan untuk reset borang.'
                ], 422);
            }

            $confirm = $request->input('confirm');
            if ($confirm !== 'RESET') {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfirmasi tidak valid. Ketik "RESET" untuk melanjutkan.'
                ], 422);
            }

            DB::beginTransaction();

            $authId = auth()->id();
            $deletedData = 0;
            $deletedImports = 0;

            // 1. Delete all borang data
            $deletedData = \App\Models\BorangData::where('id_pengajuan', $pengajuan->id)->delete();

            // 2. Delete all borang imports
            $imports = BorangImport::where('id_pengajuan', $pengajuan->id)->get();
            foreach ($imports as $import) {
                if ($import->stored_path && Storage::disk('local')->exists($import->stored_path)) {
                    Storage::disk('local')->delete($import->stored_path);
                }
                $import->delete();
                $deletedImports++;
            }

            // 3. Mark draft LED documents as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->update(['is_latest' => false]);

            // 4. Update Permohonan akreditasi status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                'tanggal_draft_borang' => null,
            ]);

            // 5. Log status change
            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                "Borang direset oleh prodi. Data dihapus: {$deletedData} entries, {$deletedImports} imports."
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Borang berhasil direset!',
                'data' => [
                    'deleted_data' => $deletedData,
                    'deleted_imports' => $deletedImports,
                    'new_status' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Reset borang failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset borang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get borang statistics (for confirmation modal)
     */
    public function getBorangStats($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('view', $pengajuan);

            $totalData = \App\Models\BorangData::where('id_pengajuan', $pengajuan->id)->count();
            $totalImports = BorangImport::where('id_pengajuan', $pengajuan->id)->count();

            $lastImport = BorangImport::where('id_pengajuan', $pengajuan->id)
                ->latest()
                ->first();

            $draftBorang = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->where('is_latest', true)
                ->first();

            // ✅ UPDATED: Remove review_kesiapan statuses
            $allowedStatuses = [
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            ];

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_data' => $totalData,
                    'total_imports' => $totalImports,
                    'last_import' => $lastImport ? [
                        'date' => $lastImport->imported_at?->format('d M Y H:i'),
                        'status' => $lastImport->status,
                    ] : null,
                    'draft_borang' => $draftBorang ? [
                        'filename' => $draftBorang->original_filename,
                        'uploaded_at' => $draftBorang->created_at->format('d M Y H:i'),
                    ] : null,
                    'can_reset' => in_array($pengajuan->status, $allowedStatuses)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get borang stats failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload file for borang
     */
    public function uploadBorangFile(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB
            'dataset_id' => 'required|string'
        ]);

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            DB::beginTransaction();

            // Upload file
            $file = $request->file('file');
            $filename = 'borang_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('permohonan-akreditasi/' . $pengajuan->id . '/borang_data', $filename, 'public');

            // Get or create borang import
            $import = $this->getOrCreateBorangImport($pengajuan);

            // Save file path to database
            \App\Models\BorangData::updateOrCreate(
                [
                    'id_borang_import' => $import->id,
                    'dataset_id' => $request->dataset_id
                ],
                [
                    'nilai' => $path // Store file path
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload borang file gagal: ' . $e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload dan import DOCX borang
     */
    public function importBorangDocx(Request $request, $id, $isAddVersion = false)
    {
        $request->validate([
            'docx_file' => 'required|file|mimes:doc,docx|max:10240',
            'keterangan' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            if (in_array($pengajuan->status, [
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            ])) {
                return response()->json(['success' => false, 'message' => 'Sedang menunggu validasi Dokumen. Perubahan dokumen tidak diizinkan untuk sementara.'], 403);
            }
            $user = auth()->user();

            // Authorization
            if ($user->role_selected !== 'admin_prodi' && $user->id !== $pengajuan->id_user_pengaju) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon maaf, Anda tidak memiliki akses ke bagian ini'
                ], 500);
            }

            // Ambil dokumen versi terbaru
            $latestDoc = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kualitatif')
                ->where('is_latest', true)
                ->first();

            // Tentukan versi dan dokumen ID
            if ($isAddVersion) {
                // Tambah versi baru
                $versi = ($latestDoc->versi ?? 0) + 1;
                if ($latestDoc) {
                    $latestDoc->update(['is_latest' => false]);
                }
                $dokumenId = null; // create new
            } else {
                // Re-upload versi existing
                if (!$latestDoc) {
                    // Jika belum ada dokumen, buat versi pertama otomatis
                    $versi = 1;
                    $dokumenId = null;
                } else {
                    $versi = $latestDoc->versi;
                    $dokumenId = $latestDoc->id;
                }
            }

            // Simpan file
            $file = $request->file('docx_file');
            $filename = "kualitatif_v{$versi}_" . time() . ".docx";
            $path = $file->storeAs("permohonan-akreditasi/{$pengajuan->id}/kualitatif", $filename, 'public');
            // Simpan / update dokumen
            $dokumen = PengajuanDokumen::updateOrCreate(
                ['id' => $dokumenId],
                [
                    'id_pengajuan'      => $pengajuan->id,
                    'jenis_dokumen'     => 'data_kualitatif',
                    'nama_file'         => $filename,
                    'path_file'         => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size'         => $file->getSize(),
                    'mime_type'         => $file->getMimeType(),
                    'uploaded_by'       => $user->id,
                    'keterangan'        => $request->keterangan ?? "Laporan Evaluasi Diri v{$versi}",
                    'versi'             => $versi,
                    'is_latest'         => true,
                ]
            );

            // Catat import borang (pending)
            $import = BorangImport::create([
                'id_pengajuan'      => $pengajuan->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path'       => $path,
                'status'            => 'pending',
                'imported_by'       => $user->id,
                'imported_at'       =>  now(),
            ]);

            // Dispatch job import borang
            ImportBorangDocxJob::dispatch($import->id, $pengajuan->id, storage_path("app/public/{$path}"));
            $import->update(['status' => 'processing']);
            // Log status upload/re-upload
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from'  => $pengajuan->status,
                'status_to'    => PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                'changed_by'   => $user->id,
                'changed_at'   => now(),
                'keterangan'   => ($isAddVersion ? 'Upload versi baru' : 'Re-upload') .
                    " (v{$versi}) : {$file->getClientOriginalName()}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isAddVersion
                    ? "Upload berhasil (versi {$versi})"
                    : "Re-upload berhasil (versi {$versi})",
                'data' => [
                    'import_id' => $import->id,
                    'dokumen_id' => $dokumen->id,
                    'versi' => $versi,
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import DOCX error', ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check import status
     */
    public function checkImportStatus($id, $importId)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);
            $borangImport = BorangImport::findOrFail($importId);

            return response()->json([
                'success' => true,
                'status' => $borangImport->status,
                'progress' => [
                    'sections' => [
                        'parsed' => $borangImport->parsed_sections,
                        'total' => $borangImport->total_sections,
                        'percentage' => $borangImport->section_percentage
                    ],
                    'tables' => [
                        'parsed' => $borangImport->parsed_tables,
                        'total' => $borangImport->total_tables,
                        'percentage' => $borangImport->completion_percentage
                    ]
                ],
                'errors' => $borangImport->parsing_errors ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('Check import status DOCX Error: ' . $e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template DOCX
     */
    public function downloadBorangTemplate($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $degreeLevel = $pengajuan->studyProgram->degreeLevel->code;
            $fileName = 'TEMPLATE_LAPORAN_EVALUASI_DIRI_' . $degreeLevel . '.docx';
            $templatePath = storage_path('app/public/templates/' . $fileName);

            if (!file_exists($templatePath)) {
                Artisan::call('borang:generate-template', [
                    '--degree_level' => $degreeLevel,
                ]);

                if (!file_exists($templatePath)) {
                    throw new \RuntimeException("Template belum berhasil dibuat: {$templatePath}");
                }
            }
            return response()->download($templatePath, $fileName);
        } catch (\Exception $e) {
            Log::error('Download template DOCX Error', [
                'error' => $e->getMessage()
            ]);

            $previous = URL::previous();
            $current = request()->fullUrl();

            if (!$previous || rtrim($previous, '/') === rtrim($current, '/')) {
                abort(500, 'Gagal mendownload template: ' . $e->getMessage());
            }

            return redirect()->to($previous)->with('error', 'Gagal mendownload template: ' . $e->getMessage());
        }
    }

    /**
     * Export borang yang sudah diisi ke DOCX
     */
    public function exportBorangDocx($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'pengaju',
                'borangData'
            ])->findOrFail($id);

            $exportService = new BorangExportService($pengajuan);
            $phpWord = $exportService->generate();
            // $exportService->cleanupTmpPdfImages();

            $fileName = 'LAPORAN_EVALUASI_DIRI_' . Str::slug($pengajuan->studyProgram->name) . '_' . date('Y-m-d') . '.docx';
            $tempFile = storage_path('app/temp/' . $fileName);

            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }

            $exportService->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Export DOCX Error', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal export borang: ' . $e->getMessage());
        }
    }

    /**
     * ✅ FIXED: Helper method - Get or create borang import record
     */
    private function getOrCreateBorangImport($pengajuan)
    {
        $draftBorang = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'draft_borang')
            ->where('is_latest', true)
            ->first();

        $authId = auth()->id();

        if (!$draftBorang) {
            $draftBorang = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'draft_borang',
                'nama_file' => 'online_form.txt',
                'path_file' => 'online_form',
                'original_filename' => 'Online Form',
                'file_size' => 0,
                'mime_type' => 'text/plain',
                'uploaded_by' => $authId,
                'keterangan' => 'Data dari form online',
                'versi' => 1,
                'is_latest' => true,
            ]);
        }

        $import = \App\Models\BorangImport::firstOrCreate(
            [
                'id_dokumen' => $draftBorang->id,
                'status' => 'manual_entry'
            ],
            [
                'id_pengajuan' => $pengajuan->id,
                'original_filename' => 'Online Form',
                'imported_by' => $authId,
                'imported_at' => now(),
                'total_sections' => 0,
                'total_tables' => 0,
                'parsed_sections' => 0,
                'parsed_tables' => 0,
            ]
        );

        return $import;
    }
    /**
     * Upload bukti pembayaran (Langkah 7)
     */
    public function uploadBuktiPembayaran(Request $request, $id)
    {
        $request->validate([
            'tanggal_pembayaran'    => 'required|date',
            'formulir_pembayaran'   => 'required|file|max:5120',
            // 'bukti_pembayaran'      => 'required|file|mimes:xlsx,pdf,jpg,jpeg,png|max:5120',
        ]);

        $pengajuan = PengajuanAkreditasi::with('pembayaran')->findOrFail($id);
        $this->authorize('update', $pengajuan);

        if ($pengajuan->pembayaran && $pengajuan->pembayaran->status == 'menunggu_pembayaran') {
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN)
                return back()->with('error', 'Status Permohonan akreditasi tidak sesuai untuk upload bukti pembayaran.');
        }

        DB::beginTransaction();
        try {
            // === Upload formulir pembayaran ===
            $formulirFile = $request->file('formulir_pembayaran');
            $formulirFilename = 'formulir_bayar_' . time() . '.' . $formulirFile->getClientOriginalExtension();
            $formulirPath = $formulirFile->storeAs("permohonan-akreditasi/{$pengajuan->id}/pembayaran", $formulirFilename, 'public');

            PengajuanDokumen::create([
                'id_pengajuan'      => $pengajuan->id,
                'jenis_dokumen'     => 'formulir_pembayaran',
                'nama_file'         => $formulirFilename,
                'path_file'         => $formulirPath,
                'original_filename' => $formulirFile->getClientOriginalName(),
                'file_size'         => $formulirFile->getSize(),
                'mime_type'         => $formulirFile->getMimeType(),
                'uploaded_by'       => Auth::id(),
                'is_latest'         => true,
            ]);

            // // === Upload bukti pembayaran ===
            // $buktiFile = $request->file('bukti_pembayaran');
            // $buktiFilename = 'bukti_bayar_' . time() . '.' . $buktiFile->getClientOriginalExtension();
            // $buktiPath = $buktiFile->storeAs("permohonan-akreditasi/{$pengajuan->id}/pembayaran", $buktiFilename, 'public');

            // // Tandai dokumen bukti pembayaran sebelumnya tidak terbaru
            // PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            //     ->where('jenis_dokumen', 'bukti_pembayaran')
            //     ->update(['is_latest' => false]);

            // PengajuanDokumen::create([
            //     'id_pengajuan'      => $pengajuan->id,
            //     'jenis_dokumen'     => 'bukti_pembayaran',
            //     'nama_file'         => $buktiFilename,
            //     'path_file'         => $buktiPath,
            //     'original_filename' => $buktiFile->getClientOriginalName(),
            //     'file_size'         => $buktiFile->getSize(),
            //     'mime_type'         => $buktiFile->getMimeType(),
            //     'uploaded_by'       => Auth::id(),
            //     'is_latest'         => true,
            // ]);

            // === Update atau buat record pembayaran ===
            $pembayaran = PengajuanPembayaran::updateOrCreate(
                ['id_pengajuan' => $pengajuan->id],
                [
                    'status_pembayaran'  => 'menunggu_verifikasi',
                    'tanggal_pembayaran' => $request->tanggal_pembayaran,
                    'bukti_path'         => $formulirPath,
                    'formulir_path'      => $formulirPath,
                    'nomor_invoice'      => $pengajuan->pembayaran->nomor_invoice ?? 'INV-' . Auth::id(),
                    'jumlah_pembayaran'  => $pengajuan->pembayaran->jumlah_pembayaran ?? PengajuanPembayaran::BIAYA_AKREDITASI,
                    'catatan_pembayaran'  => $request->catatan_pembayaran,
                ]
            );

            // === Update status Permohonan akreditasi ===
            $oldStatus = $pengajuan->status;
            $newStatus = PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN ?? $oldStatus;

            $pengajuan->update([
                'status' => $newStatus,
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                $newStatus,
                'Bukti pembayaran dan formulir diupload oleh prodi. Sedang menunggu verifikasi pembayaran oleh bagian Keuangan LAMDEPILAR'
            );

            DB::commit();

            return back()->with('success', 'Bukti dan formulir pembayaran berhasil diupload. Menunggu verifikasi dari Keuangan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload bukti pembayaran failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload borang final (Langkah 7)
     */
    /**
     * Upload borang final
     */
    public function uploadBorangFinal(Request $request, $id)
    {
        $request->validate([
            'borang_final' => 'required|file|mimes:docx|max:10240', // 10MB
            'keterangan' => 'nullable|string|max:500',
        ]);

        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            if (
                $pengajuan->status !== PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI ||
                $pengajuan->pembayaran->status_pembayaran !== 'terverifikasi'
            ) {
                return back()->with('error', 'Pembayaran harus diverifikasi terlebih dahulu.');
            }

            DB::beginTransaction();

            // Upload file
            $file = $request->file('borang_final');
            $filename = 'borang_final_' . time() . '.docx';
            $path = $file->storeAs('permohonan-akreditasi/' . $pengajuan->id . '/final', $filename, 'public');

            // ✅ Create document record - FIXED
            PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'borang_final',
                'nama_file' => $filename,                           // ✅ Added
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),               // ✅ Added
                'uploaded_by' => $authId,
                'keterangan' => $request->keterangan,
                'versi' => 1,
                'is_latest' => true,
            ]);

            // Update status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                'tanggal_borang_final' => now(),
            ]);

            // Log
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $oldStatus,
                'status_to' => PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => 'Dokumen final diupload',
            ]);

            DB::commit();

            return back()->with('success', 'Dokumen final berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload borang final failed: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen
     */
    public function downloadDokumen($id)
    {
        $dokumen = PengajuanDokumen::with('pengajuan')->findOrFail($id);
        $authUser = Auth::user();
        $pengajuan = $dokumen->pengajuan;

        $userStudyProgramIds = $authUser->studyPrograms()->pluck('study_programs.id')->toArray();
        $hasAccess = in_array($pengajuan->id_program_studi, $userStudyProgramIds)
            || $pengajuan->id_de_assigned === $authUser->id || $pengajuan->id_validator_assigned === $authUser->id
            || $authUser->role === 'admin';

        // if (!$hasAccess) {
        //     abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        // }
        // dd($dokumen->path_file);
        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        $filePath = Storage::disk('public')->path($dokumen->path_file);
        return response()->download($filePath, $dokumen->original_filename);
    }

    /**
     * Log status change
     */
    private function logStatus($pengajuan, $oldStatus, $newStatus, $keterangan = null)
    {
        $pengajuan->statusLog()->create([
            'status_from' => $oldStatus ?? 'new',
            'status_to' => $newStatus,
            'changed_by' => Auth::id(),
            'keterangan' => $keterangan,
            'changed_at' => now(),
        ]);
    }
}
