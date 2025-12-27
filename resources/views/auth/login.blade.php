@extends('layouts.template.auth')

@section('title', 'Login - DAISY')

@section('content')
<div class="auth-wrapper">
    <div class="auth-container fade-in">
        <!-- Left Side - Branding -->
        <div class="auth-side">
            <div class="auth-logo">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('assets/images/logo.png') }}" class="auth-logo-brand" alt="DAISY Logo">
                </a>
            </div>
            <h2>DAISY</h2>
            <p>DEPILAR Accreditation Information System</p>

            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature-icon">🎓</div>
                    <div class="auth-feature-text">
                        <h4>Sistem Terakreditasi</h4>
                        <p>Kelola akreditasi program studi dengan mudah</p>
                    </div>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">📊</div>
                    <div class="auth-feature-text">
                        <h4>Dashboard Lengkap</h4>
                        <p>Monitor progress dan statistik real-time</p>
                    </div>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature-icon">🔒</div>
                    <div class="auth-feature-text">
                        <h4>Aman & Terpercaya</h4>
                        <p>Data terenkripsi dengan standar keamanan yang memadai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="auth-content slide-in-right">
            <div class="auth-header">
                <h1>Selamat Datang Kembali! 👋</h1>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
            <div class="auth-alert success">
                <span class="auth-alert-icon alert-permanent">✓</span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="auth-alert error">
                <span class="auth-alert-icon alert-permanent">✕</span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="auth-alert error">
                <span class="auth-alert-icon alert-permanent">✕</span>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <!-- Login Form -->
            <form class="auth-form" method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nama@example.com" required autofocus>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <i class="bi bi-eye" id="password-icon"></i>
                        </button>
                    </div>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember & Forgot -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Ingat Saya
                        </label>
                    </div>
                    <a href="{{ route('password.request') }}" style="font-size: 14px; color: var(--primary); text-decoration: none; font-weight: 600;">
                        Lupa Password?
                    </a>
                </div>
                <div class="form-group mt-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <input type="text" class="form-control @error('captcha') is-invalid @enderror" placeholder="Masukkan Captcha" name="captcha" required>
                        </div>
                        <div class="col-4 captcha px-0">
                            <span>{!! captcha_img() !!}</span>
                        </div>
                        <div class="col-2 pl-0">
                            <button type="button" class="btn btn-danger reload" id="reload-captcha" title="Refresh captcha">&#x21bb;</button>
                        </div>
                    </div>
                    @error('captcha')
                    <small class="text-danger" role="alert">
                        {{ $message }}
                    </small>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary" id="loginBtn">
                    <span>Masuk</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

            <!-- Footer -->
            {{-- <div class="auth-footer">
                <p>
                    Belum punya akun?
                    <a href="{{ route('register') }}">Daftar Sekarang</a>
            </p>
        </div> --}}
    </div>
</div>
</div>
@endsection

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

    // Form Submit with Loading
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });

    // Social Login (placeholder)
    function socialLogin(provider) {
        // Implement social login logic
        alert('Social login dengan ' + provider + ' akan segera tersedia!');
    }

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.auth-alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

</script>
@endpush
