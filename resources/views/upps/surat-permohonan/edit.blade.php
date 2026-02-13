{{-- resources/views/upps/surat-permohonan/edit.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Edit Draft Permohonan Akreditasi')

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
            <li class="breadcrumb-item"><a href="{{ route('upps.surat-permohonan') }}">Permohonan Akreditasi</a></li>
            <li class="breadcrumb-item active">Edit Draft</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-4">
        <h5>
            <i class="bi bi-pencil-square"></i>
            Edit Draft Permohonan Akreditasi
        </h5>
        <p class="text-muted">
            Tuliskan data pokok permohonan akreditasi program studi sebagai berikut
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

    @php
    // Konsisten dengan controller update: surat_permohonan + is_latest
    $existingDokumen = $pengajuan->dokumen()
    ->where('jenis_dokumen', 'surat_permohonan')
    ->where('is_latest', true)
    ->latest()
    ->first();
    @endphp

    <form action="{{ route('upps.surat-permohonan.update', $pengajuan->id) }}" method="POST" enctype="multipart/form-data" id="formPermohonan">
        @csrf
        @method('PUT')
        <input type="hidden" name="is_draft" id="is_draft" value="1">

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

                        {{-- Section 1: Nomor Permohonan --}}
                        <div class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Nomor Permohonan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nomor_permohonan" id="nomor_permohonan" class="form-control @error('nomor_permohonan') is-invalid @enderror" value="{{ old('nomor_permohonan', $pengajuan->nomor_permohonan) }}" placeholder="Contoh: 001/AKR/UNIV/2025" required>
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
                            <!-- Nama Universitas -->
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
                                    {{-- sama seperti create: jika prodiUser tersedia, lock pilihan --}}
                                    <option value="{{ $prodiUser->id }}" selected>
                                        {{ $prodiUser->full_name }}
                                    </option>
                                    @else
                                    @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('id_program_studi', $pengajuan->id_program_studi) == $prodi->id ? 'selected' : '' }}>
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
                                <input type="number" name="tahun_akreditasi" id="tahun_akreditasi" class="form-control @error('tahun_akreditasi') is-invalid @enderror" value="{{ old('tahun_akreditasi', $pengajuan->tahun_akreditasi) }}" min="2024" max="{{ date('Y') + 2 }}" required>
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

                        {{-- Section 4: Kontak Pemohon --}}
                        <div class="mb-4">
                            <!-- Email Pemohon -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Email Pemohon <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="pemohon_email" id="pemohon_email" class="form-control @error('pemohon_email') is-invalid @enderror" value="{{ old('pemohon_email', $pengajuan->pemohon_email ?? $authUser->email) }}" placeholder="contoh@email.com" required>
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
                                <input type="text" name="pemohon_phone" id="pemohon_phone" class="form-control @error('pemohon_phone') is-invalid @enderror" value="{{ old('pemohon_phone', $pengajuan->pemohon_phone ?? $authUser->phone) }}" placeholder="08xxxxxxxxxx" required>
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
                                    File Permohonan Akreditasi
                                    <span class="text-danger" id="label-required">*</span>
                                </label>
                                <input type="file" name="file_surat_permohonan" id="file_surat_permohonan" class="form-control @error('file_surat_permohonan') is-invalid @enderror" accept=".pdf" data-existing="{{ $existingDokumen ? 1 : 0 }}" data-existing-name="{{ $existingDokumen?->original_filename }}" data-existing-size="{{ $existingDokumen?->file_size }}" data-existing-uploaded="{{ $existingDokumen?->created_at?->locale('id')->translatedFormat('d M Y H:i') }}" data-existing-url="{{ $existingDokumen ? route('upps.surat-permohonan.download', $pengajuan->id) : '' }}">
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

            <!-- Sidebar (boleh pakai versi edit kamu sebelumnya, ini minimal) -->
            <div class="col-lg-4">
                <div class="card border-warning mb-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Status Draft
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Nomor:</strong> {{ $pengajuan->nomor_permohonan }}
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
                                <strong>Simpan sebagai Draft:</strong> Data disimpan dan bisa dilanjutkan nanti.
                            </li>
                            <li>
                                <strong>Kirim Permohonan:</strong> File surat wajib diupload. Permohonan langsung dikirim ke LAMDEPILAR.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-shield-exclamation"></i> Perhatian
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3 small">
                            <li class="mb-2">File PDF maksimal 5MB</li>
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

        // ✅ beda utama edit: apakah sudah ada file existing?
        var existingFile = @json((bool) $existingDokumen);

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

        // ✅ Default halaman edit draft: anggap mode draft (file opsional)
        setFileOptional(true);

        // ===============================
        // Draft button
        // ===============================
        if (btnDraft) {
            btnDraft.addEventListener('click', function() {
                if (isDraftInput) isDraftInput.value = '1';

                // draft: file tidak wajib
                if (fileInput) fileInput.removeAttribute('required');
                setFileOptional(true);

                // draft boleh tanpa nomor permohonan (sesuai controller)
                if (nomorPermohonanInput) nomorPermohonanInput.removeAttribute('required');

                safeSubmit();
            });
        }

        // ===============================
        // Submit button
        // ===============================
        if (btnSubmit) {
            btnSubmit.addEventListener('click', function() {
                if (isDraftInput) isDraftInput.value = '0';

                // kirim: nomor permohonan wajib (sesuai controller)
                if (nomorPermohonanInput) nomorPermohonanInput.setAttribute('required', 'required');

                // kirim: file wajib hanya jika belum ada file existing
                if (!existingFile && fileInput) {
                    if (fileInput) fileInput.setAttribute('required', 'required');
                    setFileOptional(false);

                    if (!fileInput.files || !fileInput.files.length) {
                        alert('File surat permohonan wajib diupload untuk mengirim permohonan!');
                        fileInput.focus();
                        return;
                    }
                } else {
                    // sudah ada file lama → upload baru opsional
                    if (fileInput) fileInput.removeAttribute('required');
                    setFileOptional(false); // saat kirim tetap tampilkan mode "wajib" pada UI (tanpa badge opsional)
                }

                if (!confirm('Apakah Anda yakin data yang diisi sudah benar dan siap untuk dikirim?')) {
                    return;
                }

                // cegah double submit
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

                safeSubmit();
            });
        }

        // ===============================
        // Tampilkan file existing di preview (kalau ada)
        // ===============================
        if (fileInput && preview && fileInput.dataset.existing === '1') {
            var name = fileInput.dataset.existingName || 'File PDF';
            var sizeBytes = parseFloat(fileInput.dataset.existingSize || '0');
            var sizeKB = sizeBytes ? (sizeBytes / 1024).toFixed(2) : '';
            var uploaded = fileInput.dataset.existingUploaded || '';
            var url = fileInput.dataset.existingUrl || '';

            preview.innerHTML =
                '<div class="alert alert-success alert-dismissible fade show">' +
                '<i class="bi bi-file-earmark-pdf"></i> ' +
                '<strong>File saat ini:</strong> ' + name +
                (sizeKB ? ' <span class="text-muted">(' + sizeKB + ' KB)</span>' : '') +
                (uploaded ? '<div class="small text-muted">Diupload: ' + uploaded + '</div>' : '') +
                (url ? '<div class="mt-2"><a class="btn btn-sm btn-success" href="' + url + '" target="_blank">' +
                    '<i class="bi bi-eye"></i> Lihat</a></div>' : '') +
                '</div>';
        }
    });

</script>
@endpush
