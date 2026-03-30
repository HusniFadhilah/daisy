<?php
// app/Http/Controllers/DE/PenyimpananArsipPelaksanaanAkreditasiController.php

namespace App\Http\Controllers\DE;

use ZipArchive;
use App\Models\University;
use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PenyimpananArsipAkreditasiController extends Controller
{
    /**
     * Display list of pengajuan yang perlu/sudah diarsipkan
     */
    public function index(Request $request)
    {
        $statusLogs = [
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ];
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'asesmen.hasil',
            'dokumen' => function ($q) {
                $q->where('is_latest', true);
            },
            'statusLog' => function ($q) use ($statusLogs) {
                $q->whereIn('status_to', $statusLogs)->orderBy('changed_at', 'desc');
            },
        ])->whereExists(function ($q) use ($statusLogs) {
            $q->select(DB::raw(1))
                ->from('pengajuan_status_log as l')
                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                ->whereIn('l.status_to', $statusLogs);
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

        if (!in_array($pengajuan->status, [
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ])) {
            return redirect()
                ->route('de.penyimpanan-arsip-akreditasi')
                ->with('error', 'Hasil akreditasi belum dilaporkan.');
        }

        $documentChecklist = PengajuanAkreditasi::getDocumentChecklist($pengajuan);

        // ✅ Get berita acara penyimpanan arsip
        $beritaAcara = null;
        if ($pengajuan->asesmen) {
            $beritaAcara = AsesmenDocument::where('id_asesmen', $pengajuan->asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP)
                ->where('is_active', true)
                ->latest()
                ->first();
        }

        // ✅ Check if can save arsip
        $canSaveArsip = $beritaAcara !== null;

        return view('de.penyimpanan-arsip-akreditasi.show', compact(
            'pengajuan',
            'documentChecklist',
            'beritaAcara',
            'canSaveArsip'
        ));
    }

    /**
     * ✅ Upload Berita Acara Penyimpanan Arsip
     */
    public function uploadBeritaAcara(Request $request, $id)
    {
        $request->validate([
            'berita_acara' => 'required|file|mimes:pdf|max:10240',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'berita_acara.required' => 'File Berita Acara harus diupload.',
            'berita_acara.mimes' => 'File harus berformat PDF.',
            'berita_acara.max' => 'Ukuran file maksimal 10MB.',
        ]);

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            $file = $request->file('berita_acara');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = 'berita_acara_penyimpanan_arsip_' . time() . '.' . $extension;

            $path = $file->storeAs(
                'asesmen_documents/' . $asesmen->id . '/berita_acara_arsip',
                $fileName,
                'public'
            );

            // Deactivate previous berita acara
            AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP)
                ->update(['is_active' => false]);

            // Create new record
            $document = AsesmenDocument::create([
                'id_asesmen' => $asesmen->id,
                'type' => AsesmenDocument::TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP,
                'title' => 'Berita Acara Penyimpanan Arsip Akreditasi',
                'path' => $path,
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now(),
                'keterangan' => $request->keterangan,
                'is_active' => true,
                'version' => 1,
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyimpanan-arsip-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil diupload! silahkan lakukan penyimpanan arsip dengan mengklik tombol di bawah');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload berita acara arsip failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal upload: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Download Berita Acara Penyimpanan Arsip
     */
    public function downloadBeritaAcara($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP)
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

    /**
     * ✅ Delete Berita Acara Penyimpanan Arsip
     */
    public function deleteBeritaAcara($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);
            $asesmen = $pengajuan->asesmen;

            if (!$asesmen) {
                throw new \Exception('Asesmen tidak ditemukan.');
            }

            // Cannot delete if already saved arsip
            if ($pengajuan->status != PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN) {
                throw new \Exception('Berita acara tidak dapat dihapus karena arsip sudah disimpan.');
            }

            $beritaAcara = AsesmenDocument::where('id_asesmen', $asesmen->id)
                ->where('type', AsesmenDocument::TYPE_BERITA_ACARA_PENYIMPANAN_ARSIP)
                ->where('is_active', true)
                ->latest()
                ->firstOrFail();

            // Delete file from storage
            if (Storage::disk('public')->exists($beritaAcara->path)) {
                Storage::disk('public')->delete($beritaAcara->path);
            }

            // Soft delete (mark as inactive)
            $beritaAcara->update(['is_active' => false]);

            DB::commit();

            return redirect()
                ->route('de.penyimpanan-arsip-akreditasi.show', $id)
                ->with('success', 'Berita Acara berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal hapus: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Simpan arsip (UPDATED with validation)
     */
    public function simpanArsip(Request $request, $id)
    {
        $request->validate([
            'catatan_penyimpanan' => 'nullable|string|max:2000',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        if ($pengajuan->status !== PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN) {
            return back()->with('error', 'Status saat ini tidak sesuai untuk penyimpanan arsip.');
        }

        // ✅ Validate berita acara
        if (!AsesmenDocument::hasBeritaAcaraPenyimpananArsip($pengajuan->asesmen->id)) {
            return back()->with('error', 'Berita Acara Penyimpanan Arsip harus diupload terlebih dahulu.');
        }

        // Validate document checklist
        $checklist = PengajuanAkreditasi::getDocumentChecklist($pengajuan);
        $missingCritical = array_filter($checklist, function ($item) {
            return $item['critical'] && !$item['exists'];
        });

        if (!empty($missingCritical)) {
            $missingList = implode(', ', array_column($missingCritical, 'label'));
            return back()->with('error', 'Dokumen penting masih kurang: ' . $missingList);
        }

        DB::beginTransaction();
        try {
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                'tanggal_penyimpanan' => now(),
            ]);

            $pengajuan->statusLog()->create([
                'status_from' => PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
                'status_to' => PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Arsip pelaksanaan akreditasi disimpan. ' . ($request->catatan_penyimpanan ? $request->catatan_penyimpanan : ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyimpanan-arsip-akreditasi.show', $id)
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
                'is_active' => 0
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
                ->route('de.penyimpanan-arsip-akreditasi.show', $id)
                ->with('success', 'Proses akreditasi berhasil diselesaikan!');
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
