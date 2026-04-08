<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DE\Concerns\HasReminderPembayaran;
use App\Mail\InvoicePembayaranMail;
use App\Models\DegreeLevel;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanPembayaran;
use App\Models\University;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ValidasiPembayaranBandingController extends Controller
{
    use HasReminderPembayaran;

    // Status pelaksanaan banding yang relevan untuk pembayaran
    private const STATUS_RELEVAN = [
        PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
        PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
        PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
    ];
    private RecipientResolverService $recipientResolver;
    private MailDeliveryService $mailDelivery;

    public function __construct(
        RecipientResolverService $recipientResolver,
        MailDeliveryService $mailDelivery
    ) {
        $this->recipientResolver = $recipientResolver;
        $this->mailDelivery = $mailDelivery;
    }

    // ============================================================
    // INDEX
    // ============================================================

    public function index(Request $request)
    {
        // ✅ Query dari PengajuanPembayaran jenis banding
        //    + sertakan pengajuan yang sudah banding_diterima tapi belum ada invoice
        //    (untuk keperluan kirim invoice dari halaman ini)
        $query = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
            'verifier',
        ])->where('jenis_pembayaran', 'banding');

        // Filter status pembayaran
        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        // Filter universitas
        if ($request->filled('university_id')) {
            $query->whereHas(
                'pengajuan.studyProgram',
                fn($q) =>
                $q->where('id_university', $request->university_id)
            );
        }

        // Filter jenjang
        if ($request->filled('degree_level_id')) {
            $query->whereHas(
                'pengajuan.studyProgram',
                fn($q) =>
                $q->where('id_degree_level', $request->degree_level_id)
            );
        }

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($w) use ($search) {
                $w->where('nomor_invoice', 'like', "%{$search}%")
                    ->orWhereHas(
                        'pengajuan',
                        fn($p) =>
                        $p->where('nomor_pengajuan', 'like', "%{$search}%")
                    )
                    ->orWhereHas(
                        'pengajuan.studyProgram',
                        fn($p) =>
                        $p->where('name', 'like', "%{$search}%")
                    );
            });
        }

        $pembayarans = $query
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Pengajuan yang sudah banding_diterima tapi BELUM ada invoice banding
        // → kandidat untuk dikirimi invoice
        $pengajuanBelumInvoice = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ])
            ->where('status', PengajuanAkreditasi::STATUS_BANDING_DITERIMA)
            ->whereDoesntHave('pembayaranBanding')
            ->get();

        $stats       = $this->calculateStatistics();
        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html'    => view(
                    'de.validasi-pembayaran-banding.components.table-content',
                    compact('pembayarans')
                )->render(),
                'stats'   => $stats,
            ]);
        }

        $pendingValidasi   = $this->getPendingValidasi('banding');
        $pendingPembayaran = $this->getPendingPembayaran('banding');

        return view('de.validasi-pembayaran-banding.index', compact(
            'pembayarans',
            'stats',
            'universities',
            'degreeLevels',
            'pengajuanBelumInvoice',
            'pendingValidasi',
            'pendingPembayaran',
        ));
    }

    public function kirimReminderKeuangan(Request $request)
    {
        return $this->processKirimReminderKeuangan($request, 'banding');
    }

    public function kirimReminderUPPS(Request $request)
    {
        return $this->processKirimReminderUPPS($request, 'banding');
    }

    // ============================================================
    // SHOW
    // ============================================================

    public function show($id)
    {
        // $id = PengajuanPembayaran.id
        $pembayaran = PengajuanPembayaran::with([
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
            'pengajuan.pengaju',
            'pengajuan.statusLog' => fn($q) => $q->orderBy('changed_at', 'asc'),
            'verifier',
        ])
            ->where('jenis_pembayaran', 'banding')
            ->findOrFail($id);

        // Formulir pembayaran banding yang sudah diupload UPPS
        $dokumenFormulir = PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
            ->where('jenis_dokumen', 'formulir_pembayaran_banding')
            ->where('is_latest', true)
            ->first();

        // Surat penerimaan banding (dari DE) — konteks tambahan
        $suratPenerimaan = PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
            ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
            ->where('is_latest', true)
            ->first();

        return view('de.validasi-pembayaran-banding.show', compact(
            'pembayaran',
            'dokumenFormulir',
            'suratPenerimaan',
        ));
    }

    // ============================================================
    // KIRIM INVOICE BANDING
    // ============================================================

    /**
     * Kirim invoice banding ke satu atau beberapa PS.
     * Dipanggil dari:
     *  - Modal di index validasi-pembayaran-banding
     *  - Tombol di de/penerimaan-banding/show
     */
    public function kirimInvoice(Request $request)
    {
        $request->validate([
            'id_pengajuan' => 'required|array|min:1',
            'id_pengajuan.*' => 'exists:pengajuan_akreditasi,id',
            'jumlah_pembayaran' => 'required|numeric|min:1000000',
            'tanggal_jatuh_tempo' => 'required|date|after:today',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'id_pengajuan.required' => 'Pilih minimal satu permohonan banding.',
            'jumlah_pembayaran.required' => 'Jumlah pembayaran wajib diisi.',
            'jumlah_pembayaran.min' => 'Jumlah pembayaran minimal Rp 1.000.000.',
            'tanggal_jatuh_tempo.after' => 'Tanggal jatuh tempo harus setelah hari ini.',
        ]);

        DB::beginTransaction();

        try {
            $sent = 0;
            $skipped = [];

            foreach ($request->id_pengajuan as $pengajuanId) {
                $pengajuan = PengajuanAkreditasi::with([
                    'pembayaranBanding',
                    'pengaju.activeEmails',
                    'studyProgram.users.activeEmails',
                    'studyProgram.university',
                    'studyProgram.degreeLevel',
                ])->findOrFail($pengajuanId);

                if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DITERIMA) {
                    $skipped[] = $pengajuan->nomor_pengajuan . ' (status tidak sesuai)';
                    continue;
                }

                if ($pengajuan->pembayaranBanding) {
                    $skipped[] = $pengajuan->nomor_pengajuan . ' (invoice sudah ada)';
                    continue;
                }

                $pembayaran = PengajuanPembayaran::create([
                    'id_pengajuan' => $pengajuanId,
                    'jenis_pembayaran' => 'banding',
                    'nomor_invoice' => PengajuanPembayaran::generateNomorInvoice('BND'),
                    'jumlah_pembayaran' => $request->jumlah_pembayaran,
                    'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                    'status_pembayaran' => 'menunggu_pembayaran',
                    'keterangan' => $request->keterangan,
                    'created_by' => auth()->id(),
                ]);

                $pengajuan->update([
                    'status' => PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN_BANDING,
                ]);

                $pengajuan->statusLog()->create([
                    'status_from' => PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                    'status_to' => PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN_BANDING,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => 'Invoice pembayaran banding dikirim. Menunggu pembayaran dari PS.',
                ]);

                $templateFormulirPembayaran = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                    ->where('jenis_dokumen', 'template_formulir_pembayaran')
                    ->where('is_latest', true)
                    ->latest('id')
                    ->first();

                $this->sendInvoiceEmail($pengajuan, $pembayaran, $templateFormulirPembayaran);

                $sent++;
            }

            DB::commit();

            $msg = "Invoice banding berhasil dikirim ke {$sent} program studi.";
            if (!empty($skipped)) {
                $msg .= ' Dilewati: ' . implode(', ', $skipped);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal kirim invoice banding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Gagal mengirim invoice: ' . $e->getMessage());
        }
    }

    // ============================================================
    // VALIDASI PEMBAYARAN
    // ============================================================

    /**
     * Validasi pembayaran banding oleh Keuangan / DE.
     * Setelah terverifikasi, status PengajuanAkreditasi TIDAK berubah.
     * DE-lah yang kemudian assign assessor → banding_ditugaskan.
     */
    public function validasi(Request $request, $id)
    {
        $request->validate([
            'action'             => 'required|in:approve,upload_ulang,ditolak',
            'catatan_verifikasi' => 'required|string|min:5|max:2000',
        ], [
            'catatan_verifikasi.required' => 'Catatan validasi wajib diisi.',
            'catatan_verifikasi.min'      => 'Catatan minimal 5 karakter.',
        ]);

        DB::beginTransaction();
        try {
            $pembayaran = PengajuanPembayaran::with('pengajuan')
                ->where('jenis_pembayaran', 'banding')
                ->findOrFail($id);

            if ($pembayaran->status_pembayaran !== 'menunggu_verifikasi') {
                return back()->with('error', 'Pembayaran tidak dalam status "Menunggu Validasi".');
            }

            $action     = $request->action;
            $pengajuan  = $pembayaran->pengajuan;
            $oldStatus  = $pengajuan->status;

            // Map action → status_pembayaran baru
            $statusMap = [
                'approve'     => 'terverifikasi',
                'upload_ulang' => 'upload_ulang',
                'ditolak'     => 'ditolak',
            ];

            $newStatusPembayaran = $statusMap[$action];

            // Update invoice
            $pembayaran->status_pembayaran  = $newStatusPembayaran;
            $pembayaran->catatan_verifikasi = $request->catatan_verifikasi;
            $pembayaran->verified_by        = auth()->id();

            if (in_array('tanggal_verifikasi', $pembayaran->getFillable())) {
                $pembayaran->tanggal_verifikasi = now();
            }
            $pembayaran->save();

            // Keterangan log
            $keteranganMap = [
                'approve'      => 'Pembayaran banding divalidasi. Siap untuk penugasan assessor.',
                'upload_ulang' => 'Keuangan/DE meminta upload ulang bukti pembayaran banding.',
                'ditolak'      => 'Pembayaran banding ditolak.',
            ];

            // ✅ Status PengajuanAkreditasi TIDAK BERUBAH untuk semua action
            //    (banding_diterima tetap, DE assign assessor setelahnya secara manual)
            $pengajuan->statusLog()->create([
                'status_from' => $oldStatus,
                'status_to'   => $oldStatus, // sama
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => $keteranganMap[$action],
            ]);

            DB::commit();

            $pesanMap = [
                'approve'      => 'Pembayaran banding berhasil divalidasi.',
                'upload_ulang' => 'Permintaan upload ulang berhasil dikirim ke PS.',
                'ditolak'      => 'Pembayaran banding ditolak.',
            ];

            return redirect()
                ->route('de.validasi-pembayaran-banding.show', $id)
                ->with('success', $pesanMap[$action]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal validasi pembayaran banding', [
                'invoice_id' => $id,
                'error'      => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }

    // ============================================================
    // DOWNLOAD
    // ============================================================

    /**
     * Download formulir pembayaran banding yang sudah diupload UPPS
     */
    public function downloadFormulir($id)
    {
        $pembayaran = PengajuanPembayaran::where('jenis_pembayaran', 'banding')->findOrFail($id);

        $dokumen = PengajuanDokumen::where('id_pengajuan', $pembayaran->id_pengajuan)
            ->where('jenis_dokumen', 'formulir_pembayaran_banding')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->path_file, $dokumen->original_filename);
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function calculateStatistics(): array
    {
        $row = PengajuanPembayaran::where('jenis_pembayaran', 'banding')
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status_pembayaran = 'menunggu_pembayaran'  THEN 1 ELSE 0 END) AS menunggu_pembayaran,
                SUM(CASE WHEN status_pembayaran = 'menunggu_verifikasi'  THEN 1 ELSE 0 END) AS menunggu_verifikasi,
                SUM(CASE WHEN status_pembayaran = 'upload_ulang'         THEN 1 ELSE 0 END) AS upload_ulang,
                SUM(CASE WHEN status_pembayaran = 'terverifikasi'        THEN 1 ELSE 0 END) AS terverifikasi,
                SUM(CASE WHEN status_pembayaran = 'ditolak'              THEN 1 ELSE 0 END) AS ditolak,
                COALESCE(SUM(CASE WHEN status_pembayaran = 'terverifikasi' THEN jumlah_pembayaran ELSE 0 END), 0) AS total_nominal_lunas
            ")
            ->first();

        // Pengajuan banding_diterima yang BELUM ada invoice
        $belumInvoice = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_BANDING_DITERIMA)
            ->whereDoesntHave('pembayaranBanding')
            ->count();

        return [
            'total'               => (int)   ($row->total                 ?? 0),
            'belum_invoice'       => (int)    $belumInvoice,
            'menunggu_pembayaran' => (int)   ($row->menunggu_pembayaran   ?? 0),
            'menunggu_verifikasi' => (int)   ($row->menunggu_verifikasi   ?? 0),
            'upload_ulang'        => (int)   ($row->upload_ulang          ?? 0),
            'terverifikasi'       => (int)   ($row->terverifikasi         ?? 0),
            'ditolak'             => (int)   ($row->ditolak               ?? 0),
            'total_nominal_lunas' => (float) ($row->total_nominal_lunas   ?? 0),
        ];
    }

    private function sendInvoiceEmail(
        PengajuanAkreditasi $pengajuan,
        PengajuanPembayaran $pembayaran,
        ?PengajuanDokumen $templateFormulirPembayaran = null
    ): void {
        $pengajuan->loadMissing([
            'pengaju.activeEmails',
            'studyProgram.users.activeEmails',
            'studyProgram.university',
            'studyProgram.degreeLevel',
        ]);

        $emails = [];

        if ($pengajuan->studyProgram && $pengajuan->studyProgram->users) {
            $emails = $this->recipientResolver->emailsForUsers($pengajuan->studyProgram->users);
        }

        if (empty($emails) && $pengajuan->pengaju) {
            $emails = $this->recipientResolver->emailsForUser($pengajuan->pengaju);
        }

        if (empty($emails)) {
            Log::warning('Invoice tidak dikirim karena tidak ada email tujuan', [
                'pengajuan_id' => $pengajuan->id,
                'pembayaran_id' => $pembayaran->id,
                'jenis_pembayaran' => $pembayaran->jenis_pembayaran,
            ]);
            return;
        }

        $result = $this->mailDelivery->sendToEmails(
            to: $emails,
            mailable: new InvoicePembayaranMail($pembayaran, $templateFormulirPembayaran),
            useQueue: true
        );
    }
}
