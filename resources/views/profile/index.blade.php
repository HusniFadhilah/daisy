@extends('layouts.template.app')

@section('title', 'Profil Saya - DAISY')

@push('styles')
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
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-envelope"></i>
                            <div>
                                <div class="info-item-label">Email</div>
                                <div class="info-item-value">{{ $user->email ?? 'email@example.com' }}</div>
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
                                <div class="info-item-value">{{ $user->phone }}</div>
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

                    @if($user->studyProgram)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-mortarboard"></i>
                            <div>
                                <div class="info-item-label">Program Studi</div>
                                <div class="info-item-value">
                                    {{ $user->studyProgram->name }}
                                    @if($user->studyProgram->degreeLevel)
                                    ({{ $user->studyProgram->degreeLevel->alias }})
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($user->studyProgram && $user->studyProgram->category)
                    <div class="col-sm-6">
                        <div class="info-item">
                            <i class="bi bi-tag"></i>
                            <div>
                                <div class="info-item-label">Kategori Prodi</div>
                                <div class="info-item-value">{{ $user->studyProgram->category->name }}</div>
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
                                    {{ isset($user->created_at) ? $user->created_at->format('d F Y') : '-' }}
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
                                    {{ isset($user->updated_at) ? $user->updated_at->format('d F Y H:i') : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Form Edit Profil (tetap, tapi lebih “padat”) --}}
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
                    <small class="text-muted">Email harus valid dan unik</small>
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

                <div class="col-md-6 mb-3">
                    <label for="id_university" class="form-label fw-semibold">Universitas</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-building"></i>
                        </span>
                        <select class="form-select @error('id_university') is-invalid @enderror" id="id_university" name="id_university">
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
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="id_study_program" class="form-label fw-semibold">Program Studi</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-mortarboard"></i>
                        </span>
                        <select class="form-select @error('id_study_program') is-invalid @enderror" id="id_study_program" name="id_study_program">
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
                    </div>
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
                <strong>Catatan:</strong> Untuk mengubah password, gunakan menu
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
@endsection

@push('scripts')
<script>
    // Preview dan Upload Avatar
    document.getElementById('avatarInput').addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!validTypes.includes(file.type)) {
                alert('Format file tidak valid. Gunakan JPG, PNG, atau GIF.');
                return;
            }

            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
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
        const email = document.getElementById('email').value.trim();

        if (name.length < 3) {
            e.preventDefault();
            alert('Nama harus minimal 3 karakter.');
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Format email tidak valid.');
            return false;
        }
    });

    // Filter Program Studi berdasarkan Universitas
    const universitySelect = document.getElementById('id_university');
    const prodiSelect = document.getElementById('id_study_program');
    const allProdiOptions = Array.from(prodiSelect.options);

    universitySelect.addEventListener('change', function() {
        const selectedUniversityId = this.value;

        // Clear current options except first one
        prodiSelect.innerHTML = '<option value="">-- Pilih Program Studi --</option>';

        if (!selectedUniversityId) {
            // Show all options if no university selected
            allProdiOptions.slice(1).forEach(option => {
                prodiSelect.appendChild(option.cloneNode(true));
            });
        } else {
            // Filter by university
            allProdiOptions.slice(1).forEach(option => {
                if (option.dataset.university === selectedUniversityId) {
                    prodiSelect.appendChild(option.cloneNode(true));
                }
            });
        }
    });

</script>
@endpush
