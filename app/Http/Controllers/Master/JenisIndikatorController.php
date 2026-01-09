<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
<<<<<<<< HEAD:app/Http/Controllers/JenisIndikatorController.php
use Yajra\DataTables\Facades\DataTables;
========
use App\Models\JenisIndikator;
use App\Http\Controllers\Controller;
>>>>>>>> origin/main:app/Http/Controllers/Master/JenisIndikatorController.php

class JenisIndikatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
<<<<<<<< HEAD:app/Http/Controllers/JenisIndikatorController.php
        if ($request->ajax()) {
            $data = JenisIndikator::select('jenis_indikator.*');
            
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<button type="button" class="btn btn-sm btn-warning" onclick="editRecord('.$row->id_jenis.')" data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil"></i></button>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord('.$row->id_jenis.')"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        if ($request->wantsJson()) {
            $jenisIndikator = JenisIndikator::all();
            return response()->json([
                'success' => true,
                'data' => $jenisIndikator
            ]);
        }
        
        return view('indikator.jenis.index');
========
        $jenisIndikator = JenisIndikator::all();

        return response()->json([
            'success' => true,
            'data' => $jenisIndikator
        ]);
>>>>>>>> origin/main:app/Http/Controllers/Master/JenisIndikatorController.php
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
