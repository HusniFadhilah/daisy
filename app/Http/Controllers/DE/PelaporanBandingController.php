<?php
// app/Http/Controllers/DE/PelaporanBandingController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PelaporanBandingController extends Controller
{
    /**
     * Display list of pengajuan yang perlu/sudah dilaporkan
     */
    public function index(Request $request)
    {
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'asesmen.hasil',
            'dokumen' => function ($q) {
                $q->where('jenis_dokumen', 'laporan_banding')
                    ->where('is_latest', true);
            },
            'statusLog' => function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                ])->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ Filter: yang sudah punya hasil banding (sudah selesai pelaksanaan)
            ->whereNotNull('hasil_banding')
            // ✅ Dan pernah masuk fase pelaporan
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', [
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
        $sortBy = $request->get('sort_by', 'tanggal_pelaksanaan_banding');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'tanggal_pelaksanaan_banding') {
            $query->orderByRaw('COALESCE(tanggal_pelaksanaan_banding, tanggal_pelaporan_banding, created_at) ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::whereNotNull('hasil_banding')
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        return view('de.pelaporan-banding.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList'
        ));
    }

    /**
     * Show detail pelaporan banding
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'asesmen.hasil',
            'dokumen' => function ($q) {
                $q->whereIn('jenis_dokumen', ['laporan_ak', 'laporan_al', 'laporan_banding', 'lainnya'])
                    ->where('is_latest', true)
                    ->orderBy('created_at', 'desc');
            },
            'statusLog',
        ])->findOrFail($id);

        // Check if has hasil banding
        if (!$pengajuan->hasil_banding) {
            return redirect()
                ->route('de.pelaporan-banding')
                ->with('error', 'Pengajuan ini belum memiliki hasil banding.');
        }

        return view('de.pelaporan-banding.show', compact('pengajuan'));
    }

    /**
     * Upload laporan banding
     */
    public function uploadLaporan(Request $request, $id)
    {
        $request->validate([
            'file_laporan' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk upload laporan banding.');
        }

        // Validasi hasil banding harus sudah ada
        if (!$pengajuan->hasil_banding) {
            return back()->with('error', 'Hasil banding belum ditentukan.');
        }

        DB::beginTransaction();
        try {
            // Upload file
            $file = $request->file('file_laporan');
            $filename = 'laporan_banding_' . $pengajuan->nomor_pengajuan . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengajuan_dokumen/laporan_banding', $filename, 'public');

            // Mark previous laporan as not latest
            PengajuanDokumen::where('id_pengajuan', $id)
                ->where('jenis_dokumen', 'laporan_banding')
                ->update(['is_latest' => false]);

            // Create new dokumen record
            $dokumen = PengajuanDokumen::create([
                'id_pengajuan' => $id,
                'jenis_dokumen' => 'laporan_banding',
                'nama_file' => $filename,
                'path_file' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'keterangan' => $request->keterangan,
                'versi' => PengajuanDokumen::where('id_pengajuan', $id)
                    ->where('jenis_dokumen', 'laporan_banding')
                    ->max('versi') + 1,
                'is_latest' => true,
            ]);

            // Update status ke BANDING_DILAPORKAN
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                'tanggal_pelaporan_banding' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                'status_to' => PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Laporan banding diupload. ' . ($request->keterangan ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.pelaporan-banding.show', $id)
                ->with('success', 'Laporan banding berhasil diupload. Status diubah menjadi "Banding Dilaporkan".');
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return back()->with('error', 'Gagal upload laporan banding: ' . $e->getMessage());
        }
    }

    /**
     * Download laporan banding
     */
    public function downloadLaporan($id)
    {
        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'laporan_banding')
            ->where('is_latest', true)
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            return back()->with('error', 'File laporan tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->path_file, $dokumen->original_filename);
    }

    /**
     * Get timeline data via AJAX
     */
    public function getTimeline($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'statusLog' => function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                    PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                    PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                ])
                    ->orderBy('changed_at', 'asc');
            }
        ])->findOrFail($id);

        $timeline = $pengajuan->statusLog->map(function ($log) {
            return [
                'status' => \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to,
                'date' => $log->changed_at->locale('id')->translatedFormat('d M Y H:i'),
                'keterangan' => $log->keterangan,
                'changed_by' => $log->changedBy->name ?? '-',
            ];
        });

        return response()->json([
            'success' => true,
            'timeline' => $timeline,
        ]);
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        $stats = [
            'total' => 0,
            'belum_dilaporkan' => 0,
            'sudah_dilaporkan' => 0,
            'diterima' => 0,
            'ditolak' => 0,
        ];

        // Total: yang punya hasil_banding
        $stats['total'] = PengajuanAkreditasi::whereNotNull('hasil_banding')->count();

        // Belum dilaporkan: status = BANDING_DILAKSANAKAN
        $stats['belum_dilaporkan'] = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
            ->whereNotNull('hasil_banding')
            ->count();

        // Sudah dilaporkan: status = BANDING_DILAPORKAN
        $stats['sudah_dilaporkan'] = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN)
            ->count();

        // Hasil banding
        $hasilBanding = PengajuanAkreditasi::whereNotNull('hasil_banding')
            ->select('hasil_banding', DB::raw('count(*) as total'))
            ->groupBy('hasil_banding')
            ->pluck('total', 'hasil_banding');

        $stats['diterima'] = $hasilBanding['diterima'] ?? 0;
        $stats['ditolak'] = $hasilBanding['ditolak'] ?? 0;

        return $stats;
    }
}
