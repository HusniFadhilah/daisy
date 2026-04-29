<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Models\JenisIndikator;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class JenisIndikatorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = JenisIndikator::select('jenis_indikator.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<div class="btn-group" role="group">';
                    $btn .= '<a href="' . route('jenis-indikator.edit', $row->id) . '" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>';
                    $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRecord('.$row->id.')"><i class="bi bi-trash"></i></button>';
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

        $jenisIndikators = JenisIndikator::orderBy('nama_jenis')->get();

        return view('master-data.indikator.jenis.index', compact('jenisIndikators'));
    }

    public function create()
    {
        return view('master-data.indikator.jenis.create');
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

        if (!$request->wantsJson()) {
            return redirect()->route('jenis-indikator.index')
                ->with('success', 'Jenis indikator berhasil ditambahkan.');
        }

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

    public function edit($id)
    {
        $jenisIndikator = JenisIndikator::findOrFail($id);

        return view('master-data.indikator.jenis.edit', compact('jenisIndikator'));
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
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis Indikator not found'
                ], 404);
            }

            return redirect()->route('jenis-indikator.index')->with('error', 'Jenis indikator tidak ditemukan.');
        }

        $jenisIndikator->update($validated);

        if (!$request->wantsJson()) {
            return redirect()->route('jenis-indikator.index')
                ->with('success', 'Jenis indikator berhasil diperbarui.');
        }

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
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis Indikator not found'
                ], 404);
            }

            return redirect()->route('jenis-indikator.index')->with('error', 'Jenis indikator tidak ditemukan.');
        }

        $jenisIndikator->delete();

        if (!request()->wantsJson()) {
            return redirect()->route('jenis-indikator.index')
                ->with('success', 'Jenis indikator berhasil dihapus.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Jenis Indikator deleted successfully.'
        ]);
    }
}
