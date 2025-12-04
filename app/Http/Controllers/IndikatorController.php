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
        $indikator = Indikator::with(['elemenStandar', 'jenisIndikator'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $indikator
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id_elemen',
            'id_jenis' => 'required|exists:jenis_indikator,id_jenis',
            'kode_indikator' => 'required|string|max:100',
            'deskripsi_indikator' => 'required|string',
        ]);

        $indikator = Indikator::create($validated);
        $indikator->load(['elemenStandar', 'jenisIndikator']);

        return response()->json([
            'success' => true,
            'message' => 'Indikator created successfully.',
            'data' => $indikator
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $indikator = Indikator::with(['elemenStandar.kriteria', 'jenisIndikator'])->find($id);
        
        if (!$indikator) {
            return response()->json([
                'success' => false,
                'message' => 'Indikator not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $indikator
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id_elemen',
            'id_jenis' => 'required|exists:jenis_indikator,id_jenis',
            'kode_indikator' => 'required|string|max:100',
            'deskripsi_indikator' => 'required|string',
        ]);

        $indikator = Indikator::find($id);
        
        if (!$indikator) {
            return response()->json([
                'success' => false,
                'message' => 'Indikator not found'
            ], 404);
        }
        
        $indikator->update($validated);
        $indikator->load(['elemenStandar', 'jenisIndikator']);

        return response()->json([
            'success' => true,
            'message' => 'Indikator updated successfully.',
            'data' => $indikator
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $indikator = Indikator::find($id);
        
        if (!$indikator) {
            return response()->json([
                'success' => false,
                'message' => 'Indikator not found'
            ], 404);
        }
        
        $indikator->delete();

        return response()->json([
            'success' => true,
            'message' => 'Indikator deleted successfully.'
        ]);
    }
}
