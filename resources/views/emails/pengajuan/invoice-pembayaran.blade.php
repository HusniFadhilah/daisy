@extends('emails.template')

@section('content')
<p style="margin-top:0;">Yth. Tim Akreditasi Program Studi <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

@if($isBanding)
<p>
    Bersama email ini kami sampaikan bahwa <strong>invoice pembayaran banding</strong>
    telah diterbitkan untuk pengajuan banding program studi.
</p>
@else
<p>
    Bersama email ini kami sampaikan bahwa <strong>invoice pembayaran akreditasi</strong>
    telah diterbitkan untuk permohonan akreditasi program studi.
</p>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Nomor Invoice</strong> : {{ $pembayaran->nomor_invoice }}<br>
            {{-- <strong>Program Studi</strong> : {{ $pengajuan->studyProgram->name }}<br>
            <strong>Perguruan Tinggi</strong> : {{ $pengajuan->studyProgram->university->name ?? '-' }}<br> --}}
            <strong>Jumlah Pembayaran</strong> :
            Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}<br>
            <strong>Jatuh Tempo</strong> :
            {{ \Carbon\Carbon::parse($pembayaran->tanggal_jatuh_tempo)->locale('id')->translatedFormat('d F Y') }}
            @if(!empty($pembayaran->keterangan))
            <br><strong>Keterangan</strong> : {{ $pembayaran->keterangan }}
            @endif
        </td>
    </tr>
</table>

@if($templateFormulirPembayaran)
<table width="100%" cellpadding="0" cellspacing="0" style="background:#e9f7ef;border-left:4px solid #198754;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Lampiran Tambahan:</strong><br>
            Templat formulir pembayaran telah disertakan pada email ini atau tersedia pada sistem.
        </td>
    </tr>
</table>
@endif

<p>Langkah selanjutnya:</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:10px 0 20px 0;">
    <tr>
        <td style="font-size:14px;line-height:1.8;">
            • Periksa detail invoice pembayaran<br>
            • Lengkapi formulir pembayaran yang tersedia<br>
            • Lakukan pembayaran sebelum tanggal jatuh tempo<br>
            • Upload formulir dan bukti pembayaran melalui sistem
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Lihat Detail Pembayaran
            </a>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Catatan:</strong><br>
            @if($isBanding)
            Invoice ini diterbitkan khusus untuk <strong>proses banding akreditasi</strong>.
            <br>
            @endif
            {{ $pembayaran->keterangan }}
        </td>
    </tr>
</table>

@endsection
