<?php
// app/Http/Controllers/DE/PenyampaianTemplateController.php

namespace App\Http\Controllers\DE;

use App\Models\University;
use Illuminate\Http\Request;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanStatusLog;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class PenyampaianTemplateController extends Controller
{
    /**
     * Display list of permohonan yang perlu dikirim template
     */
    public function index(Request $request)
    {
        // Ambil status terakhir setiap pengajuan
        $latestStatus = PengajuanStatusLog::select('id_pengajuan', 'status_to')
            ->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
            ])
            ->orderByDesc('changed_at')
            ->get()
            ->unique('id_pengajuan');

        // Query pengajuan dengan status terakhir sesuai filter
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
        ])
            ->whereIn('id', $latestStatus->pluck('id_pengajuan'));

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
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort - prioritas ke tanggal_surat_permohonan_diterima
        $sortBy = $request->get('sort_by', 'tanggal_surat_permohonan_diterima');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $pengajuans = $query->paginate(20);

        // Calculate statistics
        $stats = $this->calculateStatistics();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $tahunList = PengajuanAkreditasi::whereIn('status', [
            PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
            PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
        ])
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->filter()
            ->sort()
            ->values();

        return view('de.penyampaian-template.index', compact(
            'pengajuans',
            'stats',
            'universities',
            'tahunList'
        ));
    }

    /**
     * Show detail permohonan
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'pengaju',
            'dokumen' => function ($q) {
                $q->whereIn('jenis_dokumen', ['borang_template', 'template_formulir_pembayaran'])
                    ->where('is_latest', true);
            },
            'statusLog',
        ])->findOrFail($id);

        return view('de.penyampaian-template.show', compact('pengajuan'));
    }

    /**
     * Kirim template via link
     */
    public function kirimTemplateLink(Request $request, $id)
    {
        $request->validate([
            'template_led_link' => 'required|url|max:500',
            'template_pembayaran_link' => 'required|url|max:500',
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'template_led_link.required' => 'Link Template Dokumen wajib diisi',
            'template_led_link.url' => 'Format link Template Dokumen tidak valid',
            'template_pembayaran_link.required' => 'Link Template Formulir Pembayaran wajib diisi',
            'template_pembayaran_link.url' => 'Format link Template Formulir Pembayaran tidak valid',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if (!in_array($pengajuan->status, [PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM])) {
            return back()->with('error', 'Status permohonan tidak sesuai. Permohonan akreditasi harus sudah diterima terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            // Simpan Template Dokumen
            $pengajuan->dokumen()->create([
                'original_filename' => 'Link Template Dokumen',
                'nama_file' => 'Link Template Dokumen',
                'jenis_dokumen' => 'borang_template',
                'template_link' => $request->template_led_link,
                'keterangan' => 'Template Dokumen via link',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
            ]);

            // Simpan Template Formulir Pembayaran
            $pengajuan->dokumen()->create([
                'original_filename' => 'Link Template Formulir Pembayaran',
                'nama_file' => 'Link Template Formulir Pembayaran',
                'jenis_dokumen' => 'template_formulir_pembayaran',
                'template_link' => $request->template_pembayaran_link,
                'keterangan' => 'Template Formulir Pembayaran via link',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
            ]);

            // Update status pengajuan
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                'tanggal_template_led_dikirim' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => $oldStatus,
                'status_to' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Formulir Pembayaran dan Template Dokumen telah dikirim oleh DE'
                // .($request->keterangan ? '. ' . $request->keterangan : ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-template')
                ->with('success', 'Formulir Pembayaran dan Template Dokumen berhasil dikirim via link.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim template: ' . $e->getMessage());
        }
    }

    /**
     * Kirim template via upload file
     */
    public function kirimTemplateUpload(Request $request, $id)
    {
        $request->validate([
            'file_template_led' => 'required|file|mimes:pdf,zip,rar,docx|max:51200', // max 50MB
            'file_template_pembayaran' => 'required|file|mimes:pdf,docx,xlsx,xls|max:10240', // max 10MB
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'file_template_led.required' => 'File Template Dokumen wajib diupload',
            'file_template_led.mimes' => 'Format file Template LED harus PDF, ZIP, RAR, atau DOCX',
            'file_template_led.max' => 'Ukuran file Template LED maksimal 50MB',
            'file_template_pembayaran.required' => 'File Template Formulir Pembayaran wajib diupload',
            'file_template_pembayaran.mimes' => 'Format file Template Pembayaran harus PDF, DOCX, XLSX, atau XLS',
            'file_template_pembayaran.max' => 'Ukuran file Template Pembayaran maksimal 10MB',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        // Validasi status
        if (!in_array($pengajuan->status, [PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA, PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM])) {
            return back()->with('error', 'Status permohonan tidak sesuai. Permohonan Akreditasi harus sudah diterima terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            // Upload Template Dokumen
            $fileLED = $request->file('file_template_led');
            $filenameLED = time() . '_LED_' . str_replace(' ', '_', $fileLED->getClientOriginalName());
            $pathLED = $fileLED->storeAs('dokumen/template-borang', $filenameLED, 'public');

            $pengajuan->dokumen()->create([
                'jenis_dokumen' => 'borang_template',
                'nama_file' => $filenameLED,
                'path_file' => $pathLED,
                'original_filename' => $fileLED->getClientOriginalName(),
                'file_size' => $fileLED->getSize(),
                'mime_type' => $fileLED->getMimeType(),
                'keterangan' => 'Template Dokumen',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
            ]);

            // Upload Template Formulir Pembayaran
            $filePembayaran = $request->file('file_template_pembayaran');
            $filenamePembayaran = time() . '_PEMBAYARAN_' . str_replace(' ', '_', $filePembayaran->getClientOriginalName());
            $pathPembayaran = $filePembayaran->storeAs('dokumen/template-pembayaran', $filenamePembayaran, 'public');

            $pengajuan->dokumen()->create([
                'jenis_dokumen' => 'template_formulir_pembayaran',
                'nama_file' => $filenamePembayaran,
                'path_file' => $pathPembayaran,
                'original_filename' => $filePembayaran->getClientOriginalName(),
                'file_size' => $filePembayaran->getSize(),
                'mime_type' => $filePembayaran->getMimeType(),
                'keterangan' => 'Template Formulir Pembayaran',
                'uploaded_by' => auth()->id(),
                'is_latest' => true,
            ]);

            // Update status pengajuan
            $oldStatus = $pengajuan->status;
            $pengajuan->update([
                'status' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                'tanggal_template_led_dikirim' => now(),
            ]);

            // Log status change
            $pengajuan->statusLog()->create([
                'status_from' => $oldStatus,
                'status_to' => PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
                'keterangan' => 'Formulir Pembayaran (' . $fileLED->getClientOriginalName() . ') ' .
                    'dan Template Dokumen (' . $filePembayaran->getClientOriginalName() . ') dikirim oleh DE'
                // .($request->keterangan ? '. ' . $request->keterangan : ''),
            ]);

            DB::commit();

            return redirect()
                ->route('de.penyampaian-template')
                ->with('success', 'Formulir Pembayaran dan Template Dokumen berhasil dikirim.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim template: ' . $e->getMessage());
        }
    }

    /**
     * Download template yang sudah dikirim
     */
    public function download($id, $jenis = 'borang_template')
    {
        $dokumen = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', $jenis)
            ->where('is_latest', true)
            ->firstOrFail();

        // Jika link
        if ($dokumen->template_link) {
            return redirect($dokumen->template_link);
        }

        // Jika file upload
        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($dokumen->path_file, $dokumen->original_filename);
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics(): array
    {
        $base = PengajuanAkreditasi::query();

        // Total: pernah ada permohonan atau template LED terkait
        $total = (clone $base)
            ->whereHas('statusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                    PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM,
                ]);
            })
            ->count();

        // Belum dikirim: status terakhir SURAT_PERMOHONAN_DITERIMA, belum pernah TEMPLATE_LED_DIKIRIM
        $belumDikirim = (clone $base)
            ->whereHas('statusLog', function ($q) {
                $q->whereIn('status_to', [
                    PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA,
                    PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM,
                ]);
            })
            ->whereDoesntHave('statusLog', function ($q) {
                $q->where('status_to', PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM);
            })
            ->count();

        // Sudah dikirim: pernah TEMPLATE_LED_DIKIRIM
        $sudahDikirim = (clone $base)
            ->whereHas('statusLog', function ($q) {
                $q->where('status_to', PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM);
            })
            ->count();
        return [
            'total' => $total,
            'belum_dikirim' => $belumDikirim,
            'sudah_dikirim' => $sudahDikirim,
        ];
    }
}
