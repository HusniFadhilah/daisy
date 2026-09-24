<?php

namespace App\Http\Controllers\Asesmen;

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
use App\Models\PenilaianElemenAl;
use App\Models\PenilaianImportLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ImportPenilaianExcelJob;
use App\Models\HasilAkreditasi;
use App\Services\HasilAkreditasiService;
use App\Services\PenilaianExcelService;
use App\Repositories\SyaratAkreditasiRepository;

class ALController extends Controller
{
    public function __construct(
        private readonly HasilAkreditasiService $hasilService,
        private readonly SyaratAkreditasiRepository $syaratRepo,
    ) {}

    /**
     * Display a listing of asesmens (berkas) for current user
     */
    public function berkas()
    {
        $user = Auth::user();

        $asesmens = Asesmen::whereHas('userRoles', function ($query) use ($user) {
            $query->where('id_user', $user->id)
                ->where('jenis_asesmen', 'al')
                ->where('id_role', Role::ID_ROLE_ASESOR);
        })
            ->with([
                // Role user ini sendiri
                'userRoles' => function ($query) use ($user) {
                    $query->where('id_user', $user->id)
                        ->where('jenis_asesmen', 'al')
                        ->where('id_role', Role::ID_ROLE_ASESOR)
                        ->with('role');
                },
                // Semua asesor AL (untuk cek first opener & status tim)
                'allAsesorRolesAl' => function ($query) {
                    $query->where('jenis_asesmen', 'al')
                        ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
                        ->with('user')
                        ->orderBy('updated_at');
                },
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'pengajuan.dokumen' => function ($q) {
                    $q->whereIn('jenis_dokumen', [
                        'surat_tugas_asesor_al',
                        'data_kualitatif',
                        'draft_borang',
                        'borang_final',
                        'data_suplemen',
                        'data_kuantitatif',
                        'kuantitatif',
                    ])->where('is_latest', true)->orderBy('created_at', 'desc');
                },
                // Dokumen asesmen untuk status berita acara, LHA, ringkasan
                // LHA dimuat tanpa filter is_active agar bisa baca status revisi / persetujuan
                'documents' => function ($q) {
                    $q->whereIn('type', [
                        'berita_acara_al',
                        'hasil_akreditasi_confidential',
                    ])->where('is_active', true);
                },
                // LHA diload terpisah agar bisa baca semua field status-nya
                'lhaDocuments' => function ($q) {
                    $q->where('type', 'lha_asesor')
                        ->where('is_active', true)
                        ->latest('updated_at');
                },
                'hasil',
                'penilaianElemenAl:id,id_asesmen,id_asesor,skor',
            ])
            ->latest()
            ->paginate(10);

        $progressAll = $this->calculateProgressBulk(
            $asesmens->pluck('id')->toArray(),
            $user->id
        );

        foreach ($asesmens as $asesmen) {
            $asesmen->progress = $progressAll[$asesmen->id] ?? [
                'total' => 0,
                'completed' => 0,
                'remaining' => 0,
                'percentage' => 0,
            ];

            $assignment = $asesmen->userRoles->first();
            $asesmen->statusInfo = AsesmenUserRole::getStatusInfo($assignment);

            // ── First opener (dari relasi allAsesorRolesAl) ─────────────────
            $pengisiIds = $asesmen->penilaianElemenAl
                ->whereNotNull('skor')
                ->pluck('id_asesor');

            $asesmen->firstOpenerRole = $asesmen->allAsesorRolesAl
                ->filter(fn ($role) => $pengisiIds->contains($role->id_user))
                ->sortBy(fn ($role) => [$role->submitted_at ?? $role->updated_at, $role->id])
                ->first()
                ?? $asesmen->allAsesorRolesAl
                    ->where('status_pekerjaan', '!=', 'not_started')
                    ->sortBy(fn ($role) => [$role->submitted_at ?? $role->updated_at, $role->id])
                    ->first();

            // ── Status penilaian — hanya dari first opener ───────────────────
            // Karena hanya satu asesor (first opener) yang mengisi penilaian,
            // status cukup dilihat dari status_pekerjaan milik first opener saja.
            $asesmen->penilaian_status = match ($asesmen->firstOpenerRole?->status_pekerjaan) {
                'submitted', 'approved', 'validated' => 'selesai',
                'in_progress', 'revision_required'   => 'on_progress',
                default                              => 'belum', // not_started atau belum ada opener
            };

            // ── Status dokumen ───────────────────────────────────────────────
            $docs = $asesmen->documents;

            $asesmen->has_berita_acara = $docs->where('type', 'berita_acara_al')->isNotEmpty();
            $asesmen->has_ringkasan    = $docs->where('type', 'hasil_akreditasi_confidential')->isNotEmpty();

            // ── Status LHA ───────────────────────────────────────────────────
            // status_persetujuan_prodi: enum('pending','approved','revision_required','rejected')
            // default = 'pending', tidak ada field is_finalized
            // → null hanya jika belum ada dokumen sama sekali
            // → 'pending'           = sudah diupload/difinalisasi, menunggu persetujuan prodi → info
            // → 'revision_required' = prodi minta revisi → kuning
            // → 'approved'          = disetujui prodi → hijau
            // → 'rejected'          = ditolak prodi → merah
            $lha = $asesmen->lhaDocuments->first();
            $asesmen->lha_status = is_null($lha)
                ? null
                : $lha->status_persetujuan_prodi; // langsung pakai nilai enum-nya
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
        $step = (int) request('step', 1);
        $step = in_array($step, [1, 2]) ? $step : 1;

        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->firstOrFail();

        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . $user->role_selected . ' tidak diizinkan membuka halaman ini.');
        }

