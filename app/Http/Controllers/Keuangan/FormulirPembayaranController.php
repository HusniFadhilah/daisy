<?php

namespace App\Http\Controllers\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use App\Models\PengajuanDokumen;
use App\Models\University;
use App\Models\DegreeLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FormulirPembayaranController extends Controller
{
    /**
     * Display list of formulir pembayaran yang sudah diupload prodi
     */
    public function index(Request $request)
    {
        $q = (string) $request->get('q');
        $university_id = $request->get('university_id');
        $degree_level_id = $request->get('degree_level_id');
        $status = $request->get('status');

        // Query pengajuan yang sudah upload formulir pembayaran
        $pengajuanQuery = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pembayaran',
            'pengaju',
            'dokumen' => function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true);
            }
        ])
            ->whereHas('dokumen', function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true);
            })
            ->whereHas('pembayaran'); // Harus sudah ada pembayaran

        // Filter by search
        if (!empty($q)) {
            $pengajuanQuery->where(function ($w) use ($q) {
                $w->where('nomor_pengajuan', 'like', "%{$q}%")
                    ->orWhere('judul', 'like', "%{$q}%")
                    ->orWhereHas('studyProgram', function ($sp) use ($q) {
                        $sp->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('pembayaran', function ($p) use ($q) {
                        $p->where('nomor_invoice', 'like', "%{$q}%");
                    });
            });
        }

        // Filter by university
        if (!empty($university_id)) {
            $pengajuanQuery->whereHas('studyProgram', function ($sp) use ($university_id) {
                $sp->where('id_university', $university_id);
            });
        }

        // Filter by degree level
        if (!empty($degree_level_id)) {
            $pengajuanQuery->whereHas('studyProgram', function ($sp) use ($degree_level_id) {
                $sp->where('id_degree_level', $degree_level_id);
            });
        }

        // Filter by status pembayaran
        if (!empty($status)) {
            $pengajuanQuery->whereHas('pembayaran', function ($p) use ($status) {
                $p->where('status_pembayaran', $status);
            });
        }

        $pengajuan = $pengajuanQuery
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        // Get filter data
        $universities = University::nonExample()->orderBy('name')->get();
        $degreeLevels = DegreeLevel::orderBy('code')->get();

        // Statistics
        $stats = [
            'total' => PengajuanAkreditasi::whereHas('dokumen', function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true);
            })->count(),

            'today' => PengajuanAkreditasi::whereHas('dokumen', function ($q) {
                $q->where('jenis_dokumen', 'formulir_pembayaran')
                    ->where('is_latest', true)
                    ->whereDate('created_at', today());
            })->count(),

            'menunggu_verifikasi' => PengajuanAkreditasi::whereHas('pembayaran', function ($p) {
                $p->where('status_pembayaran', 'menunggu_verifikasi');
            })->count(),

            'terverifikasi' => PengajuanAkreditasi::whereHas('pembayaran', function ($p) {
                $p->where('status_pembayaran', 'terverifikasi');
            })->count(),
        ];

        return view('keuangan.formulir.index', compact(
            'pengajuan',
            'q',
            'universities',
            'degreeLevels',
            'university_id',
            'degree_level_id',
            'status',
            'stats'
        ));
    }

    /**
     * Show detail formulir pembayaran
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'pembayaran.verifier',
            'pengaju',
            'dokumen' => function ($q) {
                $q->where('is_latest', true)
                    ->orderBy('created_at', 'desc');
            },
        ])->findOrFail($id);

        // Get all dokumen pembayaran (formulir & bukti)
        $dokumenPembayaran = $pengajuan->dokumen->filter(function ($dok) {
            return in_array($dok->jenis_dokumen, ['formulir_pembayaran', 'bukti_pembayaran']);
        });

        // Separate formulir and bukti
        $formulirPembayaran = $dokumenPembayaran->where('jenis_dokumen', 'formulir_pembayaran')->first();
        $buktiPembayaran = $pengajuan->pembayaran ? $pengajuan->pembayaran->bukti_path : null;

        return view('keuangan.formulir.show', compact(
            'pengajuan',
            'dokumenPembayaran',
            'formulirPembayaran',
            'buktiPembayaran'
        ));
    }

    /**
     * Download formulir pembayaran
     */
    public function download($id, $dokumenId)
    {
        $pengajuan = PengajuanAkreditasi::findOrFail($id);
        $dokumen = PengajuanDokumen::where('id', $dokumenId)
            ->where('id_pengajuan', $id)
            ->where('jenis_dokumen', 'formulir_pembayaran')
            ->firstOrFail();

        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk('public')->download(
            $dokumen->file_path,
            $dokumen->original_filename
        );
    }
}
