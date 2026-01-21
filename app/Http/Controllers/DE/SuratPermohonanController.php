<?php
// app/Http/Controllers/DE/SuratPermohonanController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\StudyProgram;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SuratPermohonanController extends Controller
{
    /**
     * Display list of pengajuan with surat permohonan
     */
    public function index(Request $request)
    {
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
        ])
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
            ]);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by university
        if ($request->filled('university_id')) {
            $query->whereHas('studyProgram', function ($q) use ($request) {
                $q->where('id_university', $request->university_id);
            });
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun_akreditasi', $request->tahun);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_pengingat');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        return view('de.surat-permohonan.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList'
        ));
    }

    /**
     * Show detail pengajuan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'dokumen' => function ($q) {
                $q->where('jenis_dokumen', 'surat_permohonan')
                    ->where('is_latest', true);
            },
            'statusLog',
        ])->findOrFail($id);

        return view('de.surat-permohonan.show', compact('pengajuan'));
    }

    /**
     * Terima surat permohonan (update status)
     */
    public function terima(Request $request, $id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM) {
            return back()->with('error', 'Status pengajuan tidak sesuai untuk menerima surat permohonan.');
        }

        // Check apakah dokumen surat permohonan sudah ada
        $hasSuratPermohonan = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_permohonan')
            ->where('is_latest', true)
            ->exists();

        if (!$hasSuratPermohonan) {
            return back()->with('error', 'Dokumen surat permohonan belum diupload oleh prodi.');
        }

        DB::beginTransaction();
        try {
            // Update status pengajuan
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                'tanggal_surat_permohonan_diterima' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                'status_to' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => $request->keterangan ?? 'Surat permohonan diterima oleh DE',
            ]);

            DB::commit();

            return redirect()
                ->route('de.surat-permohonan', ['status' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA])
                ->with('success', 'Surat permohonan berhasil diterima. Status diubah menjadi "Surat Permohonan Diterima".');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menerima surat permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen surat permohonan
     */
    public function download($id)
    {
        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_permohonan')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::exists($dokumen->path_file)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::download($dokumen->path_file, $dokumen->original_filename);
    }

    /**
     * Tolak surat permohonan
     */
    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:1000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        DB::beginTransaction();
        try {
            // Update status ke ditolak (atau bisa kembali ke draft)
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_DITOLAK,
                'tanggal_surat_permohonan_ditolak' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to' => PengajuanAkreditasi::STATUS_DITOLAK,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Surat permohonan belum diterima: ' . $request->alasan_penolakan,
            ]);

            DB::commit();

            return redirect()
                ->route('de.surat-permohonan')
                ->with('success', 'Surat permohonan dari PS belum diterima.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak surat permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics()
    {
        return [
            'total' => PengajuanAkreditasi::whereIn('status', [
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            ])->count(),
            'menunggu' => PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM)
                ->count(),
            'dikirim' => PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM)
                ->count(),
            'diterima' => PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA)
                ->count(),
            'ditolak' => PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK)
                ->count(),
        ];
    }
}
