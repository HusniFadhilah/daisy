<?php
// app/Http/Controllers/DE/PenerimaanPermohonanController.php

namespace App\Http\Controllers\DE;

use App\Http\Controllers\Controller;
use App\Http\Requests\KirimSuratPenerimaanRequest;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\University;
use App\Notifications\SuratPenerimaanDikirimNotification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenerimaanPermohonanController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display list of pengajuan yang perlu penerimaan
     */
    public function index(Request $request)
    {
        $statusLogs = [
            PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
            PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
        ];
        $query = PengajuanAkreditasi::with([
            'studyProgram:id,name,full_name,id_university,id_degree_level',
            'studyProgram.university:id,name',
            'studyProgram.degreeLevel:id,name',
            'pengaju:id,name,email',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_penerimaan_de')
                ->where('is_latest', true),
            'statusLog' => function ($q) use ($statusLogs) {
                $q->whereIn('status_to', $statusLogs)->orderBy('changed_at', 'desc');
            },
        ])->whereExists(function ($q) use ($statusLogs) {
            $q->select(DB::raw(1))
                ->from('pengajuan_status_log as l')
                ->whereColumn('l.id_pengajuan', 'pengajuan_akreditasi.id')
                ->whereIn('l.status_to', $statusLogs);
        });

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy(
                $request->get('sort_by', 'tanggal_surat_permohonan_diterima'),
                $request->get('sort_order', 'desc')
            )
            ->paginate(20)
            ->appends($request->query());

        // Statistics
        $stats = $this->calculateStatistics();

        return view('de.penerimaan-permohonan.index', [
            'pengajuans' => $pengajuans,
            'stats' => $stats,
            'universities' => University::nonExample()->pluck('name', 'id'),
            'tahunList' => PengajuanAkreditasi::getTahunAkreditasiList(),
        ]);
    }

    /**
     * Show form untuk upload penerimaan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            'dokumen' => fn($q) => $q->where('jenis_dokumen', 'surat_penerimaan_de')
                ->where('is_latest', true),
        ])->findOrFail($id);

        // Authorization
        // $this->authorize('kirimSuratPenerimaan', $pengajuan);

        return view('de.penerimaan-permohonan.show', compact('pengajuan'));
    }

    /**
     * Upload penerimaan (simplified - no nomor/tanggal input)
     */
    public function kirimSuratPenerimaan(KirimSuratPenerimaanRequest $request, $id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Authorization
        $this->authorize('kirimSuratPenerimaan', $pengajuan);

        DB::beginTransaction();
        try {
            $file = $request->file('file_surat_penerimaan');

            // Store file
            $filename = $this->generateFilename($pengajuan, $file);
            $path = $file->storeAs(
                "permohonan-akreditasi/{$pengajuan->id}/surat_penerimaan",
                $filename,
                'public'
            );

            // Mark old documents as not latest
            $this->markOldDocumentsAsNotLatest($pengajuan);

            // Create document record
            $dokumen = $this->createDocumentRecord($pengajuan, $file, $path, $request);

            // Log activity
            $this->logActivity($pengajuan, $request);
            $pengajuan->update(['status' => PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM, 'tanggal_surat_penerimaan_dikirim' => now()]);
            // Send notification
            $this->sendNotification($pengajuan, $dokumen);

            DB::commit();

            return redirect()
                ->route('de.penerimaan-permohonan.show', $pengajuan->id)
                ->with('success', 'Penerimaan Permohonan Akreditasi berhasil dikirim ke program studi.');
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
            $this->cleanupFailedUpload($path ?? null);

            return back()
                ->withInput()
                ->with('error', 'Gagal mengirim Penerimaan Permohonan Akreditasi: ' . $e->getMessage());
        }
    }

    // ========================================
    // UPDATED HELPER METHOD
    // ========================================

    private function createDocumentRecord($pengajuan, $file, $path, $request)
    {
        return PengajuanDokumen::create([
            'id_pengajuan' => $pengajuan->id,
            'jenis_dokumen' => 'surat_penerimaan_de',
            'nama_file' => basename($path),
            'path_file' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => auth()->id(),
            'keterangan' => $request->keterangan ?? 'Penerimaan Permohonan Akreditasi dari LAMDEPILAR',
            'is_latest' => true,
            'versi' => PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
                ->where('jenis_dokumen', 'surat_penerimaan_de')
                ->max('versi') + 1,
        ]);
    }

    /**
     * Download File Penerimaan Permohonan Akreditasi
     */
    public function download($id)
    {
        $pengajuanDokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_penerimaan_de')
            ->where('is_latest', true)
            ->firstOrFail();
        return $pengajuanDokumen->downloadDokumen();
    }

    /**
     * Delete penerimaan (for revision)
     */
    public function destroy($id)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Authorization
        $this->authorize('kirimSuratPenerimaan', $pengajuan);

        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'surat_penerimaan_de')
            ->where('is_latest', true)
            ->firstOrFail();

        DB::transaction(function () use ($dokumen, $pengajuan) {
            // Delete file
            if (Storage::disk('public')->exists($dokumen->path_file)) {
                Storage::disk('public')->delete($dokumen->path_file);
            }

            $dokumen->update(['is_latest' => false]);

            // Log
            $pengajuan->statusLog()->create([
                'status_from' => $pengajuan->status,
                'status_to' => $pengajuan->status,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Penerimaan Permohonan Akreditasi dihapus untuk revisi',
            ]);
        });

        return back()->with('success', 'Penerimaan Permohonan Akreditasi berhasil dihapus.');
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    private function applyFilters($query, Request $request)
    {
        $query->when(
            $request->filled('university_id'),
            fn($q) => $q->whereHas(
                'studyProgram',
                fn($sq) => $sq->where('id_university', $request->university_id)
            )
        );

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

        // Filter by status penerimaan
        $query->when($request->filled('status_penerimaan'), function ($q) use ($request) {
            if ($request->status_penerimaan === 'terkirim') {
                $q->whereHas(
                    'dokumen',
                    fn($dq) =>
                    $dq->where('jenis_dokumen', 'surat_penerimaan_de')
                        ->where('is_latest', true)
                );
            } elseif ($request->status_penerimaan === 'belum_terkirim') {
                $q->whereDoesntHave(
                    'dokumen',
                    fn($dq) =>
                    $dq->where('jenis_dokumen', 'surat_penerimaan_de')
                        ->where('is_latest', true)
                );
            }
        });
    }

    private function calculateStatistics(): array
    {
        $logs = DB::table('pengajuan_status_log')
            ->select('id_pengajuan', 'status_to')
            ->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            ])
            ->get();

        $stats = [
            'total' => 0,
            'terkirim' => 0,
            'belum_terkirim' => 0,
        ];

        $logsByPengajuan = $logs->groupBy('id_pengajuan');

        foreach ($logsByPengajuan as $pengajuanId => $pengajuanLogs) {
            $statuses = $pengajuanLogs->pluck('status_to')->unique()->toArray();

            // total: pernah masuk fase penerimaan
            $stats['total']++;

            // terkirim: pernah SURAT_PENERIMAAN_DIKIRIM
            if (in_array(PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM, $statuses)) {
                $stats['terkirim']++;
            }

            // belum_terkirim: sudah diterima permohonan, tapi belum dikirimi penerimaan akreditasi
            if (
                in_array(PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, $statuses) &&
                !in_array(PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM, $statuses)
            ) {
                $stats['belum_terkirim']++;
            }
        }

        return $stats;
    }

    private function generateFilename($pengajuan, $file): string
    {
        return sprintf(
            'surat_penerimaan_%s_%s.%s',
            Str::slug($pengajuan->nomor_pengajuan),
            time(),
            $file->getClientOriginalExtension()
        );
    }

    private function markOldDocumentsAsNotLatest($pengajuan): void
    {
        PengajuanDokumen::where('id_pengajuan', $pengajuan->id)
            ->where('jenis_dokumen', 'surat_penerimaan_de')
            ->update(['is_latest' => false]);
    }

    private function logActivity($pengajuan, $request): void
    {
        $pengajuan->statusLog()->create(
            [
                'status_from' => $pengajuan->status,
                'status_to'   => PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                'changed_by'  => auth()->id(),
                'changed_at'  => now(),
                'keterangan'  => 'Penerimaan Permohonan Akreditasi dikirim ke PS' .
                    ($request->keterangan ? ' - ' . $request->keterangan : ''),
            ]
        );
    }

    private function sendNotification($pengajuan, $dokumen): void
    {
        if ($pengajuan->pengaju) {
            $pengajuan->pengaju->notify(
                new SuratPenerimaanDikirimNotification($pengajuan, $dokumen)
            );
        }
    }

    private function cleanupFailedUpload(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
