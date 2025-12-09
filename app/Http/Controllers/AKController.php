<?php

namespace App\Http\Controllers;

use App\Models\Asesmen;
use App\Models\Kriteria;
use App\Models\Indikator;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\AsesmenUserRole;
use App\Models\PenilaianElemen;
use App\Models\PenilaianImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ImportPenilaianExcelJob;
use App\Services\PenilaianExcelService;
use Illuminate\Support\Facades\Storage;

class AKController extends Controller
{
    /**
     * Display a listing of asesmens (berkas) for current user
     */
    public function berkas()
    {
        $user = Auth::user();

        // Get asesmens where user is assigned
        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id);
        })->with(['userRoles' => function ($query) use ($user) {
            $query->where('id_user', $user->id)->with('role');
        }])->latest()->paginate(10);

        // Calculate progress for each asesmen
        foreach ($asesmens as $asesmen) {
            $asesmen->progress = $this->calculateProgress($asesmen->id, $user->id);
        }

        return view('asesmen.ak.berkas.index', compact('asesmens'));
    }

    /**
     * Show detail asesmen with accordion per elemen
     */
    public function showBerkas($id)
    {
        $user = Auth::user();

        // Check if user has access to this asesmen
        $assignment = AsesmenUserRole::where('id_asesmen', $id)
            ->where('id_user', Auth::user()->id)
            ->firstOrFail();

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
            'elemenStandar.penilaian' => function ($query) use ($asesmen, $user) {
                $query->where('id_asesmen', $asesmen->id)
                    ->where('id_user', $user->id);
            }
        ])->orderBy('id_kriteria')->get();

        // Calculate progress
        $progress = $this->calculateProgress($asesmen->id, $user->id);

        return view('asesmen.ak.berkas.show', compact('asesmen', 'kriterias', 'progress'));
    }

    /**
     * Save penilaian for specific indikator (AJAX)
     * UPDATED: Support skor 0-4
     */
    public function simpanNilai(Request $request, $asesmenId)
    {
        $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id_elemen',
            'skor' => 'required|integer|min:0|max:4', // UPDATED: Support 0-4
            'komentar' => 'nullable|string|max:5000',
        ]);

        try {
            $user = Auth::user();

            // Verify user has access
            $hasAccess = AsesmenUserRole::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
                ->exists();

            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke asesmen ini'
                ], 403);
            }

            // Update or create penilaian
            $penilaian = PenilaianElemen::updateOrCreate(
                [
                    'id_asesmen' => $asesmenId,
                    'id_user' => $user->id,
                    'id_elemen' => $request->id_elemen,
                ],
                [
                    'skor' => $request->skor,
                    'komentar' => $request->komentar,
                    'status' => 'draft',
                ]
            );

            // Calculate new progress
            $progress = $this->calculateProgress($asesmenId, $user->id);

            // Get skor label and class for response
            $skorInfo = $this->getSkorInfo($request->skor);

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
     * Get skor information (label, color, class)
     */
    private function getSkorInfo($skor)
    {
        $skorMapping = [
            0 => [
                'label' => 'Tidak Memenuhi (Not Met)',
                'color' => '#f44336',
                'class' => 'danger',
            ],
            1 => [
                'label' => 'Tidak Memenuhi (Not Met)',
                'color' => '#ff9800',
                'class' => 'warning',
            ],
            2 => [
                'label' => 'Lemah (Weakness/Cause of Concern)',
                'color' => '#ffeb3b',
                'class' => 'warning',
            ],
            3 => [
                'label' => 'Memenuhi (Met)',
                'color' => '#8bc34a',
                'class' => 'success',
            ],
            4 => [
                'label' => 'Pelampauan Standar (Exceeding Standard)',
                'color' => '#4caf50',
                'class' => 'success',
            ],
        ];

        return $skorMapping[$skor] ?? [
            'label' => 'Unknown',
            'color' => '#9e9e9e',
            'class' => 'secondary',
        ];
    }

    /**
     * Calculate progress percentage for an asesmen
     */
    private function calculateProgress($asesmenId, $userId)
    {
        // Total indikators
        $totalElemens = ElemenStandar::count();

        // Completed elemens (has penilaian)
        $completedElemens = PenilaianElemen::where('id_asesmen', $asesmenId)
            ->where('id_user', $userId)
            ->whereNotNull('skor')
            ->count();

        $percentage = $totalElemens > 0
            ? round(($completedElemens / $totalElemens) * 100, 1)
            : 0;

        return [
            'total' => $totalElemens,
            'completed' => $completedElemens,
            'percentage' => $percentage,
            'remaining' => $totalElemens - $completedElemens,
        ];
    }

    /**
     * Get heatmap data for visualization (NEW)
     */
    public function getHeatmapData($asesmenId)
    {
        $user = Auth::user();

        // Verify access
        $hasAccess = AsesmenUserRole::where('id_asesmen', $asesmenId)
            ->where('id_user', $user->id)
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get all penilaian with kriteria, elemen info
        $heatmapData = PenilaianElemen::where('id_asesmen', $asesmenId)
            ->where('id_user', $user->id)
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
                    'skor_info' => $this->getSkorInfo($penilaian->skor),
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
    public function submitPenilaian(Request $request, $asesmenId)
    {
        try {
            $user = Auth::user();

            // Check access
            $assignment = AsesmenUserRole::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
                ->where('status_penawaran', 'accepted')
                ->firstOrFail();

            // Check if all elemen have been assessed
            $totalElemen = ElemenStandar::count();
            $assessedElemen = PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
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
            PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
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
                'message' => 'Penilaian berhasil di-submit! Menunggu validasi dari validator.',
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
    public function unsubmitPenilaian($asesmenId)
    {
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
                ->where('status_pekerjaan', 'submitted')
                ->firstOrFail();

            // Check if already validated
            $hasValidated = PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
                ->where('status_validasi', '!=', 'not_validated')
                ->exists();

            if ($hasValidated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian sudah divalidasi, tidak bisa dibatalkan.',
                ], 422);
            }

            DB::beginTransaction();

            // Update back to draft
            PenilaianElemen::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
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
     * ADD THIS METHOD TO: App\Http\Controllers\AKController
     * Location: After unsubmitPenilaian() method
     */

    /**
     * Reset/Hapus semua penilaian untuk asesmen tertentu
     *
     * @param int $asesmenId
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetAllPenilaian($asesmenId)
    {
        try {
            $user = Auth::user();

            // Verify access
            $assignment = AsesmenUserRole::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
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
                $totalDeleted = PenilaianElemen::where('id_asesmen', $asesmenId)
                    ->where('id_user', $user->id)
                    ->count();

                // Delete all penilaian for this user and asesmen
                PenilaianElemen::where('id_asesmen', $asesmenId)
                    ->where('id_user', $user->id)
                    ->delete();

                // Update assignment status back to not_started
                $assignment->update([
                    'status_pekerjaan' => 'not_started',
                    'submitted_at' => null,
                ]);

                DB::commit();

                // Calculate new progress (should be 0)
                $progress = $this->calculateProgress($asesmenId, $user->id);

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$totalDeleted} penilaian. Semua penilaian telah direset.",
                    'deleted_count' => $totalDeleted,
                    'progress' => $progress,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Reset penilaian failed: ' . $e->getMessage(), [
                'asesmen_id' => $asesmenId,
                'user_id' => Auth::id(),
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
    public function downloadTemplate($asesmenId)
    {
        try {
            $user = Auth::user();

            // Verify access
            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($asesmenId);

            $excelService = new PenilaianExcelService();
            $filePath = $excelService->generateTemplate($asesmen);

            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal download template: ' . $e->getMessage());
        }
    }

    /**
     * Export penilaian ke Excel (dengan data)
     */
    public function exportExcel($asesmenId)
    {
        try {
            $user = Auth::user();

            // Verify access
            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($asesmenId);

            $excelService = new PenilaianExcelService();
            $filePath = $excelService->generateWithData($asesmen, $user->id);

            return response()->download($filePath, basename($filePath))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()->back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }

    /**
     * Import penilaian dari Excel (using Queue)
     */
    public function importExcel(Request $request, $asesmenId)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $user = Auth::user();

            // Verify access
            $asesmen = Asesmen::whereHas('userRoles', function ($query) use ($user) {
                $query->where('id_user', $user->id);
            })->findOrFail($asesmenId);

            // Store file temporarily
            $file = $request->file('file');
            $filename = 'import_' . $asesmen->code . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('temp/imports', $filename);

            // Create import log
            $importLog = PenilaianImportLog::create([
                'id_asesmen' => $asesmen->id,
                'id_user' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'status' => 'queued',
            ]);

            // Dispatch job
            ImportPenilaianExcelJob::dispatch($filePath, $asesmen->id, $user->id, $importLog->id);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload. Import sedang diproses di background.',
                'import_log_id' => $importLog->id,
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal import Excel: ' . $e->getMessage(),
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

            $importLog = PenilaianImportLog::where('id_user', $user->id)
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
                'message' => 'Import log tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Get import history
     */
    public function importHistory($asesmenId)
    {
        try {
            $user = Auth::user();

            $logs = PenilaianImportLog::where('id_asesmen', $asesmenId)
                ->where('id_user', $user->id)
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
                'message' => 'Gagal memuat riwayat import',
            ], 500);
        }
    }
}
