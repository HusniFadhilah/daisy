<?php

namespace App\Http\Controllers\UPPS;

use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PermohonanBandingController extends Controller
{
    // ============================================================
    // STATUS YANG RELEVAN UNTUK BANDING (tampil di list UPPS)
    // ============================================================
    private const STATUS_LIST_UPPS = [
        PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
        PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
        PengajuanAkreditasi::STATUS_MENUNGGU_PEMBAYARAN_BANDING,
        PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DITERIMA,
        PengajuanAkreditasi::STATUS_MENUNGGU_VERIFIKASI_PEMBAYARAN_BANDING,
        PengajuanAkreditasi::STATUS_PEMBAYARAN_BANDING_DIVERIFIKASI,
    ];

    // Status yang MASIH BISA mengajukan banding
    private const STATUS_DAPAT_BANDING = [
        PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
    ];

    // ============================================================
    // INDEX
    // ============================================================

    /**
     * Daftar pengajuan yang relevan dengan proses banding untuk UPPS
     */
    public function index(Request $request)
    {
        $user            = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q
                ->where('jenis_dokumen', 'surat_permohonan_banding')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q
                ->whereIn('status_to', self::STATUS_LIST_UPPS)
                ->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', self::STATUS_LIST_UPPS);
            });

        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(20)
            ->appends($request->query());

        $tahunList = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.permohonan-banding.index', compact(
            'pengajuans',
            // 'stats',
            'tahunList'
        ));
    }

    // ============================================================
    // CREATE
    // ============================================================

    /**
     * Form pengajuan banding baru
     */
    public function create(Request $request)
    {
        $user            = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        // Hanya prodi yang eligible
        $eligibleProdiIds = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
            ->whereNotNull('tanggal_hasil_akreditasi_dikirim')
            ->whereDoesntHave('statusLog', fn($q) => $q
                ->where('status_to', PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN))
            ->pluck('id_program_studi')
            ->unique();

        $prodis = $user->studyPrograms()
            ->with(['university', 'degreeLevel'])
            ->whereIn('study_programs.id', $eligibleProdiIds)
            ->get();

        return view('upps.permohonan-banding.create', compact('prodis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_program_studi'      => 'required|exists:study_programs,id',
            'alasan_banding'        => 'required|string|max:5000',
            'file_surat_permohonan' => 'required|file|mimes:pdf|max:5120',
        ], [
            'id_program_studi.required'      => 'Program studi harus dipilih.',
            'alasan_banding.required'        => 'Alasan banding wajib diisi.',
            'file_surat_permohonan.required' => 'Surat permohonan banding wajib diupload.',
            'file_surat_permohonan.mimes'    => 'File harus berformat PDF.',
            'file_surat_permohonan.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $user            = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        // Pastikan user punya akses ke prodi ini
        if (!$studyProgramIds->contains($validated['id_program_studi'])) {
            abort(403, 'Anda tidak memiliki akses ke program studi ini.');
        }

        // Resolve id_pengajuan dari prodi yang disubmit
        $pengajuan = PengajuanAkreditasi::where('id_program_studi', $validated['id_program_studi'])
            ->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
            ->whereNotNull('tanggal_hasil_akreditasi_dikirim')
            ->whereDoesntHave('statusLog', fn($q) => $q
                ->where('status_to', PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN))
            ->latest()
            ->first();

        if (!$pengajuan) {
            return back()
                ->withInput()
                ->with('error', 'Program studi ini tidak memiliki pengajuan akreditasi yang dapat diajukan banding.');
        }

        DB::beginTransaction();
        try {
            // Validasi status
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI) {
                throw new \Exception('Status pengajuan saat ini tidak memperbolehkan pengajuan banding.');
            }

            // Cek via status log
            $sudahDiajukan = $pengajuan->statusLog()
                ->where('status_to', PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)
                ->exists();

            if ($sudahDiajukan) {
                throw new \Exception('Permohonan banding untuk pengajuan ini sudah pernah diajukan.');
            }

            $statusFrom = $pengajuan->status;

            $pengajuan->update([
                'status'                     => PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                'tanggal_permohonan_banding' => now(),
                'alasan_banding'             => $validated['alasan_banding'],
            ]);

            $this->uploadSuratBanding($pengajuan, $request->file('file_surat_permohonan'));

            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to'   => PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => 'Permohonan banding diajukan oleh UPPS/PS.',
            ]);

            DB::commit();

            return redirect()
                ->route('upps.permohonan-banding')
                ->with('success', 'Permohonan banding berhasil diajukan. Silakan tunggu konfirmasi dari LAMDEPILAR.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('store permohonan banding gagal', [
                'user_id'      => auth()->id(),
                'pengajuan_id' => $pengajuan?->id,
                'error'        => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal mengajukan banding: ' . $e->getMessage());
        }
    }

    // ============================================================
    // SHOW
    // ============================================================

    /**
     * Detail permohonan banding
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
            'asesmen.hasil',
            'dokumen' => fn($q) => $q
                ->where('jenis_dokumen', 'surat_permohonan_banding')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q
                ->whereIn('status_to', self::STATUS_LIST_UPPS)
                ->orderBy('changed_at', 'asc'),
        ])->findOrFail($id);

        // ✅ Cek akses
        $studyProgramIds = Auth::user()->studyPrograms()->pluck('study_programs.id');
        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.permohonan-banding.show', compact('pengajuan'));
    }

    // ============================================================
    // DOWNLOAD
    // ============================================================

    /**
     * Download surat permohonan banding yang sudah diupload
     */
    public function download($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // ✅ Cek akses
        $studyProgramIds = Auth::user()->studyPrograms()->pluck('study_programs.id');
        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        $pengajuanDokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_permohonan_banding')
            ->where('is_latest', true)
            ->firstOrFail();

        return $pengajuanDokumen->downloadDokumen();
    }

    /**
     * Download templat surat permohonan banding
     */
    public function downloadTemplateSurat()
    {
        $path = public_path('assets/file/TEMPLATE PERMOHONAN BANDING.docx');

        if (!file_exists($path)) {
            abort(404, 'File templat tidak ditemukan.');
        }

        return response()->download($path, 'TEMPLATE_PERMOHONAN_BANDING.docx');
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    /**
     * Upload surat permohonan banding dan simpan sebagai PengajuanDokumen
     */
    private function uploadSuratBanding(PengajuanAkreditasi $pengajuan, $file): PengajuanDokumen
    {
        // Nonaktifkan dokumen lama jika ada (edge case)
        $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_permohonan_banding')
            ->where('is_latest', true)
            ->update(['is_latest' => false]);

        $originalName  = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename      = time() . '_' . $sanitizedName;

        $path = $file->storeAs('dokumen/surat-permohonan-banding', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen'     => 'surat_permohonan_banding',
            'nama_file'         => $filename,
            'path_file'         => $path,
            'original_filename' => $originalName,
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
            'uploaded_by'       => auth()->id(),
            'is_latest'         => true,
        ]);
    }

    /**
     * Terapkan filter pencarian ke query
     */
    private function applyFilters($query, Request $request): void
    {
        $query->when(
            $request->filled('tahun'),
            fn($q) => $q->where('tahun_akreditasi', $request->tahun)
        );

        $query->when(
            $request->filled('status'),
            fn($q) => $q->where('status', $request->status)
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
}
