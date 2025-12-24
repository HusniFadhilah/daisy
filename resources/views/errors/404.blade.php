@extends('errors::custom-illustration')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/error-404.png')

@section('message')
{{ 'Maaf, halaman yang Anda cari tidak dapat ditemukan. Halaman mungkin telah dipindahkan, dihapus, atau URL yang Anda masukkan salah.' }}
@endsection

@section('actions')
<a href="{{ url('/') }}" class="btn btn-primary">
    <i class="bi bi-house-door"></i>
    <span>Kembali ke Beranda</span>
</a>
<a href="{{ url()->previous() }}" class="btn-secondary">
    <i class="bi bi-arrow-left"></i>
    <span>Halaman Sebelumnya</span>
</a>
@endsection

@section('meta')
Jika Anda yakin ini adalah kesalahan sistem, silakan hubungi
@endsection
