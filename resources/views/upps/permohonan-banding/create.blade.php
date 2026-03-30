{{-- resources/views/upps/permohonan-banding/create.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Buat Permohonan Banding')

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
            <li class="breadcrumb-item"><a href="{{ route('upps.permohonan-banding') }}">Permohonan Banding</a></li>
            <li class="breadcrumb-item active">Buat Baru</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-4">
        <h5>
            <i class="bi bi-file-earmark-plus"></i>
            Kirim Permohonan Banding
        </h5>
        <p class="text-muted">
            Tuliskan data pokok permohonan banding akreditasi program studi sebagai berikut
        </p>
    </div>

    <form action="{{ route('upps.permohonan-banding.store') }}" method="POST" enctype="multipart/form-data" id="formBanding">
        @csrf

        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> Data Pokok Permohonan Banding
                        </h5>
                    </div>
                    <div class="card-body">

                        {{-- Section 1: Program Studi --}}
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Nama Program Studi <span class="text-danger">*</span>
                                </label>
                                @if($prodis->isEmpty())
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    Tidak ada program studi yang sedang dalam masa sanggah.
                                </div>
                                @else
                                <select name="id_program_studi" id="id_program_studi" class="form-select @error('id_program_studi') is-invalid @enderror" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('id_program_studi') == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->full_name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('id_program_studi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Hanya menampilkan program studi yang sedang dalam masa sanggah
                                </small>
                                @endif
                            </div>
                        </div>

                        {{-- Section 2: Alasan Banding --}}
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Alasan/Keberatan <span class="text-danger">*</span>
                                </label>
                                <textarea name="alasan_banding" class="form-control @error('alasan_banding') is-invalid @enderror" rows="8" required placeholder="Jelaskan alasan atau keberatan Anda terhadap hasil akreditasi yang diberikan...">{{ old('alasan_banding') }}</textarea>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Jelaskan secara detail alasan pengajuan banding
                                </small>
                                @error('alasan_banding')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 3: Upload Dokumen --}}
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Surat Permohonan Banding (PDF) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_surat_permohonan" id="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf" required>
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Format: PDF | Maksimal: 5 MB
                                </small>
                                <div id="filePreview" class="mt-2"></div>
                                @error('file_surat_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('upps.permohonan-banding') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="button" class="btn btn-primary" id="btnSubmit" {{ $prodis->isEmpty() ? 'disabled' : '' }}>
                                <i class="bi bi-send"></i> Kirim Permohonan Banding
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-info mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Informasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold">Masa Sanggah:</h6>
                        <p class="small text-muted mb-3">
                            Permohonan banding hanya dapat diajukan selama masa sanggah yang telah ditentukan.
                        </p>
                        <hr>
                        <h6 class="fw-bold">Proses Banding:</h6>
                        <ol class="small mb-0 ps-3">
                            <li>Pengajuan banding diterima</li>
                            <li>Verifikasi kelengkapan dokumen</li>
                            <li>Penugasan asesor banding</li>
                            <li>Pelaksanaan banding</li>
                            <li>Pelaporan banding</li>
                            <li>Penetapan hasil akhir</li>
                        </ol>
                    </div>
                </div>

                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Perhatian
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3 small">
                            <li class="mb-2">Pastikan data yang diisi sudah benar</li>
                            <li class="mb-2">File PDF maksimal 5MB</li>
                            <li class="mb-2">Permohonan yang sudah dikirim tidak dapat dibatalkan</li>
                            <li>Hasil banding bersifat final dan tidak dapat diganggu gugat</li>
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
    // File preview
    const fileInput = document.getElementById('file_surat_permohonan');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const preview = document.getElementById('filePreview');
            if (!e.target.files || !e.target.files.length) {
                preview.innerHTML = '';
                return;
            }

            const file = e.target.files[0];
            const fileSize = file.size / 1024 / 1024;

            if (file.type !== 'application/pdf') {
                preview.innerHTML =
                    '<div class="alert alert-danger alert-dismissible fade show">' +
                    '<i class="bi bi-x-circle"></i> File harus berformat PDF' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                e.target.value = '';
                return;
            }
            if (fileSize > 5) {
                preview.innerHTML =
                    '<div class="alert alert-danger alert-dismissible fade show">' +
                    '<i class="bi bi-x-circle"></i> Ukuran file terlalu besar (' + fileSize.toFixed(2) + ' MB). Maksimal 5 MB' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                e.target.value = '';
                return;
            }
            preview.innerHTML =
                '<div class="alert alert-success alert-dismissible fade show">' +
                '<i class="bi bi-check-circle"></i> ' +
                '<strong>' + file.name + '</strong> (' + fileSize.toFixed(2) + ' MB)' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
        });
    }

    const btnSubmit = document.getElementById('btnSubmit');
    const formBanding = document.getElementById('formBanding');

    if (btnSubmit) {
        btnSubmit.addEventListener('click', async function() {
            if (!(await swalConfirmSubmit('warning', 'Apakah Anda yakin akan mengajukan permohonan banding ini? Tindakan ini tidak dapat dibatalkan.'))) {
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
            formBanding.submit();
        });
    }

</script>
@endpush
