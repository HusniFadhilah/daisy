@extends('emails.template')

@php
$isValidasi = $assignment->jenis_asesmen === 'dokumen';
$labelTugas = $isValidasi ? 'validasi' : 'penilaian';

$title = 'Penawaran Ditolak';
$preheader = 'Konfirmasi penolakan penawaran ' . $jenisAsesmen;
$headerTitle = 'Penawaran Ditolak';
@endphp

@section('content')
<p style="margin-top:0;">Yth. <strong>{{ $user->name }}</strong>,</p>

<p>Kami telah menerima penolakan Anda atas penawaran sebagai <strong>{{ $role->alias }}</strong> untuk asesmen berikut:</p>

{{-- INFO BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Asesmen</strong> : {{ $asesmen->name }}<br>
            <strong>Jenis</strong> : {{ $jenisAsesmen }}<br>
            <strong>Status</strong> : Ditolak<br>
            <strong>Waktu Penolakan</strong> : {{ \Carbon\Carbon::parse($assignment->responded_at)->locale('id')->translatedFormat('d F Y H:i') }}
        </td>
    </tr>
</table>

{{-- ALASAN --}}
@if($assignment->response_note)
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;">
            <strong>Alasan yang Anda berikan:</strong><br>
            {{ $assignment->response_note }}
        </td>
    </tr>
</table>
@endif

<p>Penolakan Anda telah dicatat dan tim kami akan menindaklanjuti penugasan {{ $labelTugas }} ini. Terima kasih atas respons Anda.</p>
@endsection
