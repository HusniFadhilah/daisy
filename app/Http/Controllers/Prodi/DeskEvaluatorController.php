<?php

namespace App\Http\Controllers\Prodi;

use App\Models\BorangImport;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\ReviewKesiapan;
use App\Mail\PembayaranVerified;
use App\Models\PengajuanDokumen;
use App\Mail\PengingatAkreditasi;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;
use App\Models\PembayaranAkreditasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReviewKesiapanNotifikasi;

class DeskEvaluatorController extends Controller
{
    /**
     * Dashboard DE
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Pengajuan yang di-assign ke DE ini
        $query = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel', // ✅ FIX
            'studyProgram.university',
            'pengaju',
            'reviewKesiapan'
        ]);
        // ->where('id_de_assigned', $user->id);

        // Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pengajuans = $query->latest()->paginate(10);

        // Statistics
        $stats = [
            'total' => PengajuanAkreditasi::where('id_de_assigned', $user->id)->count(),
            'menunggu_review' => PengajuanAkreditasi::where('id_de_assigned', $user->id)
                ->where('status', 'draft_borang_diterima')->count(),
            'menunggu_pembayaran' => PengajuanAkreditasi::where('id_de_assigned', $user->id)
                ->where('status', 'menunggu_pembayaran')->count(),
            'siap_lanjut' => PengajuanAkreditasi::where('id_de_assigned', $user->id)
                ->where('status', 'lanjut_ke_ak')->count(),
        ];

        return view('asesmen.de.index', compact('pengajuans', 'stats'));
    }

    /**
     * Show pengajuan detail
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'pengaju',
            'deskEvaluator',
            'dokumen.uploader',
            'reviewKesiapan.reviewer',
            'pembayaran.verifier',
            'statusLog.changedBy'
        ])->findOrFail($id);

        return view('asesmen.de.show', compact('pengajuan'));
    }

    /**
     * Kirim pengingat akreditasi (Langkah 1)
     */
    public function kirimPengingat(Request $request)
    {
        $request->validate([
            'id_program_studi' => 'required|array',
            'id_program_studi.*' => 'exists:study_programs,id', // ✅ FIX
            'pesan_pengingat' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $prodis = StudyProgram::with(['users', 'degreeLevel'])->whereIn('id', $request->id_program_studi)->get(); // ✅ FIX

            foreach ($prodis as $prodi) {
                // Create pengajuan record
                $pengajuan = PengajuanAkreditasi::create([
                    'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan(),
                    'id_program_studi' => $prodi->id,
                    'id_de_assigned' => Auth::id(),
                    'tahun_akreditasi' => date('Y'),
                    'status' => 'pengingat_dikirim',
                    'tanggal_pengingat' => now(),
                ]);

                // Send email to prodi users
                foreach ($prodi->users as $user) {
                    Mail::to($user->email)->send(new PengingatAkreditasi($pengajuan, $request->pesan_pengingat));
                }

                $this->logStatus($pengajuan, null, 'pengingat_dikirim', 'Pengingat dikirim ke ' . $prodi->name);
            }

            DB::commit();

            return back()->with('success', 'Pengingat berhasil dikirim ke ' . count($prodis) . ' program studi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Kirim form borang (Langkah 3)
     */
    public function kirimFormBorang(Request $request, $id)
    {
        $request->validate([
            'borang_template' => 'required|file|mimes:xlsx,xls,pdf,docx|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        if ($pengajuan->status !== 'surat_permohonan_diterima') {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk kirim borang.');
        }

        DB::beginTransaction();
        try {
            // Upload borang template
            $file = $request->file('borang_template');
            $filename = 'borang_template_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/template', $filename, 'public');

            PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'borang_template',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => Auth::id(),
                'keterangan' => $request->keterangan,
                'is_latest' => true,
            ]);

            // Update status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'borang_dikirim',
                'tanggal_borang_dikirim' => now(),
            ]);

            $this->logStatus($pengajuan, $oldStatus, 'borang_dikirim', 'Form borang dikirim');

            DB::commit();

            return back()->with('success', 'Form borang berhasil dikirim ke prodi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * View parsed borang HTML (Read-only for DE)
     */
    public function viewBorangHTML($pengajuanId, $importId)
    {
        $pengajuan = PengajuanAkreditasi::with('studyProgram.university')->findOrFail($pengajuanId);

        $import = BorangImport::with([
            'sections' => function ($q) {
                $q->with(['elemen', 'tables.dataset'])->orderBy('position');
            }
        ])->findOrFail($importId);

        // Use same preview view
        return view('asesmen.pengajuan.borang-preview', compact('pengajuan', 'import'));
    }

    /**
     * Review kesiapan borang (Langkah 5a/5b)
     */
    public function reviewKesiapan(Request $request, $id)
    {
        $request->validate([
            'hasil_review' => 'required|in:siap,belum_siap',
            'catatan_review' => 'required|string',
            'checklist' => 'nullable|array',
            'jumlah_pembayaran' => 'required_if:hasil_review,siap|numeric|min:0',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        if ($pengajuan->status !== 'draft_borang_diterima') {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk review.');
        }

        DB::beginTransaction();
        try {
            // Get review version
            $versi = ReviewKesiapan::where('id_pengajuan', $pengajuan->id)->max('versi_review') + 1;

            // Create review record
            $review = ReviewKesiapan::create([ // ✅ ASSIGN ke variable
                'id_pengajuan' => $pengajuan->id,
                'id_reviewer' => Auth::id(),
                'hasil_review' => $request->hasil_review,
                'catatan_review' => $request->catatan_review,
                'checklist_kesiapan' => $request->checklist,
                'versi_review' => $versi,
                'tanggal_review' => now(),
            ]);

            $oldStatus = $pengajuan->status;

            if ($request->hasil_review === 'siap') {
                // Langkah 5a: Siap lanjut
                $pengajuan->update([
                    'status' => 'review_kesiapan_siap',
                    'tanggal_review_kesiapan' => now(),
                ]);

                // Create invoice pembayaran (Langkah 6)
                PembayaranAkreditasi::create([
                    'id_pengajuan' => $pengajuan->id,
                    'nomor_invoice' => PembayaranAkreditasi::generateNomorInvoice(),
                    'jumlah_pembayaran' => $request->jumlah_pembayaran,
                    'status_pembayaran' => 'pending',
                    'tanggal_jatuh_tempo' => now()->addDays(14),
                ]);

                // Update status ke menunggu pembayaran
                $pengajuan->update(['status' => 'menunggu_pembayaran']);

                $this->logStatus($pengajuan, $oldStatus, 'menunggu_pembayaran', 'Review: Siap lanjut, menunggu pembayaran');

                $message = 'Review selesai. Borang SIAP untuk lanjut ke tahap AK. Invoice pembayaran telah dibuat.';
            } else {
                // Langkah 5b: Belum siap
                $pengajuan->update([
                    'status' => 'review_kesiapan_belum_siap',
                    'tanggal_review_kesiapan' => now(),
                ]);

                $this->logStatus($pengajuan, $oldStatus, 'review_kesiapan_belum_siap', 'Review: Belum siap, perlu perbaikan');

                $message = 'Review selesai. Borang BELUM SIAP dan perlu dilengkapi oleh prodi.';
            }

            DB::commit();

            // ✅ FIX: Use $review variable
            Mail::to($pengajuan->pengaju->email)->send(
                new ReviewKesiapanNotifikasi($pengajuan, $review)
            );

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Verifikasi pembayaran
     */
    public function verifikasiPembayaran(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,ditolak',
            'catatan_verifikasi' => 'required|string',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $pembayaran = $pengajuan->pembayaran;

        if (!$pembayaran || $pembayaran->status_pembayaran !== 'dibayar') {
            return back()->with('error', 'Pembayaran belum diupload atau sudah diverifikasi.');
        }

        DB::beginTransaction();
        try {
            if ($request->status === 'verified') {
                $pembayaran->update([
                    'status_pembayaran' => 'verified',
                    'tanggal_verifikasi' => now(),
                    'verified_by' => Auth::id(),
                    'catatan_verifikasi' => $request->catatan_verifikasi,
                ]);

                // ✅ UPDATE: Log status untuk tracking
                $this->logStatus($pengajuan, $pengajuan->status, $pengajuan->status, 'Pembayaran diverifikasi, siap upload borang final');

                // ✅ ADD: Send email notification
                Mail::to($pengajuan->pengaju->email)->send(
                    new PembayaranVerified($pengajuan, true)
                );

                $message = 'Pembayaran berhasil diverifikasi. Prodi dapat upload borang final.';
            } else {
                $pembayaran->update([
                    'status_pembayaran' => 'ditolak',
                    'tanggal_verifikasi' => now(),
                    'verified_by' => Auth::id(),
                    'alasan_penolakan' => $request->catatan_verifikasi,
                ]);

                $pengajuan->update(['status' => 'menunggu_pembayaran']);

                $this->logStatus($pengajuan, 'pembayaran_diterima', 'menunggu_pembayaran', 'Pembayaran ditolak: ' . $request->catatan_verifikasi);

                // ✅ ADD: Send email notification
                Mail::to($pengajuan->pengaju->email)->send(
                    new PembayaranVerified($pengajuan, false)
                );

                $message = 'Pembayaran ditolak. Prodi perlu upload ulang bukti pembayaran.';
            }

            DB::commit();

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Approve untuk lanjut ke AK (Langkah 8)
     */
    public function approveLanjutAK($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        if ($pengajuan->status !== 'borang_final_diterima') {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk approve.');
        }

        // Check if pembayaran verified
        if (!$pengajuan->pembayaran || $pengajuan->pembayaran->status_pembayaran !== 'verified') {
            return back()->with('error', 'Pembayaran belum diverifikasi.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'lanjut_ke_ak',
                'tanggal_lanjut_ak' => now(),
            ]);

            $this->logStatus($pengajuan, $oldStatus, 'lanjut_ke_ak', 'Disetujui lanjut ke tahap AK/Asesmen Dokumen');

            // TODO: Create Asesmen record and assign asesor/validator

            DB::commit();

            return back()->with('success', 'Pengajuan disetujui dan akan dilanjutkan ke tahap AK/Asesmen Dokumen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Log status
     */
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
