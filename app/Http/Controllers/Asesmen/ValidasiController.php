<?php

namespace App\Http\Controllers\Asesmen;

use App\Models\User;
use App\Models\Asesmen;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use App\Models\PenilaianElemenAk;
use App\Models\AsesmenKecukupan;
use App\Models\JenjangPenilaian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\ValidasiExcelService;

class ValidasiController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Get validator assignments
        $assignments = AsesmenUserRole::with([
            'asesmen.studyProgram.university',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->whereHas('role', fn($query) => $query->where('name', 'asesor'))
                    ->where('jenis_asesmen', 'ak')
                    ->where('status_penawaran', 'accepted')
                    ->with(['user', 'role']);
            }
        ])
            ->where('id_user', $userId)
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->where('jenis_asesmen', 'ak')
            ->where('status_penawaran', 'accepted')
            ->get();

        $validationProgressMap = PenilaianElemenAk::select(
            'id_asesmen',
            'id_asesor',
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status_validasi IN ('validated','validated_diff','approved') THEN 1 ELSE 0 END) as validated"),
            DB::raw("SUM(CASE WHEN status_validasi = 'revision_required' THEN 1 ELSE 0 END) as revision"),
            DB::raw("SUM(CASE WHEN status_validasi = 'pending' THEN 1 ELSE 0 END) as pending")
        )
            ->groupBy('id_asesmen', 'id_asesor')
            ->get()
            ->keyBy(fn($row) => $row->id_asesmen . '_' . $row->id_asesor);

        $acceptedAsesorCounts = AsesmenUserRole::select(
            'id_asesmen',
            DB::raw('COUNT(*) as total')
        )
            ->where('jenis_asesmen', 'ak')
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->groupBy('id_asesmen')
            ->pluck('total', 'id_asesmen');

        // Separate needs validation and validated
        $needsValidation = [];
        $validated = [];

        // Statistics
        $totalAsesorSubmitted = 0;
        $totalAsesorPending = 0;
        $asesorsPendingList = [];

        foreach ($assignments as $assignment) {
            $asesmen = $assignment->asesmen;
            $asesors = $asesmen->asesmenUserRoles;

            // Get asesors who submitted
            $asesorsSubmitted = $asesors->filter(function ($asesor) {
                return $asesor->status_pekerjaan === 'submitted' && !is_null($asesor->submitted_at);
            });

            // Get asesors who haven't submitted
            $asesorsPending = $asesors->filter(function ($asesor) {
                return in_array($asesor->status_pekerjaan, ['not_started', 'in_progress', 'revision_required'])
                    || is_null($asesor->submitted_at);
            });

            // Calculate validation progress for each submitted asesor
            foreach ($asesorsSubmitted as $asesor) {
                $key = $asesmen->id . '_' . $asesor->id_user;
                $progress = $validationProgressMap[$key] ?? null;

                $asesor->validation_progress = [
                    'total'      => $progress->total ?? 0,
                    'validated'  => $progress->validated ?? 0,
                    'revision'   => $progress->revision ?? 0,
                    'pending'    => $progress->pending ?? 0,
                    'percentage' => ($progress && $progress->total > 0)
                        ? round(($progress->validated / $progress->total) * 100, 1)
                        : 0,
                ];
            }

            // Count for statistics
            $totalAsesorSubmitted += $asesorsSubmitted->count();
            $totalAsesorPending += $asesorsPending->count();

            // Add to pending list with details
            foreach ($asesorsPending as $pendingAsesor) {
                $asesorsPendingList[] = [
                    'asesmen'       => $asesmen->name,
                    'asesor'        => $pendingAsesor->user,
                    'status'        => $pendingAsesor->status_pekerjaan,
                    'jenis_asesmen' => $pendingAsesor->jenis_asesmen,
                ];
            }

            // Categorize asesmen
            if ($assignment->status_pekerjaan === 'approved') {
                $validated[] = [
                    'id_asesmen' => $asesmen->id,
                    'asesmen'    => $asesmen,
                    'asesors'    => $asesorsSubmitted,
                    'assignment' => $assignment,
                ];
            } elseif ($asesorsSubmitted->isNotEmpty()) {
                $needsValidation[] = [
                    'asesmen'                => $asesmen,
                    'asesors'                => $asesorsSubmitted,
                    'asesors_pending'        => $asesorsPending,
                    'assignment'             => $assignment,
                    'total_accepted_asesors' => $acceptedAsesorCounts[$asesmen->id] ?? 0,
                ];
            }
        }

        // Prepare statistics
        $stats = [
            'total_asesmen'         => $assignments->count(),
            'total_needs_validation' => count($needsValidation),
            'total_validated'       => count($validated),
            'total_asesor_submitted' => $totalAsesorSubmitted,
            'total_asesor_pending'  => $totalAsesorPending,
        ];

        return view('asesmen.ak.validasi.index', compact(
            'needsValidation',
            'validated',
            'stats',
            'asesorsPendingList'
        ));
    }

    /**
     * Show validator dashboard with dynamic asesor support
     */
    public function asesor($idAsesmen, $jenisAsesmen = 'ak')
    {
        $user = Auth::user();

        // Check validator access
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->first();

        if (!$assignment) {
            abort(403, 'Mohon maaf role Anda sebagai ' . ($user->role_selected) . ' tidak diizinkan membuka halaman ini. Silahkan pindah ke role lain');
        }

        // Check if validator has accepted the assignment
        if ($assignment->status_penawaran !== 'accepted') {
            abort(403, 'Anda belum menerima penawaran sebagai validator untuk asesmen ini.');
        }

        $asesmen = $assignment->asesmen;

        // Get AsesmenKecukupan or AsesmenLapangan
        if ($jenisAsesmen === 'ak') {
            $asesmenDetail = AsesmenKecukupan::where('id_asesmen', $idAsesmen)->firstOrFail();
        } else {
            $asesmenDetail = AsesmenLapangan::where('id_asesmen', $idAsesmen)->firstOrFail();
        }

        // Get ALL accepted asesors (not just 2)
        $asesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', $jenisAsesmen)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->with(['user', 'role'])
            ->orderBy('urutan_asesor')
            ->get();

        // ✅ CHECK: Minimum 2 asesors
        if ($asesors->count() < 2) {
            abort(422, 'Minimal 2 asesor harus menerima penawaran untuk memulai validasi.');
        }

        // ✅ UPDATED CHECK: Only block if asesors have NEVER submitted
        // Exclude asesors with 'revision_required' status (they already submitted before)
        $asesorsNeverSubmitted = $asesors->filter(function ($asesor) {
            // Only count as "not submitted" if:
            // 1. Status is not_started or in_progress
            // 2. AND never submitted (submitted_at is null)
            // 3. OR status is revision_required BUT no submitted_at (edge case)

            // If status is 'revision_required', they already submitted before
            if ($asesor->status_pekerjaan === 'revision_required') {
                return false; // Don't block - they can revise while validator reviews
            }

            // Block only if truly never submitted
            return in_array($asesor->status_pekerjaan, ['not_started', 'in_progress'])
                && is_null($asesor->submitted_at);
        });

        if ($asesorsNeverSubmitted->count() > 0) {
            $asesorNames = $asesorsNeverSubmitted->pluck('user.name')->join(', ');
            abort(422, "Validasi belum dapat dilakukan. Menunggu {$asesorsNeverSubmitted->count()} asesor untuk submit penilaian pertama kali: {$asesorNames}");
        }

        // Calculate progress for each asesor
        $totalElemen = ElemenStandar::count();
        // Ambil semua penilaian semua asesor di asesmen ini
        $penilaianAll = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
            ->whereIn('id_asesor', $asesors->pluck('id_user'))
            ->select('id_asesor', 'status_validasi', DB::raw('COUNT(*) as count'))
            ->groupBy('id_asesor', 'status_validasi')
            ->get();

        // Mapping penilaian per asesor
        $asesorProgress = [];

        foreach ($asesors as $asesor) {
            $asesorId = $asesor->id_user;

            $items = $penilaianAll->where('id_asesor', $asesorId);

            $completed = $items->sum('count'); // semua yang punya skor
            $validated = ($items->whereIn('status_validasi', ['validated', 'validated_diff', 'approved'])->sum('count'));
            $pending = $items->where('status_validasi', 'pending')->sum('count');
            $revision = $items->where('status_validasi', 'revision_required')->sum('count');

            $completionPercentage = $totalElemen > 0 ? round(($completed / $totalElemen) * 100, 1) : 0;
            $validationPercentage = $totalElemen > 0 ? round(($validated / $totalElemen) * 100, 1) : 0;

            $asesorProgress[$asesorId] = [
                'total' => $totalElemen,
                'completed' => $completed,
                'validated' => $validated,
                'pending_validation' => $pending,
                'revision_required' => $revision,
                'completion_percentage' => $completionPercentage,
                'validation_percentage' => $validationPercentage,
                'status' => $this->getProgressStatus($completed, $validated, $totalElemen, $revision),
            ];
        }

        // Get all kriteria with elemen and penilaian from ALL asesors
        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
            'elemenStandar.penilaianElemenAk' => function ($query) use ($idAsesmen, $asesors) {
                $query->where('id_asesmen', $idAsesmen)
                    ->whereIn('id_asesor', $asesors->pluck('id_user'));
            }
        ])->get();

        // Calculate validation stats
        $validatedCount = 0;

        foreach ($kriterias as $kriteria) {
            foreach ($kriteria->elemenStandar as $elemen) {
                $penilaians = $elemen->penilaianElemenAk;

                // Cek jika semua asesor sudah dinilai DAN sudah divalidasi
                if ($penilaians->count() === $asesors->count()) {
                    $allValidated = $penilaians->every(function ($p) {
                        return in_array($p->status_validasi, ['validated', 'validated_diff', 'approved']);
                    });

                    if ($allValidated) {
                        $validatedCount++;
                    }
                }
            }
        }

        $validationPercentage = $totalElemen > 0
            ? round(($validatedCount / $totalElemen) * 100, 1)
            : 0;

        // Check if all validated
        $allValidated = $validatedCount === $totalElemen;

        // Check if approved
        $isApproved = $assignment->status_pekerjaan === 'approved';
        $asesorIds = $asesors->pluck('id_user');
        $asesorData = $asesors->map(function ($asesor) {
            return [
                'id' => $asesor->id_user,
                'name' => $asesor->user->name,
                'urutan' => $asesor->urutan_asesor,
            ];
        });
        $jenjangs = JenjangPenilaian::all();

        return view('asesmen.ak.validasi.asesor', compact(
            'asesmen',
            'asesmenDetail',
            'jenisAsesmen',
            'asesors',
            'asesorProgress',
            'asesorIds',
            'asesorData',
            'kriterias',
            'totalElemen',
            'validatedCount',
            'validationPercentage',
            'allValidated',
            'isApproved',
            'jenjangs'
        ));
    }

    /**
     * ============================================
     * GET PROGRESS STATUS
     * ============================================
     */
    private function getProgressStatus($completed, $validated, $total, $revision)
    {
        if ($revision > 0) {
            return [
                'label' => 'Perlu Revisi',
                'class' => 'danger',
                'icon' => 'exclamation-triangle'
            ];
        }

        if ($validated === $total) {
            return [
                'label' => 'Selesai Divalidasi',
                'class' => 'success',
                'icon' => 'check-circle'
            ];
        }

        if ($completed === $total) {
            return [
                'label' => 'Menunggu Validasi',
                'class' => 'warning',
                'icon' => 'clock'
            ];
        }

        if ($completed > 0) {
            return [
                'label' => 'Sedang Dikerjakan',
                'class' => 'info',
                'icon' => 'arrow-repeat'
            ];
        }

        return [
            'label' => 'Belum Mulai',
            'class' => 'secondary',
            'icon' => 'dash-circle'
        ];
    }

    public function getValidasiDetail($idAsesmen, $elemenId)
    {
        try {
            $elemen = ElemenStandar::with('kriteria')->findOrFail($elemenId);

            // Get validasi data
            $validasi = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_elemen', $elemenId)
                ->whereIn('status_validasi', ['validated', 'validated_diff', 'revision_required'])
                ->with('validator')
                ->first();

            if (!$validasi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data validasi tidak ditemukan'
                ], 404);
            }

            // Get penilaian dari semua asesor
            $penilaianAsesor = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->where('id_elemen', $elemenId)
                ->with('asesor')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'elemen' => $elemen,
                    'validasi' => $validasi,
                    'penilaianAsesor' => $penilaianAsesor,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error get validasi detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail penilaian untuk modal validasi (UPDATED for dynamic asesors)
     */
    public function getElemenDetail(Request $request, Asesmen $asesmen, ElemenStandar $elemen)
    {
        // Get ALL penilaian for this elemen
        $penilaianElemen = PenilaianElemenAk::where('id_asesmen', $asesmen->id)
            ->where('id_elemen', $elemen->id)
            ->with('asesor')
            ->get();

        $elemen = ElemenStandar::whereId($elemen->id)
            ->with(['kriteria', 'indikator'])
            ->firstOrFail();

        // Build penilaian data array
        $penilaianData = $penilaianElemen->map(function ($penilaian) {
            return [
                'asesor' => $penilaian->asesor,
                'penilaian' => $penilaian,
            ];
        });

        // Check if there's any difference in scores
        $scores = $penilaianElemen->pluck('skor')->filter()->unique();
        $hasDifference = $scores->count() > 1;

        return response()->json([
            'success' => true,
            'data' => [
                'elemen' => $elemen,
                'penilaian_data' => $penilaianData,
                'hasDifference' => $hasDifference,
                'total_asesors' => $penilaianElemen->count(),
            ]
        ]);
    }

    /**
     * Validasi satu elemen
     */
    public function validateElemen(Request $request, $idAsesmen, $elemenId)
    {
        $request->validate([
            'status' => 'required|in:validated,revision_required,validated_diff',
            'catatan_validator' => 'nullable|string',
            'skor_final' => 'nullable|integer|min:0|max:4|required_if:status,validated',
            'id_asesors' => 'nullable|array|required_if:status,revision_required',
            'id_asesors.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $statusValidasi = $request->status;
            if (in_array($request->status, ['validated', 'validated_diff'])) {
                // Validate ALL asesors for this elemen
                PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                    ->where('id_elemen', $elemenId)
                    ->update([
                        'status_validasi' => $statusValidasi,
                        'skor_final' => $request->skor_final,
                        'catatan_validator' => $request->catatan_validator,
                        'validated_at' => now(),
                        'validated_by' => Auth::id(),
                    ]);
            } else {
                // Revision required - only for selected asesors
                if (!$request->has('id_asesors') || !is_array($request->id_asesors) || count($request->id_asesors) === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pilih minimal 1 asesor untuk revisi'
                    ], 422);
                }

                // Update selected asesors
                PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                    ->where('id_elemen', $elemenId)
                    ->whereIn('id_asesor', $request->id_asesors)
                    ->update([
                        'preferensi_skor' => $request->skor_final,
                        'status_validasi' => 'revision_required',
                        'catatan_validator' => $request->catatan_validator,
                        'validated_at' => now(),
                        'validated_by' => Auth::id(),
                    ]);

                // Update asesor status pekerjaan
                AsesmenUserRole::where('id_asesmen', $idAsesmen)
                    ->whereIn('id_user', $request->id_asesors)
                    ->where('jenis_asesmen', 'ak')
                    ->update([
                        'status_pekerjaan' => 'revision_required',
                        'submitted_at' => null,
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' =>
                $statusValidasi === 'validated'
                    ? 'Penilaian berhasil divalidasi'
                    : ($statusValidasi === 'validated_diff'
                        ? 'Penilaian berhasil divalidasi dengan perbedaan nilai'
                        : 'Permintaan revisi berhasil dikirim ke asesor terpilih.'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error validate elemen: ' . $e->getMessage());
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan validasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validasi otomatis semua elemen yang nilainya sama (UPDATED for dynamic asesors)
     */
    public function validateAllAgreed($idAsesmen)
    {
        DB::beginTransaction();
        try {
            $elemenIds = ElemenStandar::pluck('id');
            $validatedCount = 0;

            // Get total number of accepted asesors
            $totalAsesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                ->where('jenis_asesmen', 'ak')
                ->where('status_penawaran', 'accepted')
                ->count();

            foreach ($elemenIds as $elemenId) {
                // Get ALL penilaian for this elemen
                $penilaianList = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                    ->where('id_elemen', $elemenId)
                    ->whereNotNull('skor')
                    ->get();

                // Must have penilaian from ALL asesors
                if ($penilaianList->count() !== $totalAsesors) {
                    continue;
                }

                // Check if ALL scores are the same
                $uniqueScores = $penilaianList->pluck('skor')->unique();

                if ($uniqueScores->count() === 1) {
                    // All asesors agree - auto validate
                    $agreedScore = $uniqueScores->first();

                    PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                        ->where('id_elemen', $elemenId)
                        ->update([
                            'status_validasi' => 'validated',
                            'skor_final' => $agreedScore,
                            'catatan_validator' => null,
                            // 'catatan_validator' => 'Auto-validated: Semua asesor memberikan nilai yang sama',
                            // 'catatan_validator' => 'Penilaian AK telah divalidasi, semua asesor telah memberikan penilaian yang sama dalam rentang yang dapat diterima',
                            'validated_at' => now(),
                            'validated_by' => Auth::id(),
                        ]);

                    $validatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memvalidasi {$validatedCount} elemen yang nilainya sama dari semua asesor",
                'validated_count' => $validatedCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error validate all agreed: ' . $e->getMessage());
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan validasi otomatis: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve semua penilaian (final step)
     */
    public function approveAllPenilaian($idAsesmen)
    {
        DB::beginTransaction();
        try {
            $asesmen = Asesmen::findOrFail($idAsesmen);
            // Check if all elemen are validated
            $totalElemen = ElemenStandar::count();

            // Get count of fully validated elemen (all asesors validated)
            $totalAsesors = AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                ->where('jenis_asesmen', 'ak')
                ->where('status_penawaran', 'accepted')
                ->count();

            $validatedElemen = 0;
            $elemenIds = ElemenStandar::pluck('id');

            foreach ($elemenIds as $elemenId) {
                $validatedCount = PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                    ->where('id_elemen', $elemenId)
                    ->whereIn('status_validasi', ['validated', 'validated_diff'])
                    ->count();

                if ($validatedCount === $totalAsesors) {
                    $validatedElemen++;
                }
            }

            if ($validatedElemen < $totalElemen) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak semua elemen telah divalidasi. ({$validatedElemen}/{$totalElemen}). Harap lengkapi validasi terlebih dahulu.",
                ], 422);
            }

            // Update validator assignment status
            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'ak')
                ->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                ]);

            // Update all asesor assignments to approved
            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                ->where('jenis_asesmen', 'ak')
                ->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                ]);

            // Lock all penilaian
            PenilaianElemenAk::where('id_asesmen', $idAsesmen)
                ->update([
                    'is_locked' => true,
                ]);
            if ($asesmen->pengajuan)
                $asesmen->pengajuan->update(['tanggal_validasi_ak' => now()]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua penilaian telah disetujui dan dikunci',
            ]);
        } catch (\Exception $e) {
            Log::error('Error approve all: ' . $e->getMessage());
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export perbandingan penilaian ke Excel
     */
    public function exportComparison(Request $request, Asesmen $asesmen)
    {
        $asesors = AsesmenUserRole::where('id_asesmen', $asesmen->id)
            ->where('jenis_asesmen', 'ak')
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->with(['user', 'role'])
            ->orderBy('urutan_asesor')
            ->get();
        $mode = $request->query('mode', 'split'); // merged/split | default: split

        [$tempFile, $filename] = ValidasiExcelService::generateTemplate($asesmen, $asesors, 'ak', $mode);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
