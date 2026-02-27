@extends('emails.template')

@php
$isValidasi = $assignment->jenis_asesmen === 'dokumen';
$labelTugas = $isValidasi ? 'validasi' : 'penilaian';
$menuTugas = $isValidasi ? 'Validasi Dokumen' : 'Berkas Penilaian';
$deskripsiFn = $isValidasi
? 'Lakukan validasi sesuai dokumen yang telah dikirim prodi'
: 'Lakukan penilaian sesuai elemen standar';

$title = 'Penawaran Diterima';
$preheader = 'Anda telah menerima penawaran sebagai ' . $role->alias;
$headerTitle = 'Penawaran Diterima';
@endphp

@section('content')
<p style="margin-top:0;">Yth. <strong>{{ $user->name }}</strong>,</p>

<p>Terima kasih telah menerima penawaran sebagai <strong>{{ $role->alias }}</strong> untuk asesmen berikut:</p>

{{-- INFO BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Asesmen</strong> : {{ $asesmen->name }}<br>
            <strong>Jenis</strong> : {{ $jenisAsesmen }}<br>
            <strong>Status</strong> : Diterima<br>
            <strong>Waktu Respons</strong> : {{ \Carbon\Carbon::parse($assignment->responded_at)->locale('id')->translatedFormat('d F Y H:i') }}
        </td>
    </tr>
</table>

@if($assignment->response_note)
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #6c757d;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;">
            <strong>Catatan Anda:</strong><br>
            {{ $assignment->response_note }}
        </td>
    </tr>
</table>
@endif

<p>Anda sekarang dapat memulai {{ $labelTugas }} melalui dashboard:</p>

{{-- BUTTON --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $dashboardUrl }}" style="
                    background:#932136;
                    color:#ffffff;
                    text-decoration:none;
                    padding:12px 32px;
                    border-radius:6px;
                    font-size:14px;
                    display:inline-block;
                ">
                Mulai {{ ucfirst($labelTugas) }}
            </a>
        </td>
    </tr>
</table>

{{-- PANDUAN --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #6c757d;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:13px;color:#495057;line-height:1.8;">
            <strong>Panduan:</strong><br>
            &bull; Login ke sistem<br>
            &bull; Akses menu <strong>{{ $menuTugas }}</strong><br>
            &bull; {{ $deskripsiFn }}<br>
            &bull; Submit {{ $labelTugas }} setelah selesai
        </td>
    </tr>
</table>
@endsection
