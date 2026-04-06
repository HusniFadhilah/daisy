<?php

namespace App\Http\Controllers\Asesmen;

use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use App\Models\HasilAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\HasilAkreditasiService;

class PelaporanController extends Controller
{
    protected $hasilService;

    public function __construct(HasilAkreditasiService $hasilService)
    {
        $this->hasilService = $hasilService;
    }

    /**
     * ============================================
     * CONFIGURATION PER JENIS ASESMEN
     * ============================================
     */
    private function getAsesmenConfig($jenisAsesmen): array
    {
        $configs = [
            'dokumen' => [
                'role' => 'validator',
                'document_type' => 'laporan_validasi_borang',
                'document_title' => 'Laporan Kesiapan LED Program Studi (LKLED)',
                'status_completed' => PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                'tanggal_field' => 'tanggal_pelaporan_validasi_borang',
                'storage_path' => 'laporan-validasi',
                'view_name' => 'asesmen.pelaporan.dokumen-show',
                'relation' => null,
                'finalize_keterangan' => 'Laporan Kesiapan LED Program Studi (LKLED) difinalisasi oleh validator',
            ],
            'ak' => [
                'role' => 'validator',
                'document_type' => 'laporan_validasi_ak',
                'document_title' => 'Laporan Penilaian Kecukupan LED Program Studi (LHK)',
                'status_completed' => PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                'tanggal_field' => 'tanggal_pelaporan_ak',
                'storage_path' => 'laporan-validasi-ak',
                'view_name' => 'asesmen.pelaporan.validasi-ak-show',
                'relation' => 'asesmenKecukupan',
                'finalize_keterangan' => 'Laporan validasi AK difinalisasi oleh',
                'update_method' => 'ak',
            ],
            'al' => [
                'role' => 'validator',
                'document_type' => 'laporan_al',
                'document_title' => 'Laporan Hasil Asesmen Lapangan',
                'status_completed' => PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
                'tanggal_field' => 'tanggal_pelaporan_al',
                'storage_path' => 'laporan-al',
                'view_name' => 'asesmen.pelaporan.al-show',
                'relation' => 'asesmenLapangan',
                'finalize_keterangan' => 'Pelaporan AL difinalisasi oleh',
                'update_method' => 'al',
                'initialize_hasil' => true,
            ],
            'banding' => [
                'role' => 'asesor_banding',
                'document_type' => 'laporan_al_banding',
                'document_title' => 'Laporan Surveilance Penanganan Banding',
                'status_completed' => PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                'tanggal_field' => 'tanggal_pelaporan_al_banding',
                'storage_path' => 'laporan-al-banding',
                'view_name' => 'asesmen.pelaporan.al-banding-show',
                'relation' => 'asesmenLapanganBanding',
                'finalize_keterangan' => 'Pelaporan AL Banding difinalisasi oleh',
                'update_method' => 'banding',
                'initialize_banding' => true,
            ],
        ];

        return $configs[$jenisAsesmen] ?? null;
    }

