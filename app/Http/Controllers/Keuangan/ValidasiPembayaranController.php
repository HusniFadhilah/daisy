<?php

namespace App\Http\Controllers\Keuangan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanPembayaran;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ValidasiPembayaranController extends Controller
{
    // ============================================================
    // INDEX — sumber data: PengajuanPembayaran (semua jenis)
    // ============================================================

    public function index(Request $request)
    {
        $q               = (string) $request->get('q');
        $university_id   = $request->get('university_id');
        $degree_level_id = $request->get('degree_level_id');
        $status          = $request->get('status');

        $query = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
            'verifier',
        ]);

        // Pencarian
        if (!empty($q)) {
            $query->where(function ($w) use ($q) {
                $w->where('nomor_invoice', 'like', "%{$q}%")
                    ->orWhereHas(
                        'pengajuan',
                        fn($p) =>
                        $p->where('nomor_pengajuan', 'like', "%{$q}%")
                            ->orWhere('judul', 'like', "%{$q}%")
                    )
                    ->orWhereHas(
                        'pengajuan.studyProgram',
                        fn($sp) =>
                        $sp->where('name', 'like', "%{$q}%")
                    );
            });
        }

        // Filter universitas
        if (!empty($university_id)) {
            $query->whereHas(
                'pengajuan.studyProgram',
                fn($sp) =>
                $sp->where('id_university', $university_id)
            );
        }

        // Filter jenjang
        if (!empty($degree_level_id)) {
            $query->whereHas(
                'pengajuan.studyProgram',
                fn($sp) =>
                $sp->where('id_degree_level', $degree_level_id)
            );
        }

        // Filter status pembayaran
        if (!empty($status)) {
            $query->where('status_pembayaran', $status);
        }

        // $pembayarans — nama baru sesuai sumber data
        $pembayarans = $query
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $stats = $this->calculateStatistics();

        return view('keuangan.pembayaran.index', compact(
            'pembayarans',
            'q',
            'university_id',
            'degree_level_id',
            'status',
            'stats',
        ));
    }

    // ============================================================
    // SHOW — $id adalah PengajuanPembayaran.id
    // ============================================================

    public function show($id)
    {
        $pembayaran = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
            'verifier',
        ])->findOrFail($id);

        $pengajuan = $pembayaran->pengajuan;

        $path = $pembayaran->bukti_path;
        $ext  = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
        $url  = $path ? Storage::disk('public')->url($path) : null;

        return view('keuangan.pembayaran.show', compact(
            'pengajuan',
            'pembayaran',
            'path',
            'ext',
            'url',
        ));
    }

    // ============================================================
    // DOWNLOAD BUKTI — $id adalah PengajuanPembayaran.id
    // ============================================================

    public function downloadBukti($id)
    {
        $pembayaran = PengajuanPembayaran::findOrFail($id);
        $path       = $pembayaran->bukti_path;

        if (empty($path)) {
            abort(404, 'File bukti pembayaran belum diupload.');
        }

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        return Storage::disk('public')->download($path);
    }

    // ============================================================
    // VERIFY — $id adalah PengajuanPembayaran.id
    // ============================================================

    public function verify(Request $request, $id)
    {
        try {
            $request->validate([
                'status_pembayaran'  => 'required|in:terverifikasi,upload_ulang,ditolak',
                'catatan_verifikasi' => 'required|string|min:5|max:2000',
            ]);

            $pembayaran = PengajuanPembayaran::with('pengajuan')->findOrFail($id);

            if ($pembayaran->status_pembayaran !== 'menunggu_verifikasi') {
                return back()->with('error', 'Pembayaran tidak dalam status "Menunggu Validasi".');
            }

            $statusInput     = $request->input('status_pembayaran');
            $jenisPembayaran = $pembayaran->jenis_pembayaran; // 'akreditasi' | 'banding'
            $pengajuan       = $pembayaran->pengajuan;

            DB::transaction(function () use ($request, $pengajuan, $pembayaran, $statusInput, $jenisPembayaran) {

                // Update record invoice
                $pembayaran->status_pembayaran  = $statusInput;
                $pembayaran->catatan_verifikasi = $request->catatan_verifikasi;
                $pembayaran->verified_by        = auth()->id();

                if (in_array('tanggal_verifikasi', $pembayaran->getFillable())) {
                    $pembayaran->tanggal_verifikasi = now();
                }

                $pembayaran->save();

                // Update status PengajuanAkreditasi
                $oldStatus  = $pengajuan->status;
                $newStatus  = $oldStatus;
                $keterangan = '';

                if ($jenisPembayaran === 'akreditasi') {
                    if ($statusInput === 'terverifikasi') {
                        $newStatus  = PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI;
                        $keterangan = 'Pembayaran akreditasi divalidasi oleh Keuangan.';

                        if (in_array('tanggal_pembayaran', $pengajuan->getFillable())) {
                            $pengajuan->tanggal_pembayaran = now();
                        }
                    } elseif ($statusInput === 'upload_ulang') {
                        $newStatus  = PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN;
                        $keterangan = 'Keuangan meminta upload ulang bukti pembayaran akreditasi.';
                    } elseif ($statusInput === 'ditolak') {
                        $newStatus  = PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN;
                        $keterangan = 'Pembayaran akreditasi ditolak oleh Keuangan.';
                    }
                } elseif ($jenisPembayaran === 'banding') {
                    if ($statusInput === 'terverifikasi') {
                        $newStatus  = PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI;
                        $keterangan = 'Pembayaran banding divalidasi oleh Keuangan.';
                    } elseif ($statusInput === 'upload_ulang') {
                        $newStatus  = PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN_BANDING;
                        $keterangan = 'Keuangan meminta upload ulang bukti pembayaran banding.';
                    } elseif ($statusInput === 'ditolak') {
                        $newStatus  = PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN_BANDING;
                        $keterangan = 'Pembayaran banding ditolak oleh Keuangan.';
                    }
                }

                if ($newStatus !== $oldStatus) {
                    $pengajuan->status = $newStatus;
                    $pengajuan->save();
                }

                $pengajuan->statusLog()->create([
                    'status_from' => $oldStatus,
                    'status_to'   => $newStatus,
                    'changed_by'  => auth()->id(),
                    'changed_at'  => now(),
                    'keterangan'  => $keterangan,
                ]);
            });

            $pesan = [
                'terverifikasi' => 'Pembayaran berhasil divalidasi.',
                'upload_ulang'  => 'Berhasil meminta upload ulang ke PS/UPPS.',
                'ditolak'       => 'Pembayaran ditolak.',
            ][$statusInput] ?? 'Status pembayaran diperbarui.';

            return redirect()
                ->route('keuangan.pembayaran.index')
                ->with('success', $pesan);
        } catch (\Throwable $e) {
            Log::error('Gagal validasi pembayaran', [
                'pembayaran_id' => $id,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memproses validasi. Silakan coba lagi.');
        }
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function calculateStatistics(): array
    {
        $row = PengajuanPembayaran::selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN status_pembayaran = 'menunggu_verifikasi' THEN 1 ELSE 0 END) AS menunggu_verifikasi,
            SUM(CASE WHEN status_pembayaran = 'terverifikasi'       THEN 1 ELSE 0 END) AS terverifikasi,
            SUM(CASE WHEN status_pembayaran = 'upload_ulang'        THEN 1 ELSE 0 END) AS upload_ulang,
            SUM(CASE WHEN status_pembayaran = 'ditolak'             THEN 1 ELSE 0 END) AS ditolak
        ")->first();

        return [
            'total'               => (int) ($row->total               ?? 0),
            'today'               => PengajuanPembayaran::where('status_pembayaran', 'menunggu_verifikasi')
                ->whereDate('updated_at', today())
                ->count(),
            'menunggu_verifikasi' => (int) ($row->menunggu_verifikasi ?? 0),
            'terverifikasi'       => (int) ($row->terverifikasi       ?? 0),
            'upload_ulang'        => (int) ($row->upload_ulang        ?? 0),
            'ditolak'             => (int) ($row->ditolak             ?? 0),
        ];
    }
}
