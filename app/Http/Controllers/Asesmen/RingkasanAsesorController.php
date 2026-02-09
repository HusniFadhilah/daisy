<?php

namespace App\Http\Controllers\Asesmen;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RingkasanAsesorController extends Controller
{
    private const TYPE_CONF = 'hasil_akreditasi_confidential';

    private function assertAccessOrFail(int $idAsesmen): void
    {
        $user = Auth::user();

        $ok = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->exists();

        abort_if(!$ok, 403, 'Unauthorized');
    }

    public function page($idAsesmen)
    {
        $this->assertAccessOrFail((int) $idAsesmen);

        $asesmen = Asesmen::with(['studyProgram.university'])->findOrFail($idAsesmen);

        $docsConf = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', self::TYPE_CONF)
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        // ✅ Cek apakah sudah ada yang upload
        $firstUpload = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', self::TYPE_CONF)
            ->with('uploader')
            ->orderBy('uploaded_at', 'asc')
            ->first();

        // ✅ Cek apakah user saat ini adalah yang pertama kali upload
        $currentUserId = Auth::id();
        $isUploader = $firstUpload && $firstUpload->uploaded_by == $currentUserId;

        // ✅ User bisa upload jika: belum ada upload ATAU dia adalah uploader pertama
        $canUpload = !$firstUpload || $isUploader;

        // ✅ Get team asesor
        $asesorTeam = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
            ->with('user')
            ->get();

        return view('asesmen.al.berkas.ringkasan-asesor', compact(
            'asesmen',
            'docsConf',
            'firstUpload',
            'canUpload',
            'isUploader',
            'asesorTeam'
        ));
    }

    public function upload(Request $request, $idAsesmen, $type)
    {
        try {
            $this->assertAccessOrFail((int) $idAsesmen);

            if (!in_array($type, [self::TYPE_CONF], true)) {
                return response()->json(['success' => false, 'message' => 'Type tidak valid'], 422);
            }

            // ✅ CEK: Apakah sudah ada yang upload sebelumnya
            $existingDoc = AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', $type)
                ->first();

            $currentUserId = Auth::id();

            // ✅ VALIDASI: Hanya uploader pertama yang bisa upload/update
            if ($existingDoc && $existingDoc->uploaded_by != $currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ringkasan hasil akreditasi sudah diupload oleh asesor lain. Hanya asesor yang pertama mengupload yang dapat menambah atau mengedit dokumen.',
                ], 403);
            }

            $request->validate([
                'file' => 'required|file|mimes:pdf|max:20480', // 20MB
                'title' => 'nullable|string|max:255',
            ]);

            $user = Auth::user();

            $baseDir = "asesmen/document/{$idAsesmen}/lha_asesor";
            $maxSort = (int) AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', $type)
                ->max('sort_order');

            $maxSort++;

            $file = $request->file('file');
            $tanggal = now()->locale('id')->isoFormat('DD MMM YYYY');
            $waktu = now()->format('H.i.s');
            $filenameBase = "Laporan_Hasil_Akreditasi_Confidential_{$idAsesmen}_{$tanggal}_{$waktu}.pdf";

            $storedPath = $file->storeAs($baseDir, $filenameBase, 'public');

            $defaultTitle = 'Laporan Hasil Akreditasi (Confidential)';

            $doc = AsesmenDocument::create([
                'id_asesmen' => $idAsesmen,
                'type' => $type,
                'title' => $request->filled('title') ? $request->input('title') : $defaultTitle,
                'sort_order' => $maxSort,
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize() ? (int) $file->getSize() : 0,
                'mime' => $file->getMimeType(),
                'is_active' => true,
                'version' => 1,
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
                'status_persetujuan_prodi' => 'pending',
                'status_persetujuan_de' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'data' => $doc,
            ]);
        } catch (\Exception $e) {
            Log::error('Upload LHA Asesor Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal upload: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function download($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int) $idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);

        $absolutePath = storage_path('app/public/' . $doc->path);
        abort_unless(is_file($absolutePath), 404, 'File tidak ditemukan');

        $downloadName = "Laporan_Hasil_Akreditasi_Confidential_{$idAsesmen}.pdf";

        return response()->download($absolutePath, $downloadName);
    }

    public function destroy($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int) $idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);

        // ✅ VALIDASI: Hanya uploader yang bisa menghapus
        $currentUserId = Auth::id();
        if ($doc->uploaded_by != $currentUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus dokumen ini. Hanya asesor yang mengupload yang dapat menghapus.',
            ], 403);
        }

        if (Storage::disk('public')->exists($doc->path)) {
            Storage::disk('public')->delete($doc->path);
        }

        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Dokumen berhasil dihapus']);
    }
}
