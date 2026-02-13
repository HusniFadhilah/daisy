{{-- resources/views/upps/surat-permohonan/edit.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Edit Draft Permohonan Banding')

@push('styles')
<style>
    .form-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('upps.surat-permohonan') }}">Permohonan Banding</a></li>
            <li class="breadcrumb-item active">Edit Draft</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-4">
        <h4>
            <i class="bi bi-pencil-square"></i>
            Edit Draft Permohonan Banding
        </h4>
        <p class="text-muted">
            Lengkapi dan kirim Permohonan Banding | Nomor: <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
        </p>
    </div>

    {{-- Alert Info Draft --}}
    <div class="alert alert-info alert-permanent mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle-fill me-2" style="font-size: 1.5rem;"></i>
            <div>
                <h6 class="alert-heading mb-2">
                    <strong>Permohonan Ini Masih Draft</strong>
                </h6>
                <p class="mb-0">
                    Anda dapat melengkapi data dan mengirim permohonan ini, atau menyimpan kembali sebagai draft.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('upps.surat-permohonan.update', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formPermohonan">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i>
                            Formulir Permohonan Banding
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Section 1: Data Program Studi --}}
                        <div class="form-section">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-building"></i> Data Program Studi
                            </h6>

                            <!-- Program Studi -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Program Studi <span class="text-danger">*</span>
                                </label>
                                <select name="id_program_studi" id="id_program_studi" class="form-select @error('id_program_studi') is-invalid @enderror" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('id_program_studi', $pengajuan->id_program_studi) == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->full_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('id_program_studi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tahun Akreditasi -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Tahun Akreditasi <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="tahun_akreditasi" id="tahun_akreditasi" class="form-control @error('tahun_akreditasi') is-invalid @enderror" value="{{ old('tahun_akreditasi', $pengajuan->tahun_akreditasi) }}" min="2024" max="{{ date('Y') + 2 }}" required>
                                @error('tahun_akreditasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 2: Jenis Akreditasi --}}
                        <div class="form-section">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-tag"></i> Jenis Akreditasi
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Pilih Jenis Akreditasi <span class="text-danger">*</span>
                                </label>
                                <select name="jenis_akreditasi" class="form-select @error('jenis_akreditasi') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    @foreach (\App\Models\PengajuanAkreditasi::jenisAkreditasiOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ old('jenis_akreditasi', $pengajuan->jenis_akreditasi) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('jenis_akreditasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 3: Upload Dokumen --}}
                        <div class="form-section">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-file-earmark-arrow-up"></i> Dokumen Permohonan
                            </h6>

                            @php
                            $existingDokumen = $pengajuan->dokumen->first();
                            @endphp

                            @if($existingDokumen)
                            <div class="alert alert-success alert-permanent mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-pdf text-danger me-2" style="font-size: 32px;"></i>
                                        <div>
                                            <strong>File Saat Ini:</strong> {{ $existingDokumen->original_filename }}
                                            <br>
                                            <small class="text-muted">
                                                {{ number_format($existingDokumen->file_size / 1024, 2) }} KB •
                                                Diupload: {{ $existingDokumen->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                    <a href="{{ route('upps.surat-permohonan.download', $pengajuan->id) }}" class="btn btn-sm btn-success" target="_blank">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                </div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    {{ $existingDokumen ? 'Ganti' : 'Upload' }} Surat Permohonan Banding (PDF)
                                    <span class="text-danger" id="label-required">
                                        {{ $existingDokumen ? '' : '*' }}
                                    </span>
                                    <span class="badge bg-secondary" id="label-optional" style="{{ $existingDokumen ? '' : 'display: none;' }}">
                                        Opsional
                                    </span>
                                </label>
                                <input type="file" name="file_surat_permohonan" id="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Format: PDF | Maksimal: 5 MB
                                    @if($existingDokumen)
                                    <br>
                                    <span class="text-info">Biarkan kosong jika tidak ingin mengganti file</span>
                                    @endif
                                    <span id="draft-info" style="{{ $existingDokumen ? '' : 'display: none;' }}">
                                        | Bisa diupload nanti jika simpan draft
                                    </span>
                                </small>
                                <div id="filePreview" class="mt-2"></div>
                                @error('file_surat_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Persyaratan Info -->
                            <div class="alert alert-light border">
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-clipboard-check"></i> Persyaratan Dokumen
                                </h6>
                                <ul class="mb-0 ps-3">
                                    <li>Surat Permohonan Banding resmi dalam format PDF</li>
                                    <li>Menggunakan kop surat program studi/universitas</li>
                                    <li>Ditandatangani oleh pejabat berwenang</li>
                                    <li>Mencantumkan tujuan akreditasi yang jelas</li>
                                </ul>
                            </div>
                        </div>

                        {{-- Section 4: Catatan --}}
                        <div class="form-section">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-chat-left-text"></i> Catatan Tambahan
                            </h6>

                            <div class="mb-0">
                                <label class="form-label fw-bold">
                                    Catatan/Keterangan (Opsional)
                                </label>
                                <textarea name="catatan_pengaju" class="form-control @error('catatan_pengaju') is-invalid @enderror" rows="4" placeholder="Masukkan catatan atau keterangan tambahan jika ada...">{{ old('catatan_pengaju', $pengajuan->catatan_pengaju) }}</textarea>
                                @error('catatan_pengaju')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <a href="{{ route('upps.surat-permohonan') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <div class="d-flex gap-2">
                                <button type="submit" name="is_draft" value="1" class="btn btn-outline-primary" id="btnDraft">
                                    <i class="bi bi-save"></i> Simpan Draft
                                </button>
                                <button type="submit" name="is_draft" value="0" class="btn btn-primary" id="btnSubmit">
                                    <i class="bi bi-send"></i> Kirim Permohonan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card border-warning mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Status Draft
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Nomor:</strong> {{ $pengajuan->nomor_pengajuan }}
                        </p>
                        <p class="mb-2">
                            <strong>Dibuat:</strong> {{ $pengajuan->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                        </p>
                        <p class="mb-3">
                            <strong>Terakhir Diubah:</strong> {{ $pengajuan->updated_at->locale('id')->translatedFormat('d M Y H:i') }}
                        </p>

                        <hr>

                        <h6 class="fw-bold mb-2">Opsi Aksi:</h6>
                        <ol class="mb-0 ps-3 small">
                            <li class="mb-2">
                                <strong>Simpan Draft:</strong> Perbarui data dan simpan untuk dilanjutkan nanti
                            </li>
                            <li>
                                <strong>Kirim Permohonan:</strong> Kirim Permohonan Banding ke LAMDEPILAR
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-shield-exclamation"></i> Perhatian
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3 small">
                            <li class="mb-2">File PDF maksimal 5MB</li>
                            <li class="mb-2">
                                Untuk mengirim, file surat permohonan
                                <strong class="text-dark">wajib diupload</strong>
                            </li>
                            <li>Permohonan yang sudah dikirim tidak dapat diubah</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file_surat_permohonan');
        const preview = document.getElementById('filePreview');
        const btnDraft = document.getElementById('btnDraft');
        const btnSubmit = document.getElementById('btnSubmit');
        const labelRequired = document.getElementById('label-required');
        const labelOptional = document.getElementById('label-optional');
        const draftInfo = document.getElementById('draft-info');
        const existingFile = {
            {
                $existingDokumen ? 'true' : 'false'
            }
        };

        // File upload preview
        if (fileInput && preview) {
            fileInput.addEventListener('change', function(e) {
                if (!e.target.files || !e.target.files.length) {
                    preview.innerHTML = '';
                    return;
                }

                const file = e.target.files[0];
                const fileSize = file.size / 1024 / 1024;
                const fileName = file.name;

                if (file.type !== 'application/pdf') {
                    preview.innerHTML =
                        '<div class="alert alert-danger alert-dismissible fade show">' +
                        '<i class="bi bi-x-circle"></i> File harus berformat PDF' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>';
                    fileInput.value = '';
                    return;
                }

                if (fileSize > 5) {
                    preview.innerHTML =
                        '<div class="alert alert-danger alert-dismissible fade show">' +
                        '<i class="bi bi-x-circle"></i> Ukuran file terlalu besar (' + fileSize.toFixed(2) + ' MB). Maksimal 5 MB' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>';
                    fileInput.value = '';
                    return;
                }

                preview.innerHTML =
                    '<div class="alert alert-success alert-dismissible fade show">' +
                    '<i class="bi bi-check-circle"></i> ' +
                    '<strong>' + fileName + '</strong> (' + fileSize.toFixed(2) + ' MB)' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
            });
        }

        // Draft button behavior
        if (btnDraft) {
            btnDraft.addEventListener('click', function(e) {
                if (fileInput) {
                    fileInput.removeAttribute('required');
                }
                if (labelRequired) labelRequired.style.display = 'none';
                if (labelOptional) labelOptional.style.display = 'inline';
                if (draftInfo) draftInfo.style.display = 'inline';
            });
        }

        // Submit button behavior
        if (btnSubmit) {
            btnSubmit.addEventListener('click', function(e) {
                // File required jika belum ada file sebelumnya
                if (!existingFile && fileInput) {
                    fileInput.setAttribute('required', 'required');

                    if (!fileInput.files || !fileInput.files.length) {
                        e.preventDefault();
                        alert('File surat permohonan wajib diupload untuk mengirim permohonan!');
                        fileInput.focus();
                        return false;
                    }
                }

                if (labelRequired) labelRequired.style.display = 'inline';
                if (labelOptional) labelOptional.style.display = 'none';
                if (draftInfo) draftInfo.style.display = 'none';

                if (!confirm('Apakah Anda yakin akan mengirim permohonan ini?')) {
                    e.preventDefault();
                    return false;
                }

                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
            });
        }
    });

</script>
@endpush
