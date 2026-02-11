<?php
// app/Http/Controllers/UPPS/PengingatAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengingatAkreditasi;
use App\Models\PengajuanAkreditasi;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

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

        // Statistics (pengingat yang sudah dikirim)
        $stats = $this->calculateStatistics($studyProgramIds);

        // Calculate expiring accreditations (seperti di Pemetaan)
        $expiringStats = $this->calculateExpiringAccreditations($studyProgramIds);

        // Get tahun list for filter
        $tahunList = PengingatAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.pengingat-akreditasi.index', compact(
            'pengingatList',
            'stats',
            'tahunList',
            'expiringStats'
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
                ->with('error', 'Pengingat ini telah direspon.');
        }

        return view('upps.pengingat-akreditasi.respond', compact('pengingat'));
    }

    /**
     * Respond to pengingat (create permohonan akreditasi)
     */
    public function respond(Request $request, $id)
    {
        $request->validate([
            'jenis_akreditasi' => 'required|in:baru,terakreditasi,perpanjangan,menuju_unggul',
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
            return back()->with('error', 'Pengingat ini telah direspon.');
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
                ->route('upps.pengingat-akreditasi')
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
     * Calculate statistics (pengingat yang sudah dikirim)
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

    /**
     * Calculate expiring accreditations (seperti logic di Pemetaan)
     * Menghitung prodi yang masa akreditasinya akan berakhir di bulan target,
     * sekaligus mengambil daftar prodinya (untuk ditampilkan di alert UPPS).
     */
    private function calculateExpiringAccreditations($studyProgramIds, int $reminderMonths = 7): array
    {
        $now = now();

        // Target bulan (now + N bulan) -> window 1 bulan penuh
        $targetMonth = $now->copy()->addMonths($reminderMonths);
        $targetStart = $targetMonth->copy()->startOfMonth();
        $targetEnd   = $targetMonth->copy()->endOfMonth();

        // Query daftar prodi yang expire pada bulan target (dalam lingkup UPPS user)
        $expiringProgramsQuery = StudyProgram::with(['university', 'degreeLevel'])
            ->whereIn('id', $studyProgramIds)
            ->whereNotNull('tanggal_kedaluwarsa')
            ->whereBetween('tanggal_kedaluwarsa', [$targetStart, $targetEnd])
            ->orderBy('tanggal_kedaluwarsa', 'asc');

        $expiringCount = (clone $expiringProgramsQuery)->count();

        // Batasi list agar alert tidak terlalu panjang (mis. max 10)
        $expiringPrograms = (clone $expiringProgramsQuery)
            ->limit(10)
            ->get();

        return [
            'count' => $expiringCount,
            'has_expiring' => $expiringCount > 0,
            'months_ahead' => $reminderMonths,
            'target_month_label' => $targetMonth->locale('id')->translatedFormat('F Y'),
            'target_start' => $targetStart,
            'target_end' => $targetEnd,
            'programs' => $expiringPrograms,
            'programs_limit' => 10,
        ];
    }
}
