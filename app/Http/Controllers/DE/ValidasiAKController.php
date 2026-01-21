<?php

namespace App\Http\Controllers\DE;

use Illuminate\Http\Request;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ValidasiAKController extends Controller
{
    /**
     * Dashboard monitoring validasi AK
     */
    public function index(Request $request)
    {
        // Build query - pengajuan yang sedang/sudah validasi AK
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'ak')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            }
        ])
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
                PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                PengajuanAkreditasi::STATUS_AK_SELESAI,
                PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
            ])
            ->whereHas('asesmen.asesmenKecukupan'); // Must have AK

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status validasi
        if ($request->filled('status_validasi')) {
            switch ($request->status_validasi) {
                case 'belum_mulai':
                    // AK assigned tapi belum ada yang mulai penilaian
                    $query->where('status', PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED);
                    break;
                case 'sedang_penilaian':
                    // Asesor sedang menilai
                    $query->where('status', PengajuanAkreditasi::STATUS_AK_IN_PROGRESS);
                    break;
                case 'sedang_validasi':
                    // Validator sedang validasi
                    $query->where('status', PengajuanAkreditasi::STATUS_AK_ON_VALIDATION);
                    break;
                case 'selesai':
                    // Validasi selesai
                    $query->where('status', PengajuanAkreditasi::STATUS_AK_SELESAI);
                    break;
                case 'dilaporkan':
                    // Sudah dilaporkan
                    $query->where('status', PengajuanAkreditasi::STATUS_AK_DILAPORKAN);
                    break;
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

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get universities for filter
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();

        // Check if AJAX
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.validasi-ak.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.validasi-ak.index', compact(
            'pengajuans',
            'stats',
            'universities'
        ));
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
        $base = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            PengajuanAkreditasi::STATUS_AK_SELESAI,
            PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
        ])->whereHas('asesmen.asesmenKecukupan');

        $total = (clone $base)->count();

        $belumMulai = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
            ->count();

        $sedangPenilaian = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
            ->count();

        $sedangValidasi = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_ON_VALIDATION)
            ->count();

        $selesai = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_SELESAI)
            ->count();

        $dilaporkan = (clone $base)
            ->where('status', PengajuanAkreditasi::STATUS_AK_DILAPORKAN)
            ->count();

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
