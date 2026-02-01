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
            'statusLog' => function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                    PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                    PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                    PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
                ])->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ basis list: pernah masuk fase surat
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', [
                        PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                        PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
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
        // Ambil pengajuan yang sudah template_led_dikirim tapi belum ada invoice
        $pengajuanList = \App\Models\PengajuanAkreditasi::with('studyProgram.degreeLevel', 'studyProgram.university')
            ->where('status', \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM)
            ->whereDoesntHave('pembayaran')
            ->get();
        $countPengajuanList = count($pengajuanList);
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
            'tahunList',
            'countPengajuanList'
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
            return back()->with('error', 'Status permohonan saat ini tidak sesuai untuk menerima permohonan akreditasi PS.');
        }

        // Check apakah dokumen surat permohonan sudah ada
        $hasSuratPermohonan = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_permohonan')
            ->where('is_latest', true)
            ->exists();

        if (!$hasSuratPermohonan) {
            return back()->with('error', 'Dokumen permohonan akreditasi belum diupload oleh PS.');
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
                'status_from' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                'status_to' => PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => $request->keterangan ?? 'Permohonan akreditasi dari PS, ditanggapi oleh LAMDEPILAR',
            ]);

            DB::commit();

            return redirect()
                ->route('de.surat-permohonan')
                ->with('success', 'Permohonan berhasil ditanggapi. Status diubah menjadi "Permohonan Akreditasi Ditanggapi".');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menerima permohonan akreditasi: ' . $e->getMessage());
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
                'keterangan' => 'Permohonan akreditasi dari PS belum diterima: ' . $request->alasan_penolakan,
            ]);

            DB::commit();

            return redirect()
                ->route('de.surat-permohonan')
                ->with('success', 'Permohonan akreditasi belum diterima.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak permohonan akreditasi: ' . $e->getMessage());
        }
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        // Ambil semua status log untuk pengajuan yang relevan
        $logs = DB::table('pengajuan_status_log')
            ->select('id_pengajuan', 'status_to')
            ->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK,
                PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM,
            ])
            ->get();

        $stats = [
            'total' => 0,
            'menunggu' => 0,
            'dikirim' => 0,
            'diterima' => 0,
            'ditolak' => 0,
        ];

        // Kelompokkan log berdasarkan id_pengajuan
        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // Total: pernah ada status terkait
            $stats['total']++;

            // Menunggu: ada PENGINGAT_DIKIRIM, tapi belum SURAT_PERMOHONAN_DIKIRIM
            if (
                in_array(PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM, $statuses)
            ) {
                $stats['menunggu']++;
            }

            // Dikirim: ada SURAT_PERMOHONAN_DIKIRIM, tapi belum diterima / ditolak
            if (
                in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK, $statuses)
            ) {
                $stats['dikirim']++;
            }

            // Diterima
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses)) {
                $stats['diterima']++;
            }

            // Ditolak
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK, $statuses)) {
                $stats['ditolak']++;
            }
        }

        return $stats;
    }
}
