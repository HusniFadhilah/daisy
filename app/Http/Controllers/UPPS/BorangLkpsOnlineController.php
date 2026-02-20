<?php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\BorangDataExcel;
use App\Models\PengajuanAkreditasi;
use App\Services\BorangImport\LkpsTableDefinitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BorangLkpsOnlineController extends Controller
{
    public function __construct(
        private LkpsTableDefinitionService $definitionService
    ) {}

    /**
     * Get semua definisi tabel LKPS + data existing (jika ada)
     */
    public function definitions(PengajuanAkreditasi $pengajuan): JsonResponse
    {
        // Pastikan relasi tidak menyebabkan N+1
        $pengajuan->loadMissing([
            'studyProgram:id,id_degree_level'
        ]);

        $degreeLevelId = $pengajuan->studyProgram->id_degree_level ?? null;

        // Ambil definitions (pastikan di dalam service juga tidak ada N+1)
        $definitions = collect(
            $this->definitionService->getDefinitions($degreeLevelId)
        );

        // Ambil hanya kolom yang diperlukan (lebih ringan)
        $existing = BorangDataExcel::select([
            'id',
            'id_dataset_borang',
            'rows',
            'status_review',
            'updated_at'
        ])
            ->where('id_pengajuan', $pengajuan->id)
            ->where('source', 'online_input')
            ->get()
            ->keyBy('id_dataset_borang');

        // Mapping tanpa reference (&) agar lebih aman
        $definitions = $definitions->map(function ($group) use ($existing) {

            $group['tables'] = collect($group['tables'])->map(function ($table) use ($existing) {

                $saved = $existing->get($table['dataset_borang_id']);

                $table['existing'] = $saved ? [
                    'id'            => $saved->id,
                    'rows'          => $saved->rows,
                    'status_review' => $saved->status_review,
                    'updated_at'    => $saved->updated_at?->diffForHumans(),
                ] : null;

                return $table;
            })->values();

            return $group;
        })->values();

        return response()->json([
            'success'     => true,
            'definitions' => $definitions,
        ]);
    }
    /**
     * Load data existing satu tabel
     */
    public function load(PengajuanAkreditasi $pengajuan, int $datasetBorangId): JsonResponse
    {
        $data = BorangDataExcel::where('id_pengajuan', $pengajuan->id)
            ->where('id_dataset_borang', $datasetBorangId)
            ->where('source', 'online_input')
            ->first();

        return response()->json([
            'success' => true,
            'rows' => $data?->rows ?? [],
        ]);
    }

    /**
     * Simpan data tabel secara online
     */
    public function save(Request $request, PengajuanAkreditasi $pengajuan): JsonResponse
    {
        $request->validate([
            'dataset_borang_id' => 'required|exists:dataset_borang,id',
            'rows' => 'required|array',
            'rows.*' => 'array',
        ]);

        $degreeLevelId = $pengajuan->studyProgram->id_degree_level ?? null;
        $definition = $this->definitionService->getTableDefinition(
            $request->dataset_borang_id,
            $degreeLevelId
        );

        if (!$definition) {
            return response()->json(['success' => false, 'message' => 'Definisi tabel tidak ditemukan'], 404);
        }

        // Filter baris kosong
        $rows = array_values(array_filter(
            $request->rows,
            fn($row) => array_filter($row, fn($cell) => $cell !== null && $cell !== '')
        ));

        $record = BorangDataExcel::updateOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'id_dataset_borang' => $request->dataset_borang_id,
                'source' => 'online_input',
            ],
            [
                'id_degree_level' => $degreeLevelId,
                'id_borang_import' => null,
                'sheet_name' => $definition['dataset_kode'],
                'elemen_kode' => explode('.', $definition['dataset_kode'])[0]
                    . '.' . explode('.', $definition['dataset_kode'])[1],
                'table_index' => 1,
                'table_title' => $definition['table_title'],
                'headers' => $definition['headers'],
                'rows' => $rows,
                'status_review' => 'raw',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan.',
            'id' => $record->id,
            'row_count' => count($rows),
            'updated_at' => $record->updated_at->diffForHumans(),
        ]);
    }

    /**
     * Hapus data online satu tabel
     */
    public function destroy(PengajuanAkreditasi $pengajuan, int $id): JsonResponse
    {
        $record = BorangDataExcel::where('id_pengajuan', $pengajuan->id)
            ->where('source', 'online_input')
            ->findOrFail($id);

        $record->delete();

        return response()->json(['success' => true, 'message' => 'Data dihapus.']);
    }
}
