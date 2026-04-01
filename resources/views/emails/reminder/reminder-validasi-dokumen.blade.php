@extends('emails.template')

@php
$title = 'Pengingat Validasi Dokumen';
$preheader = 'Segera lakukan validasi dokumen';
$headerTitle = 'Pengingat Validasi Dokumen';
$pengajuan = $assignment->asesmen->pengajuan;
@endphp

@section('content')
<p>Kepada Yth,</p>

<p style="font-size:16px;">
    <strong>{{ $assignment->user->name }}</strong>
</p>

<p>
    Program Studi: <strong>{{ $pengajuan->studyProgram->name ?? '-' }}</strong><br>
    Universitas: <strong>{{ $pengajuan->studyProgram->university->name ?? '-' }}</strong>
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;">
            {{ $pesanReminder }}
        </td>
    </tr>
</table>

<p>Silakan login ke sistem untuk melanjutkan proses validasi dokumen.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Lihat Halaman Validasi
            </a>
        </td>
    </tr>
</table>
@endsection
