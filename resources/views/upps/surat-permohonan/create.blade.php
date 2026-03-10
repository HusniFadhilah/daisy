{{-- resources/views/upps/surat-permohonan/create.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Kirim Permohonan Akreditasi')

@push('styles')
<style>
    .pengingat-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .pengingat-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .pengingat-card.selected {
        border-color: #198754;
        background-color: #d1e7dd;
    }

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
            <li class="breadcrumb-item"><a href="{{ route('upps.surat-permohonan') }}">Permohonan Akreditasi</a></li>
            <li class="breadcrumb-item active">Kirim</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-4">
        <h5>
            <i class="bi bi-file-earmark-plus"></i>
            Kirim Permohonan Akreditasi
        </h5>
        <p class="text-muted">
            Tuliskan data pokok permohonan akreditasi program studi sebagai berikut
        </p>
    </div>

    {{-- ========== END SECTION: PENGINGAT ========== --}}

    <form action="{{ route('upps.surat-permohonan.store') }}" method="POST" enctype="multipart/form-data" id="formPermohonan">
        @csrf
        <input type="hidden" name="is_draft" id="is_draft" value="0">
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-clipboard-check"></i> Data Pokok Permohonan Akreditasi
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- Section 1: Nomor Permohonan (Preview) --}}
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Nomor Permohonan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nomor_permohonan" id="nomor_permohonan" class="form-control @error('nomor_permohonan') is-invalid @enderror" value="{{ old('nomor_permohonan') }}" placeholder="Contoh: 001/AKR/UNIV/2025" required>
                                @error('nomor_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Masukkan nomor surat permohonan dari institusi Anda
                                </small>
                            </div>
                        </div>

                        {{-- Section 2: Program Studi & Universitas --}}
                        <div class="mb-4">
                            <!-- Nama Universitas (From User) -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Nama Universitas
                                </label>
                                <input type="text" id="nama_universitas" class="form-control" value="{{ $authUser->university->name ?? '-' }}" readonly disabled>
                            </div>

                            <!-- Program Studi -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Nama Program Studi <span class="text-danger">*</span>
                                </label>
                                <select name="id_program_studi" id="id_program_studi" class="form-select @error('id_program_studi') is-invalid @enderror" required>
                                    <option value="">-- Pilih Program Studi --</option>
                                    @if($prodiUser)
                                    <option value="{{ $prodiUser->id }}" selected>
                                        {{ $prodiUser->full_name }}
                                    </option>
                                    @else
                                    @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('id_program_studi') == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->full_name }}
                                    </option>
                                    @endforeach
                                    @endif
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
                                <input type="number" name="tahun_akreditasi" id="tahun_akreditasi" class="form-control @error('tahun_akreditasi') is-invalid @enderror" value="{{ old('tahun_akreditasi', date('Y')) }}" min="2024" max="{{ date('Y') + 2 }}" required>
                                @error('tahun_akreditasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 3: Jenis Permohonan Akreditasi --}}
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Jenis Permohonan Akreditasi <span class="text-danger">*</span>
                                </label>
                                <select name="jenis_akreditasi" class="form-select @error('jenis_akreditasi') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    @foreach (\App\Models\PengajuanAkreditasi::jenisAkreditasiOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ old('jenis_akreditasi') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('jenis_akreditasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 4: Kontak Pemohon --}}
                        <div class="mb-4">
                            <!-- Email Pemohon -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Email Pemohon <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="pemohon_email" id="pemohon_email" class="form-control @error('pemohon_email') is-invalid @enderror" value="{{ old('pemohon_email', $authUser->email) }}" placeholder="contoh@email.com" required>
                                @error('pemohon_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Email untuk korespondensi terkait permohonan akreditasi
                                </small>
                            </div>

                            <!-- Nomor HP Pemohon -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Nomor HP Pemohon <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="pemohon_phone" id="pemohon_phone" class="form-control @error('pemohon_phone') is-invalid @enderror" value="{{ old('pemohon_phone',$authUser->phone) }}" placeholder="08xxxxxxxxxx" required>
                                @error('pemohon_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Nomor HP yang dapat dihubungi (format: 08xxxxxxxxxx)
                                </small>
                            </div>
                        </div>

                        {{-- Section 5: Upload Dokumen --}}
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    File Permohonan Akreditasi (PDF)
                                    <span class="text-danger" id="label-required">*</span>
                                </label>
                                <input type="file" name="file_surat_permohonan" id="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> Format: PDF | Maksimal: 5 MB
                                    <span id="draft-info" style="display: none;"> | Bisa diupload nanti jika simpan draft</span>
                                </small>
                                <div id="filePreview" class="mt-2"></div>
                                @error('file_surat_permohonan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4">
                            <a href="{{ route('upps.surat-permohonan') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-primary" id="btnDraft">
                                    <i class="bi bi-save"></i> Simpan sebagai Draft
                                </button>

                                <button type="button" class="btn btn-primary" id="btnSubmit">
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
                <div class="card border-info mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Informasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold">2 Opsi Permohonan:</h6>
                        <ol class="mb-3 ps-3">
                            <li class="mb-2">
                                <strong>Simpan sebagai Draft</strong>
                                <small class="d-block text-muted">
                                    Data disimpan dan bisa dilanjutkan nanti.
                                </small>
                            </li>
                            <li>
                                <strong>Kirim Permohonan</strong>
                                <small class="d-block text-muted">
                                    File surat wajib diupload. Permohonan langsung dikirim ke LAMDEPILAR.
                                </small>
                            </li>
                        </ol>

                        <hr>

                        <h6 class="fw-bold mb-2">Status Alur:</h6>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2">Draft</span>
                            <i class="bi bi-arrow-right text-muted"></i>
                            <span class="badge bg-primary ms-2">Dikirim</span>
                        </div>
                        <small class="text-muted">
                            Draft dapat dilanjutkan kapan saja sebelum dikirim
                        </small>
                    </div>
                </div>

                <!-- Warning Card -->
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
                            <li class="mb-2">Permohonan yang sudah dikirim tidak dapat diubah</li>
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
        var form = document.getElementById('formPermohonan');

        var prodiSelect = document.getElementById('id_program_studi');
        var universityInput = document.getElementById('nama_universitas');

        var fileInput = document.getElementById('file_surat_permohonan');
        var preview = document.getElementById('filePreview');

        var btnDraft = document.getElementById('btnDraft');
        var btnSubmit = document.getElementById('btnSubmit');

        var labelRequired = document.getElementById('label-required');
        var labelOptional = document.getElementById('label-optional');
        var draftInfo = document.getElementById('draft-info');

        var nomorHpInput = document.getElementById('pemohon_phone');
        var nomorPermohonanInput = document.getElementById('nomor_permohonan');

        var isDraftInput = document.getElementById('is_draft');

        // ===============================
        // Auto-fill Nama Universitas (jika option punya data-university)
        // ===============================
        if (prodiSelect && universityInput) {
            prodiSelect.addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var universityName = selectedOption ? selectedOption.getAttribute('data-university') : '';
                if (universityName) {
                    universityInput.value = universityName;
                }
            });
        }

        // ===============================
        // Validasi Nomor HP (hanya angka)
        // ===============================
        if (nomorHpInput) {
            nomorHpInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');

                if (this.value.length > 0 && this.value.indexOf('08') !== 0) {
                    this.setCustomValidity('Nomor HP harus diawali dengan 08');
                } else if (this.value.length > 0 && this.value.length < 5) {
                    this.setCustomValidity('Nomor HP minimal 5 digit');
                } else if (this.value.length > 15) {
                    this.setCustomValidity('Nomor HP maksimal 15 digit');
                    this.value = this.value.substring(0, 15);
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        // ===============================
        // File upload preview
        // ===============================
        if (fileInput && preview) {
            fileInput.addEventListener('change', function(e) {
                if (!e.target.files || !e.target.files.length) {
                    preview.innerHTML = '';
                    return;
                }

                var file = e.target.files[0];
                var fileSize = file.size / 1024 / 1024;
                var fileName = file.name;

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

        function setFileOptional(isOptional) {
            if (labelRequired) labelRequired.style.display = isOptional ? 'none' : 'inline';
            if (labelOptional) labelOptional.style.display = isOptional ? 'inline' : 'none';
            if (draftInfo) draftInfo.style.display = isOptional ? 'inline' : 'none';
        }

        function safeSubmit() {
            if (!form) return;
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }

        // ===============================
        // Draft button
        // ===============================
        if (btnDraft) {
            btnDraft.addEventListener('click', function() {
                if (isDraftInput) isDraftInput.value = '1';

                // draft: file tidak wajib
                if (fileInput) fileInput.removeAttribute('required');
                setFileOptional(true);

                // jika draft boleh tanpa nomor permohonan (sesuaikan dengan controller)
                if (nomorPermohonanInput) nomorPermohonanInput.removeAttribute('required');

                safeSubmit();
            });
        }

        // ===============================
        // Submit button
        // ===============================
        if (btnSubmit) {
            btnSubmit.addEventListener('click', async function() {
                if (isDraftInput) isDraftInput.value = '0';

                // kirim: file wajib
                if (fileInput) fileInput.setAttribute('required', 'required');
                setFileOptional(false);

                // kirim: nomor permohonan wajib (sesuaikan controller)
                if (nomorPermohonanInput) nomorPermohonanInput.setAttribute('required', 'required');

                // cek file sebelum confirm
                if (fileInput && (!fileInput.files || !fileInput.files.length)) {
                    Swal.fire('Perhatian', 'File surat permohonan wajib diupload untuk mengirim permohonan!', 'warning');
                    fileInput.focus();
                    return;
                }

                if (!(await swalConfirmSubmit('warning', 'Apakah Anda yakin data yang diisi sudah benar dan siap untuk dikirim?'))) {
                    return;
                }

                // cegah double submit
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

                safeSubmit();
            });
        }
    });

</script>
@endpush
