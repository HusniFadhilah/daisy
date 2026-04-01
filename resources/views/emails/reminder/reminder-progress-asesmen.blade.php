@extends('emails.template')

@php
$title = 'Pengingat Progress ' . $asesmenLabel;
$preheader = 'Segera selesaikan tugas ' . $asesmenLabel;
$headerTitle = 'Pengingat Progress ' . $asesmenLabel;
$pengajuan = $assignment->asesmen->pengajuan;
@endphp

@section('content')
<p>Kepada Yth,</p>

<p style="font-size:16px;">
    <strong>{{ $assignment->user->name }}</strong>
</p>

<p>
    Anda ditugaskan sebagai <strong>{{ $asesmenRoleLabel }}</strong>.
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

<p>Mohon segera menindaklanjuti tugas Anda agar proses asesmen dapat berjalan sesuai jadwal.</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;">
                Buka Halaman Asesmen
            </a>
        </td>
    </tr>
</table>
@endsection
