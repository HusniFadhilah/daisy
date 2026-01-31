{{-- resources/views/upps/pengingat-akreditasi/respond.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Respon Pengingat Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pengingat-akreditasi') }}">Pengingat Akreditasi</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.pengingat-akreditasi.show', $pengingat->id) }}">Detail</a></li>
            <li class="breadcrumb-item active">Respon</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-reply-fill"></i> Respon Pengingat Masa Akreditasi
            </h5>
            <small class="text-muted">Buat permohonan akreditasi sebagai respon</small>
        </div>
        <a href="{{ route('upps.pengingat-akreditasi.show', $pengingat->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Info Pengingat -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Pengingat
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="30%">Program Studi</th>
                            <td>: {{ $pengingat->studyProgram->full_name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengingat->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <th>Tahun Akreditasi</th>
                            <td>: {{ $pengingat->tahun_akreditasi }}</td>
                        </tr>
                        <tr>
                            <th>Pengirim</th>
                            <td>: {{ $pengingat->pengirim->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Form Respon -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-text"></i> Form Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('upps.pengingat-akreditasi.respond', $pengingat->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Jenis Akreditasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Jenis Permohonan Akreditasi <span class="text-danger">*</span>
                            </label>
                            @foreach(\App\Models\PengajuanAkreditasi::jenisAkreditasiOptions() as $value => $label)
                            <div class="form-check">
                                <input class="form-check-input @error('jenis_akreditasi') is-invalid @enderror" type="radio" name="jenis_akreditasi" id="jenis_{{ $value }}" value="{{ $value }}" {{ old('jenis_akreditasi') == $value ? 'checked' : '' }}>
                                <label class="form-check-label" for="jenis_{{ $value }}">
                                    {{ $label }}
                                </label>
                            </div>
                            @endforeach
                            @error('jenis_akreditasi')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Upload Surat Permohonan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Upload File Permohonan Akreditasi <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf">
                            <small class="text-muted">Format: PDF | Maksimal: 5MB</small>
                            @error('file_surat_permohonan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Catatan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Catatan (Opsional)</label>
                            <textarea name="catatan_pengaju" class="form-control @error('catatan_pengaju') is-invalid @enderror" rows="4" placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan_pengaju') }}</textarea>
                            @error('catatan_pengaju')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Informasi Penting -->
                        <div class="alert alert-warning alert-permanent">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Perhatian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Pastikan surat permohonan telah ditandatangani</li>
                                <li>File harus dalam format PDF</li>
                                <li>Setelah submit, permohonan akan langsung dikirim ke LAMDEPILAR</li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-md">
                                <i class="bi bi-send"></i> Kirim Permohonan Akreditasi
                            </button>
                            <a href="{{ route('upps.pengingat-akreditasi.show', $pengingat->id) }}" class="btn btn-secondary btn-md">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
