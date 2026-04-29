<?php

namespace App\Http\Controllers\Master;

use App\Models\DegreeLevel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        return response()->json([
            'success' => true,
            'message' => 'Gunakan endpoint POST untuk membuat jenjang pendidikan.',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:15',
            'alias' => 'nullable|string|max:15',
            'name' => 'nullable|string|max:50',
            'id_category' => 'nullable|exists:study_program_categories,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['alias'] = $validated['alias'] ?? $validated['code'];

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
        return response()->json([
            'success' => true,
            'data' => $degreeLevel,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:15',
            'alias' => 'nullable|string|max:15',
            'name' => 'nullable|string|max:50',
            'id_category' => 'nullable|exists:study_program_categories,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['alias'] = $validated['alias'] ?? $validated['code'];

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
