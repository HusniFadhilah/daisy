<?php

namespace App\Http\Controllers\Master;

use App\Models\University;
use App\Models\DegreeLevel;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StudyProgramCategory;
use Yajra\DataTables\Facades\DataTables;

class StudyProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StudyProgram::with(['university', 'degreeLevel', 'category'])
                ->select('study_programs.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return '<a href="' . route('study-programs.show', $row->id) . '">' . $row->name . '</a>';
                })
                ->addColumn('university_name', function ($row) {
                    return $row->university ? $row->university->name : '-';
                })
                ->addColumn('degree_level_name', function ($row) {
                    return $row->degreeLevel ? $row->degreeLevel->name : '-';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category ? $row->category->name : '-';
                })
                ->addColumn('bentuk_pt', function ($row) {
                    return $row->bentuk_pt ?? '-';
                })
                ->addColumn('peringkat', function ($row) {
                    if ($row->peringkat_akreditasi) {
                        $class = match ($row->status_kedaluwarsa) {
                            'Aktif' => 'success',
                            'Kedaluwarsa' => 'warning',
                            default => 'secondary'
                        };
                        return '<span class="badge bg-' . $class . '">' . $row->peringkat_akreditasi . '</span>';
                    }
                    return '<span class="badge bg-secondary">-</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('study-programs.show', $row->id) . '" class="btn btn-sm btn-info text-white"><i class="bi bi-eye"></i></a>';
                    $btn .= '<a href="' . route('study-programs.edit', $row->id) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->id . ')"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['name', 'peringkat', 'action'])
                ->make(true);
        }

        return view('prodi.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $universities = University::all();
        $degreeLevels = DegreeLevel::all();
        return view('prodi.create', compact('universities', 'degreeLevels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'id_university' => 'required|exists:universities,id',
            'id_degree_level' => 'required|exists:degree_levels,id',
            'bentuk_pt' => 'nullable|in:Universitas,Institut,Sekolah Tinggi,Politeknik,Akademi',
            'email' => 'nullable|email|max:255',
        ]);

        $studyProgram = StudyProgram::create($validated);
        $studyProgram->load(['university', 'degreeLevel']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Study Program created successfully.',
                'data' => $studyProgram
            ], 201);
        }

        return redirect()->route('master-data.index', ['tab' => 'study-programs'])
            ->with('success', 'Program studi berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $studyProgram = StudyProgram::with(['university', 'degreeLevel', 'category'])->find($id);

        if (!$studyProgram) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Study Program not found'
                ], 404);
            }
            abort(404);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $studyProgram
            ]);
        }

        return view('prodi.show', compact('studyProgram'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudyProgram $studyProgram)
    {
        $universities = University::all();
        $degreeLevels = DegreeLevel::all();
        return view('prodi.edit', compact('studyProgram', 'universities', 'degreeLevels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'id_university' => 'required|exists:universities,id',
            'id_degree_level' => 'required|exists:degree_levels,id',
            'bentuk_pt' => 'nullable|in:Universitas,Institut,Sekolah Tinggi,Politeknik,Akademi',
            'email' => 'nullable|email|max:255',
            'peringkat_akreditasi' => 'nullable|string|max:255',
            'tanggal_kedaluwarsa' => 'nullable|date',
            'status_kedaluwarsa' => 'nullable|in:Aktif,Kedaluwarsa,Belum Terakreditasi',
        ]);

        $studyProgram = StudyProgram::find($id);

        if (!$studyProgram) {
            return response()->json([
                'success' => false,
                'message' => 'Study Program not found'
            ], 404);
        }

        $studyProgram->update($validated);

        // Reload data dengan relasi
        $updatedProgram = StudyProgram::with(['university', 'degreeLevel'])->find($id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Study Program updated successfully.',
                'data' => $updatedProgram
            ]);
        }

        return redirect()->route('master-data.index', ['tab' => 'study-programs'])
            ->with('success', 'Program studi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $studyProgram = StudyProgram::find($id);

        if (!$studyProgram) {
            return response()->json([
                'success' => false,
                'message' => 'Study Program not found'
            ], 404);
        }

        $studyProgram->delete();

        return redirect()->route('master-data.index', ['tab' => 'study-programs'])
            ->with('success', 'Program studi berhasil dihapus.');
    }
}
