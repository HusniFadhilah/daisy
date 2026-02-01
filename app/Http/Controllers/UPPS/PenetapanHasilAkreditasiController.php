<?php
// app/Http/Controllers/UPPS/PenetapanHasilAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenetapanHasilAkreditasiController extends Controller
{
    /**
     * Display list of penetapan hasil akreditasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penetapan')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'tanggal_penetapan'), $request->get('sort_order', 'desc'))
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

        return view('upps.penetapan-hasil-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail penetapan hasil akreditasi
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'sertifikat_akreditasi',
                'sk_akreditasi',
                'hasil_banding',
                'sk_penetapan',
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.penetapan-hasil-akreditasi.show', compact('pengajuan'));
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
                    ->orWhereHas('studyProgram', fn($ssq) =>
                        $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        // Filter by status
        $query->when($request->filled('status'), function($q) use ($request) {
            $q->where('status', $request->status);
        });

        // Filter by peringkat hasil akhir (bisa berbeda dari peringkat awal)
        $query->when($request->filled('peringkat'), function($q) use ($request) {
            // Cek apakah ada peringkat hasil banding, jika tidak gunakan peringkat hasil biasa
            $q->where(function($sq) use ($request) {
                $sq->where('peringkat_hasil_banding', $request->peringkat)
                   ->orWhere(function($ssq) use ($request) {
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
        // Total hasil yang sudah ditetapkan
        $total = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penetapan')
            ->count();

        // Hasil sudah diumumkan
        $diumumkan = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_pengumuman')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])
            ->count();

        // Proses selesai
        $selesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_SELESAI)
            ->count();

        return [
            'total' => $total,
            'diumumkan' => $diumumkan,
            'selesai' => $selesai,
        ];
    }
}
