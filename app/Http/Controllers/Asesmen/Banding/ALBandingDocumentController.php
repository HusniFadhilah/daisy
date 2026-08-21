<?php

namespace App\Http\Controllers\Asesmen\Banding;

use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\AsesmenLapanganBanding;
use App\Models\AsesmenUserRole;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ALBandingDocumentController extends Controller
{
    public function page($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $asesmen = \App\Models\Asesmen::with(['studyProgram.university'])->findOrFail($idAsesmen);

        $docs = \App\Models\AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara_al_banding')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        // ✅ Cek apakah sudah ada yang upload
        $firstUpload = \App\Models\AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara_al_banding')
            ->with('uploader')
            ->orderBy('uploaded_at', 'asc')
            ->first();

        // ✅ Cek apakah user saat ini adalah yang pertama kali upload
        $currentUserId = Auth::id();
        $isUploader = $firstUpload && $firstUpload->uploaded_by == $currentUserId;

        // ✅ User bisa upload jika: belum ada upload ATAU dia adalah uploader pertama
        $canUpload = !$firstUpload || $isUploader;

        return view('asesmen.banding.al-banding.berkas.document', compact(
            'asesmen',
            'docs',
            'firstUpload',
            'canUpload',
            'isUploader'
        ));
    }

    // Function untuk get list files via JSON
    public function getFiles($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $docs = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara_al_banding')
            ->with(['uploader'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $docs->map(function ($doc) use ($idAsesmen) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'original_name' => $doc->original_name,
                    'size' => $doc->size,
                    'size_formatted' => number_format($doc->size / 1024, 2) . ' KB',
                    'uploaded_at' => $doc->uploaded_at ? $doc->uploaded_at->locale('id')->translatedFormat('d M Y, H:i') : '-',
                    'uploader_name' => $doc->uploader ? $doc->uploader->name : '-',
                    'download_url' => route('al_banding.berkas.documents.download', ['id' => $idAsesmen, 'docId' => $doc->id]),
                    'delete_url' => route('al_banding.berkas.documents.delete', [$idAsesmen, $doc->id]),
                ];
            })
        ]);
    }

    private function assertAccessOrFail(int $idAsesmen): void
    {
        $user = Auth::user();

        $ok = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al_banding')
            ->exists();

        abort_if(!$ok, 403, 'Unauthorized');
    }

    public function index($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $docs = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara_al_banding')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'data' => $docs]);
    }

    public function uploadDocument(Request $request, $idAsesmen)
    {
        try {
            $this->assertAccessOrFail((int)$idAsesmen);

            // ✅ CEK: Apakah sudah ada yang upload sebelumnya
            $existingDoc = AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'berita_acara_al_banding')
                ->first();

            $currentUserId = Auth::id();

            // ✅ VALIDASI: Hanya uploader pertama yang bisa upload/update
            if ($existingDoc && $existingDoc->uploaded_by != $currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Berita acara sudah diupload oleh asesor banding lain. Hanya asesor banding yang pertama mengupload yang dapat mengedit dokumen.',
                ], 403);
            }

            $request->validate([
                'files' => 'required|array|min:1',
                'files.*' => 'file|mimes:pdf|max:20480', // 20MB per file
            ]);

            $user = Auth::user();

            $baseDir = "asesmen/document/{$idAsesmen}";
            $maxSort = (int) AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'berita_acara_al_banding')
                ->max('sort_order');

            $created = [];
            foreach ($request->file('files') as $file) {
                $maxSort++;

                $tanggal = now()->locale('id')->isoFormat('DD MMM YYYY');
                $waktu = now()->format('H.i.s');
                $filename = "Hasil dan Berita Acara Asesmen Lapangan Banding_{$idAsesmen}_{$tanggal}_{$waktu}.pdf";

                $storedPath = $file->storeAs($baseDir, $filename, 'public');

                $created[] = AsesmenDocument::create([
                    'id_asesmen' => $idAsesmen,
                    'title' => 'Hasil dan Berita Acara Asesmen Lapangan Banding',
                    'type' => 'berita_acara_al_banding',
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $storedPath,
                    'size' => $file->getSize() ?? 0,
                    'mime' => $file->getMimeType(),
                    'sort_order' => $maxSort,
                    'is_active' => true,
                    'uploaded_by' => $user->id,
                    'uploaded_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Hasil dan Berita Acara Asesmen Lapangan (AL) Banding berhasil diupload',
                'data' => $created,
            ]);
        } catch (\Exception $e) {
            Log::error('Upload AL Document Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Hasil dan Berita Acara Asesmen Lapangan (AL) Banding gagal diupload: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reorder(Request $request, $idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $request->validate([
            'orders' => 'required|array|min:1',
            'orders.*.id' => 'required|integer|exists:asesmen_documents,id',
            'orders.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->orders as $row) {
            AsesmenDocument::where('id', $row['id'])
                ->where('id_asesmen', $idAsesmen)
                ->update(['sort_order' => $row['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Urutan berhasil disimpan']);
    }

    public function toggleActive($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);
        $doc->update(['is_active' => !$doc->is_active]);

        return response()->json(['success' => true, 'data' => $doc]);
    }

    public function download($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);

        $absolutePath = storage_path('app/public/' . $doc->path);
        abort_unless(is_file($absolutePath), 404, 'File tidak ditemukan');

        return response()->file($absolutePath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $doc->original_name . '"',
        ]);
    }

    public function preview($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);

        $absolutePath = storage_path('app/public/' . $doc->path);
        abort_unless(is_file($absolutePath), 404, 'File tidak ditemukan');

        $filename = $doc->original_name ?: 'document.pdf';

        return response()->stream(function () use ($absolutePath) {
            $stream = fopen($absolutePath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'Content-Length'      => filesize($absolutePath),
            'Accept-Ranges'       => 'bytes',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    public function destroy($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);

        // ✅ VALIDASI: Hanya uploader yang bisa menghapus
        $currentUserId = Auth::id();
        if ($doc->uploaded_by != $currentUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus dokumen ini. Hanya asesor banding yang mengupload yang dapat menghapus.',
            ], 403);
        }

        if (Storage::disk('public')->exists($doc->path)) {
            Storage::disk('public')->delete($doc->path);
        }

        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Dokumen dihapus']);
    }

    public function finalize($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            $docs = AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'berita_acara_al_banding')
                ->where('is_active', true);

            if ($docs->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada dokumen berita acara yang diupload. Silakan upload dokumen terlebih dahulu.'
                ], 422);
            }
            $docs->first()->update(['status_persetujuan_de' => 'approved']);

            $asesmen = Asesmen::findOrFail($idAsesmen);

            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'al_banding')
                ->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $user->id,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berita Acara berhasil difinalisasi. Status Asesmen Lapangan Banding telah diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error finalizing AL berita acara: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat finalisasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unfinalize($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        try {
            DB::beginTransaction();

            $asesmen = Asesmen::findOrFail($idAsesmen);
            $asesmen->update([
                'status' => 'active'
            ]);

            $asesmenLapanganBanding = AsesmenLapanganBanding::where('id_asesmen', $idAsesmen)->first();

            if ($asesmenLapanganBanding) {
                $asesmenLapanganBanding->update([
                    'status' => 'active',
                    'finalized_at' => null,
                    'finalized_by' => null,
                ]);
            }

            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'al_banding')
                ->update([
                    'status_pekerjaan' => 'submitted',
                    'approved_at' => null,
                    'approved_by' => null,
                ]);

            if ($asesmen->id_pengajuan) {
                PengajuanAkreditasi::where('id', $asesmen->id_pengajuan)
                    ->update([
                        'tanggal_al_banding_selesai' => null,
                        'status' => 'al_banding_in_progress'
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Finalisasi dibatalkan. Status dikembalikan ke aktif.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membatalkan finalisasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
