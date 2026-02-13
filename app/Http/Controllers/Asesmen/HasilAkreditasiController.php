<?php

namespace App\Http\Controllers\Asesmen;

use Carbon\Carbon;
use App\Models\Asesmen;
use Illuminate\Http\Request;
use App\Models\HasilAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Services\HasilAkreditasiService;

class HasilAkreditasiController extends Controller
{
    protected $hasilService;

    public function __construct(HasilAkreditasiService $hasilService)
    {
        $this->hasilService = $hasilService;
    }

    /**
     * ✅ Show hasil akreditasi dashboard
     */
    public function show($asesmenId)
    {
        $asesmen = Asesmen::with([
            'pengajuan.studyProgram.category',
            'pengajuan.studyProgram.university',
            'pengajuan.studyProgram.degreeLevel',
        ])->findOrFail($asesmenId);

        $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->first();

        return view('asesmen.hasil-akreditasi.show', compact('asesmen', 'hasil'));
    }

    /**
     * ✅ Calculate & save AK score
     */
    public function hitungAK($asesmenId)
    {
        DB::beginTransaction();
        try {
            $asesmen = Asesmen::findOrFail($asesmenId);

            // Validate: All asesor approved and validator approved
            $allSubmitted = $asesmen->asesorAK()
                ->where('status_pekerjaan', '!=', 'approved')
                ->doesntExist();

            if (!$allSubmitted) {
                return back()->with('error', 'Semua asesor AK harus submit penilaian terlebih dahulu.');
            }

            // Calculate
            $hasil = $this->hasilService->saveHasilAK($asesmen, auth()->id());

            DB::commit();

            return back()->with(
                'success',
                "Perhitungan AK berhasil. Skor AK: {$hasil->skor_ak}"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hitung AK failed', [
                'asesmen_id' => $asesmenId,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menghitung AK: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Finalize AK
     */
    public function finalizeAK($asesmenId)
    {
        DB::beginTransaction();
        try {
            $asesmen = Asesmen::findOrFail($asesmenId);
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->firstOrFail();

            // Re-calculate before finalize (ensure latest data)
            $this->hasilService->saveHasilAK($asesmen, auth()->id());

            // Finalize
            $hasil->refresh();
            $this->hasilService->finalizeHasilAK($hasil, auth()->id());

            DB::commit();

            return back()->with(
                'success',
                'Hasil AK berhasil difinalisasi. Status: FINAL'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalize AK failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Calculate & save AL score
     */
    public function hitungAL($asesmenId)
    {
        DB::beginTransaction();
        try {
            $asesmen = Asesmen::findOrFail($asesmenId);

            // Validate: AK must be finalized
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->first();
            if (!$hasil || !$hasil->isAkFinalized()) {
                return back()->with('error', 'AK harus difinalisasi terlebih dahulu.');
            }

            // Validate: All asesor approved
            $allSubmitted = $asesmen->asesorAL()
                ->where('status_pekerjaan', '!=', 'approved')
                ->doesntExist();

            if (!$allSubmitted) {
                return back()->with('error', 'Semua asesor AL harus submit penilaian terlebih dahulu.');
            }

            // Calculate
            $hasil = $this->hasilService->saveHasilAL($asesmen, auth()->id());

            DB::commit();

            return back()->with(
                'success',
                "Perhitungan AL berhasil. Skor AL: {$hasil->skor_al}"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hitung AL failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghitung AL: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Finalize AL & calculate combined score
     */
    public function finalizeAL($asesmenId)
    {
        DB::beginTransaction();
        try {
            $asesmen = Asesmen::findOrFail($asesmenId);
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->firstOrFail();

            // Re-calculate before finalize
            $this->hasilService->saveHasilAL($asesmen, auth()->id());

            // Finalize
            $hasil->refresh();
            $this->hasilService->finalizeHasilAL($hasil, auth()->id());

            DB::commit();

            return back()->with(
                'success',
                "Hasil AL difinalisasi. Skor Final: {$hasil->skor_final} - {$hasil->peringkat_akreditasi}"
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finalize AL failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Publish hasil to prodi (Step 14)
     */
    public function publishHasil($asesmenId)
    {
        DB::beginTransaction();
        try {
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->firstOrFail();

            if (!$hasil->isAlFinalized()) {
                return back()->with('error', 'AL harus difinalisasi terlebih dahulu.');
            }

            $this->hasilService->publishHasil($hasil);

            DB::commit();

            return redirect()
                ->route('hasil-akreditasi.form', $hasil->id_pengajuan)
                ->with('success', 'Hasil akreditasi siap disampaikan ke prodi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Publish hasil failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Download laporan hasil (PDF/Excel)
     */
    public function downloadLaporan($asesmenId, $format = 'pdf')
    {
        try {
            $hasil = HasilAkreditasi::with([
                'asesmen.studyProgram.university',
                'asesmen.studyProgram.degreeLevel',
                'category'
            ])->where('id_asesmen', $asesmenId)->firstOrFail();

            if ($format === 'pdf') {
                // TODO: Generate PDF using dompdf or similar
                return $this->generatePDF($hasil);
            } else {
                // TODO: Generate Excel using PhpSpreadsheet
                return $this->generateExcel($hasil);
            }
        } catch (\Exception $e) {
            Log::error('Download laporan failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }

    /**
     * ✅ STEP 14: Show form penyampaian hasil akreditasi
     */
    public function showFormHasilAkreditasi($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'dokumen'
        ])->findOrFail($id);

        // Validation
        if (!$pengajuan->canStartHasilAkreditasi()) {
            return back()->with(
                'error',
                'Pelaporan AL harus selesai terlebih dahulu sebelum menyampaikan hasil akreditasi.'
            );
        }

        return view('asesmen.hasil-akreditasi.form', compact('pengajuan'));
    }

    /**
     * ✅ STEP 14: Submit hasil akreditasi
     */
    public function submitHasilAkreditasi(Request $request, $id)
    {
        $request->validate([
            'peringkat_akreditasi' => 'required|in:Unggul,Baik Sekali,Baik,Tidak Terakreditasi',
            'skor_akhir' => 'required|numeric|min:0|max:400',
            'surat_hasil_akreditasi' => 'required|file|mimes:pdf|max:5120',
            'tanggal_penyampaian' => 'required|date',
            'masa_berlaku_tahun' => 'required|integer|min:1|max:5',
            'catatan_hasil' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validate transition
            if (!$pengajuan->canTransitionTo(PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM)) {
                throw new \Exception('Status Permohonan akreditasi tidak valid untuk penyampaian hasil akreditasi.');
            }

            // Upload surat hasil
            $file = $request->file('surat_hasil_akreditasi');
            $filename = 'surat_hasil_akreditasi_' . time() . '.pdf';
            $path = $file->storeAs(
                "permohonan-akreditasi/{$pengajuan->id}/hasil-akreditasi",
                $filename,
                'public'
            );

            // Save document
            $pengajuan->dokumen()->create([
                'jenis_dokumen' => 'surat_hasil_akreditasi',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'keterangan' => 'Surat Hasil Akreditasi',
                'is_latest' => true,
            ]);

            // Calculate expiry date
            $tanggalBerlaku = Carbon::parse($request->tanggal_penyampaian);
            $tanggalKedaluwarsa = $tanggalBerlaku->copy()->addYears($request->masa_berlaku_tahun);

            // Update pengajuan
            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                "Hasil akreditasi: {$request->peringkat_akreditasi} (Skor: {$request->skor_akhir})"
            );

            $pengajuan->update([
                'tanggal_hasil_akreditasi_dikirim' => $request->tanggal_penyampaian,
            ]);

            // Update study program accreditation
            $pengajuan->studyProgram->update([
                'peringkat_akreditasi' => $request->peringkat_akreditasi,
                'tanggal_kedaluwarsa' => $tanggalKedaluwarsa,
                'status_kedaluwarsa' => 'Aktif',
            ]);

            DB::commit();

            return redirect()
                ->route('pengajuan.show', $pengajuan->id)
                ->with('success', 'Hasil akreditasi berhasil disampaikan. Masa sanggah dimulai.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit hasil akreditasi failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Gagal menyampaikan hasil: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ✅ Get validation summary API
     */
    public function getValidationSummary($asesmenId)
    {
        try {
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->firstOrFail();

            $summary = $this->hasilService->getValidationSummary($hasil);

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            Log::error('Get validation summary failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Manual override peringkat (admin only - untuk kasus khusus)
     */
    public function overridePeringkat(Request $request, $asesmenId)
    {
        $request->validate([
            'peringkat' => 'required|in:Unggul,Baik Sekali,Baik,Tidak Terakreditasi',
            'alasan_override' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $hasil = HasilAkreditasi::where('id_asesmen', $asesmenId)->firstOrFail();

            if (!$hasil->isAlFinalized()) {
                return back()->with('error', 'AL harus difinalisasi terlebih dahulu.');
            }

            $oldPeringkat = $hasil->peringkat_akreditasi;

            $hasil->update([
                'peringkat_akreditasi' => $request->peringkat,
                'catatan_validasi' => ($hasil->catatan_validasi ?? '') . "\n\n" .
                    "=== MANUAL OVERRIDE ===\n" .
                    "Dari: {$oldPeringkat} → Ke: {$request->peringkat}\n" .
                    "Alasan: {$request->alasan_override}\n" .
                    "Oleh: " . auth()->user()->name . "\n" .
                    "Tanggal: " . now()->locale('id')->translatedFormat('d M Y H:i'),
            ]);

            DB::commit();

            return back()->with('success', "Status akreditasi berhasil diubah dari {$oldPeringkat} ke {$request->peringkat}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Override peringkat failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan, silahkan coba beberapa saat lagi');
        }
    }

    /**
     * ✅ STEP 15: Start masa sanggah
     */
    public function startMasaSanggah($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            if (!$pengajuan->canStartMasaSanggah()) {
                throw new \Exception('Hasil akreditasi harus disampaikan terlebih dahulu.');
            }

            // Set masa sanggah (7 days from hasil akreditasi)
            $tanggalMulai = $pengajuan->tanggal_hasil_akreditasi_dikirim;
            $tanggalSelesai = Carbon::parse($tanggalMulai)->addDays(7);

            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                "Masa sanggah dimulai: {$tanggalMulai->locale('id')->translatedFormat('d M Y')} - {$tanggalSelesai->locale('id')->translatedFormat('d M Y')}"
            );

            $pengajuan->update([
                'tanggal_masa_sanggah_mulai' => $tanggalMulai,
                'tanggal_masa_sanggah_selesai' => $tanggalSelesai,
            ]);

            DB::commit();

            return back()->with('success', 'Masa sanggah telah dimulai (7 hari).');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Start masa sanggah failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ STEP 16-17: Submit banding
     */
    public function submitBanding(Request $request, $id)
    {
        $request->validate([
            'dokumen_banding' => 'required|file|mimes:pdf|max:10240',
            'alasan_banding' => 'required|string|max:5000',
            'tanggal_pelaksanaan' => 'required|date|after:today',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Must be in masa sanggah
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI) {
                throw new \Exception('Banding hanya bisa diajukan selama masa sanggah.');
            }

            // Check masa sanggah deadline
            if (now()->gt($pengajuan->tanggal_masa_sanggah_selesai)) {
                throw new \Exception('Masa sanggah telah berakhir.');
            }

            // Upload dokumen banding
            $file = $request->file('dokumen_banding');
            $filename = 'dokumen_banding_' . time() . '.pdf';
            $path = $file->storeAs(
                "permohonan-akreditasi/{$pengajuan->id}/banding",
                $filename,
                'public'
            );

            $pengajuan->dokumen()->create([
                'jenis_dokumen' => 'dokumen_banding',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'keterangan' => 'Dokumen Permohonan Banding',
                'is_latest' => true,
            ]);

            // Update status
            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                "Banding diajukan dengan alasan: " . substr($request->alasan_banding, 0, 200)
            );

            $pengajuan->update([
                'tanggal_permohonan_banding' => now(),
                'tanggal_pelaksanaan_banding' => $request->tanggal_pelaksanaan,
            ]);

            DB::commit();

            return back()->with('success', 'Permohonan banding berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Submit banding failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * ✅ STEP 17: Laporkan banding (admin only)
     */
    public function laporkanBanding(Request $request, $id)
    {
        $request->validate([
            'laporan_banding' => 'required|file|mimes:pdf|max:10240',
            'hasil_banding' => 'required|in:diterima,ditolak',
            'peringkat_baru' => 'required_if:hasil_banding,diterima|in:Unggul,Baik Sekali,Baik,Tidak Terakreditasi',
            'catatan_hasil_banding' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN) {
                throw new \Exception('Banding belum dilaksanakan.');
            }

            // Upload laporan
            $file = $request->file('laporan_banding');
            $filename = 'laporan_banding_' . time() . '.pdf';
            $path = $file->storeAs(
                "permohonan-akreditasi/{$pengajuan->id}/banding",
                $filename,
                'public'
            );

            $pengajuan->dokumen()->create([
                'jenis_dokumen' => 'laporan_banding',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
            ]);

            // Update status
            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                "Hasil banding: {$request->hasil_banding}"
            );

            $pengajuan->update([
                'tanggal_pelaporan_banding' => now(),
            ]);

            // If banding approved, update peringkat
            if ($request->hasil_banding === 'diterima') {
                $pengajuan->studyProgram->update([
                    'peringkat_akreditasi' => $request->peringkat_baru,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Laporan banding berhasil disubmit.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Laporkan banding failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * ✅ STEP 18: Tetapkan hasil (skip banding atau after banding)
     */
    public function tetapkanHasil($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validate: either masa sanggah ended OR banding reported
            $canProceed = (
                $pengajuan->status === PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI &&
                now()->gt($pengajuan->tanggal_masa_sanggah_selesai)
            ) || (
                $pengajuan->status === PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN
            );

            if (!$canProceed) {
                throw new \Exception('Hasil belum bisa ditetapkan. Tunggu masa sanggah selesai atau banding dilaporkan.');
            }

            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                'Hasil akreditasi ditetapkan secara resmi'
            );

            $pengajuan->update([
                'tanggal_penetapan' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Hasil akreditasi berhasil ditetapkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tetapkan hasil failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ STEP 19: Umumkan hasil
     */
    public function umumkanHasil($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            if (!$pengajuan->canTransitionTo(PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN)) {
                throw new \Exception('Hasil harus ditetapkan terlebih dahulu.');
            }

            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                'Hasil akreditasi diumumkan kepada publik'
            );

            $pengajuan->update([
                'tanggal_pengumuman' => now(),
            ]);

            DB::commit();

            // TODO: Send notification to prodi
            // TODO: Publish to public portal

            return back()->with('success', 'Hasil akreditasi berhasil diumumkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Umumkan hasil failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ STEP 20: Laporkan hasil
     */
    public function laporkanHasil(Request $request, $id)
    {
        $request->validate([
            'laporan_hasil' => 'required|file|mimes:pdf|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            if (!$pengajuan->canTransitionTo(PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)) {
                throw new \Exception('Hasil harus diumumkan terlebih dahulu.');
            }

            // Upload laporan
            $file = $request->file('laporan_hasil');
            $filename = 'laporan_hasil_akreditasi_' . time() . '.pdf';
            $path = $file->storeAs(
                "permohonan-akreditasi/{$pengajuan->id}/laporan-hasil",
                $filename,
                'public'
            );

            $pengajuan->dokumen()->create([
                'jenis_dokumen' => 'laporan_hasil_akreditasi',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
            ]);

            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                'Laporan hasil akreditasi disubmit'
            );

            $pengajuan->update([
                'tanggal_pelaporan_hasil' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Laporan hasil akreditasi berhasil disubmit.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Laporkan hasil failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * ✅ STEP 21: Simpan arsip & selesai
     */
    public function simpanArsip($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            if (!$pengajuan->canTransitionTo(PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN)) {
                throw new \Exception('Hasil harus dilaporkan terlebih dahulu.');
            }

            // Create archive package (optional: zip all documents)
            // TODO: Generate comprehensive archive

            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                'Arsip pelaksanaan akreditasi disimpan'
            );

            $pengajuan->update([
                'tanggal_penyimpanan' => now(),
            ]);

            // Mark as completed
            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_SELESAI,
                'Proses akreditasi selesai'
            );

            DB::commit();

            return back()->with('success', 'Arsip berhasil disimpan. Proses akreditasi SELESAI.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Simpan arsip failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }
}
