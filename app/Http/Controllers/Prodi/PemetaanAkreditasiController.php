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
        $query = StudyProgram::with(['university', 'degreeLevel']);

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
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // Get urgent items (kedaluwarsa dalam 6 bulan)
        $urgentPrograms = $studyPrograms->getCollection()
            ->filter(fn($p) => $p->tanggal_kedaluwarsa >= now() && $p->tanggal_kedaluwarsa <= now()->addMonths(6))
            ->take(10);

        $periode = $request->get('periode', '3bulan'); // default 3 bulan
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
        $periode = $request->get('periode', '3bulan');
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
        $query = StudyProgram::with(['university', 'degreeLevel']);

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
        $urgentPrograms = StudyProgram::with(['university', 'degreeLevel'])
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

    /**
     * Calculate statistics
     */
    private function calculateStatistics()
    {
        $today = Carbon::today();

        // 🔥 1 QUERY SAJA
        $stats = StudyProgram::selectRaw("
        COUNT(*) as total,
        SUM(status_kedaluwarsa = 'Aktif') as aktif,
        SUM(status_kedaluwarsa = 'Belum Terakreditasi') as belum_terakreditasi,
        SUM(tanggal_kedaluwarsa IS NOT NULL AND tanggal_kedaluwarsa <= ?) as kedaluwarsa,
        SUM(tanggal_kedaluwarsa BETWEEN ? AND ?) as segera_3_bulan,
        SUM(tanggal_kedaluwarsa BETWEEN ? AND ?) as segera_6_bulan,
        SUM(tanggal_kedaluwarsa BETWEEN ? AND ?) as segera_12_bulan
    ", [
            $today,
            now(),
            now()->addMonths(3),
            now(),
            now()->addMonths(6),
            now(),
            now()->addMonths(12),
        ])->first();

        // Count by peringkat (tetap 1 query terpisah, memang perlu group by)
        $byPeringkat = StudyProgram::select('peringkat_akreditasi', DB::raw('count(*) as total'))
            ->whereNotNull('peringkat_akreditasi')
            ->groupBy('peringkat_akreditasi')
            ->pluck('total', 'peringkat_akreditasi')
            ->toArray();

        return [
            'total' => (int) $stats->total,
            'aktif' => (int) $stats->aktif,
            'kedaluwarsa' => (int) $stats->kedaluwarsa,
            'belum_terakreditasi' => (int) $stats->belum_terakreditasi,
            'segera_3_bulan' => (int) $stats->segera_3_bulan,
            'segera_6_bulan' => (int) $stats->segera_6_bulan,
            'segera_12_bulan' => (int) $stats->segera_12_bulan,
            'by_peringkat' => $byPeringkat,
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

        // Check if ada pengajuan yang sedang berjalan
        $activePengajuan = PengajuanAkreditasi::where('id_program_studi', $id)
            ->whereNotIn('status', ['ditolak', 'pengajuan_completed'])
            ->latest()
            ->first();

        // Get history pengajuan
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

        $selectedPeriode = $periodes[$periode] ?? $periodes['3bulan'];
        $monthsPerPeriod = $selectedPeriode['months'];

        // Range 5 tahun
        $startRange = now()->startOfDay();
        $endRange   = now()->addYears(5)->endOfDay();

        // 🔥 Ambil data SEKALI
        $programs = StudyProgram::with(['university', 'degreeLevel'])
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
        $allPrograms = StudyProgram::with(['university', 'degreeLevel'])
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
        $periode = $request->get('periode', '3bulan');
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
}
