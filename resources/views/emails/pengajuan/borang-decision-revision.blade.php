@extends('emails.template')

@section('content')
<p style="margin-top:0;">Yth. Tim Akreditasi Program Studi <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

<p>
    Dokumen akreditasi Anda telah divalidasi oleh validator dan <strong>memerlukan revisi</strong>
    sebelum dapat dilanjutkan ke tahap berikutnya.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            {{-- <strong>Program Studi</strong> : {{ $pengajuan->studyProgram->name }}<br>
            <strong>Perguruan Tinggi</strong> : {{ $pengajuan->studyProgram->university->name ?? '-' }}<br> --}}
            <strong>Status</strong> :
            <span style="display:inline-block;padding:4px 10px;font-size:12px;font-weight:bold;border-radius:12px;background:#dc3545;color:#ffffff;">
                Perlu Revisi
            </span>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Ringkasan Validasi:</strong><br>
            Validator menemukan beberapa bagian dokumen yang masih perlu diperbaiki.
            @if($revisionCount > 0)
            Terdapat <strong>{{ $revisionCount }} poin revisi</strong> yang perlu ditindaklanjuti.
            @endif
            Silakan buka detail validasi di sistem untuk melihat hasil review lengkap.
        </td>
    </tr>
</table>

@if(!empty($validation->catatan_validator))
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Catatan Umum Validator:</strong><br>
            {{ $validation->catatan_validator }}
        </td>
    </tr>
</table>
@endif

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
