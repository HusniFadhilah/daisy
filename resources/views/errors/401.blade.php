@extends('errors::custom-illustration')

@section('title', 'Autentikasi Diperlukan')
@section('code', '401')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/error-404.png')

@section('message')
{{ 'Anda perlu login terlebih dahulu untuk mengakses halaman ini. Silakan login dengan akun yang telah terdaftar.' }}
@endsection

@section('actions')
<a href="{{ route('login') }}" class="btn btn-primary">
    <i class="bi bi-box-arrow-in-right"></i>
    <span>Login</span>
</a>
<a href="{{ url('/') }}" class="btn btn-secondary">
    <i class="bi bi-house-door"></i>
    <span>Kembali ke Beranda</span>
</a>
@endsection

@section('meta')
Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a> atau hubungi
@endsection

@push('styles')
<style>
    .error-message {
        background: #d1ecf1;
        border-left: 4px solid #0c5460;
        padding: 1rem;
        border-radius: 8px;
        display: inline-block;
        color: #0c5460;
    }

</style>
@endpush
