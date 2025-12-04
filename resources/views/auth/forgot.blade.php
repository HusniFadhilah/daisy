@extends('layouts.auth')

@section('title', 'Lupa Password - DAISY')
<div class="auth-wrapper">
    <div class="auth-container fade-in" style="max-width: 600px;">
        <!-- Centered Content -->
        <div class="auth-content" style="flex: 1; text-align: center;">
            <a href="{{ route('login') }}" class="auth-back" style="display: inline-flex; margin: 0 auto 30px;">
                <i class="bi bi-arrow-left"></i>
                Kembali ke Login
            </a>

            <!-- Icon -->
            <div class="mb-4">
                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 10px 30px rgba(147, 33, 54, 0.3);">
                    <i class="bi bi-key" style="font-size: 45px; color: white;"></i>
                </div>
            </div>

            <div class="auth-header">
                <h1>Lupa Password? 🔐</h1>
                <p style="text-align: center;">Jangan khawatir! Masukkan email Anda dan kami akan mengirimkan link untuk reset password</p>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
            <div class="auth-alert success">
                <span class="auth-alert-icon">✓</span>
                <span>{{ session('success') }}</span>
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
            <form class="auth-form" method="POST" action="{{ route('password.email') }}" id="forgotForm" style="text-align: left;">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nama@example.com" required autofocus>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted" style="font-size: 13px; display: block; margin-top: 8px;">
                        <i class="bi bi-info-circle"></i> Kami akan mengirim link reset password ke email ini
                    </small>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary" id="resetBtn">
                    <span>Kirim Link Reset Password</span>
                    <i class="bi bi-send"></i>
                </button>
            </form>

            <!-- Additional Info -->
            <div class="auth-terms" style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <div style="font-size: 14px; color: #666; margin-bottom: 15px;">
                    <i class="bi bi-shield-check" style="font-size: 24px; color: var(--success); display: block; margin-bottom: 10px;"></i>
                    <strong>Link aman dan valid selama 60 menit</strong>
                </div>
                <p style="margin: 0; font-size: 13px;">
                    Pastikan Anda menggunakan email yang terdaftar. Jika tidak menerima email, cek folder spam atau
                    <a href="#" onclick="resendEmail()" style="color: var(--primary);">kirim ulang</a>
                </p>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p>
                    Ingat password Anda?
                    <a href="{{ route('login') }}">Masuk Sekarang</a>
                </p>
                <p style="margin-top: 10px;">
                    Belum punya akun?
                    <a href="{{ route('register') }}">Daftar Sekarang</a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Custom Scripts -->
<script>
    // Form Submit with Loading
    document.getElementById('forgotForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('resetBtn');
        btn.classList.add('btn-loading');
        btn.disabled = true;
    });

    // Resend Email
    function resendEmail() {
        const email = document.getElementById('email').value;
        if (!email) {
            alert('Silakan masukkan email terlebih dahulu!');
            return;
        }
        // Submit form
        document.getElementById('forgotForm').submit();
    }

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

    // Email validation
    document.getElementById('email').addEventListener('input', function(e) {
        const email = e.target.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
            e.target.classList.add('is-invalid');
            e.target.classList.remove('is-valid');
        } else if (email) {
            e.target.classList.remove('is-invalid');
            e.target.classList.add('is-valid');
        } else {
            e.target.classList.remove('is-invalid', 'is-valid');
        }
    });

</script>
@endpush
