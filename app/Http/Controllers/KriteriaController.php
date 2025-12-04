<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kriteria = Kriteria::all();
        
        return response()->json([
            'success' => true,
            'data' => $kriteria
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_kriteria' => 'required|string|max:50',
            'nama_kriteria' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $kriteria = Kriteria::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kriteria created successfully.',
            'data' => $kriteria
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kriteria = Kriteria::with('elemenStandar')->find($id);
        
        if (!$kriteria) {
            return response()->json([
                'success' => false,
                'message' => 'Kriteria not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $kriteria
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_kriteria' => 'required|string|max:50',
            'nama_kriteria' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $kriteria = Kriteria::find($id);
        
        if (!$kriteria) {
            return response()->json([
                'success' => false,
                'message' => 'Kriteria not found'
            ], 404);
        }
        
        $kriteria->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kriteria updated successfully.',
            'data' => $kriteria
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kriteria = Kriteria::find($id);
        
        if (!$kriteria) {
            return response()->json([
                'success' => false,
                'message' => 'Kriteria not found'
            ], 404);
        }
        
        $kriteria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kriteria deleted successfully.'
        ]);
    }
}
