<?php

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
        // Build query untuk pengajuan yang sudah bayar dan menunggu upload dokumen
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'dokumen' => function ($q) {
                $q->where('is_latest', true);
            },
            'asesmen.userRoles.user',
            'asesmen.userRoles.role',
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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        // Filter by document status
        if ($request->filled('doc_status')) {
            if ($request->doc_status === 'complete') {
                $query->whereHas('dokumen', function ($q) {
                    $q->where('is_latest', true)
                        ->whereIn('jenis_dokumen', ['draft_borang', 'data_kualitatif']);
                }, '>=', 2);
            } elseif ($request->doc_status === 'incomplete') {
                $query->whereDoesntHave('dokumen', function ($q) {
                    $q->where('is_latest', true)
                        ->whereIn('jenis_dokumen', ['draft_borang', 'data_kualitatif']);
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        $pengajuanMenunggu = \App\Models\PengajuanAkreditasi::with('studyProgram.university', 'studyProgram.degreeLevel')
            ->where('status', \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI)
            ->get();
        $countPengajuanMenunggu = count($pengajuanMenunggu);

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
            'asesmen.userRoles' => function ($q) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->with(['user', 'role', 'borangValidation']);
            },
        ])->findOrFail($id);

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
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validasi status pengajuan
            if (!in_array($pengajuan->status, [
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

            if (!$hasLED || !$hasLKPS) {
                return redirect()->back()->with('error', 'Dokumen LED dan LKPS belum lengkap.');
            }

            // Update status pengajuan
            if ($pengajuan->status === PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI) {
                $pengajuan->updateStatusSafely(
                    PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                    $validated['catatan'] ?? 'Dokumen draft borang telah diterima'
                );
                $pengajuan->update(['tanggal_draft_borang' => now()]);
            } elseif ($pengajuan->status === PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA) {
                $pengajuan->updateStatusSafely(
                    PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                    $validated['catatan'] ?? 'Borang online telah lengkap dan diterima'
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Penerimaan dokumen berhasil dikonfirmasi. Silakan tugaskan validator untuk validasi dokumen.');
        } catch (\Exception $e) {
            DB::rollBack();
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
        $pengajuan = PengajuanAkreditasi::with('latestBorangImport')->lockForUpdate()->findOrFail($id);

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
            return ResponseFormatter::error(null, 'Validator ini sudah pernah ditugaskan.', 422);
        }

        try {
            // Auto-create asesmen if not exists
            if (!$pengajuan->asesmen) {
                $asesmen = Asesmen::create([
                    'id_pengajuan' => $pengajuan->id,
                    'id_study_program' => $pengajuan->id_program_studi,
                    'code' => $pengajuan->nomor_pengajuan,
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

            // Update pengajuan status
            $oldStatus = $pengajuan->status;
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
        $assignment = AsesmenUserRole::with(['asesmen.pengajuan'])->findOrFail($assignmentId);
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
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
                ]);
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
                $pengajuan = PengajuanAkreditasi::with('studyProgram')->find($pengajuanId);

                if (!$pengajuan) continue;

                // Log reminder
                $pengajuan->statusLog()->create([
                    'status_from' => $pengajuan->status,
                    'status_to' => $pengajuan->status,
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
            return redirect()->back()->with('error', 'Gagal mengirim reminder: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        // Ambil semua status log relevan
        $logs = PengajuanStatusLog::whereIn('status_to', [
            PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
            PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
        ])->get();

        // Group by pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        $totalMenungguDokumen = $logsByPengajuan->filter(fn($l) => $l->pluck('status_to')->contains(PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI))->count();
        $totalDokumenMasuk   = $logsByPengajuan->filter(fn($l) => $l->pluck('status_to')->contains(PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA))->count();
        $totalDokumenLengkap = $logsByPengajuan->filter(fn($l) => $l->pluck('status_to')->contains(PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI))->count();
        $totalDalamValidasi  = $logsByPengajuan->filter(fn($l) => $l->pluck('status_to')->intersect([
            PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
        ])->isNotEmpty())->count();
        $totalPerluRevisi    = $logsByPengajuan->filter(fn($l) => $l->pluck('status_to')->contains(PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED))->count();
        $totalTervalidasi    = $logsByPengajuan->filter(fn($l) => $l->pluck('status_to')->contains(PengajuanAkreditasi::STATUS_BORANG_VALIDATED))->count();

        return [
            'total_menunggu_dokumen' => $totalMenungguDokumen,
            'total_dokumen_masuk'    => $totalDokumenMasuk,
            'total_dokumen_lengkap'  => $totalDokumenLengkap,
            'total_dalam_validasi'   => $totalDalamValidasi,
            'total_perlu_revisi'     => $totalPerluRevisi,
            'total_tervalidasi'      => $totalTervalidasi,
        ];
    }

    /**
     * Check document completeness
     */
    private function checkDocumentCompleteness(PengajuanAkreditasi $pengajuan)
    {
        // definisi kebutuhan + alias jenis_dokumen di DB
        $required = [
            'led' => [
                'label' => 'Laporan Evaluasi Diri (LED)',
                'aliases' => ['data_kualitatif', 'draft_borang', 'borang_final'],
                // kalau LED boleh salah satu file, set true
                'any' => true,
            ],
            'suplemen' => [
                'label' => 'Suplemen LED',
                'aliases' => ['data_suplemen', 'suplemen', 'file_suplemen', 'dokumen_pendukung'],
                'any' => true,
            ],
            'lkps' => [
                'label' => 'Laporan Kinerja Program Studi (LKPS)',
                'aliases' => ['data_kuantitatif', 'kuantitatif'],
                'any' => true,
            ],
            'pengesahan' => [
                'label' => 'Lembar Pengesahan LED+Suplemen dan LKPS',
                'aliases' => ['pengesahan', 'lembar_pengesahan'], // jaga-jaga kalau ada 2 versi
                'any' => true,
            ],
            'surat_permohonan' => [
                'label' => 'Surat Permohonan',
                'aliases' => ['surat_permohonan'],
                'any' => true,
            ],
            // kalau bukti_pembayaran memang wajib, tambahkan juga
            // 'bukti_pembayaran' => [...]
        ];

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
