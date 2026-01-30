<?php
// app/Http/Controllers/UPPS/PengingatAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengingatAkreditasi;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PengingatAkreditasiController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display list of pengingat akreditasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengingatAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengirim',
            'pengajuan'
        ])->whereIn('id_program_studi', $studyProgramIds);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun_akreditasi', $request->tahun);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('studyProgram', function ($sq) use ($request) {
                    $sq->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }

        $pengingatList = $query
            ->orderBy('tanggal_dikirim', 'desc')
            ->paginate(20)
            ->appends($request->query());

        // Statistics
        $stats = $this->calculateStatistics($studyProgramIds);

        // Get tahun list for filter
        $tahunList = PengingatAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.pengingat-akreditasi.index', compact(
            'pengingatList',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail pengingat
     */
    public function show($id)
    {
        $pengingat = PengingatAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengirim',
            'pengajuan.statusLog' => fn($q) => $q->latest()
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengingat->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke pengingat ini.');
        }

        return view('upps.pengingat-akreditasi.show', compact('pengingat'));
    }

    /**
     * Show form to respond to pengingat
     */
    public function showResponseForm($id)
    {
        $pengingat = PengingatAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengirim'
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengingat->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke pengingat ini.');
        }

        // Check if already responded
        if ($pengingat->status !== PengingatAkreditasi::STATUS_BELUM_DIRESPON) {
            return redirect()
                ->route('upps.pengingat-akreditasi.show', $pengingat->id)
                ->with('error', 'Pengingat ini sudah direspon.');
        }

        return view('upps.pengingat-akreditasi.respond', compact('pengingat'));
    }

    /**
     * Respond to pengingat (create permohonan akreditasi)
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'jenis_akreditasi' => 'required|in:baru,perpanjangan,menuju_unggul',
            'file_surat_permohonan' => 'required|file|mimes:pdf|max:5120',
            'catatan_pengaju' => 'nullable|string|max:2000',
        ], [
            'jenis_akreditasi.required' => 'Jenis akreditasi wajib dipilih',
            'file_surat_permohonan.required' => 'File surat permohonan wajib diupload',
            'file_surat_permohonan.mimes' => 'File harus berformat PDF',
            'file_surat_permohonan.max' => 'Ukuran file maksimal 5MB',
        ]);

        $pengingat = PengingatAkreditasi::findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengingat->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke pengingat ini.');
        }

        // Validate not yet responded
        if ($pengingat->status !== PengingatAkreditasi::STATUS_BELUM_DIRESPON) {
            return back()->with('error', 'Pengingat ini sudah direspon.');
        }

        DB::beginTransaction();
        try {
            // Create PengajuanAkreditasi
            $pengajuan = PengajuanAkreditasi::create([
                'nomor_pengajuan' => PengajuanAkreditasi::generateNomorPengajuan($request->jenis_akreditasi),
                'id_program_studi' => $pengingat->id_program_studi,
                'id_user_pengaju' => auth()->id(),
                'id_de_assigned' => $pengingat->id_de_pengirim,
                'tahun_akreditasi' => $pengingat->tahun_akreditasi,
                'jenis_akreditasi' => $request->jenis_akreditasi,
                'status' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                'catatan_pengaju' => $request->catatan_pengaju,
                'tanggal_pengingat' => $pengingat->tanggal_dikirim,
                'tanggal_surat_permohonan_dikirim' => now(),
            ]);

            // Upload surat permohonan
            $file = $request->file('file_surat_permohonan');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $path = $file->storeAs(
                "pengajuan/{$pengajuan->id}/surat-permohonan",
                $filename,
                'public'
            );

            $pengajuan->dokumen()->create([
                'jenis_dokumen' => 'surat_permohonan',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
                'versi' => 1,
            ]);

            // Mark pengingat as responded
            $pengingat->markAsResponded($pengajuan);

            // Log status
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                'status_to' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Permohonan akreditasi dikirim sebagai respon pengingat',
            ]);

            DB::commit();

            return redirect()
                ->route('pengajuan.show', $pengajuan->id)
                ->with('success', 'Permohonan akreditasi berhasil dikirim sebagai respon pengingat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error responding to pengingat: " . $e->getMessage(), [
                'pengingat_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        $baseQuery = PengingatAkreditasi::whereIn('id_program_studi', $studyProgramIds);

        return [
            'total' => (clone $baseQuery)->count(),

            'belum_direspon' => (clone $baseQuery)
                ->where('status', PengingatAkreditasi::STATUS_BELUM_DIRESPON)
                ->count(),

            'direspon' => (clone $baseQuery)
                ->where('status', PengingatAkreditasi::STATUS_DIRESPON)
                ->count(),

            'kedaluwarsa' => (clone $baseQuery)
                ->where('status', PengingatAkreditasi::STATUS_KEDALUWARSA)
                ->count(),
        ];
    }
}
