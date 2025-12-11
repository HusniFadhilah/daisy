<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use Illuminate\Http\Request;

class IndikatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $indikator = Indikator::with(['elemenStandar.kriteria', 'jenisIndikator'])->latest()->paginate(10);

        return view('indikator.indikator.index', compact('indikator'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $elemenStandar = \App\Models\ElemenStandar::with('kriteria')->get();
        $jenisIndikator = \App\Models\JenisIndikator::all();
        return view('indikator.indikator.create', compact('elemenStandar', 'jenisIndikator'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'id_jenis' => 'required|exists:jenis_indikator,id',
            'kode_indikator' => 'required|string|max:100',
            'deskripsi_indikator' => 'required|string',
        ]);

        Indikator::create($validated);

        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $indikator = Indikator::with(['elemenStandar.kriteria', 'jenisIndikator'])->find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        return view('indikator.indikator.show', compact('indikator'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $indikator = Indikator::find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        $elemenStandar = \App\Models\ElemenStandar::with('kriteria')->get();
        $jenisIndikator = \App\Models\JenisIndikator::all();
        return view('indikator.indikator.edit', compact('indikator', 'elemenStandar', 'jenisIndikator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'id_jenis' => 'required|exists:jenis_indikator,id',
            'kode_indikator' => 'required|string|max:100',
            'deskripsi_indikator' => 'required|string',
        ]);

        $indikator = Indikator::find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        $indikator->update($validated);

        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $indikator = Indikator::find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        $indikator->delete();

        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil dihapus');
    }
}
