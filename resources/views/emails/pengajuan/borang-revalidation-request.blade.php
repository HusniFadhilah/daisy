@extends('emails.template')

@section('content')
<p style="margin-top:0;">Yth. Validator,</p>

<p>
    Dokumen akreditasi hasil revisi dari program studi berikut telah siap untuk <strong>direview ulang</strong>.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Program Studi</strong> : {{ $pengajuan->studyProgram->name }}<br>
            <strong>Perguruan Tinggi</strong> : {{ $pengajuan->studyProgram->university->name ?? '-' }}<br>
            <strong>Tahun Akreditasi</strong> : {{ $pengajuan->tahun_akreditasi ?? '-' }}
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#e7f1ff;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Ringkasan:</strong><br>
            Program studi telah mengunggah perbaikan dokumen dan menunggu validasi ulang dari validator.
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}"
               style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Buka Halaman Validasi
            </a>
        </td>
    </tr>
</table>
@endsection
