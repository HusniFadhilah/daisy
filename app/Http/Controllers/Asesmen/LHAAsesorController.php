<?php
// app/Http/Controllers/Asesmen/LhaAsesorController.php

namespace App\Http\Controllers\Asesmen;

use App\Models\Asesmen;
use App\Models\LhaAsesor;
use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class LhaAsesorController extends Controller
{
    /**
     * Show LHA form
     */
    public function index($idAsesmen)
    {
        $asesmen = Asesmen::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'asesmenLapangan',
        ])->findOrFail($idAsesmen);

        // Check access - asesor AL only
        $user = Auth::user();
        $hasAccess = $asesmen->asesorAL()
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Get or create LHA
        $lha = LhaAsesor::firstOrCreate(
            ['id_asesmen' => $idAsesmen],
            [
                'created_by' => $user->id,
                'status' => 'draft',
            ]
        );

        return view('asesmen.lha-asesor.index', compact('asesmen', 'lha'));
    }

    /**
     * Save/Update LHA (auto-save)
     */
    public function save(Request $request, $idAsesmen)
    {
        $request->validate([
            'pendahuluan' => 'nullable|string',
            'proses_al' => 'nullable|string',
            'hasil_al' => 'nullable|string',
            'rekomendasi_ps' => 'nullable|string',
            'rekomendasi_lamdepilar' => 'nullable|string',
        ]);

        $asesmen = Asesmen::findOrFail($idAsesmen);
        $user = Auth::user();

        // Check access
        $hasAccess = $asesmen->asesorAL()
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $lha = LhaAsesor::updateOrCreate(
            ['id_asesmen' => $idAsesmen],
            [
                'pendahuluan' => $request->pendahuluan,
                'proses_al' => $request->proses_al,
                'hasil_al' => $request->hasil_al,
                'rekomendasi_ps' => $request->rekomendasi_ps,
                'rekomendasi_lamdepilar' => $request->rekomendasi_lamdepilar,
                'updated_by' => $user->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'LHA berhasil disimpan',
            'completion' => $lha->getCompletionPercentage(),
        ]);
    }

    /**
     * Preview LHA as PDF
     */
    public function preview($idAsesmen)
    {
        $asesmen = Asesmen::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'asesmenLapangan',
            'asesorAL.user',  // ✅ Tambahkan ini
            'documents' => function ($q) {
                $q->where('type', 'lha_asesor')
                    ->where('is_active', true);
            }
        ])->findOrFail($idAsesmen);

        $lha = LHAAsesor::where('id_asesmen', $idAsesmen)->firstOrFail();

        // Check access
        $user = Auth::user();
        $hasAccess = $asesmen->asesorAL()
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $pdf = Pdf::loadView('asesmen.lha-asesor.pdf', compact('asesmen', 'lha'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('LHA-Preview.pdf');
    }

    /**
     * Finalize and generate PDF
     */
    public function finalize(Request $request, $idAsesmen)
    {
        $asesmen = Asesmen::with('pengajuan')->findOrFail($idAsesmen);
        $user = Auth::user();

        // Check access
        $hasAccess = $asesmen->asesorAL()
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->exists();

        if (!$hasAccess) {
            return back()->with('error', 'Akses ditolak');
        }

        $lha = LhaAsesor::where('id_asesmen', $idAsesmen)->firstOrFail();

        // Validate completion
        if ($lha->getCompletionPercentage() < 100) {
            return back()->with('error', 'Harap lengkapi semua bagian LHA sebelum finalisasi.');
        }

        DB::beginTransaction();
        try {
            // Update LHA status
            $lha->update([
                'status' => 'finalized',
                'finalized_at' => now(),
                'updated_by' => $user->id,
            ]);

            // Generate PDF
            $pdf = Pdf::loadView('asesmen.lha-asesor.pdf', compact('asesmen', 'lha'))
                ->setPaper('a4', 'portrait');

            // Save PDF to storage
            $filename = 'LHA_' . $asesmen->code . '_' . now()->format('YmdHis') . '.pdf';
            $path = "asesmen/{$idAsesmen}/lha/{$filename}";

            Storage::disk('public')->put($path, $pdf->output());

            // Deactivate old LHA documents
            AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'lha_asesor')
                ->update(['is_active' => false]);

            // Create new document record
            $document = AsesmenDocument::create([
                'id_asesmen' => $idAsesmen,
                'type' => 'lha_asesor',
                'title' => 'Laporan Hasil Asesmen Lapangan (LHA)',
                'path' => $path,
                'original_name' => $filename,
                'size' => Storage::disk('public')->size($path),
                'mime' => 'application/pdf',
                'is_active' => true,
                'version' => 1,
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('al.berkas.lha-asesor.page', $idAsesmen)
                ->with('success', 'LHA berhasil difinalisasi dan PDF telah dibuat. Dokumen siap untuk ditinjau oleh Program Studi.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error finalizing LHA: ' . $e->getMessage());
            return back()->with('error', 'Gagal finalisasi LHA: ' . $e->getMessage());
        }
    }

    /**
     * Download LHA PDF
     */
    public function download($idAsesmen)
    {
        $asesmen = Asesmen::findOrFail($idAsesmen);
        $user = Auth::user();

        // Check access
        $hasAccess = $asesmen->asesorAL()
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Akses ditolak');
        }

        $document = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'lha_asesor')
            ->where('is_active', true)
            ->latest('uploaded_at')
            ->firstOrFail();

        if (!Storage::disk('public')->exists($document->path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($document->path, $document->original_name);
    }
}
