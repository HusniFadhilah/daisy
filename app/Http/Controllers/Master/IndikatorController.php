<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ElemenStandar;
use App\Models\Indikator;
use App\Models\JenisIndikator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class IndikatorController extends Controller
{
    //     /**
    //      * Display a listing of the resource.
    //      */
    //     public function index(Request $request)
    //     {
    //         if ($request->ajax()) {
    //             $data = Indikator::with(['elemenStandar.kriteria', 'jenisIndikator'])->select('indikator.*');

    //             return DataTables::of($data)
    //                 ->addIndexColumn()
    //                 ->addColumn('elemen_nama', function ($row) {
    //                     if ($row->elemenStandar) {
    //                         return $row->elemenStandar->kode_elemen . ' - ' . Str::limit($row->elemenStandar->pernyataan_elemen, 50);
    //                     }
    //                     return '-';
    //                 })
    //                 ->addColumn('kriteria_nama', function ($row) {
    //                     return $row->elemenStandar && $row->elemenStandar->kriteria ? $row->elemenStandar->kriteria->kode_kriteria : '-';
    //                 })
    //                 ->addColumn('jenis_nama', function ($row) {
    //                     return $row->jenisIndikator ? $row->jenisIndikator->nama_jenis : '-';
    //                 })
    //                 ->addColumn('action', function ($row) {
    //                     $btn = '<div class="btn-group" role="group">';
    //                     $btn .= '<a href="' . route('indikator.edit', $row->id_indikator) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
    //                     $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord(' . $row->id_indikator . ')"><i class="bi bi-trash"></i></button>';
    //                     $btn .= '</div>';
    //                     return $btn;
    //                 })
    //                 ->rawColumns(['action'])
    //                 ->make(true);
    //         }

    //         $elemenStandar = \App\Models\ElemenStandar::with('kriteria')->get();
    //         $jenisIndikator = \App\Models\JenisIndikator::all();
    //         return view('master-data.indikator.indikator.index', compact('elemenStandar', 'jenisIndikator'));
    //     }

    public function index(Request $request)
    {
        $query = Indikator::with(['elemenStandar.kriteria', 'jenisIndikator']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('kode_indikator', 'like', "%{$search}%")
                    ->orWhere('deskripsi_indikator', 'like', "%{$search}%")
                    ->orWhereHas('elemenStandar', function ($sub) use ($search) {
                        $sub->where('kode_elemen', 'like', "%{$search}%")
                            ->orWhere('pernyataan_elemen', 'like', "%{$search}%")
                            ->orWhereHas('kriteria', function ($qKriteria) use ($search) {
                                $qKriteria->where('kode_kriteria', 'like', "%{$search}%")
                                    ->orWhere('nama_kriteria', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('jenisIndikator', function ($sub) use ($search) {
                        $sub->where('nama_jenis', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('id_elemen')) {
            $query->where('id_elemen', $request->id_elemen);
        }

        if ($request->filled('id_jenis')) {
            $query->where('id_jenis', $request->id_jenis);
        }

        $indikator = $query->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $elemenStandar = ElemenStandar::with('kriteria')->orderBy('kode_elemen')->get();
        $jenisIndikator = JenisIndikator::orderBy('nama_jenis')->get();

        return view('master-data.indikator.indikator.index', compact(
            'indikator',
            'elemenStandar',
            'jenisIndikator'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $elemenStandar = \App\Models\ElemenStandar::with('kriteria')->get();
        $jenisIndikator = \App\Models\JenisIndikator::all();
        return view('master-data.indikator.indikator.create', compact('elemenStandar', 'jenisIndikator'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'id_jenis' => 'required|exists:jenis_indikator,id',
            'kode_indikator' => 'required|string|max:100',
            'deskripsi_indikator' => 'required|string',
        ]);

        Indikator::create($validated);

        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $indikator = Indikator::with(['elemenStandar.kriteria', 'jenisIndikator'])->find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        return view('master-data.indikator.indikator.show', compact('indikator'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $indikator = Indikator::find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        $elemenStandar = \App\Models\ElemenStandar::with('kriteria')->get();
        $jenisIndikator = \App\Models\JenisIndikator::all();
        return view('master-data.indikator.indikator.edit', compact('indikator', 'elemenStandar', 'jenisIndikator'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_elemen' => 'required|exists:elemen_standar,id',
            'id_jenis' => 'required|exists:jenis_indikator,id',
            'kode_indikator' => 'required|string|max:100',
            'deskripsi_indikator' => 'required|string',
        ]);

        $indikator = Indikator::find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        $indikator->update($validated);

        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $indikator = Indikator::find($id);

        if (!$indikator) {
            return redirect()->route('indikator.index')->with('error', 'Indikator tidak ditemukan');
        }

        $indikator->delete();

        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil dihapus');
    }
}
