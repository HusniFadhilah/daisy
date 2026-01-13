<?php

namespace App\Http\Controllers\Prodi;

use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\ImportDataKualitatifJob;
use App\Services\BorangParserService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BorangUploadController extends Controller
{
    use AuthorizesRequests;
    /**
     * ✅ STANDARD: Upload Lembar Pengesahan (PDF)
     */
    public function uploadPengesahan(Request $request, $id)
    {
        $request->validate([
            'pengesahan' => 'required|file|mimes:pdf|max:5120', // 5MB
        ]);

        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            DB::beginTransaction();

            // Mark previous as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'pengesahan')
                ->update(['is_latest' => false]);

            // Upload file
            $file = $request->file('pengesahan');
            $filename = 'pengesahan_' . time() . '.pdf';
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/pengesahan', $filename, 'public');

            // ✅ STANDARD: Create document record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'pengesahan', // ✅ Konsisten
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => $authId,
                'keterangan' => 'Lembar pengesahan yang sudah ditandatangani',
                'versi' => 1,
                'is_latest' => true,
            ]);

            // Log activity
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => 'Lembar pengesahan diupload: ' . $file->getClientOriginalName(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lembar pengesahan berhasil diupload',
                'data' => [
                    'dokumen_id' => $dokumen->id,
                    'filename' => $dokumen->original_filename,
                    'file_size' => $this->formatFileSize($dokumen->file_size),
                    'uploaded_at' => $dokumen->created_at->format('d M Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload pengesahan failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload lembar pengesahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ STANDARD: Upload Data Kualitatif (DOCX) - Auto process
     */
    public function uploadKualitatif(Request $request, $id)
    {
        $request->validate([
            'file_kualitatif' => 'required|file|mimes:docx|max:10240', // 10MB
        ]);

        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            DB::beginTransaction();

            // Mark previous as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kualitatif')
                ->update(['is_latest' => false]);

            // Upload file
            $file = $request->file('file_kualitatif');
            $filename = 'kualitatif_' . time() . '.docx';
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/kualitatif', $filename, 'public');

            // ✅ STANDARD: Create document record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'data_kualitatif', // ✅ Konsisten
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => $authId,
                'keterangan' => 'Laporan Evaluasi Diri (Data Kualitatif)',
                'versi' => 1,
                'is_latest' => true,
            ]);

            // ✅ Auto-process DOCX
            try {
                $parserService = new BorangParserService();
                $import = $parserService->parseBorangDOCX($dokumen);

                Log::info('Kualitatif DOCX parsed successfully', [
                    'pengajuan_id' => $id,
                    'import_id' => $import->id,
                    'sections' => $import->total_sections,
                    'tables' => $import->total_tables
                ]);

                $processingMessage = "File diupload dan diproses: {$import->parsed_sections}/{$import->total_sections} sections";
            } catch (\Exception $parseError) {
                Log::warning('DOCX parsing failed, but file uploaded', [
                    'pengajuan_id' => $id,
                    'error' => $parseError->getMessage()
                ]);

                $processingMessage = 'File diupload. Data akan diproses manual.';
            }

            // Log activity
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => 'Laporan Evaluasi Diri diupload: ' . $file->getClientOriginalName(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan Evaluasi Diri berhasil diupload dan diproses',
                'data' => [
                    'dokumen_id' => $dokumen->id,
                    'filename' => $dokumen->original_filename,
                    'file_size' => $this->formatFileSize($dokumen->file_size),
                    'uploaded_at' => $dokumen->created_at->format('d M Y H:i'),
                    'processing_note' => $processingMessage ?? 'File diupload'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload kualitatif failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ STANDARD: Upload Data Kuantitatif (Excel) - Store only
     */
    public function uploadKuantitatif(Request $request, $id)
    {
        $request->validate([
            'file_kuantitatif' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB
        ]);

        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('update', $pengajuan);

            DB::beginTransaction();

            // Mark previous as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'data_kuantitatif')
                ->update(['is_latest' => false]);

            // Upload file
            $file = $request->file('file_kuantitatif');
            $filename = 'kuantitatif_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/kuantitatif', $filename, 'public');

            // ✅ STANDARD: Create document record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'data_kuantitatif', // ✅ Konsisten
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => $authId,
                'keterangan' => 'LKPS (Data Kuantitatif)',
                'versi' => 1,
                'is_latest' => true,
            ]);

            // Log activity
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => 'LKPS diupload: ' . $file->getClientOriginalName(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'LKPS berhasil diupload',
                'data' => [
                    'dokumen_id' => $dokumen->id,
                    'filename' => $dokumen->original_filename,
                    'file_size' => $this->formatFileSize($dokumen->file_size),
                    'uploaded_at' => $dokumen->created_at->format('d M Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Upload kuantitatif failed: ' . $e->getMessage(), [
                'pengajuan_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file yang sudah diupload
     */
    public function downloadDokumen($id, $dokumenId)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Lebih aman: pakai constraints + firstOrFail (bukan findOrFail)
            $dokumen = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('id', $dokumenId)
                ->firstOrFail();

            if (!$dokumen->path_file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen tidak berbasis file (path_file kosong).',
                ], 400);
            }

            if (!Storage::disk('public')->exists($dokumen->path_file)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File tidak ditemukan',
                ], 404);
            }

            return Storage::disk('public')->download(
                $dokumen->path_file,
                $dokumen->original_filename ?? $dokumen->nama_file
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal download file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete uploaded file
     */
    public function deleteDokumen($id, $dokumenId)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            $dokumen = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('id', $dokumenId)
                ->firstOrFail();

            // Hapus file fisik kalau memang file-based
            if ($dokumen->path_file && Storage::disk('public')->exists($dokumen->path_file)) {
                Storage::disk('public')->delete($dokumen->path_file);
            }

            $dokumen->delete();

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: format ukuran file
     */
    private function formatFileSize(?int $bytes): string
    {
        $bytes = (int) ($bytes ?? 0);
        if ($bytes <= 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, $k));
        $i = min($i, count($sizes) - 1);

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
