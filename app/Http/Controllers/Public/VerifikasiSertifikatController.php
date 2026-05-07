<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PengajuanAkreditasi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VerifikasiSertifikatController extends Controller
{
    public function show(string $nomorSertifikat)
    {
        $pengajuan = PengajuanAkreditasi::with([
            'studyProgram.university',
            'studyProgram.degreeLevel',
            'asesmen.hasil.statusFinal',
            'asesmen.hasil.statusAl',
        ])
            ->where('nomor_sertifikat', $nomorSertifikat)
            ->first();

        if (!$pengajuan) {
            return view('public.verifikasi-sertifikat', [
                'valid'          => false,
                'nomorSertifikat' => $nomorSertifikat,
                'pengajuan'      => null,
            ]);
        }

        $hasil = $pengajuan->asesmen->hasil ?? null;
        $tanggalSertifikat = $pengajuan->tanggal_sertifikat ?? $pengajuan->tanggal_penetapan;

        $masaBerlakuTahun = $pengajuan->masa_berlaku_tahun
            ?? $hasil?->statusFinal?->siklus_tahun
            ?? $hasil?->statusAl?->siklus_tahun
            ?? null;

        $tanggalBerakhir = $tanggalSertifikat && $masaBerlakuTahun
            ? Carbon::parse($tanggalSertifikat)->addYears($masaBerlakuTahun)
            : null;

        $masihBerlaku = $tanggalBerakhir ? $tanggalBerakhir->isFuture() : null;

        $peringkat = $pengajuan->peringkat_final
            ?? $pengajuan->peringkat_hasil_banding
            ?? $hasil?->peringkat_akreditasi_final
            ?? $hasil?->peringkat_akreditasi_hasil
            ?? '-';

        if ($pengajuan->jenis_akreditasi === PengajuanAkreditasi::AKREDITASI_BARU) {
            $peringkat = 'Terakreditasi Pertama';
        }

        return view('public.verifikasi-sertifikat', [
            'valid'            => true,
            'nomorSertifikat'  => $nomorSertifikat,
            'pengajuan'        => $pengajuan,
            'hasil'            => $hasil,
            'peringkat'        => $peringkat,
            'tanggalSertifikat' => $tanggalSertifikat,
            'masaBerlakuTahun' => $masaBerlakuTahun,
            'tanggalBerakhir'  => $tanggalBerakhir,
            'masihBerlaku'     => $masihBerlaku,
        ]);
    }
}
