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
     * Fungsi generik untuk upload dokumen
     */
    protected function uploadDokumen(
        Request $request,
        PengajuanAkreditasi $pengajuan,
        string $fieldName,
        string $jenisDokumen,
        string $folder,
        bool $isAddVersion = false,
        array $validations = [],
        string $defaultKeterangan = ''
    ) {
        $request->validate(array_merge([$fieldName => 'required|file'], $validations));

        DB::beginTransaction();

        try {
            if (in_array($pengajuan->status, [
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            ])) {
                return response()->json(['success' => false, 'message' => 'Sedang menunggu validasi Dokumen. Perubahan dokumen tidak diizinkan untuk sementara.'], 403);
            }
            $userId = auth()->id();

            $latest = PengajuanDokumen::where([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => $jenisDokumen,
                'is_latest' => true
            ])->first();

            // Tentukan versi
            if ($isAddVersion || !$latest) {
                $versi = ($latest->versi ?? 0) + 1;
                if ($latest) $latest->update(['is_latest' => false]);
            } else {
                $versi = $latest->versi;
            }

            $file = $request->file($fieldName);
            $filename = "{$jenisDokumen}_v{$versi}_" . time() . "." . $file->getClientOriginalExtension();
            $path = $file->storeAs("permohonan-akreditasi/{$pengajuan->id}/{$folder}", $filename, 'public');

            $dokumen = PengajuanDokumen::updateOrCreate(
                ['id' => $latest && !$isAddVersion ? $latest->id : null],
                [
                    'id_pengajuan' => $pengajuan->id,
                    'jenis_dokumen' => $jenisDokumen,
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => $userId,
                    'keterangan' => $request->keterangan ?? ($defaultKeterangan ?? "{$jenisDokumen} v{$versi}"),
                    'versi' => $versi,
                    'is_latest' => true,
                ]
            );

            // Log status
            PengajuanStatusLog::create([
                'id_pengajuan' => $pengajuan->id,
                'status_from' => $pengajuan->status,
                'status_to' => PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA,
                'changed_by' => $userId,
                'changed_at' => now(),
                'keterangan' => ($isAddVersion ? 'Upload versi baru' : 'Update tanpa versi') .
                    " {$jenisDokumen} (v{$versi})",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isAddVersion
                    ? ucfirst($jenisDokumen) . " diupload (v{$versi})"
                    : ucfirst($jenisDokumen) . " diperbarui (v{$versi})",
                'data' => [
                    'dokumen_id' => $dokumen->id,
                    'versi' => $versi
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Upload {$jenisDokumen} error", ['error' => $e]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi khusus untuk pengesahan
     */
    public function uploadPengesahan(Request $request, $id, $isAddVersion = false)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        return $this->uploadDokumen(
            $request,
            $pengajuan,
            'pengesahan',
            'lembar_pengesahan',
            'pengesahan',
            $isAddVersion,
            ['pengesahan' => 'mimes:pdf|max:5120']
        );
    }

    /**
     * Fungsi khusus untuk kuantitatif
     */
    public function uploadKuantitatif(Request $request, $id, $isAddVersion = false)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        return $this->uploadDokumen(
            $request,
            $pengajuan,
            'file_kuantitatif',
            'data_kuantitatif',
            'kuantitatif',
            $isAddVersion,
            ['file_kuantitatif' => 'mimes:xls,xlsx|max:10240']
        );
    }

    /**
     * Fungsi khusus untuk suplemen
     */
    public function uploadSuplemen(Request $request, $id, $isAddVersion = true)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        return $this->uploadDokumen(
            $request,
            $pengajuan,
            'file_suplemen',
            'data_suplemen',
            'suplemen',
            $isAddVersion,
            ['file_suplemen' => 'mimes:pdf,doc,docx,xls,xlsx,zip,rar|max:20480']
        );
    }

    /**
     * ✅ Get file history for specific document type
     */
    public function getFileHistory(Request $request, $id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $this->authorize('view', $pengajuan);

            $jenisDokumen = $request->get('jenis'); // pengesahan, kualitatif, kuantitatif

            // Map frontend names to DB names
            $jenisMap = [
                'pengesahan' => 'lembar_pengesahan',
                'kualitatif' => 'data_kualitatif',
                'kuantitatif' => 'data_kuantitatif',
            ];

            $dbJenis = $jenisMap[$jenisDokumen] ?? $jenisDokumen;

            $dokumens = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', $dbJenis)
                ->with('uploader:id,name')
                ->orderBy('versi', 'desc')
                ->get()
                ->map(function ($dok) {
                    return [
                        'id' => $dok->id,
                        'versi' => $dok->versi,
                        'filename' => $dok->original_filename,
                        'file_size' => $this->formatFileSize($dok->file_size),
                        'uploaded_by' => $dok->uploader->name ?? 'Unknown',
                        'uploaded_at' => $dok->created_at->locale('id')->translatedFormat('d M Y H:i'),
                        'is_latest' => $dok->is_latest,
                        'keterangan' => $dok->keterangan,
                        'download_url' => route('pengajuan.download-dokumen', [$dok->id_pengajuan, $dok->id]),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $dokumens
            ]);
        } catch (\Exception $e) {
            Log::error('Get file history failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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

            // ✅ Don't allow deletion of latest version
            if ($dokumen->is_latest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus versi terbaru. Upload versi baru terlebih dahulu.',
                ], 422);
            }

            // Hapus file fisik
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

    /**
     * ✅ STANDARD: Upload Data Kuantitatif (Excel) - Store only
     */
    // public function uploadKuantitatif(Request $request, $id)
    // {
    //     $request->validate([
    //         'file_kuantitatif' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB
    //     ]);

    //     try {
    //         $authId = auth()->id();
    //         $pengajuan = PengajuanAkreditasi::findOrFail($id);
    //         $this->authorize('update', $pengajuan);

    //         DB::beginTransaction();

    //         // Mark previous as not latest
    //         PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
    //             ->where('jenis_dokumen', 'data_kuantitatif')
    //             ->update(['is_latest' => false]);

    //         // Upload file
    //         $file = $request->file('file_kuantitatif');
    //         $filename = 'kuantitatif_' . time() . '.' . $file->getClientOriginalExtension();
    //         $path = $file->storeAs('permohonan-akreditasi/' . $pengajuan->id . '/kuantitatif', $filename, 'public');

    //         // ✅ STANDARD: Create document record
    //         $dokumen = PengajuanDokumen::create([
    //             'id_pengajuan' => $pengajuan->id,
    //             'jenis_dokumen' => 'data_kuantitatif', // ✅ Konsisten
    //             'nama_file' => $filename,
    //             'path_file' => $path,
    //             'original_filename' => $file->getClientOriginalName(),
    //             'file_size' => $file->getSize(),
    //             'mime_type' => $file->getMimeType(),
    //             'uploaded_by' => $authId,
    //             'keterangan' => 'LKPS (Data Kuantitatif)',
    //             'versi' => 1,
    //             'is_latest' => true,
    //         ]);

    //         // Log activity
    //         PengajuanStatusLog::create([
    //             'id_pengajuan' => $pengajuan->id,
    //             'status_from' => $pengajuan->status,
    //             'status_to' => $pengajuan->status,
    //             'changed_by' => $authId,
    //             'changed_at' => now(),
    //             'keterangan' => 'LKPS diupload: ' . $file->getClientOriginalName(),
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'LKPS berhasil diupload',
    //             'data' => [
    //                 'dokumen_id' => $dokumen->id,
    //                 'filename' => $dokumen->original_filename,
    //                 'file_size' => $this->formatFileSize($dokumen->file_size),
    //                 'uploaded_at' => $dokumen->created_at->locale('id')->translatedFormat('d M Y H:i'),
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Upload kuantitatif failed: ' . $e->getMessage(), [
    //             'pengajuan_id' => $id,
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal upload file: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}
