@extends('layouts.template.app')

@section('title', isset($asesmen) ? 'Edit Asesmen' : 'Buat Asesmen Baru')

@section('content')
<!-- Header -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="{{ route('asesmen.index') }}">Asesmen</a></li>
            <li class="breadcrumb-item active">{{ isset($asesmen) ? 'Edit' : 'Buat Baru' }}</li>
        </ol>
    </nav>
    <h2>{{ isset($asesmen) ? 'Edit Asesmen' : 'Buat Asesmen Baru' }}</h2>
</div>

<!-- Form -->
<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-data"></i>
                    {{ isset($asesmen) ? 'Form Edit Asesmen' : 'Form Asesmen Baru' }}
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($asesmen) ? route('asesmen.update', $asesmen->id) : route('asesmen.store') }}">
                    @csrf
                    @if(isset($asesmen))
                    @method('PUT')
                    @endif

                    <!-- Nama Asesmen -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            Nama Asesmen <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $asesmen->name ?? '') }}" placeholder="Contoh: Penilaian Akreditasi Universitas ABC 2025" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nama lengkap asesmen yang akan ditampilkan</small>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">
                            Deskripsi
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Deskripsi singkat tentang asesmen ini...">{{ old('description', $asesmen->description ?? '') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Informasi Perguruan Tinggi -->
                    <h5 class="mb-3">Informasi Perguruan Tinggi</h5>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="perguruan_tinggi" class="form-label fw-semibold">
                                Nama Perguruan Tinggi
                            </label>
                            <input type="text" class="form-control @error('perguruan_tinggi') is-invalid @enderror" id="perguruan_tinggi" name="perguruan_tinggi" value="{{ old('perguruan_tinggi', $asesmen->perguruan_tinggi ?? '') }}" placeholder="Contoh: Universitas Serasan">
                            @error('perguruan_tinggi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="bentuk_pt" class="form-label fw-semibold">
                                Bentuk PT
                            </label>
                            <select class="form-select @error('bentuk_pt') is-invalid @enderror" id="bentuk_pt" name="bentuk_pt">
                                <option value="">-- Pilih --</option>
                                <option value="Universitas" {{ old('bentuk_pt', $asesmen->bentuk_pt ?? '') == 'Universitas' ? 'selected' : '' }}>Universitas</option>
                                <option value="Institut" {{ old('bentuk_pt', $asesmen->bentuk_pt ?? '') == 'Institut' ? 'selected' : '' }}>Institut</option>
                                <option value="Sekolah Tinggi" {{ old('bentuk_pt', $asesmen->bentuk_pt ?? '') == 'Sekolah Tinggi' ? 'selected' : '' }}>Sekolah Tinggi</option>
                                <option value="Politeknik" {{ old('bentuk_pt', $asesmen->bentuk_pt ?? '') == 'Politeknik' ? 'selected' : '' }}>Politeknik</option>
                                <option value="Akademi" {{ old('bentuk_pt', $asesmen->bentuk_pt ?? '') == 'Akademi' ? 'selected' : '' }}>Akademi</option>
                            </select>
                            @error('bentuk_pt')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label fw-semibold">
                            Kode Panel
                        </label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $asesmen->code ?? '') }}" placeholder="Contoh: T01-P007" style="max-width: 200px;">
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- Periode Asesmen -->
                    <h5 class="mb-3">Periode Asesmen</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label fw-semibold">
                                Tanggal Mulai
                            </label>
                            <input type="date" class="form-control @error('tanggal_mulai') is-invalid @enderror" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $asesmen->tanggal_mulai ?? '') }}">
                            @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tanggal_selesai" class="form-label fw-semibold">
                                Tanggal Selesai
                            </label>
                            <input type="date" class="form-control @error('tanggal_selesai') is-invalid @enderror" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $asesmen->tanggal_selesai ?? '') }}">
                            @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Tanggal selesai harus lebih besar dari tanggal mulai</small>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <hr class="my-4">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('asesmen.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i>
                            {{ isset($asesmen) ? 'Update Asesmen' : 'Buat Asesmen' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(isset($asesmen))
        <!-- Info Card -->
        <div class="card mt-3">
            <div class="card-body">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    Asesmen ini dibuat pada {{ $asesmen->created_at->format('d M Y H:i') }}
                    @if($asesmen->updated_at != $asesmen->created_at)
                    dan terakhir diupdate pada {{ $asesmen->updated_at->format('d M Y H:i') }}
                    @endif
                </small>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Validate tanggal selesai > tanggal mulai
    const tanggalMulai = document.getElementById('tanggal_mulai');

    if (tanggalMulai) {
        tanggalMulai.addEventListener('change', function() {
            const tanggalSelesai = document.getElementById('tanggal_selesai');
            if (tanggalSelesai && this.value) {
                tanggalSelesai.min = this.value;
            }
        });
    }

</script>
@endpush
@endsection
