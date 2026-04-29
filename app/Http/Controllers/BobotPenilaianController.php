<?php

namespace App\Http\Controllers;

use App\Models\Asesmen;
use App\Models\DegreeLevel;
use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use Illuminate\Support\Facades\Log;
use App\Models\StudyProgramCategory;
use App\Services\BobotPenilaianService;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\BobotPenilaianRequest;
use App\Http\Resources\BobotPenilaianResource;

class BobotPenilaianController extends Controller
{
    protected $bobotService;

    public function __construct(BobotPenilaianService $bobotService)
    {
        $this->bobotService = $bobotService;
    }

    /**
     * Display listing of bobot penilaian
     */
    public function index(Request $request)
    {
        $filters = $request->only(['id_elemen', 'id_category', 'id_degree_level']);

        if ($request->ajax()) {
            $bobots = $this->bobotService->getAll($filters);

            return DataTables::of($bobots)
                ->addIndexColumn()
                ->addColumn('elemen_standar', function ($row) {
                    return $row->elemenStandar ? $row->elemenStandar->kode_elemen . ' - ' . $row->elemenStandar->pernyataan_elemen : '-';
                })
                ->addColumn('category', function ($row) {
                    $category = $row->category ? $row->category->name : '-';
                    $degree = $row->degreeLevel ? $row->degreeLevel->name : '-';
                    return $category . ' / ' . $degree;
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_active) {
                        return '<span class="badge bg-success">Aktif</span>';
                    } else {
                        return '<span class="badge bg-secondary">Nonaktif</span>';
                    }
                })
                ->addColumn('asesmen', function ($row) {
                    return '-'; // Bobot tidak terkait langsung dengan asesmen
                })
                ->addColumn('action', function ($row) {
                    $editBtn = '<a href="' . route('bobot-penilaian.edit', $row->id) . '" class="btn btn-sm btn-warning">Edit</a>';
                    $deleteBtn = '<button onclick="deleteRecord(' . $row->id . ')" class="btn btn-sm btn-danger">Delete</button>';

                    if ($row->is_active) {
                        $toggleBtn = '<button onclick="toggleActive(' . $row->id . ')" class="btn btn-sm btn-secondary">Nonaktifkan</button>';
                    } else {
                        $toggleBtn = '<button onclick="toggleActive(' . $row->id . ')" class="btn btn-sm btn-success">Aktifkan</button>';
                    }

                    return $editBtn . ' ' . $toggleBtn . ' ' . $deleteBtn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $bobots = $this->bobotService->getAll($filters);

        if ($request->wantsJson() || $request->is('api/*')) {
            return BobotPenilaianResource::collection($bobots);
        }

        $elemens = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $categories = StudyProgramCategory::all();
        $degreeLevels = DegreeLevel::orderBy('code')->get();
        $asesmens = Asesmen::all();

        return view('master-data.bobot-penilaian.index', compact('bobots', 'elemens', 'categories', 'degreeLevels', 'asesmens'));
    }

    /**
     * Show the form for creating a new bobot
     */
    public function create()
    {
        $elemens = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $categories = StudyProgramCategory::all();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        return view('master-data.bobot-penilaian.create', compact('elemens', 'categories', 'degreeLevels'));
    }

    /**
     * Store a newly created bobot
     */
    public function store(BobotPenilaianRequest $request)
    {
        try {
            $bobot = $this->bobotService->create($request->validated());

            if ($request->wantsJson() || $request->is('api/*')) {
                return new BobotPenilaianResource($bobot);
            }

            return redirect()->route('bobot-penilaian.index')
                ->with('success', 'Bobot penilaian berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error($e);
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Gagal menambahkan bobot penilaian',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menambahkan bobot penilaian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified bobot.
     */
    public function show($id)
    {
        $bobot = $this->bobotService->find($id);

        if (!$bobot) {
            return redirect()->route('bobot-penilaian.index')
                ->with('error', 'Bobot penilaian tidak ditemukan');
        }

        if (request()->wantsJson() || request()->is('api/*')) {
            return new BobotPenilaianResource($bobot->load(['elemenStandar.kriteria', 'category']));
        }

        return redirect()->route('bobot-penilaian.edit', $id);
    }

    /**
     * Show the form for editing the specified bobot
     */
    public function edit($id)
    {
        $bobot = $this->bobotService->find($id);

        if (!$bobot) {
            return redirect()->route('bobot-penilaian.index')
                ->with('error', 'Bobot penilaian tidak ditemukan');
        }

        $elemens = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $categories = StudyProgramCategory::all();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        return view('master-data.bobot-penilaian.edit', compact('bobot', 'elemens', 'categories', 'degreeLevels'));
    }

    /**
     * Update the specified bobot
     */
    public function update(BobotPenilaianRequest $request, $id)
    {
        try {
            $bobot = $this->bobotService->update($id, $request->validated());

            if ($request->wantsJson() || $request->is('api/*')) {
                return new BobotPenilaianResource($bobot);
            }

            return redirect()->route('bobot-penilaian.index')
                ->with('success', 'Bobot penilaian berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error($e);
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Gagal memperbarui bobot penilaian',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal memperbarui bobot penilaian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified bobot
     */
    public function destroy(Request $request, $id)
    {
        try {
            $this->bobotService->delete($id);

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Bobot penilaian berhasil dihapus'
                ]);
            }

            return redirect()->route('bobot-penilaian.index')
                ->with('success', 'Bobot penilaian berhasil dihapus');
        } catch (\Exception $e) {
            Log::error($e);
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Gagal menghapus bobot penilaian',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menghapus bobot penilaian: ' . $e->getMessage());
        }
    }

    /**
     * Calculate weighted score for an asesmen
     */
    public function calculate(Request $request, $asesmenId, $categoryId)
    {
        try {
            $hasil = $this->bobotService->calculateTotalScore($asesmenId, $categoryId);

            if ($request->wantsJson() || $request->ajax() || $request->is('api/*')) {
                return response()->json($hasil);
            }

            return redirect()->route('bobot-penilaian.index')
                ->with('success', 'Perhitungan bobot berhasil diproses.');
        } catch (\Exception $e) {
            Log::error($e);
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Gagal menghitung bobot penilaian',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal menghitung bobot penilaian: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status of bobot penilaian
     */
    public function toggleActive($id)
    {
        try {
            $bobot = $this->bobotService->find($id);

            if (!$bobot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bobot penilaian tidak ditemukan'
                ], 404);
            }

            $bobot->is_active = !$bobot->is_active;
            $bobot->save();

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'is_active' => $bobot->is_active
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status: ' . $e->getMessage()
            ], 500);
        }
    }
}
