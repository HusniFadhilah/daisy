<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Models\ElemenStandar;
use App\Models\JenjangPenilaian;
use App\Http\Controllers\Controller;
use App\Models\IndikatorPenilaianElemen;

class IndikatorPenilaianElemenController extends Controller
{
    /**
     * Display a listing of the resource with tabs.
     */
    public function index(Request $request)
    {
        $indikators = IndikatorPenilaianElemen::with(['elemenStandar', 'jenjangPenilaian'])
            ->orderBy('id_elemen')
            ->orderBy('id_jenjang_penilaian')
            ->get();

        $elemenStandars = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $jenjangPenilaians = JenjangPenilaian::orderBy('skor')->get();

        // Group by elemen standar
        $groupedIndikators = $indikators->groupBy('id_elemen');

        return view('indikator-penilaian.index', compact('indikators', 'elemenStandars', 'jenjangPenilaians', 'groupedIndikators'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $elemenStandars = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $jenjangPenilaians = JenjangPenilaian::orderBy('skor')->get();

        return view('indikator-penilaian.create', compact('elemenStandars', 'jenjangPenilaians'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'id_jenjang_penilaian' => 'required|exists:jenjang_penilaian,id',
            'deskripsi_penilaian' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $indikator = IndikatorPenilaianElemen::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Indikator penilaian berhasil ditambahkan.',
                'data' => $indikator->load(['elemenStandar', 'jenjangPenilaian'])
            ], 201);
        }

        return redirect()->route('indikator-penilaian.index')
            ->with('success', 'Indikator penilaian berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $indikator = IndikatorPenilaianElemen::with(['elemenStandar', 'jenjangPenilaian'])->findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $indikator
            ]);
        }

        return view('indikator-penilaian.show', compact('indikator'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $indikatorPenilaian = IndikatorPenilaianElemen::findOrFail($id);
        $elemenStandars = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $jenjangPenilaians = JenjangPenilaian::orderBy('skor')->get();

        return view('indikator-penilaian.edit', compact('indikatorPenilaian', 'elemenStandars', 'jenjangPenilaians'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'id_jenjang_penilaian' => 'required|exists:jenjang_penilaian,id',
            'deskripsi_penilaian' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $indikator = IndikatorPenilaianElemen::findOrFail($id);
        $indikator->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Indikator penilaian berhasil diperbarui.',
                'data' => $indikator->load(['elemenStandar', 'jenjangPenilaian'])
            ]);
        }

        return redirect()->route('indikator-penilaian.index')
            ->with('success', 'Indikator penilaian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $indikator = IndikatorPenilaianElemen::findOrFail($id);
        $indikator->delete();

        return redirect()->route('indikator-penilaian.index')
            ->with('success', 'Indikator penilaian berhasil dihapus.');
    }
}
