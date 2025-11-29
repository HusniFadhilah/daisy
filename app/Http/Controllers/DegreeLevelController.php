<?php

namespace App\Http\Controllers;

use App\Models\DegreeLevel;
use Illuminate\Http\Request;

class DegreeLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $degreeLevels = DegreeLevel::all();
        
        return response()->json([
            'success' => true,
            'data' => $degreeLevels
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('jenjang.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'nullable|string|max:50',
        ]);

        $degreeLevel = DegreeLevel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Degree Level created successfully.',
            'data' => $degreeLevel
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $degreeLevel = DegreeLevel::find($id);
        
        if (!$degreeLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Degree Level not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $degreeLevel
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DegreeLevel $degreeLevel)
    {
        return view('jenjang.edit', compact('degreeLevel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'nullable|string|max:50',
        ]);

        $degreeLevel = DegreeLevel::find($id);
        
        if (!$degreeLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Degree Level not found'
            ], 404);
        }
        
        $degreeLevel->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Degree Level updated successfully.',
            'data' => $degreeLevel
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $degreeLevel = DegreeLevel::find($id);
        
        if (!$degreeLevel) {
            return response()->json([
                'success' => false,
                'message' => 'Degree Level not found'
            ], 404);
        }
        
        
        $degreeLevel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Degree Level deleted successfully.'
        ]);
    }
}
