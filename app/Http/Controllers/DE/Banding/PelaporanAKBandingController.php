<?php

namespace App\Http\Controllers\DE\Banding;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DE\Concerns\HasReminderPelaporan;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelaporanAKBandingController extends Controller
{
    use HasReminderPelaporan;

    private const JENIS_ASESMEN  = 'ak';
    private const DOCUMENT_TYPE  = 'laporan_ak_banding';
    private const REQUIRED_STATUSES = [
        PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
        PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
    ];

    /**
     * Dashboard monitoring pelaporan AK
     */
    public function index(Request $request)
    {
        $akReportStatuses = [
            PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
            PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
        ];

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupanBanding',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'ak_banding')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            },
            'asesmen.asesmenDocuments' => function ($q) {
                $q->where('type', 'laporan_ak_banding')
                    ->where('is_active', true)
                    ->latest();
            },
            // kalau mau ambil log terkait pelaporan di table
            'statusLog' => function ($q) use ($akReportStatuses) {
                $q->whereIn('status_to', $akReportStatuses)
                    ->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ basis list: pernah mencapai AK_SELESAI / AK_DILAPORKAN (via log)
            ->whereExists(function ($q) use ($akReportStatuses) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $akReportStatuses);
            })
            ->whereHas('asesmen.asesmenKecukupanBanding');

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // ✅ Filter by status pelaporan (log-based)
        if ($request->filled('status_pelaporan')) {
            switch ($request->status_pelaporan) {
                case 'belum_lapor':
                    // pernah AK_SELESAI, belum pernah AK_DILAPORKAN, dan belum ada dokumen aktif
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as ls')
                            ->whereColumn('ls.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('ls.status_to', PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI);
                    })->whereNotExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as ld')
                            ->whereColumn('ld.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('ld.status_to', PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN);
                    })->whereDoesntHave('asesmen.asesmenDocuments', function ($q) {
                        $q->where('type', 'laporan_ak_banding')->where('is_active', true);
                    });
                    break;

                case 'sudah_lapor':
                    // pernah AK_DILAPORKAN (via log) dan ada dokumen aktif
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as ld')
                            ->whereColumn('ld.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('ld.status_to', PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN);
                    })->whereHas('asesmen.asesmenDocuments', function ($q) {
                        $q->where('type', 'laporan_ak_banding')->where('is_active', true);
                    });
                    break;
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        $stats = $this->calculateStatistics();
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.banding.pelaporan-ak-banding.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        $pendingReminderAssignments = $this->getPendingReminderAssignments(
            jenisAsesmen: self::JENIS_ASESMEN,
            documentType: self::DOCUMENT_TYPE,
            requiredStatuses: self::REQUIRED_STATUSES,
        );
        $countPendingReminderAssignments = $pendingReminderAssignments->count();

        return view('de.banding.pelaporan-ak-banding.index', compact('pengajuans', 'stats', 'universities', 'pendingReminderAssignments', 'countPendingReminderAssignments',));
    }

    public function kirimReminder(Request $request)
    {
        return $this->processKirimReminder(
            request: $request,
            jenisAsesmen: self::JENIS_ASESMEN,
            documentType: self::DOCUMENT_TYPE,
            mailSubject: 'Pengingat Pelaporan Validasi AK Banding',
            actionRouteName: 'pelaporan.ak-banding.show', // sesuaikan nama route validator
            requiredStatuses: self::REQUIRED_STATUSES,
            keteranganLog: 'Pengingat pelaporan AK Banding dikirim ke validator',
        );
    }

    /**
     * Detail pelaporan AK
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupanBanding',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'ak_banding')
                    ->with(['user', 'role_selected']);
            },
            'asesmen.asesmenDocuments' => function ($q) {
                $q->where('type', 'laporan_ak_banding')
                    ->where('is_active', true)
                    ->with('uploadedBy')
                    ->latest();
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get validation summary
        $totalElements = DB::table('elemen_standar')->count();

        $validationSummary = DB::table('penilaian_elemen_ak_banding')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->selectRaw('
                COUNT(DISTINCT id_elemen) as total_dinilai,
                COUNT(DISTINCT CASE WHEN validated_at IS NOT NULL THEN id_elemen END) as validated_count,
                AVG(CASE WHEN skor IS NOT NULL THEN skor ELSE NULL END) as avg_skor
            ')
            ->first();

        // Get laporan AK documents
        $laporanDocuments = $pengajuan->asesmen?->asesmenDocuments()->where('type', 'laporan_validasi_ak_banding')->get() ?? collect();
        // Check pelaporan status
        $statusPelaporan = [
            'has_laporan' => $laporanDocuments->count() > 0,
            'is_reported' => $pengajuan->status === PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
            'reported_at' => $pengajuan->tanggal_pelaporan_ak_banding,
        ];

        return view('de.banding.pelaporan-ak-banding.show', compact(
            'pengajuan',
            'totalElements',
            'validationSummary',
            'laporanDocuments',
            'statusPelaporan'
        ));
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics()
    {
        $akReportStatuses = [
            PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
            PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
        ];

        $base = PengajuanAkreditasi::query()
            ->whereHas('asesmen.asesmenKecukupanBanding')
            ->whereHas('statusLog', function ($q) use ($akReportStatuses) {
                $q->whereIn('status_to', $akReportStatuses);
            });

        $total = (clone $base)->count();

        $belumLapor = (clone $base)
            ->whereHas('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI))
            ->whereDoesntHave('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN))
            ->whereDoesntHave('asesmen.asesmenDocuments', function ($q) {
                $q->where('type', 'laporan_ak_banding')->where('is_active', true);
            })
            ->count();

        $sudahLapor = (clone $base)
            ->whereHas('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN))
            ->count();

        return [
            'total' => $total,
            'belum_lapor' => $belumLapor,
            'sudah_lapor' => $sudahLapor,
        ];
    }

    /**
     * Get pelaporan timeline (AJAX)
     */
    public function getTimeline($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'statusLog' => function ($q) {
                    $q->whereIn('status_to', [
                        PengajuanAkreditasi::STATUS_AK_BANDING_SELESAI,
                        PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
                    ])
                        ->orderBy('changed_at', 'desc')
                        ->with('changedBy');
                }
            ])->findOrFail($id);

            $timeline = $pengajuan->statusLog->map(function ($log) {
                return [
                    'status_from' => $log->status_from,
                    'status_to' => $log->status_to,
                    'changed_by' => $log->changedBy->name ?? 'System',
                    'changed_at' => $log->changed_at->locale('id')->translatedFormat('d M Y H:i'),
                    'keterangan' => $log->keterangan,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $timeline
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview laporan document (AJAX)
     */
    public function previewDocument($id, $documentId)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            $document = $pengajuan->asesmen
                ->asesmenDocuments()
                ->where('id', $documentId)
                ->where('type', 'laporan_ak_banding')
                ->where('is_active', true)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'path' => $document->path,
                    'url' => asset('storage/' . $document->path),
                    'size' => $document->size,
                    'mime' => $document->mime,
                    'uploaded_by' => $document->uploadedBy->name ?? 'Unknown',
                    'uploaded_at' => $document->uploaded_at?->locale('id')->translatedFormat('d M Y H:i'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
