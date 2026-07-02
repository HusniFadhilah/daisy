<?php

namespace App\Http\Controllers\Public;

use Carbon\Carbon;
use App\Models\University;
use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;

class DirektoriProdiController extends Controller
{
    // =========================================================
    // HALAMAN UTAMA
    // =========================================================
    public function index(Request $request)
    {
        $baseQuery = StudyProgram::query()
            ->where('study_programs.is_example', false)
            ->where('study_programs.is_active', true);

        $total = $baseQuery->count();

        // ── Statistik ringkasan ──
        $now = Carbon::now();

        $stats = [
            'total'              => $total,
            'total_universitas'  => University::where('universities.is_example', false)->where('universities.is_active', true)->count(),
            'aktif'              => (clone $baseQuery)->where('status_kedaluwarsa', 'Aktif')->count(),
            'kedaluwarsa'        => (clone $baseQuery)->where('status_kedaluwarsa', 'Kedaluwarsa')->count(),
            'belum_terakreditasi' => (clone $baseQuery)->where('status_kedaluwarsa', 'Belum Terakreditasi')->count(),
            'unggul'             => (clone $baseQuery)->where('peringkat_akreditasi', 'Unggul')->count(),
            'baik_sekali'        => (clone $baseQuery)->where('peringkat_akreditasi', 'Baik Sekali')->count(),
            'baik'               => (clone $baseQuery)->where('peringkat_akreditasi', 'Baik')->count(),
            'segera_berakhir'    => (clone $baseQuery)
                ->where('status_kedaluwarsa', 'Aktif')
                ->whereBetween('tanggal_kedaluwarsa', [$now, $now->copy()->addMonths(6)])
                ->count(),
        ];

        // ── Chart: Distribusi Peringkat ──
        $peringkatRaw = (clone $baseQuery)
            ->selectRaw('peringkat_akreditasi, COUNT(*) as total')
            ->groupBy('peringkat_akreditasi')
            ->pluck('total', 'peringkat_akreditasi')
            ->toArray();

        $peringkatOrder = ['Unggul', 'Baik Sekali', 'Baik', 'C'];
        $chartPeringkat = [];
        foreach ($peringkatOrder as $p) {
            if (isset($peringkatRaw[$p])) {
                $chartPeringkat[$p] = $peringkatRaw[$p];
            }
        }
        $sisaPeringkat = array_diff_key($peringkatRaw, $chartPeringkat);
        if (!empty($sisaPeringkat)) {
            $chartPeringkat['(Lainnya)'] = array_sum($sisaPeringkat);
        }
        $nullCount = (clone $baseQuery)->whereNull('peringkat_akreditasi')->count();
        if ($nullCount > 0) {
            $chartPeringkat['(Belum)'] = $nullCount;
        }

        // ── Chart: Sebaran Jenjang ──
        $chartJenjang = StudyProgram::query()
            ->where('study_programs.is_example', false)
            ->where('study_programs.is_active', true)
            ->join('degree_levels', 'study_programs.id_degree_level', '=', 'degree_levels.id')
            ->selectRaw('degree_levels.alias, COUNT(*) as total')
            ->groupBy('degree_levels.alias')
            ->orderByDesc('total')
            ->pluck('total', 'alias')
            ->toArray();

        // ── Chart: Top 10 Universitas ──
        $chartUniversitas = StudyProgram::query()
            ->where('study_programs.is_example', false)
            ->where('study_programs.is_active', true)
            ->join('universities', 'study_programs.id_university', '=', 'universities.id')
            ->selectRaw('universities.name, COUNT(*) as total')
            ->groupBy('universities.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return ['name' => $row->name, 'total' => $row->total];
            })
            ->values()
            ->toArray();

        // ── Chart: Tren Kedaluwarsa 12 bulan ke depan ──
        $chartTren = [];
        for ($i = 0; $i < 12; $i++) {
            $month = $now->copy()->addMonths($i);
            $count = (clone $baseQuery)
                ->whereYear('tanggal_kedaluwarsa', $month->year)
                ->whereMonth('tanggal_kedaluwarsa', $month->month)
                ->count();
            $chartTren[] = [
                'label' => $month->translatedFormat('M Y'),
                'total' => $count,
            ];
        }

        // ── Chart: Status Akreditasi ──
        $chartStatus = [
            'Aktif'              => $stats['aktif'],
            'Kedaluwarsa'        => $stats['kedaluwarsa'],
            'Belum Terakreditasi' => $stats['belum_terakreditasi'],
        ];

        // ── Chart: Rumpun ──
        $chartRumpun = StudyProgram::query()
            ->where('study_programs.is_example', false)
            ->where('study_programs.is_active', true)
            ->selectRaw('rumpun, COUNT(*) as total')
            ->whereNotNull('rumpun')
            ->groupBy('rumpun')
            ->pluck('total', 'rumpun')
            ->toArray();

        // ── Filter data untuk select ──
        $universities = University::where('universities.is_example', false)
            ->where('universities.is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $degreeLevels = DegreeLevel::where('degree_levels.is_active', true)
            ->orderBy('alias')
            ->get(['id', 'alias', 'name']);

        $peringkatList = StudyProgram::where('study_programs.is_example', false)
            ->where('study_programs.is_active', true)
            ->whereNotNull('peringkat_akreditasi')
            ->distinct()
            ->orderBy('peringkat_akreditasi')
            ->pluck('peringkat_akreditasi');

        return view('public.direktori-prodi', [
            'stats'        => $stats,
            'universities' => $universities,
            'degreeLevels' => $degreeLevels,
            'peringkatList' => $peringkatList,
            'chartData'    => [
                'peringkat'   => $chartPeringkat,
                'jenjang'     => $chartJenjang,
                'universitas' => $chartUniversitas,
                'tren'        => $chartTren,
                'status'      => $chartStatus,
                'rumpun'      => $chartRumpun,
            ],
        ]);
    }

