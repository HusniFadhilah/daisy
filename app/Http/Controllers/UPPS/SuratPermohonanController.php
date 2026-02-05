<?php
// app/Http/Controllers/UPPS/SuratPermohonanController.php

namespace App\Http\Controllers\UPPS;

use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Models\PengingatAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratPermohonanController extends Controller
{
    /**
     * Display list of surat permohonan yang dikirim oleh prodi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'deAssigned',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_permohonan')
                ->where('is_latest', true),
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_DRAFT,
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', [
                        PengajuanAkreditasi::STATUS_DRAFT,
                        PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
                    ]);
            });

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(20)
            ->appends($request->query());

        // Statistics
        $stats = $this->calculateStatistics($studyProgramIds);

        // Get tahun list
        $tahunList = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.surat-permohonan.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail surat permohonan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'deAssigned',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_permohonan'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.surat-permohonan.show', compact('pengajuan'));
    }

    /**
     * Show form untuk membuat permohonan baru
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        // Get study programs untuk dropdown
        $prodis = $user->studyPrograms()
            ->with(['university', 'degreeLevel'])
            ->get();

        // Auto-select jika hanya 1 prodi
        $prodiUser = $prodis->count() === 1 ? $prodis->first() : null;

        // Get pengingat yang belum direspon
        $pengingatBelumDirespon = PengingatAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengirim'
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengingatAkreditasi::STATUS_BELUM_DIRESPON)
            ->orderBy('tanggal_dikirim', 'desc')
            ->get();

        // Check jika ada id_pengingat dari parameter (untuk auto-fill)
        $selectedPengingat = null;
        if ($request->filled('id_pengingat')) {
            $selectedPengingat = $pengingatBelumDirespon->firstWhere('id', $request->id_pengingat);

            if (!$selectedPengingat) {
                return redirect()
                    ->route('upps.surat-permohonan')
                    ->with('error', 'Pengingat tidak ditemukan atau sudah direspon.');
            }
        }

        return view('upps.surat-permohonan.create', compact(
            'prodis',
            'prodiUser',
            'pengingatBelumDirespon',
            'selectedPengingat'
        ));
    }

    /**
     * Store permohonan akreditasi (submit atau draft)
     */
    public function store(Request $request)
    {
        $isDraft = $request->boolean('is_draft');

        // ✅ Check jika merespon pengingat
        $pengingat = null;
        if ($request->filled('id_pengingat')) {
            $pengingat = PengingatAkreditasi::find($request->id_pengingat);

            if ($pengingat && $pengingat->status !== PengingatAkreditasi::STATUS_BELUM_DIRESPON) {
                return back()->with('error', 'Pengingat ini sudah direspon.');
            }

            // Validate user access
            $user = auth()->user();
            if ($pengingat && !$user->studyPrograms()->where('study_programs.id', $pengingat->id_program_studi)->exists()) {
                abort(403, 'Anda tidak memiliki akses ke program studi ini.');
            }
        }

        // ✅ Validation rules - berbeda untuk draft vs submit
        $rules = [
            'id_program_studi' => 'required|exists:study_programs,id',
            'tahun_akreditasi' => 'required|integer|min:2024|max:' . (date('Y') + 2),
            'jenis_akreditasi' => 'required|in:baru,terakreditasi,perpanjangan,menuju_unggul',
            'catatan_pengaju' => 'nullable|string|max:2000',
        ];

        // File hanya required jika bukan draft
        if (!$isDraft) {
            $rules['file_surat_permohonan'] = 'required|file|mimes:pdf|max:5120';
        } else {
            $rules['file_surat_permohonan'] = 'nullable|file|mimes:pdf|max:5120';
        }

        $messages = [
            'jenis_akreditasi.required' => 'Jenis akreditasi wajib dipilih',
            'file_surat_permohonan.required' => 'File surat permohonan wajib diupload',
            'file_surat_permohonan.mimes' => 'File harus berformat PDF',
            'file_surat_permohonan.max' => 'Ukuran file maksimal 5MB',
        ];

        $validated = $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            // Tentukan status berdasarkan draft atau submit
            $status = $isDraft
                ? PengajuanAkreditasi::STATUS_DRAFT
                : PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM;

            // ✅ Create pengajuan
            $pengajuan = PengajuanAkreditasi::create([
                'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan($validated['jenis_akreditasi']),
                'id_program_studi' => $validated['id_program_studi'],
                'id_user_pengaju' => auth()->id(),
                'id_de_assigned' => $pengingat?->id_de_pengirim,
                'tahun_akreditasi' => $validated['tahun_akreditasi'],
                'jenis_akreditasi' => $validated['jenis_akreditasi'],
                'status' => $status,
                'catatan_pengaju' => $validated['catatan_pengaju'],
                'tanggal_pengingat' => $pengingat?->tanggal_dikirim,
                'tanggal_surat_permohonan_dikirim' => !$isDraft ? now() : null,
            ]);

            // ✅ Upload file jika ada
            if ($request->hasFile('file_surat_permohonan')) {
                $this->uploadSuratPermohonan($pengajuan, $request->file('file_surat_permohonan'));
            }

            // ✅ Mark pengingat as responded jika submit (bukan draft)
            if ($pengingat && !$isDraft) {
                $pengingat->markAsResponded($pengajuan);
            }

            // ✅ Log status
            $pengajuan->statusLog()->create([
                'status_from' => $pengingat
                    ? PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM
                    : null,
                'status_to' => $status,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => $isDraft
                    ? 'Draft permohonan disimpan'
                    : ($pengingat
                        ? 'Permohonan dikirim sebagai respon pengingat akreditasi'
                        : 'Permohonan akreditasi baru dibuat'),
            ]);

            DB::commit();

            $message = $isDraft
                ? 'Draft permohonan berhasil disimpan. Anda dapat melanjutkan pengisian nanti.'
                : 'Permohonan akreditasi berhasil dikirim.';

            return redirect()
                ->route('upps.surat-permohonan')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating pengajuan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan permohonan: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Helper method untuk upload surat permohonan
     */
    private function uploadSuratPermohonan(PengajuanAkreditasi $pengajuan, $file)
    {
        // Sanitize filename
        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename = time() . '_' . $sanitizedName;

        $path = $file->storeAs('dokumen/surat-permohonan', $filename, 'public');

        return $pengajuan->dokumen()->create([
            'jenis_dokumen' => 'surat_permohonan',
            'nama_file' => $filename,
            'path_file' => $path,
            'original_filename' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'is_latest' => true,
        ]);
    }

    public function edit($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_permohonan')->where('is_latest', true),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Only allow editing draft
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_DRAFT) {
            return redirect()
                ->route('upps.surat-permohonan.show', $id)
                ->with('error', 'Hanya permohonan dengan status draft yang dapat diedit.');
        }

        // Get study programs untuk dropdown
        $prodis = $user->studyPrograms()
            ->with(['university', 'degreeLevel'])
            ->get();

        return view('upps.surat-permohonan.edit', compact('pengajuan', 'prodis'));
    }

    /**
     * ✅ NEW: Update draft permohonan (update data atau kirim)
     */
    public function update(Request $request, $id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Only allow updating draft
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_DRAFT) {
            return back()->with('error', 'Hanya permohonan dengan status draft yang dapat diubah.');
        }

        $isDraft = $request->boolean('is_draft');

        // ✅ Validation rules - berbeda untuk draft vs submit
        $rules = [
            'id_program_studi' => 'required|exists:study_programs,id',
            'tahun_akreditasi' => 'required|integer|min:2024|max:' . (date('Y') + 2),
            'jenis_akreditasi' => 'required|in:baru,terakreditasi,perpanjangan,menuju_unggul',
            'catatan_pengaju' => 'nullable|string|max:2000',
        ];

        // File hanya required jika submit (bukan draft)
        $existingFile = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'surat_permohonan')
            ->where('is_latest', true)
            ->exists();

        if (!$isDraft && !$existingFile) {
            // Jika submit tapi belum ada file, maka file required
            $rules['file_surat_permohonan'] = 'required|file|mimes:pdf|max:5120';
        } else {
            $rules['file_surat_permohonan'] = 'nullable|file|mimes:pdf|max:5120';
        }

        $messages = [
            'jenis_akreditasi.required' => 'Jenis akreditasi wajib dipilih',
            'file_surat_permohonan.required' => 'File surat permohonan wajib diupload untuk mengirim permohonan',
            'file_surat_permohonan.mimes' => 'File harus berformat PDF',
            'file_surat_permohonan.max' => 'Ukuran file maksimal 5MB',
        ];

        $validated = $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            // Tentukan status berdasarkan draft atau submit
            $newStatus = $isDraft
                ? PengajuanAkreditasi::STATUS_DRAFT
                : PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM;

            $oldStatus = $pengajuan->status;

            // ✅ Update pengajuan
            $pengajuan->update([
                'id_program_studi' => $validated['id_program_studi'],
                'tahun_akreditasi' => $validated['tahun_akreditasi'],
                'jenis_akreditasi' => $validated['jenis_akreditasi'],
                'catatan_pengaju' => $validated['catatan_pengaju'],
                'status' => $newStatus,
                'tanggal_surat_permohonan_dikirim' => !$isDraft ? now() : null,
            ]);

            // ✅ Upload file baru jika ada
            if ($request->hasFile('file_surat_permohonan')) {
                // Mark old file as not latest
                $pengajuan->dokumen()
                    ->where('jenis_dokumen', 'surat_permohonan')
                    ->update(['is_latest' => false]);

                // Upload new file
                $this->uploadSuratPermohonan($pengajuan, $request->file('file_surat_permohonan'));
            }

            // ✅ Mark pengingat as responded jika submit dari draft
            if (!$isDraft && $pengajuan->id_de_assigned) {
                $pengingat = PengingatAkreditasi::where('id_de_pengirim', $pengajuan->id_de_assigned)
                    ->where('id_program_studi', $pengajuan->id_program_studi)
                    ->where('tahun_akreditasi', $pengajuan->tahun_akreditasi)
                    ->where('status', PengingatAkreditasi::STATUS_BELUM_DIRESPON)
                    ->first();

                if ($pengingat) {
                    $pengingat->markAsResponded($pengajuan);
                }
            }

            // ✅ Log status jika berubah
            if ($oldStatus !== $newStatus) {
                $pengajuan->statusLog()->create([
                    'status_from' => $oldStatus,
                    'status_to' => $newStatus,
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'keterangan' => $isDraft
                        ? 'Draft permohonan diperbarui'
                        : 'Draft permohonan dikirim',
                ]);
            }

            DB::commit();

            $message = $isDraft
                ? 'Draft permohonan berhasil diperbarui.'
                : 'Permohonan akreditasi berhasil dikirim.';

            return redirect()
                ->route('upps.surat-permohonan')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating pengajuan: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Download surat permohonan
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
            ->where('jenis_dokumen', 'surat_permohonan')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->response(
            $dokumen->path_file,
            $dokumen->original_filename
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
                    ->orWhereHas(
                        'studyProgram',
                        fn($ssq) =>
                        $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        // Filter by status
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    /**
     * Calculate statistics based on status log
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Ambil semua status log untuk pengajuan milik prodi ini
        $logs = DB::table('pengajuan_status_log as psl')
            ->join('pengajuan_akreditasi as pa', 'psl.id_pengajuan', '=', 'pa.id')
            ->whereIn('pa.id_program_studi', $studyProgramIds)
            ->whereIn('psl.status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
            ])
            ->select('psl.id_pengajuan', 'psl.status_to')
            ->get();

        $stats = [
            'total' => 0,
            'menunggu' => 0,
            'dikirim' => 0,
            'diterima' => 0,
            'ditolak' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;
            // Menunggu: ada PENGINGAT_DIKIRIM, tapi belum SURAT_PERMOHONAN_DIKIRIM
            if (
                in_array(PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM, $statuses)
            ) {
                $stats['menunggu']++;
            }
            // Dikirim: ada SURAT_PERMOHONAN_DIKIRIM, tapi belum diterima / ditolak
            if (
                in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK, $statuses)
            ) {
                $stats['dikirim']++;
            }

            // Diterima
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses)) {
                $stats['diterima']++;
            }

            // Ditolak
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK, $statuses)) {
                $stats['ditolak']++;
            }
        }

        return $stats;
    }

    public function downloadTemplateSurat()
    {
        return "";
    }
}
