<?php

namespace App\Http\Controllers;

use App\Models\Standar;
use Illuminate\Http\Request;

class StandarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $standar = Standar::all();
        
        return response()->json([
            'success' => true,
            'data' => $standar
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_standar' => 'required|string|max:10',
            'nama_standar' => 'required|string|max:255',
        ]);

        $standar = Standar::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Standar created successfully.',
            'data' => $standar
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $standar = Standar::with('elemenStandar')->find($id);
        
        if (!$standar) {
            return response()->json([
                'success' => false,
                'message' => 'Standar not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $standar
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_standar' => 'required|string|max:10',
            'nama_standar' => 'required|string|max:255',
        ]);

        $standar = Standar::find($id);
        
        if (!$standar) {
            return response()->json([
                'success' => false,
                'message' => 'Standar not found'
            ], 404);
        }
        
        $standar->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Standar updated successfully.',
            'data' => $standar
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $standar = Standar::find($id);
        
        if (!$standar) {
            return response()->json([
                'success' => false,
                'message' => 'Standar not found'
            ], 404);
        }
        
        $standar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Standar deleted successfully.'
        ]);
    }
}
