@extends('errors::custom-illustration')

@section('title', 'Sesi Telah Berakhir')
@section('code', '419')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/error-404.png')

@section('message')
{{ 'Sesi Anda telah berakhir atau halaman sudah kedaluwarsa. Ini terjadi karena Anda tidak aktif terlalu lama. Silakan muat ulang halaman dan coba lagi.' }}
@endsection

@section('actions')
<a href="javascript:window.location.reload()" class="btn btn-primary">
    <i class="bi bi-arrow-clockwise"></i>
    <span>Muat Ulang Halaman</span>
</a>
<a href="{{ url('/') }}" class="btn-secondary">
    <i class="bi bi-house-door"></i>
    <span>Kembali ke Beranda</span>
</a>
@endsection

@section('meta')
Untuk keamanan, sesi akan berakhir setelah periode tidak aktif. Jika ada pertanyaan, hubungi
@endsection

@push('styles')
<style>
    .error-message {
        background: #fff3cd;
        border-left: 4px solid #856404;
        padding: 1rem;
        border-radius: 8px;
        display: inline-block;
        color: #856404;
    }

</style>
@endpush
