<?php
// app/Http/Controllers/Asesmen/BorangValidatorController.php

namespace App\Http\Controllers\Asesmen;

use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use App\Models\DatasetSuplemen;
use Illuminate\Validation\Rule;
use App\Models\BorangValidation;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use App\Services\BorangValidationExcelService;

class BorangValidatorController extends Controller
{
    protected $excelService;

    public function __construct(BorangValidationExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    /**
     * List semua borang yang di-assign
     */
    public function index()
    {
        $user = Auth::user();

        $assignments = AsesmenUserRole::with([
            'asesmen.pengajuan.studyProgram.degreeLevel',
            'asesmen.pengajuan.studyProgram.university',
            'role_selected',
            'borangValidation',
        ])
            ->where('id_user', $user->id)
            ->whereHas('role', function ($q) {
                $q->where('name', 'validator');
            })
            ->where('jenis_asesmen', 'dokumen')
            ->orderByRaw("
                CASE status_penawaran
                    WHEN 'pending' THEN 1
                    WHEN 'accepted' THEN 2
                    WHEN 'rejected' THEN 3
                END,
                CASE status_pekerjaan
                    WHEN 'not_started' THEN 1
                    WHEN 'in_progress' THEN 2
                    WHEN 'revision_required' THEN 3
                    WHEN 'submitted' THEN 4
                    WHEN 'approved' THEN 5
                END
            ")
            ->paginate(10);

        // Calculate stats
        $stats = [
            'pending' => AsesmenUserRole::where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_penawaran', 'pending')
                ->count(),
            'in_review' => AsesmenUserRole::where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_penawaran', 'accepted')
                ->where('status_pekerjaan', 'in_progress')
                ->count(),
            'revision' => AsesmenUserRole::where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_pekerjaan', 'revision_required')
                ->count(),
            'approved' => AsesmenUserRole::where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_pekerjaan', 'approved')
                ->count(),
        ];

        return view('validator.borang.index', compact('assignments', 'stats'));
    }

    /**
     * Show borang for validation
     */
    public function show($idAssignment)
    {
        $authUser = Auth::user();

        $assignment = AsesmenUserRole::with([
            'asesmen.pengajuan.studyProgram.degreeLevel',
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.dokumen' => function ($q) {
                $q->where('is_latest', true)
                    ->whereIn('jenis_dokumen', ['pengesahan', 'draft_borang']);
            },
            'asesmen.pengajuan.latestBorangImport',
            'borangValidation',
            'role_selected',
            'user',
        ]);
        if (!in_array($authUser->role_selected, ['super_admin', 'sekretariat'])) {
            $assignment = $assignment->where('id_user', $authUser->id);
        }
        $assignment = $assignment->whereHas('role', function ($q) {
            $q->where('name', 'validator');
        })->where('jenis_asesmen', 'dokumen')->findOrFail($idAssignment);

        // Check status penawaran
        if ($assignment->status_penawaran !== 'accepted') {
            return redirect()->route('validator.borang.index')
                ->with('error', 'Anda harus menerima penawaran terlebih dahulu.');
        }

        // Auto-update status to in_progress
        if ($assignment->status_pekerjaan === 'not_started') {
            $assignment->update([
                'status_pekerjaan' => 'in_progress',
            ]);
        }

        $pengajuan = $assignment->asesmen->pengajuan;
        if (!$pengajuan) abort(404, 'Permohonan akreditasi tidak ditemukan');

        $degreeCode = $this->mapDegreeCode($pengajuan->studyProgram->degreeLevel);

        /**
         * ✅ LKPS WAJIB kuantitatif saja
         * -> eager load indikator dengan filter id_jenis = 2
         */
        $kriterias = \App\Models\Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator' => function ($q) {
                $q->where('id_jenis', 2)->orderBy('kode_indikator');
            },
        ])->get();

        /**
         * ✅ Suplemen: section_key = header tidak ikut ditampilkan & tidak ikut dihitung
         */
        $suplemenItems = DatasetSuplemen::query()
            ->where('degree_level_code', $degreeCode)
            ->where('content_type', 'list_item')
            ->where('section_key', '!=', 'header')
            ->orderBy('urutan')
            ->get();

        $suplemenGrouped = $suplemenItems->groupBy('section_key');
        $totalElemenSuplemen = $suplemenItems->count();

        // ==========================================================
        // ✅ HITUNG TOTAL (LED, SUPLEMEN, LKPS KUANTITATIF)
        //   (harus dihitung ulang, karena record lama bisa total=0)
        // ==========================================================
        $totalElemenLed = 0;
        $totalIndikatorLkpsKuant = 0;

        foreach ($kriterias as $kriteria) {
            $totalElemenLed += $kriteria->elemenStandar->count();

            foreach ($kriteria->elemenStandar as $elemen) {
                // indikator sudah difilter kuantitatif (id_jenis=2)
                $totalIndikatorLkpsKuant += $elemen->indikator->count();
            }
        }

        // ============================================
        // INITIALIZE OR GET VALIDATION
        // ============================================
        $validation = $assignment->borangValidation;

        if (!$validation) {
            // buat record baru
            $validation = BorangValidation::create([
                'id_assignment' => $assignment->id,
                'id_pengajuan' => $pengajuan->id,
                'total_elemen_led' => $totalElemenLed,
                'total_elemen_suplemen' => $totalElemenSuplemen,
                'total_indikator_lkps' => $totalIndikatorLkpsKuant,
                'reviewed_led' => 0,
                'reviewed_suplemen' => 0,
                'reviewed_lkps' => 0,
            ]);
        } else {
            /**
             * ✅ SYNC UNTUK RECORD LAMA:
             * - total LED bisa 0 (seperti kasus screenshot)
             * - total suplemen dulu mungkin dihitung dari elemen LED
             * - total LKPS harus kuantitatif saja
             *
             * Reviewed juga dijaga agar tidak melebihi total baru.
             */
            $needUpdate =
                (int)$validation->total_elemen_led !== (int)$totalElemenLed ||
                (int)$validation->total_elemen_suplemen !== (int)$totalElemenSuplemen ||
                (int)$validation->total_indikator_lkps !== (int)$totalIndikatorLkpsKuant;

            if ($needUpdate) {
                $validation->update([
                    'total_elemen_led' => $totalElemenLed,
                    'total_elemen_suplemen' => $totalElemenSuplemen,
                    'total_indikator_lkps' => $totalIndikatorLkpsKuant,

                    'reviewed_led' => min((int)$validation->reviewed_led, (int)$totalElemenLed),
                    'reviewed_suplemen' => min((int)$validation->reviewed_suplemen, (int)$totalElemenSuplemen),
                    'reviewed_lkps' => min((int)$validation->reviewed_lkps, (int)$totalIndikatorLkpsKuant),
                ]);
            }
        }

        $validation->refresh();
        $assignment->refresh();

        // ============================================
        // GET UPLOADED FILES
        // ============================================
        $uploadedFiles = $pengajuan->getUploadedDocuments();

        // ============================================
        // GET BORANG DATA (for LED content)
        // ============================================
        $borangDataCollection = \App\Models\BorangData::where('id_pengajuan', $pengajuan->id)
            ->get()
            ->keyBy('dataset_id');

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemen) {
                $elemen->setRelation('borangData', collect());

                $descKey = 'desc_' . $elemen->id;
                if (isset($borangDataCollection[$descKey])) {
                    $elemen->borangData->push($borangDataCollection[$descKey]);
                }
            }
        }

        // Get progress
        $progress = $validation->getProgressPercentage();
        $isEnvLocal = app()->environment() === 'local';
        $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            \App\Models\PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            \App\Models\PengajuanAkreditasi::STATUS_PENGAJUAN_COMPLETED,
        ];

        $log = $pengajuan->latestRelevantStatusLog($allowed);
        $lockBorang = in_array($log?->status_to, $allowed);

        // =====================
        // STATUS BANNER (VIEW)
        // =====================
        $statusPengajuan = $pengajuan->status;
        $statusPekerjaan = $assignment->status_pekerjaan;
        $finalAction = $validation?->final_action; // approve|revision|null

        $statusClass = 'info';
        $statusText  = '';

        if ($lockBorang) {
            $statusClass = 'success';
            $statusText  = 'Validasi telah selesai. Dokumen bersifat read-only dan tidak dapat diubah.';
        } else {
            if ($assignment->status_pekerjaan === 'not_started') {
                $statusClass = 'info';
                $statusText  = 'Silakan mulai validasi dokumen. Buka setiap elemen dan berikan penilaian.';
            } elseif ($assignment->status_pekerjaan === 'in_progress') {
                if ($validation->isCompletelyReviewed()) {
                    $statusClass = 'info';
                    $statusText  = 'Semua item sudah divalidasi. Silahkan lanjutkan memilih Setujui Dokumen atau Minta Revisi, lalu lakukan submit final, pada bagian "Finalisasi dan Kirim".';
                } else {
                    $statusClass = 'info';
                    $statusText  = 'Sedang dalam proses validasi. Mohon Lengkapi penilaian seluruh item sebelum melakukan submit final.';
                }
            } elseif ($validation->final_action === 'approve') {
                $statusClass = 'success';
                $statusText  = 'Dokumen telah disetujui. Menunggu sistem memperbarui status pengajuan.';
            } elseif ($validation->final_action === 'revision') {
                $statusClass = 'warning';
                $statusText  = 'Revisi telah diminta kepada prodi. Menunggu prodi melakukan perbaikan dokumen.';
            }
        }

        return view('validator.borang.show', compact(
            'assignment',
            'pengajuan',
            'validation',
            'kriterias',
            'uploadedFiles',
            'progress',
            'suplemenItems',
            'suplemenGrouped',
            'degreeCode',
            'totalElemenSuplemen',
            'isEnvLocal',
            'lockBorang',
            'statusText',
            'statusClass'
        ));
    }

    /**
     * Update review (autosave per item) - AJAX
     */
    public function updateReview(Request $request, $idAssignment)
    {
        $request->validate([
            'category' => 'required|in:led,suplemen,lkps',
            'item_id' => 'required|integer',
            'grade' => 'required|in:A,B,C',
            'catatan' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with('borangValidation')
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            $validation = $assignment->borangValidation;

            if (!$validation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation record tidak ditemukan',
                ], 404);
            }

            $category = $request->category;
            $itemId = $request->item_id;

            if ($category === 'lkps') {
                $isValid = \App\Models\Indikator::query()
                    ->where('id', $itemId)
                    ->where('id_jenis', 2) // ✅ hanya kuantitatif
                    ->exists();

                if (!$isValid) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Indikator LKPS tidak valid (bukan indikator kuantitatif).',
                    ], 422);
                }
            }

            DB::beginTransaction();

            // Get current review data
            $reviewField = "review_{$category}";
            $reviewedField = "reviewed_{$category}";
            $currentReview = $validation->$reviewField ?? [];

            // Check if this is a new review
            $isNewReview = !isset($currentReview[$itemId]);

            // Update review
            $currentReview[$itemId] = [
                'grade' => $request->grade,
                'catatan' => $request->catatan,
                'reviewed_at' => now()->toDateTimeString(),
                'reviewed_by' => $user->name,
            ];

            // Update validation
            $updateData = [
                $reviewField => $currentReview,
            ];

            // Increment reviewed count if new
            if ($isNewReview) {
                $updateData[$reviewedField] = $validation->$reviewedField + 1;
            }

            $validation->update($updateData);

            // Get fresh progress
            $progress = $validation->fresh()->getProgressPercentage();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Validasi berhasil disimpan',
                'progress' => $progress,
                'is_complete' => $validation->fresh()->isCompletelyReviewed(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update review', [
                'assignment_id' => $idAssignment,
                'category' => $request->category,
                'item_id' => $request->item_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit final validation
     */
    public function submit(Request $request, $idAssignment)
    {
        $request->validate([
            'action' => 'required|in:approve,revision',
            'catatan_validator' => 'nullable|string|max:5000',
            'catatan_led' => 'nullable|string|max:2000',
            'catatan_suplemen' => 'nullable|string|max:2000',
            'catatan_lkps' => 'nullable|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $assignment = AsesmenUserRole::with([
                'asesmen.pengajuan.studyProgram',
                'asesmen.pengajuan.pengaju',
                'borangValidation',
            ])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            $pengajuan = $assignment->asesmen->pengajuan;

            if (!$pengajuan) {
                throw new \Exception('Permohonan akreditasi tidak ditemukan');
            }

            $validation = $assignment->borangValidation;

            if (!$validation) {
                throw new \Exception('Validation record tidak ditemukan');
            }

            // Check if review complete
            if (!$validation->isCompletelyReviewed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mohon selesaikan review untuk semua item sebelum submit.',
                ], 422);
            }

            // Update catatan umum
            $validation->update([
                'catatan_validator' => $request->catatan_validator,
                'catatan_led' => $request->catatan_led,
                'catatan_suplemen' => $request->catatan_suplemen,
                'catatan_lkps' => $request->catatan_lkps,
                'final_action' => $request->action,
            ]);

            if ($request->action === 'approve') {
                // ============================================
                // APPROVE - All items must be grade C
                // ============================================
                if (!$validation->isValidationPassed()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Masih ada item dengan kategori review "Kurang tepat, perlu melengkapi" atau "Perlu diperbaiki". Ubah kategori review setiap elemen menjadi "Sudah tepat" & Simpan data. Atau pada saat submit final, pilih "Minta Revisi" jika masih ada revisi',
                    ], 422);
                }

                $assignment->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $user->id,
                ]);
                $statusFrom = $pengajuan->status;
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    'tanggal_validasi_borang_selesai' => now(),
                ]);

                // Log status
                $pengajuan->statusLog()->create([
                    'status_from' => $statusFrom,
                    'status_to' => PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    'changed_by' => $user->id,
                    'keterangan' => 'LED divalidasi dan disetujui oleh validator ' . $user->name,
                    'changed_at' => now(),
                ]);

                // Send email notification
                try {
                    Mail::to($pengajuan->pengaju->email)
                        ->queue(new \App\Mail\BorangValidationApproved($pengajuan, $validation));
                } catch (\Exception $e) {
                    Log::error('Failed to send approval email', [
                        'pengajuan_id' => $pengajuan->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $message = 'Dokumen akreditasi berhasil divalidasi dan disetujui. Mohon segera lakukan pelaporan validasi dokumen pada menu selanjutnya';
            } else {
                // ============================================
                // REVISION REQUIRED - Build revision points
                // ============================================
                $needsRevision = $validation->getNeedsRevisionItems();

                if (
                    empty($needsRevision['led']) &&
                    empty($needsRevision['suplemen']) &&
                    empty($needsRevision['lkps'])
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada item yang terdeteksi perlu direvisi. Silakan "Pilih Setujui Dokumen" atau ubah Kategori Validasi pada item yang perlu diperbaiki.',
                    ], 422);
                }

                // 1) Kumpulkan semua ID
                $ledElemenIds = collect($needsRevision['led'] ?? [])
                    ->pluck('elemen_id')
                    ->filter()
                    ->unique()
                    ->values();

                $suplemenIds = collect($needsRevision['suplemen'] ?? [])
                    ->pluck('elemen_id') // elemen_id = dataset_suplemen id
                    ->filter()
                    ->unique()
                    ->values();

                $lkpsIndikatorIds = collect($needsRevision['lkps'] ?? [])
                    ->pluck('indikator_id')
                    ->filter()
                    ->unique()
                    ->values();

                // 2) Query sekali (bulk) + keyBy untuk lookup cepat
                $elemenMap = \App\Models\ElemenStandar::query()
                    ->whereIn('id', $ledElemenIds)
                    ->get(['id', 'kode_elemen', 'pernyataan_elemen'])
                    ->keyBy('id');

                $suplemenMap = DatasetSuplemen::query()
                    ->whereIn('id', $suplemenIds)
                    ->get(['id', 'section_key', 'text_content'])
                    ->keyBy('id');

                // LKPS: eager load elemenStandar supaya tidak memicu query tambahan saat akses relasi
                $indikatorMap = \App\Models\Indikator::query()
                    ->with(['elemenStandar:id,kode_elemen,pernyataan_elemen'])
                    ->whereIn('id', $lkpsIndikatorIds)
                    ->get(['id', 'id_elemen']) // sesuaikan kolom FK kamu
                    ->keyBy('id');

                // 3) Build revision points tanpa query di dalam loop
                $revisionPoints = [];

                // LED
                if (!empty($needsRevision['led'])) {
                    $revisionPoints[] = '=== REVISI LED ===';
                    foreach ($needsRevision['led'] as $item) {
                        $elemen = $elemenMap->get($item['elemen_id']);
                        $gradeLabel = BorangValidation::getGradeLabel($item['grade']);

                        $elemenLabel = $elemen
                            ? "{$elemen->kode_elemen} - {$elemen->pernyataan_elemen}"
                            : "Elemen ID: {$item['elemen_id']}";

                        $point = "LED - {$elemenLabel}: {$gradeLabel}";
                        if (!empty($item['catatan'])) $point .= " - {$item['catatan']}";
                        $revisionPoints[] = $point;
                    }
                }

                // SUPLEMEN
                if (!empty($needsRevision['suplemen'])) {
                    $revisionPoints[] = '=== REVISI SUPLEMEN ===';
                    foreach ($needsRevision['suplemen'] as $item) {
                        $gradeLabel = BorangValidation::getGradeLabel($item['grade']);
                        $ds = $suplemenMap->get($item['elemen_id']);

                        $label = $ds
                            ? ("[" . $ds->section_key . "] " . $ds->text_content)
                            : ("Item Suplemen ID: " . $item['elemen_id']);

                        $point = "Suplemen - {$label}: {$gradeLabel}";
                        if (!empty($item['catatan'])) $point .= " - {$item['catatan']}";
                        $revisionPoints[] = $point;
                    }
                }

                // LKPS
                if (!empty($needsRevision['lkps'])) {
                    $revisionPoints[] = '=== REVISI LKPS ===';
                    foreach ($needsRevision['lkps'] as $item) {
                        $indikator = $indikatorMap->get($item['indikator_id']);
                        $gradeLabel = BorangValidation::getGradeLabel($item['grade']);

                        $es = $indikator?->elemenStandar; // sudah eager-loaded
                        $indikatorLabel = $es
                            ? "{$es->kode_elemen} - {$es->pernyataan_elemen}"
                            : "Indikator ID: {$item['indikator_id']}";

                        $point = "LKPS - {$indikatorLabel}: {$gradeLabel}";
                        if (!empty($item['catatan'])) $point .= " - {$item['catatan']}";
                        $revisionPoints[] = $point;
                    }
                }

                $validation->update(['revision_points' => $revisionPoints]);

                $assignment->update([
                    'status_pekerjaan' => 'revision_required',
                ]);
                $statusFrom = $pengajuan->status;
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                ]);

                // Log status
                $pengajuan->statusLog()->create([
                    'status_from' => $statusFrom,
                    'status_to' => PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                    'changed_by' => $user->id,
                    'keterangan' => 'Validator ' . $user->name . ' meminta revisi LED - ' . count($revisionPoints) . ' poin revisi',
                    'changed_at' => now(),
                ]);

                // Send email notification - will be handled by DE

                $message = 'Permintaan revisi berhasil dikirim.';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => route('validator.borang.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit borang validation failed', [
                'assignment_id' => $idAssignment,
                'action' => $request->action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal submit validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get validation stats (AJAX)
     */
    public function getValidationStats($idAssignment)
    {
        try {
            $assignment = AsesmenUserRole::with([
                'asesmen.pengajuan.studyProgram.degreeLevel',
                'borangValidation',
            ])
                ->where('id_user', Auth::id())
                ->findOrFail($idAssignment);

            $validation = $assignment->borangValidation;

            if (!$validation) {
                return response()->json([
                    'error' => 'Validation record tidak ditemukan'
                ], 404);
            }

            $progress = $validation->getProgressPercentage();
            $needsRevision = $validation->getNeedsRevisionItems();

            return response()->json([
                'total' => $progress['total'],
                'reviewed' => $progress['reviewed'],
                'percentage' => $progress['percentage'],
                'led' => [
                    'total' => $validation->total_elemen_led,
                    'reviewed' => $validation->reviewed_led,
                    'percentage' => $progress['led_percentage'],
                    'needs_revision' => count($needsRevision['led']),
                ],
                'suplemen' => [
                    'total' => $validation->total_elemen_suplemen,
                    'reviewed' => $validation->reviewed_suplemen,
                    'percentage' => $progress['suplemen_percentage'],
                    'needs_revision' => count($needsRevision['suplemen']),
                ],
                'lkps' => [
                    'total' => $validation->total_indikator_lkps,
                    'reviewed' => $validation->reviewed_lkps,
                    'percentage' => $progress['lkps_percentage'],
                    'needs_revision' => count($needsRevision['lkps']),
                ],
                'is_complete' => $validation->isCompletelyReviewed(),
                'is_passed' => $validation->isValidationPassed(),
            ]);
        } catch (\Exception $e) {
            Log::error('Get validation stats failed', [
                'assignment_id' => $idAssignment,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getValidationSummary($idAssignment)
    {
        try {
            $assignment = AsesmenUserRole::with([
                'asesmen.pengajuan.studyProgram.degreeLevel',
                'borangValidation',
            ])
                ->where('id_user', Auth::id())
                ->findOrFail($idAssignment);

            $validation = $assignment->borangValidation;
            if (!$validation) {
                return response()->json(['error' => 'Validation record tidak ditemukan'], 404);
            }

            // ✅ Ambil kriterias sama persis seperti Blade Anda
            $kriterias = \App\Models\Kriteria::with([
                'elemenStandar',
                'elemenStandar.indikator' => function ($q) {
                    $q->where('id_jenis', 2)->orderBy('kode_indikator');
                },
            ])->get();

            $reviewLed = $validation->review_led ?? [];
            $reviewSup = $validation->review_suplemen ?? [];
            $reviewLkps = $validation->review_lkps ?? [];

            $extract = function ($r) {
                if (!$r) return [null, ''];
                if (is_array($r)) return [$r['grade'] ?? null, $r['catatan'] ?? ''];
                return [$r, ''];
            };

            $rows = [];

            // ================= LED (ElemenStandar) =================
            foreach ($kriterias as $kriteria) {
                foreach (($kriteria->elemenStandar ?? collect()) as $elemen) {
                    $r = $reviewLed[$elemen->id] ?? null;
                    [$grade, $catatan] = $extract($r);

                    $rows[] = [
                        'category' => 'led',
                        'group' => $kriteria->kode_kriteria ?? null,
                        'kode' => $elemen->kode_elemen ?? null,
                        'judul' => $elemen->pernyataan_elemen ?? null,
                        'item_id' => $elemen->id,
                        'reviewed' => (bool) $r,
                        'grade' => in_array($grade, ['A', 'B', 'C'], true) ? $grade : null,
                        'catatan' => $catatan,
                        'anchor' => "h-led-{$elemen->id}", // ✅ sesuai Blade
                    ];
                }
            }

            $degreeLevelCode = $assignment->asesmen->pengajuan->studyProgram->degreeLevel->code ?? null;

            if ($degreeLevelCode) {
                $suplemenItems = DatasetSuplemen::query()
                    ->where('degree_level_code', $degreeLevelCode)
                    ->where('content_type', '!=', 'header')   // pakai kolom yang memang ada
                    ->orderBy('section_key')                  // ganti dari section -> section_key
                    ->orderBy('urutan')
                    ->get();

                foreach ($suplemenItems as $it) {
                    $r = $reviewSup[$it->id] ?? null;
                    [$grade, $catatan] = $extract($r);

                    $rows[] = [
                        'category' => 'suplemen',
                        'group' => $it->section_key,           // ganti dari section -> section_key
                        'kode' => "#{$it->urutan}",
                        'judul' => $it->text_content,
                        'item_id' => $it->id,
                        'reviewed' => (bool) $r,
                        'grade' => in_array($grade, ['A', 'B', 'C'], true) ? $grade : null,
                        'catatan' => $catatan,
                        'anchor' => "h-suplemen-ds-{$it->id}",
                    ];
                }
            }

            // ================= LKPS (Indikator id_jenis=2) =================
            foreach ($kriterias as $kriteria) {
                foreach (($kriteria->elemenStandar ?? collect()) as $elemen) {
                    $indikators = $elemen->indikator ?? collect(); // sudah terfilter id_jenis=2
                    if ($indikators->count() === 0) continue;

                    foreach ($indikators as $ind) {
                        $r = $reviewLkps[$ind->id] ?? null;
                        [$grade, $catatan] = $extract($r);

                        $rows[] = [
                            'category' => 'lkps',
                            'group' => $kriteria->kode_kriteria ?? null,
                            'kode' => $ind->kode_indikator ?? null,
                            'judul' => $ind->deskripsi_indikator ?? null,
                            'item_id' => $ind->id,
                            'reviewed' => (bool) $r,
                            'grade' => in_array($grade, ['A', 'B', 'C'], true) ? $grade : null,
                            'catatan' => $catatan,
                            // LKPS Anda anchor-nya ke header elemen (bukan indikator),
                            // karena di Blade: id="h-lkps-{{ $elemen->id }}"
                            'anchor' => "h-lkps-{$elemen->id}",
                            'elemen_id' => $elemen->id,
                        ];
                    }
                }
            }

            // ================= Rekap =================
            $rekap = [
                'total' => count($rows),
                'reviewed' => 0,
                'unreviewed' => 0,
                'A' => 0,
                'B' => 0,
                'C' => 0,
            ];

            foreach ($rows as $x) {
                if (!$x['reviewed']) {
                    $rekap['unreviewed']++;
                    continue;
                }
                $rekap['reviewed']++;
                if ($x['grade']) $rekap[$x['grade']]++;
            }

            return response()->json([
                'rekap' => $rekap,
                'rows' => $rows,
            ]);
        } catch (\Exception $e) {
            Log::error('Get validation summary failed', [
                'assignment_id' => $idAssignment,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Gagal mengambil summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Accept/Reject assignment offer
     */
    public function respondOffer(Request $request, $idAssignment)
    {
        $request->validate([
            'action' => 'required|in:accept,reject',
            'response_note' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $assignment = AsesmenUserRole::with('asesmen.pengajuan')
                ->where('id_user', $user->id)
                ->where('status_penawaran', 'pending')
                ->findOrFail($idAssignment);

            if ($request->action === 'accept') {
                $assignment->update([
                    'status_penawaran' => 'accepted',
                    'status_pekerjaan' => 'not_started',
                    'responded_at' => now(),
                    'response_note' => $request->response_note,
                ]);

                $assignment->asesmen->pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                ]);

                $message = 'Penawaran diterima. Silakan mulai review LED.';
            } else {
                $assignment->update([
                    'status_penawaran' => 'rejected',
                    'responded_at' => now(),
                    'response_note' => $request->response_note,
                ]);

                $message = 'Penawaran ditolak.';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Respond offer failed', [
                'assignment_id' => $idAssignment,
                'action' => $request->action,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal merespon penawaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download templat Excel kosong
     */
    public function downloadTemplate($idAssignment)
    {
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with([
                'asesmen.pengajuan.studyProgram.degreeLevel',
                'borangValidation',
            ])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            $result = $this->excelService->generateExcel($assignment, false);

            if (!$result['success']) {
                return back()->with('error', $result['message']);
            }

            return response()->download($result['file'], $result['filename'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Download templat failed', [
                'assignment_id' => $idAssignment,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal download templat: ' . $e->getMessage());
        }
    }

    /**
     * Download hasil review yang sudah ada
     */
    public function downloadReview($idAssignment)
    {
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with([
                'asesmen.pengajuan.studyProgram.degreeLevel',
                'borangValidation',
            ])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            $result = $this->excelService->generateExcel($assignment, true);

            if (!$result['success']) {
                return back()->with('error', $result['message']);
            }

            return response()->download($result['file'], $result['filename'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Download review failed', [
                'assignment_id' => $idAssignment,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal download review: ' . $e->getMessage());
        }
    }

    /**
     * Upload/Import Excel review
     */
    public function uploadReview(Request $request, $idAssignment)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $assignment = AsesmenUserRole::with('borangValidation')
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            $file = $request->file('file');
            $result = $this->excelService->importExcel($assignment, $file->getPathname());

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

            DB::commit();

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload review failed', [
                'assignment_id' => $idAssignment,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal upload review: ' . $e->getMessage());
        }
    }

    public function resetReview(Request $request, $idAssignment)
    {
        $request->validate([
            'category' => ['required', Rule::in(['led', 'suplemen', 'lkps', 'all'])],
            'reset_notes' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with('borangValidation')
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->where('status_penawaran', 'accepted') // ✅ biar konsisten dgn show()
                ->findOrFail($idAssignment);

            $validation = $assignment->borangValidation;
            if (!$validation) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation record tidak ditemukan',
                ], 404);
            }

            $category = $request->input('category');
            $resetNotes = $request->boolean('reset_notes');

            // ======================
            // ✅ RESET ALL
            // ======================
            if ($category === 'all') {
                $update = [
                    'review_led' => [],
                    'review_suplemen' => [],
                    'review_lkps' => [],
                    'reviewed_led' => 0,
                    'reviewed_suplemen' => 0,
                    'reviewed_lkps' => 0,
                ];

                if ($resetNotes) {
                    $update['catatan_led'] = null;
                    $update['catatan_suplemen'] = null;
                    $update['catatan_lkps'] = null;

                    // opsional kalau kamu mau ikut reset catatan validator
                    // $update['catatan_validator'] = null;
                }

                // opsional: kalau ada revision_points biar gak nyangkut
                if (Schema::hasColumn('borang_validations', 'revision_points')) {
                    $update['revision_points'] = [];
                }

                $validation->update($update);
            }
            // ======================
            // ✅ RESET PER-KATEGORI
            // ======================
            else {
                $reviewField = "review_{$category}";
                $reviewedField = "reviewed_{$category}";

                $noteField = match ($category) {
                    'led' => 'catatan_led',
                    'suplemen' => 'catatan_suplemen',
                    'lkps' => 'catatan_lkps',
                };

                $update = [
                    $reviewField => [],
                    $reviewedField => 0,
                ];

                if ($resetNotes) {
                    $update[$noteField] = null; // atau ''
                }

                $validation->update($update);
            }

            $fresh = $validation->fresh();
            $progress = $fresh->getProgressPercentage();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $category === 'all'
                    ? 'Review semua kategori berhasil di-reset.'
                    : "Review {$category} berhasil di-reset.",
                'progress' => $progress,
                'is_complete' => $fresh->isCompletelyReviewed(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Reset review failed', [
                'assignment_id' => $idAssignment,
                'category' => $request->category ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal reset review: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function mapDegreeCode($degreeLevel): string
    {
        // Sesuaikan field yang ada di tabel degree_levels Anda (code/name)
        $raw = strtolower((string) ($degreeLevel->code ?? $degreeLevel->name ?? ''));

        // Normalisasi beberapa kemungkinan penamaan
        $raw = str_replace([' ', '_'], '-', $raw);

        // Contoh mapping jika di DB Anda ternyata "d4" disebut "s1-terapan"
        // Anda bisa tambah mapping lain sesuai data nyata Anda.
        return match ($raw) {
            'sarjana-terapan', 's1-terapan' => 'd4',
            default => $raw,
        };
    }
}
