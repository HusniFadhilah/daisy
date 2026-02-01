<?php
// app/Http/Controllers/UPPS/PenyimpananArsipPelaksanaanAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenyimpananArsipPelaksanaanAkreditasiController extends Controller
{
    /**
     * Display list of arsip akreditasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penyimpanan')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'tanggal_penyimpanan'), $request->get('sort_order', 'desc'))
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

        return view('upps.penyimpanan-arsip-pelaksanaan-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail arsip akreditasi
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'dokumen' => fn($q) => $q->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        // Group documents by category
        $groupedDokumen = $this->groupDocuments($pengajuan);

        // Calculate statistics
        $statistics = $this->calculateProcessStatistics($pengajuan);

        return view('upps.penyimpanan-arsip-pelaksanaan-akreditasi.show', compact(
            'pengajuan',
            'groupedDokumen',
            'statistics'
        ));
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

        // Filter by peringkat hasil akhir
        $query->when($request->filled('peringkat'), function ($q) use ($request) {
            $q->where(function ($sq) use ($request) {
                $sq->where('peringkat_hasil_banding', $request->peringkat)
                    ->orWhere(function ($ssq) use ($request) {
                        $ssq->whereNull('peringkat_hasil_banding')
                            ->where('peringkat_hasil', $request->peringkat);
                    });
            });
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Total arsip disimpan
        $total = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penyimpanan')
            ->count();

        // Proses selesai
        $selesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_SELESAI)
            ->count();

        // By peringkat (prioritas hasil banding)
        $byPeringkat = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penyimpanan')
            ->get()
            ->groupBy(function ($item) {
                return $item->peringkat_hasil_banding ?? $item->peringkat_hasil ?? 'Tidak Ada';
            })
            ->map(fn($group) => $group->count())
            ->toArray();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'by_peringkat' => $byPeringkat,
        ];
    }

    /**
     * Group documents by category for display
     */
    private function groupDocuments($pengajuan): array
    {
        $dokumen = $pengajuan->dokumen->where('is_latest', true);

        return [
            'LED (Laporan Evaluasi Diri)' => $dokumen->whereIn('jenis_dokumen', [
                'data_kualitatif',
                'data_kuantitatif',
                'data_suplemen',
                'draft_borang',
                'borang_final',
            ]),
            'Dokumen Penilaian AK' => $dokumen->whereIn('jenis_dokumen', [
                'laporan_ak',
            ]),
            'Dokumen Asesmen AL' => $dokumen->whereIn('jenis_dokumen', [
                'laporan_al',
            ]),
            'Hasil Akreditasi' => $dokumen->whereIn('jenis_dokumen', [
                'sertifikat',
                'sertifikat_akreditasi',
                'sk_akreditasi',
                'sk_penetapan',
                'laporan_hasil',
            ]),
            'Dokumen Banding' => $dokumen->whereIn('jenis_dokumen', [
                'dokumen_banding',
                'hasil_banding',
            ]),
            'Dokumen Pendukung' => $dokumen->whereIn('jenis_dokumen', [
                'surat_permohonan',
                'surat_tugas',
                'bukti_pembayaran',
                'lembar_pengesahan',
                'dokumen_pendukung',
                'lainnya',
            ]),
        ];
    }

    /**
     * Calculate process statistics
     */
    private function calculateProcessStatistics($pengajuan): array
    {
        $stats = [];

        if ($pengajuan->tanggal_pengajuan && $pengajuan->tanggal_penyimpanan) {
            $stats['total_durasi'] = $pengajuan->tanggal_pengajuan->diffInDays($pengajuan->tanggal_penyimpanan);
        }

        if ($pengajuan->tanggal_pengajuan && $pengajuan->tanggal_penetapan) {
            $stats['durasi_hingga_penetapan'] = $pengajuan->tanggal_pengajuan->diffInDays($pengajuan->tanggal_penetapan);
        }

        if ($pengajuan->tanggal_validasi_borang_assigned && $pengajuan->tanggal_validasi_borang_selesai) {
            $stats['durasi_validasi_dokumen'] = $pengajuan->tanggal_validasi_borang_assigned->diffInDays($pengajuan->tanggal_validasi_borang_selesai);
        }

        if ($pengajuan->tanggal_penugasan_asesor_ak && $pengajuan->tanggal_pelaporan_ak) {
            $stats['durasi_ak'] = $pengajuan->tanggal_penugasan_asesor_ak->diffInDays($pengajuan->tanggal_pelaporan_ak);
        }

        if ($pengajuan->tanggal_penugasan_asesor_al && $pengajuan->tanggal_pelaporan_al) {
            $stats['durasi_al'] = $pengajuan->tanggal_penugasan_asesor_al->diffInDays($pengajuan->tanggal_pelaporan_al);
        }

        // Total dokumen terarsip
        $stats['total_dokumen'] = $pengajuan->dokumen->where('is_latest', true)->count();

        return $stats;
    }
}
