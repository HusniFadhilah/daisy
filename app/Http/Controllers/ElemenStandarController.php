<?php

namespace App\Http\Controllers;

use App\Models\ElemenStandar;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ElemenStandarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ElemenStandar::with(['kriteria'])->select('elemen_standar.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('kriteria_nama', function ($row) {
                    return $row->kriteria ? $row->kriteria->kode_kriteria . ' - ' . $row->kriteria->nama_kriteria : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('elemen-standar.edit', $row->id_elemen) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->id_elemen . ')"><i class="bi bi-trash"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $kriteria = \App\Models\Kriteria::all();
        return view('indikator.elemen.index', compact('kriteria'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kriteria = \App\Models\Kriteria::all();
        return view('indikator.elemen.create', compact('kriteria'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kriteria' => 'required|exists:kriteria,id',
            'kode_elemen' => 'required|string|max:50',
            'pernyataan_elemen' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        ElemenStandar::create($validated);

        return redirect()->route('elemen-standar.index')->with('success', 'Elemen Standar berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $elemenStandar = ElemenStandar::with(['kriteria', 'indikator', 'pernyataan'])->find($id);

        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }

        return view('indikator.elemen.show', compact('elemenStandar'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $elemenStandar = ElemenStandar::find($id);

        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }

        $kriteria = \App\Models\Kriteria::all();
        return view('indikator.elemen.edit', compact('elemenStandar', 'kriteria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_kriteria' => 'required|exists:kriteria,id',
            'kode_elemen' => 'required|string|max:50',
            'pernyataan_elemen' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $elemenStandar = ElemenStandar::find($id);

        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }

        $elemenStandar->update($validated);

        return redirect()->route('elemen-standar.index')->with('success', 'Elemen Standar berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $elemenStandar = ElemenStandar::find($id);

        if (!$elemenStandar) {
            return redirect()->route('elemen-standar.index')->with('error', 'Elemen Standar tidak ditemukan');
        }

        $elemenStandar->delete();

        return redirect()->route('elemen-standar.index')->with('success', 'Elemen Standar berhasil dihapus');
    }
}
