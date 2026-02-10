<?php
// app/Http/Controllers/DE/PenetapanHasilAkreditasiController.php

namespace App\Http\Controllers\DE;

use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\HasilAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Services\HasilAkreditasiService;

class PenetapanHasilAkreditasiController extends Controller
{
    protected $hasilService;

    public function __construct(HasilAkreditasiService $hasilService)
    {
        $this->hasilService = $hasilService;
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
            PengajuanAkreditasi::STATUS_BANDING_DITUGASKAN,
            PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
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
                $q->select('id', 'id_pengajuan', 'id_asesmen', 'skor_al', 'skor_final', 'peringkat_akreditasi', 'status', 'tanggal_finalisasi_al');
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
                $q->where('peringkat_akreditasi', $request->peringkat);
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
        if (!$asesmen || !$asesmen->hasil || !$asesmen->hasil->isAlFinalized()) {
            return back()->with('error', 'Hasil AL belum difinalisasi.');
        }

        $hasil = $asesmen->hasil;

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
        $validationSummary = $this->hasilService->getValidationSummary($hasil);

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
            'sudahDitetapkan'
        ));
    }

    /**
     * ✅ Tetapkan Hasil - Lock hasil penetapan
     */
    public function tetapkanHasil($id)
    {
        DB::beginTransaction();
        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();

            // ✅ Validate: Berita Acara must be uploaded
            if (!AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENETAPAN_HASIL)
                ->where('is_active', true)
                ->exists()) {
                throw new \Exception('Berita Acara Rapat Penetapan Hasil harus diupload terlebih dahulu.');
            }

            // Check if already ditetapkan
            if ($pengajuan->tanggal_penetapan) {
                throw new \Exception('Hasil telah ditetapkan sebelumnya.');
            }

            // Check if AL finalized
            if (!$hasil->isAlFinalized()) {
                throw new \Exception('Hasil AL belum difinalisasi.');
            }

            // Update pengajuan
            $statusFrom = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                'tanggal_penetapan' => now(),
                'tanggal_hasil_ditetapkan' => now(), // ✅ Update kolom ini juga
                'peringkat_final' => $hasil->peringkat_akreditasi,
                'skor_final' => $hasil->skor_final,
            ]);

            // Create status log
            $pengajuan->statusLog()->firstOrCreate(
                [
                    'status_from' => $statusFrom,
                    'status_to' => PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                ],
                [
                    'changed_by' => $authId,
                    'keterangan' => 'Hasil akreditasi telah ditetapkan.',
                    'changed_at' => now(),
                ]
            );

            DB::commit();

            return redirect()
                ->route('de.penetapan-hasil-akreditasi.show', $id)
                ->with('success', "Hasil akreditasi berhasil ditetapkan! Peringkat: {$hasil->peringkat_akreditasi} (Skor: {$hasil->skor_final})");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Penetapan hasil failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menetapkan hasil: ' . $e->getMessage());
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
                ? PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN
                : PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI;

            $pengajuan->update([
                'status' => $previousStatus,
                'tanggal_penetapan' => null,
                'tanggal_hasil_ditetapkan' => null,
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
                ->with('success', 'Berita Acara berhasil diupload!');
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
