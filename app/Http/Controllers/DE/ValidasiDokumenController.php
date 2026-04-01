<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Mail\Reminder\ReminderValidasiDokumenMail;
use App\Models\AsesmenUserRole;
use App\Models\BorangValidation;
use App\Models\DegreeLevel;
use App\Models\PengajuanAkreditasi;
use App\Models\University;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValidasiDokumenController extends Controller
{
    private RecipientResolverService $recipientResolver;
    private MailDeliveryService $mailDelivery;

    public function __construct(
        RecipientResolverService $recipientResolver,
        MailDeliveryService $mailDelivery
    ) {
        $this->recipientResolver = $recipientResolver;
        $this->mailDelivery = $mailDelivery;
    }

    /**
     * Display list of document validations
     */
    public function index(Request $request)
    {
        // Build query untuk assignments yang sedang validasi
        $statuses = [
            PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
            PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
        ];

        $query = AsesmenUserRole::query()
            ->with([
                'asesmen.pengajuan.studyProgram.university',
                'asesmen.pengajuan.studyProgram.degreeLevel',
                'user',
                'role',
                'borangValidation',
                'asesmen.pengajuan.lastBorangValidationLog', // ✅ 1 log saja
            ])
            ->where('jenis_asesmen', 'dokumen')
            ->whereHas('asesmen.pengajuan.statusLog', function ($q) use ($statuses) {
                $q->whereIn('status_to', $statuses);
            });

        // Filter by status penawaran
        if ($request->filled('status_penawaran')) {
            $query->where('status_penawaran', $request->status_penawaran);
        }

        // Filter by status pekerjaan
        if ($request->filled('status_pekerjaan')) {
            $query->where('status_pekerjaan', $request->status_pekerjaan);
        }

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('asesmen.pengajuan.studyProgram', function ($q) use ($request) {
                $q->where('id_university', $request->university_id);
            });
        }

        // Filter by degree level
        if ($request->filled('degree_level_id')) {
            $query->whereHas('asesmen.pengajuan.studyProgram', function ($q) use ($request) {
                $q->where('id_degree_level', $request->degree_level_id);
            });
        }

        // Filter by validator
        if ($request->filled('validator_id')) {
            $query->where('id_user', $request->validator_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('asesmen.pengajuan', function ($sq) use ($search) {
                    $sq->where('nomor_pengajuan', 'like', "%{$search}%");
                })
                    ->orWhereHas('asesmen.pengajuan.studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $assignments = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // Get validators who have assignments
        $validators = \App\Models\User::whereHas('asesmenUserRoles', function ($q) {
            $q->where('jenis_asesmen', 'dokumen');
        })
            ->whereJsonContains('roles', 'validator')
            ->orderBy('name')
            ->get();

        // AJAX request
        if ($request->ajax()) {
            $html = view('de.validasi-dokumen.components.table-content', compact('assignments'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'stats' => $stats,
            ]);
        }

        // Data untuk modal reminder
        $pendingAssignments = \App\Models\AsesmenUserRole::with(['user', 'asesmen.pengajuan.studyProgram'])
            ->where('jenis_asesmen', 'dokumen')
            ->whereIn('status_penawaran', ['accepted', 'pending'])
            ->whereIn('status_pekerjaan', ['not_started', 'in_progress'])
            ->get();
        $countPendingAssignments = count($pendingAssignments);

        return view('de.validasi-dokumen.index', compact(
            'assignments',
            'stats',
            'universities',
            'degreeLevels',
            'validators',
            'pendingAssignments',
            'countPendingAssignments'
        ));
    }

    /**
     * Show detail validasi
     */
    public function show($id)
    {
        $assignment = AsesmenUserRole::with([
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
            'asesmen.pengajuan.studyProgram.category',
            'asesmen.pengajuan.pengaju',
            'asesmen.pengajuan.latestBorangImport',
            'asesmen.pengajuan.dokumen' => function ($q) {
                $q->where('is_latest', true);
            },
            'user',
            'role',
            'borangValidation',
            'approver'
        ])->findOrFail($id);

        $pengajuan = $assignment->asesmen->pengajuan;
        $validation = $assignment->borangValidation;

        // Get revision details if exists
        $revisionDetails = null;
        if ($validation && $validation->review_led) {
            $revisionDetails = $this->processRevisionDetails($validation);
        }

        // Get validation progress
        $progress = null;
        if ($validation) {
            $progress = $validation->getProgressPercentage();
        }

        return view('de.validasi-dokumen.show', compact(
            'assignment',
            'pengajuan',
            'validation',
            'revisionDetails',
            'progress'
        ));
    }

    /**
     * Kirim reminder ke validator
     */
    public function kirimReminder(Request $request)
    {
        $validated = $request->validate([
            'id_assignment' => 'required|array',
            'id_assignment.*' => 'exists:asesmen_user_roles,id',
            'pesan_reminder' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $sent = 0;

            foreach ($validated['id_assignment'] as $assignmentId) {
                $assignment = AsesmenUserRole::with(['user', 'asesmen.pengajuan'])
                    ->find($assignmentId);

                if (!$assignment) continue;

                // Log reminder
                $assignment->asesmen->pengajuan->statusLog()->create([
                    'status_from' => $assignment->asesmen->pengajuan->status,
                    'status_to' => $assignment->asesmen->pengajuan->status,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => "Pengingat dikirim ke validator {$assignment->user->name}: {$validated['pesan_reminder']}",
                ]);

                $recipientEmails = $this->recipientResolver->emailsForUsers(
                    collect([$assignment->user])
                );

                $result = $this->mailDelivery->sendToEmails(
                    $recipientEmails,
                    new ReminderValidasiDokumenMail($assignment, $validated['pesan_reminder']),
                    [],
                    [],
                    true
                );

                $sent++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Pengingat berhasil dikirim ke {$sent} validator.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim pengingat: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        $statuses = [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
        ];

        $base = AsesmenUserRole::query()
            ->where('jenis_asesmen', 'dokumen')
            ->whereHas('asesmen.pengajuan.statusLog', fn($q) => $q->whereIn('status_to', $statuses));

        $row = (clone $base)->selectRaw('
        COUNT(*) as total,
        SUM(CASE WHEN status_penawaran = "pending" THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status_penawaran = "accepted" THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status_penawaran = "rejected" THEN 1 ELSE 0 END) as rejected,

        SUM(CASE WHEN status_penawaran = "accepted" AND status_pekerjaan = "not_started" THEN 1 ELSE 0 END) as not_started,
        SUM(CASE WHEN status_penawaran = "accepted" AND status_pekerjaan = "in_progress" THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status_penawaran = "accepted" AND status_pekerjaan = "submitted" THEN 1 ELSE 0 END) as submitted,
        SUM(CASE WHEN status_penawaran = "accepted" AND status_pekerjaan = "revision_required" THEN 1 ELSE 0 END) as revision_required,
        SUM(CASE WHEN status_penawaran = "accepted" AND status_pekerjaan = "approved" THEN 1 ELSE 0 END) as approved
    ')->first();

        return [
            'total' => (int) $row->total,
            'pending' => (int) $row->pending,
            'accepted' => (int) $row->accepted,
            'rejected' => (int) $row->rejected,
            'not_started' => (int) $row->not_started,
            'in_progress' => (int) $row->in_progress,
            'submitted' => (int) $row->submitted,
            'revision_required' => (int) $row->revision_required,
            'approved' => (int) $row->approved,
        ];
    }

    /**
     * Process revision details for display
     */
    private function processRevisionDetails($validation)
    {
        $details = [
            'led' => [],
            'suplemen' => [],
            'lkps' => [],
        ];

        // LED
        if ($validation->review_led) {
            foreach ($validation->review_led as $elemenId => $review) {
                if (in_array($review['grade'] ?? null, ['A', 'B'])) {
                    $elemen = \App\Models\ElemenStandar::find($elemenId);
                    $details['led'][] = [
                        'elemen' => $elemen,
                        'grade' => $review['grade'],
                        'catatan' => $review['catatan'] ?? null,
                    ];
                }
            }
        }

        // Suplemen
        if ($validation->review_suplemen) {
            foreach ($validation->review_suplemen as $elemenId => $review) {
                if (in_array($review['grade'] ?? null, ['A', 'B'])) {
                    $elemen = \App\Models\ElemenStandar::find($elemenId);
                    $details['suplemen'][] = [
                        'elemen' => $elemen,
                        'grade' => $review['grade'],
                        'catatan' => $review['catatan'] ?? null,
                    ];
                }
            }
        }

        // LKPS
        if ($validation->review_lkps) {
            foreach ($validation->review_lkps as $indikatorId => $review) {
                if (in_array($review['grade'] ?? null, ['A', 'B'])) {
                    $indikator = \App\Models\Indikator::find($indikatorId);
                    $details['lkps'][] = [
                        'indikator' => $indikator,
                        'grade' => $review['grade'],
                        'catatan' => $review['catatan'] ?? null,
                    ];
                }
            }
        }

        return $details;
    }
}
