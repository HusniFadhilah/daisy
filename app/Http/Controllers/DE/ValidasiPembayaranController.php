<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanPembayaran;
use App\Models\PengajuanDokumen;
use App\Models\University;
use App\Models\DegreeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ValidasiPembayaranController extends Controller
{
    /**
     * Display list of payment validation
     */
    public function index(Request $request)
    {
        // Build query untuk pembayaran
        $query = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'verifier'
        ]);

        // Filter by status pembayaran
        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('pengajuan.studyProgram', function ($q) use ($request) {
                $q->where('id_university', $request->university_id);
            });
        }

        // Filter by degree level
        if ($request->filled('degree_level_id')) {
            $query->whereHas('pengajuan.studyProgram', function ($q) use ($request) {
                $q->where('id_degree_level', $request->degree_level_id);
            });
        }

        // Search by nomor invoice or prodi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_invoice', 'like', "%{$search}%")
                    ->orWhereHas('pengajuan.studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $pembayarans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // AJAX request
        if ($request->ajax()) {
            $html = view('de.validasi-pembayaran.components.table-content', compact('pembayarans'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'stats' => $stats,
            ]);
        }

        return view('de.validasi-pembayaran.index', compact(
            'pembayarans',
            'stats',
            'universities',
            'degreeLevels',
        ));
    }

    /**
     * Show detail pembayaran
     */
    public function show($id)
    {
        $pembayaran = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
            'verifier'
        ])->findOrFail($id);

        // Get dokumen pembayaran terkait
        $dokumenPembayaran = PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
            ->whereIn('jenis_dokumen', ['formulir_pembayaran'])
            ->where('is_latest', true)
            ->get();

        return view('de.validasi-pembayaran.show', compact('pembayaran', 'dokumenPembayaran'));
    }

    /**
     * Kirim invoice pembayaran ke prodi
     */
    public function kirimInvoice(Request $request)
    {
        $validated = $request->validate([
            'id_pengajuan' => 'required|array',
            'id_pengajuan.*' => 'exists:pengajuan_akreditasi,id',
            'jumlah_pembayaran' => 'required|numeric|min:0',
            'tanggal_jatuh_tempo' => 'required|date|after:today',
        ]);

        DB::beginTransaction();
        try {
            $sent = 0;

            foreach ($validated['id_pengajuan'] as $pengajuanId) {
                $pengajuan = PengajuanAkreditasi::find($pengajuanId);

                // Validasi status pengajuan
                if (!in_array($pengajuan->status, [
                    PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                ])) {
                    continue;
                }

                // Generate nomor invoice
                $nomorInvoice = PengajuanPembayaran::generateNomorInvoice();

                // Create pembayaran record
                $pembayaran = PengajuanPembayaran::create([
                    'id_pengajuan' => $pengajuanId,
                    'nomor_invoice' => $nomorInvoice,
                    'jumlah_pembayaran' => $validated['jumlah_pembayaran'],
                    'tanggal_jatuh_tempo' => $validated['tanggal_jatuh_tempo'],
                    'status_pembayaran' => 'menunggu_pembayaran',
                ]);

                // Update status pengajuan
                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN,
                ]);

                // TODO: Send email notification with invoice
                // Mail::to($pengajuan->studyProgram->email)->send(new InvoicePembayaran($pembayaran));

                $sent++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Invoice berhasil dikirim ke {$sent} program studi.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal mengirim invoice: ' . $e->getMessage());
        }
    }

    /**
     * Validasi pembayaran (approve/reject)
     */
    public function validasi(Request $request, $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject,upload_ulang',
            'catatan_verifikasi' => 'nullable|string',
            'alasan_penolakan' => 'required_if:action,reject|string',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran = PengajuanPembayaran::with('pengajuan')->findOrFail($id);

            // Validasi status pembayaran harus 'menunggu_verifikasi'
            if ($pembayaran->status_pembayaran !== 'menunggu_verifikasi') {
                return redirect()->back()->with('error', 'Pembayaran tidak dalam status menunggu validasi.');
            }

            if ($validated['action'] === 'approve') {
                // Approve pembayaran
                $pembayaran->update([
                    'status_pembayaran' => 'terverifikasi',
                    'tanggal_verifikasi' => now(),
                    'verified_by' => auth()->id(),
                    'catatan_verifikasi' => $validated['catatan_verifikasi'],
                ]);

                // Update status pengajuan
                $pembayaran->pengajuan->updateStatusSafely(
                    PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                    'Pembayaran telah divalidasi oleh bagian keuangan'
                );

                $message = 'Pembayaran berhasil divalidasi.';
            } elseif ($validated['action'] === 'upload_ulang') {
                // Request upload ulang
                $pembayaran->update([
                    'status_pembayaran' => 'upload_ulang',
                    'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? 'Mohon upload ulang bukti pembayaran yang lebih jelas.',
                    'verified_by' => auth()->id(),
                ]);

                $message = 'Permintaan upload ulang berhasil dikirim.';

                // TODO: Send email notification
            } else {
                // Reject pembayaran
                $pembayaran->update([
                    'status_pembayaran' => 'ditolak',
                    'tanggal_verifikasi' => now(),
                    'verified_by' => auth()->id(),
                    'alasan_penolakan' => $validated['alasan_penolakan'],
                    'catatan_verifikasi' => $validated['catatan_verifikasi'],
                ]);

                // Kembalikan status ke menunggu pembayaran
                $pembayaran->pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN,
                ]);

                $message = 'Pembayaran ditolak.';

                // TODO: Send email notification
            }

            DB::commit();
            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memvalidasi pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        $row = PengajuanPembayaran::query()
            ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status_pembayaran = ? THEN 1 ELSE 0 END) as menunggu_pembayaran,
            SUM(CASE WHEN status_pembayaran = ? THEN 1 ELSE 0 END) as menunggu_verifikasi,
            SUM(CASE WHEN status_pembayaran = ? THEN 1 ELSE 0 END) as terverifikasi,
            SUM(CASE WHEN status_pembayaran = ? THEN 1 ELSE 0 END) as ditolak,
            SUM(CASE WHEN status_pembayaran = ? THEN 1 ELSE 0 END) as upload_ulang,
            COALESCE(SUM(CASE WHEN status_pembayaran = ? THEN jumlah_pembayaran ELSE 0 END), 0) as total_nominal
        ', [
                'menunggu_pembayaran',
                'menunggu_verifikasi',
                'terverifikasi',
                'ditolak',
                'upload_ulang',
                'terverifikasi',
            ])
            ->first();

        return [
            'total' => (int) $row->total,
            'menunggu_pembayaran' => (int) $row->menunggu_pembayaran,
            'menunggu_verifikasi' => (int) $row->menunggu_verifikasi,
            'terverifikasi' => (int) $row->terverifikasi,
            'ditolak' => (int) $row->ditolak,
            'upload_ulang' => (int) $row->upload_ulang,
            'total_nominal' => (float) $row->total_nominal,
        ];
    }
}
