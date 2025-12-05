@extends('errors::custom-illustration')

@section('title', 'Terjadi Kesalahan Server')
@section('code', '500')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/error-500.png')

@section('message')
Maaf, terjadi kesalahan pada server kami. Tim teknis kami telah diberitahu dan sedang memperbaiki masalah ini. Silakan coba beberapa saat lagi.
@endsection

@section('actions')
<a href="{{ url('/') }}" class="btn btn-primary">
    <i class="bi bi-house-door"></i>
    <span>Kembali ke Beranda</span>
</a>
<a href="javascript:window.location.reload()" class="btn-secondary">
    <i class="bi bi-arrow-clockwise"></i>
    <span>Muat Ulang Halaman</span>
</a>
@endsection

@section('meta')
Jika masalah terus berlanjut, mohon laporkan ke
@endsection
