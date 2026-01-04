<?php

namespace App\Http\Controllers\Asesmen;

use App\Models\Role;
use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\Indikator;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\AsesmenUserRole;
use App\Models\PenilaianElemenAl;
use App\Models\JenjangPenilaian;
use App\Models\PenilaianImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ImportPenilaianExcelJob;
use App\Services\PenilaianExcelService;

class ALController extends Controller
{
    /**
     * Display a listing of asesmens (berkas) for current user
     */
    public function berkas()
    {
        $user = Auth::user();

        // Get asesmens where user is assigned
        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id)->where('jenis_asesmen', 'al')->where('id_role', 3);
        })->with(['userRoles' => function ($query) use ($user) {
            $query->where('id_user', $user->id)->where('jenis_asesmen', 'al')->where('id_role', 3)->with('role');
        }])->latest()->paginate(10);

        // Ambil semua progress sekaligus
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

        return view('asesmen.al.berkas.index', compact('asesmens', 'statusPekerjaan'));
    }

    /**
     * Show detail asesmen with accordion per elemen
     */
    public function showBerkas($idAsesmen)
    {
        $user = Auth::user();
        // Check if user has access to this asesmen
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->firstOrFail();
        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . ($user->role_selected) . ' tidak diizinkan membuka halaman ini. Silahkan pindah ke role lain');
        }
        // ✅ AUTO-UPDATE STATUS: not_started → in_progress
        if ($assignment->status_pekerjaan === 'not_started') {
            $assignment->update([
                'status_pekerjaan' => 'in_progress',
                'started_at' => now(), // Opsional: track kapan mulai
            ]);
        }

        $asesmen = $assignment->asesmen;

        // Get all kriteria with elemen and indikator
        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
            'elemenStandar.penilaianElemenAl' => function ($query) use ($asesmen, $user) {
                $query->where('id_asesmen', $asesmen->id)
                    ->where('id_asesor', $user->id);
            }
        ])->get();

        $needsRevisions = PenilaianElemenAl::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->with('elemen.kriteria')
            ->get();
        $jenjangs = JenjangPenilaian::all();
        // Calculate progress
        $progress = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];

        return view('asesmen.al.berkas.show', compact('asesmen', 'kriterias', 'progress', 'jenjangs', 'needsRevisions'));
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
                ->where('jenis_asesmen', 'al')
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini'
                ], 403);
            }

            // Update or create penilaian
            $penilaian = PenilaianElemenAl::updateOrCreate(
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
        $penilaian = PenilaianElemenAl::where('id_asesor', $userId)
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
            ->where('jenis_asesmen', 'al')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get all penilaian with kriteria, elemen info
        $heatmapData = PenilaianElemenAl::where('id_asesmen', $idAsesmen)
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
                ->where('jenis_asesmen', 'al')
                ->where('status_penawaran', 'accepted')
                ->first();

            if (!$assignment)
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada penugasan asesmen',
                ], 500);

            // Check if all elemen have been assessed
            $totalElemen = ElemenStandar::count();
            $assessedElemen = PenilaianElemenAl::where('id_asesmen', $idAsesmen)
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
            PenilaianElemenAl::where('id_asesmen', $idAsesmen)
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
                'message' => 'Penilaian berhasil di-submit! Menunggu validasi oleh DE LAMDEPILAR.',
                'submitted_at' => now()->format('d M Y H:i'),
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
                ->where('jenis_asesmen', 'al')
                ->where('status_pekerjaan', 'submitted')
                ->firstOrFail();

            // ✅ PERBAIKAN: Hanya cek yang benar-benar sudah VALIDATED (final)
            $hasValidated = PenilaianElemenAl::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->exists();

            if ($hasValidated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian sudah divalidasi dan disetujui, tidak bisa dibatalkan.',
                ], 422);
            }

            // ✅ TAMBAHAN: Cek jika sudah approved
            if ($assignment->status_pekerjaan === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian sudah disetujui, tidak bisa dibatalkan.',
                ], 422);
            }

            DB::beginTransaction();

            // Update back to draft
            PenilaianElemenAl::where('id_asesmen', $idAsesmen)
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
     * ADD THIS METHOD TO: App\Http\Controllers\ALController
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
                ->where('jenis_asesmen', 'al')
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
                    'message' => 'Tidak dapat mereset penilaian yang sudah di-submit atau disetujui. Silakan batalkan submit terlebih dahulu.',
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Get count before delete
                $totalDeleted = PenilaianElemenAl::where('id_asesmen', $idAsesmen)
                    ->where('id_asesor', $user->id)
                    ->count();

                // Delete all penilaian for this user and asesmen
                PenilaianElemenAl::where('id_asesmen', $idAsesmen)
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
     * Download template Excel (format kosong)
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

            $excelService = new PenilaianExcelService(PenilaianElemenAl::class);
            $filePath = $excelService->generateTemplate($asesmen, $asesor1, $asesor2);

            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal download template: ' . $e->getMessage());
        }
    }

    /**
     * Export penilaian ke Excel (dengan data)
     */
    public function exportExcel($idAsesmen)
    {
        try {
            $user = Auth::user();

            // Verify access
            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($idAsesmen);

            $excelService = new PenilaianExcelService(PenilaianElemenAl::class);
            $filePath = $excelService->generateWithData($asesmen, $user->id);

            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal download data Excel: ' . $e->getMessage());
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
            ImportPenilaianExcelJob::dispatch(PenilaianElemenAl::class, $filePath, $asesmen->id, $user->id, $importLog->id);

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
                    'success_rate' => $importLog->success_rate,
                    'started_at' => $importLog->started_at?->format('d M Y H:i:s'),
                    'completed_at' => $importLog->completed_at?->format('d M Y H:i:s'),
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
                ->where('jenis_asesmen', 'al')
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
                'elemenStandar.penilaianElemenAl' => function ($q) use ($asesors) {
                    $q->whereIn('id_asesor', $asesors->pluck('id_user'));
                },
                'elemenStandar.penilaianElemenAl.asesor'
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
}
