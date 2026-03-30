<?php

namespace App\Http\Controllers\Master;

use App\Models\University;
use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
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
        if ($request->ajax()) {
            $data = University::withCount('studyPrograms')->select('universities.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('study_programs_count', function ($row) {
                    return $row->study_programs_count;
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('universities.edit', $row->id) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->id . ')"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $universities = University::withCount('studyPrograms')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $universities
            ]);
        }
        return view('master-data.universitas.index', compact('universities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master-data.universitas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
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
        $university = University::withCount('studyPrograms')->with('studyPrograms.degreeLevel')->find($id);

        if (!$university) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'University not found'
                ], 404);
            }
            abort(404);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $university
            ]);
        }

        return view('master-data.universitas.show', compact('university'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(University $university)
    {
        return view('master-data.universitas.edit', compact('university'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
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
