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
     * List pembayaran yang perlu divalidasi (status menunggu_verifikasi).
     * Filter utama: pengajuan.status = MENUNGGU_VERIFIKASI_PEMBAYARAN + pembayaran.status_pembayaran = menunggu_verifikasi
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
                // $p->where('status_pembayaran', 'menunggu_verifikasi');
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
     */
    public function verify(Request $request, $id)
    {
        try {
            // ✅ Validasi input
            $request->validate([
                'status_pembayaran'   => 'required|in:menunggu_verifikasi,terverifikasi,upload_ulang,ditolak',
                'catatan_verifikasi'  => 'required|string|min:5|max:2000',
                // 'alasan_penolakan'    => 'required_if:status_pembayaran,ditolak|nullable|string|max:2000',
            ]);

            // ✅ Ambil pengajuan + pembayaran
            $pengajuan = PengajuanAkreditasi::with('pembayaran')->findOrFail($id);

            if (!$pengajuan->pembayaran) {
                return back()->with('error', 'Pembayaran tidak ditemukan.');
            }

            if ($pengajuan->pembayaran->status_pembayaran !== 'menunggu_verifikasi') {
                return back()->with('error', 'Pembayaran tidak dalam status "menunggu_verifikasi".');
            }

            $statusInput = $request->input('status_pembayaran');
            $messages = [
                'terverifikasi' => 'Pembayaran diverifikasi oleh Keuangan.',
                'upload_ulang'  => 'Keuangan meminta upload ulang bukti pembayaran.',
            ];

            $message = $messages[$statusInput] ?? 'Status pembayaran tidak diketahui.';

            // 🔐 Transaction
            DB::transaction(function () use ($request, $pengajuan, $statusInput, $message) {
                $pembayaran = $pengajuan->pembayaran;

                $pembayaran->status_pembayaran   = $statusInput;
                $pembayaran->catatan_verifikasi = $request->catatan_verifikasi;
                // $pembayaran->alasan_penolakan   = $statusInput === 'ditolak'? $request->alasan_penolakan: null;

                $pembayaran->verified_by = auth()->id();

                if (array_key_exists('tanggal_verifikasi', $pembayaran->getAttributes())) {
                    $pembayaran->tanggal_verifikasi = now();
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

                if ($statusInput === 'upload_ulang') {
                    $pengajuan->status = PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN;
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
                        $message
                    );
                }
            });

            // ✅ Redirect sukses
            return redirect()
                ->route('keuangan.pembayaran.index')
                ->with(
                    'success',
                    $message
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
