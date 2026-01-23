<?php

namespace App\Http\Controllers\Prodi;

use App\Http\Controllers\Controller;
use App\Models\StudyProgram;
use App\Models\University;
use App\Models\DegreeLevel;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PemetaanAkreditasiController extends Controller
{
    /**
     * Dashboard pemetaan akreditasi
     */
    public function index(Request $request)
    {
        // Build query
        $query = StudyProgram::nonExample()->with(['university', 'degreeLevel']);

        // Filter by university
        if ($request->filled('university_id')) {
            $query->where('id_univ', $request->university_id);
        }

        // Filter by degree level
        if ($request->filled('degree_level_id')) {
            $query->where('id_level', $request->degree_level_id);
        }

        // Filter by status kedaluwarsa
        if ($request->filled('status_kedaluwarsa')) {
            $query->where('status_kedaluwarsa', $request->status_kedaluwarsa);
        }

        // Filter by peringkat
        if ($request->filled('peringkat')) {
            $query->where('peringkat_akreditasi', $request->peringkat);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_kedaluwarsa');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'tanggal_kedaluwarsa') {
            $query->orderByRaw('CASE WHEN tanggal_kedaluwarsa IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_kedaluwarsa', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $studyPrograms = $query->paginate(20);

        // Calculate statistics
        $reminderMonths = (int) $request->get('reminder_months', 7);
        $stats = $this->calculateStatistics($reminderMonths);

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // Get urgent items (kedaluwarsa dalam 6 bulan)
        $urgentPrograms = $studyPrograms->getCollection()
            ->filter(fn($p) => $p->tanggal_kedaluwarsa >= now() && $p->tanggal_kedaluwarsa <= now()->addMonths(6))
            ->take(10);

        $periode = $request->get('periode', '6bulan'); // default 6 bulan
        $timelineData = $this->getTimelineData($periode);
        $calendarData = $this->getCalendarData();

        return view('asesmen.pemetaan.index', compact(
            'studyPrograms',
            'stats',
            'universities',
            'degreeLevels',
            'urgentPrograms',
            'timelineData',
            'calendarData',
            'periode'
        ));
    }

    public function getTimelineAjax(Request $request)
    {
        $periode = $request->get('periode', '6bulan');
        $timelineData = $this->getTimelineData($periode);

        // Return HTML rendered untuk timeline cards
        $html = view('asesmen.pemetaan.components.timeline-cards', $timelineData)->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'periode' => $periode,
            'periode_label' => $timelineData['periode_label'],
        ]);
    }

    public function getCalendarAjax(Request $request)
    {
        $calendarData = $this->getCalendarData();

        // Return HTML rendered untuk calendar
        $html = view('asesmen.pemetaan.components.calendar-grid', ['calendarData' => $calendarData])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function getTableAjax(Request $request)
    {
        // Build query
        $query = StudyProgram::nonExample()->with(['university', 'degreeLevel']);

        // Apply filters
        if ($request->filled('university_id')) {
            $query->where('id_univ', $request->university_id);
        }

        if ($request->filled('degree_level_id')) {
            $query->where('id_level', $request->degree_level_id);
        }

        if ($request->filled('status_kedaluwarsa')) {
            $query->where('status_kedaluwarsa', $request->status_kedaluwarsa);
        }

        if ($request->filled('peringkat')) {
            $query->where('peringkat_akreditasi', $request->peringkat);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_kedaluwarsa');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'tanggal_kedaluwarsa') {
            $query->orderByRaw('CASE WHEN tanggal_kedaluwarsa IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_kedaluwarsa', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $studyPrograms = $query->paginate(20);

        // Get urgent programs
        $urgentPrograms = StudyProgram::nonExample()->with(['university', 'degreeLevel'])
            ->where('tanggal_kedaluwarsa', '<=', now()->addMonths(6))
            ->where('tanggal_kedaluwarsa', '>=', now())
            ->orderBy('tanggal_kedaluwarsa')
            ->limit(10)
            ->get();

        // Return HTML rendered untuk table
        $html = view('asesmen.pemetaan.components.table-content', compact('studyPrograms', 'urgentPrograms'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'total' => $studyPrograms->total(),
        ]);
    }

    public function getReminderDetailAjax(Request $request)
    {
        $targetMonths = (int) $request->get('target_months', 7); // target: now + 7 bulan
        $windowMonths = (int) $request->get('window_months', 1); // window: berapa bulan ditampilkan

        // amankan input
        if ($targetMonths < 0) $targetMonths = 0;
        if ($windowMonths < 1) $windowMonths = 1;

        $base = now()->copy()->addMonths($targetMonths);

        // contoh: target=7 (Ags), window=6 -> Ags s/d Jan (6 bulan)
        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->addMonths($windowMonths - 1)->endOfMonth();

        $programs = StudyProgram::nonExample()
            ->with(['university', 'degreeLevel'])
            ->whereBetween('tanggal_kedaluwarsa', [$start, $end])
            ->orderBy('tanggal_kedaluwarsa', 'asc')
            ->paginate(20);

        $label = $windowMonths === 1
            ? $base->locale('id')->translatedFormat('F Y')
            : $base->locale('id')->translatedFormat('F Y') . ' - ' . $end->locale('id')->translatedFormat('F Y');

        $html = view('asesmen.pemetaan.components.reminder-detail-table', [
            'programs'     => $programs,
            'label'        => $label,
            'start'        => $start,
            'end'          => $end,
            'targetMonths' => $targetMonths,
            'windowMonths' => $windowMonths,
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'meta' => [
                'label' => $label,
                'start' => $start->format('Y-m-d'),
                'end'   => $end->format('Y-m-d'),
                'total' => $programs->total(),
            ],
        ]);
    }

    public function searchProdiAjax(Request $request)
    {
        $q = trim($request->get('q', ''));
        $page = (int) $request->get('page', 1);
        $perPage = 20;

        $query = StudyProgram::nonExample()->with(['degreeLevel', 'university']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        $paginator = $query->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        $results = $paginator->getCollection()->map(function ($p) {
            return [
                'id' => $p->id,
                // label yg tampil di select2
                'text' => $p->full_name
                    ?? ($p->name . ' (' . ($p->degreeLevel->alias ?? '-') . ') - ' . ($p->university->name ?? '-')),
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $paginator->hasMorePages(),
            ],
        ]);
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(int $reminderMonths = 7)
    {
        $now = now();

        $threeMonthsLater  = $now->copy()->addMonths(3);
        $sevenMonthsLater  = $now->copy()->addMonths(7);
        $twelveMonthsLater = $now->copy()->addMonths(12);

        // ✅ Baru: target bulan (now + N bulan) -> window 1 bulan penuh
        $targetMonth   = $now->copy()->addMonths($reminderMonths);
        $targetStart   = $targetMonth->copy()->startOfMonth();
        $targetEnd     = $targetMonth->copy()->endOfMonth();

        // 🔥 1 QUERY
        $stats = StudyProgram::nonExample()->selectRaw("
        COUNT(*) as total,
        SUM(CASE WHEN status_kedaluwarsa = 'Aktif' THEN 1 ELSE 0 END) as aktif,
        SUM(CASE WHEN status_kedaluwarsa = 'Belum Terakreditasi' THEN 1 ELSE 0 END) as belum_terakreditasi,
        SUM(CASE WHEN status_kedaluwarsa = 'Kedaluwarsa' THEN 1 ELSE 0 END) as kedaluwarsa,

        -- existing (range dari sekarang)
        SUM(CASE WHEN tanggal_kedaluwarsa BETWEEN ? AND ? THEN 1 ELSE 0 END) as segera_3_bulan,
        SUM(CASE WHEN tanggal_kedaluwarsa BETWEEN ? AND ? THEN 1 ELSE 0 END) as segera_7_bulan,
        SUM(CASE WHEN tanggal_kedaluwarsa BETWEEN ? AND ? THEN 1 ELSE 0 END) as segera_12_bulan,

        -- ✅ baru (khusus bulan target)
        SUM(CASE WHEN tanggal_kedaluwarsa BETWEEN ? AND ? THEN 1 ELSE 0 END) as pengingat_bulan_target
    ", [
            $now,
            $threeMonthsLater,
            $now,
            $sevenMonthsLater,
            $now,
            $twelveMonthsLater,

            $targetStart,
            $targetEnd,
        ])->first();

        // group by peringkat (tetap)
        $byPeringkat = StudyProgram::nonExample()
            ->select('peringkat_akreditasi', DB::raw('count(*) as total'))
            ->whereNotNull('peringkat_akreditasi')
            ->groupBy('peringkat_akreditasi')
            ->pluck('total', 'peringkat_akreditasi')
            ->toArray();

        // ✅ return lama tetap ADA, tidak dihapus
        return [
            'total' => (int) $stats->total,
            'aktif' => (int) $stats->aktif,
            'kedaluwarsa' => (int) $stats->kedaluwarsa,
            'belum_terakreditasi' => (int) $stats->belum_terakreditasi,
            'segera_3_bulan' => (int) $stats->segera_3_bulan,
            'segera_7_bulan' => (int) $stats->segera_7_bulan,
            'segera_12_bulan' => (int) $stats->segera_12_bulan,
            'by_peringkat' => $byPeringkat,

            // ✅ tambahan baru (untuk kebutuhan "bulan target")
            'pengingat_bulan_target' => (int) $stats->pengingat_bulan_target,
            'pengingat' => [
                'months_ahead' => $reminderMonths,
                'target_month_label' => $targetMonth->locale('id')->translatedFormat('F Y'),
                'start' => $targetStart,
                'end' => $targetEnd,
            ],
        ];
    }

    /**
     * Detail program studi
     */
    public function show($id)
    {
        $studyProgram = StudyProgram::nonExample()->with([
            'university',
            'degreeLevel',
        ])->findOrFail($id);

        // Check if ada Permohonan akreditasi yang sedang berjalan
        $activePengajuan = PengajuanAkreditasi::where('id_program_studi', $id)
            ->whereNotIn('status', ['ditolak', 'lanjut_ke_ak'])
            ->latest()
            ->first();

        // Get history Permohonan akreditasi
        $historyPengajuan = PengajuanAkreditasi::where('id_program_studi', $id)
            ->with(['pengaju', 'deskEvaluator'])
            ->latest()
            ->get();

        return view('asesmen.pemetaan.show', compact(
            'studyProgram',
            'activePengajuan',
            'historyPengajuan'
        ));
    }

    private function getTimelineData($periode)
    {
        $periodes = [
            '1bulan'  => ['months' => 1,  'label' => 'Per Bulan'],
            '3bulan'  => ['months' => 3,  'label' => 'Per 3 Bulan (Triwulan)'],
            '4bulan'  => ['months' => 4,  'label' => 'Per 4 Bulan (Caturwulan)'],
            '6bulan'  => ['months' => 6,  'label' => 'Per 6 Bulan (Semester)'],
            '12bulan' => ['months' => 12, 'label' => 'Per Tahun'],
        ];

        $selectedPeriode = $periodes[$periode] ?? $periodes['6bulan'];
        $monthsPerPeriod = $selectedPeriode['months'];

        // Range 5 tahun
        $startRange = now()->startOfDay();
        $endRange   = now()->copy()->addYears(5)->endOfDay();

        // 🔥 Ambil data SEKALI
        $programs = StudyProgram::nonExample()->with(['university', 'degreeLevel'])
            ->whereBetween('tanggal_kedaluwarsa', [$startRange, $endRange])
            ->orderBy('tanggal_kedaluwarsa')
            ->get();

        // Jumlah periode
        $periodsCount = (int) ceil(60 / $monthsPerPeriod);
        $timeline = [];

        for ($i = 0; $i < $periodsCount; $i++) {

            $startDate = now()->copy()->addMonths($i * $monthsPerPeriod)->startOfDay();
            $endDate   = now()->copy()->addMonths(($i + 1) * $monthsPerPeriod)->endOfDay();

            // Filter dari collection (bukan query)
            $periodPrograms = $programs->filter(
                fn($p) =>
                $p->tanggal_kedaluwarsa >= $startDate &&
                    $p->tanggal_kedaluwarsa <= $endDate
            );

            $timeline[] = [
                'period'     => $i + 1,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'label'      => $this->getPeriodLabel($startDate, $endDate, $monthsPerPeriod),
                'count'      => $periodPrograms->count(),
                'programs'   => $periodPrograms->values(),
                'is_urgent'  => $i < 2,
            ];
        }

        return [
            'selected_periode' => $periode,
            'periode_label'    => $selectedPeriode['label'],
            'timeline'         => $timeline,
        ];
    }

    private function getPeriodLabel($start, $end, $months)
    {
        if ($months == 1) {
            return $start->locale('id')->translatedFormat('F Y');
        } elseif ($months == 3) {
            $quarter = ceil($start->month / 3);
            return "Triwulan {$quarter} - {$start->year}";
        } elseif ($months == 4) {
            $caturwulan = ceil($start->month / 4);
            return "Caturwulan {$caturwulan} - {$start->year}";
        } elseif ($months == 6) {
            $semester = ceil($start->month / 6);
            return "Semester {$semester} - {$start->year}";
        } elseif ($months == 12) {
            return "Tahun {$start->year}";
        }

        return $start->locale('id')->translatedFormat('M Y') . ' - ' . $end->locale('id')->translatedFormat('M Y');
    }

    private function getCalendarData()
    {
        $calendar = [];

        $startRange = now()->startOfMonth();
        $endRange = now()->copy()->addMonths(12)->endOfMonth();

        // 🔥 Ambil semua program sekali saja
        $allPrograms = StudyProgram::nonExample()->with(['university', 'degreeLevel'])
            ->whereBetween('tanggal_kedaluwarsa', [$startRange, $endRange])
            ->orderBy('tanggal_kedaluwarsa')
            ->get();

        for ($i = 0; $i < 12; $i++) {
            $month = now()->copy()->addMonths($i);
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            // Filter dari collection, bukan query
            $monthPrograms = $allPrograms->filter(
                fn($p) =>
                $p->tanggal_kedaluwarsa >= $startDate &&
                    $p->tanggal_kedaluwarsa <= $endDate
            );

            $calendar[] = [
                'month' => $month->locale('id')->translatedFormat('F Y'),
                'month_num' => $month->month,
                'year' => $month->year,
                'count' => $monthPrograms->count(),
                'programs' => $monthPrograms->values(), // reindex collection
            ];
        }

        return $calendar;
    }

    public function getTimelineView(Request $request)
    {
        $periode = $request->get('periode', '6bulan');
        $timelineData = $this->getTimelineData($periode);

        return response()->json($timelineData);
    }

    /**
     * Export to Excel
     */
    public function export(Request $request)
    {
        // TODO: Implement Excel export
        return response()->json(['message' => 'Export feature coming soon']);
    }

    // ========== TESTING METHODS (NO AUTH REQUIRED) ==========

    /**
     * Get statistics for testing (Postman)
     * Endpoint: GET /api/test/pemetaan/stats
     */
    public function getStatsForTesting()
    {
        $stats = $this->calculateStatistics();

        return response()->json([
            'success' => true,
            'message' => 'Statistik berhasil diambil',
            'data' => [
                'total_program_studi' => $stats['total'],
                'status' => [
                    'aktif' => $stats['aktif'],
                    'kedaluwarsa' => $stats['kedaluwarsa'],
                    'belum_terakreditasi' => $stats['belum_terakreditasi'],
                ],
                'segera_kedaluwarsa' => [
                    'dalam_3_bulan' => $stats['segera_3_bulan'],
                    'dalam_7_bulan' => $stats['segera_7_bulan'],
                    'dalam_12_bulan' => $stats['segera_12_bulan'],
                ],
                'by_peringkat' => $stats['by_peringkat'],
                'persentase' => [
                    'aktif' => $stats['total'] > 0 ? round(($stats['aktif'] / $stats['total']) * 100, 2) : 0,
                    'kedaluwarsa' => $stats['total'] > 0 ? round(($stats['kedaluwarsa'] / $stats['total']) * 100, 2) : 0,
                    'belum_terakreditasi' => $stats['total'] > 0 ? round(($stats['belum_terakreditasi'] / $stats['total']) * 100, 2) : 0,
                ],
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get timeline data for testing (Postman)
     * Endpoint: GET /api/test/pemetaan/timeline/{periode}
     * Periode options: 1bulan, 3bulan (default), 4bulan, 6bulan, 12bulan
     */
    public function getTimelineForTesting($periode = '6bulan')
    {
        $timelineData = $this->getTimelineData($periode);

        // Transform untuk response yang lebih bersih
        $transformedTimeline = collect($timelineData['timeline'])->map(function ($item) {
            return [
                'period' => $item['period'],
                'label' => $item['label'],
                'start_date' => $item['start_date']->format('Y-m-d'),
                'end_date' => $item['end_date']->format('Y-m-d'),
                'count' => $item['count'],
                'is_urgent' => $item['is_urgent'],
                'programs' => $item['programs']->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'university' => $p->university->name,
                    'degree_level' => $p->degreeLevel->name,
                    'peringkat' => $p->peringkat_akreditasi,
                    'tanggal_kedaluwarsa' => $p->tanggal_kedaluwarsa?->format('Y-m-d'),
                    'status' => $p->status_kedaluwarsa,
                ])->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Timeline data berhasil diambil',
            'data' => [
                'selected_periode' => $timelineData['selected_periode'],
                'periode_label' => $timelineData['periode_label'],
                'total_periods' => count($timelineData['timeline']),
                'timeline' => $transformedTimeline,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get calendar data for testing (Postman)
     * Endpoint: GET /api/test/pemetaan/calendar
     */
    public function getCalendarForTesting()
    {
        $calendarData = $this->getCalendarData();

        // Transform untuk response yang lebih bersih
        $transformedCalendar = collect($calendarData)->map(function ($item) {
            return [
                'month' => $item['month'],
                'month_num' => $item['month_num'],
                'year' => $item['year'],
                'count' => $item['count'],
                'programs' => $item['programs']->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'university' => $p->university->name,
                    'degree_level' => $p->degreeLevel->name,
                    'peringkat' => $p->peringkat_akreditasi,
                    'tanggal_kedaluwarsa' => $p->tanggal_kedaluwarsa?->format('Y-m-d'),
                    'status' => $p->status_kedaluwarsa,
                ])->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Calendar data berhasil diambil (12 bulan ke depan)',
            'data' => [
                'total_months' => count($calendarData),
                'calendar' => $transformedCalendar,
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get programs list with filters for testing (Postman)
     * Endpoint: GET /api/test/pemetaan/programs
     * Query params: status, peringkat, university_id, degree_level_id, search, limit
     */
    public function getProgramsForTesting(Request $request)
    {
        $query = StudyProgram::with(['university', 'degreeLevel', 'category']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status_kedaluwarsa', $request->status);
        }

        if ($request->filled('peringkat')) {
            $query->where('peringkat_akreditasi', $request->peringkat);
        }

        if ($request->filled('university_id')) {
            $query->where('id_univ', $request->university_id);
        }

        if ($request->filled('degree_level_id')) {
            $query->where('id_level', $request->degree_level_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_kedaluwarsa');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'tanggal_kedaluwarsa') {
            $query->orderByRaw('CASE WHEN tanggal_kedaluwarsa IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_kedaluwarsa', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Limit
        $limit = $request->get('limit', 10);
        $programs = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'message' => 'Program studi berhasil diambil',
            'data' => [
                'total' => $programs->count(),
                'limit' => $limit,
                'filters_applied' => [
                    'status' => $request->status,
                    'peringkat' => $request->peringkat,
                    'university_id' => $request->university_id,
                    'degree_level_id' => $request->degree_level_id,
                    'search' => $request->search,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder,
                ],
                'programs' => $programs->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                    'university' => [
                        'id' => $p->university->id,
                        'name' => $p->university->name,
                        'code' => $p->university->code,
                    ],
                    'degree_level' => [
                        'id' => $p->degreeLevel->id,
                        'name' => $p->degreeLevel->name,
                        'code' => $p->degreeLevel->code,
                    ],
                    'category' => $p->category ? [
                        'id' => $p->category->id,
                        'name' => $p->category->name,
                        'code' => $p->category->code,
                    ] : null,
                    'bentuk_pt' => $p->bentuk_pt,
                    'email' => $p->email,
                    'akreditasi' => [
                        'peringkat' => $p->peringkat_akreditasi,
                        'tanggal_kedaluwarsa' => $p->tanggal_kedaluwarsa?->format('Y-m-d'),
                        'status' => $p->status_kedaluwarsa,
                        'hari_tersisa' => $p->tanggal_kedaluwarsa ? now()->diffInDays($p->tanggal_kedaluwarsa, false) : null,
                    ],
                ])->toArray(),
            ],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
