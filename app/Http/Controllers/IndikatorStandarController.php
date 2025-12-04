<?php

namespace App\Http\Controllers;

use App\Models\IndikatorStandar;
use Illuminate\Http\Request;

class IndikatorStandarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $indikator = IndikatorStandar::with('elemenStandar')->get();
        
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
            'id_elemen' => 'required|exists:elemen,id_elemen',
            'jenis_indikator' => 'required|string|max:20|in:Kualitatif,Kuantitatif',
            'deskripsi_indikator' => 'required|string',
        ]);

        $indikator = IndikatorStandar::create($validated);
        $indikator->load('elemenStandar');

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
        $indikator = IndikatorStandar::with('elemenStandar.standar')->find($id);
        
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
            'id_elemen' => 'required|exists:elemen,id_elemen',
            'jenis_indikator' => 'required|string|max:20|in:Kualitatif,Kuantitatif',
            'deskripsi_indikator' => 'required|string',
        ]);

        $indikator = IndikatorStandar::find($id);
        
        if (!$indikator) {
            return response()->json([
                'success' => false,
                'message' => 'Indikator not found'
            ], 404);
        }
        
        $indikator->update($validated);
        $indikator->load('elemenStandar');

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
        $indikator = IndikatorStandar::find($id);
        
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
