<?php

namespace App\Http\Controllers\Asesmen;

use App\Http\Controllers\Controller;
use App\Jobs\ImportPenilaianExcelJob;
use App\Models\Asesmen;
use App\Models\AsesmenUserRole;
use App\Models\ElemenStandar;
use App\Models\Indikator;
use App\Models\JenjangPenilaian;
use App\Models\Kriteria;
use App\Models\PengajuanAkreditasi;
use App\Models\PenilaianElemenAk;
use App\Models\PenilaianImportLog;
use App\Models\Role;
use App\Services\PenilaianExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AKController extends Controller
{
    public function berkas()
    {
        $user = Auth::user();

        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak')
                ->where('id_role', Role::ID_ROLE_ASESOR);
        })
            ->with([
                'userRoles' => function ($query) use ($user) {
                    $query->where('id_user', $user->id)
                        ->where('jenis_asesmen', 'ak')
                        ->where('id_role', Role::ID_ROLE_ASESOR)
                        ->with('role');
                },
                // Semua asesor AK (bukan hanya current user) — untuk kalkulasi split
                'allAsesorsAk' => function ($query) {
                    $query->where('jenis_asesmen', 'ak')
                        ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                        ->with('user')
                        ->orderBy('urutan_asesor');
                },
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'pengajuan.dokumen' => function ($q) {
                    $q->whereIn('jenis_dokumen', [
                        'surat_tugas_asesor_ak',
                        'data_kualitatif',
                        'draft_borang',
                        'borang_final',
                        'data_suplemen',
                        'data_kuantitatif',
                        'kuantitatif',
                    ])
                        ->where('is_latest', true)
                        ->orderBy('created_at', 'desc');
                },
            ])
            ->latest()
            ->paginate(10);

        $asesmenIds = $asesmens->pluck('id')->toArray();

        // ── Progress per asesmen (milik current user) ────────────
        $progressAll = $this->calculateProgressBulk($asesmenIds, $user->id);

        // ── Penilaian semua asesor untuk asesmen yang tampil ─────
        // Ambil sekaligus (bulk) agar tidak N+1
        $allPenilaian = PenilaianElemenAk::whereIn('id_asesmen', $asesmenIds)
            ->whereNotNull('skor')
            ->select('id_asesmen', 'id_asesor', 'id_elemen', 'skor')
            ->get()
            ->groupBy('id_asesmen'); // ['id_asesmen' => Collection]

        $totalElemen = ElemenStandar::count();

        foreach ($asesmens as $asesmen) {
            // Progress user sendiri
            $asesmen->progress = $progressAll[$asesmen->id] ?? [
                'total' => 0,
                'completed' => 0,
                'remaining' => 0,
                'percentage' => 0,
            ];

            // Status info (button)
            $assignment = $asesmen->userRoles->first();
            $asesmen->statusInfo = AsesmenUserRole::getStatusInfo($assignment);

            // ── Kalkulasi split status ────────────────────────────
            $asesmen->splitStatus = $this->calculateSplitStatus(
                $asesmen,
                $allPenilaian->get($asesmen->id, collect()),
                $user->id,
                $totalElemen
            );
        }

        $statusPekerjaan = AsesmenUserRole::STATUS_PEKERJAAN;

        return view('asesmen.ak.berkas.index', compact('asesmens', 'statusPekerjaan'));
    }

    /**
     * Show detail asesmen with accordion per elemen
     */
    public function showBerkas($idAsesmen)
    {
        $user = Auth::user();

        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'ak')
            ->firstOrFail();

        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . ($user->role_selected) . ' tidak diizinkan membuka halaman ini. Silahkan pindah ke role lain');
        }

        $this->updateStatusAK($assignment);
        $asesmen = $assignment->asesmen;

        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
            'elemenStandar.penilaianElemenAk' => function ($query) use ($asesmen, $user) {
                $query->where('id_asesmen', $asesmen->id)
                    ->where('id_asesor', $user->id)
                    ->with('validator');
            }
        ])->get();

        $needsRevisions = PenilaianElemenAk::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->where('status_validasi', 'revision_required')
            ->with('elemen.kriteria')
            ->get();
        $countNeedsRevisions = $needsRevisions->count();
        $jenjangs            = JenjangPenilaian::all();
        $pluckColorSkor      = $jenjangs->pluck('color', 'skor');
        $progress            = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];
        $uploadedFiles       = $asesmen->pengajuan ? $asesmen->pengajuan->getUploadedDocuments() : null;

        $statusPekerjaan  = $assignment->status_pekerjaan ?? 'not_started';
        $isSubmittedOnly  = $statusPekerjaan === 'submitted';
        $isSubmitted      = in_array($statusPekerjaan, ['submitted', 'approved', 'validated']);
        $isApproved       = $statusPekerjaan === 'approved';
        $needsRevision    = $statusPekerjaan === 'revision_required';
        $hasRevisionRequests = $countNeedsRevisions > 0;
        $isComplete       = $progress['percentage'] == 100;

        // ── TAMBAHAN: apakah boleh batalkan submit? ───────────────
        // Hanya boleh jika pengajuan masih di status ak_in_progress
        // (belum masuk ak_on_validation).
        $canUnsubmit = false;
        if ($isSubmittedOnly) {
            $pengajuan = $asesmen->pengajuan;
            if ($pengajuan) {
                $latestLog = $pengajuan->latestRelevantStatusLog([
                    PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                ]);

                // Bisa unsubmit hanya jika log terakhir yang relevan = ak_in_progress
                $canUnsubmit = $latestLog &&
                    $latestLog->status_to === PengajuanAkreditasi::STATUS_AK_IN_PROGRESS;
            } else {
                // Jika tidak ada pengajuan (kasus edge), izinkan saja
                $canUnsubmit = true;
            }
        }

        return view('asesmen.ak.berkas.show', compact(
            'asesmen',
            'kriterias',
            'progress',
            'jenjangs',
            'pluckColorSkor',
            'needsRevisions',
            'countNeedsRevisions',
            'assignment',
            'uploadedFiles',
            'statusPekerjaan',
            'isSubmittedOnly',
            'isSubmitted',
            'isApproved',
            'needsRevision',
            'hasRevisionRequests',
            'isComplete',
            'canUnsubmit'
        ));
    }

    private function updateStatusAK($assignment)
    {
        // ✅ AUTO-UPDATE STATUS: not_started → in_progress
        if ($assignment->status_pekerjaan === 'not_started') {
            $assignment->update([
                'status_pekerjaan' => 'in_progress',
                'started_at' => now(), // Opsional: track kapan mulai
            ]);
            $pengajuan = $assignment->asesmen->pengajuan;
            if ($pengajuan) {
                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('ak', 'status_asesor_in_progress');
                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    ],
                    [
                        'changed_by'  => Auth::id(),
                        'keterangan'  => 'Asesor AK telah memulai proses penilaian kecukupan',
                        'changed_at'  => now(),
                    ]
                );
            }
        }
    }

    /**
     * Save penilaian for specific indikator (AJAX)
     * UPDATED: Support skor 0-4
     */
    public function simpanNilai(Request $request, $idAsesmen)
    {
        $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'skor' => 'required|integer|min:0|max:4', // UPDATED: Support 0-4
            'komentar' => 'nullable|string|max:5000',
        ]);

        try {
            $user = Auth::user();

            // Verify user has access
            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini'
                ], 403);
            }

            // ✅ Check if already validated/approved
            $existingPenilaian = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->where('id_elemen', $request->id_elemen)
                ->first();

            // ✅ PREVENT UPDATE if validated/approved (except revision_required)
            if ($existingPenilaian) {
                $validatedStatuses = ['validated', 'validated_diff', 'approved'];

                if (in_array($existingPenilaian->status_validasi, $validatedStatuses)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Penilaian telah divalidasi dan disetujui oleh validator. Tidak dapat diubah lagi.',
                        'error_type' => 'already_validated',
                        'validated_at' => $existingPenilaian->validated_at,
                        'validator_name' => $existingPenilaian->validator->name ?? 'Validator',
                    ], 422);
                }
            }

            // ✅ Check if submitted or approved assignment (except revision_required)
            if (in_array($assignment->status_pekerjaan, ['submitted', 'approved'])) {
                $needsRevision = $existingPenilaian &&
                    $existingPenilaian->status_validasi === 'revision_required';

                if (!$needsRevision) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Penilaian telah di-submit dan tidak dapat diubah'
                    ], 403);
                }
            }

            // Check if this elemen has revision_required status
            $wasRevisionRequired = false;
            $validatorId = null;

            if ($existingPenilaian && $existingPenilaian->status_validasi === 'revision_required') {
                $wasRevisionRequired = true;
                $validatorId = $existingPenilaian->id_validator;
            }

            // Update or create penilaian
            $penilaian = PenilaianElemenAk::updateOrCreate(
                [
                    'id_asesmen' => $idAsesmen,
                    'id_asesor' => $user->id,
                    'id_elemen' => $request->id_elemen,
                ],
                [
                    'skor' => $request->skor,
                    'komentar' => $request->komentar,
                    'status' => 'draft',

                    'status_validasi' => $wasRevisionRequired ? 'pending' : 'not_validated',
                    'catatan_validator' => $wasRevisionRequired ? null : ($existingPenilaian->catatan_validator ?? null),
                    'preferensi_skor' => $wasRevisionRequired ? null : ($existingPenilaian->preferensi_skor ?? null),
                    'validated_at' => $wasRevisionRequired ? null : ($existingPenilaian->validated_at ?? null),
                    'validated_by' => null,
                    'skor_final' => null,
                    'revision_count' => $existingPenilaian
                        ? ($wasRevisionRequired ? $existingPenilaian->revision_count + 1 : $existingPenilaian->revision_count)
                        : 0,
                ]
            );

            // ✅ Count revisions yang masih diperlukan
            $needsRevisionCount = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->where('status_validasi', 'revision_required')
                ->count();

            // ✅✅ GUNAKAN METHOD EXISTING (LEBIH BAIK!)
            $progress = $this->calculateProgressBulk([$idAsesmen], $user->id)[$idAsesmen];

            // Get skor label and class for response
            $skorInfo = JenjangPenilaian::getSkorInfo($request->skor);

            // ✅ Prepare revision info if was revised
            $revisionInfo = null;
            if ($wasRevisionRequired) {
                $revisionInfo = [
                    'was_revised' => true,
                    'validator_id' => $validatorId,
                    'remaining_revisions' => $needsRevisionCount,
                    'new_status' => 'pending',
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan',
                'data' => $penilaian,
                'progress' => $progress,
                'skor_info' => $skorInfo,
                'revision_info' => $revisionInfo,
                'needs_revision_count' => $needsRevisionCount,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hitung progress untuk satu user di banyak asesmen sekaligus
     */
    private function calculateProgressBulk($asesmenIds, $userId)
    {
        $totalElemens = ElemenStandar::count();

        // Ambil semua penilaian user sekaligus
        $penilaian = PenilaianElemenAk::where('id_asesor', $userId)
            ->whereIn('id_asesmen', $asesmenIds)
            ->whereNotNull('skor')
            ->select('id_asesmen', DB::raw('COUNT(*) as completed'))
            ->groupBy('id_asesmen')
            ->pluck('completed', 'id_asesmen'); // [id_asesmen => completed]

        // Mapping progress per asesmen
        $progress = [];
        foreach ($asesmenIds as $id) {
            $completed = $penilaian[$id] ?? 0;
            $percentage = $totalElemens ? round($completed / $totalElemens * 100, 1) : 0;

            $progress[$id] = [
                'total' => $totalElemens,
                'completed' => $completed,
                'remaining' => $totalElemens - $completed,
                'percentage' => $percentage,
            ];
        }

        return $progress;
    }

    /**
     * Get heatmap data for visualization (NEW)
     */
    public function getHeatmapData($idAsesmen)
    {
        $user = Auth::user();

        // Verify access
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'ak')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get all penilaian with kriteria, elemen info
        $heatmapData = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
            ->where('id_asesor', $user->id)
            ->with([
                'elemen.kriteria'
            ])
            ->get()
            ->map(function ($penilaian) {
                return [
                    'id_elemen' => $penilaian->id_elemen,
                    'kriteria_code' => $penilaian->elemen->kriteria->kode_kriteria,
                    'elemen_code' => $penilaian->elemen->kode_elemen,
                    'skor' => $penilaian->skor,
                    'skor_info' => JenjangPenilaian::getSkorInfo($penilaian->skor),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $heatmapData,
        ]);
    }

    /**
     * ============================================
     * FINALISASI & SUBMIT PENILAIAN
     * ============================================
     */

    /**
     * Submit/Finalisasi penilaian asesor
     */
    public function submitPenilaian(Request $request, $idAsesmen)
    {
        try {
            $user = Auth::user();

            // Check access
            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak')
                ->where('status_penawaran', 'accepted')
                ->first();

            if (!$assignment)
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada penugasan asesmen',
                ], 500);

            // Check if all elemen have been assessed
            $totalElemen = ElemenStandar::count();
            $assessedElemen = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->whereNotNull('skor')
                ->whereNotNull('komentar')
                ->count();

            if ($assessedElemen < $totalElemen) {
                return response()->json([
                    'success' => false,
                    'message' => "Penilaian belum lengkap. Anda baru menilai {$assessedElemen} dari {$totalElemen} elemen.",
                    'assessed' => $assessedElemen,
                    'total' => $totalElemen,
                ], 422);
            }

            DB::beginTransaction();

            // Update all penilaian status to submitted
            PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->update([
                    'status' => 'submitted',
                ]);

            // Update assignment status
            $assignment->update([
                'status_pekerjaan' => 'submitted',
                'submitted_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil di-submit! Mohon menunggu proses validasi.',
                'submitted_at' => now()->locale('id')->translatedFormat('d M Y H:i'),
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal submit penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batalkan submit (kembali ke draft)
     */
    public function unsubmitPenilaian($idAsesmen)
    {
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak')
                ->where('status_pekerjaan', 'submitted')
                ->firstOrFail();

            // ── Cek: apakah sedang divalidasi? ───────────────────
            $pengajuan = $assignment->asesmen->pengajuan;
            if ($pengajuan) {
                $latestLog = $pengajuan->latestRelevantStatusLog([
                    PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                ]);

                if (
                    $latestLog &&
                    $latestLog->status_to === PengajuanAkreditasi::STATUS_AK_ON_VALIDATION
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Penilaian sedang dalam proses validasi dan tidak dapat dibatalkan. '
                            . 'Hubungi validator jika ada kesalahan.',
                        'reason'  => 'ak_on_validation',
                    ], 422);
                }
            }

            // ── Cek: sudah divalidasi di level penilaian? ────────
            $hasValidated = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->whereIn('status_validasi', ['validated', 'validated_diff'])
                ->exists();

            if ($hasValidated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian telah divalidasi dan tidak dapat dibatalkan.',
                ], 422);
            }

            if ($assignment->status_pekerjaan === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian telah disetujui, tidak bisa dibatalkan.',
                ], 422);
            }

            DB::beginTransaction();

            PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->update(['status' => 'draft']);

            $assignment->update([
                'status_pekerjaan' => 'in_progress',
                'submitted_at'     => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Submit dibatalkan. Anda dapat melanjutkan edit penilaian.',
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan submit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ============================================
     * RESET SEMUA PENILAIAN
     * ============================================
     *
     * ADD THIS METHOD TO: App\Http\Controllers\AKController
     * Location: After unsubmitPenilaian() method
     */

    /**
     * Reset/Hapus semua penilaian untuk asesmen tertentu
     *
     * @param int $idAsesmen
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetAllPenilaian($idAsesmen)
    {
        try {
            $user = Auth::user();

            // Verify access
            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini',
                ], 403);
            }

            // Check if submitted or approved - tidak boleh reset
            if (in_array($assignment->status_pekerjaan, ['submitted', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mereset penilaian yang telah di-submit atau disetujui. Silakan batalkan submit terlebih dahulu.',
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Get count before delete
                $totalDeleted = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                    ->where('id_asesor', $user->id)
                    ->count();

                // Delete all penilaian for this user and asesmen
                PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                    ->where('id_asesor', $user->id)
                    ->delete();

                // Update assignment status back to not_started
                $assignment->update([
                    'status_pekerjaan' => 'not_started',
                    'submitted_at' => null,
                ]);

                DB::commit();

                // Calculate new progress (should be 0)
                $progress = $this->calculateProgressBulk([$idAsesmen], $user->id)[$idAsesmen];

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$totalDeleted} penilaian. Semua penilaian telah direset.",
                    'deleted_count' => $totalDeleted,
                    'progress' => $progress,
                ]);
            } catch (\Exception $e) {
                Log::error($e);
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Reset penilaian failed: ' . $e->getMessage(), [
                'id_asesmen' => $idAsesmen,
                'id_asesor' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download templat Excel (format kosong)
     */
    public function downloadTemplate($idAsesmen)
    {
        try {
            $user = Auth::user();

            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->with(['userRoles.user', 'userRoles.role'])->findOrFail($idAsesmen);

            // Ambil semua asesor (filter by role name)
            $asesors = $asesmen->userRoles->filter(function ($userRole) {
                return $userRole->id_role === 3; // atau role_id == 2
            })->values(); // Reset keys

            // Ambil asesor1 dan asesor2
            $asesor1 = $asesors->first(); // Asesor pertama
            $asesor2 = $asesors->skip(1)->first(); // Asesor kedua

            // Cek apakah ada 2 asesor
            if (!$asesor1 || !$asesor2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesmen harus memiliki minimal 2 asesor.'
                ], 400);
            }

            $excelService = new PenilaianExcelService(PenilaianElemenAk::class);
            $filePath = $excelService->generateTemplate($asesmen, $asesor1, $asesor2);

            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal download templat: ' . $e->getMessage());
        }
    }

    /**
     * Export penilaian ke Excel (dengan data)
     */
    public function exportExcel(Request $request, $idAsesmen)
    {
        try {
            $user = Auth::user();

            // Verify access
            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($idAsesmen);

            // Get mode from query parameter (template, full, personal)
            $mode = $request->query('mode', 'full'); // default: full
            $useColor = $request->query('color', 'true') === 'true';

            // Validate mode
            if (!in_array($mode, ['template', 'full', 'personal', 'split'])) {
                return redirect()->back()->with('error', 'Mode download tidak valid');
            }

            // Create service dengan mode
            $excelService = new PenilaianExcelService(PenilaianElemenAk::class, $mode, $useColor);

            // Generate file berdasarkan mode
            if ($mode === 'template') {
                $filePath = $excelService->generateTemplate($asesmen);
            } else {
                $filePath = $excelService->generateWithData($asesmen, $user->id);
            }

            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal download Excel: ' . $e->getMessage());
        }
    }

    /**
     * Import penilaian dari Excel (using Queue)
     */
    public function importExcel(Request $request, $idAsesmen)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $user = Auth::user();

            // Verify access
            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($idAsesmen);

            // Store file temporarily
            $file = $request->file('file');
            $filename = 'import_' . $asesmen->code . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('temp/imports', $filename);

            // Create import log
            $importLog = PenilaianImportLog::create([
                'id_asesmen' => $asesmen->id,
                'id_asesor' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'status' => 'queued',
            ]);

            // Dispatch job
            ImportPenilaianExcelJob::dispatch(PenilaianElemenAk::class, $filePath, $asesmen->id, $user->id, $importLog->id);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload. Proses input data penilaian sedang diproses di background.',
                'import_log_id' => $importLog->id,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload excel penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check import status (AJAX)
     */
    public function checkImportStatus($importLogId)
    {
        try {
            $user = Auth::user();

            $importLog = PenilaianImportLog::where('id_asesor', $user->id)
                ->findOrFail($importLogId);

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $importLog->status,
                    'total_rows' => $importLog->total_rows,
                    'imported_rows' => $importLog->imported_rows,
                    'failed_rows' => $importLog->failed_rows,
                    'errors' => $importLog->errors,
                    'errors_message'        => $importLog->errors_message,
                    'success_rate' => $importLog->success_rate,
                    'started_at' => $importLog->started_at?->locale('id')->translatedFormat('d M Y H:i:s'),
                    'completed_at' => $importLog->completed_at?->locale('id')->translatedFormat('d M Y H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Log upload excel penilaian tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Get import history
     */
    public function importHistory($idAsesmen)
    {
        try {
            $user = Auth::user();

            $logs = PenilaianImportLog::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat upload excel',
            ], 500);
        }
    }

    /**
     * Get comparison data for all asesors
     */
    public function getComparisonData(Asesmen $asesmen)
    {
        try {
            $idAsesmen = $asesmen->id;
            // Get all asesors for this asesmen (AK)
            $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'ak')
                ->whereHas('role', function ($q) {
                    $q->where('name', 'asesor');
                })
                ->with('user')
                ->orderBy('urutan_asesor')
                ->get();

            // Get all kriteria with elemen and penilaian
            $kriterias = Kriteria::with([
                'elemenStandar' => function ($q) {
                    // $q->orderBy('kode_elemen');
                },
                'elemenStandar.indikator' => function ($q) {
                    // $q->orderBy('kode_indikator');
                },
                'elemenStandar.penilaianElemenAk' => function ($q) use ($asesors, $asesmen) {
                    $q->whereIn('id_asesor', $asesors->pluck('id_user'))->where('id_asesmen', $asesmen->id);
                },
                'elemenStandar.penilaianElemenAk.asesor'
            ])->get();

            // Calculate statistics
            $totalElemen = 0;
            $agreedCount = 0;
            $diffCount = 0;
            $pendingCount = 0;

            foreach ($kriterias as $kriteria) {
                foreach ($kriteria->elemenStandar as $elemen) {
                    $totalElemen++;

                    $skors = [];
                    foreach ($asesors as $asesor) {
                        $penilaian = $elemen->penilaianElemenAk
                            ->where('id_asesor', $asesor->id_user)
                            ->first();

                        if ($penilaian && $penilaian->skor !== null) {
                            $skors[] = $penilaian->skor;
                        }
                    }

                    if (empty($skors)) {
                        $pendingCount++;
                    } elseif (count($skors) < 2) {
                        // Hanya 1 asesor yang sudah mengisi → belum bisa dicek split
                        $pendingCount++;
                    } else {
                        $selisih = max($skors) - min($skors);
                        if ($selisih > 1) {
                            $diffCount++;
                        } else {
                            $agreedCount++;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'asesors' => $asesors,
                    'kriterias' => $kriterias,
                    'statistics' => [
                        'total' => $totalElemen,
                        'agreed' => $agreedCount,
                        'diff' => $diffCount,
                        'pending' => $pendingCount
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /ak/berkas/{idAsesmen}/check-split-result
     * Dipanggil setelah import berhasil untuk mengecek apakah
     * ada split dengan asesor lain yang sudah mengisi.
     *
     * Response:
     *   otherHasFilled  : bool  — ada asesor lain yg sudah mengisi
     *   splitCount      : int   — jumlah elemen split
     *   splitItems      : array — [{elemenId, kodeElemen, pernyataan, skors:[{urutan,skor}]}]
     */
    public function checkSplitResult($idAsesmen)
    {
        try {
            $user = Auth::user();

            // Pastikan user punya akses
            $myRole = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak')
                ->firstOrFail();

            // Semua asesor AK untuk asesmen ini
            $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'ak')
                ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                ->with('user')
                ->orderBy('urutan_asesor')
                ->get();

            // Cek apakah ada asesor LAIN yang sudah mengisi minimal 1 penilaian
            $otherAsesors = $asesors->where('id_user', '!=', $user->id);
            $otherHasFilled = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->whereIn('id_asesor', $otherAsesors->pluck('id_user'))
                ->whereNotNull('skor')
                ->exists();

            if (!$otherHasFilled) {
                return response()->json([
                    'success'       => true,
                    'otherHasFilled' => false,
                    'splitCount'    => 0,
                    'splitItems'    => [],
                ]);
            }

            // Hitung split per elemen
            $kriterias = Kriteria::with([
                'elemenStandar',
                'elemenStandar.penilaianElemenAk' => fn($q) =>
                $q->where('id_asesmen', $idAsesmen)
                    ->whereIn('id_asesor', $asesors->pluck('id_user'))
                    ->whereNotNull('skor'),
            ])->get();

            $splitItems = [];

            foreach ($kriterias as $kriteria) {
                foreach ($kriteria->elemenStandar as $elemen) {
                    $penilaians = $elemen->penilaianElemenAk;

                    // Hanya hitung jika minimal ada 2 asesor yang sudah mengisi
                    if ($penilaians->count() < 2) continue;

                    $skors    = $penilaians->pluck('skor')->map(fn($s) => (int) $s)->toArray();
                    $selisih  = max($skors) - min($skors);

                    if ($selisih > 1) {
                        $skorDetail = $asesors->map(function ($asesor) use ($penilaians) {
                            $p = $penilaians->firstWhere('id_asesor', $asesor->id_user);
                            return [
                                'urutan'   => $asesor->urutan_asesor,
                                'nama'     => $asesor->user->name,
                                'skor'     => $p ? (int) $p->skor : null,
                                'is_me'    => $asesor->id_user === $p?->id_asesor,
                            ];
                        })->filter(fn($d) => $d['skor'] !== null)->values();

                        $splitItems[] = [
                            'elemenId'    => $elemen->id,
                            'kodeElemen'  => $elemen->kode_elemen,
                            'pernyataan'  => Str::limit($elemen->pernyataan_elemen, 80),
                            'kodeKriteria' => $kriteria->kode_kriteria,
                            'selisih'     => $selisih,
                            'skors'       => $skorDetail,
                        ];
                    }
                }
            }

            return response()->json([
                'success'        => true,
                'otherHasFilled' => true,
                'splitCount'     => count($splitItems),
                'splitItems'     => $splitItems,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Halaman Upload Excel Penilaian Manual
     */
    public function uploadExcelPage($idAsesmen)
    {
        $user = Auth::user();

        // Check if user has access to this asesmen
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'ak')
            ->firstOrFail();

        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . ($user->role_selected) . ' tidak diizinkan membuka halaman ini.');
        }

        $this->updateStatusAK($assignment);
        $asesmen = $assignment->asesmen;

        // ✅ Get revision requests
        $needsRevisions = PenilaianElemenAk::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->where('status_validasi', 'revision_required')
            ->with('elemen.kriteria')
            ->get();

        $countNeedsRevisions = $needsRevisions->count();

        // ✅ Calculate progress
        $progress = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];

        $statusPekerjaan = $assignment->status_pekerjaan ?? 'not_started';
        $isSubmittedOnly = $statusPekerjaan === 'submitted';
        $isSubmitted = isset($assignment) && in_array($statusPekerjaan, ['submitted', 'approved', 'validated']);
        $isApproved = $statusPekerjaan === 'approved';
        $needsRevision = $statusPekerjaan === 'revision_required';
        $hasRevisionRequests = $countNeedsRevisions > 0;
        $isComplete = $progress['percentage'] == 100;

        return view('asesmen.ak.berkas.upload-excel', compact(
            'asesmen',
            'assignment',
            'needsRevisions',
            'countNeedsRevisions',
            'progress',
            'statusPekerjaan',
            'isSubmittedOnly',
            'isSubmitted',
            'isApproved',
            'needsRevision',
            'hasRevisionRequests',
            'isComplete'
        ));
    }

    /**
     * Halaman Cek Split Penilaian
     */
    public function cekSplitPage($idAsesmen)
    {
        $user = Auth::user();

        // Check if user has access to this asesmen
        $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id)->where('jenis_asesmen', 'ak');
        })
            ->with([
                'userRoles' => function ($query) use ($user) {
                    $query->where('id_user', $user->id)
                        ->where('jenis_asesmen', 'ak')
                        ->with('role');
                },
                'studyProgram.university',
                'studyProgram.degreeLevel',
            ])
            ->findOrFail($idAsesmen);

        $jenjangs = JenjangPenilaian::all();
        $pluckColorSkor = $jenjangs->pluck('color', 'skor');

        return view('asesmen.ak.berkas.cek-split', compact('asesmen', 'jenjangs', 'pluckColorSkor'));
    }

    /**
     * Hitung split status untuk satu asesmen.
     *
     * Return array:
     *   myProgress      : int   — jumlah elemen yang sudah diisi user ini
     *   otherFilled     : bool  — ada asesor lain yang sudah mengisi min 1 elemen
     *   canCheck        : bool  — bisa dicek split (min 2 asesor sudah mengisi semua)
     *   splitCount      : int   — jumlah elemen split (selisih > 1)
     *   noSplitCount    : int   — jumlah elemen tidak split
     *   pendingCount    : int   — jumlah elemen belum dinilai semua asesor
     *   totalElemen     : int
     *   allDone         : bool  — semua asesor sudah mengisi semua elemen
     */
    private function calculateSplitStatus(
        Asesmen $asesmen,
        \Illuminate\Support\Collection $penilaianForAsesmen,
        int $currentUserId,
        int $totalElemen
    ): array {
        $asesors = $asesmen->allAsesorsAk; // relation sudah di-load

        if ($asesors->isEmpty()) {
            return $this->emptySplitStatus($totalElemen);
        }

        $aSesorIds  = $asesors->pluck('id_user')->toArray();
        $otherIds   = array_filter($aSesorIds, fn($id) => $id !== $currentUserId);

        // Group penilaian per asesor: ['id_asesor' => Collection<penilaian>]
        $byAsesor = $penilaianForAsesmen->groupBy('id_asesor');

        // Jumlah elemen yang diisi per asesor
        $countByAsesor = $byAsesor->map(fn($items) => $items->count());

        $myProgress   = $countByAsesor->get($currentUserId, 0);
        $otherFilled  = collect($otherIds)->some(fn($id) => ($countByAsesor->get($id, 0) > 0));

        // Cek split hanya bermakna jika min 2 asesor sudah mengisi minimal 1 elemen
        if (!$otherFilled) {
            return [
                'myProgress'   => $myProgress,
                'otherFilled'  => false,
                'canCheck'     => false,
                'splitCount'   => 0,
                'noSplitCount' => 0,
                'pendingCount' => $totalElemen,
                'totalElemen'  => $totalElemen,
                'allDone'      => false,
                'allFilled'    => false,
            ];
        }

        // Group per elemen: ['id_elemen' => [id_asesor => skor]]
        $byElemen = $penilaianForAsesmen->groupBy('id_elemen');

        $splitCount   = 0;
        $noSplitCount = 0;
        $pendingCount = 0;

        // Hanya hitung elemen yang ada data dari minimal 2 asesor
        // (kalau kurang dari 2 → pending)
        $elemenIds = ElemenStandar::pluck('id');

        foreach ($elemenIds as $elemenId) {
            $skors = ($byElemen->get($elemenId) ?? collect())
                ->whereIn('id_asesor', $aSesorIds)
                ->pluck('skor')
                ->map(fn($s) => (int) $s)
                ->values()
                ->toArray();

            if (count($skors) < 2) {
                $pendingCount++;
            } else {
                $selisih = max($skors) - min($skors);
                if ($selisih > 1) {
                    $splitCount++;
                } else {
                    $noSplitCount++;
                }
            }
        }

        $finalizedStatuses = ['submitted', 'approved', 'validated'];
        $allFinalized = $asesors->every(
            fn($role) => in_array($role->status_pekerjaan, $finalizedStatuses)
        );

        // allFilled = semua asesor sudah mengisi semua elemen (meski belum submit)
        $allFilled = collect($aSesorIds)
            ->every(fn($id) => $countByAsesor->get($id, 0) >= $totalElemen);

        return [
            'myProgress'   => $myProgress,
            'otherFilled'  => true,
            'canCheck'     => true,
            'splitCount'   => $splitCount,
            'noSplitCount' => $noSplitCount,
            'pendingCount' => $pendingCount,
            'totalElemen'  => $totalElemen,
            'allFilled'    => $allFilled,
            'allDone'      => $allFinalized,
        ];
    }

    private function emptySplitStatus(int $totalElemen): array
    {
        return [
            'myProgress'   => 0,
            'otherFilled'  => false,
            'canCheck'     => false,
            'splitCount'   => 0,
            'noSplitCount' => 0,
            'pendingCount' => $totalElemen,
            'totalElemen'  => $totalElemen,
            'allFilled'    => false,
            'allDone'      => false,
        ];
    }
}