        // ── [BARU] Cek apakah ada asesor LAIN yang sudah lebih dulu membuka ──
        $firstStartedByOther = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->where('id_user', '!=', $user->id)
            ->where('status_pekerjaan', '!=', 'not_started')
            ->with('user')
            ->orderBy('updated_at')
            ->first();

        $iAmAlreadyStarted = $assignment->status_pekerjaan !== 'not_started';

        // ── [BARU] HARD GATE: blok masuk jika orang lain sudah duluan ──────
        // Pengecualian: jika saya sendiri sudah pernah masuk sebelumnya ($iAmAlreadyStarted),
        // berarti saya memang bukan first opener tapi sudah terlanjur masuk — tetap blok.
        if ($firstStartedByOther && !$iAmAlreadyStarted) {
            // Saya belum pernah masuk, tapi orang lain sudah → blok sepenuhnya
            return redirect()->route('al.berkas')
                ->with('error_first_opener', [
                    'nama'      => $firstStartedByOther->user?->name ?? 'Asesor lain',
                    'asesmen'   => $assignment->asesmen->getName(false) ?? 'asesmen ini',
                ]);
        }

        // ── Tangkap "apakah ini kunjungan pertama saya" SEBELUM update status ─
        $isFirstVisitForMe = !$iAmAlreadyStarted; // true hanya sekali

        // ── Update status (not_started → in_progress) ───────────────────────
        $this->updateStatusAL($assignment);

        $asesmen   = $assignment->asesmen;
        $isFirstOpener   = is_null($firstStartedByOther); // pastikan true karena lolos gate
        $firstOpenerUser = null; // kita sendiri yang pertama

        // ── Sisa logika sama seperti sebelumnya ─────────────────────────────
        $kriterias = Kriteria::with([
            'elemenStandar',
            'elemenStandar.indikator.jenisIndikator',
            'elemenStandar.indikatorPenilaian.jenjangPenilaian',
            'elemenStandar.penilaianElemenAl' => function ($query) use ($asesmen, $user) {
                $query->where('id_asesmen', $asesmen->id)->where('id_asesor', $user->id);
            }
        ])->get();

