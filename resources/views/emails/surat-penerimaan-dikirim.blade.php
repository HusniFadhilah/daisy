@extends('emails.layouts.template', [
'title' => 'Penerimaan Permohonan Akreditasi',
'headerTitle' => 'Penerimaan Permohonan Akreditasi',
'preheader' => 'Penerimaan permohonan akreditasi telah dikirim untuk ' . $pengajuan->studyProgram->name . '.'
])

@section('content')
<p style="margin:0 0 12px 0;">Kepada Yth,</p>

<p style="font-size:16px;margin:0 0 10px 0;">
    <strong>{{ $notifiableName }}</strong>
</p>

{{-- INFO BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #932136;margin:20px 0;">
    <tr>
        <td style="padding:15px;">
            <p style="margin:0;font-size:14px;line-height:1.5;">
                Penerimaan permohonan akreditasi telah dikirimkan oleh LAMDEPILAR.
            </p>
        </td>
    </tr>
</table>

{{-- DETAIL BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #932136;margin:20px 0;">
    <tr>
        <td style="padding:15px;">
            <p style="margin:0 0 10px 0;font-size:14px;line-height:1.5;">
                <strong>Program Studi:</strong> {{ $pengajuan->studyProgram->full_name }}
            </p>
            <p style="margin:0 0 10px 0;font-size:14px;line-height:1.5;">
                <strong>Nomor Permohonan:</strong> {{ $pengajuan->nomor_pengajuan }}
            </p>
            <p style="margin:0;font-size:14px;line-height:1.5;">
                <strong>Tanggal Dikirim:</strong> {{ $dokumen->created_at->format('d M Y H:i') }}
            </p>
        </td>
    </tr>
</table>

<p style="margin:0 0 10px 0;">Untuk mengunduh file penerimaan permohonan akreditasi, silakan:</p>
<ol style="padding-left:18px;margin:0 0 20px 0;line-height:1.6;">
    <li>Login ke sistem</li>
    <li>Buka menu pengajuan akreditasi</li>
    <li>Unduh file penerimaan permohonan akreditasi</li>
</ol>

{{-- BUTTON --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $actionUrl }}" style="background:#932136;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:6px;font-size:14px;display:inline-block;">
                Lihat & Download File
            </a>
        </td>
    </tr>
</table>

<p style="margin:0;">Terima kasih atas perhatian Anda.</p>
@endsection
