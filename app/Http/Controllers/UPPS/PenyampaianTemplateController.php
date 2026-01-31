<?php
// app/Http/Controllers/UPPS/PenyampaianTemplateController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenyampaianTemplateController extends Controller
{
    /**
     * Display list of penyampaian template
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'borang_template',
                'template_formulir_pembayaran'
            ])->where('is_latest', true),
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ])->orderBy('changed_at', 'desc'),
        ])->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('pengajuan_status_log as l')
                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                ->whereIn('l.status_to', [
                    PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                    PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
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

        return view('upps.penyampaian-template.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail penyampaian template
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'borang_template',
                'template_formulir_pembayaran'
            ]),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Get pending upload requests
        $pendingRequests = Notification::where('notifiable_type', 'App\Models\User')
            ->where('type', 'template_upload_request')
            ->whereJsonContains('data->id_pengajuan', $id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('upps.penyampaian-template.show', compact('pengajuan', 'pendingRequests'));
    }

    /**
     * Show request upload ulang form
     */
    public function showRequestForm($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'borang_template',
                'template_formulir_pembayaran'
            ])->where('is_latest', true),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');
        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        $templateLed = $pengajuan->dokumen->firstWhere('jenis_dokumen', 'borang_template');
        $formulirPembayaran = $pengajuan->dokumen->firstWhere('jenis_dokumen', 'template_formulir_pembayaran');

        if (!$templateLed && !$formulirPembayaran) {
            return redirect()
                ->route('upps.penyampaian-template.show', $id)
                ->with('error', 'Dokumen belum tersedia.');
        }

        return view('upps.penyampaian-template.request-upload', compact(
            'pengajuan',
            'templateLed',
            'formulirPembayaran'
        ));
    }
    /**
     * Submit request upload ulang
     */
    public function requestUploadUlang(Request $request, $id)
    {
        $validated = $request->validate([
            'jenis_dokumen'   => 'required|array|min:1',
            'jenis_dokumen.*' => 'in:borang_template,template_formulir_pembayaran',
            'alasan_request'  => 'required|string|min:10|max:1000',
        ], [
            'jenis_dokumen.required' => 'Pilih minimal 1 dokumen.',
            'jenis_dokumen.array' => 'Format pilihan dokumen tidak valid.',
            'jenis_dokumen.*.in' => 'Jenis dokumen tidak valid.',
            'alasan_request.required' => 'Alasan permintaan pengiriman ulang harus diisi.',
            'alasan_request.min' => 'Alasan minimal 10 karakter.',
            'alasan_request.max' => 'Alasan maksimal 1000 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('studyProgram', 'deAssigned', 'dokumen')->findOrFail($id);

            // Check access
            $user = Auth::user();
            $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');
            if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
                abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
            }

            // Ambil dokumen yang memang tersedia (is_latest)
            $available = $pengajuan->dokumen()
                ->where('is_latest', true)
                ->whereIn('jenis_dokumen', ['borang_template', 'template_formulir_pembayaran'])
                ->pluck('jenis_dokumen')
                ->toArray();

            // Filter request hanya yang tersedia
            $requested = array_values(array_intersect($validated['jenis_dokumen'], $available));

            if (count($requested) === 0) {
                return redirect()->back()->with('error', 'Dokumen yang dipilih belum tersedia.');
            }

            $labelMap = [
                'borang_template' => 'Template Dokumen Akreditasi',
                'template_formulir_pembayaran' => 'Formulir Pembayaran',
            ];

            $requestedLabels = array_map(fn($k) => $labelMap[$k] ?? $k, $requested);
            $requestedLabelText = implode(', ', $requestedLabels);

            // Create notification for DE (LAMDEPILAR)
            $deUsers = \App\Models\User::role('asesi')->get();

            foreach ($deUsers as $deUser) {
                Notification::create([
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $deUser->id,
                    'type' => 'template_upload_request',
                    'data' => json_encode([
                        'id_pengajuan' => $pengajuan->id,
                        'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                        'jenis_dokumen' => $requested,                 // array
                        'jenis_dokumen_label' => $requestedLabels,     // array label
                        'alasan_request' => $validated['alasan_request'],
                        'program_studi' => $pengajuan->studyProgram->name,
                        'requested_by' => Auth::user()->name,
                        'requested_at' => now()->toISOString(),
                    ]),
                    'read_at' => null,
                ]);
            }

            // Confirmation to requester
            Notification::create([
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => Auth::id(),
                'type' => 'template_upload_request_sent',
                'data' => json_encode([
                    'id_pengajuan' => $pengajuan->id,
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'jenis_dokumen' => $requested,
                    'jenis_dokumen_label' => $requestedLabels,
                    'message' => "Permintaan pengiriman ulang untuk {$requestedLabelText} telah dikirim ke LAMDEPILAR.",
                ]),
                'read_at' => null,
            ]);

            DB::commit();

            return redirect()
                ->route('upps.penyampaian-template.show', $id)
                ->with('success', "Permintaan pengiriman ulang untuk {$requestedLabelText} berhasil dikirim ke LAMDEPILAR.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim permintaan: ' . $e->getMessage());
        }
    }

    /**
     * Download template dokumen
     */
    public function download($id, $jenisDokumen)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        // Validate jenis dokumen
        $allowedJenis = ['borang_template', 'template_formulir_pembayaran'];
        if (!in_array($jenisDokumen, $allowedJenis)) {
            abort(404, 'Jenis dokumen tidak valid.');
        }

        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', $jenisDokumen)
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $dokumen->path_file,
            $dokumen->original_filename
        );
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
     * Calculate statistics based on status log
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Ambil semua status log untuk pengajuan milik prodi ini
        $logs = DB::table('pengajuan_status_log as psl')
            ->join('pengajuan_akreditasi as pa', 'psl.id_pengajuan', '=', 'pa.id')
            ->whereIn('pa.id_program_studi', $studyProgramIds)
            ->whereIn('psl.status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ])
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->get();

        $stats = [
            'total' => 0,
            'menunggu' => 0,
            'diterima' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;

            // Menunggu: surat penerimaan dikirim tapi belum ada template
            if (
                in_array(PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM, $statuses)
            ) {
                $stats['menunggu']++;
            }

            // Template Dikirim
            if (in_array(PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM, $statuses)) {
                $stats['diterima']++;
            }
        }

        return $stats;
    }
}
