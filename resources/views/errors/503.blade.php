@extends('errors::custom-illustration')

@section('title', 'Layanan Sedang Tidak Tersedia')
@section('code', '503')

@section('illustration', 'https://demos.creative-tim.com/soft-ui-dashboard-pro/assets/img/illustrations/error-404.png')

@section('message')
Maaf, layanan DAISY sedang dalam pemeliharaan atau mengalami gangguan sementara. Kami sedang bekerja untuk mengembalikan layanan secepat mungkin.
@endsection

@section('actions')
<a href="javascript:window.location.reload()" class="btn btn-primary">
    <i class="bi bi-arrow-clockwise"></i>
    <span>Coba Lagi</span>
</a>
<a href="{{ url('/') }}" class="btn-secondary">
    <i class="bi bi-house-door"></i>
    <span>Kembali ke Beranda</span>
</a>
@endsection

@section('meta')
Untuk informasi lebih lanjut, hubungi
@endsection

@push('styles')
<style>
    /* Optional: Custom styles untuk 503 */
    .error-message {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        border-radius: 8px;
        display: inline-block;
    }

</style>
@endpush
