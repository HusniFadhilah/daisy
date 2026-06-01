<?php

namespace App\Http\Controllers\Prodi;

use App\Http\Controllers\Controller;
use App\Mail\PengingatAkreditasiMail;
use App\Models\DegreeLevel;
use App\Models\PengajuanAkreditasi;
use App\Models\PengingatAkreditasi;
use App\Models\StatusAkreditasi;
use App\Models\StudyProgram;
use App\Models\University;
use App\Services\MailDeliveryService;
use App\Services\PengingatAkreditasiService;
use App\Services\RecipientResolverService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PemetaanAkreditasiController extends Controller
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
     * Dashboard pemetaan akreditasi
     */
    public function index(Request $request)
    {
        // Build query
        $query = StudyProgram::nonExample()->with(['university', 'degreeLevel']);

        // ✅ Apply filters dari request
        $this->applyFilters($query, $request);

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_kedaluwarsa');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'tanggal_kedaluwarsa') {
            $query->orderByRaw('CASE WHEN tanggal_kedaluwarsa IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_kedaluwarsa', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $studyPrograms = $query->paginate(20)->appends($request->except('page'));

        // Calculate statistics
        $reminderMonths = (int) $request->get('reminder_months', 7);
        $stats = $this->calculateStatistics($reminderMonths);

        // Get filter data
        $universities = University::where('is_active', true)->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // Get urgent items (kedaluwarsa dalam 6 bulan)
        $urgentPrograms = $studyPrograms->getCollection()
            ->filter(fn($p) => $p->tanggal_kedaluwarsa >= now() && $p->tanggal_kedaluwarsa <= now()->addMonths(6))
            ->take(10);

        $periode = $request->get('periode', '6bulan');
        $filters = $this->buildFilters($request);
        $timelineData = $this->getTimelineData($periode, $filters);
        $calendarData = $this->getCalendarData($filters);

        // ✅ Pass active filters to view
        $activeFilters = $this->getActiveFiltersCount($request);
        $peringkatList = StudyProgram::nonExample()
            ->whereNotNull('peringkat_akreditasi')
            ->distinct()
            ->orderBy('peringkat_akreditasi')
            ->pluck('peringkat_akreditasi');

        return view('asesmen.pemetaan.index', compact(
            'studyPrograms',
            'stats',
            'universities',
            'degreeLevels',
            'urgentPrograms',
            'timelineData',
            'calendarData',
            'periode',
            'activeFilters',
            'peringkatList'
        ));
    }

    private function getActiveFiltersCount(Request $request)
    {
        $count = 0;

        if ($request->filled('year') && !empty($request->year)) $count++;
        if ($request->filled('month') && !empty($request->month)) $count++;
        if ($request->filled('university_id') && !empty($request->university_id)) $count++;
        if ($request->filled('degree_level_id') && !empty($request->degree_level_id)) $count++;
        if ($request->filled('status_kedaluwarsa') && !empty($request->status_kedaluwarsa)) $count++;
        if ($request->filled('peringkat') && !empty($request->peringkat)) $count++;
        if ($request->filled('is_example') && $request->is_example !== 'both') $count++;
        if ($request->filled('search') && trim($request->search) !== '') $count++;

        return $count;
    }

    public function getTimelineAjax(Request $request)
    {
        $periode = $request->get('periode', '6bulan');
        $filters = $this->buildFilters($request);

        $timelineData = $this->getTimelineData($periode, $filters);

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
        $filters = $this->buildFilters($request);
        $calendarData = $this->getCalendarData($filters);

        $html = view('asesmen.pemetaan.components.calendar-grid', ['calendarData' => $calendarData])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function getTableAjax(Request $request)
    {
        // Build query
        $query = StudyProgram::with(['university', 'degreeLevel']);

        // Apply filters
        $this->applyFilters($query, $request);

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
        $urgentQuery = StudyProgram::with(['university', 'degreeLevel'])
            ->where('tanggal_kedaluwarsa', '<=', now()->addMonths(6))
            ->where('tanggal_kedaluwarsa', '>=', now())
            ->orderBy('tanggal_kedaluwarsa');

        $this->applyFilters($urgentQuery, $request);
        $urgentPrograms = $urgentQuery->limit(10)->get();

        $html = view('asesmen.pemetaan.components.table-content', compact('studyPrograms', 'urgentPrograms'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'total' => $studyPrograms->total(),
        ]);
    }

    /**
     * Build filters dari request
     */
    private function buildFilters(Request $request)
    {
        return [
            'year' => $request->input('year', []),
            'month' => $request->input('month', []),
            'university_id' => $request->input('university_id', []),
            'degree_level_id' => $request->input('degree_level_id', []),
            'status_kedaluwarsa' => $request->input('status_kedaluwarsa', []),
            'peringkat' => $request->input('peringkat', []),
            'is_example' => $request->input('is_example', 'both'),
            'search' => $request->input('search', ''),
        ];
    }

    /**
     * Apply filters ke query
     */
    private function applyFilters($query, Request $request)
    {
        // Year filter (multiple)
        if ($request->filled('year')) {
            $years = is_array($request->year) ? $request->year : [$request->year];
            if (!empty($years)) {
                $query->where(function ($q) use ($years) {
                    foreach ($years as $year) {
                        $q->orWhereYear('tanggal_kedaluwarsa', $year);
                    }
                });
            }
        }

        // Month filter (multiple)
        if ($request->filled('month')) {
            $months = is_array($request->month) ? $request->month : [$request->month];
            if (!empty($months)) {
                $query->where(function ($q) use ($months) {
                    foreach ($months as $month) {
                        $q->orWhereMonth('tanggal_kedaluwarsa', $month);
                    }
                });
            }
        }

        // University filter (multiple)
        if ($request->filled('university_id')) {
            $universityIds = is_array($request->university_id) ? $request->university_id : [$request->university_id];
            if (!empty($universityIds)) {
                $query->whereIn('id_university', $universityIds);
            }
        }

        // Degree level filter (multiple)
        if ($request->filled('degree_level_id')) {
            $degreeLevelIds = is_array($request->degree_level_id) ? $request->degree_level_id : [$request->degree_level_id];
            if (!empty($degreeLevelIds)) {
                $query->whereIn('id_level', $degreeLevelIds);
            }
        }

        // Status filter (multiple)
        if ($request->filled('status_kedaluwarsa')) {
            $statuses = is_array($request->status_kedaluwarsa) ? $request->status_kedaluwarsa : [$request->status_kedaluwarsa];
            if (!empty($statuses)) {
                $query->whereIn('status_kedaluwarsa', $statuses);
            }
        }

        // Peringkat filter (multiple)
        if ($request->filled('peringkat')) {
            $peringkats = is_array($request->peringkat) ? $request->peringkat : [$request->peringkat];
            if (!empty($peringkats)) {
                $query->whereIn('peringkat_akreditasi', $peringkats);
            }
        }

        // ✅ NEW: is_example filter
        if ($request->filled('is_example') && $request->is_example !== 'both') {
            $isExample = $request->is_example;
            if ($isExample === 'false') {
                $query->where('is_example', false);
            } elseif ($isExample === 'true') {
                $query->where('is_example', true);
            }
        }
        // Search
        if ($request->filled('search')) {
            if (is_array($request->search) && isset($request->search['value'])) {
                // DataTable search
                $searchValue = $request->search['value'];
                if (!empty($searchValue)) {
                    $query->where('name', 'like', '%' . $searchValue . '%');
                }
            }
        }
    }

    public function getReminderDetailAjax(Request $request)
    {
        $targetMonths = $request->get('target_months'); // null/'' = show all
        $windowMonths = max(1, (int) $request->get('window_months', 1));
        $page         = max(1, (int) $request->get('page', 1));
        $dateStart    = $request->get('date_start');
        $dateEnd      = $request->get('date_end');

        $showAll = $targetMonths === null || $targetMonths === '';

        // ── Tentukan rentang tanggal ──────────────────────────────────
        $start = $end = $base = null;

        if ($dateStart || $dateEnd) {
            // Manual date range — override segalanya
            $start   = $dateStart ? Carbon::parse($dateStart)->startOfDay() : null;
            $end     = $dateEnd   ? Carbon::parse($dateEnd)->endOfDay()     : null;
            $showAll = false;
        } elseif (!$showAll) {
            $base  = now()->copy()->addMonths((int) $targetMonths);
            $start = $base->copy()->startOfMonth();
            $end   = $base->copy()->addMonths($windowMonths - 1)->endOfMonth();
        }

        try {
            $query = StudyProgram::with(['university', 'degreeLevel', 'pengingatAkreditasi']);

            // ── Filter tanggal (skip kalau show all) ─────────────────
            if (!$showAll) {
                if ($start && $end) {
                    $query->whereBetween('tanggal_kedaluwarsa', [$start, $end]);
                } elseif ($start) {
                    $query->where('tanggal_kedaluwarsa', '>=', $start);
                } elseif ($end) {
                    $query->where('tanggal_kedaluwarsa', '<=', $end);
                }
            }

            // ── Search nama prodi / kode / universitas ───────────────
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('university', fn($s) => $s->where('name', 'like', "%{$search}%"));
                });
            }

            // ── Universitas (multiple) ────────────────────────────────
            if ($request->filled('university_id')) {
                $ids = array_filter((array) $request->university_id);
                if (!empty($ids)) {
                    $query->whereIn('id_university', $ids);
                }
            }

            // ── Kategori data ─────────────────────────────────────────
            if ($request->filled('is_example') && $request->is_example !== 'both') {
                $query->where('is_example', $request->is_example === 'true');
            }

            // ── Bulan kedaluwarsa (multiple) ──────────────────────────
            if ($request->filled('month')) {
                $months = array_filter((array) $request->month);
                if (!empty($months)) {
                    $query->where(function ($q) use ($months) {
                        foreach ($months as $m) {
                            $q->orWhereMonth('tanggal_kedaluwarsa', (int) $m);
                        }
                    });
                }
            }

            // ── Tahun kedaluwarsa (multiple) ──────────────────────────
            if ($request->filled('year')) {
                $years = array_filter((array) $request->year);
                if (!empty($years)) {
                    $query->where(function ($q) use ($years) {
                        foreach ($years as $y) {
                            $q->orWhereYear('tanggal_kedaluwarsa', (int) $y);
                        }
                    });
                }
            }

            // ── Peringkat akreditasi (multiple) ──────────────────────
            if ($request->filled('peringkat')) {
                $peringkats = array_filter((array) $request->peringkat);
                if (!empty($peringkats)) {
                    $query->whereIn('peringkat_akreditasi', $peringkats);
                }
            }

            // ── Status kedaluwarsa (multiple) ────────────────────────
            if ($request->filled('status')) {
                $statuses = array_filter((array) $request->status);
                if (!empty($statuses)) {
                    $query->whereIn('status_kedaluwarsa', $statuses);
                }
            }

            $programs = $query->orderBy('tanggal_kedaluwarsa', 'asc')
                ->paginate(20, ['*'], 'page', $page);

            if ($showAll) {
                $label = 'Semua Data';
                // dummy agar view tidak error
                $start = now()->startOfYear();
                $end   = now()->endOfYear();
            } elseif ($dateStart || $dateEnd) {
                $label = \App\Libraries\Date::tglIndo($start)
                    . ' – '
                    . \App\Libraries\Date::tglIndo($end);
            } else {
                $label = $windowMonths === 1
                    ? $base->locale('id')->translatedFormat('F Y')
                    : $base->locale('id')->translatedFormat('F Y')
                    . ' – '
                    . $end->locale('id')->translatedFormat('F Y');
            }

            $html = view('asesmen.pemetaan.components.reminder-detail-table', [
                'programs'     => $programs,
                'label'        => $label,
                'start'        => $start,
                'end'          => $end,
                'targetMonths' => $targetMonths ?? '-',
                'windowMonths' => $windowMonths,
            ])->render();

            return response()->json([
                'success' => true,
                'html'    => $html,
                'meta'    => [
                    'label'        => $label,
                    'start'        => $start?->format('Y-m-d'),
                    'end'          => $end?->format('Y-m-d'),
                    'total'        => $programs->total(),
                    'current_page' => $programs->currentPage(),
                    'last_page'    => $programs->lastPage(),
                    'show_all'     => $showAll,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('getReminderDetailAjax error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function searchProdiAjax(Request $request)
    {
        $q = trim($request->get('q', ''));
        $page = (int) $request->get('page', 1);
        $perPage = 20;

        $universityId = $request->get('university_id');

        $query = StudyProgram::withExample()->with(['degreeLevel', 'university']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            });
        }

        if ($universityId) {
            $query->where('id_university', $universityId);
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
        $stats = StudyProgram::selectRaw("
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

        // ── Distribusi peringkat (dari field study_programs) ──────
        $byPeringkat = StudyProgram::nonExample()
            ->selectRaw('COALESCE(peringkat_akreditasi, "(Tidak Ada)") as peringkat, COUNT(*) as total')
            ->groupBy('peringkat_akreditasi')
            ->orderByRaw("FIELD(peringkat_akreditasi, 'Unggul','Baik Sekali','Baik','C')")
            ->pluck('total', 'peringkat')
            ->toArray();

        // ── Distribusi status kedaluwarsa (field study_programs) ─────
        $byStatusProdi = StudyProgram::nonExample()
            ->selectRaw('COALESCE(status_kedaluwarsa, "(Tidak Ada)") as status, COUNT(*) as total')
            ->groupBy('status_kedaluwarsa')
            ->pluck('total', 'status')
            ->toArray();

        $statusAkreditasiMaster = StatusAkreditasi::orderBy('urutan')
            ->get(['id', 'status', 'makna', 'warna', 'siklus_tahun', 'urutan']);

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
            'by_status_prodi' => $byStatusProdi,
            'status_akreditasi_master' => $statusAkreditasiMaster,

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
        $studyProgram = StudyProgram::with([
            'university',
            'degreeLevel',
        ])->findOrFail($id);

        // Check if ada Permohonan akreditasi yang sedang berjalan
        $activePengajuan = PengajuanAkreditasi::where('id_program_studi', $id)
            ->whereNotIn('status', [PengajuanAkreditasi::STATUS_DITOLAK, PengajuanAkreditasi::STATUS_SELESAI])
            ->where('is_active', 1)
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

    /**
     * Update getTimelineData untuk support filters
     */
    private function getTimelineData($periode, $filters = [])
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

        $startRange = now()->startOfDay();
        $endRange   = now()->copy()->addYears(5)->endOfDay();

        // Build query with filters
        $query = StudyProgram::nonExample()->with(['university', 'degreeLevel'])
            ->whereBetween('tanggal_kedaluwarsa', [$startRange, $endRange]);

        if (!empty($filters['is_example']) && $filters['is_example'] !== 'both') {
            if ($filters['is_example'] === 'false') {
                $query->where('is_example', false);
            } elseif ($filters['is_example'] === 'true') {
                $query->where('is_example', true);
            }
        }

        // Apply other filters (existing code)
        if (!empty($filters['year'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['year'] as $year) {
                    $q->orWhereYear('tanggal_kedaluwarsa', $year);
                }
            });
        }

        // Apply month filter
        if (!empty($filters['month'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['month'] as $month) {
                    $q->orWhereMonth('tanggal_kedaluwarsa', $month);
                }
            });
        }

        // Apply other filters
        if (!empty($filters['university_id'])) {
            $query->whereIn('id_university', $filters['university_id']);
        }
        if (!empty($filters['degree_level_id'])) {
            $query->whereIn('id_level', $filters['degree_level_id']);
        }
        if (!empty($filters['status_kedaluwarsa'])) {
            $query->whereIn('status_kedaluwarsa', $filters['status_kedaluwarsa']);
        }
        if (!empty($filters['peringkat'])) {
            $query->whereIn('peringkat_akreditasi', $filters['peringkat']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $programs = $query->orderBy('tanggal_kedaluwarsa')->get();

        $periodsCount = (int) ceil(60 / $monthsPerPeriod);
        $timeline = [];

        for ($i = 0; $i < $periodsCount; $i++) {
            $startDate = now()->copy()->addMonths($i * $monthsPerPeriod)->startOfDay();
            $endDate   = now()->copy()->addMonths(($i + 1) * $monthsPerPeriod)->endOfDay();

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

    /**
     * Update getCalendarData untuk support filters
     */
    private function getCalendarData($filters = [])
    {
        $calendar = [];

        $startRange = now()->startOfMonth();
        $endRange = now()->copy()->addMonths(12)->endOfMonth();

        // Build query with filters
        $query = StudyProgram::with(['university', 'degreeLevel'])
            ->whereBetween('tanggal_kedaluwarsa', [$startRange, $endRange]);

        if (!empty($filters['is_example']) && $filters['is_example'] !== 'both') {
            if ($filters['is_example'] === 'false') {
                $query->where('is_example', false);
            } elseif ($filters['is_example'] === 'true') {
                $query->where('is_example', true);
            }
        }

        // Apply year filter
        if (!empty($filters['year'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['year'] as $year) {
                    $q->orWhereYear('tanggal_kedaluwarsa', $year);
                }
            });
        }

        // Apply month filter
        if (!empty($filters['month'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['month'] as $month) {
                    $q->orWhereMonth('tanggal_kedaluwarsa', $month);
                }
            });
        }

        // Apply other filters
        if (!empty($filters['university_id'])) {
            $query->whereIn('id_university', $filters['university_id']);
        }
        if (!empty($filters['degree_level_id'])) {
            $query->whereIn('id_level', $filters['degree_level_id']);
        }
        if (!empty($filters['status_kedaluwarsa'])) {
            $query->whereIn('status_kedaluwarsa', $filters['status_kedaluwarsa']);
        }
        if (!empty($filters['peringkat'])) {
            $query->whereIn('peringkat_akreditasi', $filters['peringkat']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $allPrograms = $query->orderBy('tanggal_kedaluwarsa')->get();

        for ($i = 0; $i < 12; $i++) {
            $month = now()->copy()->addMonths($i);
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

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
                'programs' => $monthPrograms->values(),
            ];
        }

        return $calendar;
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

    /**
     * DataTables AJAX endpoint untuk table view
     */
    public function getDataTableAjax(Request $request)
    {
        // Base query
        $query = StudyProgram::with(['university', 'degreeLevel']);

        // Apply custom filters
        $this->applyFilters($query, $request);

        // Get total records before filtering
        $totalRecords = StudyProgram::count();

        // Get filtered records count
        $filteredRecords = $query->count();

        // DataTables search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('code', 'like', "%{$searchValue}%")
                    ->orWhereHas('university', function ($subQ) use ($searchValue) {
                        $subQ->where('name', 'like', "%{$searchValue}%");
                    });
            });

            // Update filtered count after search
            $filteredRecords = $query->count();
        }

        // DataTables ordering
        if ($request->filled('order.0.column')) {
            $columnIndex = $request->input('order.0.column');
            $columnDir = $request->input('order.0.dir', 'asc');

            // Map column index to database column
            $columns = [
                0 => 'id',
                1 => 'name',
                2 => 'id_level',
                3 => 'peringkat_akreditasi',
                4 => 'status_kedaluwarsa',
                5 => 'tanggal_kedaluwarsa',
                6 => 'tanggal_kedaluwarsa',
            ];

            if (isset($columns[$columnIndex])) {
                $orderColumn = $columns[$columnIndex];

                if ($orderColumn === 'tanggal_kedaluwarsa') {
                    $query->orderByRaw('CASE WHEN tanggal_kedaluwarsa IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('tanggal_kedaluwarsa', $columnDir);
                } else {
                    $query->orderBy($orderColumn, $columnDir);
                }
            }
        } else {
            // Default ordering
            $query->orderByRaw('CASE WHEN tanggal_kedaluwarsa IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_kedaluwarsa', 'asc');
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length !== -1) {
            $length = max(1, $length);
        }

        $data = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $formattedData = $data->map(function ($program, $index) use ($start) {
            $daysLeft = $program->tanggal_kedaluwarsa
                ? floor(now()->diffInDays($program->tanggal_kedaluwarsa, false))
                : null;

            $progressPercent = $daysLeft
                ? max(0, min(100, ($daysLeft / (5 * 365)) * 100))
                : 0;

            return [
                'DT_RowId' => 'row_' . $program->id,
                'DT_RowClass' => $daysLeft !== null && $daysLeft <= 90 && $daysLeft >= 0 ? 'table-warning' : '',
                'number' => $start + $index + 1,
                'program_studi' => [
                    'id' => $program->id,
                    'name' => $program->name,
                    'university' => $program->university->name ?? '-',
                ],
                'jenjang' => $program->degreeLevel->alias ?? '-',
                'peringkat' => [
                    'value' => $program->peringkat_akreditasi ?? '-',
                    'class' => $program->getPeringkatClass(),
                ],
                'status' => [
                    'value' => $program->getStatusLabel($daysLeft),
                    'class' => $program->getStatusClass($daysLeft),
                    'is_urgent' => $daysLeft !== null && $daysLeft <= 90 && $daysLeft >= 0,
                ],
                'tanggal_kedaluwarsa' => $program->tanggal_kedaluwarsa
                    ? \App\Libraries\Date::tglIndo($program->tanggal_kedaluwarsa)
                    : '-',
                'tanggal_kedaluwarsa_raw' => $program->tanggal_kedaluwarsa
                    ? $program->tanggal_kedaluwarsa->locale('id')->translatedFormat('Y-m-d')
                    : null,
                'sisa_waktu' => [
                    'days' => $daysLeft,
                    'progress' => $progressPercent,
                    'label' => $program->getSisaWaktuLabel($daysLeft),
                ],
                'can_ajukan' => $program->status_kedaluwarsa != 'Aktif' || ($daysLeft && $daysLeft <= 180),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData,
        ]);
    }

    /**
     * Get urgent programs for sidebar (keep existing AJAX endpoint)
     */
    public function getUrgentProgramsAjax(Request $request)
    {
        $query = StudyProgram::with(['university', 'degreeLevel'])
            ->where('tanggal_kedaluwarsa', '<=', now()->addMonths(6))
            ->where('tanggal_kedaluwarsa', '>=', now())
            ->orderBy('tanggal_kedaluwarsa');

        $this->applyFilters($query, $request);

        $urgentPrograms = $query->limit(10)->get();

        $html = view('asesmen.pemetaan.components.urgent-cards', compact('urgentPrograms'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'count' => $urgentPrograms->count(),
        ]);
    }

    /**
     * Kirim pengingat akreditasi (Langkah 1)
     */
    public function kirimPengingat(Request $request)
    {
        $request->validate([
            'id_program_studi' => 'required|array',
            'id_program_studi.*' => 'exists:study_programs,id',
            'pesan_pengingat' => 'required|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $prodis = StudyProgram::with(['users.activeEmails', 'degreeLevel', 'university'])
                ->whereIn('id', $request->id_program_studi)
                ->get();

            $jumlahBerhasil = 0;
            $jumlahGagal = 0;
            $emailGlobal = [];

            foreach ($prodis as $prodi) {
                try {
                    $pengingat = PengingatAkreditasi::create([
                        'id_program_studi' => $prodi->id,
                        'id_de_pengirim' => auth()->id(),
                        'tahun_akreditasi' => date('Y'),
                        'pesan_pengingat' => $request->pesan_pengingat,
                        'tanggal_dikirim' => now(),
                        'status' => 'belum_direspon',
                    ]);

                    // 1) resolve semua email penerima (unik) untuk prodi ini
                    $recipientEmails = $this->recipientResolver->emailsForUsers($prodi->users);

                    // 2) kirim email (sekali per prodi, bukan per user)
                    $result = $this->mailDelivery->sendToEmails(
                        $recipientEmails,
                        new PengingatAkreditasiMail($prodi, $request->pesan_pengingat),
                        [],    // cc
                        [],    // bcc
                        false  // useQueue (true jika ingin queue)
                    );

                    // 3) simpan daftar email yang benar-benar dituju
                    if (!empty($result['sent_to'])) {
                        $pengingat->update([
                            'email_terkirim_ke' => implode(', ', $result['sent_to']),
                        ]);

                        $emailGlobal = array_merge($emailGlobal, $result['sent_to']);
                    }

                    $jumlahBerhasil++;
                } catch (\Exception $e) {
                    Log::error("Failed to create/send pengingat for prodi {$prodi->id}: " . $e->getMessage());
                    $jumlahGagal++;
                }
            }

            DB::commit();

            // unikkan total email untuk summary
            $emailGlobal = array_values(array_unique(array_filter($emailGlobal)));

            $message = "Pengingat berhasil dikirim ke {$jumlahBerhasil} program studi";
            if ($jumlahGagal > 0) {
                $message .= " ({$jumlahGagal} gagal)";
            }
            $message .= ". Total " . count($emailGlobal) . " email terkirim.";

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in kirimPengingat: " . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
