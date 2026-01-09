<?php

namespace App\Http\Controllers\Asesmen;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use Illuminate\Http\Request;
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

    public function uploadBeritaAcara(Request $request, $idAsesmen)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $request->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf|max:20480', // 20MB per file
        ]);

        $user = Auth::user();

        $baseDir = "public/asesmen/document/{$idAsesmen}";
        $maxSort = (int) AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'berita_acara')
            ->max('sort_order');

        $created = [];
        foreach ($request->file('files') as $file) {
            $maxSort++;

            $filename = 'berita_acara_' . now()->format('Ymd_His') . '_' . uniqid() . '.pdf';
            $storedPath = $file->storeAs($baseDir, $filename); // => public/...

            $created[] = AsesmenDocument::create([
                'id_asesmen' => $idAsesmen,
                'type' => 'berita_acara',
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'file_size' => $file->getSize() ?? 0,
                'sort_order' => $maxSort,
                'is_active' => true,
                'uploaded_by' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berita acara berhasil diupload',
            'data' => $created,
        ]);
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

        $abs = storage_path('app/' . $doc->stored_path);
        abort_unless(is_file($abs), 404, 'File tidak ditemukan');

        return response()->download($abs, $doc->original_name);
    }

    public function destroy($idAsesmen, $docId)
    {
        $this->assertAccessOrFail((int)$idAsesmen);

        $doc = AsesmenDocument::where('id_asesmen', $idAsesmen)->findOrFail($docId);

        Storage::delete($doc->stored_path);
        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Dokumen dihapus']);
    }
}
