<?php

namespace App\Http\Controllers\Master;

use App\Models\University;
use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UniversityController extends Controller
{
    /**
     * Display master data page with tabs
     */
    public function masterData(Request $request, $tab = 'universities')
    {
        $universities = University::withCount('studyPrograms')->get();
        $studyPrograms = StudyProgram::with(['university', 'degreeLevel'])->get();
        $degreeLevels = DegreeLevel::all();

        return view('master-data.index', compact('universities', 'studyPrograms', 'degreeLevels', 'tab'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $universities = University::withCount('studyPrograms')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $universities
            ]);
        }

        return view('universitas.index', compact('universities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('universitas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        $university = University::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'University created successfully.',
                'data' => $university
            ], 201);
        }

        return redirect()->route('master-data.index', ['tab' => 'universities'])
            ->with('success', 'Universitas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $university = University::find($id);

        if (!$university) {
            return response()->json([
                'success' => false,
                'message' => 'University not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $university
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(University $university)
    {
        return view('universitas.edit', compact('university'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        $university = University::find($id);

        if (!$university) {
            return response()->json([
                'success' => false,
                'message' => 'University not found'
            ], 404);
        }

        $university->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'University updated successfully.',
                'data' => $university
            ]);
        }

        return redirect()->route('master-data.index', ['tab' => 'universities'])
            ->with('success', 'Universitas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $university = University::find($id);

        if (!$university) {
            return response()->json([
                'success' => false,
                'message' => 'University not found'
            ], 404);
        }

        $university->delete();

        return redirect()->route('master-data.index', ['tab' => 'universities'])
            ->with('success', 'Universitas berhasil dihapus.');
    }
}
