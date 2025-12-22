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

        // Filter by status kadaluarsa
        if ($request->filled('status_kadaluarsa')) {
            $query->where('status_kadaluarsa', $request->status_kadaluarsa);
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
        $sortBy = $request->get('sort_by', 'tanggal_kadaluarsa');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'tanggal_kadaluarsa') {
            $query->orderByRaw('CASE WHEN tanggal_kadaluarsa IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_kadaluarsa', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $studyPrograms = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // Get urgent items (kadaluarsa dalam 6 bulan)
        $urgentPrograms = StudyProgram::with(['university', 'degreeLevel'])
            ->where('tanggal_kadaluarsa', '<=', now()->addMonths(6))
            ->where('tanggal_kadaluarsa', '>=', now())
            ->orderBy('tanggal_kadaluarsa')
            ->limit(10)
            ->get();

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

        if ($request->filled('status_kadaluarsa')) {
            $query->where('status_kadaluarsa', $request->status_kadaluarsa);
        }

        if ($request->filled('peringkat')) {
            $query->where('peringkat_akreditasi', $request->peringkat);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_kadaluarsa');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'tanggal_kadaluarsa') {
            $query->orderByRaw('CASE WHEN tanggal_kadaluarsa IS NULL THEN 1 ELSE 0 END')
                ->orderBy('tanggal_kadaluarsa', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $studyPrograms = $query->paginate(20);

        // Get urgent programs
        $urgentPrograms = StudyProgram::with(['university', 'degreeLevel'])
            ->where('tanggal_kadaluarsa', '<=', now()->addMonths(6))
            ->where('tanggal_kadaluarsa', '>=', now())
            ->orderBy('tanggal_kadaluarsa')
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
        $total = StudyProgram::count();
        $aktif = StudyProgram::where('status_kadaluarsa', 'Aktif')->count();
        $kadaluarsa = StudyProgram::whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', Carbon::today())
            ->count();
        $belumTerakreditasi = StudyProgram::where('status_kadaluarsa', 'Belum Terakreditasi')->count();

        // Programs expiring in 3 months
        $segera3Bulan = StudyProgram::where('tanggal_kadaluarsa', '<=', now()->addMonths(3))
            ->where('tanggal_kadaluarsa', '>=', now())
            ->count();

        // Programs expiring in 6 months
        $segera6Bulan = StudyProgram::where('tanggal_kadaluarsa', '<=', now()->addMonths(6))
            ->where('tanggal_kadaluarsa', '>=', now())
            ->count();

        // Programs expiring in 12 months
        $segera12Bulan = StudyProgram::where('tanggal_kadaluarsa', '<=', now()->addMonths(12))
            ->where('tanggal_kadaluarsa', '>=', now())
            ->count();

        // Count by peringkat
        $byPeringkat = StudyProgram::select('peringkat_akreditasi', DB::raw('count(*) as total'))
            ->whereNotNull('peringkat_akreditasi')
            ->groupBy('peringkat_akreditasi')
            ->pluck('total', 'peringkat_akreditasi')
            ->toArray();

        return [
            'total' => $total,
            'aktif' => $aktif,
            'kadaluarsa' => $kadaluarsa,
            'belum_terakreditasi' => $belumTerakreditasi,
            'segera_3_bulan' => $segera3Bulan,
            'segera_6_bulan' => $segera6Bulan,
            'segera_12_bulan' => $segera12Bulan,
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
            ->whereNotIn('status', ['ditolak', 'lanjut_ke_ak'])
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
        // Define periode ranges
        $periodes = [
            '1bulan' => ['months' => 1, 'label' => 'Per Bulan'],
            '3bulan' => ['months' => 3, 'label' => 'Per 3 Bulan (Triwulan)'],
            '4bulan' => ['months' => 4, 'label' => 'Per 4 Bulan (Caturwulan)'],
            '6bulan' => ['months' => 6, 'label' => 'Per 6 Bulan (Semester)'],
            '12bulan' => ['months' => 12, 'label' => 'Per Tahun'],
        ];

        $selectedPeriode = $periodes[$periode] ?? $periodes['3bulan'];
        $monthsPerPeriod = $selectedPeriode['months'];

        // Generate periods for next 5 years
        $periodsCount = (int)ceil(60 / $monthsPerPeriod);
        $timeline = [];

        for ($i = 0; $i < $periodsCount; $i++) {
            $startDate = now()->addMonths($i * $monthsPerPeriod);
            $endDate = now()->addMonths(($i + 1) * $monthsPerPeriod)->subDay();

            // Count programs expiring in this period
            $count = StudyProgram::whereBetween('tanggal_kadaluarsa', [$startDate, $endDate])
                ->count();

            // Get actual programs
            $programs = StudyProgram::with(['university', 'degreeLevel'])
                ->whereBetween('tanggal_kadaluarsa', [$startDate, $endDate])
                ->orderBy('tanggal_kadaluarsa')
                ->get();

            $timeline[] = [
                'period' => $i + 1,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'label' => $this->getPeriodLabel($startDate, $endDate, $monthsPerPeriod),
                'count' => $count,
                'programs' => $programs,
                'is_urgent' => $i < 2, // First 2 periods are urgent
            ];
        }

        return [
            'selected_periode' => $periode,
            'periode_label' => $selectedPeriode['label'],
            'timeline' => $timeline,
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

        for ($i = 0; $i < 12; $i++) {
            $month = now()->addMonths($i);
            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            $count = StudyProgram::whereBetween('tanggal_kadaluarsa', [$startDate, $endDate])
                ->count();

            $programs = StudyProgram::with(['university', 'degreeLevel'])
                ->whereBetween('tanggal_kadaluarsa', [$startDate, $endDate])
                ->orderBy('tanggal_kadaluarsa')
                ->get();

            $calendar[] = [
                'month' => $month->locale('id')->translatedFormat('F Y'),
                'month_num' => $month->month,
                'year' => $month->year,
                'count' => $count,
                'programs' => $programs,
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
