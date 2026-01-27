@extends('layouts.template.app')

@section('title', 'Data Program Studi - DAISY')

@push('styles')
<style>
    .info-section {
        background: #f8f9fa;
        border-left: 4px solid var(--primary);
        padding: 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
    }

    .prodi-card {
        transition: all 0.3s;
        border: 1px solid #dee2e6;
    }

    .prodi-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .prodi-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        padding: 1rem;
        border-radius: 0.375rem 0.375rem 0 0;
    }

    .badge-jenjang {
        font-size: 0.85rem;
        padding: 0.35rem 0.75rem;
    }

    .logo-preview {
        max-height: 150px;
        max-width: 100%;
        object-fit: contain;
        border: 2px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem;
        background: white;
    }

    .logo-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
    }

    .logo-upload-area:hover {
        border-color: var(--primary);
        background-color: #f8f9fa;
    }

    .logo-upload-area.dragover {
        border-color: var(--primary);
        background-color: #e7f3ff;
    }

</style>
@endpush

@section('content')
<!-- Header -->
<div class="welcome-section mb-4 py-4">
    <div class="welcome-content">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="mb-2">
                    <i class="bi bi-building me-2"></i>Data Program Studi
                </h2>
                <p class="mb-0">Kelola data lembaga dan program studi untuk akreditasi</p>
            </div>
            <a href="{{ route('profile') }}" class="quick-btn">
                <i class="bi bi-arrow-left"></i>Kembali ke Profil
            </a>
        </div>
    </div>
</div>

<!-- Info Section -->
<div class="info-section">
    <div class="d-flex align-items-start">
        <i class="bi bi-info-circle text-primary fs-4 me-3"></i>
        <div>
            <h6 class="fw-bold mb-2">Informasi Penting</h6>
            <p class="mb-1 small">Data ini akan digunakan untuk <strong>Lembar Pengesahan</strong> akreditasi program studi.</p>
            <p class="mb-0 small">Pastikan semua informasi yang diisi akurat dan terkini.</p>
        </div>
    </div>
</div>

