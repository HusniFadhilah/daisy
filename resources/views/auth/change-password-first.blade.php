@extends('layouts.template.app')

@section('title', 'Ganti Password - DAISY')

@section('content')
<div class="container-fluid py-3">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-white text-center py-4">
                    <i class="fas fa-key fa-3x mb-3"></i>
                    <h3 class="mb-0 fw-bold">Ganti Password</h3>
                    <p class="mb-0 small">Untuk keamanan akun, disarankan mengganti password default Anda</p>
                </div>
                <div class="card-body p-4">
                    @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('change.password.first.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-1"></i> Password Lama
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye" id="current_password-icon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Password default: password123</small>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">
                                <i class="fas fa-key me-1"></i> Password Baru
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye" id="new_password-icon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>

                        <div class="mb-4">
                            <label for="new_password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-check-circle me-1"></i> Konfirmasi Password Baru
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password_confirmation" name="new_password_confirmation" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                    <i class="fas fa-eye" id="new_password_confirmation-icon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-2"></i> Ganti Password
                            </button>
                        </div>
                    </form>

                    <div class="text-center">
                        <form action="{{ route('change.password.skip') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted text-decoration-none">
                                <i class="fas fa-times-circle me-1"></i> Lewati, ganti nanti
                            </button>
                        </form>
                    </div>

                    <div class="alert alert-warning mt-4 mb-0" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Tips Keamanan:</strong> Gunakan kombinasi huruf besar, kecil, angka, dan simbol
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

</script>
@endpush
@endsection
