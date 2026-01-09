@extends('layouts.template.app')

@section('title', isset($asesmen) ? 'Edit Asesmen' : 'Buat Asesmen Baru')

@section('content')
<!-- Header -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="{{ route('asesmen.index') }}">Asesmen</a></li>
            @if(isset($pengajuan))
            <li class="breadcrumb-item">
                <a href="{{ route('de.pengajuan.show', $pengajuan->id) }}">
                    Pengajuan {{ $pengajuan->nomor_pengajuan }}
                </a>
            </li>
            @endif
            <li class="breadcrumb-item active">{{ isset($asesmen) ? 'Edit' : 'Buat Baru' }}</li>
        </ol>
    </nav>
    <h2>{{ isset($asesmen) ? 'Edit Asesmen' : 'Buat Asesmen Baru' }}</h2>
</div>

@if(isset($pengajuan))
<div class="alert alert-info alert-permanent fade show">
    <i class="bi bi-info-circle"></i>
    <strong>Info:</strong> Asesmen ini dibuat dari Pengajuan Akreditasi <strong>{{ $pengajuan->nomor_pengajuan }}</strong>.
    Data program studi dan universitas akan otomatis terisi.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

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

                    @if(isset($pengajuan))
                    <input type="hidden" name="id_pengajuan" value="{{ $pengajuan->id }}">
                    <input type="hidden" name="id_study_program" value="{{ $pengajuan->id_program_studi }}">
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

                    <!-- Informasi Perguruan Tinggi (READ ONLY - dari relasi) -->
                    <h5 class="mb-3">Informasi Perguruan Tinggi</h5>

                    @if(isset($studyProgram))
                    {{-- Tampilkan info dari studyProgram --}}
                    <div class="alert alert-secondary alert-permanent">
                        <strong><i class="bi bi-info-circle"></i> Data dari Pengajuan:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Program Studi:</strong> {{ $studyProgram->full_name }}</li>
                            <li><strong>Universitas:</strong> {{ $studyProgram->university->name }}</li>
                        </ul>
                    </div>
                    @elseif(isset($asesmen) && $asesmen->studyProgram)
                    {{-- Tampilkan info dari asesmen existing --}}
                    <div class="alert alert-secondary alert-permanent">
                        <strong><i class="bi bi-info-circle"></i> Informasi Program Studi:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Program Studi:</strong> {{ $asesmen->studyProgram->full_name }}</li>
                            <li><strong>Universitas:</strong> {{ $asesmen->studyProgram->university->name }}</li>
                        </ul>
                    </div>
                    @else
                    {{-- Jika tidak ada studyProgram (create manual tanpa pengajuan) --}}
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Asesmen ini tidak terhubung dengan program studi.
                        Untuk menghubungkan dengan program studi, buat asesmen melalui pengajuan akreditasi.
                    </div>
                    @endif

                    <div class="mb-3">
                        <label for="code" class="form-label fw-semibold">
                            Kode Panel
                        </label>
                        <input type="text" class="form-control @error('kode_panel') is-invalid @enderror" id="kode_panel" name="kode_panel" value="{{ old('kode_panel', $asesmen->kode_panel ?? '') }}" placeholder="Contoh: T01-P007" style="max-width: 200px;">
                        @error('kode_panel')
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
