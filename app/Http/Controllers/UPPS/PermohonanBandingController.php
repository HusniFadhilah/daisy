<?php
// app/Http/Controllers/UPPS/PermohonanBandingController.php

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
    /**
     * ✅ Display list of permohonan banding
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_permohonan_banding')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                PengajuanAkreditasi::STATUS_BANDING_DITUGASKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                PengajuanAkreditasi::STATUS_BANDING_DITUGASKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
            ]);

        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(20)
            ->appends($request->query());

        $stats = $this->calculateStatistics($studyProgramIds);

        $tahunList = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.permohonan-banding.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * ✅ Show create form
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        // Get study programs untuk dropdown
        $prodis = $user->studyPrograms()
            ->with(['university', 'degreeLevel'])
            ->get();

        // Auto-select dari URL param ?prodi_id=xxx
        $selectedProdiId = $request->get('prodi_id');
        $selectedProdi = null;

        if ($selectedProdiId && $studyProgramIds->contains($selectedProdiId)) {
            $selectedProdi = $prodis->firstWhere('id', $selectedProdiId);
        } elseif ($prodis->count() === 1) {
            // Auto-select jika hanya 1 prodi
            $selectedProdi = $prodis->first();
            $selectedProdiId = $selectedProdi->id;
        }

        // ✅ Get pengajuan akreditasi yang bisa dibanding (masa sanggah)
        $pengajuansAvailable = collect();
        if ($selectedProdiId) {
            $pengajuansAvailable = PengajuanAkreditasi::with([
                'asesmen.hasil',
                'studyProgram'
            ])
                ->where('id_program_studi', $selectedProdiId)
                ->whereIn('status', [
                    PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                    PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                ])
                ->whereNotNull('tanggal_hasil_akreditasi_dikirim')
                ->latest()
                ->get();
        }

        // Auto-select pengajuan dari URL param ?pengajuan_id=xxx
        $selectedPengajuanId = $request->get('pengajuan_id');
        $selectedPengajuan = null;

        if ($selectedPengajuanId) {
            $selectedPengajuan = $pengajuansAvailable->firstWhere('id', $selectedPengajuanId);
        }

        return view('upps.permohonan-banding.create', compact(
            'prodis',
            'selectedProdi',
            'selectedProdiId',
            'pengajuansAvailable',
            'selectedPengajuan'
        ));
    }

    /**
     * ✅ Store permohonan banding
     */
    public function store(Request $request)
    {
        $rules = [
            'id_pengajuan' => 'required|exists:pengajuan_akreditasi,id',
            'alasan_banding' => 'required|string|max:5000',
            'file_surat_permohonan' => 'required|file|mimes:pdf|max:5120',
        ];

        $messages = [
            'id_pengajuan.required' => 'Pengajuan akreditasi harus dipilih',
            'id_pengajuan.exists' => 'Pengajuan akreditasi tidak valid',
            'alasan_banding.required' => 'Alasan banding wajib diisi',
            'file_surat_permohonan.required' => 'File surat permohonan banding wajib diupload',
            'file_surat_permohonan.mimes' => 'File harus berformat PDF',
            'file_surat_permohonan.max' => 'Ukuran file maksimal 5MB',
        ];

        $validated = $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($validated['id_pengajuan']);

            // Validate: masa sanggah masih berlaku
            if (!in_array($pengajuan->status, [
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            ])) {
                throw new \Exception('Masa sanggah untuk pengajuan ini sudah berakhir atau belum dimulai.');
            }

            // Check if already submitted banding
            $existingBanding = $pengajuan->dokumen()
                ->where('jenis_dokumen', 'surat_permohonan_banding')
                ->exists();

            if ($existingBanding) {
                throw new \Exception('Permohonan banding untuk pengajuan ini sudah pernah diajukan.');
            }

            // ✅ Update pengajuan
            $statusFrom = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                'tanggal_permohonan_banding' => now(),
                'alasan_banding' => $validated['alasan_banding'],
            ]);

            // ✅ Upload file
            $this->uploadSuratPermohonanBanding($pengajuan, $request->file('file_surat_permohonan'));

            // ✅ Log status
            $pengajuan->statusLog()->create([
                'status_from' => $statusFrom,
                'status_to' => PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Permohonan banding telah diajukan',
            ]);

            DB::commit();

            return redirect()
                ->route('upps.permohonan-banding')
                ->with('success', 'Permohonan banding berhasil diajukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating permohonan banding: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal mengajukan banding: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Upload surat permohonan banding
     */
    private function uploadSuratPermohonanBanding(PengajuanAkreditasi $pengajuan, $file)
    {
        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename = time() . '_' . $sanitizedName;

        $path = $file->storeAs('dokumen/surat-permohonan-banding', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_permohonan_banding',
            'nama_file' => $filename,
            'path_file' => $path,
            'original_filename' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'is_latest' => true,
        ]);
    }

    /**
     * ✅ Show detail permohonan banding
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
            'asesmen.hasil',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_permohonan_banding'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.permohonan-banding.show', compact('pengajuan'));
    }

    /**
     * ✅ Download surat permohonan banding
     */
    public function download($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses untuk mengunduh dokumen ini.');
        }

        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_permohonan_banding')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download(
            $dokumen->path_file,
            $dokumen->original_filename
        );
    }

    /**
     * ✅ Download templat surat
     */
    public function downloadTemplateSurat()
    {
        $path = public_path('assets/file/TEMPLATE PERMOHONAN BANDING.docx');

        if (!file_exists($path)) {
            abort(404, 'File templat tidak ditemukan');
        }

        return response()->download(
            $path,
            'TEMPLATE_PERMOHONAN_BANDING.docx'
        );
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        $query->when(
            $request->filled('tahun'),
            fn($q) => $q->where('tahun_akreditasi', $request->tahun)
        );

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhere('nomor_permohonan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', fn($ssq) => $ssq->where('name', 'like', "%{$search}%"));
            });
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    /**
     * ✅ Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        $base = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds);

        $stats = [
            'total' => (clone $base)->whereIn('status', [
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DITERIMA,
                PengajuanAkreditasi::STATUS_BANDING_DITUGASKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
            ])->count(),

            'diajukan' => (clone $base)->where('status', PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)->count(),
            'diterima' => (clone $base)->where('status', PengajuanAkreditasi::STATUS_BANDING_DITERIMA)->count(),
            'proses' => (clone $base)->whereIn('status', [
                PengajuanAkreditasi::STATUS_BANDING_DITUGASKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
            ])->count(),
            'selesai' => (clone $base)->where('status', PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN)->count(),
        ];

        return $stats;
    }
}
