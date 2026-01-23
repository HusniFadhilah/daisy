<?php

namespace App\Http\Controllers\DE;

use Illuminate\Http\Request;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PelaporanALController extends Controller
{
    /**
     * Dashboard monitoring pelaporan AL
     */
    public function index(Request $request)
    {
        $alReportStatuses = [
            PengajuanAkreditasi::STATUS_AL_SELESAI,
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
        ];

        // Build query - pengajuan yang sudah selesai validasi AL
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            },
            'asesmen.asesmenDocuments' => function ($q) {
                $q->where('type', 'laporan_al')
                    ->where('is_active', true)
                    ->latest();
            },
            // optional: kalau mau tampilkan log di table
            'statusLog' => function ($q) use ($alReportStatuses) {
                $q->whereIn('status_to', $alReportStatuses)
                    ->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ basis list: pernah mencapai AL_SELESAI / AL_DILAPORKAN (via log)
            ->whereExists(function ($q) use ($alReportStatuses) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $alReportStatuses);
            })
            ->whereHas('asesmen.asesmenKecukupan');

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status pelaporan
        if ($request->filled('status_pelaporan')) {
            switch ($request->status_pelaporan) {
                case 'belum_lapor':
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_SELESAI);
                    })
                        ->whereNotExists(function ($q) {
                            $q->select(DB::raw(1))
                                ->from('pengajuan_status_log as l')
                                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                                ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN);
                        })
                        ->whereDoesntHave('asesmen.asesmenDocuments', function ($q) {
                            $q->where('type', 'laporan_al')->where('is_active', true);
                        });
                    break;

                case 'sudah_lapor':
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('pengajuan_status_log as l')
                            ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                            ->where('l.status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN);
                    })
                        ->whereHas('asesmen.asesmenDocuments', function ($q) {
                            $q->where('type', 'laporan_al')->where('is_active', true);
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

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get universities for filter
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();

        // Check if AJAX
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.pelaporan-al.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.pelaporan-al.index', compact(
            'pengajuans',
            'stats',
            'universities'
        ));
    }

    /**
     * Detail pelaporan AL
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenKecukupan',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'al')
                    ->with(['user', 'role_selected']);
            },
            'asesmen.asesmenDocuments' => function ($q) {
                $q->where('type', 'laporan_al')
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

        $validationSummary = DB::table('penilaian_elemen_al')
            ->where('id_asesmen', $pengajuan->asesmen->id ?? 0)
            ->selectRaw('
                COUNT(DISTINCT id_elemen) as total_dinilai,
                AVG(CASE WHEN skor IS NOT NULL THEN skor ELSE NULL END) as avg_skor
            ')
            ->first();

        $laporanDocuments = $pengajuan->asesmen?->asesmenDocuments()
            ->where('type', 'laporan_al')
            ->orderBy('created_at', 'desc')
            ->get() ?? collect();

        $isReported = $pengajuan->statusLog()
            ->where('status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN)
            ->exists();

        $statusPelaporan = [
            'has_laporan' => $laporanDocuments->count() > 0,
            'is_reported' => $isReported,
            'reported_at' => $pengajuan->tanggal_pelaporan_al, // boleh tetap pakai field ini
        ];

        return view('de.pelaporan-al.show', compact(
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
        $scope = [
            PengajuanAkreditasi::STATUS_AL_SELESAI,
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
        ];

        $base = PengajuanAkreditasi::query()
            ->whereHas('asesmen.asesmenKecukupan')
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $scope));

        $total = (clone $base)->count();

        $belumLapor = (clone $base)
            ->whereHas('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AL_SELESAI))
            ->whereDoesntHave('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN))
            ->whereDoesntHave('asesmen.asesmenDocuments', function ($q) {
                $q->where('type', 'laporan_al')->where('is_active', true);
            })
            ->count();

        $sudahLapor = (clone $base)
            ->whereHas('statusLog', fn($q) => $q->where('status_to', PengajuanAkreditasi::STATUS_AL_DILAPORKAN))
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
                        PengajuanAkreditasi::STATUS_AL_SELESAI,
                        PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
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
                    'changed_at' => $log->changed_at->format('d M Y H:i'),
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
                ->where('type', 'laporan_al')
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
                    'uploaded_at' => $document->uploaded_at?->format('d M Y H:i'),
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
