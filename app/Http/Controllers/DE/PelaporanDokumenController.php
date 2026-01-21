<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\AsesmenDocument;
use App\Models\AsesmenUserRole;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelaporanDokumenController extends Controller
{
    /**
     * Dashboard monitoring pelaporan dokumen validasi
     */
    public function index(Request $request)
    {
        // Build query - hanya pengajuan yang sudah masuk tahap validasi dokumen
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->where('status_penawaran', 'accepted')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            },
            'asesmen.documents' => function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true)
                    ->latest();
            }
        ])
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                PengajuanAkreditasi::STATUS_DRAFT_BORANG_FINAL_DITERIMA,
                PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
            ]);

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_univ', $request->university_id);
            });
        }

        // Filter by status pelaporan
        if ($request->filled('status_pelaporan')) {
            switch ($request->status_pelaporan) {
                case 'belum_upload':
                    // Belum upload laporan
                    $query->where('status', '!=', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
                        ->whereDoesntHave('asesmen.documents', function ($q) {
                            $q->where('type', 'laporan_validasi_borang')
                                ->where('is_active', true);
                        });
                    break;
                case 'sudah_upload':
                    // Sudah upload tapi belum finalisasi
                    $query->where('status', '!=', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
                        ->whereHas('asesmen.documents', function ($q) {
                            $q->where('type', 'laporan_validasi_borang')
                                ->where('is_active', true);
                        });
                    break;
                case 'selesai':
                    // Sudah difinalisasi
                    $query->where('status', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN);
                    break;
            }
        }

        // Search by prodi name or nomor permohonan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get universities for filter
        $universities = University::nonExample()->orderBy('name')->get();

        // Check if AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.pelaporan-dokumen.components.table-content', compact('pengajuans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.pelaporan-dokumen.index', compact(
            'pengajuans',
            'stats',
            'universities'
        ));
    }

    /**
     * Detail pelaporan dokumen untuk satu pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenUserRoles' => function ($q) {
                $q->where('jenis_asesmen', 'dokumen')
                    ->where('status_penawaran', 'accepted')
                    ->whereHas('role_selected', fn($r) => $r->where('name', 'validator'))
                    ->with(['user', 'role_selected']);
            },
            'asesmen.documents' => function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->orderBy('version', 'desc')
                    ->orderBy('created_at', 'desc');
            },
            'statusLog' => function ($q) {
                $q->orderBy('changed_at', 'desc')->with('changedBy');
            }
        ])->findOrFail($id);

        // Get validator assignment
        $validatorAssignment = $pengajuan->asesmen?->asesmenUserRoles
            ->where('jenis_asesmen', 'dokumen')
            ->where('status_penawaran', 'accepted')
            ->first();

        // Get latest laporan validasi
        $laporanValidasi = $pengajuan->asesmen?->documents
            ->where('type', 'laporan_validasi_borang')
            ->where('is_active', true)
            ->first();

        // Check status pelaporan
        $statusPelaporan = $this->getStatusPelaporan($pengajuan, $laporanValidasi);

        return view('de.pelaporan-dokumen.show', compact(
            'pengajuan',
            'validatorAssignment',
            'laporanValidasi',
            'statusPelaporan'
        ));
    }

    /**
     * Calculate statistics for dashboard
     */
    private function calculateStatistics()
    {
        $baseQuery = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_DRAFT_BORANG_FINAL_DITERIMA,
            PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
        ]);

        $total = $baseQuery->count();

        // Sudah difinalisasi
        $selesai = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN)
            ->count();

        // Belum upload laporan (status bukan VALIDASI_BORANG_DILAPORKAN dan tidak ada dokumen)
        $belumUpload = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_DRAFT_BORANG_FINAL_DITERIMA,
        ])
            ->whereDoesntHave('asesmen.documents', function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true);
            })
            ->count();

        // Sudah upload tapi belum finalisasi
        $menungguFinalisasi = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            PengajuanAkreditasi::STATUS_DRAFT_BORANG_FINAL_DITERIMA,
        ])
            ->whereHas('asesmen.documents', function ($q) {
                $q->where('type', 'laporan_validasi_borang')
                    ->where('is_active', true);
            })
            ->count();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'belum_upload' => $belumUpload,
            'menunggu_finalisasi' => $menungguFinalisasi,
        ];
    }

    /**
     * Get status pelaporan untuk satu pengajuan
     */
    private function getStatusPelaporan($pengajuan, $laporanValidasi)
    {
        if ($pengajuan->status === PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN) {
            return [
                'status' => 'selesai',
                'label' => 'Selesai Dilaporkan',
                'class' => 'success',
                'icon' => 'check-circle-fill',
                'description' => 'Pelaporan validasi dokumen telah selesai dan difinalisasi'
            ];
        }

        if ($laporanValidasi) {
            return [
                'status' => 'menunggu_finalisasi',
                'label' => 'Menunggu Finalisasi',
                'class' => 'warning',
                'icon' => 'hourglass-split',
                'description' => 'Laporan validasi sudah diupload, menunggu finalisasi dari validator'
            ];
        }

        return [
            'status' => 'belum_upload',
            'label' => 'Belum Upload Laporan',
            'class' => 'danger',
            'icon' => 'x-circle-fill',
            'description' => 'Validator belum mengupload laporan validasi dokumen'
        ];
    }

    /**
     * Get data for ajax table refresh
     */
    public function getTableAjax(Request $request)
    {
        return $this->index($request);
    }
}
