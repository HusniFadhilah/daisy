<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\AsesmenUserRole;
use App\Models\BorangValidation;
use App\Models\University;
use App\Models\DegreeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ValidasiDokumenController extends Controller
{
    /**
     * Display list of document validations
     */
    public function index(Request $request)
    {
        // Build query untuk assignments yang sedang validasi
        $query = AsesmenUserRole::with([
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
            'user',
            'role',
            'borangValidation'
        ])
            ->where('jenis_asesmen', 'dokumen')
            ->whereHas('asesmen.pengajuan', function ($q) {
                $q->whereIn('status', [
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                    PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                    PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                ]);
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

        return view('de.validasi-dokumen.index', compact(
            'assignments',
            'stats',
            'universities',
            'degreeLevels',
            'validators'
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
                    'keterangan' => "Reminder dikirim ke validator {$assignment->user->name}: {$validated['pesan_reminder']}",
                ]);

                // TODO: Send email
                // Mail::to($assignment->user->email)->send(new ReminderValidasiDokumen($assignment, $validated['pesan_reminder']));

                $sent++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Reminder berhasil dikirim ke {$sent} validator.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim reminder: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics()
    {
        $baseQuery = AsesmenUserRole::where('jenis_asesmen', 'dokumen')
            ->whereHas('asesmen.pengajuan', function ($q) {
                $q->whereIn('status', [
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
                    PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
                    PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                    PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                    PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                ]);
            });

        return [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status_penawaran', 'pending')->count(),
            'accepted' => (clone $baseQuery)->where('status_penawaran', 'accepted')->count(),
            'rejected' => (clone $baseQuery)->where('status_penawaran', 'rejected')->count(),

            'not_started' => (clone $baseQuery)
                ->where('status_penawaran', 'accepted')
                ->where('status_pekerjaan', 'not_started')
                ->count(),

            'in_progress' => (clone $baseQuery)
                ->where('status_penawaran', 'accepted')
                ->where('status_pekerjaan', 'in_progress')
                ->count(),

            'submitted' => (clone $baseQuery)
                ->where('status_penawaran', 'accepted')
                ->where('status_pekerjaan', 'submitted')
                ->count(),

            'revision_required' => (clone $baseQuery)
                ->where('status_penawaran', 'accepted')
                ->where('status_pekerjaan', 'revision_required')
                ->count(),

            'approved' => (clone $baseQuery)
                ->where('status_penawaran', 'accepted')
                ->where('status_pekerjaan', 'approved')
                ->count(),
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
