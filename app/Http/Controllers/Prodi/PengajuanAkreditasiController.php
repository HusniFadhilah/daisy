<?php

namespace App\Http\Controllers\Prodi;

use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PengajuanAkreditasiController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of pengajuan
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get study program IDs for current user
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel',
            'studyProgram.university',
            'pengaju',
            'deskEvaluator',
            'pembayaran'
        ])->whereIn('id_program_studi', $studyProgramIds);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_pengajuan', 'like', '%' . $request->search . '%')
                    ->orWhereHas('studyProgram', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $pengajuans = $query->latest()->paginate(10);

        return view('asesmen.pengajuan.index', compact('pengajuans'));
    }

    /**
     * Show the form for creating a new pengajuan (Langkah 2)
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $prodiUser = $user->studyPrograms()->with(['degreeLevel', 'university'])->first();
        $prodis = $prodiUser ? null : StudyProgram::all();

        // ✅ CHECK: Apakah ada pengajuan yang sudah dibuat DE?
        $pengajuanId = $request->get('pengajuan_id');
        $pengajuan = null;

        if ($pengajuanId) {
            $pengajuan = PengajuanAkreditasi::with('studyProgram')
                ->where('id', $pengajuanId)
                ->where('status', 'pengingat_dikirim')
                ->whereNull('id_user_pengaju')
                ->first();

            // Check authorization
            if ($pengajuan) {
                $userStudyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();
                if (!in_array($pengajuan->id_program_studi, $userStudyProgramIds)) {
                    abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
                }
            }
        }

        return view('asesmen.pengajuan.create', compact('prodiUser', 'prodis', 'pengajuan'));
    }

    /**
     * Store a newly created pengajuan (Langkah 2: Submit Surat Permohonan)
     */
    public function store(Request $request)
    {
        $request->validate([
            'pengajuan_id' => 'nullable|exists:pengajuan_akreditasi,id',
            'id_program_studi' => 'required|exists:study_programs,id',
            'tahun_akreditasi' => 'required|integer|min:2024',
            'jenis_akreditasi' => 'required|in:baru,perpanjangan,re-akreditasi',
            'catatan_pengaju' => 'nullable|string',
            'surat_permohonan' => 'required|file|mimes:pdf|max:5120', // 5MB
        ]);

        DB::beginTransaction();
        try {
            // ✅ CHECK: Update existing atau create new?
            if ($request->filled('pengajuan_id')) {
                // UPDATE EXISTING
                $pengajuan = PengajuanAkreditasi::where('id', $request->pengajuan_id)
                    ->where('status', 'pengingat_dikirim')
                    ->whereNull('id_user_pengaju')
                    ->firstOrFail();

                // Check authorization
                $user = Auth::user();
                $userStudyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();
                if (!in_array($pengajuan->id_program_studi, $userStudyProgramIds)) {
                    abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
                }

                // Update pengajuan
                $pengajuan->update([
                    'id_user_pengaju' => Auth::id(),
                    'id_program_studi' => $request->id_program_studi, // Bisa diganti jika DE salah pilih
                    'tahun_akreditasi' => $request->tahun_akreditasi,
                    'jenis_akreditasi' => $request->jenis_akreditasi,
                    'tanggal_pengajuan' => now(),
                    'catatan_pengaju' => $request->catatan_pengaju,
                    'status' => 'surat_permohonan_diterima',
                    'tanggal_surat_permohonan' => now(),
                ]);

                $this->logStatus($pengajuan, 'pengingat_dikirim', 'surat_permohonan_diterima', 'Data dilengkapi oleh prodi');
            } else {
                // CREATE NEW (jika prodi submit tanpa pengingat dari DE)
                $pengajuan = PengajuanAkreditasi::create([
                    'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan(),
                    'id_program_studi' => $request->id_program_studi,
                    'id_user_pengaju' => Auth::id(),
                    'tahun_akreditasi' => $request->tahun_akreditasi,
                    'jenis_akreditasi' => $request->jenis_akreditasi,
                    'tanggal_pengajuan' => now(),
                    'catatan_pengaju' => $request->catatan_pengaju,
                    'status' => 'surat_permohonan_diterima',
                    'tanggal_surat_permohonan' => now(),
                ]);

                $this->logStatus($pengajuan, null, 'surat_permohonan_diterima', 'Surat permohonan diajukan (tanpa pengingat)');
            }

            // Upload surat permohonan
            if ($request->hasFile('surat_permohonan')) {
                $file = $request->file('surat_permohonan');
                $filename = 'surat_permohonan_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/surat', $filename, 'public');

                PengajuanDokumen::create([
                    'id_pengajuan' => $pengajuan->id,
                    'jenis_dokumen' => 'surat_permohonan',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => Auth::id(),
                    'is_latest' => true,
                ]);
            }

            DB::commit();

            $message = $request->filled('pengajuan_id')
                ? 'Data pengajuan berhasil dilengkapi. Nomor pengajuan: ' . $pengajuan->nomor_pengajuan
                : 'Pengajuan akreditasi berhasil disubmit. Nomor pengajuan: ' . $pengajuan->nomor_pengajuan;

            return redirect()->route('pengajuan.show', $pengajuan->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.degreeLevel', // ✅ FIX
            'studyProgram.university',
            'pengaju',
            'deskEvaluator',
            'dokumen.uploader',
            'reviewKesiapan.reviewer',
            'pembayaran',
            'statusLog.changedBy'
        ])->findOrFail($id);

        // Check authorization
        $this->authorize('view', $pengajuan);

        return view('asesmen.pengajuan.show', compact('pengajuan'));
    }

    /**
     * Upload draft borang (Langkah 4)
     */
    public function uploadDraftBorang(Request $request, $id)
    {
        $request->validate([
            'draft_borang' => 'required|file|mimes:pdf,xlsx,xls|max:10240', // 10MB
            'keterangan' => 'nullable|string|max:500',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        // Check status
        if (!in_array($pengajuan->status, ['borang_dikirim', 'review_kesiapan_belum_siap'])) {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk upload draft borang.');
        }

        DB::beginTransaction();
        try {
            // Mark previous drafts as not latest
            PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->update(['is_latest' => false]);

            // Upload new draft
            $file = $request->file('draft_borang');
            $filename = 'draft_borang_v' . (time()) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/draft', $filename, 'public');

            $versi = PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'draft_borang')
                ->max('versi') + 1;

            PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'draft_borang',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => Auth::id(),
                'keterangan' => $request->keterangan,
                'versi' => $versi,
                'is_latest' => true,
            ]);

            // Update status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'draft_borang_diterima',
                'tanggal_draft_borang' => now(),
            ]);

            $this->logStatus($pengajuan, $oldStatus, 'draft_borang_diterima', 'Draft borang diupload (v' . $versi . ')');

            DB::commit();

            return back()->with('success', 'Draft borang berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload bukti pembayaran (Langkah 7)
     */
    public function uploadBuktiPembayaran(Request $request, $id)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tanggal_pembayaran' => 'required|date',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        if ($pengajuan->status !== 'menunggu_pembayaran') {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk upload bukti pembayaran.');
        }

        DB::beginTransaction();
        try {
            // Upload bukti pembayaran
            $file = $request->file('bukti_pembayaran');
            $filename = 'bukti_bayar_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/pembayaran', $filename, 'public');

            PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'bukti_pembayaran',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => Auth::id(),
                'is_latest' => true,
            ]);

            // Update pembayaran
            $pengajuan->pembayaran->update([
                'status_pembayaran' => 'dibayar',
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
            ]);

            // Update pengajuan status
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => 'pembayaran_diterima',
                'tanggal_pembayaran' => now(),
            ]);

            $this->logStatus($pengajuan, $oldStatus, 'pembayaran_diterima', 'Bukti pembayaran diupload');

            DB::commit();

            return back()->with('success', 'Bukti pembayaran berhasil diupload. Menunggu verifikasi dari DE.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload borang final (Langkah 7)
     */
    public function uploadBorangFinal(Request $request, $id)
    {
        $request->validate([
            'borang_final' => 'required|file|mimes:pdf,xlsx|max:10240',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $this->authorize('update', $pengajuan);

        // ✅ FIX: Check correct status
        if ($pengajuan->status !== 'pembayaran_diterima') {
            return back()->with('error', 'Upload bukti pembayaran terlebih dahulu.');
        }

        // ✅ FIX: Check payment verified
        if (!$pengajuan->pembayaran || $pengajuan->pembayaran->status_pembayaran !== 'verified') {
            return back()->with('error', 'Pembayaran belum diverifikasi oleh DE.');
        }

        DB::beginTransaction();
        try {
            // Upload borang final
            $file = $request->file('borang_final');
            $filename = 'borang_final_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan/' . $pengajuan->id . '/final', $filename, 'public');

            PengajuanDokumen::create([
                'id_pengajuan' => $pengajuan->id,
                'jenis_dokumen' => 'borang_final',
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
                'status' => 'borang_final_diterima',
                'tanggal_borang_final' => now(),
            ]);

            $this->logStatus($pengajuan, $oldStatus, 'borang_final_diterima', 'Borang final diupload');

            DB::commit();

            return back()->with('success', 'Borang final berhasil diupload. Menunggu approval dari DE.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen
     */
    public function downloadDokumen($id)
    {
        $dokumen = PengajuanDokumen::with('pengajuan')->findOrFail($id);

        // Check authorization
        $user = Auth::user();
        $pengajuan = $dokumen->pengajuan;

        $userStudyProgramIds = $user->studyPrograms()->pluck('study_programs.id')->toArray();
        $hasAccess = in_array($pengajuan->id_program_studi, $userStudyProgramIds)
            || $pengajuan->id_de_assigned === $user->id
            || $user->role === 'admin';

        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        // Check if file exists
        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Get full path
        $filePath = Storage::disk('public')->path($dokumen->path_file);

        // Return download response
        return response()->download($filePath, $dokumen->original_filename);
    }

    /**
     * Log status change
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
