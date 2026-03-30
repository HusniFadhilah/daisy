@extends('layouts.template.app')

@section('title', 'Detail User - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <h2>Detail Pengguna</h2>
        <div>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <p class="form-control-plaintext">{{ $user->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <p class="form-control-plaintext">{{ $user->email }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Role</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }} fs-6">
                                {{ ucfirst($user->role) }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Email</label>
                        <p class="form-control-plaintext">
                            @if($user->email_verified_at)
                            <span class="badge bg-success fs-6">Terverifikasi</span>
                            <br>
                            <small class="text-muted">{{ $user->email_verified_at->locale('id')->translatedFormat('d M Y H:i') }}</small>
                            @else
                            <span class="badge bg-warning fs-6">Belum Terverifikasi</span>
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Terdaftar Sejak</label>
                        <p class="form-control-plaintext">{{ $user->created_at->locale('id')->translatedFormat('d F Y H:i') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Terakhir Diperbarui</label>
                        <p class="form-control-plaintext">{{ $user->updated_at->locale('id')->translatedFormat('d F Y H:i') }}</p>
                    </div>
                </div>
            </div>

            @if($user->id !== auth()->id())
            <hr>
            <div class="d-flex justify-content-end">
                <form id="form-hapus-user" action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-danger tombol-hapus" data-id-form="form-hapus-user" data-text="pengguna">
                        <i class="bi bi-trash"></i> Hapus Pengguna
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
