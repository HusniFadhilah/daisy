@extends('layouts.template.app')

@section('title', 'Tambah User - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <h2>Tambah Pengguna</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="notification_emails" class="form-label">Email Notifikasi Tambahan</label>
                    <textarea class="form-control @error('notification_emails') is-invalid @enderror" id="notification_emails" name="notification_emails" rows="3" placeholder="email1@example.com&#10;email2@example.com">{{ old('notification_emails') }}</textarea>
                    <small class="text-muted">Pisahkan beberapa email dengan baris baru, koma, titik koma, atau spasi.</small>
                    @error('notification_emails')
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
                        <option value="super_admin" {{ old('role_selected') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="sekretariat" {{ old('role_selected') === 'sekretariat' ? 'selected' : '' }}>Sekretariat</option>
                        <option value="keuangan_lamdepilar" {{ old('role_selected') === 'keuangan_lamdepilar' ? 'selected' : '' }}>Keuangan LAMDEPILAR</option>
                        <option value="asesor" {{ old('role_selected') === 'asesor' ? 'selected' : '' }}>Asesor</option>
                        <option value="asesor_banding" {{ old('role_selected') === 'asesor_banding' ? 'selected' : '' }}>Asesor Banding</option>
                        <option value="validator" {{ old('role_selected') === 'validator' ? 'selected' : '' }}>Validator</option>
                        <option value="admin_prodi" {{ old('role_selected') === 'admin_prodi' ? 'selected' : '' }}>PT/UPPS/PS</option>
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
                            <input class="form-check-input" type="checkbox" name="roles[]" value="sekretariat" id="role_sekretariat" {{ in_array('sekretariat', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_sekretariat">Sekretariat</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="keuangan_lamdepilar" id="role_keuangan_lamdepilar" {{ in_array('keuangan_lamdepilar', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_keuangan_lamdepilar">Keuangan LAMDEPILAR</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="asesor" id="role_asesor" {{ in_array('asesor', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_asesor">Asesor</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="asesor_banding" id="role_asesor_banding" {{ in_array('asesor_banding', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_asesor_banding">Asesor Banding</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="validator" id="role_validator" {{ in_array('validator', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_validator">Validator</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="admin_prodi" id="role_admin_prodi" {{ in_array('admin_prodi', old('roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_admin_prodi">PT/UPPS/PS</label>
                        </div>
                    </div>
                    <small class="text-muted">Role yang bisa di-switch oleh user ini. Kosongkan untuk auto-sync dari assignment</small>
                    @error('roles')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <hr class="my-4">
                <h5 class="mb-3">Informasi Profil (Opsional)</h5>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">No. Telepon</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08123456789" maxlength="20">
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="position" class="form-label">Jabatan</label>
                        <input type="text" class="form-control @error('position') is-invalid @enderror" id="position" name="position" value="{{ old('position') }}" placeholder="Dosen/Kaprodi/Staff">
                        @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="institution" class="form-label">Institusi</label>
                    <input type="text" class="form-control @error('institution') is-invalid @enderror" id="institution" name="institution" value="{{ old('institution') }}" placeholder="Nama institusi">
                    @error('institution')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Alamat</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
                    @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_university" class="form-label">Universitas</label>
                        <select class="form-select @error('id_university') is-invalid @enderror" id="id_university" name="id_university">
                            <option value="">Pilih Universitas</option>
                            @foreach($universities as $univ)
                            <option value="{{ $univ->id }}" {{ old('id_university') == $univ->id ? 'selected' : '' }}>
                                {{ $univ->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_university')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="id_study_programs" class="form-label">Program Studi</label>
                        <select class="form-select @error('id_study_programs') is-invalid @enderror @error('id_study_programs.*') is-invalid @enderror" id="id_study_programs" name="id_study_programs[]" multiple data-no-select2>
                        </select>
                        <small class="text-muted">Khusus role PT/UPPS/PS, pilih satu atau beberapa prodi/PS yang dapat dikelola.</small>
                        @error('id_study_programs')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('id_study_programs.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
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
@push('scripts')
<script>
    $(document).ready(function() {
        $('#id_university').select2({
            theme: 'bootstrap-5'
            , width: '100%'
            , placeholder: 'Pilih Universitas'
            , allowClear: true
        , });

        $('#id_study_programs').select2({
            theme: 'bootstrap-5'
            , width: '100%'
            , placeholder: 'Pilih Program Studi'
            , allowClear: true
            , closeOnSelect: false
            , ajax: {
                url: '{{ route("ajax.prodi.search") }}'
                , dataType: 'json'
                , delay: 250
                , cache: true
                , data: function(params) {
                    return {
                        q: params.term
                        , page: params.page || 1
                        , university_id: $('#id_university').val()
                    , };
                }
                , processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.results
                        , pagination: data.pagination
                    };
                }
            , }
        , });

        $('#id_university').on('change', function() {
            $('#id_study_programs').val(null).trigger('change');
        });
    });

</script>
@endpush
