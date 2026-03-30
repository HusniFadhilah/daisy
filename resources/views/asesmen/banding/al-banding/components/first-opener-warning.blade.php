@extends('layouts.template.app')

@section('title', 'Konfirmasi Akses Penilaian AL Banding')

@section('content')
<div class="container py-5" style="max-width: 640px;">

    {{-- Icon besar --}}
    <div class="text-center mb-4">
        @if($isFirstOpener)
        <div class="mb-3">
            <span style="font-size: 4rem;">🔓</span>
        </div>
        <h4 class="fw-bold">Anda Akan Membuka Halaman Penilaian AL Banding</h4>
        <p class="text-muted">Belum ada asesor banding lain yang membuka halaman ini sebelumnya.</p>
        @else
        <div class="mb-3">
            <span style="font-size: 4rem;">🔒</span>
        </div>
        <h4 class="fw-bold">Halaman Penilaian Sudah Dibuka</h4>
        <p class="text-muted">
            Asesor banding lain sudah lebih dulu membuka halaman penilaian AL Banding ini.
        </p>
        @endif
    </div>

    {{-- Card utama --}}
    <div class="card shadow-sm">
        <div class="card-body p-4">

            @if($isFirstOpener)
            {{-- ── KASUS 1: Saya yang pertama ─────────────────────── --}}
            <div class="alert alert-success alert-permanent border-start border-2 border-success mb-4">
                <h6 class="fw-bold mb-2">
                    <i class="bi bi-shield-check me-2"></i>Pengisian Penilaian Akan Diberikan kepada Anda
                </h6>
                <p class="mb-2 small">
                    Begitu Anda klik <strong>Lanjutkan</strong>, Anda menjadi satu-satunya asesor banding
                    yang dapat:
                </p>
                <ul class="mb-0 small">
                    <li>Membuka halaman penilaian AL Banding ini</li>
                    <li>Mengisi form penilaian elemen</li>
                    <li>Mengupload file Excel penilaian</li>
                    <li>Memfinalisasi dan mengirim penilaian</li>
                </ul>
            </div>

            <div class="alert alert-warning alert-permanent border-start border-2 border-warning mb-4">
                <h6 class="fw-bold mb-1">
                    <i class="bi bi-people me-2"></i>Asesor Banding Lain Tidak Akan Bisa Mengisi
                </h6>
                <p class="mb-0 small">
                    Setelah Anda lanjutkan, asesor banding lain dalam tim akan mendapat pesan
                    bahwa Anda sudah memegang bagian penilaian dan mereka tidak bisa mengisi.
                </p>
            </div>

            @else
            {{-- ── KASUS 2: Orang lain sudah duluan ───────────────── --}}
            <div class="alert alert-danger alert-permanent border-start border-2 border-danger mb-4">
                <h6 class="fw-bold mb-2">
                    <i class="bi bi-lock-fill me-2"></i>
                    Penilaian Sudah Dibuka oleh
                    <strong>{{ $firstOpenerUser?->name ?? 'Asesor Banding Lain' }}</strong>
                </h6>
                <p class="mb-2 small">
                    Asesor banding <strong>{{ $firstOpenerUser?->name ?? 'lain' }}</strong> sudah lebih
                    dulu membuka halaman ini. Anda <strong>tidak dapat</strong>:
                </p>
                <ul class="mb-0 small">
                    <li>Mengisi form penilaian elemen</li>
                    <li>Mengupload file Excel penilaian</li>
                    <li>Memfinalisasi dan mengirim penilaian</li>
                </ul>
            </div>

            <div class="alert alert-info alert-permanent border-start border-2 border-info mb-4">
                <h6 class="fw-bold mb-1">
                    <i class="bi bi-eye me-2"></i>Yang Masih Dapat Anda Lakukan
                </h6>
                <p class="mb-0 small">
                    Anda tetap dapat membuka halaman untuk <em>melihat</em> data penilaian
                    dan mengunduh hasilnya dalam format Excel.
                </p>
            </div>
            @endif

        </div>

        {{-- Footer dengan tombol --}}
        <div class="card-footer bg-white border-top py-3">
            <form method="POST" action="{{ $confirmRoute }}">
                @csrf
                <input type="hidden" name="continue_url" value="{{ $continueUrl }}">

                <div class="d-flex justify-content-between align-items-center gap-3">
                    <a href="{{ route('al_banding.berkas') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penilaian
                    </a>

                    @if($isFirstOpener)
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-pencil-square me-1"></i>
                        Lanjutkan &amp; Mulai Penilaian
                    </button>
                    @else
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-eye me-1"></i>
                        Lanjutkan (Hanya Melihat)
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
