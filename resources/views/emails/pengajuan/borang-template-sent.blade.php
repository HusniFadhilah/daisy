@extends('emails.template')

@section('content')
<p style="margin-top:0;">Yth. Tim Akreditasi Program Studi <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

<p>
    LAMDEPILAR telah mengirimkan <strong>Templat Dokumen Akreditasi</strong>
    untuk permohonan akreditasi program studi Anda.
</p>

{{-- <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
<strong>Program Studi</strong> : {{ $pengajuan->studyProgram->name }}<br>
<strong>Jenjang</strong> : {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}<br>
<strong>Perguruan Tinggi</strong> : {{ $pengajuan->studyProgram->university->name ?? '-' }}
</td>
</tr>
</table> --}}

@if($metode === 'link')
{{-- <table width="100%" cellpadding="0" cellspacing="0" style="background:#e7f1ff;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Metode Pengiriman</strong> : Link<br>
            <strong>Akses Templat</strong> :
            <a href="{{ $dokumen->template_link }}">{{ $dokumen->template_link }}</a>
</td>
</tr>
</table> --}}

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $dokumen->template_link }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Buka Templat Dokumen
            </a>
        </td>
    </tr>
</table>
@else
{{-- <table width="100%" cellpadding="0" cellspacing="0" style="background:#e9f7ef;border-left:4px solid #198754;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Metode Pengiriman</strong> : Lampiran file<br>
            <strong>Nama File</strong> : {{ $dokumen->original_filename ?? $dokumen->nama_file }}<br>
@if(!empty($dokumen->file_size))
<strong>Ukuran File</strong> : {{ number_format($dokumen->file_size / 1024, 2) }} KB<br>
@endif
File templat telah disertakan sebagai lampiran pada email ini.
</td>
</tr>
</table> --}}
@endif

<p>Langkah selanjutnya:</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:10px 0 20px 0;">
    <tr>
        <td style="font-size:14px;line-height:1.8;">
            • Unduh atau buka templat dokumen<br>
            • Lengkapi dokumen sesuai panduan<br>
            • Upload draft LED dan LKPS melalui sistem
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Akses Templat Dokumen
            </a>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Catatan Penting:</strong><br>
            Pastikan dokumen diisi lengkap dan sesuai format yang ditentukan sebelum diunggah kembali ke sistem.
            @if(!empty($dokumen->keterangan) && $dokumen->keterangan != 'Templat Formulir Pembayaran' && $dokumen->keterangan != 'Templat Dokumen')
            {{ $dokumen->keterangan }}
            @endif
        </td>
    </tr>
</table>
@endsection
