<?php
// app/Http/Controllers/DE/PelaporanHasilAkreditasiController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\University;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PelaporanHasilAkreditasiController extends Controller
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
                $q->whereIn('jenis_dokumen', ['laporan_hasil', 'sertifikat'])
                    ->where('is_latest', true);
            },
            'statusLog' => function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                ])->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ Filter: yang sudah ditetapkan atau lebih lanjut
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('pengajuan_status_log as l')
                    ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                    ->whereIn('l.status_to', [
                        PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                        PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                        PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
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

        // Filter by peringkat
        if ($request->filled('peringkat')) {
            $query->where('peringkat_final', $request->peringkat);
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
        $sortBy = $request->get('sort_by', 'tanggal_penetapan');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'tanggal_penetapan') {
            $query->orderByRaw('COALESCE(tanggal_penetapan, created_at) ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
        ])
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        return view('de.pelaporan-hasil-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList'
        ));
    }

    /**
     * Show detail pelaporan hasil
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
                $q->whereIn('jenis_dokumen', [
                    'laporan_ak',
                    'laporan_al',
                    'laporan_banding',
                    'laporan_hasil',
                    'sertifikat',
                    'lainnya'
                ])
                    ->where('is_latest', true)
                    ->orderBy('created_at', 'desc');
            },
            'statusLog',
        ])->findOrFail($id);

        // Check if hasil sudah ditetapkan
        $allowed = [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
        ];
        $log = $pengajuan->latestRelevantStatusLog($allowed);
        if (!in_array($log?->status_to, $allowed)) {
            return redirect()
                ->route('de.pelaporan-hasil-akreditasi')
                ->with('error', 'Hasil akreditasi belum ditetapkan.');
        }

        $hasil = $pengajuan->asesmen->hasil ?? null;
        $peringkat = $hasil->peringkat_akreditasi_final ?? null;
        return view('de.pelaporan-hasil-akreditasi.show', compact('pengajuan', 'hasil', 'peringkat'));
    }

    /**
     * Upload laporan hasil akreditasi
     */
    // public function uploadLaporan(Request $request, $id)
    // {
    //     $request->validate([
    //         'file_laporan' => 'required|file|mimes:pdf,doc,docx|max:10240',
    //         'keterangan' => 'nullable|string|max:1000',
    //     ]);

    //     $pengajuan = PengajuanAkreditasi::findOrFail($id);

    //     // Validasi status
    //     if (!in_array($pengajuan->status, [
    //         PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
    //         PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
    //     ])) {
    //         return back()->with('error', 'Status saat ini tidak sesuai untuk upload laporan hasil.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Upload file
    //         $file = $request->file('file_laporan');
    //         $filename = 'laporan_hasil_' . $pengajuan->nomor_pengajuan . '_' . time() . '.' . $file->getClientOriginalExtension();
    //         $path = $file->storeAs('pengajuan_dokumen/laporan_hasil', $filename, 'public');

    //         // Mark previous laporan as not latest
    //         PengajuanDokumen::where('id_pengajuan', $id)
    //             ->where('jenis_dokumen', 'laporan_hasil')
    //             ->update(['is_latest' => false]);

    //         // Create new dokumen record
    //         PengajuanDokumen::create([
    //             'id_pengajuan' => $id,
    //             'jenis_dokumen' => 'laporan_hasil',
    //             'nama_file' => $filename,
    //             'path_file' => $path,
    //             'original_filename' => $file->getClientOriginalName(),
    //             'file_size' => $file->getSize(),
    //             'mime_type' => $file->getMimeType(),
    //             'uploaded_by' => auth()->id(),
    //             'keterangan' => $request->keterangan,
    //             'versi' => PengajuanDokumen::where('id_pengajuan', $id)
    //                 ->where('jenis_dokumen', 'laporan_hasil')
    //                 ->max('versi') + 1,
    //             'is_latest' => true,
    //         ]);

    //         DB::commit();

    //         return redirect()
    //             ->route('de.pelaporan-hasil-akreditasi.show', $id)
    //             ->with('success', 'Laporan hasil akreditasi berhasil diupload.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         // Delete uploaded file if exists
    //         if (isset($path) && Storage::disk('public')->exists($path)) {
    //             Storage::disk('public')->delete($path);
    //         }

    //         return back()->with('error', 'Gagal upload laporan: ' . $e->getMessage());
    //     }
    // }

    // /**
    //  * Upload sertifikat akreditasi
    //  */
    // public function uploadSertifikat(Request $request, $id)
    // {
    //     $request->validate([
    //         'file_sertifikat' => 'required|file|mimes:pdf|max:5120',
    //         'nomor_sertifikat' => 'nullable|string|max:100',
    //         'masa_berlaku_tahun' => 'nullable|integer|min:1|max:10',
    //         'keterangan' => 'nullable|string|max:1000',
    //     ]);

    //     $pengajuan = PengajuanAkreditasi::findOrFail($id);

    //     // Validasi status
    //     if (!in_array($pengajuan->status, [
    //         PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
    //         PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
    //     ])) {
    //         return back()->with('error', 'Status saat ini tidak sesuai untuk upload sertifikat.');
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Upload file
    //         $file = $request->file('file_sertifikat');
    //         $filename = 'sertifikat_' . $pengajuan->nomor_pengajuan . '_' . time() . '.pdf';
    //         $path = $file->storeAs('pengajuan_dokumen/sertifikat', $filename, 'public');

    //         // Mark previous sertifikat as not latest
    //         PengajuanDokumen::where('id_pengajuan', $id)
    //             ->where('jenis_dokumen', 'sertifikat')
    //             ->update(['is_latest' => false]);

    //         // Create new dokumen record
    //         PengajuanDokumen::create([
    //             'id_pengajuan' => $id,
    //             'jenis_dokumen' => 'sertifikat',
    //             'nama_file' => $filename,
    //             'path_file' => $path,
    //             'original_filename' => $file->getClientOriginalName(),
    //             'file_size' => $file->getSize(),
    //             'mime_type' => $file->getMimeType(),
    //             'uploaded_by' => auth()->id(),
    //             'keterangan' => $request->keterangan,
    //             'versi' => PengajuanDokumen::where('id_pengajuan', $id)
    //                 ->where('jenis_dokumen', 'sertifikat')
    //                 ->max('versi') + 1,
    //             'is_latest' => true,
    //         ]);

    //         // Update pengajuan jika ada info tambahan
    //         if ($request->filled('masa_berlaku_tahun')) {
    //             $pengajuan->update([
    //                 'masa_berlaku_tahun' => $request->masa_berlaku_tahun,
    //             ]);
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('de.pelaporan-hasil-akreditasi.show', $id)
    //             ->with('success', 'Sertifikat akreditasi berhasil diupload.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         // Delete uploaded file if exists
    //         if (isset($path) && Storage::disk('public')->exists($path)) {
    //             Storage::disk('public')->delete($path);
    //         }

    //         return back()->with('error', 'Gagal upload sertifikat: ' . $e->getMessage());
    //     }
    // }

    public function uploadDokumen(Request $request, $id)
    {
        // validasi "conditional": file boleh salah satu, tapi minimal ada salah satu
        $request->validate([
            'file_laporan' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'file_sertifikat' => 'nullable|file|mimes:pdf|max:5120',
            'masa_berlaku_tahun' => 'nullable|integer|min:1|max:10',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        if (!$request->hasFile('file_laporan') && !$request->hasFile('file_sertifikat')) {
            return back()->with('error', 'Minimal upload salah satu: Laporan Hasil atau Sertifikat.');
        }

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status (sama seperti method upload yang lama)
        if (!in_array($pengajuan->status, [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        ])) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk upload dokumen.');
        }

        DB::beginTransaction();

        // untuk rollback file kalau error
        $uploadedPaths = [];

        try {
            // === 1) Upload laporan (jika ada) ===
            if ($request->hasFile('file_laporan')) {
                $file = $request->file('file_laporan');
                $filename = 'laporan_hasil_' . $pengajuan->nomor_pengajuan . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pengajuan_dokumen/laporan_hasil', $filename, 'public');
                $uploadedPaths[] = $path;

                PengajuanDokumen::where('id_pengajuan', $id)
                    ->where('jenis_dokumen', 'laporan_hasil')
                    ->update(['is_latest' => false]);

                PengajuanDokumen::create([
                    'id_pengajuan' => $id,
                    'jenis_dokumen' => 'laporan_hasil',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                    'keterangan' => $request->keterangan,
                    'versi' => (PengajuanDokumen::where('id_pengajuan', $id)
                        ->where('jenis_dokumen', 'laporan_hasil')
                        ->max('versi') ?? 0) + 1,
                    'is_latest' => true,
                ]);
            }

            // === 2) Upload sertifikat (jika ada) ===
            if ($request->hasFile('file_sertifikat')) {
                $file = $request->file('file_sertifikat');
                $filename = 'sertifikat_' . $pengajuan->nomor_pengajuan . '_' . time() . '.pdf';
                $path = $file->storeAs('pengajuan_dokumen/sertifikat', $filename, 'public');
                $uploadedPaths[] = $path;

                PengajuanDokumen::where('id_pengajuan', $id)
                    ->where('jenis_dokumen', 'sertifikat')
                    ->update(['is_latest' => false]);

                PengajuanDokumen::create([
                    'id_pengajuan' => $id,
                    'jenis_dokumen' => 'sertifikat',
                    'nama_file' => $filename,
                    'path_file' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_by' => auth()->id(),
                    'keterangan' => $request->keterangan,
                    'versi' => (PengajuanDokumen::where('id_pengajuan', $id)
                        ->where('jenis_dokumen', 'sertifikat')
                        ->max('versi') ?? 0) + 1,
                    'is_latest' => true,
                ]);

                if ($request->filled('masa_berlaku_tahun')) {
                    $pengajuan->update([
                        'masa_berlaku_tahun' => $request->masa_berlaku_tahun,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('de.pelaporan-hasil-akreditasi.show', $id)
                ->with('success', 'Dokumen berhasil diupload.');
        } catch (\Exception $e) {
            DB::rollBack();

            // hapus file yang sudah sempat terupload
            foreach ($uploadedPaths as $p) {
                if (Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                }
            }

            return back()->with('error', 'Gagal upload dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Selesaikan pelaporan hasil
     */
    public function selesaikanPelaporan(Request $request, $id)
    {
        $request->validate([
            'catatan_pelaporan' => 'nullable|string|max:2000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if (!in_array($pengajuan->status, [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        ])) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk menyelesaikan pelaporan.');
        }

        // Validasi dokumen minimal: laporan hasil atau sertifikat
        $hasLaporan = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'laporan_hasil')
            ->where('is_latest', true)
            ->exists();

        $hasSertifikat = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'sertifikat')
            ->where('is_latest', true)
            ->exists();

        if (!$hasLaporan || !$hasSertifikat) {
            return back()->with('error', 'Wajib upload Laporan Hasil dan Sertifikat sebelum menyelesaikan pelaporan.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $pengajuan->status;

            // Update status
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                'tanggal_pelaporan_hasil' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => $oldStatus,
                'status_to' => PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Pelaporan hasil akreditasi selesai. ' . ($request->catatan_pelaporan ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.pelaporan-hasil-akreditasi.show', $id)
                ->with('success', 'Pelaporan hasil akreditasi berhasil diselesaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyelesaikan pelaporan: ' . $e->getMessage());
        }
    }

    /**
     * Download dokumen
     */
    public function downloadDokumen($id, $jenisdokumen)
    {
        $pengajuanDokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', $jenisdokumen)
            ->where('is_latest', true)
            ->firstOrFail();

        return $pengajuanDokumen->downloadDokumen();
    }

    /**
     * Get timeline data via AJAX
     */
    public function getTimeline($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'statusLog' => function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
                    PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
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
            'punya_sertifikat' => 0,
            'unggul' => 0,
            'baik_sekali' => 0,
            'baik' => 0,
            'tidak_terakreditasi' => 0,
        ];

        // Total yang sudah ditetapkan
        $stats['total'] = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
        ])->count();

        // Belum dilaporkan
        $stats['belum_dilaporkan'] = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        ])->count();

        // Sudah dilaporkan
        $stats['sudah_dilaporkan'] = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
            ->count();

        // Punya sertifikat
        $stats['punya_sertifikat'] = PengajuanDokumen::where('jenis_dokumen', 'sertifikat')
            ->where('is_latest', true)
            ->distinct('id_pengajuan')
            ->count('id_pengajuan');

        // Distribusi peringkat
        // $peringkatDist = PengajuanAkreditasi::whereIn('status', [
        //     PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
        //     PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        //     PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
        // ])
        //     ->select('peringkat_final', DB::raw('count(*) as total'))
        //     ->groupBy('peringkat_final')
        //     ->pluck('total', 'peringkat_final');

        // $stats['unggul'] = $peringkatDist['Unggul'] ?? 0;
        // $stats['baik_sekali'] = $peringkatDist['Baik Sekali'] ?? 0;
        // $stats['baik'] = $peringkatDist['Baik'] ?? 0;
        // $stats['tidak_terakreditasi'] = $peringkatDist['Tidak Terakreditasi'] ?? 0;

        return $stats;
    }



    /**
     * ✅ Generate Sertifikat Akreditasi (PDF)
     */
    public function generateSertifikat($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.hasil',
            ])->findOrFail($id);

            // Validate: Harus sudah ditetapkan
            if (!$pengajuan->tanggal_penetapan) {
                return back()->with('error', 'Hasil belum ditetapkan. Sertifikat hanya dapat digenerate setelah pelaporan.');
            }

            $asesmen = $pengajuan->asesmen;
            $hasil = $asesmen->hasil;

            // Generate nomor sertifikat (jika belum ada)
            if (!$pengajuan->nomor_sertifikat) {
                $nomorSertifikat = $this->generateNomorSertifikat($pengajuan);
                $pengajuan->update(['nomor_sertifikat' => $nomorSertifikat]);
            }

            // Calculate masa berlaku berdasarkan peringkat
            $masaBerlaku = $this->calculateMasaBerlaku($hasil->peringkat_akreditasi_final, $pengajuan->tanggal_penetapan);
            // Parse detail skor
            $detailSkorAL = $hasil->detail_skor_al ?? [];
            $elemenList = $detailSkorAL['elemen'] ?? [];
            $data = [
                'pengajuan' => $pengajuan,
                'hasil' => $hasil,
                'studyProgram' => $pengajuan->studyProgram,
                'university' => $pengajuan->studyProgram->university,
                'nomorSertifikat' => $pengajuan->nomor_sertifikat,
                'tanggalPenetapan' => $pengajuan->tanggal_penetapan,
                'masaBerlaku' => $masaBerlaku,
                'elemenList' => $elemenList,
            ];
            // Generate PDF
            $pdf = Pdf::loadView('de.pelaporan-hasil-akreditasi.sertifikat-pdf', $data);
            $pdf->setPaper('A4', 'landscape');

            $fileName = 'Sertifikat_Akreditasi_' . $pengajuan->studyProgram->code . '_' . time() . '.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Generate sertifikat failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal generate sertifikat: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Generate Nomor Sertifikat
     */
    private function generateNomorSertifikat($pengajuan): string
    {
        $tahun = $pengajuan->tanggal_penetapan->format('Y');
        $bulan = $pengajuan->tanggal_penetapan->format('m');

        // Format: XXXX/LAMDIK-SER/KAT/MM/YYYY
        // Example: 0001/LAMDIK-SER/S1/01/2025

        $count = PengajuanAkreditasi::whereYear('tanggal_penetapan', $tahun)
            ->whereMonth('tanggal_penetapan', $bulan)
            ->whereNotNull('nomor_sertifikat')
            ->count() + 1;

        $kategori = $pengajuan->studyProgram->degreeLevel->code ?? 'XX';

        return sprintf(
            '%04d/LAMDIK-SER/%s/%s/%s',
            $count,
            $kategori,
            $bulan,
            $tahun
        );
    }

    /**
     * ✅ Calculate Masa Berlaku Sertifikat
     */
    private function calculateMasaBerlaku($hasil, $tanggalPenetapan): array
    {
        $siklus = $hasil?->statusFinal?->siklus_tahun
            ?? $hasil?->statusAl?->siklus_tahun
            ?? $hasil?->statusAk?->siklus_tahun
            ?? 1; // fallback jika relasi null

        $tanggalMulai    = \Carbon\Carbon::parse($tanggalPenetapan);
        $tanggalBerakhir = $tanggalMulai->copy()->addYears($siklus);

        return [
            'tahun'            => $siklus,
            'tanggal_mulai'    => $tanggalMulai,
            'tanggal_berakhir' => $tanggalBerakhir,
        ];
    }

    /**
     * ✅ Preview Sertifikat (HTML)
     */
    public function previewSertifikat($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.hasil.statusFinal', // ← eager load untuk calculateMasaBerlaku
                'asesmen.hasil.statusAl',
                'asesmen.hasil.statusAk',
            ])->findOrFail($id);

            if (!$pengajuan->tanggal_penetapan) {
                return back()->with('error', 'Hasil belum ditetapkan.');
            }

            $hasil       = $pengajuan->asesmen->hasil;
            $masaBerlaku = $this->calculateMasaBerlaku($hasil, $pengajuan->tanggal_penetapan);
            $elemenList  = ($hasil->detail_skor_al ?? [])['elemen'] ?? [];

            $resume = [
                'mahasiswa_aktif'       => 248,
                'dosen_tetap'           => 12,
                'rasio_dosen_mahasiswa' => '1 : 21',
                'tahun_berdiri'         => 2004,
                'jumlah_lulusan'        => 1.340,
                'rata_ipk'              => '3.52',
                'masa_studi'            => '8.4',
                'serapan_kerja'         => 87,
                'narasi_kondisi'        => 'Program studi telah beroperasi selama 20 tahun dengan
        rekam jejak akademik yang konsisten. Kualitas lulusan diakui secara nasional
        melalui berbagai penghargaan di bidang desain arsitektur.',

                'keunggulan' => [
                    ['kriteria' => 'K1 – Visi & Misi',   'temuan' => 'Visi misi selaras dengan RIP universitas dan diperbarui secara berkala melalui proses partisipatif.'],
                    ['kriteria' => 'K4 – Penelitian',     'temuan' => 'Jumlah publikasi Scopus meningkat 40% dalam dua tahun terakhir dengan keterlibatan mahasiswa S1.'],
                    ['kriteria' => 'K6 – Lulusan',        'temuan' => 'Tingkat serapan kerja ≤ 6 bulan mencapai 87%, melampaui rata-rata nasional bidang arsitektur.'],
                    ['kriteria' => 'K3 – Kurikulum',      'temuan' => 'Kurikulum OBE telah diimplementasikan penuh dengan asesmen berbasis capaian pembelajaran yang terukur.'],
                    ['kriteria' => 'K5 – PKM',            'temuan' => 'Dosen aktif dalam kegiatan pengabdian dengan mitra industri skala nasional dan internasional.'],
                ],

                'ketidakunggulan' => [
                    ['kriteria' => 'K2 – Tata Kelola',   'temuan' => 'Sistem penjaminan mutu internal belum sepenuhnya terdokumentasi; SOP belum konsisten dijalankan.'],
                    ['kriteria' => 'K7 – Sarana',        'temuan' => 'Laboratorium komputer desain membutuhkan pembaruan perangkat keras yang signifikan.'],
                    ['kriteria' => 'K3 – Kurikulum',     'temuan' => 'Integrasi mata kuliah interdisiplin dengan prodi lain masih sangat terbatas.'],
                    ['kriteria' => 'K8 – Keuangan',      'temuan' => 'Proporsi pendanaan penelitian dari sumber eksternal masih di bawah 30% dari total anggaran riset.'],
                ],
            ];

            $data = [
                'pengajuan'        => $pengajuan,
                'hasil'            => $hasil,
                'studyProgram'     => $pengajuan->studyProgram,
                'university'       => $pengajuan->studyProgram->university,
                'nomorSertifikat'  => $pengajuan->generateNomorSertifikat(),
                'tanggalPenetapan' => $pengajuan->tanggal_penetapan,
                'masaBerlaku'      => $masaBerlaku,
                'elemenList'       => $elemenList,
                'resume'            => $resume
            ];

            return view('de.pelaporan-hasil-akreditasi.sertifikat-pdf', $data);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal preview sertifikat: ' . $e->getMessage());
        }
    }
}
