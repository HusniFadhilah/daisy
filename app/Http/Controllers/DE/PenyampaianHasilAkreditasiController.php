<?php

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

class PenyampaianHasilAkreditasiController extends Controller
{
    protected $hasilService;

    public function __construct(HasilAkreditasiService $hasilService)
    {
        $this->hasilService = $hasilService;
    }

    /**
     * ✅ Index - List pengajuan yang siap untuk penyampaian hasil
     */
    public function index(Request $request)
    {
        $scopeStatuses = [
            PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
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
            ->whereHas('asesmen.asesmenLapangan', function ($q) {
                $q->where('status', 'finalized');
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
            $html = view('de.penyampaian-hasil-akreditasi.components.table-content', compact('pengajuans'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'total' => $pengajuans->total(),
            ]);
        }

        return view('de.penyampaian-hasil-akreditasi.index', compact('pengajuans', 'stats'));
    }

    private function calculateStatistics(array $scopeStatuses): array
    {
        $base = PengajuanAkreditasi::query()
            ->whereHas('statusLog', fn($q) => $q->whereIn('status_to', $scopeStatuses))
            ->whereHas('asesmen.asesmenLapangan', fn($q) => $q->where('status', 'finalized'));

        $total = (clone $base)->count();

        $sudahFinal = (clone $base)->whereHas('asesmen.hasil', function ($q) {
            $q->whereNotNull('tanggal_finalisasi_al');
        })->count();

        $belumFinal = $total - $sudahFinal;

        return [
            'total' => $total,
            'belum_final' => $belumFinal,
            'sudah_final' => $sudahFinal,
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

        // Validation: AL must be completed
        if (!$asesmen || !$asesmen->asesmenLapangan || !in_array($asesmen->asesmenLapangan->status, ['completed', 'finalized'])) {
            return back()->with('error', 'Asesmen Lapangan belum selesai.');
        }

        $hasil = HasilAkreditasi::initializeHasil($this->hasilService, $pengajuan, $authId);

        $hasil->load([
            'studyProgram',
            'category',
            'finalizedAlBy',
        ]);

        // ✅ Check if berita acara exists
        $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
            ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
            ->where('is_active', true)
            ->latest()
            ->first();

        $canFinalize = $beritaAcara !== null;

        // Get validation summary for Unggul
        $validationSummary = $this->hasilService->getValidationSummary($hasil);

        // Parse detail skor
        $detailSkorAL = $hasil->detail_skor_al ?? [];
        $kriteriaList = $detailSkorAL['kriteria'] ?? [];
        $elemenList = $detailSkorAL['elemen'] ?? [];

        return view('de.penyampaian-hasil-akreditasi.show', compact(
            'pengajuan',
            'asesmen',
            'hasil',
            'validationSummary',
            'kriteriaList',
            'elemenList',
            'beritaAcara',      // ✅ NEW
            'canFinalize'       // ✅ NEW
        ));
    }

    /**
     * ✅ Calculate - Manual recalculate
     */
    public function calculate($id)
    {
        DB::beginTransaction();
        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            // Validate: AL must be completed
            if (!$asesmen->asesmenLapangan || !in_array($asesmen->asesmenLapangan->status, ['completed', 'finalized'])) {
                throw new \Exception('Asesmen Lapangan belum selesai.');
            }

            // Recalculate AK
            $this->hasilService->saveHasilAK($asesmen, $authId);

            // Recalculate AL
            $hasil = $this->hasilService->saveHasilAL($asesmen, $authId);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', "Perhitungan berhasil diperbarui. Skor AL: {$hasil->skor_al}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recalculate hasil failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghitung ulang: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Finalize - Lock hasil AL
     */
    public function finalize($id)
    {
        DB::beginTransaction();
        try {
            $authId = auth()->id();
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();

            // ✅ Validate: Berita Acara must be uploaded
            if (!AsesmenDocument::hasBeritaAcaraPenyampaianHasil($asesmen->id)) {
                throw new \Exception('Berita Acara Rapat Penyampaian Hasil harus diupload terlebih dahulu.');
            }

            // Check if already finalized
            if ($hasil->isAlFinalized()) {
                throw new \Exception('Hasil AL telah difinalisasi sebelumnya.');
            }

            // Recalculate before finalize (ensure latest data)
            $this->hasilService->saveHasilAK($asesmen, $authId);
            $this->hasilService->saveHasilAL($asesmen, $authId);
            $hasil->refresh();

            // Finalize AK first
            if (!$hasil->isAkFinalized()) {
                $this->hasilService->finalizeHasilAK($hasil, $authId);
                $hasil->refresh();
            }

            // Finalize AL
            $this->hasilService->finalizeHasilAL($hasil, $authId);
            $hasil->refresh();
            $statusFrom = $pengajuan->status;
            $pengajuan->checkUpdateStatusAKAL('al', 'status_hasil_akreditasi_disampaikan');
            $pengajuan->statusLog()->firstOrCreate(
                [
                    'status_from' => $statusFrom,
                    'status_to'   => PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                ],
                [
                    'changed_by'  => $authId,
                    'keterangan'  => 'Hasil akreditasi telah dikirim ke prodi.',
                    'changed_at'  => now(),
                ]
            );
            $statusFrom = $pengajuan->status;
            $pengajuan->checkUpdateStatusAKAL('al', 'status_masa_sanggah_dimulai');
            $pengajuan->statusLog()->firstOrCreate(
                [
                    'status_from' => $statusFrom,
                    'status_to'   => PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                ],
                [
                    'changed_by'  => $authId,
                    'keterangan'  => 'Masa sanggah hasil akreditasi telah dimulai.',
                    'changed_at'  => now(),
                ]
            );

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', "Hasil akreditasi berhasil difinalisasi dan disampaikan ke prodi! Peringkat: {$hasil->peringkat_akreditasi} (Skor: {$hasil->skor_final})");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalize hasil failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal finalisasi: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Download summary PDF
     */
    public function downloadSummary($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'asesmen.hasil'
            ])->findOrFail($id);

            $hasil = $pengajuan->asesmen->hasil;

            if (!$hasil || !$hasil->isAlFinalized()) {
                return back()->with('error', 'Hasil belum difinalisasi.');
            }

            // TODO: Generate PDF using DomPDF or similar
            // For now, redirect to show page
            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('info', 'Fitur download PDF dalam pengembangan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Upload Berita Acara
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
            $fileName = 'berita_acara_penyampaian_hasil_' . time() . '.' . $extension;

            // Store file
            $path = $file->storeAs(
                'asesmen_documents/' . $asesmen->id . '/berita_acara',
                $fileName,
                'public'
            );

            // Deactivate previous berita acara (if exists)
            AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
                ->update(['is_active' => false]);

            // Create new record
            $document = AsesmenDocument::create([
                'id_asesmen' => $asesmen->id,
                'type' => AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL,
                'title' => 'Berita Acara Rapat Penyampaian Hasil Akreditasi',
                'path' => $path,
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'keterangan' => $request->keterangan,
                'is_active' => true,
                'version' => 1,
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil diupload!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload berita acara failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Download Berita Acara
     */
    public function downloadBeritaAcara($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
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
     * ✅ Delete Berita Acara
     */
    public function deleteBeritaAcara($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
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
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }
}
