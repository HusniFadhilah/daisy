@extends('errors::custom-illustration')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/error-404.png')

@section('message')
{{ 'Anda telah mengirimkan terlalu banyak permintaan dalam waktu singkat. Untuk keamanan, akses Anda dibatasi sementara. Silakan tunggu beberapa saat sebelum mencoba lagi.' }}
@endsection

@section('actions')
<a href="{{ url('/') }}" class="btn btn-primary">
    <i class="bi bi-house-door"></i>
    <span>Kembali ke Beranda</span>
</a>
@endsection

@section('meta')
Pembatasan akan diangkat secara otomatis. Jika Anda memerlukan bantuan, hubungi
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

    .error-code {
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.6;
        }
    }

</style>
@endpush
