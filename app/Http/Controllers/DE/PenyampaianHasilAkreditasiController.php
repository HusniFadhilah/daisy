<?php

namespace App\Http\Controllers\DE;

use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\HasilAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
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
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'asesmen.asesmenLapangan',
            'asesmen.hasil' => function ($q) {
                $q->select('id', 'id_pengajuan', 'id_asesmen', 'skor_al', 'skor_final', 'peringkat_akreditasi', 'status', 'tanggal_finalisasi_al');
            }
        ])
            // Filter: AL sudah selesai
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_AL_SELESAI,
                PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH,
            ])
            ->whereHas('asesmen.asesmenLapangan', function ($q) {
                $q->where('status', 'completed');
            });

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by peringkat
        if ($request->filled('peringkat')) {
            $query->whereHas('asesmen.hasil', function ($q) use ($request) {
                $q->where('peringkat_akreditasi', $request->peringkat);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $pengajuans = $query->latest()->paginate(15);

        return view('de.penyampaian-hasil-akreditasi.index', compact('pengajuans'));
    }

    /**
     * ✅ Show - Detail hasil dengan auto-calculate
     */
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

        // Validation: AL must be completed
        if (!$asesmen || !$asesmen->asesmenLapangan || $asesmen->asesmenLapangan->status !== 'completed') {
            return back()->with('error', 'Asesmen Lapangan belum selesai.');
        }

        // ✅ AUTO-CALCULATE: Check if hasil exists, if not create it
        $hasil = HasilAkreditasi::firstOrCreate(
            [
                'id_pengajuan' => $pengajuan->id,
                'id_asesmen' => $asesmen->id,
            ],
            [
                'id_study_program' => $asesmen->id_study_program,
                'id_category' => $pengajuan->studyProgram->id_category,
                'status' => 'draft_al',
            ]
        );

        // Auto-calculate if not yet calculated or still draft
        if (!$hasil->skor_al || $hasil->status === 'draft_al') {
            try {
                DB::beginTransaction();

                // Calculate AK first (if not exists)
                if (!$hasil->skor_ak) {
                    $this->hasilService->saveHasilAK($asesmen, auth()->id());
                    $hasil->refresh();
                }

                // Calculate AL
                $this->hasilService->saveHasilAL($asesmen, auth()->id());
                $hasil->refresh();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Auto-calculate failed', [
                    'pengajuan_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Load full hasil with relations
        $hasil->load([
            'studyProgram',
            'category',
            'finalizedAlBy',
        ]);

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
            'elemenList'
        ));
    }

    /**
     * ✅ Calculate - Manual recalculate
     */
    public function calculate($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            // Validate: AL must be completed
            if (!$asesmen->asesmenLapangan || $asesmen->asesmenLapangan->status !== 'completed') {
                throw new \Exception('Asesmen Lapangan belum selesai.');
            }

            // Recalculate AK
            $this->hasilService->saveHasilAK($asesmen, auth()->id());

            // Recalculate AL
            $hasil = $this->hasilService->saveHasilAL($asesmen, auth()->id());

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
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            $hasil = HasilAkreditasi::where('id_asesmen', $asesmen->id)->firstOrFail();

            // Check if already finalized
            if ($hasil->isAlFinalized()) {
                throw new \Exception('Hasil AL sudah difinalisasi sebelumnya.');
            }

            // Recalculate before finalize (ensure latest data)
            $this->hasilService->saveHasilAK($asesmen, auth()->id());
            $this->hasilService->saveHasilAL($asesmen, auth()->id());
            $hasil->refresh();

            // Finalize AK first
            if (!$hasil->isAkFinalized()) {
                $this->hasilService->finalizeHasilAK($hasil, auth()->id());
                $hasil->refresh();
            }

            // Finalize AL
            $this->hasilService->finalizeHasilAL($hasil, auth()->id());
            $hasil->refresh();

            DB::commit();

            return redirect()
                ->route('de.penyampaian-hasil-akreditasi.show', $id)
                ->with('success', "Hasil akreditasi berhasil difinalisasi! Peringkat: {$hasil->peringkat_akreditasi} (Skor: {$hasil->skor_final})");
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
}
