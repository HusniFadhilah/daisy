<?php

namespace App\Http\Controllers\DE;

use Illuminate\Http\Request;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;

class ValidasiAKController extends Controller
{
    /**
     * Dashboard monitoring validasi AK
     */
    public function index(Request $request)
    {
        $akStatuses = [
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
        ];

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'ak')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            },
            // opsional: kalau mau tampilkan log AK di table
            'statusLog' => function ($q) use ($akStatuses) {
                $q->whereIn('status_to', $akStatuses)
                    ->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ basis list: pernah masuk fase AK (via status log)
            ->whereExists(function ($q) use ($akStatuses) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $akStatuses);
            })
            ->whereHas('asesmen.asesmenKecukupan'); // Must have AK

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status validasi (berdasar log: "pernah punya status_to")
        if ($request->filled('status_validasi')) {
            $map = [
                'belum_mulai'      => PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                'sedang_penilaian' => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                'sedang_validasi'  => PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                'selesai'          => PengajuanAkreditasi::STATUS_AK_SELESAI,
                'dilaporkan'       => PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
            ];

            if (isset($map[$request->status_validasi])) {
                $target = $map[$request->status_validasi];

                $query->whereExists(function ($q) use ($target) {
                    $q->select(DB::raw(1))
                        ->from('pengajuan_status_log as l2')
                        ->whereColumn('l2.id_pengajuan', 'pengajuan_akreditasi.id')
                        ->where('l2.status_to', $target);
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        $stats = $this->calculateStatistics();
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.validasi-ak.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.validasi-ak.index', compact('pengajuans', 'stats', 'universities'));
    }

    /**
     * Detail monitoring validasi AK
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'ak')
                    ->with(['user', 'role_selected']);
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get total elements to assess
        $totalElements = DB::table('elemen_standar')->count();

        // Get penilaian progress per asesor
        $asesorProgress = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('skor')
            ->groupBy('id_asesor')
            ->select('id_asesor', DB::raw('COUNT(*) as completed'))
            ->get()
            ->keyBy('id_asesor');

        // Get validasi progress per validator
        $validatorProgress = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->whereNotNull('validated_at')
            ->groupBy('validated_by')
            ->select('validated_by', DB::raw('COUNT(DISTINCT id_elemen) as completed'))
            ->get()
            ->keyBy('validated_by');

        // Build detailed progress per user
        $userProgress = [];
        if ($pengajuan->asesmen) {
            foreach ($pengajuan->asesmen->asesmenUserRoles as $assignment) {
                $userId = $assignment->id_user;
                $roleName = $assignment->role_selected->name;

                if ($roleName === 'asesor') {
                    $completed = $asesorProgress[$userId]->completed ?? 0;
                    $pending = $totalElements - $completed;

                    $userProgress[$userId] = [
                        'role' => 'asesor',
                        'total' => $totalElements,
                        'completed' => $completed,
                        'pending' => $pending,
                        'percentage' => $totalElements ? round(($completed / $totalElements) * 100, 1) : 0,
                        'status' => $assignment->status_pekerjaan,
                    ];
                } elseif ($roleName === 'validator') {
                    $completed = $validatorProgress[$userId]->completed ?? 0;
                    $pending = $totalElements - $completed;

                    $userProgress[$userId] = [
                        'role' => 'validator',
                        'total' => $totalElements,
                        'completed' => $completed,
                        'pending' => $pending,
                        'percentage' => $totalElements ? round(($completed / $totalElements) * 100, 1) : 0,
                        'status' => $assignment->status_pekerjaan,
                    ];
                }
            }
        }

        // Get validation details
        // Count elemen yang fully validated (semua penilaian sudah divalidasi)
        $fullyValidatedElements = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->groupBy('id_elemen')
            ->havingRaw('COUNT(*) = COUNT(CASE WHEN validated_at IS NOT NULL THEN 1 END)')
            ->pluck('id_elemen');

        $validationSummary = DB::table('penilaian_elemen_ak')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->selectRaw('
                COUNT(DISTINCT id_elemen) as total_dinilai,
                COUNT(*) as total_penilaian,
                SUM(CASE WHEN validated_at IS NOT NULL THEN 1 ELSE 0 END) as validated_penilaian,
                SUM(CASE WHEN validated_at IS NULL AND skor IS NOT NULL THEN 1 ELSE 0 END) as pending_validation,
                AVG(CASE WHEN skor IS NOT NULL THEN skor ELSE NULL END) as avg_skor
            ')
            ->first();

        // Add fully validated count
        $validationSummary->validated_count = $fullyValidatedElements->count();

        return view('de.validasi-ak.show', compact(
            'pengajuan',
            'userProgress',
            'totalElements',
            'validationSummary'
        ));
    }

    /**
     * Calculate statistics for dashboard
     */
    private function calculateStatistics()
    {
        $akStatuses = [
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
        ];

        $base = PengajuanAkreditasi::query()
            ->whereHas('asesmen.asesmenKecukupan')
            ->whereHas('statusLog', function ($q) use ($akStatuses) {
                $q->whereIn('status_to', $akStatuses);
            });

        // tetap seperti kamu (berdasarkan base)
        $total = (clone $base)->count();

        // ✅ 3 ini pakai snapshot dari pengajuan_akreditasi.status
        $belumMulai = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
            ->count();

        $sedangPenilaian = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
            ->count();

        $sedangValidasi = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_ON_VALIDATION)
            ->count();

        // tetap pakai status log (historical)
        $selesai = (clone $base)->whereHas(
            'statusLog',
            fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AK_SELESAI)
        )->count();

        $dilaporkan = (clone $base)->whereHas(
            'statusLog',
            fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
        )->count();

        return [
            'total' => $total,
            'belum_mulai' => $belumMulai,
            'sedang_penilaian' => $sedangPenilaian,
            'sedang_validasi' => $sedangValidasi,
            'selesai' => $selesai,
            'dilaporkan' => $dilaporkan,
        ];
    }

    /**
     * Get validation timeline (AJAX)
     */
    public function getValidationTimeline($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'asesmen.asesmenUserRoles' => function ($q) {
                    $q->where('jenis_asesmen', 'ak')
                        ->with(['user', 'role_selected']);
                }
            ])->findOrFail($id);

            if (!$pengajuan->asesmen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asesmen not found'
                ], 404);
            }

            // Get penilaian dengan validasi info
            $penilaianTimeline = DB::table('penilaian_elemen_ak as p')
                ->join('elemen_standar as e', 'p.id_elemen', '=', 'e.id')
                ->leftJoin('users as asesor', 'p.id_asesor', '=', 'asesor.id')
                ->leftJoin('users as validator', 'p.validated_by', '=', 'validator.id')
                ->where('p.id_asesmen', $pengajuan->asesmen->id)
                ->select(
                    'e.nama_elemen',
                    'e.kode_elemen',
                    'asesor.name as asesor_name',
                    'p.skor',
                    'p.created_at as penilaian_at',
                    'validator.name as validator_name',
                    'p.validated_at',
                    'p.catatan_validasi'
                )
                ->orderBy('p.validated_at', 'desc')
                ->orderBy('p.created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $penilaianTimeline
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
