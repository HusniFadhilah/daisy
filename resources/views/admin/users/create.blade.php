@extends('layouts.template.app')

@section('title', 'Tambah User - Daisy')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Pengguna</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role Dasar <span class="text-danger">*</span></label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        <option value="">Pilih Role</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="role_selected" class="form-label">Role Aktif (Default Login) <span class="text-danger">*</span></label>
                    <select class="form-select @error('role_selected') is-invalid @enderror" id="role_selected" name="role_selected" required>
                        <option value="">Pilih Role Aktif</option>
                        <option value="default" {{ old('role_selected') === 'default' ? 'selected' : '' }}>Default User</option>
                        <option value="super_admin" {{ old('role_selected') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="asesi" {{ old('role_selected') === 'asesi' ? 'selected' : '' }}>DE (Asesi)</option>
                        <option value="asesor" {{ old('role_selected') === 'asesor' ? 'selected' : '' }}>Asesor</option>
                        <option value="validator" {{ old('role_selected') === 'validator' ? 'selected' : '' }}>Validator</option>
                        <option value="verifikator" {{ old('role_selected') === 'verifikator' ? 'selected' : '' }}>Verifikator</option>
                        <option value="admin_univ" {{ old('role_selected') === 'admin_univ' ? 'selected' : '' }}>PT</option>
                        <option value="admin_prodi" {{ old('role_selected') === 'admin_prodi' ? 'selected' : '' }}>PS/UPPS/PT</option>
                    </select>
                    <small class="text-muted">Role yang akan aktif saat user pertama kali login</small>
                    @error('role_selected')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Semua Roles (Multiple)</label>
                    <div class="border rounded p-3 bg-light">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="super_admin" id="role_super_admin" {{ in_array('super_admin', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_super_admin">Super Admin</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="asesi" id="role_asesi" {{ in_array('asesi', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_asesi">DE (Asesi)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="asesor" id="role_asesor" {{ in_array('asesor', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_asesor">Asesor</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="validator" id="role_validator" {{ in_array('validator', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_validator">Validator</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="verifikator" id="role_verifikator" {{ in_array('verifikator', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_verifikator">Verifikator</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="admin_univ" id="role_admin_univ" {{ in_array('admin_univ', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_admin_univ">PT</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="admin_prodi" id="role_admin_prodi" {{ in_array('admin_prodi', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_admin_prodi">PS/UPPS/PT</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="default" id="role_default" {{ in_array('default', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_default">Default User</label>
                        </div>
                    </div>
                    <small class="text-muted">Role yang bisa di-switch oleh user ini. Kosongkan untuk auto-sync dari assignment</small>
                    @error('roles')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control"
                           id="password_confirmation" name="password_confirmation" required>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
