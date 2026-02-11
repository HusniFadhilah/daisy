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
        $university_id = $request->get('university_id');
        $degree_level_id = $request->get('degree_level_id');
        $status = $request->get('status');

        // Query pengajuan yang sudah upload formulir pembayaran
        $pengajuanQuery = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pembayaran',
            'pengaju',
            'dokumen' => function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true);
            }
        ])
            ->whereHas('dokumen', function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true);
            })
            ->whereHas('pembayaran'); // Harus sudah ada pembayaran

        // Filter by search
        if (!empty($q)) {
            $pengajuanQuery->where(function ($w) use ($q) {
                $w->where('nomor_pengajuan', 'like', "%{$q}%")
                    ->orWhere('judul', 'like', "%{$q}%")
                    ->orWhereHas('studyProgram', function ($sp) use ($q) {
                        $sp->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('pembayaran', function ($p) use ($q) {
                        $p->where('nomor_invoice', 'like', "%{$q}%");
                    });
            });
        }

        // Filter by university
        if (!empty($university_id)) {
            $pengajuanQuery->whereHas('studyProgram', function ($sp) use ($university_id) {
                $sp->where('id_university', $university_id);
            });
        }

        // Filter by degree level
        if (!empty($degree_level_id)) {
            $pengajuanQuery->whereHas('studyProgram', function ($sp) use ($degree_level_id) {
                $sp->where('id_degree_level', $degree_level_id);
            });
        }

        // Filter by status pembayaran
        if (!empty($status)) {
            $pengajuanQuery->whereHas('pembayaran', function ($p) use ($status) {
                $p->where('status_pembayaran', $status);
            });
        }

        $pengajuan = $pengajuanQuery
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Statistics
        $stats = [
            'total' => PengajuanAkreditasi::whereHas('dokumen', function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true);
            })->count(),

            'today' => PengajuanAkreditasi::whereHas('dokumen', function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true)
                    ->whereDate('created_at', today());
            })->count(),

            'menunggu_verifikasi' => PengajuanAkreditasi::whereHas('pembayaran', function ($p) {
                $p->where('status_pembayaran', 'menunggu_verifikasi');
            })->count(),

            'terverifikasi' => PengajuanAkreditasi::whereHas('pembayaran', function ($p) {
                $p->where('status_pembayaran', 'terverifikasi');
            })->count(),
        ];

        return view('keuangan.pembayaran.index', compact(
            'pengajuan',
            'q',
            'university_id',
            'degree_level_id',
            'status',
            'stats'
        ));
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

            // ✅ Ambil Permohonan akreditasi + pembayaran
            $pengajuan = PengajuanAkreditasi::with('pembayaran')->findOrFail($id);

            if (!$pengajuan->pembayaran) {
                return back()->with('error', 'Pembayaran tidak ditemukan.');
            }

            if ($pengajuan->pembayaran->status_pembayaran !== 'menunggu_verifikasi') {
                return back()->with('error', 'Pembayaran tidak dalam status "Menunggu Validasi".');
            }

            $statusInput = $request->input('status_pembayaran');
            $messages = [
                'terverifikasi' => 'Pembayaran divalidasi oleh Keuangan.',
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
            Log::error('Gagal validasi pembayaran', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with(
                'error',
                'Terjadi kesalahan saat memproses validasi. Silakan coba lagi.'
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
