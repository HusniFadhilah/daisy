<?php
// app/Http/Controllers/DE/PenetapanHasilAkreditasiController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\HasilAkreditasi;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenetapanHasilAkreditasiController extends Controller
{
    /**
     * Display list of pengajuan yang siap untuk penetapan hasil
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
                    PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                    PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                    PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                ])->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ Filter: yang siap untuk penetapan
            // Yaitu: masa sanggah selesai (tanpa banding) ATAU banding sudah dilaporkan
            ->where(function ($q) {
                // Sudah masa sanggah dan tidak ada banding
                $q->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI)
                    ->whereNull('tanggal_pelaporan_banding');
            })
            ->orWhere(function ($q) {
                // Atau banding sudah dilaporkan
                $q->where('status', PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN);
            })
            ->orWhere(function ($q) {
                // Atau sudah ditetapkan
                $q->where('status', PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN);
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

        // Filter by peringkat
        if ($request->filled('peringkat')) {
            $query->where(function ($q) use ($request) {
                // Cek di pengajuan (jika ada banding yang diterima)
                $q->where('peringkat_final', $request->peringkat)
                    // Atau cek di hasil akreditasi
                    ->orWhereHas('asesmen.hasil', function ($hq) use ($request) {
                        $hq->where('peringkat_akreditasi', $request->peringkat);
                    });
            });
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
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
        ])
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        return view('de.penetapan-hasil-akreditasi.index', compact(
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
            'studyProgram.category',
            'pengaju',
            'asesmen.hasil',
            'asesmen.asesmenKecukupan.validators',
            'asesmen.asesmenLapangan.validators',
            'dokumen' => function ($q) {
                $q->whereIn('jenis_dokumen', ['laporan_ak', 'laporan_al', 'laporan_banding', 'sertifikat'])
                    ->where('is_latest', true)
                    ->orderBy('created_at', 'desc');
            },
            'statusLog',
        ])->findOrFail($id);

        return view('de.penetapan-hasil-akreditasi.show', compact('pengajuan'));
    }

    /**
     * Tetapkan hasil akreditasi
     */
    public function tetapkanHasil(Request $request, $id)
    {
        $request->validate([
            'catatan_penetapan' => 'nullable|string|max:2000',
        ]);

        $pengajuan = PengajuanAkreditasi::with('asesmen.hasil')->findOrFail($id);

        // Validasi status
        if (!in_array($pengajuan->status, [
            PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
            PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
        ])) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk penetapan hasil.');
        }

        // Validasi harus punya hasil akreditasi
        if (!$pengajuan->asesmen || !$pengajuan->asesmen->hasil) {
            return back()->with('error', 'Hasil akreditasi belum tersedia.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $pengajuan->status;

            // Determine final result
            $peringkatFinal = null;
            $skorFinal = null;

            // Jika ada banding yang diterima, gunakan hasil banding
            if ($pengajuan->hasil_banding === 'diterima') {
                $peringkatFinal = $pengajuan->peringkat_final;
                $skorFinal = $pengajuan->skor_final;
            } else {
                // Jika tidak ada banding atau banding ditolak, gunakan hasil AL
                $hasil = $pengajuan->asesmen->hasil;
                $peringkatFinal = $hasil->peringkat_akreditasi;
                $skorFinal = $hasil->skor_final;
            }

            // Update pengajuan
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                'tanggal_penetapan' => now(),
                'peringkat_final' => $peringkatFinal,
                'skor_final' => $skorFinal,
            ]);

            // Update HasilAkreditasi status jika belum published
            if ($pengajuan->asesmen->hasil->status !== 'published') {
                $pengajuan->asesmen->hasil->update([
                    'status' => 'published',
                    'catatan_perhitungan' => $request->catatan_penetapan
                        ? 'Penetapan: ' . $request->catatan_penetapan
                        : 'Hasil akreditasi telah ditetapkan.',
                ]);
            }

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => $oldStatus,
                'status_to' => PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Hasil akreditasi ditetapkan: ' . $peringkatFinal . ' (Skor: ' . $skorFinal . '). ' . ($request->catatan_penetapan ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.penetapan-hasil-akreditasi.show', $id)
                ->with('success', 'Hasil akreditasi berhasil ditetapkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menetapkan hasil: ' . $e->getMessage());
        }
    }

    /**
     * Batalkan penetapan (rollback)
     */
    public function batalkanPenetapan(Request $request, $id)
    {
        $request->validate([
            'alasan_pembatalan' => 'required|string|max:1000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN) {
            return back()->with('error', 'Hanya hasil yang telah ditetapkan yang dapat dibatalkan.');
        }

        DB::beginTransaction();
        try {
            // Tentukan status sebelumnya
            $previousStatus = $pengajuan->hasil_banding
                ? PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN
                : PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI;

            // Rollback status
            $pengajuan->update([
                'status' => $previousStatus,
                'tanggal_penetapan' => null,
            ]);

            // Update hasil akreditasi status
            if ($pengajuan->asesmen && $pengajuan->asesmen->hasil) {
                $pengajuan->asesmen->hasil->update([
                    'status' => 'final_combined',
                ]);
            }

            // Log
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                'status_to' => $previousStatus,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Penetapan dibatalkan. Alasan: ' . $request->alasan_pembatalan,
            ]);

            DB::commit();

            return redirect()
                ->route('de.penetapan-hasil-akreditasi.show', $id)
                ->with('success', 'Penetapan hasil berhasil dibatalkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan penetapan: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        $stats = [
            'total' => 0,
            'menunggu_penetapan' => 0,
            'sudah_ditetapkan' => 0,
            'unggul' => 0,
            'baik_sekali' => 0,
            'baik' => 0,
            'tidak_terakreditasi' => 0,
        ];

        // Total yang siap penetapan atau sudah ditetapkan
        $stats['total'] = PengajuanAkreditasi::where(function ($q) {
            $q->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI)
                ->whereNull('tanggal_pelaporan_banding');
        })
            ->orWhere('status', PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN)
            ->orWhere('status', PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN)
            ->count();

        // Menunggu penetapan
        $stats['menunggu_penetapan'] = PengajuanAkreditasi::where(function ($q) {
            $q->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI)
                ->whereNull('tanggal_pelaporan_banding');
        })
            ->orWhere('status', PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN)
            ->count();

        // Sudah ditetapkan
        $stats['sudah_ditetapkan'] = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN)
            ->count();

        // Distribusi peringkat (dari yang sudah ditetapkan)
        $peringkatDist = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN)
            ->select('peringkat_final', DB::raw('count(*) as total'))
            ->groupBy('peringkat_final')
            ->pluck('total', 'peringkat_final');

        $stats['unggul'] = $peringkatDist['Unggul'] ?? 0;
        $stats['baik_sekali'] = $peringkatDist['Baik Sekali'] ?? 0;
        $stats['baik'] = $peringkatDist['Baik'] ?? 0;
        $stats['tidak_terakreditasi'] = $peringkatDist['Tidak Terakreditasi'] ?? 0;

        return $stats;
    }
}
