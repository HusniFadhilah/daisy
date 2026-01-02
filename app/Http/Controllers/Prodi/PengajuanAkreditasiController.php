<?php

namespace App\Http\Controllers\Prodi;

use App\Models\Kriteria;
use App\Models\BorangImport;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use App\Jobs\ImportBorangDocxJob;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\BorangExportService;
use App\Services\BorangParserService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PengajuanAkreditasiController extends Controller
{
    use AuthorizesRequests;
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

        // ✅ CHECK: Apakah ada pengajuan yang sudah dibuat DE?
        $pengajuanId = $request->get('pengajuan_id');
        $pengajuan = null;

        if ($pengajuanId) {
            $pengajuan = PengajuanAkreditasi::with('studyProgram')
                ->where('id', $pengajuanId)
                ->where('status', 'pengingat_dikirim')
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
            // ✅ CHECK: Update existing atau create new?
            if ($request->filled('pengajuan_id')) {
                // UPDATE EXISTING
                $pengajuan = PengajuanAkreditasi::where('id', $request->pengajuan_id)
                    ->where('status', 'pengingat_dikirim')
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
                    'id_program_studi' => $request->id_program_studi, // Bisa diganti jika DE salah pilih
                    'tahun_akreditasi' => $request->tahun_akreditasi,
                    'jenis_akreditasi' => $request->jenis_akreditasi,
                    'tanggal_pengajuan' => now(),
                    'catatan_pengaju' => $request->catatan_pengaju,
                    'status' => 'surat_permohonan_diterima',
                    'tanggal_surat_permohonan' => now(),
                ]);

                $this->logStatus($pengajuan, 'pengingat_dikirim', 'surat_permohonan_diterima', 'Data dilengkapi oleh prodi');
            } else {
                // CREATE NEW (jika prodi submit tanpa pengingat dari DE)
                $pengajuan = PengajuanAkreditasi::create([
                    'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan(),
                    'id_program_studi' => $request->id_program_studi,
                    'id_user_pengaju' => Auth::id(),
                    'tahun_akreditasi' => $request->tahun_akreditasi,
                    'jenis_akreditasi' => $request->jenis_akreditasi,
                    'tanggal_pengajuan' => now(),
                    'catatan_pengaju' => $request->catatan_pengaju,
                    'status' => 'surat_permohonan_diterima',
                    'tanggal_surat_permohonan' => now(),
                ]);

                $this->logStatus($pengajuan, null, 'surat_permohonan_diterima', 'Surat permohonan diajukan (tanpa pengingat)');
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
            'studyProgram.degreeLevel', // ✅ FIX
            'studyProgram.university',
            'pengaju',
            'deskEvaluator',
            'dokumen.uploader',
            'reviewKesiapan.reviewer',
            'pembayaran',
            'statusLog.changedBy'
        ])->findOrFail($id);

        // Check authorization
        $this->authorize('view', $pengajuan);

        return view('asesmen.pengajuan.show', compact('pengajuan'));
    }

    /**
     * Upload draft borang (DOCX only for processing)
     */
    public function uploadDraftBorang(Request $request, $id)
    {
        try {
            $request->validate([
                'draft_borang' => 'required|file|mimes:docx|max:10240',
                'keterangan' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error($e);
            // ✅ Return JSON for validation errors
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e; // Re-throw for non-AJAX requests
        }
        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            // Check status
            if (!in_array($pengajuan->status, [
                'borang_dikirim',
                'review_kesiapan_belum_siap',
                'draft_borang_diterima'
            ])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pengajuan tidak sesuai untuk upload draft borang.'
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

            // ✅ Create document record - FIXED with all required fields
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'draft_borang',
                'nama_file' => $filename,                           // ✅ Added
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),               // ✅ Added
                'uploaded_by' => $authId,
                'keterangan' => $request->keterangan ?? 'Upload draft borang versi ' . $newVersion,
                'versi' => $newVersion,
                'is_latest' => true,
            ]);

            // Update pengajuan status (only if not already draft_borang_diterima)
            $oldStatus = $pengajuan->status;
            if ($pengajuan->status !== 'draft_borang_diterima') {
                $pengajuan->update([
                    'status' => 'draft_borang_diterima',
                    'tanggal_draft_borang' => now(),
                ]);

                // Log status change
                PengajuanStatusLog::create([
                    'id_pengajuan' => $pengajuan->id,
                    'status_from' => $oldStatus,
                    'status_to' => 'draft_borang_diterima',
                    'changed_by' => $authId,
                    'changed_at' => now(),
                    'keterangan' => 'Draft borang diupload (versi ' . $newVersion . ')',
                ]);
            } else {
                // Just log the re-upload
                PengajuanStatusLog::create([
                    'id_pengajuan' => $pengajuan->id,
                    'status_from' => $oldStatus,
                    'status_to' => $oldStatus,
                    'changed_by' => $authId,
                    'changed_at' => now(),
                    'keterangan' => 'Draft borang diupload ulang (versi ' . $newVersion . '): ' . ($request->keterangan ?? 'Revisi dokumen'),
                ]);
            }

            DB::commit();

            // Return JSON for AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft borang berhasil diupload (Versi ' . $newVersion . ')',
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

            return back()->with('success', 'Draft borang berhasil diupload (Versi ' . $newVersion . ').');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Upload draft borang failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'user_id' => $authId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors()['draft_borang'] ?? ['Unknown error']),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload draft borang failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'user_id' => $authId,
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
    public function processBorangDOCX(Request $request, $id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            // Get latest draft borang
            $draftBorang = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->where('is_latest', true)
                ->firstOrFail();

            // Check cooldown (prevent re-processing within 1 hour)
            $latestImport = $pengajuan->latestBorangImport;
            if ($latestImport && $latestImport->imported_at->diffInMinutes(now()) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon tunggu minimal 10 menit sebelum memproses ulang. Terakhir diproses: ' . $latestImport->imported_at->diffForHumans()
                ], 429);
            }

            // Process using parser service
            $parserService = new \App\Services\BorangParserService();
            $import = $parserService->parseBorangDOCX($draftBorang);

            return response()->json([
                'success' => true,
                'message' => 'Pembacaan data borang berhasil!',
                'data' => [
                    'id' => $import->id,
                    'total_sections' => $import->total_sections,
                    'total_tables' => $import->total_tables,
                    'parsed_sections' => $import->parsed_sections,
                    'parsed_tables' => $import->parsed_tables,
                    'completion' => $import->completion_percentage,
                    'status' => $import->status,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draft borang tidak ditemukan. Silakan upload terlebih dahulu.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Process borang DOCX failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses borang: ' . $e->getMessage()
            ], 500);
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
     * ✅ NEW: Finalize & Submit Borang Online
     */
    public function submitBorangOnline(Request $request, $id)
    {
        $request->validate([
            // 'lembar_pengesahan' => 'required|file|mimes:pdf|max:5120', // 5MB
            // 'nama_ketua_prodi' => 'required|string|max:255',
            // 'nip_ketua_prodi' => 'required|string|max:50',
            // 'nama_wakil_ketua' => 'nullable|string|max:255',
            // 'nip_wakil_ketua' => 'nullable|string|max:50',
            // 'tanggal_pengesahan' => 'required|date',
            // 'tempat_pengesahan' => 'required|string|max:255',
            // 'scan_pengesahan' => 'required|file|mimes:pdf|max:5120', // 5MB
            // 'keterangan' => 'nullable|string|max:1000',
        ]);

        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            // ✅ Validate: Check if all fields are filled
            $import = $this->getOrCreateBorangImport($pengajuan);

            // Count total fields required
            $kriterias = \App\Models\Kriteria::with(['elemenStandar.datasetBorang'])->get();
            $totalRequiredFields = 0;
            foreach ($kriterias as $kriteria) {
                foreach ($kriteria->elemenStandar as $elemen) {
                    $datasetCount = $elemen->datasetBorang->count();
                    $totalRequiredFields += ($datasetCount > 0) ? $datasetCount : 1;
                }
            }

            // Count filled fields
            $filledFields = \App\Models\BorangData::where('id_borang_import', $import->id)
                ->whereNotNull('nilai')
                ->count();

            if ($filledFields < $totalRequiredFields) {
                return response()->json([
                    'success' => false,
                    'message' => "Borang belum lengkap! Anda baru mengisi $filledFields dari $totalRequiredFields field.",
                    'filled' => $filledFields,
                    'required' => $totalRequiredFields,
                ], 422);
            }

            DB::beginTransaction();

            // Upload scan pengesahan
            $file = $request->file('scan_pengesahan');
            if ($file) {
                $filename = 'pengesahan_' . time() . '.pdf';
                $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/pengesahan', $filename, 'public');

                // Create document record
                PengajuanDokumen::create([
                    'id_pengajuan' => $pengajuan->id,
                    'jenis_dokumen' => 'lembar_pengesahan',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => $authId,
                    'keterangan' => $request->keterangan,
                    'versi' => 1,
                    'is_latest' => true,
                ]);

                // Save lembar pengesahan data to borang_data
                $pengesahanData = [
                    // 'nama_ketua_prodi' => $request->nama_ketua_prodi,
                    // 'nip_ketua_prodi' => $request->nip_ketua_prodi,
                    // 'nama_wakil_ketua' => $request->nama_wakil_ketua,
                    // 'nip_wakil_ketua' => $request->nip_wakil_ketua,
                    // 'tanggal_pengesahan' => $request->tanggal_pengesahan,
                    // 'tempat_pengesahan' => $request->tempat_pengesahan,
                    'scan_path' => $path,
                ];

                \App\Models\BorangData::updateOrCreate(
                    [
                        'id_borang_import' => $import->id,
                        'dataset_id' => 'lembar_pengesahan'
                    ],
                    [
                        'nilai' => json_encode($pengesahanData)
                    ]
                );
            }

            // Update import status
            $import->update([
                'status' => 'completed',
                'imported_at' => now(),
            ]);

            // Update pengajuan status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'borang_online_selesai',
                'tanggal_draft_borang' => now(),
            ]);

            // Log status change
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $oldStatus,
                'status_to' => 'borang_online_selesai',
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => 'Borang online difinalisasi dan di-submit',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Borang evaluasi diri berhasil disubmit!',
                'data' => [
                    'status' => $pengajuan->status,
                    'submitted_at' => now()->format('d M Y H:i'),
                    'total_fields' => $totalRequiredFields,
                    'filled_fields' => $filledFields,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Submit borang online failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal submit borang: ' . $e->getMessage()
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

        // ✅ Get existing data
        $existingData = $this->getExistingBorangData($pengajuan);

        // ✅ Calculate detailed progress
        $progressData = $this->calculateBorangProgress($kriterias, $existingData);
        // dd($progressData);
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
        $totalFields = 0;
        $filledFields = 0;
        $totalElemen = 0;
        $completedElemen = 0;
        $kriteriaProgress = [];
        $elemenProgress = [];

        foreach ($kriterias as $kriteria) {
            $kriteriaTotal = 0;
            $kriteriaFilled = 0;
            $kriteriaElemenTotal = 0;
            $kriteriaElemenComplete = 0;

            foreach ($kriteria->elemenStandar as $elemen) {
                $elemenTotal = 0;
                $elemenFilled = 0;

                $totalElemen++;
                $kriteriaElemenTotal++;

                // ✅ Check description field (desc_{elemen_id})
                $descKey = 'desc_' . $elemen->id;
                $elemenTotal++;
                $totalFields++;

                $hasDesc = false;
                if (isset($existingData[$descKey])) {
                    $descValue = trim($existingData[$descKey]);
                    // Check if has meaningful content (not just placeholder/empty)
                    if (!empty($descValue) && strlen($descValue) > 20) {
                        $elemenFilled++;
                        $filledFields++;
                        $hasDesc = true;
                    }
                }

                // ✅ Check dataset fields (tables)
                $hasAllTables = true;
                foreach ($elemen->datasetBorang as $dataset) {
                    if ($dataset->tipe_field === 'table') {
                        $elemenTotal++;
                        $totalFields++;

                        if (isset($existingData[$dataset->kode])) {
                            $tableValue = trim($existingData[$dataset->kode]);

                            // ✅ Check if has REAL data (improved method)
                            if ($this->hasTableData($tableValue)) {
                                $elemenFilled++;
                                $filledFields++;
                            }
                        }
                    }
                }

                // ✅ Elemen is complete if ALL fields are filled
                $isElemenComplete = ($elemenFilled === $elemenTotal && $elemenTotal > 0);

                if ($isElemenComplete) {
                    $completedElemen++;
                    $kriteriaElemenComplete++;
                }

                // Store elemen progress
                $elemenProgress[$elemen->id] = [
                    'total' => $elemenTotal,
                    'filled' => $elemenFilled,
                    'percentage' => $elemenTotal > 0 ? round(($elemenFilled / $elemenTotal) * 100) : 0,
                    'is_complete' => $isElemenComplete,
                    'has_desc' => $hasDesc,
                    'has_all_tables' => $hasAllTables
                ];

                $kriteriaTotal += $elemenTotal;
                $kriteriaFilled += $elemenFilled;
            }

            // Store kriteria progress
            $kriteriaProgress[$kriteria->id] = [
                'total_fields' => $kriteriaTotal,
                'filled_fields' => $kriteriaFilled,
                'total_elemen' => $kriteriaElemenTotal,
                'completed_elemen' => $kriteriaElemenComplete,
                'percentage' => $kriteriaTotal > 0 ? round(($kriteriaFilled / $kriteriaTotal) * 100) : 0,
            ];
        }

        return [
            // ✅ Field-level metrics
            'total_fields' => $totalFields,
            'filled_fields' => $filledFields,
            'remaining_fields' => $totalFields - $filledFields,
            'field_percentage' => $totalFields > 0 ? round(($filledFields / $totalFields) * 100) : 0,

            // ✅ Elemen-level metrics
            'total_elemen' => $totalElemen,
            'completed_elemen' => $completedElemen,
            'remaining_elemen' => $totalElemen - $completedElemen,
            'elemen_percentage' => $totalElemen > 0 ? round(($completedElemen / $totalElemen) * 100) : 0,

            // Detail
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

            // Get or create borang import
            $import = $this->getOrCreateBorangImport($pengajuan);

            // ✅ FIXED: WHERE clause hanya (id_pengajuan, dataset_id)
            // id_borang_import tetap disimpan tapi tidak jadi WHERE clause
            \App\Models\BorangData::updateOrCreate(
                [
                    'id_pengajuan' => $pengajuan->id,
                    'dataset_id' => $request->dataset_id  // ✅ 2 fields only
                ],
                [
                    'nilai' => $request->value,
                    'id_borang_import' => $import->id  // ✅ Saved in data, not WHERE
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save borang field failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'dataset_id' => $request->dataset_id,
                'trace' => $e->getTraceAsString()
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

            // Get or create borang import
            $import = $this->getOrCreateBorangImport($pengajuan);

            $savedCount = 0;

            // Save each field
            foreach ($request->data as $datasetId => $value) {
                if ($value) {
                    // ✅ FIXED: Consistent WHERE clause
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

            Log::error('Save borang online failed: ' . $e->getMessage());

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

        // Get latest borang import
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

        // Get import record
        $import = \App\Models\BorangImport::where('id_dokumen', $draftBorang->id)
            ->where('status', 'manual_entry')
            ->first();

        if (!$import) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        // Get all borang data for this import
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
            // Validate: Only allow reset in certain statuses
            $allowedStatuses = [
                'borang_dikirim',
                'draft_borang_diterima',
                'borang_online_selesai',
                'review_kesiapan_belum_siap'
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
                // Delete related file if exists
                if ($import->stored_path && Storage::disk('local')->exists($import->stored_path)) {
                    Storage::disk('local')->delete($import->stored_path);
                }
                $import->delete();
                $deletedImports++;
            }

            // 3. Mark draft borang documents as not latest (keep for history)
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->update(['is_latest' => false]);

            // 4. Update pengajuan status (back to borang_dikirim)
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'borang_dikirim',
                'tanggal_draft_borang' => null,
                'tanggal_borang_final' => null,
                'borang_status' => null,
                'borang_imported_at' => null,
            ]);

            // 5. Log status change
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $oldStatus,
                'status_to' => 'borang_dikirim',
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => "Borang direset oleh prodi. Data dihapus: {$deletedData} entries, {$deletedImports} imports.",
            ]);

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
                    'new_status' => 'borang_dikirim',
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Reset borang failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
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
                    'can_reset' => in_array($pengajuan->status, [
                        'borang_dikirim',
                        'draft_borang_diterima',
                        'borang_online_selesai',
                        'review_kesiapan_belum_siap'
                    ])
                ]
            ]);
        } catch (\Exception $e) {
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
    public function importBorangDocx(Request $request, $id)
    {
        $request->validate([
            'docx_file' => 'required|file|mimes:docx,doc|max:10240' // Max 10MB
        ]);

        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $authUser = auth()->user();
            // Check authorization
            if ($authUser->role !== 'prodi' && $authUser->id_prodi !== $pengajuan->id_prodi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengupload & memproses borang ini'
                ], 403);
            }

            $file = $request->file('docx_file');
            $fileName = 'borang_import_' . $pengajuan->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('temp/imports', $fileName);
            $fullPath = Storage::disk('local')->path($filePath);

            if (!file_exists($fullPath) || !is_readable($fullPath)) {
                Log::error('DOCX file not found or unreadable: ' . $fullPath);
                return response()->json([
                    'success' => false,
                    'message' => 'File DOCX tidak bisa diakses'
                ], 500);
            }

            // Create import record
            $borangImport = BorangImport::create([
                'id_pengajuan' => $pengajuan->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $filePath,
                'status' => 'pending',
                'imported_by' => $authUser->id,
            ]);

            // Count total sections and tables (estimate)
            $kriteriaCount = \App\Models\Kriteria::count();
            $elemenCount = \App\Models\ElemenStandar::count();
            $tableCount = \App\Models\DatasetBorang::where('tipe_field', 'table')->count();

            $borangImport->update([
                'total_sections' => $elemenCount,
                'total_tables' => $tableCount
            ]);

            // Dispatch job
            ImportBorangDocxJob::dispatch($pengajuan->id, $fullPath);

            return response()->json([
                'success' => true,
                'message' => 'File DOCX sedang diproses. Silakan tunggu beberapa saat dan refresh halaman.',
                'import_id' => $borangImport->id
            ]);
        } catch (\Exception $e) {
            Log::error('Import DOCX Error: ' . $e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload dan memproses file: ' . $e->getMessage()
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

            $templatePath = storage_path('app/public/templates/TEMPLATE_BORANG_EVALUASI_DIRI.docx');

            if (!file_exists($templatePath)) {
                // Generate template if not exists
                Artisan::call('db:seed', ['--class' => 'BorangExampleSeeder']);
            }

            return response()->download(
                $templatePath,
                'Template_Borang_LED.docx'
            );
        } catch (\Exception $e) {
            Log::error('Download template DOCX Error: ' . $e);
            return back()->with('error', 'Gagal mendownload template: ' . $e->getMessage());
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

            // Generate DOCX dengan data yang sudah diisi
            $exportService = new BorangExportService($pengajuan);
            $phpWord = $exportService->generate();

            $fileName = 'Borang_' . str_replace('/', '_', $pengajuan->nomor_pengajuan) . '_' . date('Y-m-d') . '.docx';
            // $fileName = 'Template_Laporan_Evaluasi_Program Studi-LAMDEPILAR' . str_replace('/', '_', $pengajuan->nomor_pengajuan) . '_' . date('Y-m-d') . '.docx';
            $tempFile = storage_path('app/temp/' . $fileName);

            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }

            $exportService->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Export DOCX Error: ' . $e);
            return back()->with('error', 'Gagal export borang: ' . $e->getMessage());
        }
    }

    /**
     * ✅ FIXED: Helper method - Get or create borang import record
     */
    private function getOrCreateBorangImport($pengajuan)
    {
        // Get latest draft borang document
        $draftBorang = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'draft_borang')
            ->where('is_latest', true)
            ->first();
        $authId = auth()->id();

        if (!$draftBorang) {
            // Create dummy draft record for online form
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

        // ✅ FIXED: Use 'id_dokumen' not 'id_pengajuan_dokumen'
        $import = \App\Models\BorangImport::firstOrCreate(
            [
                'id_dokumen' => $draftBorang->id,  // ✅ Correct column name
                'status' => 'manual_entry'
            ],
            [
                'id_pengajuan' => $pengajuan->id,  // ✅ Add pengajuan ID
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
            'bukti_pembayaran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tanggal_pembayaran' => 'required|date',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        if ($pengajuan->status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk upload bukti pembayaran.');
        }

        DB::beginTransaction();
        try {
            // Upload bukti pembayaran
            $file = $request->file('bukti_pembayaran');
            $filename = 'bukti_bayar_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/pembayaran', $filename, 'public');

            PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'bukti_pembayaran',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => Auth::id(),
                'is_latest' => true,
            ]);

            // Update pembayaran
            $pengajuan->pembayaran->update([
                'status_pembayaran' => 'dibayar',
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

            // Update pengajuan status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'pembayaran_diterima',
                'tanggal_pembayaran' => now(),
            ]);

            $this->logStatus($pengajuan, $oldStatus, 'pembayaran_diterima', 'Bukti pembayaran diupload');

            DB::commit();

            return back()->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi dari DE.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload bukti pembayaran failed: ' . $e->getMessage());
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
                $pengajuan->pembayaran->status_pembayaran !== 'verified'
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

        // Check authorization
        $authUser = Auth::user();
        $pengajuan = $dokumen->pengajuan;

        $userStudyProgramIds = $authUser->studyPrograms()->pluck('study_programs.id')->toArray();
        $hasAccess = in_array($pengajuan->id_program_studi, $userStudyProgramIds)
            || $pengajuan->id_de_assigned === $authUser->id
            || $authUser->role === 'admin';

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Get full path
        $filePath = Storage::disk('public')->path($dokumen->path_file);

        // Return download response
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
