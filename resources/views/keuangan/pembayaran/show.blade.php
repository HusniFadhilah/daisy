@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-4 gap-2">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-credit-card"></i>
                Validasi Pembayaran
            </h3>
            <p class="text-muted mb-0">
                {{ $pengajuan->judul }}
            </p>
        </div>

        <span class="badge bg-secondary align-self-md-center">
            Status Pembayaran:
            {{ strtoupper(optional($pengajuan->pembayaran)->status_pembayaran ?? '-') }}
        </span>
    </div>

    @php
    $pembayaran = $pengajuan->pembayaran;
    $path = optional($pembayaran)->bukti_path;
    $ext = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
    $url = $path ? Storage::disk('public')->url($path) : null;
    @endphp

    <div class="row">

        {{-- ================= LEFT COLUMN ================= --}}
        <div class="col-lg-7">

            {{-- ===== Invoice Info ===== --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt"></i> Informasi Invoice
                    </h5>
                </div>

                <div class="card-body">
                    <div class="alert alert-info alert-permanent">
                        <strong>Invoice:</strong> {{ $pembayaran->nomor_invoice ?? '-' }}<br>
                        <strong>Jumlah:</strong>
                        Rp {{ number_format($pembayaran->jumlah_pembayaran ?? 0, 0, ',', '.') }}<br>
                        <strong>Jatuh Tempo</strong>
                        {{ \App\Libraries\Date::tglIndo($pengajuan->pembayaran->tanggal_jatuh_tempo) }}<br>
                        <strong>Tanggal Pembayaran dari Prodi:</strong>
                        {{ $pembayaran->tanggal_pembayaran
                            ? \App\Libraries\Date::hariTglWaktu($pembayaran->tanggal_pembayaran)
                            : '-' }}
                    </div>

                    @if($pembayaran->catatan_verifikasi)
                    <div class="mb-2">
                        <label class="text-muted small">Catatan Verifikasi (Terakhir)</label>
                        <p class="mb-0">{{ $pembayaran->catatan_verifikasi }}</p>
                    </div>
                    @endif

                    {{-- ALASAN PENOLAKAN (FUTURE) --}}
                    {{--
                    @if($pembayaran->alasan_penolakan)
                        <div class="mb-2">
                            <label class="text-muted small text-danger">Alasan Penolakan</label>
                            <p class="mb-0 text-danger">{{ $pembayaran->alasan_penolakan }}</p>
                </div>
                @endif
                --}}

                @if($pembayaran->verified_by)
                <div class="mt-3 small text-muted">
                    Diverifikasi oleh:
                    {{ optional($pembayaran->verifier)->name ?? '-' }}
                </div>
                @endif
            </div>
        </div>

        {{-- ===== Aksi Verifikasi ===== --}}
        @if($pembayaran && $pembayaran->status_pembayaran === 'menunggu_verifikasi')
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-shield-check"></i> Verifikasi dan Validasi Pembayaran
                </h5>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('keuangan.pembayaran.verify', $pengajuan->id) }}">
                    @csrf

                    {{-- PILIHAN STATUS --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Keputusan Validasi <span class="text-danger">*</span>
                        </label>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_pembayaran" id="status_verified" value="terverifikasi" checked>
                            <label class="form-check-label" for="status_verified">
                                <i class="bi bi-check-circle text-success"></i>
                                Verifikasi & Setujui Pembayaran
                            </label>
                        </div>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="status_pembayaran" id="status_upload_ulang" value="upload_ulang">
                            <label class="form-check-label" for="status_upload_ulang">
                                <i class="bi bi-arrow-repeat text-warning"></i>
                                Meminta Upload Ulang (Bukti Pembayaran & Formulir Pembayaran)
                            </label>
                        </div>

                        {{-- STATUS DITOLAK (DISIAPKAN, TAPI DISEMBUNYIKAN) --}}
                        {{--
                            <div class="form-check mt-2">
                                <input class="form-check-input"
                                       type="radio"
                                       name="status_pembayaran"
                                       id="status_ditolak"
                                       value="ditolak">
                                <label class="form-check-label text-danger" for="status_ditolak">
                                    <i class="bi bi-x-circle"></i>
                                    Pembayaran Ditolak
                                </label>
                            </div>
                            --}}
                    </div>

                    {{-- CATATAN VERIFIKASI --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Catatan Verifikasi <span class="text-danger">*</span>
                        </label>
                        <textarea name="catatan_verifikasi" class="form-control @error('catatan_verifikasi') is-invalid @enderror" rows="3" required>{{ old('catatan_verifikasi') }}</textarea>
                        @error('catatan_verifikasi')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- ALASAN PENOLAKAN (FUTURE) --}}
                    {{--
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">
                                Alasan Penolakan <span class="text-danger">*</span>
                            </label>
                            <textarea name="alasan_penolakan"
                                      class="form-control"
                                      rows="2"></textarea>
                        </div>
                        --}}

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-check"></i> Simpan Keputusan
                    </button>
                </form>
            </div>
        </div>
        @elseif($pembayaran && $pembayaran->status_pembayaran === 'menunggu_pembayaran')
        <div class="alert alert-warning alert-permanent">
            <i class="bi bi-check-circle"></i>
            Sedang menunggu prodi melakukan pembayaran dan mengupload bukti pembayaran
        </div>
        @elseif($pembayaran && $pembayaran->status_pembayaran === 'terverifikasi')
        <div class="alert alert-success alert-permanent">
            <i class="bi bi-check-circle"></i>
            Pembayaran ini telah <strong>Terverifikasi</strong> pada {{ \App\Libraries\Date::tglWaktu($pembayaran->tanggal_verifikasi) }}.
        </div>
        @elseif($pembayaran && $pembayaran->status_pembayaran === 'upload_ulang')
        <div class="alert alert-warning alert-permanent">
            <i class="bi bi-check-circle"></i>
            Pembayaran ini belum terverifikasi. Bagian keuangan meminta prodi untuk mengupload ulang bukti pembayaran & formulir pembayaran
        </div>
        @elseif($pembayaran && $pembayaran->status_pembayaran === 'ditolak')
        <div class="alert alert-warning alert-permanent">
            <i class="bi bi-check-circle"></i>
            Pembayaran ini belum terverifikasi (ditolak). Bagian keuangan meminta prodi untuk mengupload ulang bukti pembayaran & formulir pembayaran
        </div>
        @else
        <div class="alert alert-warning alert-permanent">
            <i class="bi bi-exclamation-triangle"></i>
            Pembayaran tidak berada pada status
            <strong>Menunggu Verifikasi</strong>.
        </div>
        @endif
    </div>

    {{-- ================= RIGHT COLUMN ================= --}}
    <div class="col-lg-5">

        {{-- ===== Bukti Pembayaran ===== --}}
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-file-earmark-image"></i> Bukti Pembayaran
                </h5>
            </div>

            <div class="card-body">
                @if(!$path)
                <p class="text-muted mb-0">Bukti pembayaran belum diupload.</p>
                @else
                @if(in_array($ext, ['jpg','jpeg','png']))
                <img src="{{ $url }}" class="img-fluid rounded border mb-2" alt="Bukti Pembayaran">
                @endif

                <a href="{{ route('keuangan.pembayaran.download-bukti', $pengajuan->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-download"></i> Download Bukti
                </a>
                @endif
            </div>
        </div>

        {{-- ===== Info Prodi ===== --}}
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle"></i> Info Prodi
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="text-muted small">Universitas</label>
                    <p class="fw-bold mb-0">
                        {{ optional(optional($pengajuan->studyProgram)->university)->name ?? '-' }}
                    </p>
                </div>
                <div class="mb-2">
                    <label class="text-muted small">Program Studi</label>
                    <p class="fw-bold mb-0">
                        {{ optional($pengajuan->studyProgram)->name ?? '-' }}
                    </p>
                </div>
                <div>
                    <label class="text-muted small">Jenjang</label>
                    <p class="fw-bold mb-0">
                        {{ optional(optional($pengajuan->studyProgram)->degreeLevel)->name ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
@endsection
