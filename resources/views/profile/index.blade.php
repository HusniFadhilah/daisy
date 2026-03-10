@extends('layouts.template.app')

@section('title', 'Profil Saya - DAISY LAMDEPILAR')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .page-header-compact {
        margin-bottom: 1.5rem;
    }

    .page-header-compact h2 {
        font-size: 1.5rem;
        margin-bottom: 0;
    }

    .page-header-compact p {
        margin-bottom: 0;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .avatar-section {
        text-align: center;
    }

    .avatar-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 0.75rem;
    }

    .avatar-preview {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 600;
        color: white;
        border: 4px solid var(--light);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .avatar-upload-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background: white;
        border: 2px solid var(--primary);
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        font-size: 0.9rem;
    }

    .avatar-upload-btn:hover {
        background: var(--primary);
        color: white;
        transform: scale(1.05);
    }

    .profile-name {
        font-size: 1.05rem;
        font-weight: 600;
        margin-bottom: 0.1rem;
    }

    .profile-role {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.1rem;
    }

    .profile-meta {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        padding: 0.75rem;
        background: var(--light);
        border-radius: 8px;
        margin-bottom: 0.75rem;
    }

    .info-item i {
        font-size: 1.2rem;
        color: var(--primary);
        margin-right: 0.75rem;
        width: 26px;
        text-align: center;
        margin-top: 2px;
    }

    .info-item-label {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.1rem;
    }

    .info-item-value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #212529;
    }

    .required-field::after {
        content: " *";
        color: var(--danger);
    }

    .prodi-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        margin: 0.25rem;
        background: #e7f3ff;
        border: 1px solid #0dcaf0;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .readonly-info {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        padding: 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .avatar-preview {
            width: 90px;
            height: 90px;
            font-size: 2rem;
        }
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
                    <i class="bi bi-person-circle me-2"></i>Profil Saya
                </h2>
                <p class="mb-0">Kelola informasi profil dan akun Anda</p>
            </div>
            <a href="{{ route('profile.password') }}" class="quick-btn">
                <i class="bi bi-key"></i>Ubah Password
            </a>
        </div>
    </div>
</div>

