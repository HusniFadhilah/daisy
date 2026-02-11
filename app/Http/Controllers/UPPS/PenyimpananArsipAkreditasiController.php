<?php
// app/Http/Controllers/UPPS/PenyimpananArsipPelaksanaanAkreditasiController.php

namespace App\Http\Controllers\UPPS;

use Illuminate\Http\Request;
use App\Models\AsesmenDocument;
use App\Models\PengajuanDokumen;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanAkreditasi;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PenyimpananArsipAkreditasiController extends Controller
{
    /**
     * Display list of arsip akreditasi
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $studyProgramIds = $user->studyPrograms()->pluck('study_programs.id');

        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ])->orderBy('changed_at', 'desc'),
        ])
            ->whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penyimpanan')
            ->whereIn('status', [
                PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
                PengajuanAkreditasi::STATUS_SELESAI,
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        $pengajuans = $query
            ->orderBy($request->get('sort_by', 'tanggal_penyimpanan'), $request->get('sort_order', 'desc'))
            ->paginate(20)
            ->appends($request->query());

        // Statistics
        $stats = $this->calculateStatistics($studyProgramIds);

        // Get tahun list
        $tahunList = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->distinct()
            ->pluck('tahun_akreditasi')
            ->sort()
            ->values();

        return view('upps.penyimpanan-arsip-akreditasi.index', compact(
            'pengajuans',
            'stats',
            'tahunList'
        ));
    }

    /**
     * Show detail arsip akreditasi
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

        if (!in_array($pengajuan->status, [
            PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN,
            PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN,
            PengajuanAkreditasi::STATUS_SELESAI,
        ])) {
            return redirect()
                ->route('de.penyimpanan-arsip-akreditasi')
                ->with('error', 'Hasil akreditasi belum dilaporkan.');
        }

        $documentChecklist = $this->getDocumentChecklist($pengajuan);

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

        return view('upps.penyimpanan-arsip-akreditasi.show', compact(
            'pengajuan',
            'documentChecklist',
            'beritaAcara',
            'canSaveArsip'
        ));
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
        // Dokumen dari pengajuan (PengajuanDokumen)
        $dokumens = $pengajuan->dokumen->keyBy('jenis_dokumen');

        // Dokumen dari asesmen (AsesmenDocument)
        $asesmen = $pengajuan->asesmen ?? null;

        $asesmenTypes = [
            'laporan_validasi_borang',
            'laporan_validasi_ak',
            'berita_acara_al',
            'lha_asesor',
            'laporan_al',
            'berita_acara_penyampaian_hasil', // sesuai yang kamu tulis (kalau typo -> perbaiki ke "hasil")
            'berita_acara_penetapan_hasil',
        ];

        $asesmenDokumens = collect();

        if ($asesmen) {
            $asesmenDokumens = AsesmenDocument::query()
                ->where('id_asesmen', $asesmen->id)
                ->whereIn('type', $asesmenTypes)
                ->get()
                ->keyBy('type');
        }

        return [
            // =========================
            // Dokumen Pengajuan (lama)
            // =========================
            'sertifikat' => [
                'label' => 'Sertifikat Akreditasi',
                'critical' => true,
                'exists' => $dokumens->has('sertifikat'),
                'dokumen' => $dokumens->get('sertifikat'),
                'source' => 'pengajuan',
            ],
            'surat_permohonan' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['surat_permohonan'] ?? 'Surat Permohonan Akreditasi',
                'critical' => true,
                'exists' => $dokumens->has('surat_permohonan'),
                'dokumen' => $dokumens->get('surat_permohonan'),
                'source' => 'pengajuan',
            ],
            'surat_penerimaan' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['surat_penerimaan_de'] ?? 'Surat Penerimaan Akreditasi',
                'critical' => true,
                'exists' => $dokumens->has('surat_penerimaan_de'),
                'dokumen' => $dokumens->get('surat_penerimaan_de'),
                'source' => 'pengajuan',
            ],
            'formulir_pembayaran' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['formulir_pembayaran'] ?? 'Bukti Pembayaran',
                'critical' => true,
                'exists' => $dokumens->has('formulir_pembayaran'),
                'dokumen' => $dokumens->get('formulir_pembayaran'),
                'source' => 'pengajuan',
            ],
            'data_kualitatif' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['data_kualitatif'] ?? 'Laporan Evaluasi Diri (LED)',
                'critical' => true,
                'exists' => $dokumens->has('data_kualitatif'),
                'dokumen' => $dokumens->get('data_kualitatif'),
                'source' => 'pengajuan',
            ],
            'data_kuantitatif' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['data_kuantitatif'] ?? 'Laporan Kinerja Program Studi (LKPS)',
                'critical' => true,
                'exists' => $dokumens->has('data_kuantitatif'),
                'dokumen' => $dokumens->get('data_kuantitatif'),
                'source' => 'pengajuan',
            ],
            'data_suplemen' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['data_suplemen'] ?? 'Suplemen Laporan Evaluasi Diri',
                'critical' => true,
                'exists' => $dokumens->has('data_suplemen'),
                'dokumen' => $dokumens->get('data_suplemen'),
                'source' => 'pengajuan',
            ],
            'lembar_pengesahan' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['lembar_pengesahan'] ?? 'Lembar Pengesahan Dokumen',
                'critical' => true,
                'exists' => $dokumens->has('lembar_pengesahan'),
                'dokumen' => $dokumens->get('lembar_pengesahan'),
                'source' => 'pengajuan',
            ],
            'surat_tugas_validator_dokumen' => [
                'label' => 'Surat Tugas Validator',
                'critical' => true,
                'exists' => $dokumens->has('surat_tugas_validator_dokumen'),
                'dokumen' => $dokumens->get('surat_tugas_validator_dokumen'),
                'source' => 'pengajuan',
            ],
            'surat_tugas_asesor_ak' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['surat_tugas_asesor_ak'] ?? 'Surat Tugas Asesor AK',
                'critical' => true,
                'exists' => $dokumens->has('surat_tugas_asesor_ak'),
                'dokumen' => $dokumens->get('surat_tugas_asesor_ak'),
                'source' => 'pengajuan',
            ],
            'surat_tugas_asesor_al' => [
                'label' => PengajuanDokumen::JENIS_DOKUMEN_ALIAS['surat_tugas_asesor_al'] ?? 'Surat Tugas Asesor AL',
                'critical' => true,
                'exists' => $dokumens->has('surat_tugas_asesor_al'),
                'dokumen' => $dokumens->get('surat_tugas_asesor_al'),
                'source' => 'pengajuan',
            ],
            'laporan_hasil' => [
                'label' => 'Laporan Hasil Akreditasi',
                'critical' => true,
                'exists' => $dokumens->has('laporan_hasil'),
                'dokumen' => $dokumens->get('laporan_hasil'),
                'source' => 'pengajuan',
            ],

            // =========================
            // Dokumen Asesmen (baru)
            // =========================
            'asesmen_laporan_validasi_borang' => [
                'label' => 'Laporan Validasi Borang',
                'critical' => false,
                'exists' => $asesmenDokumens->has('laporan_validasi_borang'),
                'dokumen' => $asesmenDokumens->get('laporan_validasi_borang'),
                'source' => 'asesmen',
            ],
            'asesmen_laporan_validasi_ak' => [
                'label' => 'Laporan Validasi AK',
                'critical' => false,
                'exists' => $asesmenDokumens->has('laporan_validasi_ak'),
                'dokumen' => $asesmenDokumens->get('laporan_validasi_ak'),
                'source' => 'asesmen',
            ],
            'asesmen_berita_acara_al' => [
                'label' => 'Berita Acara AL',
                'critical' => false,
                'exists' => $asesmenDokumens->has('berita_acara_al'),
                'dokumen' => $asesmenDokumens->get('berita_acara_al'),
                'source' => 'asesmen',
            ],
            'asesmen_lha_asesor' => [
                'label' => 'Laporan Hasil Asesmen Lapangan (Asesor)',
                'critical' => false,
                'exists' => $asesmenDokumens->has('lha_asesor'),
                'dokumen' => $asesmenDokumens->get('lha_asesor'),
                'source' => 'asesmen',
            ],
            'asesmen_laporan_al' => [
                'label' => 'Laporan Rekap AL',
                'critical' => false,
                'exists' => $asesmenDokumens->has('laporan_al'),
                'dokumen' => $asesmenDokumens->get('laporan_al'),
                'source' => 'asesmen',
            ],
            'asesmen_berita_acara_penyampaian_hasil' => [
                'label' => 'Berita Acara Penyampaian Hasil',
                'critical' => false,
                'exists' => $asesmenDokumens->has('berita_acara_penyampaian_hasil'),
                'dokumen' => $asesmenDokumens->get('berita_acara_penyampaian_hasil'),
                'source' => 'asesmen',
            ],
            'asesmen_berita_acara_penetapan_hasil' => [
                'label' => 'Berita Acara Penetapan Hasil',
                'critical' => false,
                'exists' => $asesmenDokumens->has('berita_acara_penetapan_hasil'),
                'dokumen' => $asesmenDokumens->get('berita_acara_penetapan_hasil'),
                'source' => 'asesmen',
            ],


            // 'laporan_ak' => [
            //     'label' => 'Laporan Hasil AK',
            //     'critical' => true,
            //     'exists' => $dokumens->has('laporan_ak'),
            //     'dokumen' => $dokumens->get('laporan_ak'),
            // ],
            // 'laporan_al' => [
            //     'label' => 'Laporan Hasil AL',
            //     'critical' => true,
            //     'exists' => $dokumens->has('laporan_al'),
            //     'dokumen' => $dokumens->get('laporan_al'),
            // ],
            // 'laporan_hasil' => [
            //     'label' => 'Laporan Hasil Akreditasi',
            //     'critical' => true,
            //     'exists' => $dokumens->has('laporan_hasil'),
            //     'dokumen' => $dokumens->get('laporan_hasil'),
            // ],
            // 'laporan_banding' => [
            //     'label' => 'Laporan Banding',
            //     'critical' => false,
            //     'exists' => $dokumens->has('laporan_banding'),
            //     'dokumen' => $dokumens->get('laporan_banding'),
            // ],
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
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
                        fn($ssq) =>
                        $ssq->where('name', 'like', "%{$search}%")
                    );
            });
        });

        // Filter by status
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        // Filter by peringkat hasil akhir
        $query->when($request->filled('peringkat'), function ($q) use ($request) {
            $q->where(function ($sq) use ($request) {
                $sq->where('peringkat_hasil_banding', $request->peringkat)
                    ->orWhere(function ($ssq) use ($request) {
                        $ssq->whereNull('peringkat_hasil_banding')
                            ->where('peringkat_hasil', $request->peringkat);
                    });
            });
        });
    }

    /**
     * Calculate statistics
     */
    private function calculateStatistics($studyProgramIds): array
    {
        // Total arsip disimpan
        $total = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penyimpanan')
            ->count();

        // Proses selesai
        $selesai = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->where('status', PengajuanAkreditasi::STATUS_SELESAI)
            ->count();

        // By peringkat (prioritas hasil banding)
        $byPeringkat = PengajuanAkreditasi::whereIn('id_program_studi', $studyProgramIds)
            ->whereNotNull('tanggal_penyimpanan')
            ->get()
            ->groupBy(function ($item) {
                return $item->peringkat_hasil_banding ?? $item->peringkat_hasil ?? 'Tidak Ada';
            })
            ->map(fn($group) => $group->count())
            ->toArray();

        return [
            'total' => $total,
            'selesai' => $selesai,
            'by_peringkat' => $byPeringkat,
        ];
    }

    /**
     * Group documents by category for display
     */
    private function groupDocuments($pengajuan): array
    {
        $dokumen = $pengajuan->dokumen->where('is_latest', true);

        return [
            'LED (Laporan Evaluasi Diri)' => $dokumen->whereIn('jenis_dokumen', [
                'data_kualitatif',
                'data_kuantitatif',
                'data_suplemen',
                'draft_borang',
                'borang_final',
            ]),
            'Dokumen Penilaian AK' => $dokumen->whereIn('jenis_dokumen', [
                'laporan_ak',
            ]),
            'Dokumen Asesmen AL' => $dokumen->whereIn('jenis_dokumen', [
                'laporan_al',
            ]),
            'Hasil Akreditasi' => $dokumen->whereIn('jenis_dokumen', [
                'sertifikat',
                'sk_akreditasi',
                'sk_penetapan',
                'laporan_hasil',
            ]),
            // 'Dokumen Banding' => $dokumen->whereIn('jenis_dokumen', [
            //     'dokumen_banding',
            //     'hasil_banding',
            // ]),
            'Dokumen Pendukung' => $dokumen->whereIn('jenis_dokumen', [
                'surat_permohonan',
                'surat_tugas',
                'bukti_pembayaran',
                'lembar_pengesahan',
                'dokumen_pendukung',
                'lainnya',
            ]),
        ];
    }

    /**
     * Calculate process statistics
     */
    private function calculateProcessStatistics($pengajuan): array
    {
        $stats = [];

        if ($pengajuan->tanggal_pengajuan && $pengajuan->tanggal_penyimpanan) {
            $stats['total_durasi'] = $pengajuan->tanggal_pengajuan->diffInDays($pengajuan->tanggal_penyimpanan);
        }

        if ($pengajuan->tanggal_pengajuan && $pengajuan->tanggal_penetapan) {
            $stats['durasi_hingga_penetapan'] = $pengajuan->tanggal_pengajuan->diffInDays($pengajuan->tanggal_penetapan);
        }

        if ($pengajuan->tanggal_validasi_borang_assigned && $pengajuan->tanggal_validasi_borang_selesai) {
            $stats['durasi_validasi_dokumen'] = $pengajuan->tanggal_validasi_borang_assigned->diffInDays($pengajuan->tanggal_validasi_borang_selesai);
        }

        if ($pengajuan->tanggal_penugasan_asesor_ak && $pengajuan->tanggal_pelaporan_ak) {
            $stats['durasi_ak'] = $pengajuan->tanggal_penugasan_asesor_ak->diffInDays($pengajuan->tanggal_pelaporan_ak);
        }

        if ($pengajuan->tanggal_penugasan_asesor_al && $pengajuan->tanggal_pelaporan_al) {
            $stats['durasi_al'] = $pengajuan->tanggal_penugasan_asesor_al->diffInDays($pengajuan->tanggal_pelaporan_al);
        }

        // Total dokumen terarsip
        $stats['total_dokumen'] = $pengajuan->dokumen->where('is_latest', true)->count();

        return $stats;
    }
}
