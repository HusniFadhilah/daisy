<?php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Jobs\ImportBorangExcelJob;
use App\Models\BorangDataExcel;
use App\Models\BorangImport;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BorangLkpsImportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Upload & dispatch import job
     */
    public function import(Request $request, PengajuanAkreditasi $pengajuan): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:20480',
            'id_degree_level' => 'nullable|exists:degree_levels,id',
        ]);

        // $this->authorize('update', $pengajuan);

        $file = $request->file('file');
        $filename = 'lkps_' . $pengajuan->nomor_pengajuan . '_' . time()
            . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('temp/lkps_imports', $filename);
        $idDegreeLevel = $pengajuan->studyProgram->id_degree_level;
        $import = BorangImport::create([
            'id_pengajuan' => $pengajuan->id,
            'id_degree_level' => $idDegreeLevel,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $filePath,
            'status' => 'pending',
            'imported_by' => Auth::id(),
        ]);

        // Simpan juga sebagai dokumen pengajuan (file referensi)
        $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'data_kuantitatif',
            'original_filename' => $file->getClientOriginalName(),
            'path_file' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => Auth::id(),
            'versi' => $pengajuan->dokumen()
                ->where('jenis_dokumen', 'data_kuantitatif')
                ->count() + 1,
        ]);

        ImportBorangExcelJob::dispatch(
            $import->id,
            $filePath,
            $pengajuan->id,
            Auth::id(),
            $idDegreeLevel
        );

        return response()->json([
            'success' => true,
            'message' => 'File LKPS berhasil diupload. Sedang diproses...',
            'borang_import_id' => $import->id,
        ]);
    }

    /**
     * Polling status import
     */
    public function status(PengajuanAkreditasi $pengajuan, int $importId): JsonResponse
    {
        $import = BorangImport::where('id_pengajuan', $pengajuan->id)
            ->findOrFail($importId);

        return response()->json([
            'success' => true,
            'status' => $import->status,
            'summary' => $import->getSummary(),
        ]);
    }

    /**
     * Get semua tabel yang sudah diimport (grouped by sheet)
     */
    public function getData(PengajuanAkreditasi $pengajuan): JsonResponse
    {
        $data = BorangDataExcel::where('id_pengajuan', $pengajuan->id)
            ->with('borangImport:id,original_filename,created_at,status')
            ->orderBy('sheet_name')
            ->orderBy('table_index')
            ->get()
            ->groupBy('sheet_name')
            ->map(fn($tables) => $tables->map(fn($t) => [
                'id' => $t->id,
                'sheet_name' => $t->sheet_name,
                'elemen_kode' => $t->elemen_kode,
                'table_index' => $t->table_index,
                'table_title' => $t->table_title,
                'row_count' => count($t->rows ?? []),
                'status_review' => $t->status_review,
                'import_file' => $t->borangImport?->original_filename,
                'updated_at' => $t->updated_at->diffForHumans(),
            ]));

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => BorangDataExcel::where('id_pengajuan', $pengajuan->id)->count(),
        ]);
    }

    /**
     * Preview satu tabel sebagai HTML
     */
    public function getTableHtml(PengajuanAkreditasi $pengajuan, int $id): JsonResponse
    {
        $table = BorangDataExcel::where('id_pengajuan', $pengajuan->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'html' => $table->toHtml(),
            'table_title' => $table->table_title,
            'sheet_name' => $table->sheet_name,
        ]);
    }

    /**
     * Review tabel (approve/reject)
     */
    public function review(Request $request, PengajuanAkreditasi $pengajuan, int $id): JsonResponse
    {
        $request->validate([
            'status_review' => 'required|in:approved,rejected,reviewed',
            'catatan_review' => 'nullable|string|max:1000',
        ]);

        $table = BorangDataExcel::where('id_pengajuan', $pengajuan->id)
            ->findOrFail($id);

        $table->update([
            'status_review' => $request->status_review,
            'catatan_review' => $request->catatan_review,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Jika approved, sync ke borang_data
        if ($request->status_review === 'approved') {
            $table->syncToBorangData();
        }

        return response()->json([
            'success' => true,
            'message' => 'Review berhasil disimpan.',
        ]);
    }
}
