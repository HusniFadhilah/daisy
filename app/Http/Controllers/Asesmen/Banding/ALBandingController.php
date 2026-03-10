<?php

namespace App\Http\Controllers\Asesmen\Banding;

use App\Models\Role;
use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\Indikator;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use App\Models\JenjangPenilaian;
use App\Models\PenilaianElemenAlBanding;
use App\Models\PenilaianImportLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ImportPenilaianExcelJob;
use App\Services\PenilaianExcelService;

class ALBandingController extends Controller
{
    /**
     * Display a listing of asesmens (berkas) for current user
     */
    public function berkas()
    {
        $user = Auth::user();

        // Get asesmens where user is assigned
        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id)->where('jenis_asesmen', 'al_banding')->where('id_role', Role::ID_ROLE_ASESOR_BANDING);
        })
            ->with([
                'userRoles' => function ($query) use ($user) {
                    $query->where('id_user', $user->id)
                        ->where('jenis_asesmen', 'al_banding')
                        ->where('id_role', Role::ID_ROLE_ASESOR_BANDING)
                        ->with('role');
                },
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'pengajuan.dokumen' => function ($q) {
                    // ✅ Load dokumen akreditasi
                    $q->whereIn('jenis_dokumen', [
                        'surat_tugas_asesor_al_banding',
                        'data_kualitatif',
                        'draft_borang',
                        'borang_final',  // LED
                        'data_suplemen',                                      // ✅ Suplemen (fixed)
                        'data_kuantitatif',
                        'kuantitatif'                    // LKPS
                    ])
                        ->where('is_latest', true)
                        ->orderBy('created_at', 'desc');
                }
            ])
            ->latest()
            ->paginate(10);

        // Calculate progress
        $progressAll = $this->calculateProgressBulk(
            $asesmens->pluck('id')->toArray(),
            $user->id
        );

        // Map ke masing-masing asesmen
        foreach ($asesmens as $asesmen) {
            $asesmen->progress = $progressAll[$asesmen->id] ?? [
                'total' => 0,
                'completed' => 0,
                'remaining' => 0,
                'percentage' => 0
            ];

            $assignment = $asesmen->userRoles->first();
            $asesmen->statusInfo = AsesmenUserRole::getStatusInfo($assignment);
        }

        $statusPekerjaan = AsesmenUserRole::STATUS_PEKERJAAN;

        return view('asesmen.banding.al-banding.berkas.index', compact('asesmens', 'statusPekerjaan'));
    }


    /**
     * Show detail asesmen with accordion per elemen
     */
    public function showBerkas($idAsesmen)
    {
        $user = Auth::user();
        $step = (int) request('step', 1);
        $step = in_array($step, [1, 2]) ? $step : 1;
        // Check if user has access to this asesmen
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al_banding')
            ->firstOrFail();
        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . ($user->role_selected) . ' tidak diizinkan membuka halaman ini. Silahkan pindah ke role lain');
        }

        $this->updateStatusAL($assignment);
        $asesmen = $assignment->asesmen;

        // Get all kriteria with elemen and indikator
        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
            'elemenStandar.penilaianElemenAlBanding' => function ($query) use ($asesmen, $user) {
                $query->where('id_asesmen', $asesmen->id)
                    ->where('id_asesor', $user->id);
            }
        ])->get();

        $needsRevisions = PenilaianElemenAlBanding::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->with('elemen.kriteria')
            ->get();
        $jenjangs = JenjangPenilaian::all();
        // Calculate progress
        $progress = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];
        // ✅ Ambil semua asesor TIM dulu, SEBELUM updateStatusAL
        $asesorTeam = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al_banding')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->with('user')
            ->get();

        // ✅ Tentukan siapa editor SEBELUM status diubah
        $firstActiveAsesor = $asesorTeam
            ->where('status_pekerjaan', '!=', 'not_started')
            ->sortBy('started_at')
            ->first();

        // $isEditorAsesor = !$firstActiveAsesor || $firstActiveAsesor->id_user == $user->id;
        $isEditorAsesor = true;

        $otherAsesorsProgress = [];
        foreach ($asesorTeam as $member) {
            if ($member->id_user == $user->id) continue;
            $otherAsesorsProgress[$member->id_user] = [
                'user'             => $member->user,
                'status_pekerjaan' => $member->status_pekerjaan,
                'progress'         => $this->calculateProgressBulk([$idAsesmen], $member->id_user)[$idAsesmen],
                'started_at'       => $member->started_at,
            ];
        }
        $isFinalized = in_array($asesmen->asesmenLapanganBanding->status, ['completed', 'finalized']);
        $isInProgress = $asesmen->asesmenLapanganBanding->isInProgress();
        $uploadedFiles = $asesmen->pengajuan ? $asesmen->pengajuan->getUploadedDocuments() : null;

        return view('asesmen.banding.al-banding.berkas.show', compact(
            'asesmen',
            'kriterias',
            'progress',
            'jenjangs',
            'step',
            'needsRevisions',
            'isFinalized',
            'assignment',
            'uploadedFiles',
            'isInProgress',
            'asesorTeam',
            'isEditorAsesor',
            'firstActiveAsesor',
            'otherAsesorsProgress'
        ));
    }

    private function updateStatusAL($assignment)
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
                $pengajuan->checkUpdateStatusAKAL('al_banding', 'status_asesor_in_progress');
                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    ],
                    [
                        'changed_by'  => Auth::id(),
                        'keterangan'  => 'Asesor AL telah memulai proses penilaian lapangan',
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
            $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('id_user', $user->id)
                ->where('jenis_asesmen', 'al_banding')
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini'
                ], 403);
            }

            // Update or create penilaian
            $penilaian = PenilaianElemenAlBanding::updateOrCreate(
                [
                    'id_asesmen' => $idAsesmen,
                    'id_asesor' => $user->id,
                    'id_elemen' => $request->id_elemen,
                ],
                [
                    'skor' => $request->skor,
                    'komentar' => $request->komentar,
                    'status' => 'draft',
                ]
            );

            // Calculate new progress
            $progress = $this->calculateProgressBulk([$idAsesmen], $user->id)[$idAsesmen];

            // Get skor label and class for response
            $skorInfo = JenjangPenilaian::getSkorInfo($request->skor);

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan',
                'data' => $penilaian,
                'progress' => $progress,
                'skor_info' => $skorInfo,
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
        $penilaian = PenilaianElemenAlBanding::where('id_asesor', $userId)
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
            ->where('jenis_asesmen', 'al_banding')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get all penilaian with kriteria, elemen info
        $heatmapData = PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
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
                ->where('jenis_asesmen', 'al_banding')
                ->where('status_penawaran', 'accepted')
                ->first();

            if (!$assignment)
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada penugasan asesmen',
                ], 500);

            // Check if all elemen have been assessed
            $totalElemen = ElemenStandar::count();
            $assessedElemen = PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
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
            PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->update([
                    'status' => 'approved',
                ]);

            // Update assignment status
            $assignment->update([
                // 'status_pekerjaan' => 'submitted',
                'status_pekerjaan' => 'approved',
                'submitted_at' => now(),
            ]);

            $pengajuan = $assignment->asesmen->pengajuan;
            if ($pengajuan) {
                // ada assignment yang status_pekerjaan-nya BUKAN approved?
                $hasUnapproved = $pengajuan->assignments()
                    ->where('status_pekerjaan', '!=', 'approved')
                    ->exists();

                if ($hasUnapproved) {
                } else {
                    $statusFrom = $pengajuan->status;
                    $pengajuan->checkUpdateStatusAKAL('al_banding', 'status_asesor_selesai');
                    $pengajuan->statusLog()->firstOrCreate(
                        [
                            'status_from' => $statusFrom,
                            'status_to'   => PengajuanAkreditasi::STATUS_AL_SELESAI,
                        ],
                        [
                            'changed_by'  => Auth::id(),
                            'keterangan'  => 'Seluruh asesor AL telah menyelesaikan proses penilaian lapangan',
                            'changed_at'  => now(),
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $assignment->status_pekerjaan == 'submitted' ? 'Penilaian berhasil di-submit! Menunggu validasi oleh LAMDEPILAR.' : 'Penilaian berhasil di-submit dan difinalisasi!',
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
                ->where('jenis_asesmen', 'al_banding')
                ->where('status_pekerjaan', 'submitted')
                ->firstOrFail();

            // ✅ PERBAIKAN: Hanya cek yang benar-benar telah VALIDATED (final)
            $hasValidated = PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)->whereIn('status', ['approved'])
                ->exists();

            if ($hasValidated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian telah divalidasi dan disetujui, tidak bisa dibatalkan.',
                ], 422);
            }

            // ✅ TAMBAHAN: Cek jika telah approved
            if ($assignment->status_pekerjaan === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian telah disetujui, tidak bisa dibatalkan.',
                ], 422);
            }

            DB::beginTransaction();

            // Update back to draft
            PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->update([
                    'status' => 'draft',
                ]);

            $assignment->update([
                'status_pekerjaan' => 'in_progress',
                'submitted_at' => null,
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
     * ADD THIS METHOD TO: App\Http\Controllers\ALBandingController
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
                ->where('jenis_asesmen', 'al_banding')
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
                $totalDeleted = PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
                    ->where('id_asesor', $user->id)
                    ->count();

                // Delete all penilaian for this user and asesmen
                PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
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

            $excelService = new PenilaianExcelService(PenilaianElemenAlBanding::class);
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
            $useColor = $request->query('color', 'false') === 'true';

            // Validate mode
            if (!in_array($mode, ['template', 'full', 'personal'])) {
                return redirect()->back()->with('error', 'Mode download tidak valid');
            }

            // Create service dengan mode
            $excelService = new PenilaianExcelService(PenilaianElemenAlBanding::class, $mode, $useColor); // atau PenilaianElemenAlBanding

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

    // /**
    //  * Import penilaian dari Excel (using Queue)
    //  */
    // public function importExcel(Request $request, $idAsesmen)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
    //     ]);

    //     try {
    //         $user = Auth::user();

    //         // Verify access
    //         $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
    //             $query->where('id_user', $user->id);
    //         })->findOrFail($idAsesmen);

    //         // Store file temporarily
    //         $file = $request->file('file');
    //         $filename = 'import_' . $asesmen->code . '_' . time() . '.' . $file->getClientOriginalExtension();
    //         $filePath = $file->storeAs('temp/imports', $filename);

    //         // Create import log
    //         $importLog = PenilaianImportLog::create([
    //             'id_asesmen' => $asesmen->id,
    //             'id_asesor' => $user->id,
    //             'filename' => $file->getClientOriginalName(),
    //             'status' => 'queued',
    //         ]);

    //         // Dispatch job
    //         ImportPenilaianExcelJob::dispatch(PenilaianElemenAlBanding::class, $filePath, $asesmen->id, $user->id, $importLog->id);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'File berhasil diupload. Proses input data penilaian sedang diproses di background.',
    //             'import_log_id' => $importLog->id,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error($e);
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal upload excel penilaian: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

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
            // Get all asesors for this asesmen (AL)
            $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'al_banding')
                ->whereHas('role', function ($q) {
                    $q->where('name', 'asesor');
                })
                ->with('user')
                ->orderBy('urutan_asesor')
                ->get();

            // Get all kriteria with elemen and penilaian
            $kriterias = Kriteria::with([
                'elemenStandar' => function ($q) {
                    $q->orderBy('kode_elemen');
                },
                'elemenStandar.indikator' => function ($q) {
                    $q->orderBy('kode_indikator');
                },
                'elemenStandar.penilaianElemenAlBanding' => function ($q) use ($asesors) {
                    $q->whereIn('id_asesor', $asesors->pluck('id_user'));
                },
                'elemenStandar.penilaianElemenAlBanding.asesor'
            ])->orderBy('kode_kriteria')->get();

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
                    } elseif (count(array_unique($skors)) === 1) {
                        $agreedCount++;
                    } else {
                        $diffCount++;
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

    public function exportLaporanPdf($idAsesmen)
    {
        $user = Auth::user();

        // akses minimal sama seperti showBerkas
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al_banding')
            ->where('status_penawaran', 'accepted')
            ->firstOrFail();

        // muat data asesmen + prodi + univ
        $asesmen = Asesmen::with(['studyProgram.university', 'studyProgram.degreeLevel'])->findOrFail($idAsesmen);

        // ambil daftar asesor AL accepted (untuk cover)
        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al_banding')
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->with('user')
            ->orderBy('urutan_asesor')
            ->get();

        // ambil elemen + kriteria + penilaian user ini
        $rows = ElemenStandar::with('kriteria')
            ->orderBy('id_kriteria')
            ->orderBy('kode_elemen')
            ->get()
            ->map(function ($elemen) use ($idAsesmen, $user) {
                $p = PenilaianElemenAlBanding::where('id_asesmen', $idAsesmen)
                    ->where('id_asesor', $user->id)
                    ->where('id_elemen', $elemen->id)
                    ->first();

                $skor = $p?->skor;
                $skorLabel = $skor === null ? '-' : JenjangPenilaian::getSkorLabelAttribute($skor, true);

                return [
                    'kriteria' => $elemen->kriteria?->kode_kriteria . ' - ' . $elemen->kriteria?->nama_kriteria,
                    'kode_elemen' => $elemen->kode_elemen,
                    'pernyataan' => $elemen->pernyataan_elemen,
                    'skor' => $skorLabel,
                    'komentar' => $p?->komentar ?? '',
                ];
            });

        // =========================
        // 1) Generate PDF Utama
        // =========================
        $tmpDir = storage_path('app/temp');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0775, true);

        $mainPdfPath = $tmpDir . '/laporan_al_main_' . $asesmen->code . '_' . $user->id . '.pdf';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // ========== COVER ==========
        $pdf->AddPage();

        $univName = $asesmen->studyProgram?->university?->name ?? '-';
        $prodiName = $asesmen->studyProgram?->full_name ?? $asesmen->studyProgram?->name ?? '-';
        $level = $asesmen->studyProgram?->degreeLevel?->name ?? $asesmen->studyProgram?->degreeLevel?->code ?? '-';

        $periode = '-';
        if ($asesmen->tanggal_mulai && $asesmen->tanggal_selesai) {
            $periode = $asesmen->tanggal_mulai->locale('id')->translatedFormat('d M Y') . ' s/d ' . $asesmen->tanggal_selesai->locale('id')->translatedFormat('d M Y');
        }

        $asesorLines = '';
        foreach ($asesors as $i => $a) {
            $asesorLines .= ($i + 1) . '. ' . ($a->user?->name ?? '-') . "<br>";
        }
        if ($asesorLines === '') $asesorLines = '-';

        // desain cover yang “rapi”
        $coverHtml = '
    <div style="text-align:center;">
        <div style="font-size:16px; font-weight:bold;">LAPORAN HASIL ASESMENT LAPANGAN</div>
        <div style="font-size:12px; margin-top:4px;">(AL)</div>
        <div style="margin-top:12px; font-size:11px;">Kode Asesmen: <b>' . e($asesmen->code) . '</b></div>
        <hr style="margin-top:10px;">
    </div>

    <table cellpadding="6" cellspacing="0" style="width:100%; font-size:11px;">
        <tr>
            <td style="width:30%;"><b>Perguruan Tinggi</b></td>
            <td style="width:70%;">' . e($univName) . '</td>
        </tr>
        <tr>
            <td><b>Program Studi</b></td>
            <td>' . e($prodiName) . '</td>
        </tr>
        <tr>
            <td><b>Jenjang</b></td>
            <td>' . e($level) . '</td>
        </tr>
        <tr>
            <td><b>Panel / Kode Panel</b></td>
            <td>' . e($asesmen->kode_panel ?? '-') . '</td>
        </tr>
        <tr>
            <td><b>Periode Asesmen</b></td>
            <td>' . e($periode) . '</td>
        </tr>
        <tr>
            <td valign="top"><b>Tim Asesor</b></td>
            <td>' . $asesorLines . '</td>
        </tr>
    </table>

    <div style="margin-top:18px; font-size:10px; color:#555;">
        Dokumen ini dihasilkan oleh sistem dan merupakan bagian dari proses asesmen lapangan.
    </div>

    <div style="position: absolute; bottom: 35px; left: 15px; right: 15px; font-size:11px;">
        <table style="width:100%;" cellpadding="6">
            <tr>
                <td style="width:50%; text-align:left;">
                    <b>Dibuat pada:</b><br>' . now()->locale('id')->translatedFormat('d M Y H:i') . '
                </td>
                <td style="width:50%; text-align:right;">
                    <b>Asesor penyusun:</b><br>' . e($user->name) . '
                </td>
            </tr>
        </table>
    </div>
    ';

        $pdf->writeHTML($coverHtml, true, false, true, false, '');

        // ========== HALAMAN PENILAIAN ==========
        $pdf->AddPage();

        $pdf->writeHTML('<h3 style="margin:0;">Rekap Penilaian Elemen (AL)</h3>
    <div style="font-size:10px; color:#555; margin-top:2px;">Asesor: <b>' . e($user->name) . '</b></div>
    <hr>', true, false, true, false, '');

        // tabel penilaian (basic, aman di TCPDF)
        $table = '<table border="1" cellpadding="4" cellspacing="0" style="width:100%; font-size:9px;">
        <thead>
            <tr style="font-weight:bold; background-color:#f2f2f2;">
                <th style="width:18%;">Kriteria</th>
                <th style="width:10%;">Kode</th>
                <th style="width:42%;">Pernyataan Elemen</th>
                <th style="width:12%;">Skor</th>
                <th style="width:18%;">Komentar</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($rows as $r) {
            $table .= '<tr>
            <td>' . e($r['kriteria']) . '</td>
            <td>' . e($r['kode_elemen']) . '</td>
            <td>' . e($r['pernyataan']) . '</td>
            <td>' . e($r['skor']) . '</td>
            <td>' . e($r['komentar']) . '</td>
        </tr>';
        }

        $table .= '</tbody></table>';

        $pdf->writeHTML($table, true, false, true, false, '');

        $pdf->Output($mainPdfPath, 'F');

        // =========================
        // 2) Ambil berita acara (multi-file) aktif
        // =========================
        $beritaAcaraDocs = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara_al_banding')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // =========================
        // 3) MERGE: main + berita acara via FPDI
        // =========================
        $merger = new Fpdi('P', 'mm', 'A4', true, 'UTF-8');
        $merger->setPrintHeader(false);
        $merger->setPrintFooter(false);
        $merger->SetAutoPageBreak(true, 15);

        $sources = [];

        $sources[] = $mainPdfPath;

        foreach ($beritaAcaraDocs as $doc) {
            $abs = storage_path('app/' . $doc->stored_path);
            if (is_file($abs)) $sources[] = $abs;
        }

        foreach ($sources as $src) {
            $pageCount = $merger->setSourceFile($src);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplId = $merger->importPage($pageNo);
                $size = $merger->getTemplateSize($tplId);
                $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $merger->useTemplate($tplId);
            }
        }

        $filename = 'Laporan_Asesmen_Lapangan_' . $asesmen->code . '.pdf';

        // output stream download
        return response($merger->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Append/merge all pages of an existing PDF to the end of current FPDI(TCPDF) document.
     */
    private function appendPdf(Fpdi $pdf, string $filePath): void
    {
        $pageCount = $pdf->setSourceFile($filePath);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);

            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);
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
            ->where('jenis_asesmen', 'al_banding')
            ->firstOrFail();

        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . ($user->role_selected) . ' tidak diizinkan membuka halaman ini.');
        }

        $this->updateStatusAL($assignment);
        $asesmen = $assignment->asesmen;

        // ✅ Calculate progress untuk AL
        $progress = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];

        $statusPekerjaan = $assignment->status_pekerjaan ?? 'not_started';
        $isSubmittedOnly = $statusPekerjaan === 'submitted';
        $isApproved = $statusPekerjaan === 'approved';
        $isComplete = $progress['percentage'] == 100;

        // ✅ CEK UPLOADER PERTAMA (from import log)
        $firstUpload = PenilaianImportLog::where('id_asesmen', $idAsesmen)
            ->where('status', 'completed') // Hanya yang berhasil
            ->with('asesor')
            ->orderBy('created_at', 'asc')
            ->first();

        // ✅ Cek apakah user saat ini adalah uploader pertama
        $currentUserId = Auth::id();
        $isUploader = $firstUpload && $firstUpload->id_asesor == $currentUserId;

        // ✅ User bisa upload jika: belum ada upload ATAU dia adalah uploader pertama
        // $canUpload = !$firstUpload || $isUploader;
        $canUpload = true;

        // ✅ Get team asesor AL
        $asesorTeam = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al_banding')
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->with('user')
            ->orderBy('urutan_asesor')
            ->get();

        return view('asesmen.banding.al-banding.berkas.upload-excel', compact(
            'asesmen',
            'assignment',
            'progress',
            'statusPekerjaan',
            'isSubmittedOnly',
            'isApproved',
            'isComplete',
            'firstUpload',      // ✅ Tambahkan
            'canUpload',        // ✅ Tambahkan
            'isUploader',       // ✅ Tambahkan
            'asesorTeam'        // ✅ Tambahkan
        ));
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

            // ✅ CEK: Apakah sudah ada yang upload sebelumnya
            $existingUpload = PenilaianImportLog::where('id_asesmen', $idAsesmen)
                ->where('status', 'completed')
                ->first();

            $currentUserId = Auth::id();

            // ✅ VALIDASI: Hanya uploader pertama yang bisa upload ulang
            // if ($existingUpload && $existingUpload->id_asesor != $currentUserId) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'File Excel sudah diupload oleh asesor lain. Hanya asesor yang pertama mengupload yang dapat mengupload ulang.',
            //     ], 403);
            // }

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
            ImportPenilaianExcelJob::dispatch(PenilaianElemenAlBanding::class, $filePath, $asesmen->id, $user->id, $importLog->id);

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
}
