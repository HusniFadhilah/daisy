@extends('layouts.template.auth')

@section('title', 'Login - DAISY')

@php
function getRoleIcon($roleName) {
$icons = [
'super_admin' => 'shield-fill-check',
'asesi' => 'person-badge',
'asesor' => 'clipboard-check',
'validator' => 'check2-circle',
'verifikator' => 'shield-check',
'admin_univ' => 'building',
'admin_prodi' => 'mortarboard',
'default' => 'person',
];
return $icons[$roleName] ?? 'person';
}

function getRoleDescription($roleName) {
$descriptions = [
'super_admin' => 'Akses penuh ke seluruh sistem',
'asesi' => 'Desk Evaluator - Review dan evaluasi pengajuan',
'asesor' => 'Melakukan penilaian dokumen akreditasi',
'validator' => 'Validasi hasil penilaian asesor',
'verifikator' => 'Verifikasi dokumen dan data',
'admin_univ' => 'Administrator Perguruan Tinggi',
'admin_prodi' => 'Administrator Program Studi',
'default' => 'Pengguna umum',
];
return $descriptions[$roleName] ?? 'User role';
}
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    body {
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .role-selection-container {
        max-width: 800px;
        width: 100%;
        padding: 20px;
    }

    .role-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .role-card-header {
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .role-card-body {
        padding: 40px;
    }

    .role-option {
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
    }

    .role-option:hover {
        border-color: #932136;
        background: #fff5f7;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(147, 33, 54, 0.1);
    }

    .role-option.active {
        border-color: #932136;
        background: linear-gradient(135deg, rgba(147, 33, 54, 0.1) 0%, rgba(135, 8, 32, 0.1) 100%);
    }

    .role-option input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .role-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 15px;
    }

    .role-info {
        flex: 1;
    }

    .role-name {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .role-description {
        font-size: 14px;
        color: #666;
    }

    .check-icon {
        color: #932136;
        font-size: 24px;
        display: none;
    }

    .role-option.active .check-icon {
        display: block;
    }

    .btn-continue {
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        border: none;
        color: white;
        padding: 12px 40px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-continue:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(147, 33, 54, 0.3);
        color: white;
    }

    .btn-continue:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

</style>
@endpush

@section('content')
<div class="role-selection-container">
    <div class="role-card">
        <div class="role-card-header">
            <h3 class="mb-2">Selamat Datang, {{ Auth::user()->name }}!</h3>
            <p class="mb-0">Pilih role yang ingin Anda gunakan</p>
        </div>

        <div class="role-card-body">
            <form action="{{ route('select.role.post') }}" method="POST" id="roleForm">
                @csrf

                <div id="roleOptions">
                    @foreach($roles as $role)
                    <label class="role-option {{ $role['name'] === $current_role ? 'active' : '' }}">
                        <input type="radio" name="role" value="{{ $role['name'] }}" {{ $role['name'] === $current_role ? 'checked' : '' }}>

                        <div class="d-flex align-items-center">
                            <div class="role-icon">
                                <i class="bi bi-{{ getRoleIcon($role['name']) }}"></i>
                            </div>
                            <div class="role-info">
                                <div class="role-name">{{ $role['alias'] }}</div>
                                <div class="role-description">{{ getRoleDescription($role['name']) }}</div>
                            </div>
                            <div class="check-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-continue">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Lanjutkan ke Dashboard
                    </button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-muted">
                    <i class="bi bi-box-arrow-left me-1"></i>
                    Keluar
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Custom Scripts -->
<script>
    // Make entire card clickable
    document.querySelectorAll('.role-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove active from all
            document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('active'));

            // Add active to clicked
            this.classList.add('active');

            // Check radio
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

</script>
@endpush
