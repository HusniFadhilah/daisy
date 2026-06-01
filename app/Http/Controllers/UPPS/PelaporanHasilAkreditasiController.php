<?php
// app/Http/Controllers/UPPS/PelaporanHasilAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PelaporanHasilAkreditasiController extends Controller
{
    /**
     * Display list of pelaporan hasil akreditasi
     */
    public function index(Request $request)
    {
        $statusLogs = [
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ];
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', $statusLogs)->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_pelaporan_hasil')->whereExists(function ($q) use ($statusLogs) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', $statusLogs);
            });

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'tanggal_pelaporan_hasil'), $request->get('sort_order', 'desc'))
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

        return view('upps.pelaporan-hasil-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail pelaporan hasil akreditasi
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'sertifikat',
                'sertifikat_banding',
                'sk_akreditasi',
                'sk_penetapan',
                'laporan_hasil',
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        // Check access
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        $hasil = $pengajuan->asesmen->hasil ?? null;
        $peringkat = $hasil->peringkat_akreditasi_final ?? null;

        return view('upps.pelaporan-hasil-akreditasi.show', compact('pengajuan', 'hasil', 'peringkat'));
    }

    public function previewSertifikat($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.hasil.statusFinal',
            'asesmen.hasil.statusAl',
            'asesmen.hasil.statusAk',
        ])->findOrFail($id);

        $user = Auth::user();
        if (!$user->studyPrograms()->pluck('study_programs.id')->contains($pengajuan->id_program_studi)) {
            abort(403);
        }

        $hasil = $pengajuan->asesmen->hasil;
        $tanggalPenetapan = ($pengajuan->hasBanding() ? $pengajuan->tanggal_sertifikat_banding : null)
            ?? $pengajuan->tanggal_sertifikat_pelaporan
            ?? $pengajuan->tanggal_sertifikat
            ?? $pengajuan->tanggal_penetapan;
        $masaBerlakuTahun = ($pengajuan->hasBanding() ? $pengajuan->masa_berlaku_tahun_banding : null)
            ?? $pengajuan->masa_berlaku_tahun_pelaporan
            ?? $pengajuan->masa_berlaku_tahun
            ?? $hasil?->statusFinal?->siklus_tahun
            ?? $hasil?->statusAl?->siklus_tahun
            ?? $hasil?->statusAk?->siklus_tahun
            ?? 1;

        $tanggalMulai    = \Carbon\Carbon::parse($tanggalPenetapan);
        $tanggalBerakhir = $tanggalMulai->copy()->addYears($masaBerlakuTahun);
        $masaBerlaku     = [
            'tahun'            => $masaBerlakuTahun,
            'tanggal_mulai'    => $tanggalMulai,
            'tanggal_berakhir' => $tanggalBerakhir,
        ];

        $elemenList = ($hasil?->detail_skor_al ?? [])['elemen'] ?? [];

        $resumeRaw = $hasil
            ? $hasil->getResumeAsesmenOrDefault()
            : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

        $resume = $resumeRaw;
        if (!isset($resume['bab']) || !is_array($resume['bab'])) {
            $resume['bab'] = [];
        }

        return view('de.pelaporan-hasil-akreditasi.sertifikat-pdf', [
            'pengajuan'        => $pengajuan,
            'hasil'            => $hasil,
            'studyProgram'     => $pengajuan->studyProgram,
            'university'       => $pengajuan->studyProgram->university,
            'nomorSertifikat'  => ($pengajuan->hasBanding() ? $pengajuan->nomor_sertifikat_banding : null)
                ?? $pengajuan->nomor_sertifikat_pelaporan
                ?? $pengajuan->nomor_sertifikat
                ?? $pengajuan->generateNomorSertifikat(),
            'tanggalPenetapan' => $tanggalPenetapan,
            'masaBerlaku'      => $masaBerlaku,
            'elemenList'       => $elemenList,
            'resume'           => $resume,
            'forPdf'           => false,
            'downloadUrl'      => route('upps.pelaporan-hasil-akreditasi.download-sertifikat', $id),
        ]);
    }

    public function downloadSertifikat($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.hasil.statusFinal',
            'asesmen.hasil.statusAl',
            'asesmen.hasil.statusAk',
        ])->findOrFail($id);

        $user = Auth::user();
        if (!$user->studyPrograms()->pluck('study_programs.id')->contains($pengajuan->id_program_studi)) {
            abort(403);
        }

        $hasil = $pengajuan->asesmen->hasil;
        $tanggalPenetapan = ($pengajuan->hasBanding() ? $pengajuan->tanggal_sertifikat_banding : null)
            ?? $pengajuan->tanggal_sertifikat_pelaporan
            ?? $pengajuan->tanggal_sertifikat
            ?? $pengajuan->tanggal_penetapan;
        $masaBerlakuTahun = ($pengajuan->hasBanding() ? $pengajuan->masa_berlaku_tahun_banding : null)
            ?? $pengajuan->masa_berlaku_tahun_pelaporan
            ?? $pengajuan->masa_berlaku_tahun
            ?? $hasil?->statusFinal?->siklus_tahun
            ?? $hasil?->statusAl?->siklus_tahun
            ?? $hasil?->statusAk?->siklus_tahun
            ?? 1;

        $tanggalMulai    = \Carbon\Carbon::parse($tanggalPenetapan);
        $tanggalBerakhir = $tanggalMulai->copy()->addYears($masaBerlakuTahun);
        $masaBerlaku     = [
            'tahun'            => $masaBerlakuTahun,
            'tanggal_mulai'    => $tanggalMulai,
            'tanggal_berakhir' => $tanggalBerakhir,
        ];

        $elemenList = ($hasil?->detail_skor_al ?? [])['elemen'] ?? [];

        $resumeRaw = $hasil
            ? $hasil->getResumeAsesmenOrDefault()
            : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

        $resume = $resumeRaw;
        if (!isset($resume['bab']) || !is_array($resume['bab'])) {
            $resume['bab'] = [];
        }

        $nomorSertifikat = ($pengajuan->hasBanding() ? $pengajuan->nomor_sertifikat_banding : null)
            ?? $pengajuan->nomor_sertifikat_pelaporan
            ?? $pengajuan->nomor_sertifikat
            ?? $pengajuan->generateNomorSertifikat();

        $pdf = Pdf::loadView('de.pelaporan-hasil-akreditasi.sertifikat-pdf', [
            'pengajuan'        => $pengajuan,
            'hasil'            => $hasil,
            'studyProgram'     => $pengajuan->studyProgram,
            'university'       => $pengajuan->studyProgram->university,
            'nomorSertifikat'  => $nomorSertifikat,
            'tanggalPenetapan' => $tanggalPenetapan,
            'masaBerlaku'      => $masaBerlaku,
            'elemenList'       => $elemenList,
            'resume'           => $resume,
            'forPdf'           => true,
            'downloadUrl'      => null,
        ])->setPaper('A4', 'landscape');

        $fileName = 'Sertifikat_Akreditasi_' . str_replace(' ', '_', $pengajuan->studyProgram->name) . '.pdf';
        return $pdf->download($fileName);
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
        // Total hasil yang sudah dilaporkan
        $total = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_pelaporan_hasil')
            ->count();

        // Arsip disimpan
        $arsipDisimpan = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penyimpanan')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])
            ->count();

        // Proses selesai
        $selesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_SELESAI)
            ->count();

        return [
            'total' => $total,
            'arsip_disimpan' => $arsipDisimpan,
            'selesai' => $selesai,
        ];
    }
}
