@extends('emails.template')

@php
$title = 'Pengingat Masa Akreditasi';
$preheader = 'Pengingat masa berlaku akreditasi program studi Anda';
$headerTitle = 'Pengingat Masa Akreditasi';
@endphp

@section('content')
<p style="margin-top:0;">Yth. Tim Akreditasi Program Studi <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

<!-- INFO BOX -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;">
            <p style="margin:0;font-size:14px;line-height:1.6;">
                {{ $pesanPengingat }}
            </p>
        </td>
    </tr>
</table>

<p>Untuk melanjutkan proses akreditasi, silakan:</p>

<!-- LIST (email-safe) -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:10px 0;">
    <tr>
        <td style="line-height:1.6;">
            <ol>
                <li>Login ke sistem</li>
                <li>Submit permohonan akreditasi</li>
                <li>Ikuti tahapan selanjutnya sesuai panduan</li>
            </ol>
        </td>
    </tr>
</table>

<!-- BUTTON -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ route('upps.pengingat-akreditasi') }}" style="
                background:#932136;
                color:#ffffff;
                text-decoration:none;
                padding:12px 28px;
                border-radius:6px;
                font-size:14px;
                display:inline-block;
                font-weight:bold;
            ">
                Lihat Detail
            </a>
        </td>
    </tr>
</table>
@endsection
