<?php

namespace App\Http\Controllers\Prodi;

use App\Models\BorangImport;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\BorangParserService;
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
                'uploaded_by' => auth()->id(),
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
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => 'Draft borang diupload (versi ' . $newVersion . ')',
                ]);
            } else {
                // Just log the re-upload
                PengajuanStatusLog::create([
                    'id_pengajuan' => $pengajuan->id,
                    'status_from' => $oldStatus,
                    'status_to' => $oldStatus,
                    'changed_by' => auth()->id(),
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
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $e->errors()['draft_borang'] ?? ['Unknown error']),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload draft borang failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'user_id' => auth()->id(),
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
            if ($latestImport && $latestImport->imported_at->diffInMinutes(now()) < 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon tunggu minimal 1 jam sebelum memproses ulang. Terakhir diproses: ' . $latestImport->imported_at->diffForHumans()
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
        $pengajuan = PengajuanAkreditasi::with('studyProgram.university')->findOrFail($id);
        $this->authorize('update', $pengajuan);

        // Load kriteria with elemen
        $kriteria = \App\Models\Kriteria::with([
            'elemenStandar.pernyataan',
            'elemenStandar.indikator.jenisIndikator'
        ])->orderBy('kode_kriteria')->get();

        return view('asesmen.pengajuan.borang-online', compact('pengajuan', 'kriteria'));
    }

    /**
     * Save borang online (AJAX)
     */
    public function saveBorangOnline(Request $request, $id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            // Save logic here...
            // You can save to database or generate DOCX from form data

            return response()->json([
                'success' => true,
                'message' => 'Data borang berhasil disimpan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
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
                'uploaded_by' => auth()->id(),
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
                'changed_by' => auth()->id(),
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
        $user = Auth::user();
        $pengajuan = $dokumen->pengajuan;

        $userStudyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();
        $hasAccess = in_array($pengajuan->id_program_studi, $userStudyProgramIds)
            || $pengajuan->id_de_assigned === $user->id
            || $user->role === 'admin';

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
