@extends('emails.template')

@php
$title = 'Pengingat Pelaporan Dokumen';
//$preheader = 'Segera upload laporan validasi dokumen';
$headerTitle = 'Pengingat Pelaporan Dokumen';
$pengajuan = $assignment->asesmen->pengajuan;
@endphp

@section('content')
<p>Kepada Yth,</p>

<p style="font-size:16px;">
    <strong>{{ $assignment->user->name }}</strong>
</p>

<p>
    Berikut informasi penugasan Anda:<br>
    Program Studi: <strong>{{ $pengajuan->studyProgram->name ?? '-' }}</strong><br>
    Universitas: <strong>{{ $pengajuan->studyProgram->university->name ?? '-' }}</strong>
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;">
            {{ $pesanReminder }}
        </td>
    </tr>
</table>

<p>Mohon segera mengunggah laporan validasi dokumen agar proses dapat dilanjutkan.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Lihat Halaman Pelaporan
            </a>
        </td>
    </tr>
</table>
@endsection
