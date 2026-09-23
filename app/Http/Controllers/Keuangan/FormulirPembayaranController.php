<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\PengajuanPembayaran;
use App\Models\University;
use App\Models\DegreeLevel;
use Illuminate\Http\Request;

class FormulirPembayaranController extends Controller
{
    private const JENIS_FORMULIR = [
        'formulir_pembayaran',
        'formulir_pembayaran_banding',
    ];

    public function index(Request $request)
    {
        $q               = (string) $request->get('q');
        $university_id   = $request->get('university_id');
        $degree_level_id = $request->get('degree_level_id');
        $status          = $request->get('status');
        $jenis           = $request->get('jenis'); // 'akreditasi' | 'banding' | ''

        $pengajuanQuery = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            // ✅ Load SEMUA invoice (HasMany) — filter per jenis di blade
            //    Lebih aman daripada HasOne latestOfMany yang bisa null
            'semuaPembayaran',
            // Load formulir terbaru kedua jenis
            'dokumen' => fn($q) => $q
                ->whereIn('jenis_dokumen', self::JENIS_FORMULIR)
                ->where('is_latest', true)
                ->orderByDesc('created_at'),
        ])
            ->whereHas(
                'dokumen',
                fn($q) =>
                $q->whereIn('jenis_dokumen', self::JENIS_FORMULIR)
                    ->where('is_latest', true)
            )
            ->where(
                fn($w) =>
                $w->whereHas('pembayaran')
                    ->orWhereHas('pembayaranBanding')
            );

        // Filter jenis
        if (!empty($jenis)) {
            if ($jenis === 'akreditasi') {
                $pengajuanQuery
                    ->whereHas(
                        'dokumen',
                        fn($q) =>
                        $q->where('jenis_dokumen', 'formulir_pembayaran')->where('is_latest', true)
                    )
                    ->whereHas('pembayaran');
            } elseif ($jenis === 'banding') {
                $pengajuanQuery
                    ->whereHas(
                        'dokumen',
                        fn($q) =>
                        $q->where('jenis_dokumen', 'formulir_pembayaran_banding')->where('is_latest', true)
                    )
                    ->whereHas('pembayaranBanding');
            }
        }

        // Filter status — cek di semua invoice
        if (!empty($status)) {
            $pengajuanQuery->whereHas(
                'semuaPembayaran',
                fn($p) =>
                $p->where('status_pembayaran', $status)
            );
        }

        // Filter universitas
        if (!empty($university_id)) {
            $pengajuanQuery->whereHas(
                'studyProgram',
                fn($sp) =>
                $sp->where('id_university', $university_id)
            );
        }

        // Filter jenjang
        if (!empty($degree_level_id)) {
            $pengajuanQuery->whereHas(
                'studyProgram',
                fn($sp) =>
                $sp->where('id_degree_level', $degree_level_id)
            );
        }

        // Pencarian
        if (!empty($q)) {
            $pengajuanQuery->where(function ($w) use ($q) {
                $w->where('nomor_pengajuan', 'like', "%{$q}%")
                    ->orWhereHas(
                        'studyProgram',
                        fn($sp) =>
                        $sp->where('name', 'like', "%{$q}%")
                    )
                    ->orWhereHas(
                        'semuaPembayaran',
                        fn($p) =>
                        $p->where('nomor_invoice', 'like', "%{$q}%")
                    );
            });
        }

        $pengajuan = $pengajuanQuery
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();
        $stats        = $this->calculateStatistics();

        return view('keuangan.formulir.index', compact(
            'pengajuan',
            'q',
            'jenis',
            'universities',
            'degreeLevels',
            'university_id',
            'degree_level_id',
            'status',
            'stats',
        ));
    }

    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pengaju',
            // ✅ Load semua invoice sekaligus
            'semuaPembayaran.verifier',
            // Load semua versi dokumen formulir untuk riwayat
            'dokumen' => fn($q) => $q
                ->whereIn('jenis_dokumen', self::JENIS_FORMULIR)
                ->orderByDesc('created_at'),
        ])->findOrFail($id);

        // Pisah per jenis — dari relasi semuaPembayaran
        $pmbAkreditasi = $pengajuan->semuaPembayaran
            ->firstWhere('jenis_pembayaran', 'akreditasi');
        $pmbBanding    = $pengajuan->semuaPembayaran
            ->firstWhere('jenis_pembayaran', 'banding');

        // Dokumen terbaru per jenis
        $formulirAkreditasi = $pengajuan->dokumen
            ->where('jenis_dokumen', 'formulir_pembayaran')
            ->firstWhere('is_latest', true);

        $formulirBanding = $pengajuan->dokumen
            ->where('jenis_dokumen', 'formulir_pembayaran_banding')
            ->firstWhere('is_latest', true);

        $riwayatDokumen = $pengajuan->dokumen->sortByDesc('created_at');

        // Inject ke $pengajuan agar blade bisa akses via $pengajuan->pembayaran
        // (opsional — blade show sudah pakai $pmbAkreditasi/$pmbBanding langsung)

        return view('keuangan.formulir.show', compact(
            'pengajuan',
            'formulirAkreditasi',
            'formulirBanding',
            'pmbAkreditasi',
            'pmbBanding',
            'riwayatDokumen',
        ));
    }

    public function download($id, $dokumenId)
    {
        $dokumen = PengajuanDokumen::where('id', $dokumenId)
            ->where('id_pengajuan', $id)
            ->whereIn('jenis_dokumen', self::JENIS_FORMULIR)
            ->firstOrFail();

        return $dokumen->downloadDokumen();
    }

    private function calculateStatistics(): array
    {
        $base       = PengajuanDokumen::whereIn('jenis_dokumen', self::JENIS_FORMULIR)->where('is_latest', true);
        $akreditasi = PengajuanDokumen::where('jenis_dokumen', 'formulir_pembayaran')->where('is_latest', true);
        $banding    = PengajuanDokumen::where('jenis_dokumen', 'formulir_pembayaran_banding')->where('is_latest', true);

        return [
            'total'      => (clone $base)->count(),
            'today'      => (clone $base)->whereDate('created_at', today())->count(),
            'akreditasi' => (clone $akreditasi)->count(),
            'banding'    => (clone $banding)->count(),
        ];
    }
}
