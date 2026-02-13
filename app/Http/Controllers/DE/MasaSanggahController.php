<?php

namespace App\Http\Controllers\DE;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PengajuanAkreditasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class MasaSanggahController extends Controller
{
    /**
     * ✅ Index - List pengajuan dalam masa sanggah
     */
    public function index(Request $request)
    {
        $query = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'asesmen.hasil',
            'statusLog' => fn($q) => $q->whereIn('status_to', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            ])->orderBy('created_at', 'desc'),
        ])->whereNotNull('tanggal_masa_sanggah_mulai');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by peringkat
        if ($request->filled('peringkat')) {
            $query->whereHas('asesmen.hasil', function ($q) use ($request) {
                $q->where('peringkat_akreditasi', $request->peringkat);
            });
        }

        // Filter masa sanggah status
        if ($request->filled('masa_sanggah_status')) {
            $now = now();

            if ($request->masa_sanggah_status === 'aktif') {
                $query->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
                    ->where('tanggal_masa_sanggah_selesai', '>', $now);
            } elseif ($request->masa_sanggah_status === 'hampir_habis') {
                $query->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
                    ->where('tanggal_masa_sanggah_selesai', '>', $now)
                    ->where('tanggal_masa_sanggah_selesai', '<=', $now->copy()->addDays(2));
            } elseif ($request->masa_sanggah_status === 'selesai') {
                $query->where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI);
            } elseif ($request->masa_sanggah_status === 'ada_banding') {
                $query->whereIn('status', [
                    PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                    PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                ]);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('studyProgram', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        $pengajuans = $query->latest('tanggal_hasil_akreditasi_dikirim')->paginate(15);

        // Calculate statistics
        $stats = [
            'total' => PengajuanAkreditasi::whereIn('status', [
                PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_SELESAI,
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
            ])->count(),

            'aktif' => PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
                ->where('tanggal_masa_sanggah_selesai', '>', now())
                ->count(),

            'hampir_habis' => PengajuanAkreditasi::where('status', PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI)
                ->where('tanggal_masa_sanggah_selesai', '>', now())
                ->where('tanggal_masa_sanggah_selesai', '<=', now()->addDays(2))
                ->count(),

            'ada_banding' => PengajuanAkreditasi::whereIn('status', [
                PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
            ])->count(),
        ];

        return view('de.masa-sanggah.index', compact('pengajuans', 'stats'));
    }

    /**
     * ✅ Show - Detail masa sanggah
     */
    public function show($id)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'studyProgram.category',
            'asesmen.hasil',
            'dokumen' => function ($q) {
                $q->where('is_latest', true);
            }
        ])->findOrFail($id);

        // Validate: Hasil akreditasi harus sudah disampaikan
        if (!$pengajuan->tanggal_hasil_akreditasi_dikirim) {
            return back()->with('error', 'Hasil akreditasi belum disampaikan.');
        }

        $hasil = $pengajuan->asesmen->hasil ?? null;

        // Calculate masa sanggah info
        $masaSanggahInfo = $this->getMasaSanggahInfo($pengajuan);

        // Get banding dokumen if exists
        $dokumenBanding = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'dokumen_banding')
            ->where('is_latest', true)
            ->first();

        $laporanBanding = $pengajuan->dokumen()
            ->where('jenis_dokumen', 'laporan_banding')
            ->where('is_latest', true)
            ->first();

        return view('de.masa-sanggah.show', compact(
            'pengajuan',
            'hasil',
            'masaSanggahInfo',
            'dokumenBanding',
            'laporanBanding'
        ));
    }

    /**
     * ✅ Start masa sanggah
     */
    public function startMasaSanggah($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validate
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM) {
                throw new \Exception('Status pengajuan tidak valid untuk memulai masa sanggah.');
            }

            if (!$pengajuan->tanggal_hasil_akreditasi_dikirim) {
                throw new \Exception('Hasil akreditasi belum disampaikan.');
            }

            // Set masa sanggah (7 days from hasil akreditasi)
            $tanggalMulai = $pengajuan->tanggal_hasil_akreditasi_dikirim;
            $tanggalSelesai = Carbon::parse($tanggalMulai)->addDays(7);

            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI,
                "Masa sanggah dimulai: {$tanggalMulai->locale('id')->translatedFormat('d M Y')} - {$tanggalSelesai->locale('id')->translatedFormat('d M Y')}"
            );

            $pengajuan->update([
                'tanggal_masa_sanggah_mulai' => $tanggalMulai,
                'tanggal_masa_sanggah_selesai' => $tanggalSelesai,
            ]);

            DB::commit();

            return redirect()
                ->route('de.masa-sanggah.show', $id)
                ->with('success', 'Masa sanggah telah dimulai (7 hari).');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Start masa sanggah failed', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Akhiri masa sanggah (jika tidak ada banding)
     */
    public function endMasaSanggah($id)
    {
        DB::beginTransaction();
        try {
            $pengajuan = PengajuanAkreditasi::findOrFail($id);

            // Validate
            if ($pengajuan->status !== PengajuanAkreditasi::STATUS_MASA_SANGGAH_DIMULAI) {
                throw new \Exception('Status pengajuan tidak valid.');
            }

            // Check if masa sanggah sudah selesai
            if (now()->lt($pengajuan->tanggal_masa_sanggah_selesai)) {
                throw new \Exception('Masa sanggah belum selesai.');
            }

            // Check if ada banding
            if ($pengajuan->tanggal_permohonan_banding) {
                throw new \Exception('Tidak dapat mengakhiri masa sanggah karena ada pengajuan banding.');
            }

            // Proceed to penetapan hasil (skip banding)
            $pengajuan->updateStatusSafely(
                PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN,
                'Masa sanggah selesai tanpa banding. Hasil ditetapkan.'
            );

            $pengajuan->update([
                'tanggal_penetapan' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('de.masa-sanggah.show', $id)
                ->with('success', 'Masa sanggah telah selesai. Hasil akreditasi ditetapkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('End masa sanggah failed', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * ✅ Helper: Get masa sanggah info
     */
    private function getMasaSanggahInfo($pengajuan): array
    {
        $info = [
            'is_active' => false,
            'is_expired' => false,
            'is_hampir_habis' => false,
            'days_left' => 0,
            'hours_left' => 0,
            'countdown_text' => '',
            'badge_class' => 'secondary',
            'has_banding' => false,
        ];

        if (!$pengajuan->tanggal_masa_sanggah_mulai) {
            return $info;
        }

        $now = now();
        $mulai = Carbon::parse($pengajuan->tanggal_masa_sanggah_mulai);
        $selesai = Carbon::parse($pengajuan->tanggal_masa_sanggah_selesai);

        // Check if has banding
        $info['has_banding'] = !is_null($pengajuan->tanggal_permohonan_banding);

        if ($now->lt($selesai)) {
            // Active
            $info['is_active'] = true;

            $diff = $now->diff($selesai);
            $info['days_left'] = $diff->days;
            $info['hours_left'] = $diff->h;

            if ($info['days_left'] <= 2) {
                $info['is_hampir_habis'] = true;
                $info['badge_class'] = 'warning';
            } else {
                $info['badge_class'] = 'success';
            }

            // Countdown text
            if ($info['days_left'] > 0) {
                $info['countdown_text'] = "{$info['days_left']} hari {$info['hours_left']} jam lagi";
            } else {
                $info['countdown_text'] = "{$info['hours_left']} jam lagi";
            }
        } else {
            // Expired
            $info['is_expired'] = true;
            $info['badge_class'] = 'danger';

            $daysPassed = $now->diffInDays($selesai);
            $info['countdown_text'] = "Telah terlewat {$daysPassed} hari";
        }

        return $info;
    }
}