    /**
     * ============================================
     * GENERIC: UPLOAD LAPORAN
     * ============================================
     */
    private function genericUploadLaporan(Request $request, $idAssignment, $jenisAsesmen)
    {
        $config = $this->getAsesmenConfig($jenisAsesmen);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Jenis asesmen tidak valid.'], 400);
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5120',
            'title' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Get assignment
            $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', $config['role']))
                ->where('jenis_asesmen', $jenisAsesmen)
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            // Upload file
            $file = $request->file('file');
            $pengajuan = $assignment->asesmen?->pengajuan;

            if (!$pengajuan) {
                $path = $file->store("asesmen/{$assignment->asesmen->id}/{$config['storage_path']}", 'public');
            } else {
                $path = $file->store("permohonan-akreditasi/{$pengajuan->id}/{$config['storage_path']}", 'public');
            }

            // Get or create document
            $doc = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', $config['document_type'])
                ->where('is_active', true)
                ->latest('id')
                ->first();

            $payload = [
                'id_asesmen' => $assignment->id_asesmen,
                'type' => $config['document_type'],
                'title' => $request->title ?: $config['document_title'],
                'sort_order' => 1,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'uploaded_by' => $user->id,
                'uploaded_at' => now(),
                'is_active' => true,
            ];

            if ($doc) {
                // Delete old file
                if ($doc->path) Storage::disk('public')->delete($doc->path);
                $doc->update($payload + ['version' => ($doc->version ?? 1) + 1]);
            } else {
                $doc = AsesmenDocument::create($payload + ['version' => 1]);
            }

            // Update assignment status
            if ($jenisAsesmen === 'al') {
                $assignment->update(['status_pekerjaan' => 'in_progress']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $config['document_title'] . ' berhasil diupload.',
                'doc' => [
                    'title' => $doc->title,
                    'original_name' => $doc->original_name,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            Log::error("Upload laporan {$jenisAsesmen} failed", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ============================================
     * GENERIC: FINALIZE PELAPORAN
     * ============================================
     */
    private function genericFinalizePelaporan(Request $request, $idAssignment, $jenisAsesmen)
    {
        $config = $this->getAsesmenConfig($jenisAsesmen);
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Jenis asesmen tidak valid.'], 400);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Get assignment
            $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', $config['role']))
                ->where('jenis_asesmen', $jenisAsesmen)
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            $pengajuan = $assignment->asesmen?->pengajuan;

            // Check if document exists
            $docExists = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', $config['document_type'])
                ->where('is_active', true)
                ->exists();

            if (!$docExists) {
                return response()->json([
                    'success' => false,
                    'message' => "Upload dulu file {$config['document_title']} (PDF) sebelum finalisasi.",
                ], 422);
            }

            // Check if already finalized
            if ($pengajuan && $pengajuan->status === $config['status_completed']) {
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Pelaporan telah difinalisasi sebelumnya.']);
            }

            // Update pengajuan status
            if ($pengajuan) {
                $statusFrom = $pengajuan->status;

                // Update status based on jenis asesmen
                if (isset($config['update_method'])) {
                    $pengajuan->checkUpdateStatusAKAL($config['update_method'], 'status_asesor_dilaporkan');
                } else {
                    $pengajuan->update([
                        'status' => $config['status_completed'],
                        $config['tanggal_field'] => now(),
                    ]);
                }

                // Log status change
                $pengajuan->statusLog()->create([
                    'status_from' => $statusFrom,
                    'status_to' => $config['status_completed'],
                    'changed_by' => $user->id,
                    'keterangan' => $config['finalize_keterangan'] . ' ' . $user->name,
                    'changed_at' => now(),
                ]);
            }

            // Update related asesmen
            if (isset($config['relation']) && $config['relation']) {
                $relatedAsesmen = $assignment->asesmen->{$config['relation']};
                if ($relatedAsesmen) {
                    $relatedAsesmen->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'completed_by' => $user->id,
                    ]);
                }
            }

            // Update assignment status for AL
            if ($jenisAsesmen === 'al') {
                $assignment->asesmen->update(['status' => 'completed']);
                $assignment->update(['status_pekerjaan' => 'submitted', 'submitted_at' => now()]);

                // Initialize hasil akreditasi
                if (isset($config['initialize_hasil']) && $config['initialize_hasil'] && $pengajuan) {
                    HasilAkreditasi::initializeHasil($this->hasilService, $pengajuan, $user->id);
                }
            }

            if ($jenisAsesmen === 'banding') {
                $assignment->update(['status_pekerjaan' => 'submitted', 'submitted_at' => now()]);

                // Initialize hasil akreditasi
                if (isset($config['initialize_banding']) && $config['initialize_banding'] && $pengajuan) {
                    HasilAkreditasi::initializeBanding($this->hasilService, $pengajuan, $user->id);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pelaporan {$jenisAsesmen} berhasil difinalisasi.",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Finalize pelaporan {$jenisAsesmen} failed", ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal finalisasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ============================================
     * GENERIC: SHOW PELAPORAN
     * ============================================
     */
    private function genericShowPelaporan($idAssignment, $jenisAsesmen)
    {
        $config = $this->getAsesmenConfig($jenisAsesmen);
        if (!$config) {
            abort(404, 'Jenis asesmen tidak valid.');
        }

        $user = Auth::user();

        $eagerLoads = [
            'asesmen.pengajuan.studyProgram.university',
            'asesmen.pengajuan.studyProgram.degreeLevel',
            'asesmen.pengajuan.pengaju',
            'asesmen.pengajuan.statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
            'asesmen.documents' => fn($q) => $q->where('type', $config['document_type'])->where('is_active', true),
            'role_selected',
        ];

        // Add relation if exists
        if (isset($config['relation']) && $config['relation']) {
            $eagerLoads[] = 'asesmen.' . $config['relation'];
        }

        $assignment = AsesmenUserRole::with($eagerLoads)
            ->where('id_user', $user->id)
            ->whereHas('role', fn($q) => $q->where('name', $config['role']))
            ->where('jenis_asesmen', $jenisAsesmen)
            ->findOrFail($idAssignment);

        if ($assignment->status_penawaran !== 'accepted') {
            abort(403, 'Anda tidak memiliki akses ke pelaporan ini.');
        }

        $pengajuan = $assignment->asesmen->pengajuan;

        return view($config['view_name'], compact('assignment', 'pengajuan'));
    }

    /**
     * ============================================
     * GENERIC: DOWNLOAD LAPORAN
     * ============================================
     */
    private function genericDownloadLaporan($idAssignment, $jenisAsesmen)
    {
        $config = $this->getAsesmenConfig($jenisAsesmen);
        if (!$config) {
            abort(404, 'Jenis asesmen tidak valid.');
        }

        $user = Auth::user();

        $assignment = AsesmenUserRole::with([
            'asesmen.documents' => fn($q) => $q->where('type', $config['document_type'])->where('is_active', true)
        ])
            ->where('id_user', $user->id)
            ->whereHas('role', fn($q) => $q->where('name', $config['role']))
            ->where('jenis_asesmen', $jenisAsesmen)
            ->findOrFail($idAssignment);

        if ($assignment->status_penawaran !== 'accepted') {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        $document = $assignment->asesmen->documents()
            ->where('type', $config['document_type'])
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (!$document) {
            abort(404, 'File laporan tidak ditemukan.');
        }

        if (!Storage::disk('public')->exists($document->path)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($document->path, $document->original_name);
    }

    /**
     * ============================================
     * PUBLIC METHODS - UPLOAD LAPORAN
     * ============================================
     */
    public function uploadLaporanValidasi(Request $request, $idAssignment)
    {
        return $this->genericUploadLaporan($request, $idAssignment, 'dokumen');
    }

    public function uploadLaporanValidasiAK(Request $request, $idAssignment)
    {
        return $this->genericUploadLaporan($request, $idAssignment, 'ak');
    }

    public function uploadLaporanAL(Request $request, $idAssignment)
    {
        return $this->genericUploadLaporan($request, $idAssignment, 'al');
    }

    public function uploadLaporanBanding(Request $request, $idAssignment)
    {
        return $this->genericUploadLaporan($request, $idAssignment, 'banding');
    }

    /**
     * ============================================
     * PUBLIC METHODS - FINALIZE PELAPORAN
     * ============================================
     */
    public function finalizePelaporanValidasi(Request $request, $idAssignment)
    {
        return $this->genericFinalizePelaporan($request, $idAssignment, 'dokumen');
    }

    public function finalizeValidasiAK(Request $request, $idAssignment)
    {
        return $this->genericFinalizePelaporan($request, $idAssignment, 'ak');
    }

    public function finalizePelaporanAL(Request $request, $idAssignment)
    {
        return $this->genericFinalizePelaporan($request, $idAssignment, 'al');
    }

    public function finalizePelaporanBanding(Request $request, $idAssignment)
    {
        return $this->genericFinalizePelaporan($request, $idAssignment, 'banding');
    }

    /**
     * ============================================
     * PUBLIC METHODS - SHOW PELAPORAN
     * ============================================
     */
    public function showDokumen($idAssignment)
    {
        return $this->genericShowPelaporan($idAssignment, 'dokumen');
    }

    public function showValidasiAK($idAssignment)
    {
        return $this->genericShowPelaporan($idAssignment, 'ak');
    }

    public function showAK($idAssignment)
    {
        return $this->genericShowPelaporan($idAssignment, 'ak');
    }

    public function showAL($idAssignment)
    {
        return $this->genericShowPelaporan($idAssignment, 'al');
    }

    public function showBanding($idAssignment)
    {
        return $this->genericShowPelaporan($idAssignment, 'banding');
    }

    /**
     * ============================================
     * PUBLIC METHODS - DOWNLOAD LAPORAN
     * ============================================
     */
    public function downloadLaporanDokumen($idAssignment)
    {
        return $this->genericDownloadLaporan($idAssignment, 'dokumen');
    }

    public function downloadLaporanValidasiAK($idAssignment)
    {
        return $this->genericDownloadLaporan($idAssignment, 'ak');
    }

    public function downloadLaporanAL($idAssignment)
    {
        return $this->genericDownloadLaporan($idAssignment, 'al');
    }

    public function downloadLaporanBanding($idAssignment)
    {
        return $this->genericDownloadLaporan($idAssignment, 'banding');
    }

    /**
     * ============================================
     * MAIN INDEX - Overview All Types
     * ============================================
     */
    public function index()
    {
        $user = Auth::user();

        $assignments = AsesmenUserRole::with([
            'asesmen.pengajuan',
            'asesmen.studyProgram.university',
            'role_selected',
        ])
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->whereIn('jenis_asesmen', ['dokumen', 'ak', 'al', 'banding'])
            ->latest('created_at')
            ->get();

        $byType = [
            'dokumen' => $assignments->where('jenis_asesmen', 'dokumen'),
            'ak' => $assignments->where('jenis_asesmen', 'ak'),
            'al' => $assignments->where('jenis_asesmen', 'al'),
            'banding' => $assignments->where('jenis_asesmen', 'banding'),
        ];

        $stats = [
            'dokumen' => [
                'total' => $byType['dokumen']->count(),
                'pending' => $byType['dokumen']->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('dokumen'))->count(),
                'completed' => $byType['dokumen']->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_validasi_borang !== null)->count(),
            ],
            'ak' => [
                'total' => $byType['ak']->count(),
                'pending' => $byType['ak']->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('ak'))->count(),
                'completed' => $byType['ak']->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_ak !== null)->count(),
            ],
            'al' => [
                'total' => $byType['al']->count(),
                'pending' => $byType['al']->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('al'))->count(),
                'completed' => $byType['al']->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_al !== null)->count(),
            ],
            'banding' => [
                'total' => $byType['banding']->count(),
                'pending' => $byType['banding']->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('banding'))->count(),
                'completed' => $byType['banding']->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_banding !== null)->count(),
            ],
        ];

        return view('asesmen.pelaporan.index', compact('stats', 'byType'));
    }

