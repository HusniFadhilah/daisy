<?php

namespace App\Http\Controllers\Asesmen;

use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ALDocumentController extends Controller
{
    public function page($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $asesmen = \App\Models\Asesmen::with(['studyProgram.university'])->findOrFail($idAsesmen);

        $docs = \App\Models\AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        return view('asesmen.al.berkas.document', compact('asesmen', 'docs'));
    }

    // Function untuk get list files via JSON
    public function getFiles($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $docs = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara')
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
                    'uploaded_at' => $doc->uploaded_at ? $doc->uploaded_at->format('d M Y, H:i') : '-',
                    'uploader_name' => $doc->uploader ? $doc->uploader->name : '-',
                    'download_url' => route('al.berkas.documents.download', ['id' => $idAsesmen, 'docId' => $doc->id]),
                    'delete_url' => route('al.berkas.documents.delete', [$idAsesmen, $doc->id]),
                ];
            })
        ]);
    }

    private function assertAccessOrFail(int $idAsesmen): void
    {
        $user = Auth::user();

        $ok = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->exists();

        abort_if(!$ok, 403, 'Unauthorized');
    }

    public function index($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $docs = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'data' => $docs]);
    }

    public function uploadDocument(Request $request, $idAsesmen)
    {
        try {
            $this->assertAccessOrFail((int)$idAsesmen);

            $request->validate([
                'files' => 'required|array|min:1',
                'files.*' => 'file|mimes:pdf|max:20480', // 20MB per file
            ]);

            $user = Auth::user();

            $baseDir = "asesmen/document/{$idAsesmen}";
            $maxSort = (int) AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'berita_acara')
                ->max('sort_order');

            $created = [];
            foreach ($request->file('files') as $file) {
                $maxSort++;

                // ✅ Format nama file yang lebih deskriptif
                // Format: Hasil dan Berita Acara Asesmen Lapangan - ID 2 - 12 Jan 2026 - 14.30.45.pdf
                $tanggal = now()->locale('id')->isoFormat('DD MMM YYYY');
                $waktu = now()->format('H.i.s');
                $filename = "Hasil dan Berita Acara Asesmen Lapangan_{$idAsesmen}_{$tanggal}.pdf";

                // storeAs dengan disk 'public'
                $storedPath = $file->storeAs($baseDir, $filename, 'public');

                // Full path untuk verifikasi
                $fullPath = storage_path('app/public/' . $storedPath);

                $created[] = AsesmenDocument::create([
                    'id_asesmen' => $idAsesmen,
                    'title' => 'Hasil dan Berita Acara Asesmen Lapangan',
                    'type' => 'berita_acara',
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
                'message' => 'Hasil dan Berita Acara Asesmen Lapangan (AL) berhasil diupload',
                'data' => $created,
            ]);
        } catch (\Exception $e) {
            Log::error('Upload AL Document Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Hasil dan Berita Acara Asesmen Lapangan (AL) gagal diupload: ' . $e->getMessage(),
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

        // ✅ Generate nama download dari data dokumen
        $tanggal = $doc->uploaded_at ? $doc->uploaded_at->locale('id')->isoFormat('DD MMM YYYY') : now()->locale('id')->isoFormat('DD MMM YYYY');
        $downloadName = "Hasil dan Berita Acara Asesmen Lapangan_{$idAsesmen}_{$tanggal}.pdf";

        return response()->download($absolutePath, $downloadName);
    }

    public function destroy($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);

        if (Storage::disk('public')->exists($doc->path)) {
            Storage::disk('public')->delete($doc->path);
        }

        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Dokumen dihapus']);
    }

    /**
     * Finalisasi Berita Acara AL
     * Update status di: asesmens, asesmen_lapangan, asesmen_user_roles
     */
    public function finalize($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // 1. Cek apakah ada dokumen berita acara
            $docsCount = AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'berita_acara')
                ->where('is_active', true)
                ->count();

            if ($docsCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada dokumen berita acara yang diupload. Silakan upload dokumen terlebih dahulu.'
                ], 422);
            }

            // 2. Update Asesmen (main table)
            $asesmen = Asesmen::findOrFail($idAsesmen);

            // 4. Update Asesmen User Roles (semua asesor AL)
            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'al')
                ->update([
                    'status_pekerjaan' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => $user->id,
                ]);

            // 5. Update tanggal selesai AL di pengajuan_akreditasi (jika ada)
            if ($asesmen->pengajuan)
                $asesmen->pengajuan->checkUpdateStatusAKAL('al', 'status_asesor_selesai');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berita Acara berhasil difinalisasi. Status Asesmen Lapangan telah diperbarui.'
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

    /**
     * Batalkan finalisasi (jika diperlukan)
     */
    public function unfinalize($idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        try {
            DB::beginTransaction();

            // 1. Update Asesmen (main table)
            $asesmen = Asesmen::findOrFail($idAsesmen);
            $asesmen->update([
                'status' => 'active'
            ]);

            // 2. Update Asesmen Lapangan
            $asesmenLapangan = AsesmenLapangan::where('id_asesmen', $idAsesmen)->first();

            if ($asesmenLapangan) {
                $asesmenLapangan->update([
                    'status' => 'active',
                    'completed_at' => null,
                    'completed_by' => null,
                ]);
            }

            // 3. Update Asesmen User Roles
            AsesmenUserRole::where('id_asesmen', $idAsesmen)
                ->where('jenis_asesmen', 'al')
                ->update([
                    'status_pekerjaan' => 'submitted',
                    'approved_at' => null,
                    'approved_by' => null,
                ]);

            // 4. Update pengajuan_akreditasi (jika ada)
            if ($asesmen->id_pengajuan) {
                PengajuanAkreditasi::where('id', $asesmen->id_pengajuan)
                    ->update([
                        'tanggal_al_selesai' => null,
                        'status' => 'al_in_progress'
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
