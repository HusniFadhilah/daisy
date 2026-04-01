@extends('emails.template')

@php
$title = 'Pengingat Penawaran ' . $asesmenRoleLabel;
$preheader = 'Segera konfirmasi penawaran ' . $asesmenRoleLabel;
$headerTitle = 'Pengingat Penawaran ' . $asesmenRoleLabel;
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

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;">
            {{ $pesanReminder }}
        </td>
    </tr>
</table>

<p>Mohon segera memberikan konfirmasi terhadap penawaran ini melalui sistem.</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;">
                Lihat Penawaran
            </a>
        </td>
    </tr>
</table>
@endsection
