<?php

namespace App\Http\Controllers;

use App\Services\BobotPenilaianService;
use App\Http\Requests\BobotPenilaianRequest;
use App\Http\Resources\BobotPenilaianResource;
use App\Models\ElemenStandar;
use App\Models\StudyProgramCategory;
use App\Models\Asesmen;
use Illuminate\Http\Request;

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
        $filters = $request->only(['id_elemen', 'id_category']);
        $bobots = $this->bobotService->getAll($filters);

        if ($request->wantsJson() || $request->is('api/*')) {
            return BobotPenilaianResource::collection($bobots);
        }

        $elemens = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $categories = StudyProgramCategory::all();
        $asesmens = Asesmen::all();

        return view('bobot-penilaian.index', compact('bobots', 'elemens', 'categories', 'asesmens'));
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

            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json($hasil);
            }

            return view('bobot-penilaian.hasil', compact('hasil', 'asesmenId', 'categoryId'));
        } catch (\Exception $e) {
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
}
