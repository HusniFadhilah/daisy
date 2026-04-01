@extends('emails.template')

@section('content')
<p style="margin-top:0;">Yth. Tim Akreditasi Program Studi <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

<p>
    Dokumen akreditasi yang Anda unggah telah <strong>disetujui</strong> oleh validator.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #198754;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            {{-- <strong>Program Studi</strong> : {{ $pengajuan->studyProgram->name }}<br>
            <strong>Perguruan Tinggi</strong> : {{ $pengajuan->studyProgram->university->name ?? '-' }}<br> --}}
            <strong>Status</strong> :
            <span style="display:inline-block;padding:4px 10px;font-size:12px;font-weight:bold;border-radius:12px;background:#198754;color:#ffffff;">
                Disetujui
            </span>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#e9f7ef;border-left:4px solid #198754;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Ringkasan:</strong><br>
            Validasi dokumen telah selesai dan dokumen dapat dilanjutkan ke tahap berikutnya.
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Lihat Detail Validasi
            </a>
        </td>
    </tr>
</table>
@endsection
