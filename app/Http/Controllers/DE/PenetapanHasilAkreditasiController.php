<?php
// app/Http/Controllers/DE/PenetapanHasilAkreditasiController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Mail\Reminder\ReminderContextMail;
use App\Models\Asesmen;
use App\Models\AsesmenDocument;
use App\Models\HasilAkreditasi;
use App\Models\PengajuanAkreditasi;
use App\Repositories\SyaratAkreditasiRepository;
use App\Services\HasilAkreditasiService;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PenetapanHasilAkreditasiController extends Controller
{
    protected $hasilService;
    protected $syaratRepo;
    private RecipientResolverService     $recipientResolver;
    private MailDeliveryService          $mailDelivery;

    public function __construct(HasilAkreditasiService $hasilService, SyaratAkreditasiRepository  $syaratRepo, RecipientResolverService $recipientResolver, MailDeliveryService $mailDelivery,)
    {
        $this->hasilService = $hasilService;
        $this->syaratRepo = $syaratRepo;
        $this->recipientResolver = $recipientResolver;
        $this->mailDelivery = $mailDelivery;
    }

    /**
     * ✅ Index - List pengajuan yang siap untuk penetapan hasil
     */
    public function index(Request $request)
    {
        $scopeStatuses = [
            PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN,
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ];

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'asesmen.asesmenLapangan',
            'asesmen.hasil' => function ($q) {
                // $q->select('id', 'id_pengajuan', 'id_asesmen', 'skor_al', 'skor_final', 'peringkat_akreditasi_final', 'status', 'tanggal_finalisasi_al');
            },
            'statusLog' => function ($q) use ($scopeStatuses) {
                $q->whereIn('status_to', $scopeStatuses)->orderBy('created_at', 'desc');
            },
        ])
            ->whereExists(function ($q) use ($scopeStatuses) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $scopeStatuses);
            })
            ->whereHas('asesmen.hasil', function ($q) {
                $q->whereNotNull('tanggal_finalisasi_al');
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('peringkat')) {
            $query->whereHas('asesmen.hasil', function ($q) use ($request) {
                $q->where('peringkat_akreditasi_final', $request->peringkat);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhere('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $pengajuans = $query->latest()->paginate(15);

        $stats = $this->calculateStatistics($scopeStatuses);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('de.penetapan-hasil-akreditasi.components.table-content', compact('pengajuans'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.penetapan-hasil-akreditasi.index', compact('pengajuans', 'stats'));
    }

    private function calculateStatistics(array $scopeStatuses): array
    {
        $base = PengajuanAkreditasi::query()
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $scopeStatuses))
            ->whereHas('asesmen.hasil', fn($q) => $q->whereNotNull('tanggal_finalisasi_al'));

        $total = (clone $base)->count();

        // Sudah ditetapkan = yang sudah ada tanggal_penetapan atau status >= HASIL_DITETAPKAN
        $sudahDitetapkan = (clone $base)->where(function ($q) {
            $q->whereNotNull('tanggal_penetapan')
                ->orWhereIn('status', [
                    PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                    PengajuanAkreditasi::STATUS_SELESAI,
                ]);
        })->count();

        $belumDitetapkan = $total - $sudahDitetapkan;

        return [
            'total' => $total,
            'belum_ditetapkan' => $belumDitetapkan,
            'sudah_ditetapkan' => $sudahDitetapkan,
        ];
    }

    /**
     * ✅ Show - Detail hasil dengan auto-calculate
     */
    public function show($id)
    {
        try {

            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.asesmenKecukupan',
                'asesmen.asesmenLapangan.asesors.user',
                'asesmen.penilaianElemenAl.elemenStandar.kriteria',
            ])->findOrFail($id);

            $asesmen = $pengajuan->asesmen;

            // Validation: AL must be finalized
            $hasil = $asesmen->hasil;
            if (!$asesmen || !$hasil || !$hasil->isAlFinalized()) {
                return back()->with('error', 'Hasil AL belum difinalisasi.');
            }

            // 🚨 Jika proses banding sedang berjalan tapi AL banding belum ada
            $statusBanding = [
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_AK_BANDING_DILAPORKAN,
                PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN
            ];
            $log = $pengajuan->latestRelevantStatusLog($statusBanding);
            $sedangBanding = in_array($log->status_to, $statusBanding);

            $alBandingBelumAda = is_null($hasil->skor_al_banding);

            // kondisi utama
            if ($sedangBanding && $alBandingBelumAda) {
                return back()->with('warning', 'Penetapan belum dapat dilakukan karena proses banding masih berjalan.');
            }

            $hasil->load([
                'studyProgram',
                'category',
                'finalizedAlBy',
            ]);

            // ✅ Get berita acara penyampaian (read-only)
            $beritaAcaraPenyampaian = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->first();

            // ✅ Get berita acara penetapan
            $beritaAcaraPenetapan = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENETAPAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->first();

            $canTetapkan = $beritaAcaraPenetapan !== null;

            // Get validation summary for Unggul
            $validationSummary = $this->hasilService->getValidationSummary($hasil, 'final');

            // Parse detail skor
            $detailSkorAL = $hasil->detail_skor_al ?? [];
            $kriteriaList = $detailSkorAL['kriteria'] ?? [];
            $elemenList = $detailSkorAL['elemen'] ?? [];

            // ✅ Check if already penetapan
            $sudahDitetapkan = $pengajuan->tanggal_penetapan !== null
                || in_array($pengajuan->status, [
                    PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                    PengajuanAkreditasi::STATUS_SELESAI,
                ]);

            // Siapkan data final (boleh recalculate di sini)
            if (is_null($hasil->skor_final)) {
                $this->hasilService->saveHasilPenetapan($hasil, $authId);
                $hasil->refresh();
            }
            $rentangSkor = $this->syaratRepo->getRentangSkor();
            return view('de.penetapan-hasil-akreditasi.show', compact(
                'pengajuan',
                'asesmen',
                'hasil',
                'validationSummary',
                'kriteriaList',
                'elemenList',
                'beritaAcaraPenyampaian',
                'beritaAcaraPenetapan',
                'canTetapkan',
                'sudahDitetapkan',
                'rentangSkor'
            ));
        } catch (Exception $e) {
            Log::error($e);
            return back()->with('error', 'Gagal menetapkan hasil: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Tetapkan Hasil - Lock hasil penetapan
     */
    public function tetapkanHasil($id)
    {
        DB::beginTransaction();
        try {
            $authId    = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen   = $pengajuan->asesmen;

            // Guard: berita acara penetapan wajib ada
            if (!AsesmenDocument::hasBeritaAcaraPenetapanHasil($asesmen->id)) {
                throw new \Exception('Berita Acara Penetapan harus diupload terlebih dahulu.');
            }

            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();

            if (!$hasil->isAlFinalized()) {
                throw new \Exception('AL harus difinalisasi sebelum penetapan.');
            }
            if ($hasil->isPenetapanFinalized()) {
                throw new \Exception('Penetapan sudah dikunci sebelumnya.');
            }

            // Kunci penetapan resmi
            $this->hasilService->finalizeHasilPenetapan($hasil, $authId);
            $hasil->refresh();

            // Update status pengajuan
            $this->updateStatusPengajuanPenetapan($pengajuan, $hasil, $authId);

            DB::commit();

            $this->notifikasiUPPSHasilDitetapkan($pengajuan, $hasil);

            return redirect()
                ->route('de.penetapan-hasil-akreditasi.show', $id)
                ->with(
                    'success',
                    "Penetapan berhasil dikunci! "
                        . "Peringkat: {$hasil->peringkat_akreditasi_final} (Skor: {$hasil->skor_final})"
                );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tetapkan hasil failed', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal menetapkan hasil: ' . $e->getMessage());
        }
    }

    private function notifikasiUPPSHasilDitetapkan(
        PengajuanAkreditasi $pengajuan,
        HasilAkreditasi $hasil,
    ): void {
        try {
            $pengajuan->loadMissing([
                'studyProgram.university',
                'studyProgram.users.activeEmails',
                'pengaju.activeEmails',
            ]);

            $namaProdi = $pengajuan->studyProgram->name ?? '-';

            $prodUsers = $pengajuan->studyProgram?->users;
            $emails    = $prodUsers && $prodUsers->isNotEmpty()
                ? $this->recipientResolver->emailsForUsers($prodUsers)
                : $this->recipientResolver->emailsForUser($pengajuan->pengaju);

            if (empty($emails)) {
                Log::warning('Notifikasi penetapan hasil tidak dikirim: tidak ada email UPPS.', [
                    'pengajuan_id' => $pengajuan->id,
                ]);
                return;
            }

            $peringkat         = $hasil->peringkat_akreditasi_final ?? '-';
            $skor              = $hasil->skor_final ?? '-';
            $tanggalPenetapan  = $pengajuan->tanggal_penetapan
                ? \Carbon\Carbon::parse($pengajuan->tanggal_penetapan)->locale('id')->translatedFormat('d F Y')
                : '-';
            $tanggalKedaluwarsa = $pengajuan->tanggal_kedaluwarsa_akhir
                ? \Carbon\Carbon::parse($pengajuan->tanggal_kedaluwarsa_akhir)->locale('id')->translatedFormat('d F Y')
                : '-';

            $this->mailDelivery->sendToEmails(
                $emails,
                new ReminderContextMail(
                    recipientName: 'Tim Akreditasi Program Studi <strong>' . $namaProdi . '</strong>',
                    pesanReminder: "Hasil akreditasi program studi Anda telah ditetapkan oleh LAMDEPILAR.\n\Status Akreditasi : {$peringkat}\nTanggal Penetapan   : {$tanggalPenetapan}\n" . ($tanggalKedaluwarsa ? 'Berlaku Hingga      : {$tanggalKedaluwarsa}\n' : '') . "\nSertifikat akreditasi akan segera diterbitkan dan disampaikan kepada program studi. Mohon pantau sistem untuk informasi lebih lanjut.",
                    subject: 'Hasil Akreditasi Ditetapkan',
                    actionUrl: route('upps.penetapan-hasil-akreditasi.show', $pengajuan->id),
                    actionLabel: 'Lihat Hasil Penetapan',
                    contextInfo: null,
                    headerTitle: 'Hasil Akreditasi Ditetapkan',
                    preheader: "Hasil akreditasi {$namaProdi} telah ditetapkan. Status akreditasi: {$peringkat}.",
                ),
                [],
                [],
                true
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi penetapan hasil ke UPPS', [
                'pengajuan_id' => $pengajuan->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * ✅ Batalkan Penetapan (optional - jika diperlukan)
     */
    public function batalkanPenetapan($id)
    {
        DB::beginTransaction();
        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validate
            if (!$pengajuan->tanggal_penetapan) {
                throw new \Exception('Hasil belum ditetapkan.');
            }

            // Check if sudah diumumkan
            if (in_array($pengajuan->status, [
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])) {
                throw new \Exception('Hasil sudah diumumkan/dilaporkan, tidak dapat dibatalkan.');
            }

            $statusFrom = $pengajuan->status;

            // Revert to previous status (MASA_SANGGAH_SELESAI or BANDING_DILAPORKAN)
            $previousStatus = $pengajuan->hasBanding()
                ? PengajuanAkreditasi::STATUS_AL_BANDING_DILAPORKAN
                : PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI;

            $pengajuan->update([
                'status' => $previousStatus,
                'tanggal_penetapan' => null,
            ]);

            // Create status log
            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to' => $previousStatus,
                'changed_by' => $authId,
                'keterangan' => 'Penetapan hasil dibatalkan.',
                'changed_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('de.penetapan-hasil-akreditasi.show', $id)
                ->with('success', 'Penetapan hasil berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal batalkan penetapan: ' . $e->getMessage());
        }
    }

    private function updateStatusPengajuanPenetapan(PengajuanAkreditasi $pengajuan, $hasil, int $authId): void
    {
        // Step 1: hasil_akreditasi_ditetapkan
        $siklusTahun = $hasil->statusFinal->siklus_tahun;
        $tanggalKedaluwarsaAkhir = now()->addYear($siklusTahun);
        $statusFrom = $pengajuan->status;
        $pengajuan->update([
            'status' => PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            'tanggal_penetapan' => now(),
            'tanggal_kedaluwarsa_akhir' => $tanggalKedaluwarsaAkhir, // ✅ Update kolom ini juga
            'peringkat_final' => $hasil->peringkat_akreditasi,
            'skor_final' => $hasil->skor_final,
        ]);
        $pengajuan->statusLog()->firstOrCreate(
            [
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            ],
            [
                'changed_by' => $authId,
                'keterangan' => 'Hasil akreditasi telah ditetapkan secara resmi.',
                'changed_at' => now(),
            ]
        );
        $pengajuan->studyProgram->update(['status_kedaluwarsa' => 'Aktif', 'tanggal_kedaluwarsa' => $tanggalKedaluwarsaAkhir, 'peringkat_akreditasi_final' => $hasil->peringkat_akreditasi_final]);
    }

    /**
     * ✅ Upload Berita Acara Penetapan
     */
    public function uploadBeritaAcara(Request $request, $id)
    {
        $request->validate([
            'berita_acara' => 'required|file|mimes:pdf|max:10240', // max 10MB
            'keterangan' => 'nullable|string|max:500',
        ], [
            'berita_acara.required' => 'File Berita Acara harus diupload.',
            'berita_acara.mimes' => 'File harus berformat PDF.',
            'berita_acara.max' => 'Ukuran file maksimal 10MB.',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            $file = $request->file('berita_acara');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = 'berita_acara_penetapan_hasil_' . time() . '.' . $extension;

            // Store file
            $path = $file->storeAs(
                'asesmen_documents/' . $asesmen->id . '/berita_acara',
                $fileName,
                'public'
            );

            // Deactivate previous berita acara (if exists)
            AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENETAPAN_HASIL)
                ->update(['is_active' => false]);

            // Create new record
            $document = AsesmenDocument::create([
                'id_asesmen' => $asesmen->id,
                'type' => AsesmenDocument::TYPE_BERITA_ACARA_PENETAPAN_HASIL,
                'title' => 'Berita Acara Rapat Penetapan Hasil Akreditasi',
                'path' => $path,
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                // 'keterangan' => $request->keterangan,
                'is_active' => true,
                'version' => 1,
            ]);

            DB::commit();

            return redirect()
                ->route('de.penetapan-hasil-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil diupload! Mohon segera lakukan penetapan hasil (dengan klik tombol "Tetapkan Hasil"');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload berita acara penetapan failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Download Berita Acara Penetapan
     */
    public function downloadBeritaAcara($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENETAPAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->firstOrFail();

            if (!Storage::disk('public')->exists($beritaAcara->path)) {
                throw new \Exception('File tidak ditemukan.');
            }

            return Storage::disk('public')->download(
                $beritaAcara->path,
                $beritaAcara->original_name
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal download: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Delete Berita Acara Penetapan
     */
    public function deleteBeritaAcara($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENETAPAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->firstOrFail();

            // Delete file from storage
            if (Storage::disk('public')->exists($beritaAcara->path)) {
                Storage::disk('public')->delete($beritaAcara->path);
            }

            // Soft delete (mark as inactive)
            $beritaAcara->update(['is_active' => false]);

            DB::commit();

            return redirect()
                ->route('de.penetapan-hasil-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}
