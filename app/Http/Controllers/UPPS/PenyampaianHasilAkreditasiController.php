<?php
// app/Http/Controllers/UPPS/PenyampaianHasilAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PenyampaianHasilAkreditasiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
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
            ->whereNotNull('tanggal_hasil_akreditasi_dikirim')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
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

        return view('upps.penyampaian-hasil-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'asesmen.asesmenLapangan',
            'asesmen.hasil',
            'dokumen' => fn($q) => $q->whereIn('jenis_dokumen', [
                'sertifikat',
                'sertifikat_akreditasi',
                'sk_akreditasi',
            ])->orderBy('created_at', 'desc'),
            'statusLog' => fn($q) => $q->orderBy('changed_at', 'desc'),
        ])->findOrFail($id);

        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
            abort(403, 'Anda tidak memiliki akses ke permohonan ini.');
        }

        $hasil = $pengajuan->asesmen->hasil ?? null;
        $peringkat = $hasil->peringkat_akreditasi ?? null;

        // ✅ Get detail skor per elemen
        $elemenList = [];
        if ($hasil && $hasil->detail_skor_al) {
            $detailSkorAL = $hasil->detail_skor_al;
            $elemenList = $detailSkorAL['elemen'] ?? [];
        }

        // ✅ Get berita acara
        $beritaAcara = null;
        if ($pengajuan->asesmen) {
            $beritaAcara = AsesmenDocument::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        return view('upps.penyampaian-hasil-akreditasi.show', compact(
            'pengajuan',
            'hasil',
            'peringkat',
            'elemenList',
            'beritaAcara'
        ));
    }

    /**
     * ✅ Download Berita Acara
     */
    public function downloadBeritaAcara($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            $user = Auth::user();
            $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

            if (!$studyProgramIds->contains($pengajuan->id_program_studi)) {
                abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
            }

            $asesmen = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYAMPAIAN_HASIL)
                ->where('is_active', true)
                ->latest()
                ->firstOrFail();

            if (!Storage::disk('public')->exists($beritaAcara->path)) {
                throw new \Exception('File tidak ditemukan.');
            }

            return Storage::disk('public')->download(
                $beritaAcara->path,
                $beritaAcara->original_name
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal download: ' . $e->getMessage());
        }
    }

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
                        fn($ssq) => $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        // ✅ FIX: Filter by peringkat (smart: prioritas banding)
        $query->when($request->filled('peringkat'), function ($q) use ($request) {
            $peringkat = $request->peringkat;
            $q->where(function ($sq) use ($peringkat) {
                $sq->where('peringkat_hasil_banding', $peringkat)
                    ->orWhere(function ($ssq) use ($peringkat) {
                        $ssq->whereNull('peringkat_hasil_banding')
                            ->where('peringkat_hasil', $peringkat);
                    });
            });
        });
    }

    /**
     * ✅ Calculate statistics dengan smart grouping
     */
    private function calculateStatistics($studyProgramIds): array
    {
        $totalHasilAkreditasi = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_hasil_akreditasi_dikirim')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])
            ->count();

        $prosesSelesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_SELESAI)
            ->count();

        // ✅ Smart grouping: prioritas hasil banding
        $pengajuans = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_hasil_akreditasi_dikirim')
            ->get();

        $byPeringkat = $pengajuans->groupBy(function ($item) {
            return $item->peringkat_hasil_banding ?? $item->peringkat_hasil ?? 'Tidak Ada';
        })->map(fn($group) => $group->count())->toArray();

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
