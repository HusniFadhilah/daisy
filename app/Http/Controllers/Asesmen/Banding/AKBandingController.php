<?php

namespace App\Http\Controllers\Asesmen\Banding;

use App\Http\Controllers\Controller;
use App\Jobs\ImportPenilaianExcelJob;
use App\Models\Asesmen;
use App\Models\AsesmenUserRole;
use App\Models\ElemenStandar;
use App\Models\Indikator;
use App\Models\JenjangPenilaian;
use App\Models\Kriteria;
use App\Models\PengajuanAkreditasi;
use App\Models\PenilaianElemenAkBanding;
use App\Models\PenilaianImportLog;
use App\Models\Role;
use App\Services\PenilaianExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AKBandingController extends Controller
{
    public function berkas()
    {
        $user = Auth::user();

        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak_banding')
                ->where('id_role', Role::ID_ROLE_ASESOR_BANDING);
        })
            ->with([
                'userRoles' => function ($query) use ($user) {
                    $query->where('id_user', $user->id)
                        ->where('jenis_asesmen', 'ak_banding')
                        ->where('id_role', Role::ID_ROLE_ASESOR_BANDING)
                        ->with('role');
                },
                // Semua asesor AK (bukan hanya current user) — untuk kalkulasi split
                'allAsesorsAkBanding' => function ($query) {
                    $query->where('jenis_asesmen', 'ak_banding')
                        ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
                        ->with('user')
                        ->orderBy('urutan_asesor');
                },
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'pengajuan.dokumen' => function ($q) {
                    $q->whereIn('jenis_dokumen', [
                        'surat_tugas_asesor_ak_banding',
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
        $allPenilaian = PenilaianElemenAkBanding::whereIn('id_asesmen', $asesmenIds)
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

        return view('asesmen.banding.ak-banding.berkas.index', compact('asesmens', 'statusPekerjaan'));
    }

    /**
     * Show detail asesmen with accordion per elemen
     */
    public function showBerkas($idAsesmen)
    {
        $user = Auth::user();

        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'ak_banding')
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
            'elemenStandar.penilaianElemenAkBanding' => function ($query) use ($asesmen, $user) {
                $query->where('id_asesmen', $asesmen->id)
                    ->where('id_asesor', $user->id)
                    ->with('validator');
            },
        ])->get();

        $needsRevisions = PenilaianElemenAkBanding::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->where('status_validasi', 'revision_required')
            ->with('elemen.kriteria')
            ->get();

        $countNeedsRevisions = $needsRevisions->count();
        $jenjangs        = JenjangPenilaian::all();
        $pluckColorSkor  = $jenjangs->pluck('color', 'skor');
        $progress        = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];
        $uploadedFiles   = $asesmen->pengajuan ? $asesmen->pengajuan->getUploadedDocuments() : null;

        $statusPekerjaan    = $assignment->status_pekerjaan ?? 'not_started';
        $isSubmittedOnly    = $statusPekerjaan === 'submitted';
        $isSubmitted        = in_array($statusPekerjaan, ['submitted', 'approved', 'validated']);
        $isApproved         = $statusPekerjaan === 'approved';
        $needsRevision      = $statusPekerjaan === 'revision_required';
        $hasRevisionRequests = $countNeedsRevisions > 0;
        $isComplete         = $progress['percentage'] == 100;

        // ── Apakah boleh batalkan submit? ─────────────────────────────
        $canUnsubmit = false;
        if ($isSubmittedOnly) {
            $pengajuan = $asesmen->pengajuan;
            if ($pengajuan) {
                $latestLog = $pengajuan->latestRelevantStatusLog([
                    PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
                ]);
                $canUnsubmit = $latestLog &&
                    $latestLog->status_to === PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS;
            } else {
                $canUnsubmit = true;
            }
        }

        // ── Split readiness (reusable via computeSplitResult) ─────────
        $split     = $this->computeSplitResult($asesmen->id, $user->id);
        $hasSplit  = $split['splitCount'] > 0;
        $canSubmit = $split['allComplete'] && !$hasSplit;

        return view('asesmen.banding.ak-banding.berkas.show', compact(
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
            'canUnsubmit',
            'split',
            'hasSplit',
            'canSubmit',
        ));
    }

    private function updateStatusAK($assignment)
    {
        // ✅ AUTO-UPDATE STATUS: not_started → in_progress
        if ($assignment->status_pekerjaan === 'not_started') {
            $assignment->update([
                'status_pekerjaan' => 'in_progress',
                'started_at'       => now(),
            ]);
            $pengajuan = $assignment->asesmen->pengajuan;
            if ($pengajuan) {
                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('ak_banding', 'status_asesor_in_progress');
                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
                    ],
                    [
                        'changed_by' => Auth::id(),
                        'keterangan' => 'Asesor AK banding telah memulai proses penilaian kecukupan banding',
                        'changed_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Save penilaian for specific elemen (AJAX)
     */
    public function simpanNilai(Request $request, $idAsesmen)
    {
        $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'skor'      => 'required|integer|min:0|max:4',
            'komentar'  => 'nullable|string|max:5000',
        ]);

        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak_banding')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini',
                ], 403);
            }

            $existingPenilaian = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->where('id_elemen', $request->id_elemen)
                ->first();

            // Block if already validated/approved
            if ($existingPenilaian) {
                $validatedStatuses = ['validated', 'validated_diff', 'approved'];
                if (in_array($existingPenilaian->status_validasi, $validatedStatuses)) {
                    return response()->json([
                        'success'        => false,
                        'message'        => 'Penilaian telah divalidasi dan disetujui oleh validator. Tidak dapat diubah lagi.',
                        'error_type'     => 'already_validated',
                        'validated_at'   => $existingPenilaian->validated_at,
                        'validator_name' => $existingPenilaian->validator->name ?? 'Validator',
                    ], 422);
                }
            }

            // Block if submitted/approved (except revision_required)
            if (in_array($assignment->status_pekerjaan, ['submitted', 'approved'])) {
                $needsRevision = $existingPenilaian &&
                    $existingPenilaian->status_validasi === 'revision_required';

                if (!$needsRevision) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Penilaian telah di-submit dan tidak dapat diubah',
                    ], 403);
                }
            }

            $wasRevisionRequired = false;
            $validatorId         = null;

            if ($existingPenilaian && $existingPenilaian->status_validasi === 'revision_required') {
                $wasRevisionRequired = true;
                $validatorId         = $existingPenilaian->id_validator;
            }

            // ✅ FIX: beginTransaction was missing in original banding controller
            DB::beginTransaction();

            $penilaian = PenilaianElemenAkBanding::updateOrCreate(
                [
                    'id_asesmen' => $idAsesmen,
                    'id_asesor'  => $user->id,
                    'id_elemen'  => $request->id_elemen,
                ],
                [
                    'skor'             => $request->skor,
                    'komentar'         => $request->komentar,
                    'status'           => 'draft',
                    'status_validasi'  => $wasRevisionRequired ? 'pending' : 'not_validated',
                    'catatan_validator' => $wasRevisionRequired ? null : ($existingPenilaian->catatan_validator ?? null),
                    'preferensi_skor'  => $wasRevisionRequired ? null : ($existingPenilaian->preferensi_skor ?? null),
                    'validated_at'     => $wasRevisionRequired ? null : ($existingPenilaian->validated_at ?? null),
                    'validated_by'     => null,
                    'skor_final'       => null,
                    'revision_count'   => $existingPenilaian
                        ? ($wasRevisionRequired
                            ? $existingPenilaian->revision_count + 1
                            : $existingPenilaian->revision_count)
                        : 0,
                ]
            );

            $needsRevisionCount = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->where('status_validasi', 'revision_required')
                ->count();

            $progress  = $this->calculateProgressBulk([$idAsesmen], $user->id)[$idAsesmen];
            $skorInfo  = JenjangPenilaian::getSkorInfo($request->skor);

            $revisionInfo = null;
            if ($wasRevisionRequired) {
                $revisionInfo = [
                    'was_revised'          => true,
                    'validator_id'         => $validatorId,
                    'remaining_revisions'  => $needsRevisionCount,
                    'new_status'           => 'pending',
                ];
            }

            DB::commit();

            return response()->json([
                'success'              => true,
                'message'              => 'Penilaian berhasil disimpan',
                'data'                 => $penilaian,
                'progress'             => $progress,
                'skor_info'            => $skorInfo,
                'revision_info'        => $revisionInfo,
                'needs_revision_count' => $needsRevisionCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit/Finalisasi penilaian asesor
     */
    public function submitPenilaian(Request $request, $idAsesmen)
    {
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak_banding')
                ->where('status_penawaran', 'accepted')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada penugasan asesmen',
                ], 500);
            }

            $totalElemen    = ElemenStandar::count();
            $assessedElemen = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->whereNotNull('skor')
                ->whereNotNull('komentar')
                ->count();

            if ($assessedElemen < $totalElemen) {
                return response()->json([
                    'success'  => false,
                    'message'  => "Penilaian belum lengkap. Anda baru menilai {$assessedElemen} dari {$totalElemen} elemen.",
                    'assessed' => $assessedElemen,
                    'total'    => $totalElemen,
                ], 422);
            }

            // ── TAMBAHAN: Cek semua asesor sudah selesai ──────────────
            $splitReadiness = $this->checkAllAsesorsComplete($idAsesmen, $user->id);
            if (!$splitReadiness['allComplete']) {
                $belumSelesai = collect($splitReadiness['asesors'])
                    ->where('is_done', false)
                    ->where('is_me', false)
                    ->map(fn($a) => $a['name'] . " ({$a['completed']}/{$a['total']} elemen)")
                    ->implode(', ');

                return response()->json([
                    'success'        => false,
                    'message'        => 'Finalisasi belum dapat dilakukan. Asesor lain belum menyelesaikan penilaian: ' . $belumSelesai,
                    'error_type'     => 'other_asesor_not_complete',
                    'split_readiness' => $splitReadiness['asesors'],
                ], 422);
            }

            DB::beginTransaction();

            PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->update(['status' => 'submitted']);

            $assignment->update([
                'status_pekerjaan' => 'submitted',
                'submitted_at'     => now(),
            ]);

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Penilaian berhasil di-submit! Mohon menunggu proses validasi.',
                'submitted_at' => now()->locale('id')->translatedFormat('d M Y H:i'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
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
                ->where('jenis_asesmen', 'ak_banding')
                ->where('status_pekerjaan', 'submitted')
                ->firstOrFail();

            // ── Cek: apakah sedang divalidasi? ───────────────────
            $pengajuan = $assignment->asesmen->pengajuan;
            if ($pengajuan) {
                $latestLog = $pengajuan->latestRelevantStatusLog([
                    PengajuanAkreditasi::STATUS_AK_BANDING_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
                ]);

                if (
                    $latestLog &&
                    $latestLog->status_to === PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION
                ) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Penilaian sedang dalam proses validasi dan tidak dapat dibatalkan. '
                            . 'Hubungi validator jika ada kesalahan.',
                        'reason'  => PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION,
                    ], 422);
                }
            }

            // Block if any penilaian already validated
            $hasValidated = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->whereIn('status_validasi', ['validated', 'validated_diff'])
                ->exists();

            if ($hasValidated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian telah divalidasi dan disetujui, tidak bisa dibatalkan.',
                ], 422);
            }

            // ✅ Block if pengajuan sudah masuk status ak_banding_on_validation
            $pengajuan = Asesmen::find($idAsesmen)?->pengajuan;
            if ($pengajuan && $pengajuan->status === PengajuanAkreditasi::STATUS_AK_BANDING_ON_VALIDATION) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat membatalkan submit karena asesmen banding sudah dalam proses validasi.',
                ], 422);
            }

            DB::beginTransaction();

            PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
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
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan submit: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset/Hapus semua penilaian untuk asesmen tertentu
     */
    public function resetAllPenilaian($idAsesmen)
    {
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak_banding')
                ->first();

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini',
                ], 403);
            }

            if (in_array($assignment->status_pekerjaan, ['submitted', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mereset penilaian yang telah di-submit atau disetujui. Silakan batalkan submit terlebih dahulu.',
                ], 422);
            }

            DB::beginTransaction();

            $totalDeleted = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->count();

            PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->delete();

            $assignment->update([
                'status_pekerjaan' => 'not_started',
                'submitted_at'     => null,
            ]);

            DB::commit();

            $progress = $this->calculateProgressBulk([$idAsesmen], $user->id)[$idAsesmen];

            return response()->json([
                'success'       => true,
                'message'       => "Berhasil menghapus {$totalDeleted} penilaian. Semua penilaian telah direset.",
                'deleted_count' => $totalDeleted,
                'progress'      => $progress,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reset penilaian banding failed: ' . $e->getMessage(), [
                'id_asesmen' => $idAsesmen,
                'id_asesor'  => Auth::id(),
                'trace'      => $e->getTraceAsString(),
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

            // ✅ FIX: Gunakan Role::ID_ROLE_ASESOR_BANDING, bukan hardcoded 3
            $asesors = $asesmen->userRoles->filter(function ($userRole) {
                return $userRole->id_role === Role::ID_ROLE_ASESOR_BANDING;
            })->values();

            $asesor1 = $asesors->first();
            $asesor2 = $asesors->skip(1)->first();

            if (!$asesor1 || !$asesor2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesmen banding harus memiliki minimal 2 asesor.',
                ], 400);
            }

            $excelService = new PenilaianExcelService(PenilaianElemenAkBanding::class);
            $filePath     = $excelService->generateTemplate($asesmen, $asesor1, $asesor2);

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

            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($idAsesmen);

            $mode     = $request->query('mode', 'full');
            $useColor = $request->query('color', 'true') === 'true';

            if (!in_array($mode, ['template', 'full', 'personal', 'split'])) {
                return redirect()->back()->with('error', 'Mode download tidak valid');
            }

            $excelService = new PenilaianExcelService(PenilaianElemenAkBanding::class, $mode, $useColor);

            $filePath = $mode === 'template'
                ? $excelService->generateTemplate($asesmen)
                : $excelService->generateWithData($asesmen, $user->id);

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
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $user = Auth::user();

            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($idAsesmen);

            $file     = $request->file('file');
            $filename = 'import_' . $asesmen->code . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('temp/imports', $filename);

            $importLog = PenilaianImportLog::create([
                'id_asesmen' => $asesmen->id,
                'id_asesor'  => $user->id,
                'filename'   => $file->getClientOriginalName(),
                'status'     => 'queued',
            ]);

            ImportPenilaianExcelJob::dispatch(PenilaianElemenAkBanding::class, $filePath, $asesmen->id, $user->id, $importLog->id);

            return response()->json([
                'success'       => true,
                'message'       => 'File berhasil diupload. Proses input data penilaian sedang diproses di background.',
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
                'data'    => [
                    'status'        => $importLog->status,
                    'total_rows'    => $importLog->total_rows,
                    'imported_rows' => $importLog->imported_rows,
                    'failed_rows'   => $importLog->failed_rows,
                    'errors'        => $importLog->errors,
                    'errors_message'        => $importLog->errors_message,
                    'success_rate'  => $importLog->success_rate,
                    'started_at'    => $importLog->started_at?->locale('id')->translatedFormat('d M Y H:i:s'),
                    'completed_at'  => $importLog->completed_at?->locale('id')->translatedFormat('d M Y H:i:s'),
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
                'data'    => $logs,
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
     * Get comparison data for all asesors (AJAX)
     */
    public function getComparisonData(Asesmen $asesmen)
    {
        try {
            $idAsesmen = $asesmen->id;

            // ✅ FIX: Filter by ID_ROLE_ASESOR_BANDING, bukan by role name 'asesor'
            $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'ak_banding')
                ->where('id_role', Role::ID_ROLE_ASESOR_BANDING)
                ->with('user')
                ->orderBy('urutan_asesor')
                ->get();

            $kriterias = Kriteria::with([
                'elemenStandar',
                'elemenStandar.indikator',
                'elemenStandar.penilaianElemenAkBanding' => function ($q) use ($asesors, $asesmen) {
                    $q->whereIn('id_asesor', $asesors->pluck('id_user'))
                        ->where('id_asesmen', $asesmen->id);
                },
                'elemenStandar.penilaianElemenAkBanding.asesor',
            ])->get();

            // Calculate statistics
            $totalElemen  = 0;
            $agreedCount  = 0;
            $diffCount    = 0;
            $pendingCount = 0;

            foreach ($kriterias as $kriteria) {
                foreach ($kriteria->elemenStandar as $elemen) {
                    $totalElemen++;
                    $skors = [];

                    foreach ($asesors as $asesor) {
                        $penilaian = $elemen->penilaianElemenAkBanding
                            ->where('id_asesor', $asesor->id_user)
                            ->first();

                        if ($penilaian && $penilaian->skor !== null) {
                            $skors[] = $penilaian->skor;
                        }
                    }

                    if (empty($skors)) {
                        $pendingCount++;
                    } elseif (count(array_unique($skors)) === 1) {
                        $agreedCount++;
                    } else {
                        $diffCount++;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'asesors'    => $asesors,
                    'kriterias'  => $kriterias,
                    'statistics' => [
                        'total'   => $totalElemen,
                        'agreed'  => $agreedCount,
                        'diff'    => $diffCount,
                        'pending' => $pendingCount,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
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

            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak_banding')
                ->firstOrFail();

            $result = $this->computeSplitResult($idAsesmen, $user->id);

            return response()->json(['success' => true, ...$result]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hitung data split untuk satu asesmen.
     * Reusable — dipanggil dari checkSplitResult(), submitPenilaian(),
     * showBerkas(), dan uploadExcelPage().
     *
     * Return:
     *   otherHasFilled : bool
     *   allComplete    : bool   — semua asesor sudah mengisi SEMUA elemen
     *   splitCount     : int
     *   splitItems     : array
     *   asesors        : Collection  — data lengkap asesor + progress
     */
    private function computeSplitResult(int $idAsesmen, int $currentUserId): array
    {
        $totalElemen = ElemenStandar::count();

        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'ak_banding')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
            ->with('user')
            ->orderBy('urutan_asesor')
            ->get();

        // ── Progress per asesor ───────────────────────────────
        $countMap = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
            ->whereIn('id_asesor', $asesors->pluck('id_user'))
            ->whereNotNull('skor')
            ->selectRaw('id_asesor, COUNT(*) as completed')
            ->groupBy('id_asesor')
            ->pluck('completed', 'id_asesor');

        $asesorsProgress = $asesors->map(fn($role) => [
            'id_user'    => $role->id_user,
            'name'       => $role->user->name ?? 'N/A',
            'completed'  => (int) $countMap->get($role->id_user, 0),
            'total'      => $totalElemen,
            'percentage' => $totalElemen
                ? round($countMap->get($role->id_user, 0) / $totalElemen * 100, 1)
                : 0,
            'is_done'    => (int) $countMap->get($role->id_user, 0) >= $totalElemen,
            'is_me'      => $role->id_user === $currentUserId,
        ]);

        $otherAsesors   = $asesors->where('id_user', '!=', $currentUserId);
        $otherHasFilled = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
            ->whereIn('id_asesor', $otherAsesors->pluck('id_user'))
            ->whereNotNull('skor')
            ->exists();

        $allComplete = $asesorsProgress->every(fn($a) => $a['is_done']);

        if (!$otherHasFilled) {
            return [
                'otherHasFilled' => false,
                'allComplete'    => false,
                'splitCount'     => 0,
                'splitItems'     => [],
                'asesors'        => $asesorsProgress,
            ];
        }

        // ── Hitung split ─────────────────────────────────────
        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.penilaianElemenAkBanding' => fn($q) =>
            $q->where('id_asesmen', $idAsesmen)
                ->whereIn('id_asesor', $asesors->pluck('id_user'))
                ->whereNotNull('skor'),
        ])->get();

        $splitItems = [];

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemen) {
                $penilaians = $elemen->penilaianElemenAkBanding;

                if ($penilaians->count() < 2) continue;

                $skors   = $penilaians->pluck('skor')->map(fn($s) => (int) $s)->toArray();
                $selisih = max($skors) - min($skors);

                if ($selisih > 1) {
                    $skorDetail = $asesors->map(function ($asesor) use ($penilaians, $currentUserId) {
                        $p = $penilaians->firstWhere('id_asesor', $asesor->id_user);
                        return [
                            'urutan' => $asesor->urutan_asesor,
                            'nama'   => $asesor->user->name,
                            'skor'   => $p ? (int) $p->skor : null,
                            'is_me'  => $asesor->id_user === $currentUserId,
                        ];
                    })->filter(fn($d) => $d['skor'] !== null)->values();

                    $splitItems[] = [
                        'elemenId'     => $elemen->id,
                        'kodeElemen'   => $elemen->kode_elemen,
                        'pernyataan'   => Str::limit($elemen->pernyataan_elemen, 80),
                        'kodeKriteria' => $kriteria->kode_kriteria,
                        'selisih'      => $selisih,
                        'skors'        => $skorDetail,
                    ];
                }
            }
        }

        return [
            'otherHasFilled' => true,
            'allComplete'    => $allComplete,
            'splitCount'     => count($splitItems),
            'splitItems'     => $splitItems,
            'asesors'        => $asesorsProgress,
        ];
    }

    /**
     * Halaman Upload Excel Penilaian Manual
     */
    public function uploadExcelPage($idAsesmen)
    {
        $user = Auth::user();

        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'ak_banding')
            ->firstOrFail();

        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . ($user->role_selected) . ' tidak diizinkan membuka halaman ini.');
        }

        $this->updateStatusAK($assignment);
        $asesmen = $assignment->asesmen;

        $needsRevisions = PenilaianElemenAkBanding::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->where('status_validasi', 'revision_required')
            ->with('elemen.kriteria')
            ->get();

        $countNeedsRevisions = $needsRevisions->count();
        $progress            = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];

        $statusPekerjaan     = $assignment->status_pekerjaan ?? 'not_started';
        $isSubmittedOnly     = $statusPekerjaan === 'submitted';
        $isSubmitted         = in_array($statusPekerjaan, ['submitted', 'approved', 'validated']);
        $isApproved          = $statusPekerjaan === 'approved';
        $needsRevision       = $statusPekerjaan === 'revision_required';
        $hasRevisionRequests = $countNeedsRevisions > 0;
        $isComplete          = $progress['percentage'] == 100;

        // ── Split readiness (reusable via computeSplitResult) ─────────
        $split     = $this->computeSplitResult($asesmen->id, $user->id);
        $hasSplit  = $split['splitCount'] > 0;
        $canSubmit = $split['allComplete'] && !$hasSplit;

        return view('asesmen.banding.ak-banding.berkas.upload-excel', compact(
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
            'isComplete',
            'split',
            'hasSplit',
            'canSubmit',
        ));
    }

    /**
     * Halaman Cek Split Penilaian
     */
    public function cekSplitPage($idAsesmen)
    {
        $user = Auth::user();

        $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id)
                ->where('jenis_asesmen', 'ak_banding');
        })
            ->with([
                'userRoles' => function ($query) use ($user) {
                    $query->where('id_user', $user->id)
                        ->where('jenis_asesmen', 'ak_banding')
                        ->with('role');
                },
                'studyProgram.university',
                'studyProgram.degreeLevel',
            ])
            ->findOrFail($idAsesmen);

        $jenjangs       = JenjangPenilaian::all();
        $pluckColorSkor = $jenjangs->pluck('color', 'skor');

        return view('asesmen.banding.ak-banding.berkas.cek-split', compact('asesmen', 'jenjangs', 'pluckColorSkor'));
    }


    /**
     * Get heatmap data for visualization
     */
    public function getHeatmapData($idAsesmen)
    {
        $user = Auth::user();

        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'ak_banding')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $heatmapData = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
            ->where('id_asesor', $user->id)
            ->with('elemen.kriteria')
            ->get()
            ->map(function ($penilaian) {
                return [
                    'id_elemen'     => $penilaian->id_elemen,
                    'kriteria_code' => $penilaian->elemen->kriteria->kode_kriteria,
                    'elemen_code'   => $penilaian->elemen->kode_elemen,
                    'skor'          => $penilaian->skor,
                    'skor_info'     => JenjangPenilaian::getSkorInfo($penilaian->skor),
                ];
            });

        return response()->json(['success' => true, 'data' => $heatmapData]);
    }


    /**
     * HTTP endpoint: inisialisasi penilaian banding dari AK (dipanggil manual via route)
     */
    public function initFromAK($idAsesmen)
    {
        $user = Auth::user();

        $assignmentBanding = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'ak_banding')
            ->where('id_role', Role::ID_ROLE_ASESOR_BANDING)
            ->first();

        if (!$assignmentBanding) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke asesmen banding ini',
            ], 403);
        }

        if (in_array($assignmentBanding->status_pekerjaan, ['submitted', 'approved'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menginisialisasi karena penilaian banding sudah di-submit atau disetujui.',
            ], 422);
        }

        try {
            $result = $this->doInitFromAK($assignmentBanding);

            return response()->json([
                'success'     => true,
                'message'     => "Berhasil menginisialisasi {$result['initialized']} penilaian dari data AK."
                    . ($result['skipped'] > 0 ? " {$result['skipped']} elemen dilewati karena sudah memiliki penilaian." : ''),
                'initialized' => $result['initialized'],
                'skipped'     => $result['skipped'],
                'source'      => $result['source'],
                'progress'    => $this->calculateProgressBulk([$assignmentBanding->id_asesmen], $user->id)[$assignmentBanding->id_asesmen],
            ]);
        } catch (\Exception $e) {
            Log::error('Init from AK (HTTP) failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal inisialisasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inti logika inisialisasi penilaian banding dari AK.
     * Dipanggil setelah asesor banding menerima penawaran, maupun via HTTP endpoint.
     *
     * Cara pakai dari controller lain:
     *   (new AKBandingController)->doInitFromAK($assignmentBanding);
     *
     * @param  AsesmenUserRole  $assignmentBanding  — row urutan asesor banding yang baru accept
     * @return array  ['initialized', 'skipped', 'source']
     * @throws \Exception
     */
    public function doInitFromAK(AsesmenUserRole $assignmentBanding): array
    {
        $idAsesmen       = $assignmentBanding->id_asesmen;
        $idAsesorBanding = $assignmentBanding->id_user;
        $urutanBanding = $assignmentBanding->urutan_asesor;

        // Cari POSISI (ke-berapa) asesor ini di antara sesama banding asesor (0-based).
        // Jangan pakai urutan_asesor langsung sebagai index — nilainya bisa non-sequential
        // (contoh: 1, 3) bila assignment di-create bersamaan dgn role lain di asesmen yg sama.
        $semuaBandingAsesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'ak_banding')
            ->where('id_role', Role::ID_ROLE_ASESOR_BANDING)
            ->orderBy('urutan_asesor')
            ->pluck('id_user');

        $posisiBanding = $semuaBandingAsesors->search($idAsesorBanding); // 0-based

        if ($posisiBanding === false) {
            throw new \Exception(
                "User id={$idAsesorBanding} tidak ditemukan di daftar asesor banding untuk asesmen ini."
            );
        }

        // Ambil semua asesor AK urut by urutan_asesor,
        // pilih berdasarkan POSISI banding asesor (bukan nilai urutan_asesor)
        $asesorsAK = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'ak')
            ->where('id_role', Role::ID_ROLE_ASESOR)
            ->where('status_penawaran', 'accepted')
            ->orderBy('urutan_asesor')
            ->with('user')
            ->get();

        $assignmentAK = $asesorsAK->get($posisiBanding); // 0-based

        if (!$assignmentAK) {
            throw new \Exception(
                "Tidak ditemukan asesor AK posisi ke-" . ($posisiBanding + 1) . " untuk asesmen ini."
                    . " (Total asesor AK: {$asesorsAK->count()})"
            );
        }

        // Ambil penilaian AK sumber (model AK, bukan banding)
        $penilaianAKSumber = \App\Models\PenilaianElemenAk::where('id_asesmen', $idAsesmen)
            ->where('id_asesor', $assignmentAK->id_user)
            ->get();

        if ($penilaianAKSumber->isEmpty()) {
            throw new \Exception(
                "Asesor AK posisi ke-{$urutanBanding} ({$assignmentAK->user->name}) belum memiliki data penilaian."
            );
        }

        DB::beginTransaction();

        $initialized = 0;
        $skipped     = 0;

        foreach ($penilaianAKSumber as $src) {
            // Lewati jika sudah ada penilaian banding untuk elemen ini
            $exists = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $idAsesorBanding)
                ->where('id_elemen', $src->id_elemen)
                ->whereNotNull('skor')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            // Copy data, status tetap draft & not_validated (belum difinalisasi)
            PenilaianElemenAkBanding::updateOrCreate(
                [
                    'id_asesmen' => $idAsesmen,
                    'id_asesor'  => $idAsesorBanding,
                    'id_elemen'  => $src->id_elemen,
                ],
                [
                    'skor'              => $src->skor,
                    'komentar'          => $src->komentar,
                    'status'            => 'draft',
                    'status_validasi'   => 'not_validated',
                    'validated_at'      => null,
                    'validated_by'      => null,
                    'skor_final'        => null,
                    'preferensi_skor'   => null,
                    'catatan_validator' => null,
                    'revision_count'    => 0,
                    'is_locked'         => false,
                ]
            );

            $initialized++;
        }

        // Update status pekerjaan ke in_progress jika sebelumnya not_started
        if ($initialized > 0) {
            $this->updateStatusAK($assignmentBanding); // pastikan status diperbarui sesuai logika AK
        }

        DB::commit();  // ← pindah ke sini, selalu dijalankan

        return [
            'initialized' => $initialized,
            'skipped'     => $skipped,
            'source'      => [
                'urutan_asesor' => $urutanBanding,
                'nama_asesor'   => $assignmentAK->user->name ?? '-',
            ],
        ];
    }


    /**
     * Hitung progress untuk satu user di banyak asesmen sekaligus
     */
    private function calculateProgressBulk($asesmenIds, $userId)
    {
        $totalElemens = ElemenStandar::count();

        $penilaian = PenilaianElemenAkBanding::where('id_asesor', $userId)
            ->whereIn('id_asesmen', $asesmenIds)
            ->whereNotNull('skor')
            ->select('id_asesmen', DB::raw('COUNT(*) as completed'))
            ->groupBy('id_asesmen')
            ->pluck('completed', 'id_asesmen');

        $progress = [];
        foreach ($asesmenIds as $id) {
            $completed  = $penilaian[$id] ?? 0;
            $percentage = $totalElemens ? round($completed / $totalElemens * 100, 1) : 0;

            $progress[$id] = [
                'total'      => $totalElemens,
                'completed'  => $completed,
                'remaining'  => $totalElemens - $completed,
                'percentage' => $percentage,
            ];
        }

        return $progress;
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
        $asesors = $asesmen->allAsesorsAkBanding; // relation sudah di-load

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

    /**
     * Cek apakah semua asesor AK pada suatu asesmen sudah mengisi semua elemen.
     *
     * Return array:
     *   allComplete   : bool  — semua asesor sudah 100%
     *   otherComplete : bool  — semua asesor *lain* sudah 100%
     *   asesors       : array — [{ id_user, name, completed, total, percentage, is_done, is_me }]
     */
    private function checkAllAsesorsComplete(int $idAsesmen, int $currentUserId): array
    {
        $totalElemen = ElemenStandar::count();

        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'ak_banding')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
            ->with('user')
            ->orderBy('urutan_asesor')
            ->get();

        if ($asesors->isEmpty() || $totalElemen === 0) {
            return ['allComplete' => false, 'otherComplete' => false, 'asesors' => []];
        }

        $countMap = PenilaianElemenAkBanding::where('id_asesmen', $idAsesmen)
            ->whereIn('id_asesor', $asesors->pluck('id_user'))
            ->whereNotNull('skor')
            ->selectRaw('id_asesor, COUNT(*) as completed')
            ->groupBy('id_asesor')
            ->pluck('completed', 'id_asesor');

        $result        = [];
        $allComplete   = true;
        $otherComplete = true;

        foreach ($asesors as $role) {
            $completed  = (int) ($countMap->get($role->id_user, 0));
            $percentage = round($completed / $totalElemen * 100, 1);
            $done       = $completed >= $totalElemen;

            $result[] = [
                'id_user'    => $role->id_user,
                'name'       => $role->user->name ?? 'N/A',
                'completed'  => $completed,
                'total'      => $totalElemen,
                'percentage' => $percentage,
                'is_done'    => $done,
                'is_me'      => $role->id_user === $currentUserId,
            ];

            if (!$done) {
                $allComplete = false;
                if ($role->id_user !== $currentUserId) {
                    $otherComplete = false;
                }
            }
        }

        return [
            'allComplete'   => $allComplete,
            'otherComplete' => $otherComplete,
            'asesors'       => $result,
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
