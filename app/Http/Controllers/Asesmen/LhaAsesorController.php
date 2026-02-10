<?php
// app/Http/Controllers/Asesmen/LhaAsesorController.php

namespace App\Http\Controllers\Asesmen;

use App\Models\Asesmen;
use App\Models\LhaAsesor;
use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // ✅ Get atau create 1 LHA untuk asesmen ini (tanpa id_user)
        $lha = LhaAsesor::with([
            'pendahuluanEditor',
            'prosesAlEditor',
            'hasilAlEditor',
            'rekomendasiPsEditor',
            'rekomendasiLamdepilarEditor',
        ])->firstOrCreate(
            ['id_asesmen' => $idAsesmen],
            [
                'created_by' => $user->id,
                'status' => 'draft',
            ]
        );

        // ✅ Get team asesor
        $asesorTeam = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('jenis_asesmen', 'al')
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor');
            })
            ->with('user')
            ->get();

        return view('asesmen.lha-asesor.index', compact(
            'asesmen',
            'lha',
            'asesorTeam'
        ));
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
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // ✅ Update data dengan tracking per field
        $lha = LhaAsesor::where('id_asesmen', $idAsesmen)->firstOrFail();

        // Check if finalized
        if ($lha->isFinalized()) {
            return response()->json([
                'success' => false,
                'message' => 'LHA sudah difinalisasi dan tidak dapat diubah'
            ], 422);
        }

        $updateData = ['updated_by' => $user->id];

        // ✅ Track per-field updates
        if ($request->has('pendahuluan')) {
            $updateData['pendahuluan'] = $request->pendahuluan;
            $updateData['pendahuluan_updated_by'] = $user->id;
            $updateData['pendahuluan_updated_at'] = now();
        }

        if ($request->has('proses_al')) {
            $updateData['proses_al'] = $request->proses_al;
            $updateData['proses_al_updated_by'] = $user->id;
            $updateData['proses_al_updated_at'] = now();
        }

        if ($request->has('hasil_al')) {
            $updateData['hasil_al'] = $request->hasil_al;
            $updateData['hasil_al_updated_by'] = $user->id;
            $updateData['hasil_al_updated_at'] = now();
        }

        if ($request->has('rekomendasi_ps')) {
            $updateData['rekomendasi_ps'] = $request->rekomendasi_ps;
            $updateData['rekomendasi_ps_updated_by'] = $user->id;
            $updateData['rekomendasi_ps_updated_at'] = now();
        }

        if ($request->has('rekomendasi_lamdepilar')) {
            $updateData['rekomendasi_lamdepilar'] = $request->rekomendasi_lamdepilar;
            $updateData['rekomendasi_lamdepilar_updated_by'] = $user->id;
            $updateData['rekomendasi_lamdepilar_updated_at'] = now();
        }

        $lha->update($updateData);

        // Reload relations
        $lha->load([
            'pendahuluanEditor',
            'prosesAlEditor',
            'hasilAlEditor',
            'rekomendasiPsEditor',
            'rekomendasiLamdepilarEditor',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'LHA berhasil disimpan',
            'completion' => $lha->getCompletionPercentage(),
            'field_editors' => [
                'pendahuluan' => $lha->getFieldEditorInfo('pendahuluan'),
                'proses_al' => $lha->getFieldEditorInfo('proses_al'),
                'hasil_al' => $lha->getFieldEditorInfo('hasil_al'),
                'rekomendasi_ps' => $lha->getFieldEditorInfo('rekomendasi_ps'),
                'rekomendasi_lamdepilar' => $lha->getFieldEditorInfo('rekomendasi_lamdepilar'),
            ],
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
        ])->findOrFail($idAsesmen);

        $lha = LhaAsesor::where('id_asesmen', $idAsesmen)->firstOrFail();

        // Check access
        $user = Auth::user();
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
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
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $lha = LhaAsesor::where('id_asesmen', $idAsesmen)->firstOrFail();

        // ✅ Validate completion
        if ($lha->getCompletionPercentage() < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Harap lengkapi semua bagian LHA sebelum finalisasi. Saat ini baru ' . $lha->getCompletionPercentage() . '% terisi.'
            ], 422);
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

            $asesmen->asesmenLapangan->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => $user->id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'LHA berhasil difinalisasi dan PDF telah dibuat.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error finalizing LHA: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal finalisasi LHA: ' . $e->getMessage()
            ], 500);
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
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al')
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
