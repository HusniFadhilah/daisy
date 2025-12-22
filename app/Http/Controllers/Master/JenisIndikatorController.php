<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Models\JenisIndikator;
use App\Http\Controllers\Controller;

class JenisIndikatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jenisIndikator = JenisIndikator::all();

        return response()->json([
            'success' => true,
            'data' => $jenisIndikator
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $jenisIndikator = JenisIndikator::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jenis Indikator created successfully.',
            'data' => $jenisIndikator
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $jenisIndikator = JenisIndikator::with('indikator')->find($id);

        if (!$jenisIndikator) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis Indikator not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $jenisIndikator
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_jenis' => 'required|string|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $jenisIndikator = JenisIndikator::find($id);

        if (!$jenisIndikator) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis Indikator not found'
            ], 404);
        }

        $jenisIndikator->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jenis Indikator updated successfully.',
            'data' => $jenisIndikator
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jenisIndikator = JenisIndikator::find($id);

        if (!$jenisIndikator) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis Indikator not found'
            ], 404);
        }

        $jenisIndikator->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jenis Indikator deleted successfully.'
        ]);
    }
}
