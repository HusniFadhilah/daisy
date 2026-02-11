<?php
// app/Http/Controllers/UPPS/PelaksanaanALController.php

namespace App\Http\Controllers\UPPS;

use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\HasilAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\HasilAkreditasiService;

class PelaksanaanALController extends Controller
{
    protected $hasilService;

    public function __construct(HasilAkreditasiService $hasilService)
    {
        $this->hasilService = $hasilService;
    }

    /**
     * Display list of pelaksanaan AL
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesorAL',
            'asesmen.beritaAcaraAL' => function ($q) {
                $q->where('type', 'berita_acara_al')
                    ->where('is_active', true)
                    ->latest();
            },
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AL_SELESAI,
            ])->orderBy('changed_at', 'desc'),
        ])->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('pengajuan_status_log as l')
                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                ->whereIn('l.status_to', [
                    PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                    PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                    PengajuanAkreditasi::STATUS_AL_SELESAI,
                ]);
        });

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(20)
            ->appends($request->query());

        // Statistics
        $stats = $this->calculateStatistics($studyProgramIds);

        // Get tahun list
        $tahunList = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.pelaksanaan-al.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail pelaksanaan AL
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'asesmen.asesorAL',
            'asesmen.beritaAcaraAL' => function ($q) {
                $q->where('type', 'berita_acara_al')
                    ->where('is_active', true)
                    ->with('uploader')
                    ->latest();
            },
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'draft_borang',
                'borang_final',
                'surat_tugas_asesor_al'
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.pelaksanaan-al.show', compact('pengajuan'));
    }

    /**
     * Show approval form for berita acara
     */
    public function showApprovalForm($id, $docId)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram',
            'asesmen.beritaAcaraAL'
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }
        // Get berita acara document
        $beritaAcara = AsesmenDocument::where('id', $docId)
            ->where('id_asesmen', $pengajuan->asesmen->id)
            ->where('type', 'berita_acara_al')
            ->with('uploader')
            ->firstOrFail();

        // Check if already approved/rejected
        if (in_array($beritaAcara->status_persetujuan_prodi, ['approved', 'rejected'])) {
            return redirect()
                ->route('upps.pelaksanaan-al.show', $pengajuan->id)
                ->with('error', 'Berita acara ini sudah ditindaklanjuti.');
        }

        return view('upps.pelaksanaan-al.approve-berita-acara', compact('pengajuan', 'beritaAcara'));
    }

    /**
     * Approve, request revision, or reject berita acara
     */
    public function approveBeritaAcara(Request $request, $id, $docId)
    {
        $request->validate([
            'action' => 'required|in:approve,revision,reject',
            'catatan_prodi' => 'nullable|string|max:2000',
        ], [
            'action.required' => 'Silakan pilih tindakan yang akan diambil',
            'action.in' => 'Tindakan tidak valid',
        ]);

        $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Get berita acara document
        $beritaAcara = AsesmenDocument::where('id', $docId)
            ->where('id_asesmen', $pengajuan->asesmen->id)
            ->where('type', 'berita_acara_al')
            ->firstOrFail();

        // Check if already approved/rejected (final status)
        if (in_array($beritaAcara->status_persetujuan_prodi, ['approved', 'rejected'])) {
            return back()->with('error', 'Berita acara ini sudah ditindaklanjuti.');
        }

        DB::beginTransaction();
        try {
            // Map action to status
            $statusMap = [
                'approve' => 'approved',
                'revision' => 'revision_required',
                'reject' => 'rejected',
            ];

            $newStatus = $statusMap[$request->action];

            // Update berita acara document
            $beritaAcara->update([
                'status_persetujuan_prodi' => $newStatus,
                'approved_by_prodi' => auth()->id(),
                'approved_at_prodi' => now(),
                'catatan_prodi' => $request->catatan_prodi,
            ]);

            // Create appropriate log message
            $logMessages = [
                'approved' => 'Berita Acara Asesmen Lapangan disetujui oleh Program Studi',
                'revision_required' => 'Berita Acara Asesmen Lapangan memerlukan revisi: ' . $request->catatan_prodi,
                'rejected' => 'Berita Acara Asesmen Lapangan ditolak oleh Program Studi: ' . $request->catatan_prodi,
            ];

            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => $logMessages[$newStatus],
            ]);

            DB::commit();

            // Success messages
            $messages = [
                'approved' => 'Berita acara berhasil disetujui.',
                'revision_required' => 'Permintaan revisi berita acara berhasil dikirim.',
                'rejected' => 'Berita acara ditolak.',
            ];

            return redirect()
                ->route('upps.pelaksanaan-al.show', $pengajuan->id)
                ->with('success', $messages[$newStatus]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving berita acara: " . $e->getMessage(), [
                'berita_acara_id' => $docId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    /**
     * Process approval from show page (simplified)
     */
    public function processApproval(Request $request, $id, $docId)
    {
        $request->validate([
            'action' => 'required|in:approve,revision',
            'catatan_prodi' => 'nullable|string|max:2000',
        ], [
            'action.required' => 'Silakan pilih tindakan yang akan diambil',
            'action.in' => 'Tindakan tidak valid',
        ]);

        $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

        // Check access
        $user = Auth::user();
        $authId = $user->id;
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Get berita acara document
        $beritaAcara = AsesmenDocument::where('id', $docId)
            ->where('id_asesmen', $pengajuan->asesmen->id)
            ->where('type', 'berita_acara_al')
            ->firstOrFail();

        // Check if already in final status
        if ($beritaAcara->isFinalStatus()) {
            return back()->with('error', 'Berita acara ini sudah dalam status final.');
        }

        DB::beginTransaction();
        try {
            // Map action to status
            $statusMap = [
                'approve' => 'approved',
                'revision' => 'revision_required',
            ];

            $newStatus = $statusMap[$request->action];

            // Update berita acara document
            $beritaAcara->update([
                'status_persetujuan_prodi' => $newStatus,
                'approved_by_prodi' => $authId,
                'approved_at_prodi' => now(),
                'catatan_prodi' => $request->catatan_prodi,
            ]);

            // Create log message
            $logMessages = [
                'approved' => 'Berita Acara Asesmen Lapangan "' . $beritaAcara->title . '" disetujui oleh Program Studi',
                'revision_required' => 'Berita Acara Asesmen Lapangan "' . $beritaAcara->title . '" memerlukan revisi',
            ];

            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => $authId,
                'changed_at' => now(),
                'keterangan' => $logMessages[$newStatus],
            ]);

            if ($request->action === 'approve') {
                $this->approveLHA($pengajuan, $authId, $logMessages, $newStatus);
            }

            DB::commit();

            // Success messages
            $messages = [
                'approved' => 'Berita acara berhasil disetujui.',
                'revision_required' => 'Permintaan revisi berita acara berhasil dikirim ke asesor.',
            ];

            return redirect()
                ->route('upps.pelaksanaan-al.show', $pengajuan->id)
                ->with('success', $messages[$newStatus]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing berita acara approval: " . $e->getMessage(), [
                'berita_acara_id' => $docId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    /**
     * Process approval for LHA (Laporan Hasil Asesmen)
     */
    public function processLHAApproval(Request $request, $id, $docId)
    {
        $request->validate([
            'action' => 'required|in:approve,revision',
            'catatan_prodi' => 'nullable|string|max:2000',
        ], [
            'action.required' => 'Silakan pilih tindakan yang akan diambil',
            'action.in' => 'Tindakan tidak valid',
        ]);

        $pengajuan = PengajuanAkreditasi::with('asesmen')->findOrFail($id);

        // Check access
        $user = Auth::user();
        $authId = $user->id;
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Get LHA document
        $lha = AsesmenDocument::where('id', $docId)
            ->where('id_asesmen', $pengajuan->asesmen->id)
            ->where('type', 'lha_asesor')
            ->firstOrFail();

        // Check if already in final status
        if ($lha->isFinalStatus()) {
            return back()->with('error', 'Laporan hasil asesmen ini sudah dalam status final.');
        }

        DB::beginTransaction();
        try {
            // Map action to status
            $statusMap = [
                'approve' => 'approved',
                'revision' => 'revision_required',
            ];

            $newStatus = $statusMap[$request->action];

            // Update LHA document
            $lha->update([
                'status_persetujuan_prodi' => $newStatus,
                'approved_by_prodi' => $authId,
                'approved_at_prodi' => now(),
                'catatan_prodi' => $request->catatan_prodi,
            ]);

            // Create log message
            $logMessages = [
                'approved' => 'Laporan Hasil Asesmen Lapangan "' . $lha->title . '" disetujui oleh Program Studi',
                'revision_required' => 'Laporan Hasil Asesmen Lapangan "' . $lha->title . '" memerlukan revisi',
            ];

            $this->approveLHA($pengajuan, $authId, $logMessages, $newStatus);

            DB::commit();

            // Success messages
            $messages = [
                'approved' => 'Laporan hasil asesmen berhasil disetujui.',
                'revision_required' => 'Permintaan revisi laporan berhasil dikirim ke asesor.',
            ];

            return redirect()
                ->route('upps.pelaksanaan-al.show', $pengajuan->id)
                ->with('success', $messages[$newStatus]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing LHA approval: " . $e->getMessage(), [
                'lha_id' => $docId,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage());
        }
    }

    private function approveLHA($pengajuan, $authId, $logMessages, $newStatus)
    {
        $beritaAcaraAL = $pengajuan->asesmen->beritaAcaraAL;
        if ($beritaAcaraAL) {
            $beritaAcaraAL->first()->update([
                'status_persetujuan_prodi' => 'approved',
                'approved_by_prodi' => $authId,
                'approved_at_prodi' => now(),
            ]);
        }

        if ($pengajuan)
            $pengajuan->checkUpdateStatusAKAL('al', 'status_asesor_selesai');

        $pengajuan->statusLog()->create([
            'status_from' => PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            'status_to' => $pengajuan->status,
            'changed_by' => $authId,
            'changed_at' => now(),
            'keterangan' => $logMessages[$newStatus],
        ]);   //
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        $query->when(
            $request->filled('tahun'),
            fn($q) => $q->where('tahun_akreditasi', $request->tahun)
        );

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas(
                        'studyProgram',
                        fn($ssq) =>
                        $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        // Filter by status
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Total penugasan asesor AL
        $totalPenugasan = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED,
                PengajuanAkreditasi::STATUS_AL_IN_PROGRESS,
                PengajuanAkreditasi::STATUS_AL_SELESAI,
            ])
            ->count();

        // Berita acara AL (yang sudah diupload)
        $beritaAcaraCount = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereHas('asesmen.beritaAcaraAL', function ($q) {
                $q->where('type', 'berita_acara_al')
                    ->where('is_active', true);
            })
            ->count();

        // Pending approval (termasuk revision_required)
        $pendingApproval = AsesmenDocument::whereHas('asesmen.pengajuan', function ($q) use ($studyProgramIds) {
            $q->whereIn('id_program_studi', $studyProgramIds);
        })
            ->where('type', 'berita_acara_al')
            ->where('is_active', true)
            ->whereIn('status_persetujuan_prodi', ['pending', 'revision_required'])
            ->count();

        return [
            'total' => $totalPenugasan,
            'berita_acara' => $beritaAcaraCount,
            'pending_approval' => $pendingApproval,
        ];
    }
}
