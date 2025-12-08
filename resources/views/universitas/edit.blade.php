@extends('layouts.template.app')

@section('title', 'Edit Universitas - Daisy')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Universitas</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master-data.index', ['tab' => 'universities']) }}">Univ & Prodi</a></li>
                <li class="breadcrumb-item active">Edit Universitas</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('universities.update', $university->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="code" class="form-label">Kode Universitas <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('code') is-invalid @enderror" 
                           id="code" 
                           name="code" 
                           value="{{ old('code', $university->code) }}"
                           placeholder="Contoh: UGM"
                           required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Universitas <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $university->name) }}"
                           placeholder="Contoh: Universitas Gadjah Mada"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('master-data.index', ['tab' => 'universities']) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
