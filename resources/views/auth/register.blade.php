<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - DAISY</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Auth CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container fade-in">
            <!-- Left Side - Branding -->
            <div class="auth-side">
                <div class="auth-logo">
                    <a href="{{ route('home') }}">
                        <img src="{{ asset('assets/images/logo.png') }}" class="auth-logo-brand" alt="DAISY Logo">
                    </a>
                </div>
                <h2>Bergabung dengan DAISY</h2>
                <p>Daftar sekarang dan mulai kelola akreditasi program studi Anda dengan lebih mudah</p>

                <div class="auth-features">
                    <div class="auth-feature">
                        <div class="auth-feature-icon">⚡</div>
                        <div class="auth-feature-text">
                            <h4>Setup Cepat</h4>
                            <p>Akun Anda siap dalam hitungan menit</p>
                        </div>
                    </div>
                    <div class="auth-feature">
                        <div class="auth-feature-icon">🎯</div>
                        <div class="auth-feature-text">
                            <h4>Fitur Lengkap</h4>
                            <p>Akses semua tools yang Anda butuhkan</p>
                        </div>
                    </div>
                    <div class="auth-feature">
                        <div class="auth-feature-icon">👥</div>
                        <div class="auth-feature-text">
                            <h4>Dukungan 24/7</h4>
                            <p>Tim support siap membantu kapan saja</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Register Form -->
            <div class="auth-content slide-in-right">
                <a href="{{ route('login') }}" class="auth-back">
                    <i class="bi bi-arrow-left"></i>
                    Kembali ke Login
                </a>

                <div class="auth-header">
                    <h1>Buat Akun Baru 🚀</h1>
                    <p>Isi data di bawah untuk membuat akun</p>
                </div>

                <!-- Alert Messages -->
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

                <!-- Register Form -->
                <form class="auth-form" method="POST" action="{{ route('register') }}" id="registerForm">
                    @csrf

                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Dr. Eng. Nama Lengkap, ST., MT" required autofocus>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nama@example.com" required>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Institution (Optional) -->
                    <div class="form-group">
                        <label for="institution">Institusi</label>
                        <input type="text" class="form-control @error('institution') is-invalid @enderror" id="institution" name="institution" value="{{ old('institution') }}" placeholder="Nama Universitas/Institusi">
                        @error('institution')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Min. 8 karakter" required onkeyup="checkPasswordStrength()">
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
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <div class="password-input-group">
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ketik ulang password" required onkeyup="checkPasswordMatch()">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="bi bi-eye" id="password_confirmation-icon"></i>
                            </button>
                        </div>
                        <div id="passwordMatchMsg" style="font-size: 13px; margin-top: 5px;"></div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            Saya setuju dengan <a href="#" style="color: var(--primary);">Syarat & Ketentuan</a> dan <a href="#" style="color: var(--primary);">Kebijakan Privasi</a>
                        </label>
                        @error('terms')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-auth-primary" id="registerBtn">
                        <span>Daftar Sekarang</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <!-- Footer -->
                <div class="auth-footer">
                    <p>
                        Sudah punya akun?
                        <a href="{{ route('login') }}">Masuk Sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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

        // Check Password Strength
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthContainer = document.getElementById('passwordStrength');
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            if (password.length === 0) {
                strengthContainer.style.display = 'none';
                return;
            }

            strengthContainer.style.display = 'block';

            let strength = 0;

            // Length check
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;

            // Complexity checks
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            // Remove all strength classes
            strengthBar.classList.remove('weak', 'medium', 'strong');
            strengthText.classList.remove('weak', 'medium', 'strong');

            if (strength <= 2) {
                strengthBar.classList.add('weak');
                strengthText.classList.add('weak');
                strengthText.textContent = 'Lemah - Gunakan kombinasi huruf, angka, dan simbol';
            } else if (strength <= 4) {
                strengthBar.classList.add('medium');
                strengthText.classList.add('medium');
                strengthText.textContent = 'Sedang - Tambahkan karakter khusus untuk lebih kuat';
            } else {
                strengthBar.classList.add('strong');
                strengthText.classList.add('strong');
                strengthText.textContent = 'Kuat - Password sangat aman!';
            }
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

        // Form Submit with Loading
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }

            const btn = document.getElementById('registerBtn');
            btn.classList.add('btn-loading');
            btn.disabled = true;
        });

        // Social Register (placeholder)
        function socialRegister(provider) {
            alert('Pendaftaran dengan ' + provider + ' akan segera tersedia!');
        }

        // Auto-dismiss alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.auth-alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

    </script>
</body>
</html>
