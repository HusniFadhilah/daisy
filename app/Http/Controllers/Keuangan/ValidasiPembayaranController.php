<?php

namespace App\Http\Controllers\Keuangan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ValidasiPembayaranController extends Controller
{
    /**
     * List pembayaran yang perlu divalidasi (status dibayar).
     * Filter utama: pengajuan.status = MENUNGGU_PEMBAYARAN + pembayaran.status_pembayaran = dibayar
     */
    public function index(Request $request)
    {
        $q = (string) $request->get('q');

        $pengajuanQuery = PengajuanAkreditasi::query()
            ->with([
                'studyProgram.university',
                'pembayaran',
                'pengaju',
            ])
            // ->where('status', PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN)
            ->whereHas('pembayaran', function ($p) {
                // $p->where('status_pembayaran', 'dibayar');
            });

        if (!empty($q)) {
            $pengajuanQuery->where(function ($w) use ($q) {
                $w->where('nomor_pengajuan', 'like', "%{$q}%")
                    ->orWhereHas('studyProgram', function ($sp) use ($q) {
                        $sp->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('pembayaran', function ($p) use ($q) {
                        $p->where('nomor_invoice', 'like', "%{$q}%");
                    });
            });
        }

        $pengajuan = $pengajuanQuery
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('keuangan.pembayaran.index', compact('pengajuan', 'q'));
    }

    /**
     * Detail satu pengajuan: tampilkan info invoice + bukti pembayaran.
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pembayaran.verifier',
            'pengaju',
        ])->findOrFail($id);

        if (!$pengajuan->pembayaran) {
            abort(404, 'Data pembayaran belum tersedia.');
        }

        $path = $pengajuan->pembayaran->bukti_path;
        $url  = $path ? Storage::disk('public')->url($path) : null;
        $ext  = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;

        return view('keuangan.pembayaran.show', compact('pengajuan', 'path', 'url', 'ext'));
    }

    /**
     * Download bukti pembayaran (pdf/jpg/png).
     * Disarankan pakai signed route kalau butuh proteksi tambahan.
     */
    public function downloadBukti($id)
    {
        $pengajuan = PengajuanAkreditasi::with('pembayaran')->findOrFail($id);

        if (!$pengajuan->pembayaran) {
            abort(404, 'Data pembayaran tidak ditemukan.');
        }

        // ✅ Samakan nama field ini dengan kolom di tabel pengajuan_pembayaran Anda
        // Jika kolom Anda "bukti_pembayaran_path", ganti di sini dan di blade show.
        $path = $pengajuan->pembayaran->bukti_path;

        if (empty($path)) {
            abort(404, 'File bukti pembayaran belum diupload.');
        }

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File bukti pembayaran tidak ditemukan di storage.');
        }

        return Storage::disk('public')->download($path);
    }

    /**
     * Verifikasi / Tolak pembayaran.
     * Input:
     * - status: verified | ditolak
     * - catatan_verifikasi (required)
     * - alasan_penolakan (required jika ditolak)
     */
    public function verify(Request $request, $id)
    {
        try {
            // ✅ Validasi input
            $request->validate([
                'status_pembayaran'   => 'required|in:terverifikasi,ditolak',
                'catatan_verifikasi'  => 'required|string|min:5|max:2000',
                'alasan_penolakan'    => 'required_if:status_pembayaran,ditolak|nullable|string|max:2000',
            ]);

            // ✅ Ambil pengajuan + pembayaran
            $pengajuan = PengajuanAkreditasi::with('pembayaran')->findOrFail($id);

            if (!$pengajuan->pembayaran) {
                return back()->with('error', 'Pembayaran tidak ditemukan.');
            }

            if ($pengajuan->pembayaran->status_pembayaran !== 'dibayar') {
                return back()->with('error', 'Pembayaran tidak dalam status "dibayar".');
            }

            $statusInput = $request->input('status_pembayaran');

            // 🔐 Transaction
            DB::transaction(function () use ($request, $pengajuan, $statusInput) {
                $pembayaran = $pengajuan->pembayaran;

                $pembayaran->status_pembayaran   = $statusInput;
                $pembayaran->catatan_verifikasi = $request->catatan_verifikasi;
                $pembayaran->alasan_penolakan   = $statusInput === 'ditolak'
                    ? $request->alasan_penolakan
                    : null;

                $pembayaran->verified_by = auth()->id();

                if (array_key_exists('verified_at', $pembayaran->getAttributes())) {
                    $pembayaran->verified_at = now();
                }

                $pembayaran->save();

                // 🔁 Update status pengajuan
                $oldStatus = $pengajuan->status;

                if ($statusInput === 'terverifikasi') {
                    $pengajuan->status = PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI;

                    if (array_key_exists('tanggal_pembayaran', $pengajuan->getAttributes())) {
                        $pengajuan->tanggal_pembayaran = now();
                    }
                }

                if ($statusInput === 'ditolak') {
                    $pengajuan->status = PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN;
                }

                $pengajuan->save();

                // 📝 Log status (jika ada)
                if (method_exists($this, 'logStatus')) {
                    $this->logStatus(
                        $pengajuan,
                        $oldStatus,
                        $pengajuan->status,
                        $statusInput === 'terverifikasi'
                            ? 'Pembayaran diverifikasi oleh Keuangan.'
                            : 'Pembayaran ditolak oleh Keuangan.'
                    );
                }
            });

            // ✅ Redirect sukses
            return redirect()
                ->route('keuangan.pembayaran.index')
                ->with(
                    'success',
                    $statusInput === 'terverifikasi'
                        ? 'Pembayaran berhasil diverifikasi.'
                        : 'Pembayaran berhasil ditolak.'
                );
        } catch (\Throwable $e) {

            // ❌ Log error lengkap
            Log::error('Gagal verifikasi pembayaran', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with(
                'error',
                'Terjadi kesalahan saat memproses verifikasi. Silakan coba lagi.'
            );
        }
    }

    private function logStatus($pengajuan, $oldStatus, $newStatus, $keterangan = null)
    {
        $pengajuan->statusLog()->create([
            'status_from' => $oldStatus ?? 'new',
            'status_to' => $newStatus,
            'changed_by' => Auth::id(),
            'keterangan' => $keterangan,
            'changed_at' => now(),
        ]);
    }
}
