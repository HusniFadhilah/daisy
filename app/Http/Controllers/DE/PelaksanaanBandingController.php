<?php

namespace App\Http\Controllers\DE;

use App\Models\Asesmen;
use App\Models\AsesmenUserRole;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PelaksanaanBandingController extends Controller
{
    private const JENIS_ASESMEN = 'banding';

    private function phaseStatuses(): array
    {
        return [
            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
        ];
    }

    // ============================================================
    // INDEX
    // ============================================================

    public function index(Request $request)
    {
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenBanding',
            'asesmen.asesmenUserRoles' => fn($q) =>
            $q->where('jenis_asesmen', self::JENIS_ASESMEN)->with(['user', 'role_selected']),
            'statusLog',
        ])
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $this->phaseStatuses()));

        if ($request->filled('university_id')) {
            $query->whereHas(
                'studyProgram',
                fn($q) =>
                $q->where('id_university', $request->university_id)
            );
        }

        if ($request->filled('status_pelaksanaan')) {
            $query->whereHas(
                'statusLog',
                fn($q) =>
                $q->where('status_to', $request->status_pelaksanaan)
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas(
                        'studyProgram',
                        fn($sq) =>
                        $sq->where('name', 'like', "%{$search}%")
                    );
            });
        }

        $pengajuans   = $query->orderByDesc('updated_at')->paginate(20);
        $stats        = $this->calculateStatistics();
        $universities = \App\Models\University::nonExample()->orderBy('name')->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.pelaksanaan-banding.components.table-content', compact('pengajuans'))->render();
            return response()->json(['success' => true, 'html' => $html, 'total' => $pengajuans->total()]);
        }

        return view('de.pelaksanaan-banding.index', compact('pengajuans', 'stats', 'universities'));
    }

    // ============================================================
    // SHOW
    // ============================================================

    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.asesmenBanding',
            'asesmen.asesmenUserRoles' => fn($q) =>
            $q->where('jenis_asesmen', self::JENIS_ASESMEN)->with(['user', 'role_selected']),
            'dokumen',
            'statusLog' => fn($q) => $q->orderByDesc('changed_at')->with('changedBy'),
        ])->findOrFail($id);

        // Dokumen pelaksanaan
        $suratTugasBanding = $pengajuan->dokumen
            ->where('jenis_dokumen', 'surat_tugas_asesor_banding')
            ->where('is_latest', true)->first();

        $beritaAcaraBanding = $pengajuan->dokumen
            ->where('jenis_dokumen', 'berita_acara_banding')
            ->where('is_latest', true)->first();

        $laporanBanding = $pengajuan->dokumen
            ->where('jenis_dokumen', 'laporan_banding')
            ->where('is_latest', true)->first();

        $asesors = $pengajuan->asesmen
            ? $pengajuan->asesmen->asesmenUserRoles->where('jenis_asesmen', self::JENIS_ASESMEN)
            : collect();

        return view('de.pelaksanaan-banding.show', compact(
            'pengajuan',
            'suratTugasBanding',
            'beritaAcaraBanding',
            'laporanBanding',
            'asesors',
        ));
    }

    // ============================================================
    // START PELAKSANAAN: banding_ditugaskan → banding_dilaksanakan
    // ============================================================

    public function startPelaksanaan(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status harus banding_ditugaskan untuk memulai pelaksanaan.',
                ], 422);
            }

            // Pastikan minimal 1 asesor sudah accepted
            $hasAccepted = AsesmenUserRole::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('jenis_asesmen', self::JENIS_ASESMEN)
                ->where('status_penawaran', 'accepted')
                ->exists();

            if (!$hasAccepted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada asesor banding yang menyetujui penawaran.',
                ], 422);
            }

            $statusFrom = $pengajuan->status;
            $pengajuan->update(['status' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN]);
            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                'changed_by'  => Auth::id(),
                'keterangan'  => 'Pelaksanaan banding dimulai.'
                    . ($request->catatan ? ' Catatan: ' . $request->catatan : ''),
                'changed_at'  => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pelaksanaan banding berhasil dimulai.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('startPelaksanaanBanding failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // UPLOAD DOKUMEN PELAKSANAAN
    // Mendukung: berita_acara_banding, laporan_banding
    // ============================================================

    public function uploadDokumen(Request $request, $id, $jenisDokumen)
    {
        $validJenis = ['berita_acara_banding', 'laporan_banding'];
        abort_unless(in_array($jenisDokumen, $validJenis), 400, 'Jenis dokumen tidak valid.');

        $request->validate(['file' => 'required|file|mimes:pdf|max:10240']);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Non-aktifkan dokumen lama
            $pengajuan->dokumen()
                ->where('jenis_dokumen', $jenisDokumen)
                ->update(['is_latest' => false]);

            $versi        = ($pengajuan->dokumen()->where('jenis_dokumen', $jenisDokumen)->max('versi') ?? 0) + 1;
            $file         = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $sanitized    = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $filename     = time() . '_' . strtoupper($jenisDokumen) . '_' . $sanitized;
            $folder       = 'dokumen/' . str_replace('_', '-', $jenisDokumen);
            $path         = $file->storeAs($folder, $filename, 'public');

            $labels = [
                'berita_acara_banding' => 'Berita Acara Banding',
                'laporan_banding'      => 'Laporan Banding',
            ];

            $pengajuan->dokumen()->create([
                'jenis_dokumen'     => $jenisDokumen,
                'nama_file'         => $filename,
                'path_file'         => $path,
                'original_filename' => $originalName,
                'file_size'         => $file->getSize(),
                'mime_type'         => $file->getMimeType(),
                'uploaded_by'       => Auth::id(),
                'keterangan'        => "{$labels[$jenisDokumen]} — {$pengajuan->nomor_pengajuan}",
                'is_latest'         => true,
                'versi'             => $versi,
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "{$labels[$jenisDokumen]} berhasil diupload.",
                'data'    => ['versi' => $versi, 'filename' => $originalName],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('uploadDokumenBanding failed', ['jenis' => $jenisDokumen, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // DOWNLOAD DOKUMEN
    // ============================================================

    public function downloadDokumen($id, $jenisDokumen)
    {
        $validJenis = ['surat_tugas_asesor_banding', 'berita_acara_banding', 'laporan_banding'];
        abort_unless(in_array($jenisDokumen, $validJenis), 400, 'Jenis dokumen tidak valid.');

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $dokumen   = $pengajuan->dokumen()
            ->where('jenis_dokumen', $jenisDokumen)
            ->where('is_latest', true)
            ->firstOrFail();

        if ($dokumen->template_link) return redirect($dokumen->template_link);

        if ($dokumen->path_file && Storage::disk('public')->exists($dokumen->path_file)) {
            return Storage::disk('public')->download($dokumen->path_file, $dokumen->original_filename);
        }

        abort(404, 'File tidak ditemukan.');
    }

    // ============================================================
    // SUBMIT LAPORAN: banding_dilaksanakan → banding_dilaporkan
    // ============================================================

    public function submitLaporan(Request $request, $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::with('dokumen')->findOrFail($id);

            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status harus banding_dilaksanakan untuk submit laporan.',
                ], 422);
            }

            // Pastikan laporan sudah diupload
            $laporanAda = $pengajuan->dokumen()
                ->where('jenis_dokumen', 'laporan_banding')
                ->where('is_latest', true)
                ->exists();

            if (!$laporanAda) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload laporan banding terlebih dahulu sebelum submit.',
                ], 422);
            }

            $statusFrom = $pengajuan->status;
            $pengajuan->update(['status' => PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN]);
            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
                'changed_by'  => Auth::id(),
                'keterangan'  => 'Laporan banding disubmit.'
                    . ($request->catatan ? ' Catatan: ' . $request->catatan : ''),
                'changed_at'  => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Laporan banding berhasil disubmit. Status diperbarui ke Dilaporkan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('submitLaporanBanding failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function calculateStatistics(): array
    {
        $base = PengajuanAkreditasi::whereHas(
            'statusLog',
            fn($q) =>
            $q->whereIn('status_to', $this->phaseStatuses())
        );

        return [
            'total' => (clone $base)->count(),

            'ditugaskan' => (clone $base)->whereHas(
                'statusLog',
                fn($q) =>
                $q->where('status_to', PengajuanAkreditasi::STATUS_ASESOR_AK_BANDING_ASSIGNED)
            )->count(),

            'dilaksanakan' => (clone $base)->whereHas(
                'statusLog',
                fn($q) =>
                $q->where('status_to', PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
            )->count(),

            'dilaporkan' => (clone $base)->whereHas(
                'statusLog',
                fn($q) =>
                $q->where('status_to', PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN)
            )->count(),
        ];
    }
}
