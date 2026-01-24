<?php
// app/Http/Controllers/DE/PelaksanaanBandingController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\HasilAkreditasi;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelaksanaanBandingController extends Controller
{
    /**
     * Display list of pengajuan with banding
     */
    public function index(Request $request)
    {
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'asesmen.hasil',
            'statusLog' => function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_MASA_SANGGAH,
                    PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                ])->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ Filter: hanya yang pernah masuk fase banding
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', [
                        PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                        PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                        PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                    ]);
            });

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

        // Filter by hasil banding
        if ($request->filled('hasil_banding')) {
            $query->where('hasil_banding', $request->hasil_banding);
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
        $sortBy = $request->get('sort_by', 'tanggal_banding');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'tanggal_banding') {
            $query->orderByRaw('COALESCE(tanggal_banding, created_at) ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::whereNotNull('tanggal_banding')
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        return view('de.pelaksanaan-banding.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList'
        ));
    }

    /**
     * Show detail pengajuan banding
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'asesmen.hasil',
            'asesmen.asesmenKecukupan.validators',
            'asesmen.asesmenLapangan.validators',
            'dokumen' => function ($q) {
                $q->whereIn('jenis_dokumen', ['laporan_ak', 'laporan_al', 'lainnya'])
                    ->where('is_latest', true);
            },
            'statusLog',
        ])->findOrFail($id);

        // Check if banding exists
        if (!$pengajuan->tanggal_banding) {
            return redirect()
                ->route('de.pelaksanaan-banding')
                ->with('error', 'Pengajuan ini belum mengajukan banding.');
        }

        return view('de.pelaksanaan-banding.show', compact('pengajuan'));
    }

    /**
     * Mulai pelaksanaan banding
     */
    public function mulaiPelaksanaan(Request $request, $id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk memulai pelaksanaan banding.');
        }

        DB::beginTransaction();
        try {
            // Update status
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                'tanggal_pelaksanaan_banding' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                'status_to' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => $request->keterangan ?? 'Memulai pelaksanaan banding',
            ]);

            DB::commit();

            return redirect()
                ->route('de.pelaksanaan-banding.show', $id)
                ->with('success', 'Pelaksanaan banding dimulai.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memulai pelaksanaan banding: ' . $e->getMessage());
        }
    }

    /**
     * Selesaikan pelaksanaan banding dan tentukan hasilnya
     */
    public function selesaikanPelaksanaan(Request $request, $id)
    {
        $request->validate([
            'hasil_banding' => 'required|in:diterima,ditolak',
            'peringkat_final' => 'required_if:hasil_banding,diterima|nullable|string',
            'skor_final' => 'required_if:hasil_banding,diterima|nullable|numeric|min:0|max:400',
            'catatan_hasil' => 'nullable|string|max:2000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk menyelesaikan pelaksanaan banding.');
        }

        DB::beginTransaction();
        try {
            // Update hasil banding di pengajuan
            $updateData = [
                'hasil_banding' => $request->hasil_banding,
                'catatan_hasil' => $request->catatan_hasil,
            ];

            // Jika banding diterima, update peringkat dan skor final
            if ($request->hasil_banding === 'diterima') {
                $updateData['peringkat_final'] = $request->peringkat_final;
                $updateData['skor_final'] = $request->skor_final;

                // Update di HasilAkreditasi juga
                if ($pengajuan->asesmen && $pengajuan->asesmen->hasil) {
                    $pengajuan->asesmen->hasil->update([
                        'skor_final' => $request->skor_final,
                        'peringkat_akreditasi' => $request->peringkat_final,
                        'catatan_perhitungan' => 'Hasil banding diterima. ' . ($request->catatan_hasil ?? ''),
                    ]);
                }
            }

            $pengajuan->update($updateData);

            // Status tetap di BANDING_DILAKSANAKAN, belum pindah ke BANDING_DILAPORKAN
            // Nanti pindah setelah membuat laporan di step 17

            // Log
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                'status_to' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Hasil banding: ' . $request->hasil_banding . '. ' . ($request->catatan_hasil ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.pelaksanaan-banding.show', $id)
                ->with('success', 'Hasil pelaksanaan banding berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan hasil banding: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        // Ambil semua status log untuk fase banding
        $logs = DB::table('pengajuan_status_log')
            ->select('id_pengajuan', 'status_to')
            ->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
            ])
            ->get();

        $stats = [
            'total' => 0,
            'diajukan' => 0,
            'sedang_dilaksanakan' => 0,
            'selesai' => 0,
            'diterima' => 0,
            'ditolak' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status banding
            $stats['total']++;

            // Diajukan: ada BANDING_DIAJUKAN, tapi belum DILAKSANAKAN
            if (
                in_array(PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN, $statuses)
            ) {
                $stats['diajukan']++;
            }

            // Sedang dilaksanakan: ada BANDING_DILAKSANAKAN, tapi belum DILAPORKAN
            if (
                in_array(PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN, $statuses)
            ) {
                $stats['sedang_dilaksanakan']++;
            }

            // Selesai: sudah DILAPORKAN
            if (in_array(PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN, $statuses)) {
                $stats['selesai']++;
            }
        }

        // Hitung hasil banding (diterima/ditolak) dari tabel pengajuan
        $hasilBanding = PengajuanAkreditasi::whereNotNull('hasil_banding')
            ->select('hasil_banding', DB::raw('count(*) as total'))
            ->groupBy('hasil_banding')
            ->pluck('total', 'hasil_banding');

        $stats['diterima'] = $hasilBanding['diterima'] ?? 0;
        $stats['ditolak'] = $hasilBanding['ditolak'] ?? 0;

        return $stats;
    }
}
