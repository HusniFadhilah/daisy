@extends('emails.template')
@php
$title = 'Penawaran Validasi Dokumen';
$preheader = 'Anda ditunjuk sebagai Validator Borang untuk ' . $pengajuan->nomor_pengajuan;
$headerTitle = 'Penawaran Validasi Dokumen';
@endphp

@section('content')
<p style="margin-top:0;">Yth. <strong>{{ $assignment->user->name }}</strong>,</p>

<p>Anda telah ditunjuk sebagai <strong>Validator Borang</strong> untuk pengajuan berikut:</p>

{{-- INFO BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Pengajuan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Program Studi</strong> : {{ $pengajuan->studyProgram->name ?? '-' }}<br>
            <strong>Jenjang</strong> : {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}<br>
            <strong>Perguruan Tinggi</strong> : {{ $pengajuan->studyProgram->university->name ?? '-' }}
        </td>
    </tr>
</table>

@if($catatanDe)
{{-- CATATAN DE --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;">
            <strong>Catatan DE:</strong><br>
            {{ $catatanDe }}
        </td>
    </tr>
</table>
@endif

<p>Silakan gunakan akun <strong>{{ $assignment->user->email }}</strong> untuk <strong>menerima atau menolak</strong> penawaran ini melalui tombol berikut:</p>

{{-- BUTTON --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $acceptUrl }}" style="
                    background:#932136;
                    color:#ffffff;
                    text-decoration:none;
                    padding:12px 32px;
                    border-radius:6px;
                    font-size:14px;
                    display:inline-block;
                ">
                Lihat Detail & Respond
            </a>
        </td>
    </tr>
</table>

{{-- CATATAN PENTING --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #6c757d;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:13px;color:#495057;line-height:1.8;">
            <strong>Catatan Penting:</strong><br>
            &bull; Harap berikan respons segera mungkin<br>
            &bull; Jika menolak, mohon berikan alasan yang jelas<br>
            &bull; Setelah menerima, Anda dapat langsung memulai penilaian
        </td>
    </tr>
</table>
@endsection
