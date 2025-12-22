@extends('errors::custom-illustration')

@section('title', 'Akses Ditolak')
@section('code', '403')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/error-404.png')

@section('message')
{{ $exception ? $exception->getMessage() : 'Maaf, Anda tidak memiliki izin untuk mengakses halaman ini. Silakan hubungi administrator jika Anda merasa ini adalah kesalahan.' }}
@endsection

@section('actions')
<a href="{{ url('/') }}" class="btn btn-primary">
    <i class="bi bi-house-door"></i>
    <span>Kembali ke Beranda</span>
</a>
@auth
<a href="{{ route('dashboard') }}" class="btn-secondary">
    <i class="bi bi-speedometer2"></i>
    <span>Dashboard</span>
</a>
@else
<a href="{{ route('login') }}" class="btn-secondary">
    <i class="bi bi-box-arrow-in-right"></i>
    <span>Login</span>
</a>
@endauth
@endsection

@section('meta')
Jika Anda memerlukan akses, hubungi administrator di
@endsection

@push('styles')
<style>
    .error-message {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        padding: 1rem;
        border-radius: 8px;
        display: inline-block;
        color: #721c24;
    }

</style>
@endpush
