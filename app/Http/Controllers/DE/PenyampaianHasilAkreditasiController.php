<?php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Jobs\SelesaikanMasaSanggahJob;
use App\Mail\Reminder\ReminderContextMail;
use App\Models\AsesmenDocument;
use App\Models\HasilAkreditasi;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Repositories\SyaratAkreditasiRepository;
use App\Services\HasilAkreditasiService;
use App\Services\MailDeliveryService;
use App\Services\RecipientResolverService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PenyampaianHasilAkreditasiController extends Controller
{
    public function __construct(
        private readonly HasilAkreditasiService      $hasilService,
        private readonly SyaratAkreditasiRepository  $syaratRepo,
        private readonly RecipientResolverService   $recipientResolver,
        private readonly MailDeliveryService         $mailDelivery,
    ) {}

    // =========================================================
    // INDEX
    // =========================================================

    public function index(Request $request)
    {
        $scopeStatuses = [
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIHITUNG,
            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
            PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
            PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
        ];

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'asesmen.asesmenLapangan',
            'asesmen.hasil' => function ($q) {
                // $q->select('id', 'id_pengajuan', 'id_asesmen', 'skor_al', 'skor_final', 'peringkat_akreditasi', 'status', 'tanggal_finalisasi_al');
            },
            'statusLog' => function ($q) use ($scopeStatuses) {
                $q->whereIn('status_to', $scopeStatuses)->orderByDesc('created_at');
            },
        ])
            ->whereExists(function ($q) use ($scopeStatuses) {
                $q->selectRaw('1')
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $scopeStatuses);
            })
            ->whereHas('asesmen.asesmenLapangan', fn($q) => $q->where('status', 'finalized'));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('peringkat')) {
            $query->whereHas('asesmen.hasil', fn($q) => $q->where('peringkat_akreditasi_hasil', $request->peringkat));
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
        $stats      = $this->calculateStatistics($scopeStatuses);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html'    => view('de.penyampaian-hasil-akreditasi.components.table-content', compact('pengajuans'))->render(),
                'total'   => $pengajuans->total(),
            ]);
        }

        return view('de.penyampaian-hasil-akreditasi.index', compact('pengajuans', 'stats'));
    }

    // =========================================================
    // SHOW
    // =========================================================

    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'asesmen.asesmenKecukupan',
            'asesmen.asesmenLapangan.asesors.user',
            'asesmen.penilaianElemenAl.elemenStandar.kriteria',
        ])->findOrFail($id);

        $asesmen = $pengajuan->asesmen;

        if (
            !$asesmen || !$asesmen->asesmenLapangan
            || !in_array($asesmen->asesmenLapangan->status, ['completed', 'finalized'])
        ) {
            return back()->with('error', 'Asesmen Lapangan belum selesai.');
        }

        $hasil = HasilAkreditasi::initializeHasil($this->hasilService, $pengajuan, auth()->id());

        $hasil->load(['studyProgram', 'category', 'finalizedAlBy']);

        $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
            ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
            ->where('is_active', true)
            ->latest()
            ->first();

        // $canFinalize       = $beritaAcara !== null && !$hasil->isAlFinalized();
        $dokumenHasil = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->whereIn('jenis_dokumen', ['sertifikat'])
            ->where('is_latest', true)
            ->get();

        $sertifikat = $dokumenHasil->firstWhere('jenis_dokumen', 'sertifikat');
        $resume = $hasil->getDraftResumeAsesmenOrDefault();
        $resumeSaved = $hasil->hasResumeAsesmen();
        $canFinalize = $beritaAcara !== null && $sertifikat !== null && $resumeSaved && !$hasil->isAlFinalized();
        $validationSummary = $this->hasilService->getValidationSummary($hasil, 'hasil');

        $detailSkorAL = $hasil->detail_skor_al ?? [];
        $kriteriaList = $hasil->getKriteriaOrderedList();
        $elemenList   = $detailSkorAL['elemen']   ?? [];
        // Untuk kartu keterangan batasan skor — dari syarat_akreditasi kelompok rentang_skor
        $rentangSkor = $this->syaratRepo->getRentangSkor();
        $syaratKualitatif = $this->syaratRepo->getSyaratKualitatif();
        return view('de.penyampaian-hasil-akreditasi.show', compact(
            'pengajuan',
            'asesmen',
            'hasil',
            'validationSummary',
            'kriteriaList',
            'elemenList',
            'beritaAcara',
            'sertifikat',
            'resume',
            'resumeSaved',
            'canFinalize',
            'rentangSkor',
            'syaratKualitatif'
        ));
    }

    public function saveResume(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $charLimit = HasilAkreditasi::resumeBabCharLimit();

        $request->validate([
            'bab'           => 'required|array|min:1|max:20',
            'bab.*.title'   => 'required|string|max:120',
            'bab.*.content' => [
                'nullable',
                'string',
                new \App\Rules\MaxPlainTextLength($charLimit),
            ],

            'meta.masa_berlaku_tahun' => 'nullable|integer|min:1|max:10',
            'meta.nomor_sertifikat'   => 'nullable|string|max:100',
            'meta.tanggal_sertifikat' => 'nullable|date',
            'meta.keterangan'         => 'nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        $allowed = [
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIHITUNG,
            PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
        ];

        if (!in_array($pengajuan->status, $allowed)) {
            return response()->json([
                'ok' => false,
                'message' => 'Status tidak mengizinkan perubahan resume.',
            ], 422);
        }

        $hasil = $pengajuan->asesmen?->hasil;

        if (!$hasil) {
            return response()->json([
                'ok' => false,
                'message' => 'Data hasil akreditasi tidak ditemukan.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $allowedTags = '<p><br><strong><em><u><ol><ul><li><h3><h4><blockquote>';

            $bab = [];

            foreach ($request->input('bab', []) as $item) {
                $bab[] = [
                    'title' => trim($item['title'] ?? ''),
                    'content' => !empty($item['content'])
                        ? strip_tags($item['content'], $allowedTags)
                        : null,
                ];
            }

            $hasil->saveResumeAsesmen(['bab' => $bab], auth()->id());

            $meta = $request->input('meta', []);

            $updateData = [];

            if (!empty($meta['masa_berlaku_tahun'])) {
                $updateData['masa_berlaku_tahun'] = (int) $meta['masa_berlaku_tahun'];
            }

            if (!empty($meta['nomor_sertifikat'])) {
                $updateData['nomor_sertifikat'] = $meta['nomor_sertifikat'];
            }

            if (!empty($meta['tanggal_sertifikat'])) {
                $updateData['tanggal_sertifikat'] = $meta['tanggal_sertifikat'];
            }

            if (!empty($meta['keterangan'])) {
                $updateData['keterangan_pelaporan'] = $meta['keterangan'];
            }

            if (!empty($updateData)) {
                $pengajuan->update($updateData);
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Draft resume asesmen berhasil disimpan.',
                'saved_at' => now()->locale('id')->translatedFormat('d M Y, H:i'),
                'has_resume' => $hasil->fresh()->hasResumeAsesmen(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan resume: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadSertifikat(Request $request, int $id)
    {
        $request->validate([
            'file_sertifikat' => 'required|file|mimes:pdf|max:5120',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        $hasil = $pengajuan->asesmen?->hasil;

        if (!$hasil || !$hasil->hasResumeAsesmen()) {
            return back()->with('error', 'Resume asesmen harus disimpan terlebih dahulu sebelum upload sertifikat.');
        }

        DB::beginTransaction();

        $uploadedPath = null;

        try {
            $file = $request->file('file_sertifikat');

            $filename = 'sertifikat_' . $pengajuan->nomor_pengajuan . '_' . time() . '.pdf';

            $path = $file->storeAs(
                'pengajuan_dokumen/sertifikat',
                $filename,
                'public'
            );

            $uploadedPath = $path;

            PengajuanDokumen::where('id_pengajuan', $id)
                ->where('jenis_dokumen', 'sertifikat')
                ->update(['is_latest' => false]);

            PengajuanDokumen::create([
                'id_pengajuan' => $id,
                'jenis_dokumen' => 'sertifikat',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'keterangan' => $request->keterangan,
                'versi' => (PengajuanDokumen::where('id_pengajuan', $id)
                    ->where('jenis_dokumen', 'sertifikat')
                    ->max('versi') ?? 0) + 1,
                'is_latest' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', 'Sertifikat berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($uploadedPath && Storage::disk('public')->exists($uploadedPath)) {
                Storage::disk('public')->delete($uploadedPath);
            }

            return back()->with('error', 'Gagal upload sertifikat: ' . $e->getMessage());
        }
    }

    // =========================================================
    // CALCULATE (recalculate manual)
    // =========================================================

    public function calculate($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen   = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            if (
                !$asesmen->asesmenLapangan
                || !in_array($asesmen->asesmenLapangan->status, ['completed', 'finalized'])
            ) {
                throw new \Exception('Asesmen Lapangan belum selesai.');
            }

            $authId = auth()->id();
            $this->hasilService->saveHasilAK($asesmen, $authId);
            $hasil = $this->hasilService->saveHasilAL($asesmen, $authId);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', "Perhitungan berhasil diperbarui. Skor AL: {$hasil->skor_al}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recalculate hasil failed', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menghitung ulang: ' . $e->getMessage());
        }
    }

    // =========================================================
    // FINALIZE
    // =========================================================

    public function finalize(Request $request, $id)
    {
        $request->validate([
            'tanggal_masa_sanggah_selesai' => [
                'required',
                'date',
                (new \Illuminate\Validation\Rules\Date)->after(now()->addMinute(2)),
            ],
        ], [
            'tanggal_masa_sanggah_selesai.after' => 'Tanggal masa sanggah minimal 2 menit dari waktu server.',
        ]);

        DB::beginTransaction();
        try {
            $authId    = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen   = $pengajuan->asesmen;

            if (!AsesmenDocument::hasBeritaAcaraPenyampaianHasil($asesmen->id)) {
                throw new \Exception('Berita Acara Rapat Penyampaian Hasil harus diupload terlebih dahulu.');
            }

            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();
            if ($hasil->isAlFinalized()) {
                throw new \Exception('Hasil AL telah difinalisasi sebelumnya.');
            }
            $hasSertifikat = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'sertifikat')
                ->where('is_latest', true)
                ->exists();

            if (!$hasSertifikat) {
                throw new \Exception('Sertifikat akreditasi harus diupload terlebih dahulu.');
            }

            if (!$hasil->hasResumeAsesmen()) {
                throw new \Exception('Resume asesmen akreditasi harus diisi terlebih dahulu.');
            }

            // Parse datetime-local (format: Y-m-d\TH:i)
            $endAt = Carbon::parse($request->tanggal_masa_sanggah_selesai);

            // Recalculate sebelum lock
            $this->hasilService->saveHasilAK($asesmen, $authId);
            $this->hasilService->saveHasilAL($asesmen, $authId);
            $hasil->refresh();

            if (!$hasil->isAkFinalized()) {
                $this->hasilService->finalizeHasilAK($hasil, $authId);
                $hasil->refresh();
            }

            $this->hasilService->finalizeHasilAL($hasil, $authId);
            $hasil->finalizeResumeAsesmen($authId);
            $hasil->refresh();

            // Update status pengajuan + simpan deadline masa sanggah
            $this->updateStatusPengajuan($pengajuan, $hasil, $authId, $endAt);

            // Jadwalkan penyelesaian masa sanggah sesuai pilihan user
            $mode = config('akreditasi.masa_sanggah_mode', 'delay');

            if ($mode === 'delay') {
                // Model A: delayed job sampai endAt
                SelesaikanMasaSanggahJob::dispatch($pengajuan->id, 'delay')->delay($endAt);
            } else {
                // Model B: sweep - jangan delay panjang
                // Deadline disimpan di DB; scheduler yang akan “men-trigger” penyelesaian
                // (Opsional) kamu bisa dispatch job immediate untuk cek cepat, tapi tidak wajib:
                // SelesaikanMasaSanggahJob::dispatch($pengajuan->id, 'sweep');
            }

            DB::commit();

            $this->notifikasiUPPSHasilDisampaikan($pengajuan, $hasil, $endAt);

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', "Hasil akreditasi berhasil difinalisasi! Masa sanggah akan berakhir pada {$endAt->format('d-m-Y H:i:s')}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalize hasil failed', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal finalisasi: ' . $e->getMessage());
        }
    }

    public function downloadDokumen($id, $jenisDokumen)
    {
        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', $jenisDokumen)
            ->where('is_latest', true)
            ->latest()
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $dokumen->path_file,
            $dokumen->original_filename ?? $dokumen->nama_file
        );
    }

    public function previewSertifikat($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.hasil.statusFinal',
                'asesmen.hasil.statusAl',
                'asesmen.hasil.statusAk',
            ])->findOrFail($id);

            $hasil       = $pengajuan->asesmen->hasil;
            $tanggalPenetapan = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;
            $masaBerlaku = $this->calculateMasaBerlaku($hasil, $tanggalPenetapan, $pengajuan->masa_berlaku_tahun);
            $elemenList  = ($hasil->detail_skor_al ?? [])['elemen'] ?? [];

            // Ambil resume dari DB; normalisasi ke struktur array bab
            $resumeRaw = $hasil
                ? $hasil->getResumeAsesmenOrDefault()
                : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

            // Pastikan key 'bab' selalu array
            $resume = $resumeRaw;
            if (!isset($resume['bab']) || !is_array($resume['bab'])) {
                $resume['bab'] = [];
            }

            $data = [
                'pengajuan'        => $pengajuan,
                'hasil'            => $hasil,
                'studyProgram'     => $pengajuan->studyProgram,
                'university'       => $pengajuan->studyProgram->university,
                'nomorSertifikat'  => $pengajuan->nomor_sertifikat ?? $pengajuan->generateNomorSertifikat(),
                'tanggalPenetapan' => $tanggalPenetapan,
                'masaBerlaku'      => $masaBerlaku,
                'elemenList'       => $elemenList,
                'resume'           => $resume,
            ];
            return view('de.penyampaian-hasil-akreditasi.sertifikat-pdf', $data);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal preview sertifikat: ' . $e->getMessage());
        }
    }

    public function previewSertifikat2($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.hasil.statusFinal',
                'asesmen.hasil.statusAl',
                'asesmen.hasil.statusAk',
            ])->findOrFail($id);

            $hasil       = $pengajuan->asesmen->hasil;
            $tanggalPenetapan = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;
            $masaBerlaku = $this->calculateMasaBerlaku($hasil, $tanggalPenetapan, $pengajuan->masa_berlaku_tahun);
            $elemenList  = ($hasil->detail_skor_al ?? [])['elemen'] ?? [];

            // Ambil resume dari DB; normalisasi ke struktur array bab
            $resumeRaw = $hasil
                ? $hasil->getResumeAsesmenOrDefault()
                : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

            // Pastikan key 'bab' selalu array
            $resume = $resumeRaw;
            if (!isset($resume['bab']) || !is_array($resume['bab'])) {
                $resume['bab'] = [];
            }

            $data = [
                'pengajuan'        => $pengajuan,
                'hasil'            => $hasil,
                'studyProgram'     => $pengajuan->studyProgram,
                'university'       => $pengajuan->studyProgram->university,
                'nomorSertifikat'  => $pengajuan->nomor_sertifikat ?? $pengajuan->generateNomorSertifikat(),
                'tanggalPenetapan' => $tanggalPenetapan,
                'masaBerlaku'      => $masaBerlaku,
                'elemenList'       => $elemenList,
                'resume'           => $resume,
            ];
            return view('de.penyampaian-hasil-akreditasi.sertifikat-pdf2', $data);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal preview sertifikat: ' . $e->getMessage());
        }
    }

    public function previewSertifikat3($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.hasil.statusFinal',
                'asesmen.hasil.statusAl',
                'asesmen.hasil.statusAk',
            ])->findOrFail($id);

            $hasil       = $pengajuan->asesmen->hasil;
            $tanggalPenetapan = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;
            $masaBerlaku = $this->calculateMasaBerlaku($hasil, $tanggalPenetapan, $pengajuan->masa_berlaku_tahun);
            $elemenList  = ($hasil->detail_skor_al ?? [])['elemen'] ?? [];

            // Ambil resume dari DB; normalisasi ke struktur array bab
            $resumeRaw = $hasil
                ? $hasil->getResumeAsesmenOrDefault()
                : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

            // Pastikan key 'bab' selalu array
            $resume = $resumeRaw;
            if (!isset($resume['bab']) || !is_array($resume['bab'])) {
                $resume['bab'] = [];
            }

            $data = [
                'pengajuan'        => $pengajuan,
                'hasil'            => $hasil,
                'studyProgram'     => $pengajuan->studyProgram,
                'university'       => $pengajuan->studyProgram->university,
                'nomorSertifikat'  => $pengajuan->nomor_sertifikat ?? $pengajuan->generateNomorSertifikat(),
                'tanggalPenetapan' => $tanggalPenetapan,
                'masaBerlaku'      => $masaBerlaku,
                'elemenList'       => $elemenList,
                'resume'           => $resume,
            ];
            return view('de.penyampaian-hasil-akreditasi.sertifikat-pdf3', $data);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal preview sertifikat: ' . $e->getMessage());
        }
    }

    public function previewSertifikat4($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.hasil.statusFinal',
                'asesmen.hasil.statusAl',
                'asesmen.hasil.statusAk',
            ])->findOrFail($id);

            $hasil       = $pengajuan->asesmen->hasil;
            $tanggalPenetapan = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;
            $masaBerlaku = $this->calculateMasaBerlaku($hasil, $tanggalPenetapan, $pengajuan->masa_berlaku_tahun);
            $elemenList  = ($hasil->detail_skor_al ?? [])['elemen'] ?? [];

            // Ambil resume dari DB; normalisasi ke struktur array bab
            $resumeRaw = $hasil
                ? $hasil->getResumeAsesmenOrDefault()
                : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

            // Pastikan key 'bab' selalu array
            $resume = $resumeRaw;
            if (!isset($resume['bab']) || !is_array($resume['bab'])) {
                $resume['bab'] = [];
            }

            $data = [
                'pengajuan'        => $pengajuan,
                'hasil'            => $hasil,
                'studyProgram'     => $pengajuan->studyProgram,
                'university'       => $pengajuan->studyProgram->university,
                'nomorSertifikat'  => $pengajuan->nomor_sertifikat ?? $pengajuan->generateNomorSertifikat(),
                'tanggalPenetapan' => $tanggalPenetapan,
                'masaBerlaku'      => $masaBerlaku,
                'elemenList'       => $elemenList,
                'resume'           => $resume,
            ];
            return view('de.penyampaian-hasil-akreditasi.sertifikat-pdf4', $data);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal preview sertifikat: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Calculate Masa Berlaku Sertifikat
     */
    private function calculateMasaBerlaku($hasil, $tanggalPenetapan, $masaBerlakuTahun = null): array
    {
        $siklus = $masaBerlakuTahun ?? $hasil?->statusFinal?->siklus_tahun
            ?? $hasil?->statusAl?->siklus_tahun
            ?? $hasil?->statusAk?->siklus_tahun
            ?? 1; // fallback jika relasi null

        $tanggalMulai    = \Carbon\Carbon::parse($tanggalPenetapan);
        $tanggalBerakhir = $tanggalMulai->copy()->addYears($siklus);

        return [
            'tahun'            => $siklus,
            'tanggal_mulai'    => $tanggalMulai,
            'tanggal_berakhir' => $tanggalBerakhir,
        ];
    }

    private function notifikasiUPPSHasilDisampaikan(
        PengajuanAkreditasi $pengajuan,
        HasilAkreditasi $hasil,
        Carbon $endAt,
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
                Log::warning('Notifikasi penyampaian hasil tidak dikirim: tidak ada email UPPS.', [
                    'pengajuan_id' => $pengajuan->id,
                ]);
                return;
            }

            $peringkat       = $hasil->peringkat_akreditasi_hasil ?? '-';
            $tanggalSanggah  = $endAt->locale('id')->translatedFormat('d F Y H:i');

            $this->mailDelivery->sendToEmails(
                $emails,
                new ReminderContextMail(
                    recipientName: 'Tim Akreditasi Program Studi <strong>' . $namaProdi . '</strong>',
                    pesanReminder: "Hasil Asesmen Lapangan (AL) untuk program studi Anda telah disampaikan oleh LAMDEPILAR.\n\Status akreditasi program studi: {$peringkat}\n\nMasa sanggah atas hasil ini akan berlangsung hingga:\n{$tanggalSanggah}\n\Program studi dapat mengajukan banding jika memiliki keberatan terhadap hasil akreditasi.",
                    subject: 'Hasil Asesmen Lapangan Telah Disampaikan',
                    actionUrl: route('upps.penyampaian-hasil.show', $pengajuan->id),
                    actionLabel: 'Lihat Hasil Akreditasi',
                    contextInfo: null,
                    headerTitle: 'Hasil Asesmen Lapangan Telah Disampaikan',
                    preheader: "Hasil AL program studi Anda telah disampaikan",
                ),
                [],
                [],
                true
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim notifikasi penyampaian hasil ke UPPS', [
                'pengajuan_id' => $pengajuan->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    // =========================================================
    // DOWNLOAD SUMMARY
    // =========================================================

    public function downloadSummary($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'asesmen.hasil',
            ])->findOrFail($id);

            $hasil = $pengajuan->asesmen?->hasil;

            if (!$hasil || !$hasil->isAlFinalized()) {
                return back()->with('error', 'Hasil belum difinalisasi.');
            }

            // TODO: Generate PDF menggunakan DomPDF atau Snappy
            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('info', 'Fitur download PDF sedang dalam pengembangan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // BERITA ACARA
    // =========================================================

    public function uploadBeritaAcara(Request $request, $id)
    {
        $request->validate([
            'berita_acara' => 'required|file|mimes:pdf|max:10240',
            'keterangan'   => 'nullable|string|max:500',
        ], [
            'berita_acara.required' => 'File Berita Acara harus diupload.',
            'berita_acara.mimes'    => 'File harus berformat PDF.',
            'berita_acara.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen   = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            $file       = $request->file('berita_acara');
            $fileName   = 'berita_acara_penyampaian_hasil_' . time() . '.' . $file->getClientOriginalExtension();
            $path       = $file->storeAs(
                "asesmen_documents/{$asesmen->id}/berita_acara",
                $fileName,
                'public'
            );

            // Non-aktifkan BA sebelumnya
            AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
                ->update(['is_active' => false]);

            AsesmenDocument::create([
                'id_asesmen'    => $asesmen->id,
                'type'          => AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL,
                'title'         => 'Berita Acara Rapat Penyampaian Hasil Akreditasi',
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'size'          => $file->getSize(),
                'mime'          => $file->getMimeType(),
                'uploaded_by'   => auth()->id(),
                'uploaded_at'   => now(),
                'keterangan'    => $request->keterangan,
                'is_active'     => true,
                'version'       => 1,
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil diupload! Mohon segera lakukan finalisasi apabila telah sesuai');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload berita acara failed', [
                'pengajuan_id' => $id,
                'error'        => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    public function downloadBeritaAcara($id)
    {
        try {
            $pengajuan   = PengajuanAkreditasi::findOrFail($id);
            $beritaAcara = AsesmenDocument::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->firstOrFail();

            if (!Storage::disk('public')->exists($beritaAcara->path)) {
                throw new \Exception('File tidak ditemukan di storage.');
            }

            return Storage::disk('public')->download($beritaAcara->path, $beritaAcara->original_name);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal download: ' . $e->getMessage());
        }
    }

    public function deleteBeritaAcara($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan   = PengajuanAkreditasi::findOrFail($id);
            $beritaAcara = AsesmenDocument::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->firstOrFail();

            if (Storage::disk('public')->exists($beritaAcara->path)) {
                Storage::disk('public')->delete($beritaAcara->path);
            }

            $beritaAcara->update(['is_active' => false]);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function calculateStatistics(array $scopeStatuses): array
    {
        $base = PengajuanAkreditasi::query()
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $scopeStatuses))
            ->whereHas('asesmen.asesmenLapangan', fn($q) => $q->where('status', 'finalized'));

        $total      = (clone $base)->count();
        $sudahFinal = (clone $base)->whereHas('asesmen.hasil', fn($q) => $q->whereNotNull('tanggal_finalisasi_al'))->count();

        return [
            'total'       => $total,
            'sudah_final' => $sudahFinal,
            'belum_final' => $total - $sudahFinal,
        ];
    }

    /**
     * Transisi status pengajuan: hasil dikirim → masa sanggah dimulai.
     * Dipisah ke method sendiri agar finalize() tidak terlalu panjang.
     */
    private function updateStatusPengajuan(PengajuanAkreditasi $pengajuan, $hasil, int $authId, \Carbon\Carbon $endAt): void
    {
        $statusFrom = $pengajuan->status;

        $pengajuan->checkUpdateStatusAKAL('al', 'status_hasil_akreditasi_disampaikan', [
            'peringkat_hasil' => $hasil->peringkat_akreditasi_hasil,
            'skor_hasil'      => $hasil->skor_hasil ?? $hasil->skor_al, // sesuaikan kebijakan skornya
        ]);

        $pengajuan->statusLog()->firstOrCreate(
            [
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
            ],
            [
                'changed_by' => $authId,
                'keterangan' => 'Hasil akreditasi telah disampaikan ke prodi.',
                'changed_at' => now(),
            ]
        );

        $statusFrom = $pengajuan->fresh()->status;

        $pengajuan->checkUpdateStatusAKAL('al', 'status_masa_sanggah_dimulai', [
            'tanggal_masa_sanggah_selesai' => $endAt,
        ]);

        $pengajuan->statusLog()->firstOrCreate(
            [
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
            ],
            [
                'changed_by' => $authId,
                'keterangan' => 'Masa sanggah hasil akreditasi dimulai.',
                'changed_at' => now(),
            ]
        );
    }
}
