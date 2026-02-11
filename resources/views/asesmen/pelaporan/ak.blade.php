{{-- resources/views/asesmen/pelaporan/ak.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaporan Validasi AK')

@push('styles')
<style>
    .redirect-notice {
        background: linear-gradient(135deg, #0dcaf0 0%, #0a8fbd 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
    }

    .countdown {
        font-size: 3rem;
        font-weight: bold;
        margin: 1rem 0;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2>
                <i class="bi bi-clipboard-check text-white"></i>
                Pelaporan Validasi AK
            </h2>
            <p class="mb-0">Kelola pelaporan hasil validasi Asesmen Kecukupan (AK)</p>
        </div>
    </div>

    <!-- Redirect Notice -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="redirect-notice">
                <i class="bi bi-info-circle" style="font-size: 4rem;"></i>
                <h3 class="mt-3 mb-3">Halaman Dialihkan</h3>

                <p class="mb-4">
                    Untuk pelaporan AK, silakan gunakan halaman
                    <strong>Pelaporan Validasi AK</strong>.
                </p>

                <div class="countdown" id="countdown">5</div>

                <p class="mb-4">
                    Anda akan dialihkan secara otomatis dalam <span id="seconds">5</span> detik...
                </p>

                <a href="{{ route('pelaporan.indexValidasiAK') }}" class="btn btn-light btn-md">
                    <i class="bi bi-arrow-right"></i> Pergi Sekarang
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var countdown = 5;
    var countdownElement = document.getElementById('countdown');
    var secondsElement = document.getElementById('seconds');

    var timer = setInterval(function() {
        countdown--;
        countdownElement.textContent = countdown;
        secondsElement.textContent = countdown;

        if (countdown === 0) {
            clearInterval(timer);
            window.location.href = "{{ route('pelaporan.indexValidasiAK') }}";
        }
    }, 1000);

</script>
@endpush
