<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DE\Concerns\HasReminderPembayaran;
use App\Mail\PenerimaanBandingMail;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\University;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PermohonanBandingController extends Controller
{
    use AuthorizesRequests, HasReminderPembayaran;

    private RecipientResolverService $recipientResolver;
    private MailDeliveryService $mailDelivery;

    public function __construct(
        RecipientResolverService $recipientResolver,
        MailDeliveryService $mailDelivery,
    ) {
        $this->recipientResolver = $recipientResolver;
        $this->mailDelivery      = $mailDelivery;
    }

    // ============================================================
    // INDEX
    // ============================================================

    public function index(Request $request)
    {
        $statusLogs = [
            PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
        ];
        $query = PengajuanAkreditasi::with([
            'studyProgram:id,name,full_name,id_university,id_degree_level',
            'studyProgram.university:id,name',
            'studyProgram.degreeLevel:id,name',
            'pengaju:id,name,email',
            // ✅ Load dokumen surat penerimaan banding
            'dokumen' => fn($q) => $q
                ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q
                ->whereIn('status_to', $statusLogs)
                ->orderBy('changed_at', 'desc'),
        ])
            // ✅ Basis list: pernah ada log STATUS_BANDING_DIAJUKAN
            ->whereExists(function ($q) use ($statusLogs) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $statusLogs);
            });

        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy(
                $request->get('sort_by', 'tanggal_permohonan_banding'),
                $request->get('sort_order', 'desc')
            )
            ->paginate(20)
            ->appends($request->query());

        $stats     = $this->calculateStatistics();
        $tahunList = PengajuanAkreditasi::getTahunAkreditasiList();
        $universities = University::nonExample()->pluck('name', 'id');

        return view('de.permohonan-banding.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList'
        ));
    }

    // ============================================================
    // SHOW
    // ============================================================

    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'asesmen.hasil',
            // Surat permohonan banding dari UPPS
            'dokumen' => fn($q) => $q
                ->whereIn('jenis_dokumen', [
                    'surat_permohonan_banding',
                    'surat_penerimaan_banding_de',
                ])
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q
                ->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                ])
                ->orderBy('changed_at', 'asc'),
        ])->findOrFail($id);

        // Untuk modal kirim invoice (jika belum ada invoice banding)
        $sudahAdaInvoice = (bool) $pengajuan->pembayaranBanding;

        $pendingValidasi   = $this->getPendingValidasi('banding');
        $pendingPembayaran = $this->getPendingPembayaran('banding');

        return view('de.permohonan-banding.show', compact('pengajuan', 'sudahAdaInvoice', 'pendingValidasi', 'pendingPembayaran'));
    }

    // ============================================================
    // KIRIM PENERIMAAN BANDING
    // ============================================================

    /**
     * Upload surat penerimaan banding sekaligus ubah status ke BANDING_DITERIMA.
     * Tidak ada step "tanggapi" — langsung kirim penerimaan.
     */
    public function kirim(Request $request, $id)
    {
        $request->validate([
            'file_surat_penerimaan' => 'required|file|mimes:pdf|max:5120',
            'keterangan'            => 'nullable|string|max:1000',
        ], [
            'file_surat_penerimaan.required' => 'File surat penerimaan banding wajib diupload.',
            'file_surat_penerimaan.mimes'    => 'File harus berformat PDF.',
            'file_surat_penerimaan.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // ✅ Hanya bisa kirim penerimaan jika status masih BANDING_DIAJUKAN
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN) {
            return back()->with(
                'error',
                'Penerimaan banding hanya dapat dikirim jika status pengajuan adalah "Banding Diajukan".'
            );
        }

        DB::beginTransaction();
        try {
            $file = $request->file('file_surat_penerimaan');

            // ✅ Generate filename & store
            $filename = sprintf(
                'surat_penerimaan_banding_%s_%s.%s',
                $pengajuan->nomor_pengajuan,
                time(),
                $file->getClientOriginalExtension()
            );

            $path = $file->storeAs(
                "pengajuan/{$pengajuan->id}/surat_penerimaan_banding",
                $filename,
                'public'
            );

            // ✅ Mark dokumen lama as not latest (jika ada)
            $pengajuan->dokumen()
                ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
                ->where('is_latest', true)
                ->update(['is_latest' => false]);

            // ✅ Simpan dokumen baru
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan'      => $pengajuan->id,
                'jenis_dokumen'     => 'surat_penerimaan_banding_de',
                'nama_file'         => $filename,
                'path_file'         => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size'         => $file->getSize(),
                'mime_type'         => $file->getMimeType(),
                'uploaded_by'       => auth()->id(),
                'keterangan'        => $request->keterangan ?? 'Penerimaan Permohonan Banding dari LAMDEPILAR',
                'is_latest'         => true,
                'versi'             => $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
                    ->max('versi') + 1,
            ]);

            // ✅ Update status & tanggal
            $statusFrom = $pengajuan->status;
            $pengajuan->update([
                'status'                    => PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                'tanggal_penerimaan_banding' => now(),
            ]);

            // ✅ Log status
            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => 'Penerimaan permohonan banding dikirim ke PS/UPPS' .
                    ($request->keterangan ? ' — ' . $request->keterangan : '.'),
            ]);

            DB::commit();

            $this->sendNotification($pengajuan, $dokumen);

            return redirect()
                ->route('de.permohonan-banding.show', $pengajuan->id)
                ->with('success', 'Penerimaan Permohonan Banding berhasil dikirim ke program studi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('kirim penerimaan banding gagal', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);

            // Cleanup file jika sudah tersimpan tapi transaksi rollback
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim penerimaan banding: ' . $e->getMessage());
        }
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function sendNotification(PengajuanAkreditasi $pengajuan, PengajuanDokumen $dokumen): void
    {
        try {
            $pengajuan->loadMissing([
                'studyProgram.users.activeEmails',
                'studyProgram.university',
                'pengaju.activeEmails',
            ]);

            $prodUsers = $pengajuan->studyProgram?->users;
            $emails    = $prodUsers && $prodUsers->isNotEmpty()
                ? $this->recipientResolver->emailsForUsers($prodUsers)
                : $this->recipientResolver->emailsForUser($pengajuan->pengaju);

            if (empty($emails)) {
                Log::warning('Surat penerimaan banding tidak dikirim: recipient kosong.', [
                    'pengajuan_id'    => $pengajuan->id,
                    'study_program_id' => $pengajuan->studyProgram->id ?? null,
                ]);
                return;
            }

            $this->mailDelivery->sendToEmails(
                to: $emails,
                mailable: new PenerimaanBandingMail($pengajuan, $dokumen),
                useQueue: true
            );
        } catch (\Throwable $e) {
            // Tidak throw — pengiriman surat sudah berhasil, kegagalan email tidak boleh rollback
            Log::error('Gagal mengirim notifikasi penerimaan banding', [
                'pengajuan_id' => $pengajuan->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    // ============================================================
    // DOWNLOAD
    // ============================================================

    /**
     * Download surat penerimaan banding yang sudah diupload DE
     */
    public function download($id)
    {
        $pengajuanDokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
            ->where('is_latest', true)
            ->firstOrFail();
        return $pengajuanDokumen->downloadDokumen();
    }

    /**
     * Download surat permohonan banding yang diupload UPPS
     */
    public function downloadSuratPermohonan($id)
    {
        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_permohonan_banding')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return Storage::disk('public')->download(
            $dokumen->path_file,
            $dokumen->original_filename
        );
    }

    // ============================================================
    // DESTROY
    // ============================================================

    /**
     * Hapus surat penerimaan (untuk revisi / upload ulang)
     */
    public function destroy($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
            ->where('is_latest', true)
            ->firstOrFail();

        DB::transaction(function () use ($dokumen, $pengajuan) {
            if (Storage::disk('public')->exists($dokumen->path_file)) {
                Storage::disk('public')->delete($dokumen->path_file);
            }

            $dokumen->update(['is_latest' => false]);

            // Rollback status ke BANDING_DIAJUKAN
            $pengajuan->update([
                'status'                    => PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                'tanggal_penerimaan_banding' => null,
            ]);

            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                'status_to'   => PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => 'Surat penerimaan banding dihapus untuk diupload ulang.',
            ]);
        });

        return back()->with('success', 'Surat penerimaan banding berhasil dihapus. Silakan upload ulang.');
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    private function applyFilters($query, Request $request): void
    {
        $query->when(
            $request->filled('university_id'),
            fn($q) => $q->whereHas(
                'studyProgram',
                fn($sq) => $sq->where('id_university', $request->university_id)
            )
        );

        $query->when(
            $request->filled('tahun'),
            fn($q) => $q->where('tahun_akreditasi', $request->tahun)
        );

        $query->when(
            $request->filled('status_penerimaan'),
            function ($q) use ($request) {
                if ($request->status_penerimaan === 'terkirim') {
                    $q->whereHas('dokumen', fn($dq) => $dq
                        ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
                        ->where('is_latest', true));
                } elseif ($request->status_penerimaan === 'belum_terkirim') {
                    $q->whereDoesntHave('dokumen', fn($dq) => $dq
                        ->where('jenis_dokumen', 'surat_penerimaan_banding_de')
                        ->where('is_latest', true));
                }
            }
        );

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', fn($ssq) =>
                    $ssq->where('name', 'like', "%{$search}%"));
            });
        });
    }

    private function calculateStatistics(): array
    {
        $logs = DB::table('pengajuan_status_log')
            ->select('id_pengajuan', 'status_to')
            ->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
            ])
            ->get()
            ->groupBy('id_pengajuan');

        $stats = [
            'total'          => 0,
            'belum_terkirim' => 0,
            'terkirim'       => 0,
        ];

        foreach ($logs as $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            $stats['total']++;

            if (in_array(PengajuanAkreditasi::STATUS_BANDING_DITERIMA, $statuses)) {
                $stats['terkirim']++;
            } elseif (in_array(PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN, $statuses)) {
                $stats['belum_terkirim']++;
            }
        }

        return $stats;
    }
}
