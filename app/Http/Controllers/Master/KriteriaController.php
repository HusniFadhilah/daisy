<?php

namespace App\Http\Controllers\Master;

use App\Models\Kriteria;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class KriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $kriteria = Kriteria::select('kriteria.*');
        // if ($request->ajax()) {

        //     return DataTables::of($kriteria)
        //         ->addIndexColumn()
        //         ->addColumn('action', function ($row) {
        //             $btn = '<div class="btn-group" role="group">';
        //             $btn .= '<a href="' . route('kriteria.edit', $row->id_kriteria) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
        //             $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->id_kriteria . ')"><i class="bi bi-trash"></i></button>';
        //             $btn .= '</div>';
        //             return $btn;
        //         })
        //         ->rawColumns(['action'])
        //         ->make(true);
        // }

        $query = Kriteria::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('kode_kriteria', 'like', "%{$search}%")
                ->orWhere('nama_kriteria', 'like', "%{$search}%")
                ->orWhere('keterangan', 'like', "%{$search}%");
        }

        $kriteria = $query->paginate(10)->withQueryString();

        return view('master-data.indikator.kriteria.index', compact('kriteria'));

        // return view('master-data.indikator.kriteria.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master-data.indikator.kriteria.create');
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

        return view('master-data.indikator.kriteria.show', compact('kriteria'));
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

        return view('master-data.indikator.kriteria.edit', compact('kriteria'));
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
