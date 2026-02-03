<?php

namespace App\Http\Controllers\Asesmen;

use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\AsesmenLapangan;
use App\Models\AsesmenUserRole;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PelaporanController extends Controller
{
    /**
     * ============================================
     * MAIN INDEX - Overview All Types
     * ============================================
     */
    public function index()
    {
        $user = Auth::user();

        // Get all validator assignments
        $assignments = AsesmenUserRole::with([
            'asesmen.pengajuan',
            'asesmen.studyProgram.university',
            'role_selected',
        ])
            ->where('id_user', $user->id)
            ->where('status_penawaran', 'accepted')
            ->whereHas('role', fn($q) => $q->where('name', 'validator'))
            ->whereIn('jenis_asesmen', ['dokumen', 'ak', 'al'])
            ->latest('created_at')
            ->get();

        // Group by type
        $byType = [
            'dokumen' => $assignments->where('jenis_asesmen', 'dokumen'),
            'ak' => $assignments->where('jenis_asesmen', 'ak'),
            'al' => $assignments->where('jenis_asesmen', 'al'),
        ];

        // Stats per type
        $stats = [
            'dokumen' => [
                'total' => $byType['dokumen']->count(),
                'pending' => $byType['dokumen']->filter(
                    fn($a) =>
                    $a->asesmen->pengajuan?->canBeReported('dokumen')
                )->count(),
                'completed' => $byType['dokumen']->filter(
                    fn($a) =>
                    $a->asesmen->pengajuan?->tanggal_pelaporan_validasi_borang !== null
                )->count(),
            ],
            'ak' => [
                'total' => $byType['ak']->count(),
                'pending' => $byType['ak']->filter(
                    fn($a) =>
                    $a->asesmen->pengajuan?->canBeReported('ak')
                )->count(),
                'completed' => $byType['ak']->filter(
                    fn($a) =>
                    $a->asesmen->pengajuan?->tanggal_pelaporan_ak !== null
                )->count(),
            ],
            'al' => [
                'total' => $byType['al']->count(),
                'pending' => $byType['al']->filter(
                    fn($a) =>
                    $a->asesmen->pengajuan?->canBeReported('al')
                )->count(),
                'completed' => $byType['al']->filter(
                    fn($a) =>
                    $a->asesmen->pengajuan?->tanggal_pelaporan_al !== null
                )->count(),
            ],
        ];

        return view('asesmen.pelaporan.index', compact('stats', 'byType'));
    }

    /**
     * ============================================
     * INDEX Pelaporan Validasi Dokumen
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
            'pending' => $assignments->filter(
                fn($a) =>
                $a->asesmen->pengajuan?->canBeReported('dokumen')
            )->count(),
            'completed' => $assignments->filter(
                fn($a) =>
                $a->asesmen->pengajuan?->tanggal_pelaporan_validasi_borang !== null
            )->count(),
            'in_progress' => $assignments->filter(
                fn($a) =>
                $a->status_pekerjaan === 'in_progress'
            )->count(),
        ];

        return view('asesmen.pelaporan.dokumen', compact('assignments', 'stats'));
    }

    /**
     * ============================================
     * INDEX PELAPORAN VALIDASI AK
     * ============================================
     */
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
            'pending' => $assignments->filter(
                fn($a) =>
                $a->asesmen->pengajuan?->canBeReported('ak')
            )->count(),
            'completed' => $assignments->filter(
                fn($a) =>
                $a->asesmen->pengajuan?->tanggal_pelaporan_ak !== null
            )->count(),
            'in_progress' => $assignments->filter(
                fn($a) =>
                $a->status_pekerjaan === 'in_progress'
            )->count(),
        ];

        return view('asesmen.pelaporan.validasi-ak', compact('assignments', 'stats'));
    }

    /**
     * ============================================
     * INDEX PELAPORAN AK (Asesor)
     * ============================================
     */
    public function indexAK()
    {
        // This might be for asesor's AK reporting if needed
        // For now, redirect to validasi-ak
        return redirect()->route('pelaporan.indexValidasiAK');
    }

    /**
     * ============================================
     * INDEX PELAPORAN AL
     * ============================================
     */
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
            'pending' => $assignments->filter(
                fn($a) =>
                $a->asesmen->pengajuan?->canBeReported('al')
            )->count(),
            'completed' => $assignments->filter(
                fn($a) =>
                $a->asesmen->pengajuan?->tanggal_pelaporan_al !== null
            )->count(),
            'in_progress' => $assignments->filter(
                fn($a) =>
                $a->status_pekerjaan === 'in_progress'
            )->count(),
        ];

        return view('asesmen.pelaporan.al', compact('assignments', 'stats'));
    }

    public function uploadLaporanValidasi(Request $request, $idAssignment)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:5120', // 5MB
            'title' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with([
                'asesmen.pengajuan',
                'role',
            ])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            $pengajuan = $assignment->asesmen->pengajuan;
            if (!$pengajuan) throw new \Exception('Permohonan akreditasi tidak ditemukan');

            // ✅ hanya boleh upload ketika status Permohonan akreditasi VALIDATED / BORANG_FINAL_DITERIMA
            // if (!in_array($pengajuan->status, [
            //     PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            //     PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            // ], true)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Pelaporan hanya bisa dibuat setelah dokumen divalidasi atau draft final diterima.',
            //     ], 422);
            // }

            $file = $request->file('file');
            $path = $file->store("permohonan-akreditasi/{$pengajuan->id}/laporan-validasi", 'public');

            // ✅ satu file saja: update kalau telah ada, create kalau belum
            $doc = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', 'laporan_validasi_borang')
                ->where('is_active', true)
                ->latest('id')
                ->first();

            $payload = [
                'id_asesmen' => $assignment->id_asesmen,
                'type' => 'laporan_validasi_borang',
                'title' => $request->title ?: 'Laporan Kesiapan LED Program Studi (LKLED)',
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
                // hapus file lama (optional tapi bagus)
                if ($doc->path) Storage::disk('public')->delete($doc->path);

                $doc->update($payload + ['version' => ($doc->version ?? 1) + 1]);
            } else {
                $doc = AsesmenDocument::create($payload + ['version' => 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Laporan Kesiapan LED Program Studi (LKLED) berhasil diupload.',
                'doc' => [
                    'title' => $doc->title,
                    'original_name' => $doc->original_name,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('uploadLaporanValidasi failed', ['error' => $e]);
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    public function finalizePelaporanValidasi(Request $request, $idAssignment)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with([
                'asesmen.pengajuan',
                'role_selected',
            ])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'dokumen')
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            $pengajuan = $assignment->asesmen->pengajuan;
            if (!$pengajuan) throw new \Exception('Permohonan akreditasi tidak ditemukan');

            // ✅ hanya boleh finalize dari 2 status ini
            // if (!in_array($pengajuan->status, [
            //     PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            //     PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            // ], true)) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Status Permohonan akreditasi tidak memenuhi syarat untuk pelaporan.',
            //     ], 422);
            // }

            // ✅ wajib telah upload file
            $docExists = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', 'laporan_validasi_borang')
                ->where('is_active', true)
                ->exists();

            if (!$docExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload dulu file Laporan Validasi (PDF) sebelum finalisasi.',
                ], 422);
            }

            // idempotent
            if ($pengajuan->status === PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN) {
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Pelaporan telah difinalisasi sebelumnya.']);
            }

            $statusFrom = $pengajuan->status;

            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                'tanggal_pelaporan_validasi_borang' => now(),
            ]);

            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to' => PengajuanAkreditasi::STATUS_VALIDASI_BORANG_DILAPORKAN,
                'changed_by' => $user->id,
                'keterangan' => 'Laporan Kesiapan LED Program Studi (LKLED) difinalisasi oleh validator ' . $user->name,
                'changed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pelaporan validasi berhasil difinalisasi. Status Permohonan akreditasi telah diperbarui.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('finalizePelaporanValidasi failed', ['error' => $e]);
            return response()->json(['success' => false, 'message' => 'Gagal finalisasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * =========================
     * 2) VALIDASI AK (validator)
     *    - upload file laporan validasi AK
     *    - finalize -> status pengajuan: AK_SELESAI + tanggal_validasi_ak
     * =========================
     */
    public function uploadLaporanValidasiAK(Request $request, $idAssignment)
    {
        $request->validate([
            'file'  => 'required|file|mimes:pdf|max:5120',
            'title' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // validator AK biasanya assignment jenis_asesmen = 'ak' dan role = validator
            $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'ak')
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            $file = $request->file('file');
            $pengajuan = $assignment->asesmen?->pengajuan;
            if (!$pengajuan)
                $path = $file->store("asesmen/{$assignment->asesmen->id}/laporan-validasi-ak", 'public');
            else
                $path = $file->store("permohonan-akreditasi/{$pengajuan->id}/laporan-validasi-ak", 'public');

            $doc = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', 'laporan_validasi_ak')
                ->where('is_active', true)
                ->latest('id')
                ->first();

            $payload = [
                'id_asesmen'     => $assignment->id_asesmen,
                'type'           => 'laporan_validasi_ak',
                'title'          => $request->title ?: 'Laporan Penilaian Kecukupan LED Program Studi (LHK)',
                'sort_order'     => 1,
                'path'           => $path,
                'original_name'  => $file->getClientOriginalName(),
                'size'           => $file->getSize(),
                'mime'           => $file->getMimeType(),
                'uploaded_by'    => $user->id,
                'uploaded_at'    => now(),
                'is_active'      => true,
            ];

            if ($doc) {
                if ($doc->path) Storage::disk('public')->delete($doc->path);
                $doc->update($payload + ['version' => ($doc->version ?? 1) + 1]);
            } else {
                $doc = AsesmenDocument::create($payload + ['version' => 1]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Laporan Validasi AK berhasil diupload.',
                'doc' => [
                    'title' => $doc->title,
                    'original_name' => $doc->original_name,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('uploadLaporanValidasiAK failed', ['error' => $e]);
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    public function finalizeValidasiAK(Request $request, $idAssignment)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'ak')
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            $pengajuan = $assignment->asesmen?->pengajuan;

            // wajib upload file validasi AK
            $docExists = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', 'laporan_validasi_ak')
                ->where('is_active', true)
                ->exists();

            if (!$docExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload dulu file Laporan Validasi AK (PDF) sebelum finalisasi.',
                ], 422);
            }

            if ($pengajuan) {
                // idempotent
                if ($pengajuan->status === PengajuanAkreditasi::STATUS_AK_DILAPORKAN) {
                    DB::commit();
                    return response()->json(['success' => true, 'message' => 'Laporan validasi AK telah difinalisasi sebelumnya.']);
                }
                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('ak', 'status_asesor_dilaporkan');
                $pengajuan->statusLog()->create([
                    'status_from' => $statusFrom,
                    'status_to'   => PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
                    'changed_by'  => $user->id,
                    'keterangan'  => 'Laporan validasi AK difinalisasi oleh ' . $user->name,
                    'changed_at'  => now(),
                ]);
            }

            $asesmenKecukupan = $assignment->asesmen->asesmenKecukupan;

            if ($asesmenKecukupan) {
                $asesmenKecukupan->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $user->id,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Laporan validasi AK berhasil difinalisasi. Pelaporan AK telah selesai dilakukan.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('finalizeValidasiAK failed', ['error' => $e]);
            return response()->json(['success' => false, 'message' => 'Gagal finalisasi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * =========================
     * 3) AK DILAPORKAN (pelaporan AK)
     *    - upload laporan AK (final report)
     *    - finalize -> status pengajuan: AK_DILAPORKAN + tanggal_pelaporan_ak
     *
     * Catatan:
     * - biasanya ini dilakukan oleh asesor/ketua asesor AK atau admin LAM
     * - Anda bisa ubah whereHas('role', ...) sesuai kebutuhan (asesor / admin)
     * =========================
     */
    // public function uploadLaporanAK(Request $request, $idAssignment)
    // {
    //     $request->validate([
    //         'file'  => 'required|file|mimes:pdf|max:5120',
    //         'title' => 'nullable|string|max:150',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $user = Auth::user();

    //         // default: hanya asesor AK (accepted) yang boleh upload laporan AK
    //         $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
    //             ->where('id_user', $user->id)
    //             ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
    //             ->where('jenis_asesmen', 'ak')
    //             ->findOrFail($idAssignment);

    //         if ($assignment->status_penawaran !== 'accepted') {
    //             return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
    //         }

    //         $pengajuan = $assignment->asesmen?->pengajuan;
    //         if (!$pengajuan) throw new \Exception('Permohonan akreditasi tidak ditemukan');

    //         $file = $request->file('file');
    //         $path = $file->store("permohonan-akreditasi/{$pengajuan->id}/laporan-ak", 'public');

    //         $doc = AsesmenDocument::query()
    //             ->where('id_asesmen', $assignment->id_asesmen)
    //             ->where('type', 'laporan_ak')
    //             ->where('is_active', true)
    //             ->latest('id')
    //             ->first();

    //         $payload = [
    //             'id_asesmen'     => $assignment->id_asesmen,
    //             'type'           => 'laporan_ak',
    //             'title'          => $request->title ?: 'Laporan Asesmen Kecukupan (AK)',
    //             'sort_order'     => 1,
    //             'path'           => $path,
    //             'original_name'  => $file->getClientOriginalName(),
    //             'size'           => $file->getSize(),
    //             'mime'           => $file->getMimeType(),
    //             'uploaded_by'    => $user->id,
    //             'uploaded_at'    => now(),
    //             'is_active'      => true,
    //         ];

    //         if ($doc) {
    //             if ($doc->path) Storage::disk('public')->delete($doc->path);
    //             $doc->update($payload + ['version' => ($doc->version ?? 1) + 1]);
    //         } else {
    //             $doc = AsesmenDocument::create($payload + ['version' => 1]);
    //         }

    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Laporan AK berhasil diupload.',
    //             'doc' => [
    //                 'title' => $doc->title,
    //                 'original_name' => $doc->original_name,
    //             ],
    //         ]);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('uploadLaporanAK failed', ['error' => $e]);
    //         return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
    //     }
    // }

    // public function finalizePelaporanAK(Request $request, $idAssignment)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $user = Auth::user();

    //         $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
    //             ->where('id_user', $user->id)
    //             ->whereHas('role', fn($q) => $q->where('name', 'asesor'))
    //             ->where('jenis_asesmen', 'ak')
    //             ->findOrFail($idAssignment);

    //         if ($assignment->status_penawaran !== 'accepted') {
    //             return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
    //         }

    //         $pengajuan = $assignment->asesmen?->pengajuan;
    //         if (!$pengajuan) throw new \Exception('Permohonan akreditasi tidak ditemukan');

    //         $docExists = AsesmenDocument::query()
    //             ->where('id_asesmen', $assignment->id_asesmen)
    //             ->where('type', 'laporan_ak')
    //             ->where('is_active', true)
    //             ->exists();

    //         if (!$docExists) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Upload dulu file Laporan AK (PDF) sebelum finalisasi.',
    //             ], 422);
    //         }

    //         if ($pengajuan->status === PengajuanAkreditasi::STATUS_AK_SELESAI) {
    //             DB::commit();
    //             return response()->json(['success' => true, 'message' => 'Pelaporan AK telah difinalisasi sebelumnya.']);
    //         }

    //         $statusFrom = $pengajuan->status;

    //         $pengajuan->checkUpdateStatusAKAL('ak', 'status_asesor_selesai');

    //         $pengajuan->statusLog()->create([
    //             'status_from' => $statusFrom,
    //             'status_to'   => PengajuanAkreditasi::STATUS_AK_SELESAI,
    //             'changed_by'  => $user->id,
    //             'keterangan'  => 'Pelaporan AK difinalisasi oleh asesor ' . $user->name,
    //             'changed_at'  => now(),
    //         ]);

    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pelaporan AK berhasil difinalisasi. Status Permohonan akreditasi menjadi AK DILAPORKAN.',
    //         ]);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('finalizePelaporanAK failed', ['error' => $e]);
    //         return response()->json(['success' => false, 'message' => 'Gagal finalisasi: ' . $e->getMessage()], 500);
    //     }
    // }

    /**
     * =========================
     * 4) AL DILAPORKAN (pelaporan AL)
     *    - upload laporan AL (final report)
     *    - finalize -> status pengajuan: AL_DILAPORKAN + tanggal_pelaporan_al
     *
     * Catatan:
     * - biasanya dilakukan oleh asesor AL (accepted)
     * =========================
     */
    public function uploadLaporanAL(Request $request, $idAssignment)
    {
        $request->validate([
            'file'  => 'required|file|mimes:pdf|max:5120',
            'title' => 'nullable|string|max:150',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'al')
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            $file = $request->file('file');

            $pengajuan = $assignment->asesmen?->pengajuan;
            if (!$pengajuan)
                $path = $file->store("asesmen/{$assignment->asesmen->id}/laporan-al", 'public');
            else
                $path = $file->store("permohonan-akreditasi/{$pengajuan->id}/laporan-al", 'public');

            $doc = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', 'laporan_al')
                ->where('is_active', true)
                ->latest('id')
                ->first();

            $payload = [
                'id_asesmen'     => $assignment->id_asesmen,
                'type'           => 'laporan_al',
                'title'          => $request->title ?: 'Laporan Hasil Asesmen Lapangan Program Studi (LHA)',
                'sort_order'     => 1,
                'path'           => $path,
                'original_name'  => $file->getClientOriginalName(),
                'size'           => $file->getSize(),
                'mime'           => $file->getMimeType(),
                'uploaded_by'    => $user->id,
                'uploaded_at'    => now(),
                'is_active'      => true,
            ];

            if ($doc) {
                if ($doc->path) Storage::disk('public')->delete($doc->path);
                $doc->update($payload + ['version' => ($doc->version ?? 1) + 1]);
            } else {
                $doc = AsesmenDocument::create($payload + ['version' => 1]);
            }
            $assignment->update(['status_pekerjaan' => 'in_progress']);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Laporan AL berhasil diupload.',
                'doc' => [
                    'title' => $doc->title,
                    'original_name' => $doc->original_name,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('uploadLaporanAL failed', ['error' => $e]);
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    public function finalizePelaporanAL(Request $request, $idAssignment)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $assignment = AsesmenUserRole::with(['asesmen.pengajuan', 'role'])
                ->where('id_user', $user->id)
                ->whereHas('role', fn($q) => $q->where('name', 'validator'))
                ->where('jenis_asesmen', 'al')
                ->findOrFail($idAssignment);

            if ($assignment->status_penawaran !== 'accepted') {
                return response()->json(['success' => false, 'message' => 'Penawaran belum diterima.'], 422);
            }

            $pengajuan = $assignment->asesmen?->pengajuan;

            $docExists = AsesmenDocument::query()
                ->where('id_asesmen', $assignment->id_asesmen)
                ->where('type', 'laporan_al')
                ->where('is_active', true)
                ->exists();

            if (!$docExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload dulu file Laporan AL (PDF) sebelum finalisasi.',
                ], 422);
            }

            if ($pengajuan) {
                if ($pengajuan->status === PengajuanAkreditasi::STATUS_AL_DILAPORKAN) {
                    DB::commit();
                    return response()->json(['success' => true, 'message' => 'Pelaporan AL telah difinalisasi sebelumnya.']);
                }

                $statusFrom = $pengajuan->status;
                $pengajuan->checkUpdateStatusAKAL('al', 'status_asesor_dilaporkan');

                $pengajuan->statusLog()->create([
                    'status_from' => $statusFrom,
                    'status_to'   => PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
                    'changed_by'  => $user->id,
                    'keterangan'  => 'Pelaporan AL difinalisasi oleh ' . $user->name,
                    'changed_at'  => now(),
                ]);
            }
            $assignment->asesmen->update([
                'status' => 'completed'
            ]);

            // 3. Update Asesmen Lapangan
            $asesmenLapangan = $assignment->asesmen->asesmenLapangan;

            if ($asesmenLapangan) {
                $asesmenLapangan->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'completed_by' => $user->id,
                ]);
            }

            $assignment->update(['status_pekerjaan' => 'submitted', 'submitted_at' => now()]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pelaporan AL berhasil difinalisasi. Status Permohonan akreditasi menjadi AL DILAPORKAN.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('finalizePelaporanAL failed', ['error' => $e]);
            return response()->json(['success' => false, 'message' => 'Gagal finalisasi: ' . $e->getMessage()], 500);
        }
    }
}