        $needsRevisions = PenilaianElemenAl::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)->with('elemen.kriteria')->get();

        $jenjangs  = JenjangPenilaian::all();
        $progress  = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];
        $penilaianByElemen = PenilaianElemenAl::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->get()
            ->keyBy('id_elemen');

        $asesorTeam = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->with('user')->get();

        $isEditorAsesor  = true; // yang masuk ke sini PASTI first opener
        $firstActiveAsesor = $asesorTeam
            ->where('status_pekerjaan', '!=', 'not_started')
            ->sortBy('updated_at')->first();

        $otherAsesorsProgress = [];
        foreach ($asesorTeam as $member) {
            if ($member->id_user == $user->id) continue;
            $otherAsesorsProgress[$member->id_user] = [
                'user'             => $member->user,
                'status_pekerjaan' => $member->status_pekerjaan,
                'progress'         => $this->calculateProgressBulk([$idAsesmen], $member->id_user)[$idAsesmen],
                'started_at'       => $member->updated_at,
            ];
        }

        $isFinalized   = in_array($asesmen->asesmenLapangan->status, ['completed', 'finalized']);
        $isInProgress  = $asesmen->asesmenLapangan->isInProgress();
        $uploadedFiles = $asesmen->pengajuan?->getUploadedDocuments();

        return view('asesmen.al.berkas.show', compact(
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
            'otherAsesorsProgress',
            'isFirstVisitForMe',   // [BARU]
            'isFirstOpener',       // [BARU] selalu true di sini
            'firstOpenerUser',     // [BARU] selalu null di sini
            'penilaianByElemen'
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
                $pengajuan->checkUpdateStatusAKAL('al', 'status_asesor_in_progress');
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

        $completedByAsesmen = PenilaianElemenAl::where('id_asesor', $userId)
            ->whereIn('id_asesmen', $asesmenIds)
            ->whereNotNull('skor')
            ->whereNotNull('komentar')
            ->where('komentar', '!=', '')
            ->get(['id_asesmen', 'id_elemen'])
            ->groupBy('id_asesmen')
            ->map(fn ($rows) => $rows->pluck('id_elemen')->unique()->count());

        $progress = [];
        foreach ($asesmenIds as $id) {
            $completed = (int) ($completedByAsesmen[$id] ?? 0);
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

            PenilaianElemenAl::where('id_asesmen', $idAsesmen)
                ->where('id_asesor', $user->id)
                ->update([
                    'status' => 'submitted',
                ]);

            $assignment->update([
                'status_pekerjaan' => 'submitted',
                'submitted_at' => now(),
            ]);

            $pengajuan = $assignment->asesmen->pengajuan;
            if ($pengajuan && !$this->hasilService->hasUnfinishedAlAsesor($idAsesmen)) {
                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('al', 'status_asesor_selesai');
                $pengajuan->statusLog()->firstOrCreate(
                    [
                        'status_from' => $statusFrom,
                        'status_to'   => PengajuanAkreditasi::STATUS_AL_SELESAI,
                    ],
                    [
                        'changed_by'  => Auth::id(),
                        'keterangan'  => 'Pengisi penilaian AL telah mengirim penilaian lapangan',
                        'changed_at'  => now(),
                    ]
                );
            }

            DB::commit();

            try {
                $this->hasilService->prepareDraftHasilAL($assignment->asesmen, $user->id, true);
            } catch (\Throwable $e) {
                Log::warning('submitPenilaian: skor resmi belum tersimpan', [
                    'asesmen_id' => $idAsesmen,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil dikirim. Kunci final tetap pada sekretariat.',
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
                ->where('jenis_asesmen', 'al')
                ->where('status_penawaran', 'accepted')
                ->firstOrFail();

            $hasil = HasilAkreditasi::where('id_asesmen', $idAsesmen)->first();
            if ($hasil?->isAlFinalized()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hasil sudah dikunci oleh sekretariat, penilaian tidak bisa dibatalkan.',
                ], 422);
            }

            if (!in_array($assignment->status_pekerjaan, ['submitted', 'approved'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penilaian belum dikirim, tidak ada yang perlu dibatalkan.',
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

            $pengajuan = $assignment->asesmen->pengajuan;
            if ($pengajuan?->status === PengajuanAkreditasi::STATUS_AL_SELESAI) {
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    'tanggal_al_selesai' => null,
                ]);
                $pengajuan->statusLog()->create([
                    'status_from' => PengajuanAkreditasi::STATUS_AL_SELESAI,
                    'status_to'   => PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    'changed_by'  => Auth::id(),
                    'keterangan'  => 'Pengisi membatalkan kirim penilaian AL',
                    'changed_at'  => now(),
                ]);
            }

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
                    'message' => 'Tidak dapat mereset penilaian yang telah di-submit atau disetujui. Silakan batalkan submit terlebih dahulu.',
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

            $excelService = new PenilaianExcelService(PenilaianElemenAl::class);
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
            if (!in_array($mode, ['template', 'full', 'personal', 'personal_al'])) {
                return redirect()->back()->with('error', 'Mode download tidak valid');
            }

            // Create service dengan mode
            $excelService = new PenilaianExcelService(PenilaianElemenAl::class, $mode, $useColor); // atau PenilaianElemenAl

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
                        $penilaian = $elemen->penilaianElemenAl
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

    /**
     * Halaman Upload Excel Penilaian Manual
     */
    public function uploadExcelPage($idAsesmen)
    {
        $user = Auth::user();

        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->firstOrFail();

        if ($assignment->role->name != $user->role_selected) {
            abort(403, 'Mohon maaf role Anda sebagai ' . $user->role_selected . ' tidak diizinkan membuka halaman ini.');
        }

        // ── Cek asesor lain yang sudah duluan — SAMA PERSIS dengan showBerkas ──
        $firstStartedByOther = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->where('id_user', '!=', $user->id)
            ->where('status_pekerjaan', '!=', 'not_started')
            ->with('user')
            ->orderBy('updated_at')
            ->first();

        $iAmAlreadyStarted = $assignment->status_pekerjaan !== 'not_started';

        if ($firstStartedByOther && !$iAmAlreadyStarted) {
            return redirect()->route('al.berkas')
                ->with('error_first_opener', [
                    'nama'    => $firstStartedByOther->user?->name ?? 'Asesor lain',
                    'asesmen' => $assignment->asesmen->getName(false) ?? 'asesmen ini',
                ]);
        }

        $isFirstVisitForMe = !$iAmAlreadyStarted;
        $isFirstOpener     = is_null($firstStartedByOther);
        $firstOpenerUser   = null;

        $this->updateStatusAL($assignment);

        $asesmen = $assignment->asesmen;
        $progress = $this->calculateProgressBulk([$asesmen->id], $user->id)[$asesmen->id];

        $statusPekerjaan = $assignment->fresh()->status_pekerjaan ?? 'not_started';
        $isSubmittedOnly = $statusPekerjaan === 'submitted';
        $isApproved      = $statusPekerjaan === 'approved';
        $isComplete      = $progress['percentage'] == 100;

        // ── Asesor team — SAMA dengan showBerkas ────────────────────────────
        $asesorTeam = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
            ->with('user')
            ->orderBy('urutan_asesor')
            ->get();

        $isEditorAsesor = true; // lolos gate = pasti first opener
        $firstActiveAsesor = $asesorTeam
            ->where('status_pekerjaan', '!=', 'not_started')
            ->sortBy('updated_at')
            ->first();

        return view('asesmen.al.berkas.upload-excel', compact(
            'asesmen',
            'assignment',
            'progress',
            'statusPekerjaan',
            'isSubmittedOnly',
            'isApproved',
            'isComplete',
            'asesorTeam',
            'isEditorAsesor',
            'firstActiveAsesor',
            'isFirstVisitForMe',
            'isFirstOpener',
            'firstOpenerUser',
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
            $safeCode = str_replace(['/', '\\'], '-', $asesmen->code);

            $filename = 'import_' . $safeCode . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('', $filename);

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

    public function confirmOpener(Request $request, $idAsesmen)
    {
        $continueUrl = $request->input('continue_url');
        $sessionKey  = "al_opener_confirmed_{$idAsesmen}";

        $request->session()->put($sessionKey, true);

        return redirect($continueUrl);
    }

    public function showHasil($idAsesmen)
    {
        $user = Auth::user();
        $assignment = $this->findAcceptedAlAssignment($idAsesmen, $user->id);

        $asesmen = Asesmen::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengajuan',
            'hasil',
        ])->findOrFail($idAsesmen);

        $canEdit = $this->canAsesorEditHasil($asesmen, $assignment);
        $calcError = null;
        $hasil = $asesmen->hasil;
        $skorIsPreview = false;
        $unfinished = $this->hasilService->hasUnfinishedAlAsesor($asesmen->id);

        if ($hasil?->isAlFinalized()) {
            $canEdit = false;
        } else {
            try {
                $hasil = $this->hasilService->prepareDraftHasilAL($asesmen, $user->id, !$unfinished);
            } catch (\Throwable $e) {
                $calcError = $e->getMessage();
                $hasil = $asesmen->hasil;
            }
        }

        if (!$hasil) {
            $hasil = new HasilAkreditasi([
                'status' => 'draft_al',
                'id_asesmen' => $asesmen->id,
                'id_pengajuan' => $asesmen->id_pengajuan,
            ]);
        }

        if ($hasil->exists) {
            $hasil->loadMissing(['studyProgram', 'category', 'finalizedAlBy']);
        }
        $resume = $hasil->exists
            ? $hasil->getDraftResumeAsesmenOrDefault()
            : HasilAkreditasi::resumeAsesmenSkeleton();
        $resumeSaved = $hasil->exists && $hasil->hasResumeAsesmen();
        $detailSkorAL = $hasil->detail_skor_al ?? [];
        $kriteriaList = $hasil->exists ? $hasil->getKriteriaOrderedList() : [];
        $elemenList = $detailSkorAL['elemen'] ?? [];
        $displaySkor = $hasil->skor_al;
        $totalElemen = ElemenStandar::count();
        $assessedElemen = PenilaianElemenAl::where('id_asesmen', $asesmen->id)
            ->where('id_asesor', $user->id)
            ->whereNotNull('skor')
            ->whereNotNull('komentar')
            ->count();
        $isPenilaianComplete = $totalElemen > 0 && $assessedElemen >= $totalElemen;
        $rentangSkor = $this->syaratRepo->getRentangSkor();
        $skorMinimumUnggul = $this->syaratRepo->getSkorMinimumUnggul();
        $kriteriaRequired = $this->syaratRepo->getKriteriaRequired();

        if (!$hasil->isAlFinalized()) {
            try {
                $preview = $this->hasilService->calculateAL($asesmen, false, true);
                $previewElemen = $preview['detail_elemen'] ?? [];
                if (count($previewElemen) >= count($elemenList) || empty($elemenList)) {
                    $skorIsPreview = $unfinished;
                    $displaySkor = $preview['skor_total'];
                    $kriteriaList = $this->kriteriaListFromCalc($preview);
                    $elemenList = $previewElemen;
                } else {
                    $skorIsPreview = true;
                }
                $calcError = null;
            } catch (\Throwable $e) {
                if (empty($elemenList) && !$displaySkor) {
                    $calcError = $calcError ?: $e->getMessage();
                }
            }
        }

        $validationSummary = null;
        try {
            if (!$hasil->relationLoaded('studyProgram') || !$hasil->studyProgram) {
                $hasil->setRelation('studyProgram', $asesmen->studyProgram);
            }
            if (!$hasil->id_pengajuan) {
                $hasil->id_pengajuan = $asesmen->id_pengajuan;
            }
            if (!$hasil->isAlFinalized() && $displaySkor) {
                $hasil->skor_final = null;
                $hasil->skor_al = $displaySkor;
            }
            if (!empty($elemenList)) {
                $pelampauan = [];
                foreach ($elemenList as $el) {
                    $kode = $el['kode_kriteria'] ?? null;
                    $skorElemen = (float) ($el['skor'] ?? 0);
                    if ($kode && $skorElemen >= 4) {
                        $pelampauan[$kode][] = $el['kode_elemen'] ?? ($el['nama_elemen'] ?? '');
                    }
                }
                $hasil->pelampauan_standar_al = $pelampauan;
            }
            $validationSummary = $this->hasilService->getValidationSummary($hasil, 'al');
        } catch (\Throwable $e) {
            Log::warning('showHasil: validasi unggul tidak tersedia', [
                'asesmen_id' => $asesmen->id,
                'error' => $e->getMessage(),
            ]);
        }

        return view('asesmen.al.berkas.hasil', compact(
            'asesmen',
            'assignment',
            'hasil',
            'resume',
            'resumeSaved',
            'kriteriaList',
            'elemenList',
            'canEdit',
            'calcError',
            'displaySkor',
            'skorIsPreview',
            'totalElemen',
            'assessedElemen',
            'isPenilaianComplete',
            'rentangSkor',
            'skorMinimumUnggul',
            'kriteriaRequired',
            'validationSummary'
        ));
    }

    public function saveResume(Request $request, $idAsesmen)
    {
        $charLimit = HasilAkreditasi::resumeBabCharLimit();

        $request->validate([
            'bab'           => 'required|array|min:1|max:20',
            'bab.*.title'   => 'required|string|max:120',
            'bab.*.content' => [
                'nullable',
                'string',
                new \App\Rules\MaxPlainTextLength($charLimit),
            ],
        ]);

        $user = Auth::user();
        $assignment = $this->findAcceptedAlAssignment($idAsesmen, $user->id);
        $asesmen = Asesmen::with(['hasil', 'studyProgram'])->findOrFail($idAsesmen);
        $hasil = $asesmen->hasil;

        if (!$hasil) {
            try {
                $hasil = $this->hasilService->prepareDraftHasilAL($asesmen, $user->id, false);
            } catch (\Throwable $e) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Data hasil belum siap: ' . $e->getMessage(),
                ], 422);
            }
        }

        if ($hasil->isAlFinalized() || !$this->canAsesorEditHasil($asesmen, $assignment)) {
            return response()->json([
                'ok' => false,
                'message' => 'Draft resume sudah dikunci atau Anda tidak berwenang mengubahnya.',
            ], 422);
        }

        $allowedTags = '<p><br><strong><em><u><ol><ul><li><h3><h4><blockquote>';
        $bab = [];

        foreach ($request->input('bab', []) as $item) {
            $bab[] = [
                'title' => trim($item['title'] ?? ''),
                'content' => !empty($item['content'])
                    ? strip_tags($item['content'], $allowedTags)
                    : null,
            ];
        }

        $hasil->saveResumeAsesmen(['bab' => $bab], $user->id);

        return response()->json([
            'ok' => true,
            'message' => 'Draft resume asesmen berhasil disimpan.',
            'saved_at' => now()->locale('id')->translatedFormat('d M Y, H:i'),
            'has_resume' => $hasil->fresh()->hasResumeAsesmen(),
        ]);
    }

    private function findAcceptedAlAssignment(int $idAsesmen, int $userId): AsesmenUserRole
    {
        $assignment = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $userId)
            ->where('jenis_asesmen', 'al')
            ->where('id_role', Role::ID_ROLE_ASESOR)
            ->where('status_penawaran', 'accepted')
            ->first();

        abort_if(!$assignment, 403, 'Anda tidak memiliki penugasan asesor AL pada asesmen ini.');

        return $assignment;
    }

    private function canAsesorEditHasil(Asesmen $asesmen, AsesmenUserRole $assignment): bool
    {
        if ($asesmen->hasil?->isAlFinalized()) {
            return false;
        }

        $firstStartedByOther = AsesmenUserRole::where('id_asesmen', $asesmen->id)
            ->where('jenis_asesmen', 'al')
            ->where('id_role', Role::ID_ROLE_ASESOR)
            ->where('id_user', '!=', $assignment->id_user)
            ->where('status_pekerjaan', '!=', 'not_started')
            ->exists();

        return !$firstStartedByOther || $assignment->status_pekerjaan !== 'not_started';
    }

    private function kriteriaListFromCalc(array $calc): array
    {
        $kriteriaList = $calc['detail_kriteria'] ?? [];
        $elemenList = $calc['detail_elemen'] ?? [];
        $ordered = [];

        foreach ($elemenList as $elemen) {
            $kode = $elemen['kode_kriteria'] ?? null;
            if ($kode && !isset($ordered[$kode]) && isset($kriteriaList[$kode])) {
                $ordered[$kode] = $kriteriaList[$kode];
            }
        }

        return $ordered;
    }
}
