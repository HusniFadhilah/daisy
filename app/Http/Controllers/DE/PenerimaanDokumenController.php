<?php
// app/Http/Controllers/DE/PenerimaanDokumenController.php

namespace App\Http\Controllers\DE;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Asesmen;
use App\Models\University;
use App\Models\DegreeLevel;
use Illuminate\Http\Request;
use App\Models\AsesmenUserRole;
use App\Models\BorangValidation;
use App\Models\PengajuanDokumen;
use App\Helpers\ResponseFormatter;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PenerimaanDokumenController extends Controller
{
    /**
     * CONFIG: Set validation mode
     */
    private const BORANG_VALIDATOR_MODE = 'strict'; // Only 1 validator allowed

    /**
     * Display list of document submissions
     */
    public function index(Request $request)
    {
        // Subquery untuk mendapatkan status terakhir dari log
        $latestStatusSubquery = PengajuanStatusLog::select('status_to')
            ->whereColumn('id_pengajuan', 'pengajuan_akreditasi.id')
            ->orderByDesc('changed_at')
            ->limit(1);

        // Build query dengan join ke status log
        $query = PengajuanAkreditasi::select('pengajuan_akreditasi.*')
            ->selectSub($latestStatusSubquery, 'latest_status_from_log')
            ->with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'pengaju',
                'dokumen' => function ($q) {
                    $q->where('is_latest', true);
                },
                'asesmen.userRoles.user',
                'asesmen.userRoles.role',
                'latestStatusLog' => function ($q) {
                    $q->orderByDesc('changed_at')->limit(1);
                }
            ])
            ->whereHas('statusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                    PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                    PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                    PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                    PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                ]);
            });

        // Filter by status - gunakan status dari log
        if ($request->filled('status')) {
            $query->whereHas('statusLog', function ($q) use ($request) {
                $q->where('status_to', $request->status)
                    ->whereRaw('changed_at = (
                        SELECT MAX(changed_at)
                        FROM pengajuan_status_log psl2
                        WHERE psl2.id_pengajuan = pengajuan_status_log.id_pengajuan
                    )');
            });
        }

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_university', $request->university_id);
            });
        }

        // Filter by degree level
        if ($request->filled('degree_level_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_degree_level', $request->degree_level_id);
            });
        }

        // Filter by document status - gunakan pengecekan dokumen aktual
        if ($request->filled('doc_status')) {
            if ($request->doc_status === 'complete') {

                $query->whereHas('dokumen', function ($q) {
                    $q->where('is_latest', true)
                        ->whereIn('jenis_dokumen', ['draft_borang', 'data_kualitatif']);
                })
                    ->whereHas('dokumen', function ($q) {
                        $q->where('is_latest', true)
                            ->where('jenis_dokumen', 'data_kuantitatif');
                    })
                    ->whereHas('dokumen', function ($q) {
                        $q->where('is_latest', true)
                            ->where('jenis_dokumen', 'lembar_pengesahan');
                    })
                    // Suplemen hanya wajib untuk menuju_unggul
                    ->where(function ($q) {
                        $q->where('jenis_akreditasi', '!=', 'menuju_unggul')
                            ->orWhereHas('dokumen', function ($sq) {
                                $sq->where('is_latest', true)
                                    ->where('jenis_dokumen', 'data_suplemen');
                            });
                    });
            } elseif ($request->doc_status === 'incomplete') {

                $query->where(function ($q) {
                    // LED missing
                    $q->whereDoesntHave('dokumen', function ($sq) {
                        $sq->where('is_latest', true)
                            ->whereIn('jenis_dokumen', ['draft_borang', 'data_kualitatif']);
                    })
                        // OR LKPS missing
                        ->orWhereDoesntHave('dokumen', function ($sq) {
                            $sq->where('is_latest', true)
                                ->where('jenis_dokumen', 'data_kuantitatif');
                        })
                        // OR pengesahan missing
                        ->orWhereDoesntHave('dokumen', function ($sq) {
                            $sq->where('is_latest', true)
                                ->where('jenis_dokumen', 'lembar_pengesahan');
                        })
                        // OR (menuju_unggul but suplemen missing)
                        ->orWhere(function ($qq) {
                            $qq->where('jenis_akreditasi', 'menuju_unggul')
                                ->whereDoesntHave('dokumen', function ($sq2) {
                                    $sq2->where('is_latest', true)
                                        ->where('jenis_dokumen', 'data_suplemen');
                                });
                        });
                });
            } elseif ($request->doc_status === 'none') {

                $query->whereDoesntHave('dokumen', function ($q) {
                    $q->where('is_latest', true);
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhere('judul', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort - prioritas ke tanggal terbaru dari log
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $pengajuans = $query->paginate(20)->through(function ($pengajuan) {
            // Set status aktual dari log untuk konsistensi
            if ($pengajuan->latestStatusLog) {
                $pengajuan->actual_status = $pengajuan->latestStatusLog->status_to;
                $pengajuan->status_changed_at = $pengajuan->latestStatusLog->changed_at;
            } else {
                $pengajuan->actual_status = $pengajuan->status;
                $pengajuan->status_changed_at = null;
            }
            return $pengajuan;
        });

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // Pengajuan menunggu upload
        $pengajuanMenunggu = PengajuanAkreditasi::with('studyProgram.university', 'studyProgram.degreeLevel')
            ->whereHas('statusLog', function ($q) {
                $q->where('status_to', PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI)
                    ->whereRaw('changed_at = (
                        SELECT MAX(changed_at)
                        FROM pengajuan_status_log psl2
                        WHERE psl2.id_pengajuan = pengajuan_status_log.id_pengajuan
                    )');
            })
            ->get();
        $countPengajuanMenunggu = $pengajuanMenunggu->count();

        // AJAX request
        if ($request->ajax()) {
            $html = view('de.penerimaan-dokumen.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'stats' => $stats,
            ]);
        }

        return view('de.penerimaan-dokumen.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'degreeLevels',
            'pengajuanMenunggu',
            'countPengajuanMenunggu'
        ));
    }

    /**
     * Show detail pengajuan dan dokumen
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'dokumen' => function ($q) {
                $q->where('is_latest', true)->orderBy('created_at', 'desc');
            },
            'borangImports',
            'latestBorangImport',
            'statusLog' => function ($q) {
                $q->orderByDesc('changed_at');
            },
            'latestStatusLog',
            'asesmen.userRoles' => function ($q) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->with(['user', 'role', 'borangValidation']);
            },
        ])->findOrFail($id);
        if ($pengajuan->status == PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM) {
            $newStatus = PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA;
            $keterangan = 'Dokumen LED (DOCX) dan LKPS (Excel) telah diterima oleh LAMDEPILAR';

            $pengajuan->updateStatusSafely($newStatus, $keterangan);
        }

        // Set actual status from log
        if ($pengajuan->latestStatusLog) {
            $pengajuan->actual_status = $pengajuan->latestStatusLog->status_to;
        } else {
            $pengajuan->actual_status = $pengajuan->status;
        }

        // Get uploaded documents grouped
        $uploadedDocuments = $pengajuan->getUploadedDocuments();

        // Check document completeness
        $docCompleteness = $this->checkDocumentCompleteness($pengajuan);

        // Get current validator
        $currentValidator = $pengajuan->getCurrentBorangValidator();

        // Can assign validator if docs complete and status allows
        $canAssignValidator = $pengajuan->canAssignValidator();

        return view('de.penerimaan-dokumen.show', compact(
            'pengajuan',
            'uploadedDocuments',
            'docCompleteness',
            'currentValidator',
            'canAssignValidator'
        ));
    }

    /**
     * Konfirmasi penerimaan dokumen
     */
    public function konfirmasiPenerimaan(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('latestStatusLog')->findOrFail($id);

            // Get actual status from log
            $actualStatus = $pengajuan->latestStatusLog
                ? $pengajuan->latestStatusLog->status_to
                : $pengajuan->status;

            // Validasi status pengajuan dari log
            if (!in_array($actualStatus, [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            ])) {
                return redirect()->back()->with('error', 'Status pengajuan tidak valid untuk konfirmasi penerimaan dokumen.');
            }

            // Check if documents are uploaded
            $hasLED = $pengajuan->dokumen()
                ->where('is_latest', true)
                ->whereIn('jenis_dokumen', ['draft_borang', 'data_kualitatif'])
                ->exists();

            $hasLKPS = $pengajuan->dokumen()
                ->where('is_latest', true)
                ->where('jenis_dokumen', 'data_kuantitatif')
                ->exists();

            $hasPengesahan = $pengajuan->dokumen()
                ->where('is_latest', true)
                ->where('jenis_dokumen', 'lembar_pengesahan')
                ->exists();

            $needSuplemen = ($pengajuan->jenis_akreditasi === 'menuju_unggul');

            $hasSuplemen = true;
            if ($needSuplemen) {
                $hasSuplemen = $pengajuan->dokumen()
                    ->where('is_latest', true)
                    ->where('jenis_dokumen', 'data_suplemen')
                    ->exists();
            }

            if (!$hasLED || !$hasLKPS || !$hasPengesahan || !$hasSuplemen) {
                $missing = [];
                if (!$hasLED) $missing[] = 'LED';
                if (!$hasLKPS) $missing[] = 'LKPS';
                if (!$hasPengesahan) $missing[] = 'Lembar Pengesahan';
                if ($needSuplemen && !$hasSuplemen) $missing[] = 'Suplemen';

                return redirect()->back()->with('error', 'Dokumen belum lengkap: ' . implode(', ', $missing) . '.');
            }

            // Update status pengajuan berdasarkan status aktual dari log
            if ($actualStatus === PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI) {
                $pengajuan->updateStatusSafely(
                    PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                    $validated['catatan'] ?? 'Dokumen draft borang telah diterima'
                );
                $pengajuan->update(['tanggal_draft_borang' => now()]);
            } elseif ($actualStatus === PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA) {
                $pengajuan->updateStatusSafely(
                    PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                    $validated['catatan'] ?? 'Borang online telah lengkap dan diterima'
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Penerimaan dokumen berhasil dikonfirmasi. Silakan tugaskan validator untuk validasi dokumen.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Konfirmasi penerimaan dokumen gagal', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Gagal konfirmasi penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Show form untuk assign validator borang
     */
    public function showAssignValidatorForm($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'latestBorangImport',
            'latestStatusLog',
            'borangValidators.user',
            'borangValidators.role'
        ])->findOrFail($id);

        // Validation checks
        if (!$pengajuan->canAssignValidator()) {
            return back()->with('error', 'Pengajuan ini belum siap untuk assign validator.');
        }

        // Get current assignment
        $currentAssignment = $pengajuan->borangValidators()
            ->whereIn('status_penawaran', ['pending', 'accepted'])
            ->with(['user', 'role'])
            ->first();

        // Get available validators
        $excludeUserIds = $pengajuan->borangValidators()
            ->pluck('id_user')
            ->toArray();

        $validators = User::whereJsonContains('roles', 'validator')
            ->when(count($excludeUserIds) > 0, function ($q) use ($excludeUserIds) {
                $q->whereNotIn('id', $excludeUserIds);
            })
            ->orderBy('name')
            ->get();

        return view('de.penerimaan-dokumen.assign-validator', compact(
            'pengajuan',
            'currentAssignment',
            'validators'
        ));
    }

    /**
     * Assign validator untuk borang (STRICT MODE)
     */
    public function assignValidator(Request $request, $id)
    {
        $request->validate([
            'id_validator' => 'required|exists:users,id',
            'catatan_de' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        $pengajuan = PengajuanAkreditasi::with('latestBorangImport', 'latestStatusLog')
            ->lockForUpdate()
            ->findOrFail($id);

        // Validation checks
        if (!$pengajuan->canAssignValidator()) {
            return ResponseFormatter::error(null, 'Pengajuan tidak dapat ditugaskan validator.', 422);
        }

        // Check validator
        $validator = User::findOrFail($request->id_validator);
        if (!$validator->hasRole('validator')) {
            return ResponseFormatter::error(null, 'User yang dipilih bukan validator.', 422);
        }

        // Prevent duplicate
        if ($pengajuan->isUserAssignedAsValidator($validator->id)) {
            return ResponseFormatter::error(null, 'Validator ini sudah pernah ditugaskan sebelumnya.', 422);
        }

        try {
            // Auto-create asesmen if not exists
            if (!$pengajuan->asesmen) {
                $asesmen = Asesmen::create([
                    'id_pengajuan' => $pengajuan->id,
                    'id_study_program' => $pengajuan->id_program_studi,
                    'code' => 'ASM-' . $pengajuan->nomor_pengajuan,
                    'name' => $pengajuan->judul,
                    'description' => $pengajuan->judul,
                    'status' => 'active',
                ]);
            } else {
                $asesmen = $pengajuan->asesmen;
            }

            // STRICT MODE: Delete pending assignments
            if (self::BORANG_VALIDATOR_MODE === 'strict') {
                $deletedCount = AsesmenUserRole::where('id_asesmen', $asesmen->id)
                    ->where('jenis_asesmen', 'dokumen')
                    ->where('status_penawaran', 'pending')
                    ->delete();

                if ($deletedCount > 0) {
                    Log::info('Deleted pending dokumen validator assignments', [
                        'pengajuan_id' => $id,
                        'count' => $deletedCount
                    ]);
                }
            }

            // Get validator role
            $validatorRole = Role::where('name', 'validator')->firstOrFail();

            // Create new assignment
            $assignment = AsesmenUserRole::create([
                'id_asesmen' => $asesmen->id,
                'id_user' => $validator->id,
                'id_role' => $validatorRole->id,
                'jenis_asesmen' => 'dokumen',
                'status_penawaran' => 'pending',
                'status_pekerjaan' => 'not_started',
            ]);

            // Create borang validation record
            BorangValidation::create([
                'id_assignment' => $assignment->id,
                'id_pengajuan' => $pengajuan->id,
                'total_sections' => $pengajuan->latestBorangImport->total_sections ?? 0,
                'validated_sections' => 0,
            ]);

            // Get current status from log
            $oldStatus = $pengajuan->latestStatusLog
                ? $pengajuan->latestStatusLog->status_to
                : $pengajuan->status;

            // Update status pengajuan
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                'tanggal_validasi_borang_assigned' => now(),
            ]);

            $this->logStatus(
                $pengajuan,
                $oldStatus,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                "Validator {$validator->name} ditugaskan untuk review LED. " . ($request->catatan_de ?? '')
            );

            // Send email notification
            try {
                Mail::to($validator->email)
                    ->queue(new \App\Mail\ValidatorBorangAssignedMail(
                        $pengajuan,
                        $assignment,
                        $request->catatan_de
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to queue validator assignment email', [
                    'pengajuan_id' => $id,
                    'error' => $e->getMessage(),
                ]);
            }

            DB::commit();

            return ResponseFormatter::success(
                ['assignment_id' => $assignment->id, 'validator' => $validator->name],
                "Validator {$validator->name} berhasil ditugaskan. Email penawaran sedang diproses."
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign borang validator', [
                'pengajuan_id' => $id,
                'validator_id' => $request->id_validator,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ResponseFormatter::error(null, 'Terjadi kesalahan server.', 500);
        }
    }

    /**
     * Cancel validator assignment
     */
    public function cancelValidator($assignmentId)
    {
        $assignment = AsesmenUserRole::with(['asesmen.pengajuan.latestStatusLog'])->findOrFail($assignmentId);
        $pengajuan = $assignment->asesmen->pengajuan;

        if ($assignment->status_penawaran !== 'pending') {
            return redirect()->back()->with('error', 'Hanya penugasan pending yang dapat dibatalkan.');
        }

        DB::beginTransaction();
        try {
            // Delete validation record
            BorangValidation::where('id_assignment', $assignment->id)->delete();

            // Delete assignment
            $assignment->delete();

            // Revert status if no other validators
            $hasOtherValidators = $pengajuan->borangValidators()
                ->whereIn('status_penawaran', ['pending', 'accepted'])
                ->exists();

            if (!$hasOtherValidators) {
                $oldStatus = $pengajuan->latestStatusLog
                    ? $pengajuan->latestStatusLog->status_to
                    : $pengajuan->status;

                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                ]);

                $this->logStatus(
                    $pengajuan,
                    $oldStatus,
                    PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                    'Penugasan validator dibatalkan, status dikembalikan ke Borang Online Selesai'
                );
            }

            DB::commit();

            return redirect()->back()->with('success', 'Penugasan validator berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel validator assignment', [
                'assignment_id' => $assignmentId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal membatalkan penugasan.');
        }
    }

    /**
     * Kirim reminder upload dokumen
     */
    public function kirimReminder(Request $request)
    {
        $validated = $request->validate([
            'id_pengajuan' => 'required|array',
            'id_pengajuan.*' => 'exists:pengajuan_akreditasi,id',
            'pesan_reminder' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $sent = 0;

            foreach ($validated['id_pengajuan'] as $pengajuanId) {
                $pengajuan = PengajuanAkreditasi::with('studyProgram', 'latestStatusLog')->find($pengajuanId);

                if (!$pengajuan) continue;

                // Get actual status
                $actualStatus = $pengajuan->latestStatusLog
                    ? $pengajuan->latestStatusLog->status_to
                    : $pengajuan->status;

                // Log reminder
                $pengajuan->statusLog()->create([
                    'status_from' => $actualStatus,
                    'status_to' => $actualStatus,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => "Reminder dikirim: {$validated['pesan_reminder']}",
                ]);

                // TODO: Send email
                // Mail::to($pengajuan->studyProgram->email)->send(new ReminderUploadDokumen($pengajuan, $validated['pesan_reminder']));

                $sent++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Reminder berhasil dikirim ke {$sent} program studi.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kirim reminder gagal', [
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Gagal mengirim reminder: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics - IMPROVED VERSION
     */
    private function calculateStatistics(): array
    {
        // Gunakan subquery untuk mendapatkan status terakhir dari setiap pengajuan
        $latestStatusSubquery = DB::table('pengajuan_status_log as psl')
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->whereRaw('psl.changed_at = (
                SELECT MAX(psl2.changed_at)
                FROM pengajuan_status_log psl2
                WHERE psl2.id_pengajuan = psl.id_pengajuan
            )')
            ->groupBy('psl.id_pengajuan', 'psl.status_to');

        // Query dengan join ke latest status
        $stats = DB::table('pengajuan_akreditasi as pa')
            ->joinSub($latestStatusSubquery, 'latest', function ($join) {
                $join->on('pa.id', '=', 'latest.id_pengajuan');
            })
            ->selectRaw("
                COUNT(DISTINCT CASE WHEN latest.status_to = ? THEN pa.id END) AS total_menunggu_dokumen,
                COUNT(DISTINCT CASE WHEN latest.status_to = ? THEN pa.id END) AS total_dokumen_lengkap,
                COUNT(DISTINCT CASE WHEN latest.status_to IN (?, ?) THEN pa.id END) AS total_dalam_validasi,
                COUNT(DISTINCT CASE WHEN latest.status_to = ? THEN pa.id END) AS total_perlu_revisi
            ", [
                PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            ])
            ->first();

        // Hitung dokumen masuk dan tervalidasi dari log
        $historyStats = DB::table('pengajuan_status_log')
            ->selectRaw("
                COUNT(DISTINCT CASE WHEN status_to = ? THEN id_pengajuan END) AS total_dokumen_masuk,
                COUNT(DISTINCT CASE WHEN status_to = ? THEN id_pengajuan END) AS total_tervalidasi
            ", [
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            ])
            ->first();

        return [
            'total_menunggu_dokumen' => (int) ($stats->total_menunggu_dokumen ?? 0),
            'total_dokumen_masuk'    => (int) ($historyStats->total_dokumen_masuk ?? 0),
            'total_dokumen_lengkap'  => (int) ($stats->total_dokumen_lengkap ?? 0),
            'total_dalam_validasi'   => (int) ($stats->total_dalam_validasi ?? 0),
            'total_perlu_revisi'     => (int) ($stats->total_perlu_revisi ?? 0),
            'total_tervalidasi'      => (int) ($historyStats->total_tervalidasi ?? 0),
        ];
    }

    /**
     * Check document completeness
     */
    private function checkDocumentCompleteness(PengajuanAkreditasi $pengajuan)
    {
        $required = [
            'led' => [
                'label' => 'Laporan Evaluasi Diri (LED)',
                'aliases' => ['data_kualitatif', 'draft_borang', 'borang_final'],
            ],
            'lkps' => [
                'label' => 'Laporan Kinerja Program Studi (LKPS)',
                'aliases' => ['data_kuantitatif'],
            ],
            'pengesahan' => [
                'label' => 'Lembar Pengesahan Dokumen',
                'aliases' => ['lembar_pengesahan'],
            ],
        ];
        $needSuplemen = $pengajuan->need_suplemen;
        if ($needSuplemen) {
            $required['suplemen'] = [
                'label' => 'Suplemen LED',
                'aliases' => ['data_suplemen'],
            ];
        }

        $uploaded = $pengajuan->dokumen()
            ->where('is_latest', true)
            ->pluck('jenis_dokumen')
            ->toArray();

        $uploadedSet = array_flip($uploaded);

        $details = [];
        $uploadedCount = 0;

        foreach ($required as $key => $cfg) {
            $found = false;
            foreach ($cfg['aliases'] as $alias) {
                if (isset($uploadedSet[$alias])) {
                    $found = true;
                    break;
                }
            }

            $details[$key] = [
                'label' => $cfg['label'],
                'uploaded' => $found,
            ];

            if ($found) $uploadedCount++;
        }

        $totalRequired = count($required);
        $percentage = $totalRequired ? (int) round(($uploadedCount / $totalRequired) * 100) : 0;

        return [
            'details' => $details,
            'is_complete' => ($uploadedCount === $totalRequired),
            'percentage' => $percentage,
            'need_suplemen' => $needSuplemen,
        ];
    }

    /**
     * Log status
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
