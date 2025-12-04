@extends('layouts.auth')

@section('title', 'Reset Password - DAISY')
<div class="auth-wrapper">
    <div class="auth-container fade-in" style="max-width: 600px;">
        <!-- Centered Content -->
        <div class="auth-content" style="flex: 1;">
            <!-- Icon -->
            <div class="mb-4 text-center">
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 10px 30px rgba(147, 33, 54, 0.3);">
                    <i class="bi bi-shield-lock" style="font-size: 45px; color: white;"></i>
                </div>
            </div>

            <div class="auth-header" style="text-align: center;">
                <h1>Reset Password Anda 🔒</h1>
                <p>Masukkan password baru untuk akun Anda</p>
            </div>

            <!-- Alert Messages -->
            @if(session('status'))
            <div class="auth-alert success">
                <span class="auth-alert-icon">✓</span>
                <span>{{ session('status') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="auth-alert error">
                <span class="auth-alert-icon">✕</span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="auth-alert error">
                <span class="auth-alert-icon">✕</span>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- Reset Password Form -->
            <form class="auth-form" method="POST" action="{{ route('password.update') }}" id="resetForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email (Read-only) -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $email ?? old('email') }}" readonly style="background: #f8f9fa;">
                </div>

                <!-- New Password -->
                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Min. 8 karakter" required autofocus onkeyup="checkPasswordStrength()">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="bi bi-eye" id="password-icon"></i>
                        </button>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="password-strength" id="passwordStrength" style="display: none;">
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>

                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password" required onkeyup="checkPasswordMatch()">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                            <i class="bi bi-eye" id="password_confirmation-icon"></i>
                        </button>
                    </div>
                    <div id="passwordMatchMsg" style="font-size: 13px; margin-top: 5px;"></div>
                </div>

                <!-- Password Requirements -->
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
                    <div style="font-size: 13px; font-weight: 600; color: #666; margin-bottom: 10px;">
                        <i class="bi bi-info-circle"></i> Password harus memenuhi:
                    </div>
                    <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #666; line-height: 1.8;">
                        <li id="req-length">Minimal 8 karakter</li>
                        <li id="req-uppercase">Mengandung huruf besar (A-Z)</li>
                        <li id="req-lowercase">Mengandung huruf kecil (a-z)</li>
                        <li id="req-number">Mengandung angka (0-9)</li>
                        <li id="req-special">Mengandung karakter khusus (!@#$%^&*)</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary" id="resetBtn">
                    <span>Reset Password</span>
                    <i class="bi bi-check-circle"></i>
                </button>
            </form>

            <!-- Footer -->
            <div class="auth-footer">
                <p>
                    Ingat password Anda?
                    <a href="{{ route('login') }}">Masuk Sekarang</a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Custom Scripts -->
<script>
    // Toggle Password Visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');

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

    // Check Password Strength & Requirements
    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthContainer = document.getElementById('passwordStrength');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        if (password.length === 0) {
            strengthContainer.style.display = 'none';
            resetRequirements();
            return;
        }

        strengthContainer.style.display = 'block';

        let strength = 0;

        // Check requirements
        const hasLength = password.length >= 8;
        const hasUppercase = /[A-Z]/.test(password);
        const hasLowercase = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        // Update requirement list
        updateRequirement('req-length', hasLength);
        updateRequirement('req-uppercase', hasUppercase);
        updateRequirement('req-lowercase', hasLowercase);
        updateRequirement('req-number', hasNumber);
        updateRequirement('req-special', hasSpecial);

        // Calculate strength
        if (hasLength) strength++;
        if (hasUppercase && hasLowercase) strength += 2;
        if (hasNumber) strength++;
        if (hasSpecial) strength++;

        // Remove all strength classes
        strengthBar.classList.remove('weak', 'medium', 'strong');
        strengthText.classList.remove('weak', 'medium', 'strong');

        if (strength <= 2) {
            strengthBar.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = 'Lemah';
        } else if (strength <= 4) {
            strengthBar.classList.add('medium');
            strengthText.classList.add('medium');
            strengthText.textContent = 'Sedang';
        } else {
            strengthBar.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = 'Kuat';
        }
    }

    function updateRequirement(id, isMet) {
        const element = document.getElementById(id);
        if (isMet) {
            element.style.color = 'var(--success)';
            element.innerHTML = element.innerHTML.replace(/^/, '✓ ');
            if (!element.innerHTML.startsWith('✓')) {
                element.innerHTML = '✓ ' + element.innerHTML;
            }
        } else {
            element.style.color = '#666';
            element.innerHTML = element.innerHTML.replace('✓ ', '');
        }
    }

    function resetRequirements() {
        ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special'].forEach(id => {
            const element = document.getElementById(id);
            element.style.color = '#666';
            element.innerHTML = element.innerHTML.replace('✓ ', '');
        });
    }

    // Check Password Match
    function checkPasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;
        const matchMsg = document.getElementById('passwordMatchMsg');

        if (confirmPassword.length === 0) {
            matchMsg.textContent = '';
            return;
        }

        if (password === confirmPassword) {
            matchMsg.style.color = 'var(--success)';
            matchMsg.innerHTML = '<i class="bi bi-check-circle"></i> Password cocok';
        } else {
            matchMsg.style.color = 'var(--danger)';
            matchMsg.innerHTML = '<i class="bi bi-x-circle"></i> Password tidak cocok';
        }
    }

    // Form Submit with Validation
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('password_confirmation').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Password dan konfirmasi password tidak cocok!');
            return false;
        }

        // Check minimum requirements
        if (password.length < 8) {
            e.preventDefault();
            alert('Password harus minimal 8 karakter!');
            return false;
        }

        const btn = document.getElementById('resetBtn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.auth-alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 7000);

    // Prevent form resubmission
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

</script>
@endpush
