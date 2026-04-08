<?php
// app/Http/Controllers/Asesmen/LhaAsesorController.php

namespace App\Http\Controllers\Asesmen\Banding;

use App\Http\Controllers\Controller;
use App\Mail\Reminder\ReminderContextMail;
use App\Models\Asesmen;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use App\Models\LhaAsesorBanding;
use App\Models\PenilaianElemenAlBanding;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LhaAsesorBandingController extends Controller
{
    private RecipientResolverService $recipientResolver;
    private MailDeliveryService $mailDelivery;

    public function __construct(
        RecipientResolverService $recipientResolver,
        MailDeliveryService $mailDelivery,
    ) {
        $this->recipientResolver = $recipientResolver;
        $this->mailDelivery      = $mailDelivery;
    }

    /**
     * Show LHS form
     */
    public function index($idAsesmen)
    {
        $asesmen = Asesmen::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'asesmenLapanganBanding',
        ])->findOrFail($idAsesmen);

        // Check access - asesor AL only
        $user = Auth::user();
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al_banding')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        // ✅ Get atau create 1 LHS untuk asesmen ini (tanpa id_user)
        $lha = LhaAsesorBanding::with([
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
            ->where('jenis_asesmen', 'al_banding')
            ->whereHas('role', function ($q) {
                $q->where('name', 'asesor_banding');
            })
            ->with('user')
            ->get();
        $statusPekerjaan = $asesorTeam->pluck('status_pekerjaan');
        if (!$statusPekerjaan->contains('approved')) {
            return redirect()->back()->with('warning', 'Masih terdapat penilaian AL banding yang belum difinalisasi. Mohon lakukan finalisasi terlebih dahulu');
        }

        $lhaDocument = $asesmen->lhaDocumentsBanding->first();

        return view('asesmen.banding.lha-asesor.index', compact(
            'asesmen',
            'lha',
            'asesorTeam',
            'lhaDocument'
        ));
    }

    /**
     * Save/Update LHS (auto-save)
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
            ->where('jenis_asesmen', 'al_banding')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // ✅ Update data dengan tracking per field
        $lha = LhaAsesorBanding::where('id_asesmen', $idAsesmen)->firstOrFail();

        // Check if finalized
        if ($lha->isFinalizedApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'LHS sudah difinalisasi dan tidak dapat diubah'
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
            'message' => 'LHS berhasil disimpan',
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
     * Preview LHS as PDF
     */
    public function preview($idAsesmen)
    {
        $asesmen = Asesmen::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'asesmenLapanganBanding',
        ])->findOrFail($idAsesmen);

        $lha = LhaAsesorBanding::where('id_asesmen', $idAsesmen)->firstOrFail();

        // Check access
        $user = Auth::user();
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al_banding')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $pdf = Pdf::loadView('asesmen.banding.lha-asesor.pdf', compact('asesmen', 'lha'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('LHS-Preview.pdf');
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
            ->where('jenis_asesmen', 'al_banding')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $lha = LhaAsesorBanding::where('id_asesmen', $idAsesmen)->firstOrFail();

        // ✅ Validate completion
        if ($lha->getCompletionPercentage() < 100) {
            return response()->json([
                'success' => false,
                'message' => 'Harap lengkapi semua bagian LHS sebelum finalisasi. Saat ini baru ' . $lha->getCompletionPercentage() . '% terisi.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update LHS status
            $lha->update([
                'status' => 'finalized',
                'finalized_at' => now(),
                'updated_by' => $user->id,
            ]);

            // Generate PDF
            $pdf = Pdf::loadView('asesmen.banding.lha-asesor.pdf', compact('asesmen', 'lha'))
                ->setPaper('a4', 'portrait');

            // Save PDF to storage
            $baseDir = "asesmen/document/{$idAsesmen}/lha_asesor_banding";

            // Ambil versi terakhir
            $latestVersion = (int) AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'lha_asesor_banding')
                ->max('version');

            $newVersion = $latestVersion + 1;

            // Generate safe filename
            $timestamp = now()->format('Ymd_His');
            $random = Str::random(5);

            $filename = "lha_asesor_banding_{$idAsesmen}_v{$newVersion}_{$timestamp}_{$random}.pdf";

            $path = "{$baseDir}/{$filename}";

            Storage::disk('public')->put($path, $pdf->output());

            // Deactivate old LHS documents
            AsesmenDocument::where('id_asesmen', $idAsesmen)
                ->where('type', 'lha_asesor_banding')
                ->update(['is_active' => false]);

            // Create new document record
            $document = AsesmenDocument::create([
                'id_asesmen' => $idAsesmen,
                'type' => 'lha_asesor_banding',
                'title' => 'Laporan Surveilance Penanganan Banding',
                'path' => $path,
                'original_name' => $filename,
                'size' => Storage::disk('public')->size($path),
                'mime' => 'application/pdf',
                'is_active' => true,
                'version' => $newVersion,
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
            ]);

            $asesmen->asesmenLapanganBanding->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completed_by' => $user->id
            ]);

            DB::commit();

            $this->notifikasiUPPSLhaFinalized($asesmen, $document);

            return response()->json([
                'success' => true,
                'message' => 'LHS berhasil difinalisasi dan PDF telah dibuat.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error finalizing LHS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal finalisasi LHS: ' . $e->getMessage()
            ], 500);
        }
    }

    private function notifikasiUPPSLhaFinalized(Asesmen $asesmen, AsesmenDocument $document): void
    {
        try {
            $asesmen->loadMissing([
                'pengajuan.studyProgram.university',
                'pengajuan.studyProgram.users.activeEmails',
                'pengajuan.pengaju.activeEmails',
            ]);

            $pengajuan = $asesmen->pengajuan;
            $namaProdi = $pengajuan->studyProgram->name ?? '-';

            $prodUsers = $pengajuan->studyProgram?->users;
            $emails    = $prodUsers && $prodUsers->isNotEmpty()
                ? $this->recipientResolver->emailsForUsers($prodUsers)
                : $this->recipientResolver->emailsForUser($pengajuan->pengaju);

            if (empty($emails)) {
                Log::warning('Notifikasi LHS Banding finalized tidak dikirim: tidak ada email UPPS.', [
                    'asesmen_id' => $asesmen->id,
                ]);
                return;
            }

            $this->mailDelivery->sendToEmails(
                $emails,
                new ReminderContextMail(
                    recipientName: 'Tim Akreditasi Program Studi <strong>' . $namaProdi . '</strong>',
                    pesanReminder: "Laporan Surveilance Penanganan Banding untuk program studi Anda telah selesai disusun oleh tim asesor dan siap untuk ditinjau.\n\nMohon segera melakukan peninjauan dan memberikan persetujuan atau permintaan revisi melalui sistem.",
                    subject: 'Laporan Surveilance Banding Siap Ditinjau',
                    actionUrl: route('upps.pelaksanaan-banding.show', $pengajuan->id),
                    actionLabel: 'Tinjau Laporan Sekarang',
                    contextInfo: 'Berikut adalah detail proses penyusunan Laporan Surveilance Banding: ',
                    headerTitle: 'Laporan Surveilance Banding Siap Ditinjau',
                    preheader: 'Tim asesor telah menyelesaikan Laporan Surveilance Banding, segera lakukan peninjauan.',
                ),
                [],
                [],
                true
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi LHS Banding finalized ke UPPS', [
                'asesmen_id' => $asesmen->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Download LHS PDF
     */
    public function download($idAsesmen)
    {
        $asesmen = Asesmen::findOrFail($idAsesmen);
        $user = Auth::user();

        // Check access
        $hasAccess = AsesmenUserRole::where('id_asesmen', $idAsesmen)
            ->where('id_user', $user->id)
            ->where('jenis_asesmen', 'al_banding')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Akses ditolak');
        }

        $document = AsesmenDocument::where('id_asesmen', $idAsesmen)
            ->where('type', 'lha_asesor_banding')
            ->where('is_active', true)
            ->latest('uploaded_at')
            ->firstOrFail();

        if (!Storage::disk('public')->exists($document->path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk('public')->download($document->path, $document->original_name);
    }
}
