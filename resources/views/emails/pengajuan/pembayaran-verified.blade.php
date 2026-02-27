@extends('emails.template')

@php
$title = $isVerified ? 'Pembayaran Diverifikasi' : 'Bukti Pembayaran Perlu Upload Ulang';
$preheader = $isVerified
? 'Pembayaran Anda telah diverifikasi. Silakan upload Borang Final.'
: 'Bukti Pembayaran Anda perlu diupload ulang. Silakan lakukan upload ulang bukti pembayaran.';
$headerTitle = $isVerified ? 'Pembayaran Diverifikasi' : 'Bukti Pembayaran Perlu Upload Ulang';
@endphp

@section('content')
<p style="margin-top:0;">Yth. Tim <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

@if($isVerified)
<p>Pembayaran akreditasi Anda telah <strong>DIVERIFIKASI</strong> oleh LAMDEPILAR.</p>

{{-- INFO BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #28a745;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Pengajuan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Invoice</strong> : {{ $pengajuan->pembayaran->nomor_invoice }}<br>
            <strong>Jumlah Pembayaran</strong> : Rp {{ number_format($pengajuan->pembayaran->jumlah_pembayaran, 0, ',', '.') }}<br>
            <strong>Status</strong> : <span style="color:#28a745;">VERIFIED</span>
        </td>
    </tr>
</table>

{{-- LANGKAH SELANJUTNYA --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#d1ecf1;border-left:4px solid #0dcaf0;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Langkah Selanjutnya:</strong><br>
            &bull; Login ke sistem<br>
            &bull; Buka detail Permohonan Akreditasi Anda<br>
            &bull; Upload <strong>Borang Final</strong> yang telah lengkap<br>
            &bull; <strong>PENTING:</strong> Pastikan tidak ada revisi data kuantitatif/kualitatif
        </td>
    </tr>
</table>

{{-- PERINGATAN --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;">
            <strong>Catatan Penting:</strong><br>
            Borang final yang diupload harus sudah final dan tidak boleh ada perubahan data setelah ini.
            Pastikan semua data telah sesuai sebelum upload!
        </td>
    </tr>
</table>

@else
<p>Pembayaran akreditasi Anda <strong>Perlu Mengupload Ulang</strong> oleh LAMDEPILAR.</p>

{{-- INFO BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Pengajuan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Invoice</strong> : {{ $pengajuan->pembayaran->nomor_invoice }}<br>
            <strong>Status</strong> : <span style="color:#dc3545;">Permintaan Upload Ulang</span>
        </td>
    </tr>
</table>

{{-- ALASAN PENOLAKAN --}}
@if($pengajuan->pembayaran->alasan_penolakan)
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8d7da;border-left:4px solid #dc3545;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;">
            <strong>Catatan LAMDEPILAR:</strong><br>
            {{ $pengajuan->pembayaran->alasan_penolakan }}
        </td>
    </tr>
</table>
@endif

{{-- LANGKAH SELANJUTNYA --}}
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
@endsection