<!-- University Logo & LPM Data -->
@if($university)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-bank me-2"></i>
            Data Universitas - {{ $university->name }}
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Logo Upload Section -->
            <div class="col-md-4 mb-4">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-image me-2"></i>Logo Universitas
                </h6>

                <div class="text-center mb-3" id="logoPreviewContainer" style="{{ $university->logo_path ? '' : 'display:none;' }}">
                    <img src="{{ $university->logo_path ? asset('storage/' . $university->logo_path) : '' }}" alt="Logo {{ $university->name }}" class="logo-preview" id="logoPreview">
                </div>

                <div class="logo-upload-area" id="logoUploadArea" onclick="document.getElementById('logoInput').click()">
                    <i class="bi bi-cloud-upload fs-1 text-primary mb-2"></i>
                    <p class="mb-1 fw-semibold">Klik untuk upload logo</p>
                    <small class="text-muted">atau drag & drop file di sini</small>
                    <small class="d-block text-muted mt-2">Format: JPG, PNG, GIF (Max 2MB)</small>
                </div>
                <input type="file" id="logoInput" name="logo" accept="image/*" style="display: none;">
            </div>

            <!-- LPM Data Section -->
            <div class="col-md-8">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-shield-check me-2"></i>Lembaga Penjaminan Mutu
                </h6>

                <form id="lpmForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="lpm_name" class="form-label fw-semibold">Nama Lembaga Penjaminan Mutu</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-building"></i>
                                </span>
                                <input type="text" class="form-control" id="lpm_name" name="lpm_name" value="{{ $university->lpm_name }}" placeholder="Contoh: Lembaga Penjaminan Mutu Universitas DEPILAR">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="lpm_email" class="form-label fw-semibold">Email LPM</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="lpm_email" name="lpm_email" value="{{ $university->lpm_email }}" placeholder="lpm@universitas.ac.id">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="lpm_phone" class="form-label fw-semibold">Telp LPM</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="text" class="form-control" id="lpm_phone" name="lpm_phone" value="{{ $university->lpm_phone }}" placeholder="024-1234567">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="lpm_mobile" class="form-label fw-semibold">Mobile/WA LPM</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-whatsapp"></i>
                                </span>
                                <input type="text" class="form-control" id="lpm_mobile" name="lpm_mobile" value="{{ $university->lpm_mobile }}" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-2"></i>Simpan Data LPM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Study Programs Data -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-mortarboard me-2"></i>
            Data Program Studi
        </h5>
    </div>
    <div class="card-body">
        @if($studyPrograms->count() > 0)
        <div class="row g-4">
            @foreach($studyPrograms as $prodi)
            <div class="col-12">
                <div class="prodi-card">
                    <div class="prodi-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">{{ $prodi->name }}</h5>
                                <small>{{ $prodi->full_name }}</small>
                            </div>
                            <div class="text-end">
                                @if($prodi->degreeLevel)
                                <span class="badge badge-jenjang bg-light text-dark">
                                    {{ $prodi->degreeLevel->alias }}
                                </span>
                                @endif
                                @if($prodi->peringkat_akreditasi)
                                <span class="badge badge-jenjang bg-warning text-dark">
                                    {{ $prodi->peringkat_akreditasi }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="p-3">
                        <form class="prodi-form" data-prodi-id="{{ $prodi->id }}">
                            @csrf
                            <div class="row">
                                <!-- Ketua Program Studi -->
                                <div class="col-12 mb-3">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-person-badge me-2"></i>Ketua Program Studi
                                    </h6>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Nama Ketua Program Studi</label>
                                    <input type="text" class="form-control" name="ketua_prodi_name" value="{{ $prodi->ketua_prodi_name }}" placeholder="Contoh: Dr. John Doe, S.T., M.T.">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">NIP Ketua Program Studi</label>
                                    <input type="text" class="form-control" name="ketua_prodi_nip" value="{{ $prodi->ketua_prodi_nip }}" placeholder="Contoh: 198012312005011001">
                                </div>

                                <!-- Ketua Tim Akreditasi -->
                                <div class="col-12 mb-3 mt-3">
                                    <h6 class="fw-bold text-success mb-3">
                                        <i class="bi bi-award me-2"></i>Tim Akreditasi
                                    </h6>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-semibold">Ketua Tim Akreditasi</label>
                                    <input type="text" class="form-control" name="ketua_tim_akreditasi" value="{{ $prodi->ketua_tim_akreditasi }}" placeholder="Contoh: Prof. Dr. Jane Smith, M.Sc.">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Telp Tim Akreditasi</label>
                                    <input type="text" class="form-control" name="akreditasi_phone" value="{{ $prodi->akreditasi_phone }}" placeholder="024-1234567">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Mobile/WA Tim Akreditasi</label>
                                    <input type="text" class="form-control" name="akreditasi_mobile" value="{{ $prodi->akreditasi_mobile }}" placeholder="08xxxxxxxxxx">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Email Tim Akreditasi</label>
                                    <input type="email" class="form-control" name="akreditasi_email" value="{{ $prodi->akreditasi_email }}" placeholder="akreditasi@prodi.ac.id">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-success px-4">
                                    <i class="bi bi-check-circle me-2"></i>Simpan Data {{ $prodi->name }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Tidak ada program studi yang ditemukan.
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ============================================
    // LOGO UPLOAD HANDLER
    // ============================================
    const logoInput = document.getElementById('logoInput');
    const logoUploadArea = document.getElementById('logoUploadArea');
    const logoPreview = document.getElementById('logoPreview');
    const logoPreviewContainer = document.getElementById('logoPreviewContainer');

    // Click to upload
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                uploadLogo(file);
            }
        });
    }

    // Drag and drop
    if (logoUploadArea) {
        logoUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        logoUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });

        logoUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');

            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                uploadLogo(file);
            } else {
                Swal.fire({
                    icon: 'error'
                    , title: 'File Tidak Valid'
                    , text: 'File harus berupa gambar (JPG, PNG, GIF)'
                , });
            }
        });
    }

    function uploadLogo(file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!validTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error'
                , title: 'Format Tidak Valid'
                , text: 'Gunakan format JPG, PNG, atau GIF'
            , });
            return;
        }

        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error'
                , title: 'File Terlalu Besar'
                , text: 'Ukuran maksimal 2MB'
            , });
            return;
        }

        const formData = new FormData();
        formData.append('logo', file);
        formData.append('_token', '{{ csrf_token() }}');

        // Show loading
        Swal.fire({
            title: 'Mengupload Logo...'
            , allowOutsideClick: false
            , didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('{{ route("profile.prodi-data.logo") }}', {
                method: 'POST'
                , body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update logo preview
                    logoPreview.src = data.logo_url + '?t=' + new Date().getTime(); // Add timestamp to bypass cache
                    logoPreviewContainer.style.display = 'block';

                    Swal.fire({
                        icon: 'success'
                        , title: 'Berhasil!'
                        , text: data.message
                        , timer: 2000
                        , showConfirmButton: false
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error'
                    , title: 'Gagal Upload'
                    , text: 'Terjadi kesalahan saat mengupload logo. Silakan coba lagi.'
                , });
            });
    }

    // ============================================
    // LPM FORM HANDLER
    // ============================================
    const lpmForm = document.getElementById('lpmForm');
    if (lpmForm) {
        lpmForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Show loading
            Swal.fire({
                title: 'Menyimpan Data...'
                , allowOutsideClick: false
                , didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route("profile.prodi-data.university") }}', {
                    method: 'POST'
                    , body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success'
                            , title: 'Berhasil!'
                            , text: data.message
                            , timer: 2000
                            , showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal Menyimpan'
                        , text: 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'
                    , });
                });
        });
    }

    // ============================================
    // PRODI FORM HANDLER
    // ============================================
    const prodiForms = document.querySelectorAll('.prodi-form');
    prodiForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const prodiId = this.dataset.prodiId;
            const formData = new FormData(this);

            // Show loading
            Swal.fire({
                title: 'Menyimpan Data...'
                , allowOutsideClick: false
                , didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`{{ route("profile.prodi-data.study-program", ":id") }}`.replace(':id', prodiId), {
                    method: 'POST'
                    , body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success'
                            , title: 'Berhasil!'
                            , text: data.message
                            , timer: 2000
                            , showConfirmButton: false
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error'
                        , title: 'Gagal Menyimpan'
                        , text: 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'
                    , });
                });
        });
    });

</script>
@endpush
