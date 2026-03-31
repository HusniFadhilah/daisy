@extends('emails.template')

@section('content')
<p style="margin-top:0;">Yth. Tim <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

@if($isVerified)
<p>
    Pembayaran {{ strtolower($jenisLabel) }} Anda telah diproses oleh LAMDEPILAR.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #28a745;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Jenis Pembayaran</strong> : {{ $jenisLabel }}<br>
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Invoice</strong> : {{ $pembayaran->nomor_invoice }}<br>
            <strong>Jumlah Pembayaran</strong> : Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}<br>
            <strong>Status</strong> :
            <span style="display:inline-block;padding:4px 10px;font-size:12px;font-weight:bold;border-radius:12px;background:#28a745;color:#ffffff;">
                Tervalidasi
            </span>
        </td>
    </tr>
</table>

@if($isBanding)
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Keterangan Banding:</strong><br>
            Pembayaran ini tervalidasi untuk proses <strong>banding akreditasi</strong>.
            Silakan lanjutkan tahapan banding sesuai ketentuan yang berlaku.
        </td>
    </tr>
</table>
@else
<table width="100%" cellpadding="0" cellspacing="0" style="background:#d1ecf1;border-left:4px solid #0dcaf0;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Langkah Selanjutnya:</strong><br>
            &bull; Login ke sistem<br>
            &bull; Buka detail Permohonan Akreditasi Anda<br>
            &bull; Upload <strong>Dokumen Akreditasi</strong> yang telah lengkap
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;">
            <strong>Catatan Penting:</strong><br>
            Pastikan semua Dokumen Akreditasi (LED, LKPS, Suplemen) telah sesuai sebelum melakukan pengiriman dokumen.
        </td>
    </tr>
</table>
@endif

@else
<p>
    Bukti pembayaran {{ strtolower($jenisLabel) }} Anda perlu diupload ulang sesuai hasil pemeriksaan LAMDEPILAR.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Jenis Pembayaran</strong> : {{ $jenisLabel }}<br>
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Invoice</strong> : {{ $pembayaran->nomor_invoice }}<br>
            <strong>Status</strong> :
            <span style="color:#dc3545;font-weight:bold;">Permintaan Upload Ulang</span>
        </td>
    </tr>
</table>

@if($pembayaran->catatan_verifikasi || $pembayaran->alasan_penolakan)
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8d7da;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;">
            <strong>Catatan LAMDEPILAR:</strong><br>
            {{ $pembayaran->alasan_penolakan ?? $pembayaran->catatan_verifikasi }}
        </td>
    </tr>
</table>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Langkah Selanjutnya:</strong><br>
            &bull; Periksa catatan LAMDEPILAR di atas<br>
            &bull; Lakukan pembayaran ulang dengan benar<br>
            &bull; Upload bukti pembayaran yang valid
        </td>
    </tr>
</table>
@endif

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Lihat Detail Pembayaran
            </a>
        </td>
    </tr>
</table>
@endsection