{{-- Baris atas: kiri avatar, kanan info akun --}}
<div class="row g-3 mb-3">
    {{-- Kartu Avatar & ringkasan --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                @if($user->role_selected === 'admin_prodi' && $user->university && $user->university->logo_path)
                <div class="mb-3">
                    <img src="{{ asset('storage/' . $user->university->logo_path) }}" alt="Logo {{ $user->university->name }}" style="max-height: 80px; max-width: 200px; object-fit: contain;">
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Logo Universitas
                        </small>
                    </div>
                </div>
                @endif

                <div class="avatar-section mb-2">
                    <div class="avatar-wrapper">
                        <div class="avatar-preview" id="avatarPreview">
                            @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar">
                            @else
                            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            @endif
                        </div>
                        <label for="avatarInput" class="avatar-upload-btn" title="Ubah foto profil">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                    </div>
                </div>
                <div class="profile-name">{{ $user->name ?? 'Nama User' }}</div>
                <div class="profile-role">{{ $user->role_alias ?? 'Role User' }}</div>
                @if($user->position)
                <div class="profile-meta">
                    <i class="bi bi-briefcase"></i> {{ $user->position }}
                </div>
                @endif
                <div class="profile-meta">
                    Bergabung: {{ \App\Libraries\Date::tglIndo($user->created_at) }}
                </div>
            </div>
        </div>
    </div>

    {{-- Informasi akun ringkas --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>Informasi Akun
                </h6>

                @if(in_array($user->role_selected, ['admin_prodi', 'admin_univ']))
                <a href="{{ route('profile.prodi-data') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-building me-1"></i>Kelola Data Prodi
                </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-envelope"></i>
                            <div>
                                <div class="info-item-label">Email</div>
                                <div class="info-item-value text-wrap">{{ $user->email ?? 'email@example.com' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <div class="info-item-label">Role</div>
                                <div class="info-item-value">{{ $user->role_alias ?? 'User' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($user->phone)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-telephone"></i>
                            <div>
                                <div class="info-item-label">No. Telepon</div>
                                <div class="info-item-value text-wrap">{{ $user->phone }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($user->university)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-building"></i>
                            <div>
                                <div class="info-item-label">Universitas</div>
                                <div class="info-item-value">{{ $user->university->name }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($user->role_selected === 'admin_prodi' && $myStudyPrograms->count() > 0)
                    <div class="col-12">
                        <div class="info-item">
                            <i class="bi bi-mortarboard"></i>
                            <div class="flex-grow-1">
                                <div class="info-item-label">Program Studi yang Dikelola</div>
                                <div class="mt-2">
                                    @foreach($myStudyPrograms as $prodi)
                                    <span class="prodi-badge">
                                        {{ $prodi->name }}
                                        @if($prodi->degreeLevel)
                                        ({{ $prodi->degreeLevel->alias }})
                                        @endif
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-calendar-check"></i>
                            <div>
                                <div class="info-item-label">Bergabung Sejak</div>
                                <div class="info-item-value">
                                    {{ isset($user->created_at) ? \App\Libraries\Date::tglIndo($user->created_at) : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-clock-history"></i>
                            <div>
                                <div class="info-item-label">Terakhir Update</div>
                                <div class="info-item-value">
                                    {{ isset($user->updated_at) ? \App\Libraries\Date::tglWaktu($user->updated_at) : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Form Edit Profil --}}
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-pencil-square me-2"></i>Edit Profil
        </h6>
    </div>
    <div class="card-body">
        <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold required-field">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Masukkan nama lengkap" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">Minimal 3 karakter</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fw-semibold required-field">Email</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="email@example.com" required readonly>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">Email tidak dapat diubah</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label fw-semibold">No. Telepon</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-telephone"></i>
                        </span>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="08xxxxxxxxxx">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="position" class="form-label fw-semibold">Jabatan</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-briefcase"></i>
                        </span>
                        <input type="text" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position', $user->position ?? '') }}" placeholder="Contoh: Ketua Program Studi">
                        @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Universitas Field --}}
                <div class="col-md-6 mb-3">
                    <label for="id_university" class="form-label fw-semibold">Universitas</label>

                    @if($user->id_university && !$canEditUniversity)
                    {{-- Readonly: Tampilkan info saja --}}
                    <div class="readonly-info">
                        <i class="bi bi-building text-primary me-2"></i>
                        <strong>{{ $user->university->name }}</strong>
                        <br>
                        <small class="text-muted">
                            <i class="bi bi-lock-fill me-1"></i>
                            Universitas tidak dapat diubah
                        </small>
                    </div>
                    <input type="hidden" name="id_university" value="{{ $user->id_university }}">
                    @elseif($canEditUniversity)
                    {{-- Editable: Tampilkan select2 --}}
                    <select class="form-select select2 @error('id_university') is-invalid @enderror" id="id_university" name="id_university">
                        <option value="">-- Pilih Universitas --</option>
                        @foreach($universities as $univ)
                        <option value="{{ $univ->id }}" {{ old('id_university', $user->id_university) == $univ->id ? 'selected' : '' }}>
                            {{ $univ->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_university')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @else
                    {{-- Tidak ada data dan tidak bisa edit --}}
                    <div class="readonly-info">
                        <i class="bi bi-info-circle text-muted me-2"></i>
                        <small class="text-muted">Tidak terikat dengan universitas tertentu</small>
                    </div>
                    @endif
                </div>

                {{-- Program Studi Field --}}
                <div class="col-md-6 mb-3">
                    <label for="id_study_program" class="form-label fw-semibold">Program Studi</label>

                    @if($user->role_selected === 'admin_prodi' && $myStudyPrograms->count() > 0)
                    {{-- Admin Prodi: Tampilkan list prodi yang dikelola (readonly) --}}
                    <div class="readonly-info">
                        <i class="bi bi-mortarboard text-primary me-2"></i>
                        <strong>Mengelola {{ $myStudyPrograms->count() }} Program Studi:</strong>
                        <div class="mt-2">
                            @foreach($myStudyPrograms as $prodi)
                            <span class="prodi-badge">
                                {{ $prodi->name }}
                                @if($prodi->degreeLevel)
                                ({{ $prodi->degreeLevel->alias }})
                                @endif
                            </span>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="bi bi-lock-fill me-1"></i>
                            Program studi ditentukan oleh sistem untuk UPPS
                        </small>
                    </div>
                    @elseif($canEditProdi)
                    {{-- Super Admin: Bisa pilih prodi --}}
                    <select class="form-select select2 @error('id_study_program') is-invalid @enderror" id="id_study_program" name="id_study_program">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($studyPrograms as $prodi)
                        <option value="{{ $prodi->id }}" data-university="{{ $prodi->id_university }}" {{ old('id_study_program', $user->id_study_program) == $prodi->id ? 'selected' : '' }}>
                            {{ $prodi->name }}
                            @if($prodi->degreeLevel)
                            ({{ $prodi->degreeLevel->alias }})
                            @endif
                            - {{ $prodi->university->name ?? '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_study_program')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @else
                    {{-- Role lain: Tidak terikat prodi --}}
                    <div class="readonly-info">
                        <i class="bi bi-info-circle text-muted me-2"></i>
                        <small class="text-muted">Tidak terikat dengan program studi tertentu</small>
                    </div>
                    @endif
                </div>

                <div class="col-12 mb-3">
                    <label for="address" class="form-label fw-semibold">Alamat</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-geo-alt"></i>
                        </span>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap">{{ old('address', $user->address ?? '') }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="alert alert-info alert-permanent mb-0 mt-2">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Catatan:</strong>
                @if(!$canEditUniversity && !$canEditProdi)
                Universitas dan Program Studi ditentukan oleh sistem sesuai role Anda. Silahkan hubungi sekretariat@lamdepilar.or.id apabila ingin mengubah prodi.<br>
                @endif
                Untuk mengubah password, gunakan menu
                <a href="{{ route('profile.password') }}" class="alert-link">Ubah Password</a>.
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary px-4">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card my-3" id="emailNotifyCard">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0">
            <i class="bi bi-envelope-plus me-2"></i>Email Notifikasi Tambahan
        </h6>
        <button type="button" class="btn btn-sm btn-outline-light" id="btnRefreshEmails" style="display:none;">
            <i class="bi bi-arrow-clockwise"></i>
        </button>
    </div>

    <div class="card-body">
        <div class="mb-2">
            <div class="text-muted small">Email Utama</div>
            <div class="fw-semibold" id="primaryEmailText">-</div>
        </div>

        <hr>

        <div class="mb-2">
            <div class="text-muted small">Daftar Email Tambahan</div>
        </div>

        <div id="emailList">
            <div class="text-muted">Memuat...</div>
        </div>

        <hr>

        <div class="mt-2">
            <label class="form-label fw-semibold">Tambah Email</label>
            <div class="input-group">
                <input type="email" class="form-control" id="newEmailInput" placeholder="email-notifikasi@example.com">
                <button class="btn btn-primary" type="button" id="btnAddEmail">
                    <i class="bi bi-plus-circle"></i> Tambah
                </button>
            </div>
            <div class="small text-danger mt-1" id="emailErrorText" style="display:none;"></div>
            <div class="small text-muted mt-1">
                Email tambahan akan menerima notifikasi sistem (selama statusnya aktif).
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5'
            , width: '100%'
            , placeholder: function() {
                $(this).data('placeholder');
            }
            , allowClear: true
        });
    });

    // Preview dan Upload Avatar
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!validTypes.includes(file.type)) {
                Swal.fire('Perhatian', 'Format file tidak valid. Gunakan JPG, PNG, atau GIF.', 'warning');
                return;
            }

            if (file.size > maxSize) {
                Swal.fire('Perhatian', 'Ukuran file terlalu besar. Maksimal 2MB.', 'warning');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(event) {
                const avatarPreview = document.getElementById('avatarPreview');
                avatarPreview.innerHTML = `<img src="${event.target.result}" alt="Avatar">`;
            };
            reader.readAsDataURL(file);

            uploadAvatar(file);
        }
    });

    function uploadAvatar(file) {
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        const uploadBtn = document.querySelector('.avatar-upload-btn');
        const originalHTML = uploadBtn.innerHTML;
        uploadBtn.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

        fetch('{{ route("profile.avatar") }}', {
                method: 'POST'
                , body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Foto profil berhasil diperbarui!');
                } else {
                    throw new Error(data.message || 'Upload gagal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Gagal mengupload foto profil. Silakan coba lagi.');
            })
            .finally(() => {
                uploadBtn.innerHTML = originalHTML;
            });
    }

    function showAlert(type, message) {
        const alertHTML = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        const mainContent = document.querySelector('.main-content') || document.querySelector('.profile-container');
        mainContent.insertAdjacentHTML('afterbegin', alertHTML);

        setTimeout(() => {
            const alert = mainContent.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();

        if (name.length < 3) {
            e.preventDefault();
            Swal.fire('Perhatian', 'Nama harus minimal 3 karakter.', 'warning');
            return false;
        }
    });

    (function() {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const endpoints = {
            index: @json(route('profile.emails.index'))
            , store: @json(route('profile.emails.store'))
            , toggle: (id) => @json(route('profile.emails.toggle', ['id' => '__ID__'])).replace('__ID__', id)
            , destroy: (id) => @json(route('profile.emails.destroy', ['id' => '__ID__'])).replace('__ID__', id)
        , };

        const elPrimary = document.getElementById('primaryEmailText');
        const elList = document.getElementById('emailList');
        const elInput = document.getElementById('newEmailInput');
        const elErr = document.getElementById('emailErrorText');
        const btnAdd = document.getElementById('btnAddEmail');

        function showFieldError(msg) {
            elErr.textContent = msg || '';
            elErr.style.display = msg ? 'block' : 'none';
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, s => ({
                '&': '&amp;'
                , '<': '&lt;'
                , '>': '&gt;'
                , '"': '&quot;'
                , "'": '&#039;'
            } [s]));
        }

        async function api(url, options) {
            options = options || {};

            var opts = {
                method: options.method || 'GET'
                , headers: {
                    'X-CSRF-TOKEN': csrf
                    , 'Accept': 'application/json'
                }
            };

            if (options.body) {
                opts.headers['Content-Type'] = 'application/json';
                opts.body = JSON.stringify(options.body);
            }

            var res = await fetch(url, opts);

            var data = {};
            try {
                data = await res.json();
            } catch (e) {
                data = {};
            }

            if (!res.ok) {
                var msg = data.message ? data.message : 'Terjadi kesalahan.';
                var err = new Error(msg);
                err.errors = data.errors ? data.errors : null;
                throw err;
            }

            return data;
        }

        function renderList(emails) {
            if (!emails || emails.length === 0) {
                elList.innerHTML =
                    '<div class="text-muted">Belum ada email tambahan.</div>';
                return;
            }

            var html = '';

            for (var i = 0; i < emails.length; i++) {
                var e = emails[i];

                var badge = e.is_active ?
                    '<span class="badge bg-success ms-2">Aktif</span>' :
                    '<span class="badge bg-warning text-dark ms-2">Nonaktif</span>';

                var toggleText = e.is_active ? 'Nonaktifkan' : 'Aktifkan';

                html +=
                    '<div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">' +
                    '<div class="me-2 text-wrap">' +
                    '<i class="bi bi-envelope me-1"></i>' +
                    escapeHtml(e.email) +
                    badge +
                    '</div>' +
                    '<div class="btn-group btn-group-sm flex-shrink-0">' +
                    '<button class="btn btn-outline-primary" data-action="toggle" data-id="' + e.id + '">' +
                    toggleText +
                    '</button>' +
                    '<button class="btn btn-outline-danger" data-action="delete" data-id="' + e.id + '">' +
                    '<i class="bi bi-trash"></i>' +
                    '</button>' +
                    '</div>' +
                    '</div>';
            }

            elList.innerHTML = html;
        }

        async function loadEmails() {
            try {
                var res = await api(endpoints.index);

                if (res && res.data) {
                    elPrimary.textContent = res.data.primary_email ?
                        res.data.primary_email :
                        '-';

                    renderList(res.data.emails ? res.data.emails : []);
                } else {
                    renderList([]);
                }
            } catch (e) {
                elList.innerHTML =
                    '<div class="text-danger">Gagal memuat email: ' +
                    escapeHtml(e.message) +
                    '</div>';
            }
        }

        btnAdd.addEventListener('click', async function() {
            showFieldError(null);

            var email = elInput.value ? elInput.value.trim() : '';
            if (!email) {
                showFieldError('Email wajib diisi.');
                return;
            }

            setButtonLoading(btnAdd, true, 'Menambah');

            try {
                await api(endpoints.store, {
                    method: 'POST'
                    , body: {
                        email: email
                    }
                });

                elInput.value = '';
                await loadEmails();

                if (typeof showAlert === 'function') {
                    showAlert('success', 'Email tambahan berhasil ditambahkan.');
                }
            } catch (e) {
                var msg = 'Gagal menambahkan email.';
                if (e.errors && e.errors.email && e.errors.email.length > 0) {
                    msg = e.errors.email[0];
                } else if (e.message) {
                    msg = e.message;
                }
                showFieldError(msg);
            } finally {
                setButtonLoading(btnAdd, false);
            }
        });

        elList.addEventListener('click', async function(ev) {
            var btn = ev.target.closest('button[data-action]');
            if (!btn) return;

            var action = btn.getAttribute('data-action');
            var id = btn.getAttribute('data-id');

            if (!id) return;

            try {
                if (action === 'toggle') {
                    setButtonLoading(btn, true, 'Memproses');

                    await api(endpoints.toggle(id), {
                        method: 'PATCH'
                    });
                    await loadEmails();

                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Status email berhasil diperbarui.');
                    }
                }

                if (action === 'delete') {
                    if (!(await swalConfirmSubmit('warning', 'Hapus email ini?'))) return;

                    setButtonLoading(btn, true, 'Menghapus');

                    await api(endpoints.destroy(id), {
                        method: 'DELETE'
                    });
                    await loadEmails();

                    if (typeof showAlert === 'function') {
                        showAlert('success', 'Email berhasil dihapus.');
                    }
                }
            } catch (e) {
                if (typeof showAlert === 'function') {
                    showAlert('danger', e.message || 'Terjadi kesalahan.');
                }
            } finally {
                setButtonLoading(btn, false);
            }
        });

        // load pertama kali
        loadEmails();
    })();

    function setButtonLoading(btn, isLoading, loadingText) {
        if (!btn) return;

        if (isLoading) {
            // simpan state awal
            btn.dataset.originalHtml = btn.innerHTML;
            btn.dataset.originalDisabled = btn.disabled ? '1' : '0';

            btn.disabled = true;
            btn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                (loadingText ? loadingText : 'Loading...');
        } else {
            // restore state
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
            btn.disabled = btn.dataset.originalDisabled === '1';
        }
    }

</script>
@endpush
