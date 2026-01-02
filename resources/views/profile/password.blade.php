@extends('layouts.template.app')

@section('title', 'Ubah Password - DAISY')

@push('styles')
<style>
    .password-toggle {
        cursor: pointer;
        color: #6c757d;
        transition: color 0.3s;
    }

    .password-toggle:hover {
        color: var(--primary);
    }

    .password-strength {
        margin-top: 0.5rem;
        height: 5px;
        background: var(--light);
        border-radius: 3px;
        overflow: hidden;
        display: none;
    }

    .password-strength-bar {
        height: 100%;
        width: 0%;
        transition: all 0.3s;
        border-radius: 3px;
    }

    .strength-weak {
        background: var(--danger);
        width: 33%;
    }

    .strength-medium {
        background: var(--warning);
        width: 66%;
    }

    .strength-strong {
        background: var(--success);
        width: 100%;
    }

    .password-requirements {
        background: var(--light);
        padding: 1.25rem;
        border-radius: 8px;
        margin-top: 1rem;
        margin-bottom: 1.5rem;
    }

    .requirement-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .requirement-item:last-child {
        margin-bottom: 0;
    }

    .requirement-item i {
        margin-right: 0.5rem;
        font-size: 1rem;
    }

    .requirement-met {
        color: var(--success);
    }

    .requirement-unmet {
        color: #6c757d;
    }

    .required-field::after {
        content: " *";
        color: var(--danger);
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
                    <i class="bi bi-shield-lock me-2"></i>Ubah Password
                </h2>
                <p class="mb-0">Jaga keamanan akun Anda dengan password yang kuat</p>
            </div>
            <a href="{{ route('profile') }}" class="quick-btn">
                <i class="bi bi-arrow-left"></i>Kembali
            </a>
        </div>
    </div>
</div>

<!-- Change Password Form -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('profile.password.update') }}" method="POST" id="passwordForm">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div class="mb-4">
                <label for="current_password" class="form-label fw-semibold required-field">Password Lama</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" placeholder="Masukkan password lama" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('current_password')">
                        <i class="bi bi-eye" id="current_password_icon"></i>
                    </span>
                    @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <small class="text-muted">Konfirmasi password Anda saat ini</small>
            </div>

            <!-- New Password -->
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold required-field">Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan password baru" required oninput="checkPasswordStrength(this.value)">
                    <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                        <i class="bi bi-eye" id="password_icon"></i>
                    </span>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Strength Indicator -->
                <div class="password-strength" id="passwordStrength">
                    <div class="password-strength-bar" id="passwordStrengthBar"></div>
                </div>
                <small class="text-muted d-block mt-2" id="strengthText"></small>

                <!-- Password Requirements -->
                <div class="password-requirements">
                    <div class="requirement-item" id="req-length">
                        <i class="bi bi-circle requirement-unmet"></i>
                        <span>Minimal 6 karakter</span>
                    </div>
                    <div class="requirement-item" id="req-uppercase">
                        <i class="bi bi-circle requirement-unmet"></i>
                        <span>Minimal 1 huruf besar (A-Z)</span>
                    </div>
                    <div class="requirement-item" id="req-lowercase">
                        <i class="bi bi-circle requirement-unmet"></i>
                        <span>Minimal 1 huruf kecil (a-z)</span>
                    </div>
                    <div class="requirement-item" id="req-number">
                        <i class="bi bi-circle requirement-unmet"></i>
                        <span>Minimal 1 angka (0-9)</span>
                    </div>
                    <div class="requirement-item" id="req-special">
                        <i class="bi bi-circle requirement-unmet"></i>
                        <span>Minimal 1 karakter spesial (!@#$%^&*)</span>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold required-field">Konfirmasi Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-shield-check"></i>
                    </span>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Masukkan ulang password baru" required oninput="checkPasswordMatch()">
                    <span class="input-group-text password-toggle" onclick="togglePassword('password_confirmation')">
                        <i class="bi bi-eye" id="password_confirmation_icon"></i>
                    </span>
                </div>
                <small class="text-muted" id="matchText"></small>
            </div>

            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Perhatian:</strong> Setelah mengubah password, Anda akan tetap login pada sesi ini.
                Namun Anda perlu menggunakan password baru untuk login di perangkat lain.
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('profile') }}" class="btn btn-secondary px-4">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary px-4" id="submitBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Ubah Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Security Tips -->
<div class="card">
    <div class="card-header custom-header bg-primary text-white">
        <h3 class="mb-0 text-white">
            <i class="bi bi-lightbulb me-2"></i>Tips Keamanan Password
        </h3>
    </div>
    <div class="card-body">
        <ul class="mb-0">
            <li class="mb-2">Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol</li>
            <li class="mb-2">Hindari menggunakan informasi pribadi seperti nama atau tanggal lahir</li>
            <li class="mb-2">Jangan gunakan password yang sama untuk akun lain</li>
            <li class="mb-2">Ubah password secara berkala (setiap 3-6 bulan)</li>
            <li class="mb-2">Jangan bagikan password Anda kepada siapapun</li>
            <li class="mb-0">Gunakan password manager untuk menyimpan password dengan aman</li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle Password Visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Check Password Strength
    function checkPasswordStrength(password) {
        const strengthBar = document.getElementById('passwordStrengthBar');
        const strengthText = document.getElementById('strengthText');
        const strengthIndicator = document.getElementById('passwordStrength');

        if (password.length === 0) {
            strengthIndicator.style.display = 'none';
            strengthText.textContent = '';
            return;
        }

        strengthIndicator.style.display = 'block';

        let strength = 0;

        // Length check
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;

        // Character variety checks
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;

        // Update requirements
        updateRequirement('req-length', password.length >= 6);
        updateRequirement('req-uppercase', /[A-Z]/.test(password));
        updateRequirement('req-lowercase', /[a-z]/.test(password));
        updateRequirement('req-number', /[0-9]/.test(password));
        updateRequirement('req-special', /[^a-zA-Z0-9]/.test(password));

        // Set strength level
        strengthBar.className = 'password-strength-bar';

        if (strength <= 3) {
            strengthBar.classList.add('strength-weak');
            strengthText.textContent = 'Kekuatan password: Lemah';
            strengthText.style.color = '#f44336'; // var(--danger)
        } else if (strength <= 5) {
            strengthBar.classList.add('strength-medium');
            strengthText.textContent = 'Kekuatan password: Sedang';
            strengthText.style.color = '#ff9800'; // var(--warning)
        } else {
            strengthBar.classList.add('strength-strong');
            strengthText.textContent = 'Kekuatan password: Kuat';
            strengthText.style.color = '#4caf50'; // var(--success)
        }

        checkFormValidity();
    }

    // Update Requirement Status
    function updateRequirement(reqId, met) {
        const req = document.getElementById(reqId);
        const icon = req.querySelector('i');

        if (met) {
            icon.classList.remove('bi-circle', 'requirement-unmet');
            icon.classList.add('bi-check-circle-fill', 'requirement-met');
            req.classList.remove('requirement-unmet');
            req.classList.add('requirement-met');
        } else {
            icon.classList.remove('bi-check-circle-fill', 'requirement-met');
            icon.classList.add('bi-circle', 'requirement-unmet');
            req.classList.remove('requirement-met');
            req.classList.add('requirement-unmet');
        }
    }

    // Check Password Match
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const matchText = document.getElementById('matchText');

        if (confirmation.length === 0) {
            matchText.textContent = '';
            matchText.style.color = '';
            checkFormValidity();
            return;
        }

        if (password === confirmation) {
            matchText.textContent = '✓ Password cocok';
            matchText.style.color = '#4caf50'; // var(--success)
        } else {
            matchText.textContent = '✗ Password tidak cocok';
            matchText.style.color = '#f44336'; // var(--danger)
        }

        checkFormValidity();
    }

    // Check Form Validity
    function checkFormValidity() {
        const currentPassword = document.getElementById('current_password').value;
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const submitBtn = document.getElementById('submitBtn');

        // Check all requirements
        const lengthMet = password.length >= 6;
        const uppercaseMet = /[A-Z]/.test(password);
        const lowercaseMet = /[a-z]/.test(password);
        const numberMet = /[0-9]/.test(password);
        const specialMet = /[^a-zA-Z0-9]/.test(password);
        const passwordsMatch = password === confirmation && password.length > 0;
        const currentPasswordFilled = currentPassword.length > 0;

        const allRequirementsMet = lengthMet && uppercaseMet && lowercaseMet &&
            numberMet && specialMet && passwordsMatch &&
            currentPasswordFilled;

        submitBtn.disabled = !allRequirementsMet;
    }

    // Form submission validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;

        if (password !== confirmation) {
            e.preventDefault();
            alert('Password baru dan konfirmasi password tidak cocok!');
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            alert('Password baru harus minimal 6 karakter!');
            return false;
        }

        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
    });

    // Monitor all password fields
    document.getElementById('current_password').addEventListener('input', checkFormValidity);
    document.getElementById('password').addEventListener('input', function() {
        checkPasswordStrength(this.value);
        checkPasswordMatch();
    });
    document.getElementById('password_confirmation').addEventListener('input', checkPasswordMatch);

</script>
@endpush
