@extends('emails.template')

@section('content')
<p style="margin-top:0;">Yth. Tim Akreditasi Program Studi <strong>{{ $pengajuan->studyProgram->name }}</strong>,</p>

<p>
    Bersama email ini kami sampaikan bahwa <strong>Surat Penerimaan Permohonan Akreditasi</strong>
    telah diterbitkan oleh LAMDEPILAR.
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #198754;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Nomor Permohonan</strong> : {{ $pengajuan->nomor_pengajuan }}<br>
            <strong>Program Studi</strong> : {{ $pengajuan->studyProgram->name }}<br>
            <strong>Perguruan Tinggi</strong> : {{ $pengajuan->studyProgram->university->name ?? '-' }}<br>
            <strong>Dokumen</strong> : {{ $dokumen->original_filename ?? $dokumen->nama_file }}<br>
            <strong>Tanggal Dikirim</strong> : {{ now()->locale('id')->translatedFormat('d F Y H:i') }}
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#e9f7ef;border-left:4px solid #198754;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Informasi Penting:</strong><br>
            Surat penerimaan permohonan akreditasi telah <strong>disertakan sebagai lampiran</strong>
            pada email ini. Silakan unduh dan gunakan dokumen tersebut sesuai kebutuhan proses akreditasi.
        </td>
    </tr>
</table>

<p>Langkah selanjutnya yang dapat dilakukan:</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:10px 0 20px 0;">
    <tr>
        <td style="font-size:14px;line-height:1.8;">
            • Login ke sistem akreditasi<br>
            • Buka detail penerimaan permohonan akreditasi<br>
            • Periksa status terbaru dan dokumen yang telah diterbitkan<br>
            • Lanjutkan proses sesuai tahapan yang berlaku
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:14px;display:inline-block;font-weight:bold;">
                Lihat Detail Penerimaan
            </a>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#fff3cd;border-left:4px solid #ffc107;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Catatan:</strong><br>
            Jika lampiran tidak terlihat pada email Anda, silakan periksa folder spam/promosi
            atau akses detail pengajuan melalui sistem.
        </td>
    </tr>
</table>
@endsection
