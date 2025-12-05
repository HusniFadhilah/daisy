<?php

namespace App\Http\Controllers;

use App\Models\ElemenStandar;
use Illuminate\Http\Request;

class ElemenStandarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $elemenStandar = ElemenStandar::with(['kriteria', 'pernyataan'])->latest()->paginate(10);
        
        return view('indikator.elemen.index', compact('elemenStandar'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kriteria = \App\Models\Kriteria::all();
        return view('indikator.elemen.create', compact('kriteria'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kriteria' => 'required|exists:kriteria,id_kriteria',
            'kode_elemen' => 'required|string|max:50',
            'pernyataan_elemen' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        ElemenStandar::create($validated);

        return redirect()->route('elemen-standar.index')->with('success', 'Elemen Standar berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $elemenStandar = ElemenStandar::with(['kriteria', 'indikator', 'pernyataan'])->find($id);
        
        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }
        
        return view('indikator.elemen.show', compact('elemenStandar'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $elemenStandar = ElemenStandar::find($id);
        
        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }
        
        $kriteria = \App\Models\Kriteria::all();
        return view('indikator.elemen.edit', compact('elemenStandar', 'kriteria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_kriteria' => 'required|exists:kriteria,id_kriteria',
            'kode_elemen' => 'required|string|max:50',
            'pernyataan_elemen' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $elemenStandar = ElemenStandar::find($id);
        
        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }
        
        $elemenStandar->update($validated);

        return redirect()->route('elemen-standar.index')->with('success', 'Elemen Standar berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $elemenStandar = ElemenStandar::find($id);
        
        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }
        
        $elemenStandar->delete();

        return redirect()->route('elemen-standar.index')->with('success', 'Elemen Standar berhasil dihapus');
    }
}
