<?php
// app/Http/Controllers/DE/PenyimpananArsipAkreditasiController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class PenyimpananArsipAkreditasiController extends Controller
{
    /**
     * Display list of pengajuan yang perlu/sudah diarsipkan
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
                $q->where('is_latest', true);
            },
            'statusLog' => function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                    PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                    PengajuanAkreditasi::STATUS_SELESAI,
                ])->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ Filter: yang sudah dilaporkan atau lebih lanjut
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ]);

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
        $sortBy = $request->get('sort_by', 'tanggal_pelaporan_hasil');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'tanggal_pelaporan_hasil') {
            $query->orderByRaw('COALESCE(tanggal_pelaporan_hasil, created_at) ' . $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ])
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        return view('de.penyimpanan-arsip-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList'
        ));
    }

    /**
     * Show detail arsip
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'asesmen.hasil',
            'asesmen.asesmenKecukupan',
            'asesmen.asesmenLapangan',
            'dokumen' => function ($q) {
                $q->where('is_latest', true)
                    ->orderBy('jenis_dokumen')
                    ->orderBy('created_at', 'desc');
            },
            'statusLog',
        ])->findOrFail($id);

        // Check if hasil sudah dilaporkan
        if (!in_array($pengajuan->status, [
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ])) {
            return redirect()
                ->route('de.penyimpanan-arsip-pelaksanaan-akreditasi')
                ->with('error', 'Hasil akreditasi belum dilaporkan.');
        }

        // Get document checklist
        $documentChecklist = $this->getDocumentChecklist($pengajuan);

        return view('de.penyimpanan-arsip-akreditasi.show', compact('pengajuan', 'documentChecklist'));
    }

    /**
     * Simpan arsip (mark as archived)
     */
    public function simpanArsip(Request $request, $id)
    {
        $request->validate([
            'catatan_penyimpanan' => 'nullable|string|max:2000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk penyimpanan arsip.');
        }

        // Validasi kelengkapan dokumen minimal
        $checklist = $this->getDocumentChecklist($pengajuan);
        $missingCritical = array_filter($checklist, function ($item) {
            return $item['critical'] && !$item['exists'];
        });

        if (!empty($missingCritical)) {
            $missingList = implode(', ', array_column($missingCritical, 'label'));
            return back()->with('error', 'Dokumen penting masih kurang: ' . $missingList);
        }

        DB::beginTransaction();
        try {
            // Update status
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                'tanggal_penyimpanan' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                'status_to' => PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Arsip pelaksanaan akreditasi disimpan. ' . ($request->catatan_penyimpanan ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyimpanan-arsip-pelaksanaan-akreditasi.show', $id)
                ->with('success', 'Arsip pelaksanaan akreditasi berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan arsip: ' . $e->getMessage());
        }
    }

    /**
     * Selesaikan proses akreditasi (final step)
     */
    public function selesaikanProses(Request $request, $id)
    {
        $request->validate([
            'catatan_penyelesaian' => 'nullable|string|max:2000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN) {
            return back()->with('error', 'Arsip belum disimpan. Simpan arsip terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            // Update status to SELESAI (final)
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_SELESAI,
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                'status_to' => PengajuanAkreditasi::STATUS_SELESAI,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Proses akreditasi selesai. ' . ($request->catatan_penyelesaian ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyimpanan-arsip-pelaksanaan-akreditasi.show', $id)
                ->with('success', 'Proses akreditasi berhasil diselesaikan! 🎉');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyelesaikan proses: ' . $e->getMessage());
        }
    }

    /**
     * Download all documents as ZIP
     */
    public function downloadAllDocuments($id)
    {
        $pengajuan = PengajuanAkreditasi::with(['dokumen' => function ($q) {
            $q->where('is_latest', true);
        }])->findOrFail($id);

        $dokumens = $pengajuan->dokumen;

        if ($dokumens->isEmpty()) {
            return back()->with('error', 'Tidak ada dokumen untuk diunduh.');
        }

        $zipFileName = 'arsip_' . $pengajuan->nomor_pengajuan . '_' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Create temp directory if not exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        foreach ($dokumens as $dokumen) {
            $filePath = storage_path('app/public/' . $dokumen->path_file);

            if (file_exists($filePath)) {
                // Create folder structure in ZIP
                $folderName = $dokumen->jenis_dokumen_alias;
                $zip->addFile($filePath, $folderName . '/' . $dokumen->original_filename);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Get document checklist for validation
     */
    private function getDocumentChecklist($pengajuan): array
    {
        $dokumens = $pengajuan->dokumen->keyBy('jenis_dokumen');

        return [
            'surat_permohonan' => [
                'label' => 'Surat Permohonan PS',
                'critical' => true,
                'exists' => $dokumens->has('surat_permohonan'),
                'dokumen' => $dokumens->get('surat_permohonan'),
            ],
            'formulir_pembayaran' => [
                'label' => 'Bukti Pembayaran',
                'critical' => true,
                'exists' => $dokumens->has('formulir_pembayaran'),
                'dokumen' => $dokumens->get('formulir_pembayaran'),
            ],
            'draft_borang' => [
                'label' => 'Dokumen',
                'critical' => true,
                'exists' => $dokumens->has('draft_borang') || $dokumens->has('borang_final'),
                'dokumen' => $dokumens->get('borang_final') ?? $dokumens->get('draft_borang'),
            ],
            'laporan_ak' => [
                'label' => 'Laporan Hasil AK',
                'critical' => true,
                'exists' => $dokumens->has('laporan_ak'),
                'dokumen' => $dokumens->get('laporan_ak'),
            ],
            'laporan_al' => [
                'label' => 'Laporan Hasil AL',
                'critical' => true,
                'exists' => $dokumens->has('laporan_al'),
                'dokumen' => $dokumens->get('laporan_al'),
            ],
            'laporan_hasil' => [
                'label' => 'Laporan Hasil Akreditasi',
                'critical' => true,
                'exists' => $dokumens->has('laporan_hasil'),
                'dokumen' => $dokumens->get('laporan_hasil'),
            ],
            'sertifikat' => [
                'label' => 'Sertifikat Akreditasi',
                'critical' => true,
                'exists' => $dokumens->has('sertifikat'),
                'dokumen' => $dokumens->get('sertifikat'),
            ],
            'laporan_banding' => [
                'label' => 'Laporan Banding',
                'critical' => false,
                'exists' => $dokumens->has('laporan_banding'),
                'dokumen' => $dokumens->get('laporan_banding'),
            ],
        ];
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        $stats = [
            'total' => 0,
            'belum_diarsipkan' => 0,
            'sudah_diarsipkan' => 0,
            'selesai' => 0,
            'unggul' => 0,
            'baik_sekali' => 0,
            'baik' => 0,
            'tidak_terakreditasi' => 0,
        ];

        // Total yang siap arsip
        $stats['total'] = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ])->count();

        // Belum diarsipkan
        $stats['belum_diarsipkan'] = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
            ->count();

        // Sudah diarsipkan
        $stats['sudah_diarsipkan'] = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN)
            ->count();

        // Selesai
        $stats['selesai'] = PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_SELESAI)
            ->count();

        // Distribusi peringkat
        $peringkatDist = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ])
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