    // =========================================================
    // AJAX DATATABLE
    // =========================================================
    public function ajax(Request $request)
    {
        $hasNoSkColumn = Schema::hasColumn('study_programs', 'no_sk');

        $query = StudyProgram::query()
            ->where('study_programs.is_example', false)
            ->where('study_programs.is_active', true)
            ->join('universities', 'study_programs.id_university', '=', 'universities.id')
            ->join('degree_levels', 'study_programs.id_degree_level', '=', 'degree_levels.id')
            ->select([
                'study_programs.id',
                'study_programs.name',
                'study_programs.code',
                'study_programs.peringkat_akreditasi',
                'study_programs.status_kedaluwarsa',
                'study_programs.tanggal_kedaluwarsa',
                'study_programs.rumpun',
                'universities.name as university_name',
                'degree_levels.alias as jenjang_alias',
            ]);

        if ($hasNoSkColumn) {
            $query->addSelect('study_programs.no_sk');
        }

        // ── Filter pencarian teks ──
        $search = trim($request->input('search_text', ''));
        if ($search === '') {
            $search = trim(data_get($request->input('search'), 'value', ''));
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('study_programs.name', 'like', '%' . $search . '%')
                    ->orWhere('study_programs.code', 'like', '%' . $search . '%')
                    ->orWhere('universities.name', 'like', '%' . $search . '%');
            });
        }

        // ── Filter universitas ──
        $univIds = array_filter((array) $request->input('university_id', []));
        if (!empty($univIds)) {
            $query->whereIn('study_programs.id_university', $univIds);
        }

        // ── Filter jenjang ──
        $jenjangIds = array_filter((array) $request->input('degree_level_id', []));
        if (!empty($jenjangIds)) {
            $query->whereIn('study_programs.id_degree_level', $jenjangIds);
        }

        // ── Filter peringkat ──
        $peringkatArr = array_filter((array) $request->input('peringkat', []));
        if (!empty($peringkatArr)) {
            $query->where(function ($q) use ($peringkatArr) {
                if (in_array('(Belum)', $peringkatArr)) {
                    $q->whereNull('study_programs.peringkat_akreditasi');
                    $real = array_diff($peringkatArr, ['(Belum)']);
                    if (!empty($real)) {
                        $q->orWhereIn('study_programs.peringkat_akreditasi', $real);
                    }
                } else {
                    $q->whereIn('study_programs.peringkat_akreditasi', $peringkatArr);
                }
            });
        }

        // ── Filter status ──
        $status = $request->input('status', '');
        if ($status !== '') {
            $query->where('study_programs.status_kedaluwarsa', $status);
        }

        // ── Filter tahun kedaluwarsa ──
        $tahunArr = array_filter((array) $request->input('tahun', []));
        if (!empty($tahunArr)) {
            $query->whereIn(
                \DB::raw('YEAR(study_programs.tanggal_kedaluwarsa)'),
                $tahunArr
            );
        }

        // ── Filter rumpun ──
        $rumpun = $request->input('rumpun', '');
        if ($rumpun !== '') {
            $query->where('study_programs.rumpun', $rumpun);
        }

        // ── Total sebelum paginasi ──
        $totalRecords   = StudyProgram::where('study_programs.is_example', false)
            ->where('study_programs.is_active', true)
            ->count();
        $filteredCount  = (clone $query)->count();

        // ── Sorting ──
        $orderCol  = $request->input('order.0.column', 5);
        $orderDir  = $request->input('order.0.dir', 'asc');
        $colMap    = [
            1 => 'study_programs.name',
            2 => 'degree_levels.alias',
            3 => 'study_programs.peringkat_akreditasi',
            4 => $hasNoSkColumn ? 'study_programs.no_sk' : 'study_programs.tanggal_kedaluwarsa',
            5 => 'study_programs.tanggal_kedaluwarsa',
        ];
        $orderColumn = $colMap[(int)$orderCol] ?? 'study_programs.tanggal_kedaluwarsa';
        $query->orderByRaw($orderColumn . ' IS NULL, ' . $orderColumn . ' ' . ($orderDir === 'asc' ? 'ASC' : 'DESC'));

        // ── Paginasi ──
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $data   = $query->skip($start)->take($length)->get();

        $now    = Carbon::now();
        $rows   = $data->map(function ($item, $index) use ($start, $now) {
            $daysLeft = $item->tanggal_kedaluwarsa
                ? (int) $now->diffInDays($item->tanggal_kedaluwarsa, false)
                : null;

            $maxDays = 365 * 5;
            $progress = null;
            if ($daysLeft !== null && $daysLeft >= 0) {
                $progress = min(100, round(($daysLeft / $maxDays) * 100));
            }

            return [
                'number'             => $start + $index + 1,
                'name'               => ucfirst($item->name),
                'university'         => $item->university_name,
                'jenjang'            => $item->jenjang_alias,
                'peringkat'          => $item->peringkat_akreditasi,
                'no_sk'              => $item->no_sk ?? null,
                'status'             => $item->status_kedaluwarsa,
                'tanggal_kedaluwarsa' => $item->tanggal_kedaluwarsa
                    ? $item->tanggal_kedaluwarsa->translatedFormat('d M Y')
                    : null,
                'sisa_hari'          => $daysLeft,
                'progress'           => $progress,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredCount,
            'data'            => $rows,
        ]);
    }
}
