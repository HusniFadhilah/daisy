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
        $statusLogs = [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
        ];
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
            'statusLog' => function ($q) use ($statusLogs) {
                $q->whereIn('status_to', $statusLogs)->orderBy('changed_at', 'desc');
            },
        ])
            // ✅ Filter: yang sudah ditetapkan atau lebih lanjut
            ->whereExists(function ($q) use ($statusLogs) {
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
            'asesmen.hasil.statusFinal',
            'asesmen.hasil.statusAl',
            'dokumen' => function ($q) {
                $q->whereIn('jenis_dokumen', [
                    'laporan_ak',
                    'laporan_al',
                    'laporan_banding',
                    'laporan_hasil',
                    'sertifikat',
                    'sertifikat_banding',
                    'lainnya',
                ])
                    ->where('is_latest', true)
                    ->orderBy('created_at', 'desc');
            },
            'statusLog',
        ])->findOrFail($id);

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

        $hasil    = $pengajuan->asesmen->hasil ?? null;
        $peringkat = $hasil->peringkat_akreditasi_final ?? null;

        // Pass resume ke view — dipakai untuk prefill editor
        $resume = $hasil
            ? $hasil->getResumeAsesmenOrDefault()
            : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

        return view(
            'de.pelaporan-hasil-akreditasi.show',
            compact('pengajuan', 'hasil', 'peringkat', 'resume')
        );
    }

    // public function uploadDokumen(Request $request, $id)
    // {
    //     // ── Karakter limit (mirror dari model) ───────────────
    //     $charLimits = \App\Models\HasilAkreditasi::resumeBabCharLimits();

    //     $request->validate([
    //         // File
    //         'file_laporan'       => 'nullable|file|mimes:pdf,doc,docx|max:10240',
    //         'file_sertifikat'    => 'nullable|file|mimes:pdf|max:5120',
    //         'masa_berlaku_tahun' => 'nullable|integer|min:1|max:10',
    //         'keterangan'         => 'nullable|string|max:1000',

    //         // Resume BAB — value adalah HTML dari TinyMCE
    //         'resume.bab.pendahuluan'            => [
    //             'nullable',
    //             'string',
    //             // Validasi panjang plain-text (strip tag) di custom rule
    //             new \App\Rules\MaxPlainTextLength($charLimits['pendahuluan']),
    //         ],
    //         'resume.bab.proses_asesmen'         => [
    //             'nullable',
    //             'string',
    //             new \App\Rules\MaxPlainTextLength($charLimits['proses_asesmen']),
    //         ],
    //         'resume.bab.hasil_asesmen'          => [
    //             'nullable',
    //             'string',
    //             new \App\Rules\MaxPlainTextLength($charLimits['hasil_asesmen']),
    //         ],
    //         'resume.bab.rekomendasi_prodi'      => [
    //             'nullable',
    //             'string',
    //             new \App\Rules\MaxPlainTextLength($charLimits['rekomendasi_prodi']),
    //         ],
    //         'resume.bab.rekomendasi_lamdepilar' => [
    //             'nullable',
    //             'string',
    //             new \App\Rules\MaxPlainTextLength($charLimits['rekomendasi_lamdepilar']),
    //         ],
    //     ], [
    //         // Pesan error custom
    //         'resume.bab.pendahuluan.max_plain_text'            => "BAB I melebihi batas {$charLimits['pendahuluan']} karakter.",
    //         'resume.bab.proses_asesmen.max_plain_text'         => "BAB II melebihi batas {$charLimits['proses_asesmen']} karakter.",
    //         'resume.bab.hasil_asesmen.max_plain_text'          => "BAB III melebihi batas {$charLimits['hasil_asesmen']} karakter.",
    //         'resume.bab.rekomendasi_prodi.max_plain_text'      => "BAB IV melebihi batas {$charLimits['rekomendasi_prodi']} karakter.",
    //         'resume.bab.rekomendasi_lamdepilar.max_plain_text' => "BAB V melebihi batas {$charLimits['rekomendasi_lamdepilar']} karakter.",
    //     ]);

    //     if (!$request->hasFile('file_laporan') && !$request->hasFile('file_sertifikat')) {
    //         return back()->with('error', 'Minimal upload salah satu: Laporan Hasil atau Sertifikat.');
    //     }

    //     $pengajuan = PengajuanAkreditasi::findOrFail($id);

    //     if (!in_array($pengajuan->status, [
    //         PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
    //         PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
    //     ])) {
    //         return back()->with('error', 'Status saat ini tidak sesuai untuk upload dokumen.');
    //     }

    //     $uploadedPaths = [];

    //     DB::beginTransaction();
    //     try {
    //         // ── Upload laporan ────────────────────────────────
    //         if ($request->hasFile('file_laporan')) {
    //             $file     = $request->file('file_laporan');
    //             $filename = 'laporan_hasil_' . $pengajuan->nomor_pengajuan . '_' . time()
    //                 . '.' . $file->getClientOriginalExtension();
    //             $path     = $file->storeAs('pengajuan_dokumen/laporan_hasil', $filename, 'public');
    //             $uploadedPaths[] = $path;

    //             PengajuanDokumen::where('id_pengajuan', $id)
    //                 ->where('jenis_dokumen', 'laporan_hasil')
    //                 ->update(['is_latest' => false]);

    //             PengajuanDokumen::create([
    //                 'id_pengajuan'      => $id,
    //                 'jenis_dokumen'     => 'laporan_hasil',
    //                 'nama_file'         => $filename,
    //                 'path_file'         => $path,
    //                 'original_filename' => $file->getClientOriginalName(),
    //                 'file_size'         => $file->getSize(),
    //                 'mime_type'         => $file->getMimeType(),
    //                 'uploaded_by'       => auth()->id(),
    //                 'keterangan'        => $request->keterangan,
    //                 'versi'             => (PengajuanDokumen::where('id_pengajuan', $id)
    //                     ->where('jenis_dokumen', 'laporan_hasil')
    //                     ->max('versi') ?? 0) + 1,
    //                 'is_latest'         => true,
    //             ]);
    //         }

    //         // ── Upload sertifikat ─────────────────────────────
    //         if ($request->hasFile('file_sertifikat')) {
    //             $file     = $request->file('file_sertifikat');
    //             $filename = 'sertifikat_' . $pengajuan->nomor_pengajuan . '_' . time() . '.pdf';
    //             $path     = $file->storeAs('pengajuan_dokumen/sertifikat', $filename, 'public');
    //             $uploadedPaths[] = $path;

    //             PengajuanDokumen::where('id_pengajuan', $id)
    //                 ->where('jenis_dokumen', 'sertifikat')
    //                 ->update(['is_latest' => false]);

    //             PengajuanDokumen::create([
    //                 'id_pengajuan'      => $id,
    //                 'jenis_dokumen'     => 'sertifikat',
    //                 'nama_file'         => $filename,
    //                 'path_file'         => $path,
    //                 'original_filename' => $file->getClientOriginalName(),
    //                 'file_size'         => $file->getSize(),
    //                 'mime_type'         => $file->getMimeType(),
    //                 'uploaded_by'       => auth()->id(),
    //                 'keterangan'        => $request->keterangan,
    //                 'versi'             => (PengajuanDokumen::where('id_pengajuan', $id)
    //                     ->where('jenis_dokumen', 'sertifikat')
    //                     ->max('versi') ?? 0) + 1,
    //                 'is_latest'         => true,
    //             ]);

    //             if ($request->filled('masa_berlaku_tahun')) {
    //                 $pengajuan->update(['masa_berlaku_tahun' => $request->masa_berlaku_tahun]);
    //             }
    //         }

    //         // ── Simpan Resume Asesmen ─────────────────────────
    //         $resumeInput = $request->input('resume', []);

    //         if (!empty($resumeInput['bab'])) {
    //             $hasil = $pengajuan->asesmen?->hasil;

    //             if ($hasil) {
    //                 // Sanitasi HTML: izinkan tag terbatas saja
    //                 $allowedTags = '<p><br><strong><em><u><ol><ul><li><h3><h4><blockquote>';
    //                 $bab = [];
    //                 foreach ($resumeInput['bab'] as $key => $html) {
    //                     // strip_tags lalu re-encode agar aman disimpan
    //                     $bab[$key] = !empty($html) ? strip_tags($html, $allowedTags) : null;
    //                 }
    //                 $resumeInput['bab'] = $bab;

    //                 $hasil->saveResumeAsesmen($resumeInput, auth()->id());
    //             }
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('de.pelaporan-hasil-akreditasi.show', $id)
    //             ->with('success', 'Dokumen dan resume asesmen berhasil disimpan.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         foreach ($uploadedPaths as $p) {
    //             if (Storage::disk('public')->exists($p)) {
    //                 Storage::disk('public')->delete($p);
    //             }
    //         }
    //         return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    //     }
    // }

    public function uploadDokumen(Request $request, $id)
    {
        // validasi "conditional": file boleh salah satu, tapi minimal ada salah satu
        $request->validate([
            'file_laporan'          => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'file_sertifikat'       => 'nullable|file|mimes:pdf|max:5120',
            'file_sertifikat_banding' => 'nullable|file|mimes:pdf|max:5120',
            'masa_berlaku_tahun'    => 'nullable|integer|min:1|max:10',
            'keterangan'            => 'nullable|string|max:1000',
        ]);

        if (
            !$request->hasFile('file_laporan')
            && !$request->hasFile('file_sertifikat')
            && !$request->hasFile('file_sertifikat_banding')
        ) {
            return back()->with('error', 'Minimal upload salah satu dokumen.');
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

            // === 2) Upload sertifikat (jika ada, hanya saat tidak banding) ===
            if ($request->hasFile('file_sertifikat') && !$pengajuan->hasBanding()) {
                $file = $request->file('file_sertifikat');
                $filename = 'sertifikat_' . $pengajuan->nomor_pengajuan . '_' . time() . '.pdf';
                $path = $file->storeAs('pengajuan_dokumen/sertifikat', $filename, 'public');
                $uploadedPaths[] = $path;

                PengajuanDokumen::where('id_pengajuan', $id)
                    ->where('jenis_dokumen', 'sertifikat')
                    ->update(['is_latest' => false]);

                PengajuanDokumen::create([
                    'id_pengajuan'      => $id,
                    'jenis_dokumen'     => 'sertifikat',
                    'nama_file'         => $filename,
                    'path_file'         => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size'         => $file->getSize(),
                    'mime_type'         => $file->getMimeType(),
                    'uploaded_by'       => auth()->id(),
                    'keterangan'        => $request->keterangan,
                    'versi'             => (PengajuanDokumen::where('id_pengajuan', $id)
                        ->where('jenis_dokumen', 'sertifikat')
                        ->max('versi') ?? 0) + 1,
                    'is_latest'         => true,
                ]);

                $pengajuan->update([
                    'masa_berlaku_tahun' => $request->masa_berlaku_tahun,
                    'nomor_sertifikat'   => $request->nomor_sertifikat,
                    'tanggal_sertifikat' => $request->tanggal_sertifikat,
                ]);
            }

            // === 3) Upload sertifikat_banding (hanya saat banding) ===
            if ($request->hasFile('file_sertifikat_banding') && $pengajuan->hasBanding()) {
                $file = $request->file('file_sertifikat_banding');
                $filename = 'sertifikat_banding_' . $pengajuan->nomor_pengajuan . '_' . time() . '.pdf';
                $path = $file->storeAs('pengajuan_dokumen/sertifikat_banding', $filename, 'public');
                $uploadedPaths[] = $path;

                PengajuanDokumen::where('id_pengajuan', $id)
                    ->where('jenis_dokumen', 'sertifikat_banding')
                    ->update(['is_latest' => false]);

                PengajuanDokumen::create([
                    'id_pengajuan'      => $id,
                    'jenis_dokumen'     => 'sertifikat_banding',
                    'nama_file'         => $filename,
                    'path_file'         => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'file_size'         => $file->getSize(),
                    'mime_type'         => $file->getMimeType(),
                    'uploaded_by'       => auth()->id(),
                    'keterangan'        => $request->keterangan,
                    'versi'             => (PengajuanDokumen::where('id_pengajuan', $id)
                        ->where('jenis_dokumen', 'sertifikat_banding')
                        ->max('versi') ?? 0) + 1,
                    'is_latest'         => true,
                ]);

                // Simpan meta sertifikat banding ke field _pelaporan
                $updateMeta = [];
                if ($request->filled('masa_berlaku_tahun')) {
                    $updateMeta['masa_berlaku_tahun_banding'] = (int) $request->masa_berlaku_tahun;
                }
                if ($request->filled('nomor_sertifikat')) {
                    $updateMeta['nomor_sertifikat_banding'] = $request->nomor_sertifikat;
                }
                if ($request->filled('tanggal_sertifikat')) {
                    $updateMeta['tanggal_sertifikat_banding'] = $request->tanggal_sertifikat;
                }
                if ($request->filled('keterangan')) {
                    $updateMeta['keterangan_sertifikat_banding'] = $request->keterangan;
                }
                if (!empty($updateMeta)) {
                    $pengajuan->update($updateMeta);
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
     * AJAX — simpan / update resume_asesmen.
     * Dipanggil sebelum upload file dokumen.
     */
    public function saveResume(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $charLimit = \App\Models\HasilAkreditasi::resumeBabCharLimit();

        $request->validate([
            // BAB
            'bab'           => 'required|array|min:1|max:20',
            'bab.*.title'   => 'required|string|max:120',
            'bab.*.content' => [
                'nullable',
                'string',
                new \App\Rules\MaxPlainTextLength($charLimit)
            ],
            // Meta sertifikat
            'meta.masa_berlaku_tahun' => 'nullable|integer|min:1|max:10',
            'meta.nomor_sertifikat'   => 'nullable|string|max:100',
            'meta.tanggal_sertifikat' => 'nullable|date',
            'meta.keterangan'         => 'nullable|string|max:1000',
        ], [
            'bab.required'          => 'Minimal harus ada 1 BAB.',
            'bab.*.title.required'  => 'Judul BAB tidak boleh kosong.',
            'bab.*.title.max'       => 'Judul BAB maksimal 120 karakter.',
        ]);

        $pengajuan = PengajuanAkreditasi::findOrFail($id);

        $allowed = [
            PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
            PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN,
        ];

        if (!in_array($pengajuan->status, $allowed)) {
            return response()->json([
                'ok'      => false,
                'message' => 'Status tidak mengizinkan perubahan resume.',
            ], 422);
        }

        $hasil = $pengajuan->asesmen ? $pengajuan->asesmen->hasil : null;

        if (!$hasil) {
            return response()->json([
                'ok'      => false,
                'message' => 'Data hasil akreditasi tidak ditemukan.',
            ], 404);
        }

        DB::beginTransaction();
        try {
            // ── Sanitasi & simpan BAB ─────────────────────────
            $allowedTags = '<p><br><strong><em><u><ol><ul><li><h3><h4><blockquote>';
            $bab = [];
            foreach ($request->input('bab', []) as $item) {
                $title   = trim($item['title'] ?? '');
                $content = isset($item['content']) ? $item['content'] : '';
                $bab[] = [
                    'title'   => $title,
                    'content' => !empty($content) ? strip_tags($content, $allowedTags) : null,
                ];
            }
            $hasil->saveResumeAsesmen(['bab' => $bab], auth()->id());

            // ── Simpan field meta ke pengajuan ────────────────
            $meta       = $request->input('meta', []);
            $updateData = [];
            $metaSuffix = $pengajuan->hasBanding() ? 'banding' : 'pelaporan';

            if (isset($meta['masa_berlaku_tahun']) && $meta['masa_berlaku_tahun'] !== '') {
                $updateData["masa_berlaku_tahun_{$metaSuffix}"] = (int) $meta['masa_berlaku_tahun'];
            }
            if (isset($meta['nomor_sertifikat']) && $meta['nomor_sertifikat'] !== '') {
                $updateData["nomor_sertifikat_{$metaSuffix}"] = $meta['nomor_sertifikat'];
            }
            if (isset($meta['tanggal_sertifikat']) && $meta['tanggal_sertifikat'] !== '') {
                $updateData["tanggal_sertifikat_{$metaSuffix}"] = $meta['tanggal_sertifikat'];
            }
            if (isset($meta['keterangan']) && $meta['keterangan'] !== '') {
                $updateData["keterangan_sertifikat_{$metaSuffix}"] = $meta['keterangan'];
            }

            if (!empty($updateData)) {
                $pengajuan->update($updateData);

                $nomorSertifikatKey = "nomor_sertifikat_{$metaSuffix}";
                if (!empty($updateData[$nomorSertifikatKey])) {
                    $pengajuan->studyProgram?->update([
                        'no_sk' => $updateData[$nomorSertifikatKey],
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok'      => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'ok'         => true,
            'message'    => 'Resume asesmen berhasil disimpan.',
            'saved_at'   => now()->locale('id')->translatedFormat('d M Y, H:i'),
            'bab_count'  => count($bab),
            'has_resume' => $hasil->fresh()->hasResumeAsesmen(),
        ]);
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

        $jenisSertifikatWajib = $pengajuan->hasBanding() ? 'sertifikat_banding' : 'sertifikat';
        $hasSertifikat = PengajuanDokumen::where('id_pengajuan', $id)
            ->where('jenis_dokumen', $jenisSertifikatWajib)
            ->where('is_latest', true)
            ->exists();

        if (!$hasLaporan || !$hasSertifikat) {
            $labelSertifikat = $pengajuan->hasBanding() ? 'Sertifikat Akreditasi (Terbaru)' : 'Sertifikat';
            return back()->with('error', "Wajib upload Laporan Hasil dan {$labelSertifikat} sebelum menyelesaikan pelaporan.");
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
                $pengajuan->studyProgram?->update(['no_sk' => $nomorSertifikat]);
            }

            // Calculate masa berlaku berdasarkan peringkat
            $tanggalPenetapan = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;
            $masaBerlaku = $this->calculateMasaBerlaku($hasil, $tanggalPenetapan, $pengajuan->masa_berlaku_tahun);
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
    private function calculateMasaBerlaku($hasil, $tanggalPenetapan, $masaBerlakuTahun = null): array
    {
        $siklus = $masaBerlakuTahun ?? $hasil?->statusFinal?->siklus_tahun
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
    // ── 3. previewSertifikat() ───────────────────────────────
    public function previewSertifikat($id)
    {
        try {
            $pengajuan = PengajuanAkreditasi::with([
                'studyProgram.university',
                'studyProgram.degreeLevel',
                'studyProgram.category',
                'asesmen.hasil.statusFinal',
                'asesmen.hasil.statusAl',
                'asesmen.hasil.statusAk',
            ])->findOrFail($id);

            if (!$pengajuan->tanggal_penetapan) {
                return back()->with('error', 'Hasil belum ditetapkan.');
            }

            $hasil = $pengajuan->asesmen->hasil;

            // Fallback chain: _pelaporan (banding override) → _penyampaian (field existing) → default
            $nomorSertifikat = ($pengajuan->hasBanding() ? $pengajuan->nomor_sertifikat_banding : null)
                ?? $pengajuan->nomor_sertifikat_pelaporan
                ?? $pengajuan->nomor_sertifikat
                ?? $pengajuan->generateNomorSertifikat();

            $tanggalPenetapan = ($pengajuan->hasBanding() ? $pengajuan->tanggal_sertifikat_banding : null)
                ?? $pengajuan->tanggal_sertifikat_pelaporan
                ?? $pengajuan->tanggal_sertifikat
                ?? $pengajuan->tanggal_penetapan;

            $masaBerlakuTahun = ($pengajuan->hasBanding() ? $pengajuan->masa_berlaku_tahun_banding : null)
                ?? $pengajuan->masa_berlaku_tahun_pelaporan
                ?? $pengajuan->masa_berlaku_tahun;

            $masaBerlaku = $this->calculateMasaBerlaku($hasil, $tanggalPenetapan, $masaBerlakuTahun);
            // Gunakan detail_skor_final jika ada (banding), fallback ke detail_skor_al
            $elemenList = ($hasil->detail_skor_final ?? $hasil->detail_skor_al ?? [])['elemen'] ?? [];

            $resumeRaw = $hasil
                ? $hasil->getResumeAsesmenOrDefault()
                : \App\Models\HasilAkreditasi::resumeAsesmenSkeleton();

            $resume = $resumeRaw;
            if (!isset($resume['bab']) || !is_array($resume['bab'])) {
                $resume['bab'] = [];
            }

            $data = [
                'pengajuan'        => $pengajuan,
                'hasil'            => $hasil,
                'studyProgram'     => $pengajuan->studyProgram,
                'university'       => $pengajuan->studyProgram->university,
                'nomorSertifikat'  => $nomorSertifikat,
                'tanggalPenetapan' => $tanggalPenetapan,
                'masaBerlaku'      => $masaBerlaku,
                'elemenList'       => $elemenList,
                'resume'           => $resume,
                // Peringkat final langsung — tidak perlu hitung ulang dari skor di blade
                'peringkatFinalOverride' => $hasil->peringkat_akreditasi_final ?? null,
            ];
            return view('de.pelaporan-hasil-akreditasi.sertifikat-pdf', $data);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal preview sertifikat: ' . $e->getMessage());
        }
    }
}
