@extends('layouts.template.app')

@section('title', 'Pengaturan - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h2 class="mb-1">Pengaturan</h2>
            <p class="text-muted mb-0">Konfigurasi biaya pembayaran dan link panduan penggunaan DAISY</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Link Panduan Penggunaan</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <h5 class="mb-3">Biaya Pembayaran</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="biaya_akreditasi" class="form-label">Biaya Akreditasi</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('biaya.akreditasi') is-invalid @enderror" id="biaya_akreditasi" name="biaya[akreditasi]" value="{{ old('biaya.akreditasi', $paymentSettings['biaya_akreditasi']) }}" min="0" step="1" required>
                                @error('biaya.akreditasi')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <small class="text-muted">Default saat ini: Rp {{ number_format($paymentSettings['biaya_akreditasi'], 0, ',', '.') }}</small>
                        </div>

                        <div class="col-md-6">
                            <label for="biaya_banding" class="form-label">Biaya Banding</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control @error('biaya.banding') is-invalid @enderror" id="biaya_banding" name="biaya[banding]" value="{{ old('biaya.banding', $paymentSettings['biaya_banding']) }}" min="0" step="1" required>
                                @error('biaya.banding')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <small class="text-muted">Default saat ini: Rp {{ number_format($paymentSettings['biaya_banding'], 0, ',', '.') }}</small>
                        </div>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">Link Panduan Penggunaan</h5>
                <div class="row g-3">
                    @foreach($panduanLinks as $key => $item)
                    <div class="col-12">
                        <label for="panduan_{{ $key }}" class="form-label">
                            {{ $item['label'] }}
                            <span class="text-muted small">({{ implode(', ', $item['roles']) }})</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                            <input type="url" class="form-control @error('panduan_links.' . $key) is-invalid @enderror" id="panduan_{{ $key }}" name="panduan_links[{{ $key }}]" value="{{ old('panduan_links.' . $key, $item['url']) }}" required>
                            <a href="{{ old('panduan_links.' . $key, $item['url']) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                            @error('panduan_links.' . $key)
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
