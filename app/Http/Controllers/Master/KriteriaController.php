<?php

namespace App\Http\Controllers\Master;

use App\Models\Kriteria;
use Illuminate\Http\Request;
<<<<<<<< HEAD:app/Http/Controllers/KriteriaController.php
use Yajra\DataTables\Facades\DataTables;
========
use App\Http\Controllers\Controller;
>>>>>>>> origin/main:app/Http/Controllers/Master/KriteriaController.php

class KriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
<<<<<<<< HEAD:app/Http/Controllers/KriteriaController.php
        if ($request->ajax()) {
            $data = Kriteria::select('kriteria.*');
            
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="'.route('kriteria.edit', $row->id_kriteria).'" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord('.$row->id_kriteria.')"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('indikator.kriteria.index');
========
        $kriteria = Kriteria::latest()->paginate(10);

        return view('indikator.kriteria.index', compact('kriteria'));
>>>>>>>> origin/main:app/Http/Controllers/Master/KriteriaController.php
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('indikator.kriteria.create');
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

        Kriteria::create($validated);

        return redirect()->route('kriteria.index')->with('success', 'Kriteria berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kriteria = Kriteria::with('elemenStandar')->find($id);

        if (!$kriteria) {
            return redirect()->route('kriteria.index')->with('error', 'Kriteria tidak ditemukan');
        }

        return view('indikator.kriteria.show', compact('kriteria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $kriteria = Kriteria::find($id);

        if (!$kriteria) {
            return redirect()->route('kriteria.index')->with('error', 'Kriteria tidak ditemukan');
        }

        return view('indikator.kriteria.edit', compact('kriteria'));
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
            return redirect()->route('kriteria.index')->with('error', 'Kriteria tidak ditemukan');
        }

        $kriteria->update($validated);

        return redirect()->route('kriteria.index')->with('success', 'Kriteria berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kriteria = Kriteria::find($id);

        if (!$kriteria) {
            return redirect()->route('kriteria.index')->with('error', 'Kriteria tidak ditemukan');
        }

        $kriteria->delete();

        return redirect()->route('kriteria.index')->with('success', 'Kriteria berhasil dihapus');
    }
}
