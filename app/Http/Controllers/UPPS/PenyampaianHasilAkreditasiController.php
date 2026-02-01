<?php
// app/Http/Controllers/UPPS/PenyampaianHasilAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PenyampaianHasilAkreditasiController extends Controller
{
    /**
     * Display list of penyampaian hasil akreditasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_hasil_akreditasi')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ]);

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

        return view('upps.penyampaian-hasil-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail penyampaian hasil akreditasi
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
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        return view('upps.penyampaian-hasil-akreditasi.show', compact('pengajuan'));
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

        // Filter by peringkat
        $query->when($request->filled('peringkat'), function($q) use ($request) {
            $q->where('peringkat_hasil', $request->peringkat);
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Total hasil akreditasi yang sudah disampaikan
        $totalHasilAkreditasi = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_hasil_akreditasi')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])
            ->count();

        // Proses selesai
        $prosesSelesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_SELESAI)
            ->count();

        // Group by peringkat (hanya yang sudah ada peringkat)
        $byPeringkat = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('peringkat_hasil')
            ->whereNotNull('tanggal_hasil_akreditasi')
            ->select('peringkat_hasil', DB::raw('count(*) as total'))
            ->groupBy('peringkat_hasil')
            ->get()
            ->pluck('total', 'peringkat_hasil')
            ->toArray();

        return [
            'total' => $totalHasilAkreditasi,
            'selesai' => $prosesSelesai,
            'unggul' => $byPeringkat['Unggul'] ?? 0,
            'baik_sekali' => $byPeringkat['Baik Sekali'] ?? 0,
            'baik' => $byPeringkat['Baik'] ?? 0,
            'tidak_terakreditasi' => $byPeringkat['Tidak Terakreditasi'] ?? 0,
        ];
    }
}
