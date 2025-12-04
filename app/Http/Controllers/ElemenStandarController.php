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
        $elemenStandar = ElemenStandar::with('kriteria')->get();
        
        return response()->json([
            'success' => true,
            'data' => $elemenStandar
        ]);
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

        $elemenStandar = ElemenStandar::create($validated);
        $elemenStandar->load('kriteria');

        return response()->json([
            'success' => true,
            'message' => 'Elemen Standar created successfully.',
            'data' => $elemenStandar
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $elemenStandar = ElemenStandar::with(['kriteria', 'indikator'])->find($id);
        
        if (!$elemenStandar) {
            return response()->json([
                'success' => false,
                'message' => 'Elemen Standar not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $elemenStandar
        ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Elemen Standar not found'
            ], 404);
        }
        
        $elemenStandar->update($validated);
        $elemenStandar->load('kriteria');

        return response()->json([
            'success' => true,
            'message' => 'Elemen Standar updated successfully.',
            'data' => $elemenStandar
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $elemenStandar = ElemenStandar::find($id);
        
        if (!$elemenStandar) {
            return response()->json([
                'success' => false,
                'message' => 'Elemen Standar not found'
            ], 404);
        }
        
        $elemenStandar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Elemen Standar deleted successfully.'
        ]);
    }
}