    /**
     * ============================================
     * INDEX METHODS
     * ============================================
     */
    public function indexDokumen()
    {
        $user = Auth::user();

        $assignments = AsesmenUserRole::with([
            'asesmen.pengajuan',
            'asesmen.studyProgram.university',
            'role_selected',
        ])
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->where('jenis_asesmen', 'dokumen')
            ->latest('created_at')
            ->get();

        $stats = [
            'total' => $assignments->count(),
            'pending' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('dokumen'))->count(),
            'completed' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_validasi_borang !== null)->count(),
            'in_progress' => $assignments->filter(fn($a) => $a->status_pekerjaan === 'in_progress')->count(),
        ];

        return view('asesmen.pelaporan.dokumen', compact('assignments', 'stats'));
    }

    public function indexValidasiAK()
    {
        $user = Auth::user();

        $assignments = AsesmenUserRole::with([
            'asesmen.pengajuan',
            'asesmen.studyProgram.university',
            'asesmen.asesmenKecukupan',
            'role_selected',
        ])
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->where('jenis_asesmen', 'ak')
            ->latest('created_at')
            ->get();

        $stats = [
            'total' => $assignments->count(),
            'pending' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('ak'))->count(),
            'completed' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_ak !== null)->count(),
            'in_progress' => $assignments->filter(fn($a) => $a->status_pekerjaan === 'in_progress')->count(),
        ];

        return view('asesmen.pelaporan.validasi-ak', compact('assignments', 'stats'));
    }

    public function indexAK()
    {
        return redirect()->route('pelaporan.indexValidasiAK');
    }

    public function indexAL()
    {
        $user = Auth::user();

        $assignments = AsesmenUserRole::with([
            'asesmen.pengajuan',
            'asesmen.studyProgram.university',
            'asesmen.asesmenLapangan',
            'role_selected',
        ])
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->where('jenis_asesmen', 'al')
            ->latest('created_at')
            ->get();

        $stats = [
            'total' => $assignments->count(),
            'pending' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('al'))->count(),
            'completed' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_al !== null)->count(),
            'in_progress' => $assignments->filter(fn($a) => $a->status_pekerjaan === 'in_progress')->count(),
        ];

        return view('asesmen.pelaporan.al', compact('assignments', 'stats'));
    }

    public function indexBanding()
    {
        $user = Auth::user();

        $assignments = AsesmenUserRole::with([
            'asesmen.pengajuan',
            'asesmen.studyProgram.university',
            'asesmen.asesmenBanding',
            'role_selected',
        ])
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'asesor_banding'))
            ->where('jenis_asesmen', 'banding')
            ->latest('created_at')
            ->get();

        $stats = [
            'total' => $assignments->count(),
            'pending' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->canBeReported('banding'))->count(),
            'completed' => $assignments->filter(fn($a) => $a->asesmen->pengajuan?->tanggal_pelaporan_banding !== null)->count(),
            'in_progress' => $assignments->filter(fn($a) => $a->status_pekerjaan === 'in_progress')->count(),
        ];

        return view('asesmen.pelaporan.banding', compact('assignments', 'stats'));
    }

    // Commented methods remain the same (showAK, downloadLaporanAK, etc.)
}
