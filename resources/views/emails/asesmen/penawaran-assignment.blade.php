@extends('emails.template')

@php
$isValidasi = $assignment->jenis_asesmen === 'dokumen';
$labelTugas = $isValidasi ? 'validasi' : 'penilaian';
$menuTugas = $isValidasi ? 'Validasi Dokumen' : 'Berkas Penilaian';
$deskripsi = $isValidasi
? 'Lakukan validasi sesuai dokumen yang telah dikirim prodi'
: 'Lakukan penilaian sesuai elemen standar';

$title = 'Penawaran ' . $role->alias;
$preheader = 'Anda ditunjuk sebagai ' . $role->alias . ' untuk ' . $asesmen->name;
$headerTitle = 'Penawaran ' . $role->alias;
@endphp

@section('content')
<p style="margin-top:0;">Yth. <strong>{{ $user->name }}</strong>,</p>

<p>Anda telah ditunjuk sebagai <strong>{{ $role->alias }}</strong> untuk asesmen berikut:</p>

{{-- INFO BOX --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #0d6efd;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:14px;line-height:1.8;">
            <strong>Asesmen</strong> : {{ $asesmen->name }}<br>
            <strong>Jenis</strong> : {{ $jenisAsesmen }}<br>
            <strong>Role</strong> : {{ $role->alias }}
            @if($assignment->urutan_asesor)
            <br><strong>Urutan</strong> : Asesor {{ $assignment->urutan_asesor }}
            @endif
        </td>
    </tr>
</table>

<p>
    Silakan gunakan akun <strong>{{ $user->email }}</strong> untuk
    <strong>menerima atau menolak</strong> penawaran ini melalui tombol berikut:
</p>

{{-- BUTTON --}}
<table width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0;">
    <tr>
        <td align="center">
            <a href="{{ $acceptUrl }}" style="
                    background:#932136;
                    color:#ffffff;
                    text-decoration:none;
                    padding:12px 32px;
                    border-radius:6px;
                    font-size:14px;
                    display:inline-block;
                ">
                Lihat Detail & Respon
            </a>
        </td>
    </tr>
</table>

{{-- CATATAN --}}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;border-left:4px solid #6c757d;margin:20px 0;">
    <tr>
        <td style="padding:15px;font-size:13px;color:#495057;line-height:1.8;">
            <strong>Catatan Penting:</strong><br>
            &bull; Harap berikan respons segera mungkin<br>
            &bull; Jika menolak, mohon berikan alasan yang jelas<br>
            &bull; Setelah menerima, Anda dapat langsung memulai penilaian
        </td>
    </tr>
</table>
@endsection
