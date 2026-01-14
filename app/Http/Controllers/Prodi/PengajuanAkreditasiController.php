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

        return view('asesmen.pengajuan.index', compact('pengajuans'));
    }

    /**
     * Show the form for creating a new pengajuan (Langkah 2)
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $prodiUser = $user->studyPrograms()->with(['degreeLevel', 'university'])->first();
        $prodis = $prodiUser ? null : StudyProgram::all();

        // Check if there's existing pengajuan from DE
        $pengajuanId = $request->get('pengajuan_id');
        $pengajuan = null;

        if ($pengajuanId) {
            $pengajuan = PengajuanAkreditasi::with('studyProgram')
                ->where('id', $pengajuanId)
                ->where('status', PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM)
                ->whereNull('id_user_pengaju')
                ->first();

            // Check authorization
            if ($pengajuan) {
                $userStudyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();
                if (!in_array($pengajuan->id_program_studi, $userStudyProgramIds)) {
                    abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
                }
            }
        }

        return view('asesmen.pengajuan.create', compact('prodiUser', 'prodis', 'pengajuan'));
    }

    /**
     * Store a newly created pengajuan (Langkah 2: Submit Surat Permohonan)
     */
    public function store(Request $request)
    {
        $request->validate([
            'pengajuan_id' => 'nullable|exists:pengajuan_akreditasi,id',
            'id_program_studi' => 'required|exists:study_programs,id',
            'tahun_akreditasi' => 'required|integer|min:2024',
            'jenis_akreditasi' => 'required|in:baru,perpanjangan,re-akreditasi',
            'catatan_pengaju' => 'nullable|string',
            'surat_permohonan' => 'required|file|mimes:pdf|max:5120', // 5MB
        ]);

        DB::beginTransaction();
        try {
            // UPDATE existing OR CREATE new
            if ($request->filled('pengajuan_id')) {
                // UPDATE EXISTING
                $pengajuan = PengajuanAkreditasi::where('id', $request->pengajuan_id)
                    ->where('status', PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM)
                    ->whereNull('id_user_pengaju')
                    ->firstOrFail();

                // Check authorization
                $user = Auth::user();
                $userStudyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();
                if (!in_array($pengajuan->id_program_studi, $userStudyProgramIds)) {
                    abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
                }

                // Update pengajuan
                $pengajuan->update([
                    'id_user_pengaju' => Auth::id(),
                    'id_program_studi' => $request->id_program_studi,
                    'tahun_akreditasi' => $request->tahun_akreditasi,
                    'jenis_akreditasi' => $request->jenis_akreditasi,
                    'tanggal_pengajuan' => now(),
                    'catatan_pengaju' => $request->catatan_pengaju,
                    'status' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                    'tanggal_surat_permohonan' => now(),
                ]);

                $this->logStatus(
                    $pengajuan,
                    PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                    PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                    'Data dilengkapi oleh prodi'
                );
            } else {
                // CREATE NEW (if prodi submits without DE reminder)
                $pengajuan = PengajuanAkreditasi::create([
                    'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan(),
                    'id_program_studi' => $request->id_program_studi,
                    'id_user_pengaju' => Auth::id(),
                    'tahun_akreditasi' => $request->tahun_akreditasi,
                    'jenis_akreditasi' => $request->jenis_akreditasi,
                    'tanggal_pengajuan' => now(),
                    'catatan_pengaju' => $request->catatan_pengaju,
                    'status' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                    'tanggal_surat_permohonan' => now(),
                ]);

                $this->logStatus(
                    $pengajuan,
                    null,
                    PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                    'Surat permohonan diajukan (tanpa pengingat)'
                );
            }

            // Upload surat permohonan
            if ($request->hasFile('surat_permohonan')) {
                $file = $request->file('surat_permohonan');
                $filename = 'surat_permohonan_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/surat', $filename, 'public');

                PengajuanDokumen::create([
                    'id_pengajuan' => $pengajuan->id,
                    'jenis_dokumen' => 'surat_permohonan',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => Auth::id(),
                    'is_latest' => true,
                ]);
            }

            DB::commit();

            $message = $request->filled('pengajuan_id')
                ? 'Data pengajuan berhasil dilengkapi. Nomor pengajuan: ' . $pengajuan->nomor_pengajuan
                : 'Pengajuan akreditasi berhasil disubmit. Nomor pengajuan: ' . $pengajuan->nomor_pengajuan;

            return redirect()->route('pengajuan.show', $pengajuan->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Store pengajuan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
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

        return view('asesmen.pengajuan.show', compact('pengajuan'));
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
                    'message' => 'Status pengajuan tidak sesuai untuk upload draft LED.'
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
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/draft_borang', $filename, 'public');

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

            // Update pengajuan status (only if not already draft_borang_diterima)
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
     * Process borang DOCX - extract data
     */
    // public function processBorangDOCX(Request $request, $id)
    // {
    //     try {
    //         $pengajuan = PengajuanAkreditasi::findOrFail($id);
    //         $this->authorize('update', $pengajuan);

    //         // Get latest draft LED
    //         $draftBorang = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
    //             ->where('jenis_dokumen', 'draft_borang')
    //             ->where('is_latest', true)
    //             ->firstOrFail();

    //         // Check cooldown (prevent re-processing within 10 minutes)
    //         $latestImport = $pengajuan->latestBorangImport;
    //         if ($latestImport && $latestImport->imported_at->diffInMinutes(now()) < 10) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Mohon tunggu minimal 10 menit sebelum memproses ulang. Terakhir diproses: ' . $latestImport->imported_at->diffForHumans()
    //             ], 429);
    //         }

    //         // Process using parser service
    //         $parserService = new BorangParserService();
    //         $import = $parserService->parseBorangDOCX($draftBorang);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pembacaan data borang berhasil!',
    //             'data' => [
    //                 'id' => $import->id,
    //                 'total_sections' => $import->total_sections,
    //                 'total_tables' => $import->total_tables,
    //                 'parsed_sections' => $import->parsed_sections,
    //                 'parsed_tables' => $import->parsed_tables,
    //                 'completion' => $import->completion_percentage,
    //                 'status' => $import->status,
    //             ]
    //         ]);
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Draft LED tidak ditemukan. Silakan upload terlebih dahulu.'
    //         ], 404);
    //     } catch (\Exception $e) {
    //         Log::error('Process borang DOCX failed', [
    //             'pengajuan_id' => $id,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal memproses borang: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

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

            // Update pengajuan status
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
                'message' => 'Laporan Evaluasi Diri berhasil disubmit!',
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
            'programStudi.university',
            'programStudi.degreeLevel'
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

        return view('asesmen.pengajuan.borang-online', compact(
            'pengajuan',
            'kriterias',
            'existingData',
            'progressData'
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
            'value' => 'required'
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
                    'message' => 'Status pengajuan tidak memungkinkan untuk reset borang.'
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

            // 4. Update pengajuan status
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

            Log::info('Borang reset successfully', [
                'pengajuan_id' => $pengajuan->id,
                'user_id' => $authId,
                'deleted_data' => $deletedData,
                'deleted_imports' => $deletedImports,
            ]);

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
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/borang_data', $filename, 'public');

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
        ]);

        DB::beginTransaction();

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $user = auth()->user();

            // Authorization
            if ($user->role !== 'admin_prodi' && $user->id !== $pengajuan->id_user_pengaju) {
                abort(403, 'Tidak memiliki akses');
            }

            // Ambil dokumen latest
            $latestDoc = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kualitatif')
                ->where('is_latest', true)
                ->first();

            // Tentukan versi
            if ($isAddVersion) {
                $versi = ($latestDoc->versi ?? 0) + 1;
                if ($latestDoc) $latestDoc->update(['is_latest' => false]);
            } else {
                if (!$latestDoc) {
                    throw new \Exception('Dokumen versi terbaru tidak ditemukan');
                }
                $versi = $latestDoc->versi;
            }

            // Simpan file
            $file = $request->file('docx_file');
            $filename = "kualitatif_v{$versi}_" . time() . ".docx";
            $path = $file->storeAs("pengajuan/{$pengajuan->id}/kualitatif", $filename, 'public');

            // Simpan / update dokumen
            $dokumen = PengajuanDokumen::updateOrCreate(
                ['id' => $isAddVersion ? null : $latestDoc->id],
                [
                    'id_pengajuan' => $pengajuan->id,
                    'jenis_dokumen' => 'data_kualitatif',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => $user->id,
                    'keterangan' => $request->keterangan ?? "Laporan Evaluasi Diri v{$versi}",
                    'versi' => $versi,
                    'is_latest' => true,
                ]
            );

            // Import record
            $import = BorangImport::create([
                'id_pengajuan' => $pengajuan->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'status' => 'pending',
                'imported_by' => $user->id,
            ]);

            ImportBorangDocxJob::dispatch($pengajuan->id, storage_path("app/public/{$path}"));

            // Log
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => $user->id,
                'changed_at' => now(),
                'keterangan' => ($isAddVersion ? 'Upload versi baru' : 'Re-upload') .
                    " (v{$versi}) : {$file->getClientOriginalName()}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isAddVersion
                    ? "Upload berhasil (versi {$versi})"
                    : "Re-upload berhasil (versi {$versi})",
                'data' => [
                    'dokumen_id' => $dokumen->id,
                    'versi' => $versi
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

            $fileName = 'LAPORAN_EVALUASI_DIRI_' . str_replace('/', '_', $pengajuan->nomor_pengajuan) . '_' . date('Y-m-d') . '.docx';
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
            'bukti_pembayaran'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tanggal_pembayaran' => 'required|date',
        ]);

        $pengajuan = PengajuanAkreditasi::with('pembayaran')->findOrFail($id);
        $this->authorize('update', $pengajuan);

        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN) {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk upload bukti pembayaran.');
        }

        DB::beginTransaction();
        try {
            // 1) Upload file bukti pembayaran (dokumen)
            $file = $request->file('bukti_pembayaran');
            $filename = 'bukti_bayar_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("pengajuan/{$pengajuan->id}/pembayaran", $filename, 'public');

            // Nonaktifkan dokumen bukti pembayaran sebelumnya (biar latest konsisten)
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'bukti_pembayaran')
                ->update(['is_latest' => false]);

            PengajuanDokumen::create([
                'id_pengajuan'        => $pengajuan->id,
                'jenis_dokumen'       => 'bukti_pembayaran',
                'nama_file'           => $filename,
                'path_file'           => $path,
                'original_filename'   => $file->getClientOriginalName(),
                'file_size'           => $file->getSize(),
                'mime_type'           => $file->getMimeType(),
                'uploaded_by'         => Auth::id(),
                'is_latest'           => true,
            ]);

            // 2) ✅ Pastikan record pembayaran ada (create jika belum ada)
            $pembayaran = PengajuanPembayaran::firstOrCreate(
                ['id_pengajuan' => $pengajuan->id],
                [
                    // Isi default minimal (sesuaikan kebutuhan Anda)
                    'status_pembayaran' => 'menunggu',   // atau 'menunggu_pembayaran'
                    'nomor_invoice' => 'INV-' . Auth::id(), // kalau ada
                    'jumlah_pembayaran' => 10000000,
                ]
            );

            // 3) Update pembayaran menjadi "dibayar"
            $pembayaran->update([
                'status_pembayaran'      => 'dibayar',
                'tanggal_pembayaran'     => $request->tanggal_pembayaran,
                // ✅ simpan path bukti di tabel pembayaran agar keuangan mudah ambil dari relasi
                'bukti_path'  => $path, // atau 'bukti_path' sesuai kolom Anda
            ]);

            // 4) Update status pengajuan: tetap menunggu verifikasi (jangan langsung diterima)
            $oldStatus = $pengajuan->status;

            // Pilih salah satu:
            // a) tetap STATUS_MENUNGGU_PEMBAYARAN tapi pembayaran.status=dibayar
            // b) pakai status baru misal STATUS_PEMBAYARAN_DIBAYAR / STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN
            // Saya sarankan opsi (b) agar jelas.

            $newStatus = defined(PengajuanAkreditasi::class . '::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN')
                ? PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN
                : $oldStatus;

            $pengajuan->update([
                'status' => $newStatus,
                // kalau Anda punya tanggal khusus untuk "upload bukti" pisahkan dari tanggal verifikasi
                // 'tanggal_upload_pembayaran' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                $newStatus,
                'Bukti pembayaran diupload oleh prodi (status pembayaran: dibayar)'
            );

            DB::commit();

            return back()->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi dari Keuangan.');
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
                $pengajuan->status !== 'pembayaran_diterima' ||
                $pengajuan->pembayaran->status_pembayaran !== 'terverifikasi'
            ) {
                return back()->with('error', 'Pembayaran harus diverifikasi terlebih dahulu.');
            }

            DB::beginTransaction();

            // Upload file
            $file = $request->file('borang_final');
            $filename = 'borang_final_' . time() . '.docx';
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/final', $filename, 'public');

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
                'status' => 'borang_final_diterima',
                'tanggal_borang_final' => now(),
            ]);

            // Log
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $oldStatus,
                'status_to' => 'borang_final_diterima',
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => 'Borang final diupload',
            ]);

            DB::commit();

            return back()->with('success', 'Borang final berhasil diupload.');
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
