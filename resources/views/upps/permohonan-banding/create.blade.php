{{-- resources/views/upps/surat-permohonan/create.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Buat Permohonan Banding')

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
            <li class="breadcrumb-item"><a href="{{ route('upps.surat-permohonan') }}">Permohonan Banding</a></li>
            <li class="breadcrumb-item active">Buat Baru</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-4">
        <h4>
            <i class="bi bi-file-earmark-plus"></i>
            Buat Permohonan Banding Baru
        </h4>
        <p class="text-muted">
            Lengkapi formulir untuk mengajukan Permohonan Banding program studi
        </p>
    </div>

    {{-- ========== SECTION: PENGINGAT YANG BELUM DIRESPON ========== --}}
    @if($pengingatBelumDirespon->count() > 0)
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">
                <i class="bi bi-bell"></i> Pengingat Akreditasi yang Belum Direspon
                <span class="badge bg-danger ms-2">{{ $pengingatBelumDirespon->count() }}</span>
            </h5>
        </div>
        <div class="card-body">
            <p class="mb-3">
                <i class="bi bi-info-circle"></i>
                <strong>Info:</strong> Anda dapat merespon pengingat di bawah ini, atau membuat permohonan baru tanpa menggunakan pengingat.
            </p>

            <div class="row">
                @foreach($pengingatBelumDirespon as $pengingat)
                <div class="col-md-6 mb-3">
                    <div class="card pengingat-card h-100 {{ $selectedPengingat?->id === $pengingat->id ? 'selected' : '' }}" onclick="selectPengingat({{ $pengingat->id }})" data-pengingat-id="{{ $pengingat->id }}" data-prodi-id="{{ $pengingat->id_program_studi }}" data-tahun="{{ $pengingat->tahun_akreditasi }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">
                                    <i class="bi bi-building"></i>
                                    {{ $pengingat->studyProgram->name }}
                                </h6>
                                @if($selectedPengingat?->id === $pengingat->id)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Dipilih
                                </span>
                                @endif
                            </div>
                            <small class="text-muted d-block mb-2">
                                {{ $pengingat->studyProgram->university->name }}
                            </small>
                            <small class="text-muted d-block mb-2">
                                <i class="bi bi-calendar"></i> Tahun: {{ $pengingat->tahun_akreditasi }}
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-clock"></i> Dikirim: {{ $pengingat->tanggal_dikirim->diffForHumans() }}
                            </small>
                            <small class="text-muted">
                                <i class="bi bi-person"></i> Sekretariat LAMDEPILAR
                            </small>
                            <hr>
                            <button class="btn btn-success btn-sm mt-3 d-block" type="button">Proses Permohonan Banding <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    {{-- ========== END SECTION: PENGINGAT ========== --}}

    <form action="{{ route('upps.surat-permohonan.store') }}" method="POST" enctype="multipart/form-data" id="formPermohonan">
        @csrf

        {{-- Hidden field untuk id_pengingat (akan diisi otomatis via JS) --}}
        <input type="hidden" name="id_pengingat" id="id_pengingat" value="{{ $selectedPengingat?->id }}">

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
                                    @if($prodiUser)
                                    <option value="{{ $prodiUser->id }}" selected>
                                        {{ $prodiUser->full_name }}
                                    </option>
                                    @else
                                    @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ old('id_program_studi', $selectedPengingat?->id_program_studi) == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->full_name }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                                @error('id_program_studi')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($selectedPengingat)
                                <small class="text-success">
                                    <i class="bi bi-check-circle"></i> Dipilih dari pengingat
                                </small>
                                @endif
                            </div>

                            <!-- Tahun Akreditasi -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Tahun Akreditasi <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="tahun_akreditasi" id="tahun_akreditasi" class="form-control @error('tahun_akreditasi') is-invalid @enderror" value="{{ old('tahun_akreditasi', $selectedPengingat?->tahun_akreditasi ?? date('Y')) }}" min="2024" max="{{ date('Y') + 2 }}" required>
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

                        {{-- Section 3: Upload Dokumen --}}
                        <div class="form-section">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-file-earmark-arrow-up"></i> Dokumen Permohonan
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Surat Permohonan Banding (PDF)
                                    <span class="text-danger" id="label-required">*</span>
                                    <span class="badge bg-secondary" id="label-optional" style="display: none;">Opsional</span>
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

                            <!-- Persyaratan Info -->
                            <div class="alert alert-light border">
                                <h6 class="fw-bold mb-2">
                                    <i class="bi bi-clipboard-check"></i> Persyaratan Dokumen
                                </h6>
                                <ul class="mb-0 ps-3">
                                    <li>Surat Permohonan Banding resmi dalam format PDF</li>
                                    <li>Menggunakan kop surat program studi/universitas</li>
                                    <li>Ditandatangani oleh pejabat berwenang (Ketua Program Studi/Dekan)</li>
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
                                <textarea name="catatan_pengaju" class="form-control @error('catatan_pengaju') is-invalid @enderror" rows="4" placeholder="Masukkan catatan atau keterangan tambahan jika ada...">{{ old('catatan_pengaju') }}</textarea>
                                @error('catatan_pengaju')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <a href="{{ route('upps.surat-permohonan') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" name="is_draft" value="1" class="btn btn-outline-primary" id="btnDraft">
                                    <i class="bi bi-save"></i> Simpan sebagai Draft
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
                <div class="card border-info mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Informasi
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold">2 Opsi Pengajuan:</h6>
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
                            @if($selectedPengingat)
                            <li class="text-success">
                                <i class="bi bi-check-circle"></i>
                                Pengingat akan ditandai sebagai "Telah direspon" setelah dikirim
                            </li>
                            @endif
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
        const form = document.getElementById('formPermohonan');
        const btnDraft = document.getElementById('btnDraft');
        const btnSubmit = document.getElementById('btnSubmit');
        const labelRequired = document.getElementById('label-required');
        const labelOptional = document.getElementById('label-optional');
        const draftInfo = document.getElementById('draft-info');

        // ===============================
        // File upload preview
        // ===============================
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

        // ===============================
        // Draft vs Submit button behavior
        // ===============================
        if (btnDraft) {
            btnDraft.addEventListener('click', function(e) {
                // Untuk draft, file tidak required
                if (fileInput) {
                    fileInput.removeAttribute('required');
                }

                // Update label
                if (labelRequired) labelRequired.style.display = 'none';
                if (labelOptional) labelOptional.style.display = 'inline';
                if (draftInfo) draftInfo.style.display = 'inline';
            });
        }

        if (btnSubmit) {
            btnSubmit.addEventListener('click', function(e) {
                // Untuk submit, file required
                if (fileInput) {
                    fileInput.setAttribute('required', 'required');

                    if (!fileInput.files || !fileInput.files.length) {
                        e.preventDefault();
                        alert('File surat permohonan wajib diupload untuk mengirim permohonan!');
                        fileInput.focus();
                        return false;
                    }
                }

                // Update label
                if (labelRequired) labelRequired.style.display = 'inline';
                if (labelOptional) labelOptional.style.display = 'none';
                if (draftInfo) draftInfo.style.display = 'none';

                // Konfirmasi
                const isPengingat = document.getElementById('id_pengingat').value !== '';
                const confirmText = isPengingat ?
                    'Apakah Anda yakin akan mengirim permohonan ini sebagai respon pengingat?' :
                    'Apakah Anda yakin data yang diisi sudah benar dan siap untuk dikirim?';

                if (!confirm(confirmText)) {
                    e.preventDefault();
                    return false;
                }

                // Disable button
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';
            });
        }
    });

    // ===============================
    // Select Pengingat Function
    // ===============================
    function selectPengingat(pengingatId) {
        // Remove selected class from all cards
        document.querySelectorAll('.pengingat-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Find clicked card
        const selectedCard = document.querySelector(`[data-pengingat-id="${pengingatId}"]`);

        if (selectedCard) {
            selectedCard.classList.add('selected');

            // Auto-fill form data
            const prodiId = selectedCard.dataset.prodiId;
            const tahun = selectedCard.dataset.tahun;

            document.getElementById('id_pengingat').value = pengingatId;
            document.getElementById('id_program_studi').value = prodiId;
            document.getElementById('tahun_akreditasi').value = tahun;

            // Show success message
            const prodiSelect = document.getElementById('id_program_studi');
            if (prodiSelect) {
                const successMsg = prodiSelect.parentElement.querySelector('.text-success');
                if (!successMsg) {
                    const msg = document.createElement('small');
                    msg.className = 'text-success d-block mt-1';
                    msg.innerHTML = '<i class="bi bi-check-circle"></i> Dipilih dari pengingat';
                    prodiSelect.parentElement.appendChild(msg);
                }
            }

            // Scroll to form
            document.getElementById('formPermohonan').scrollIntoView({
                behavior: 'smooth'
                , block: 'start'
            });
        }
    }

    // Auto-select if coming from URL parameter
    let selectedPengingat = @json($selectedPengingat);
    if (selectedPengingat) {
        window.addEventListener('load', function() {
            selectPengingat(selectedPengingat.id);
        });
    }

</script>
@endpush
