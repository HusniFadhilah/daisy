<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ElemenStandar;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ElemenStandarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = ElemenStandar::with(['kriteria'])->select('elemen_standar.*');

    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('kriteria_nama', function ($row) {
    //                 return $row->kriteria ? $row->kriteria->kode_kriteria . ' - ' . $row->kriteria->nama_kriteria : '-';
    //             })
    //             ->addColumn('action', function ($row) {
    //                 $btn = '<div class="btn-group" role="group">';
    //                 $btn .= '<a href="' . route('elemen-standar.edit', $row->id_elemen) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
    //                 $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->id_elemen . ')"><i class="bi bi-trash"></i></button>';
    //                 $btn .= '</div>';
    //                 return $btn;
    //             })
    //             ->rawColumns(['action'])
    //             ->make(true);
    //     }

    //     $kriteria = \App\Models\Kriteria::all();
    //     return view('master-data.indikator.elemen.index', compact('kriteria'));
    // }

    public function index(Request $request)
    {
        $query = ElemenStandar::with(['kriteria', 'pernyataan']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('kode_elemen', 'like', "%{$search}%")
                    ->orWhere('pernyataan_elemen', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('kriteria', function ($sub) use ($search) {
                        $sub->where('kode_kriteria', 'like', "%{$search}%")
                            ->orWhere('nama_kriteria', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('id_kriteria')) {
            $query->where('id_kriteria', $request->id_kriteria);
        }

        $elemenStandar = $query->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $kriteria = Kriteria::orderBy('kode_kriteria')->get();

        return view('master-data.indikator.elemen.index', compact('elemenStandar', 'kriteria'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kriteria = \App\Models\Kriteria::all();
        return view('master-data.indikator.elemen.create', compact('kriteria'));
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

        return view('master-data.indikator.elemen.show', compact('elemenStandar'));
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
        return view('master-data.indikator.elemen.edit', compact('elemenStandar', 'kriteria'));
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
