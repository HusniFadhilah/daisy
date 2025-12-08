@extends('layouts.template.app')

@section('title', 'Edit Program Studi - Daisy')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Program Studi</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master-data.index', ['tab' => 'study-programs']) }}">Univ & Prodi</a></li>
                <li class="breadcrumb-item active">Edit Program Studi</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('study-programs.update', $studyProgram->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="code" class="form-label">Kode Program Studi <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('code') is-invalid @enderror" 
                           id="code" 
                           name="code" 
                           value="{{ old('code', $studyProgram->code) }}"
                           placeholder="Contoh: 12345"
                           required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Program Studi <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $studyProgram->name) }}"
                           placeholder="Contoh: Teknik Informatika"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_univ" class="form-label">Universitas <span class="text-danger">*</span></label>
                    <select class="form-select @error('id_univ') is-invalid @enderror" 
                            id="id_univ" 
                            name="id_univ" 
                            required>
                        <option value="">-- Pilih Universitas --</option>
                        @foreach($universities as $university)
                            <option value="{{ $university->id }}" 
                                {{ old('id_univ', $studyProgram->id_univ) == $university->id ? 'selected' : '' }}>
                                {{ $university->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_univ')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_level" class="form-label">Jenjang <span class="text-danger">*</span></label>
                    <select class="form-select @error('id_level') is-invalid @enderror" 
                            id="id_level" 
                            name="id_level" 
                            required>
                        <option value="">-- Pilih Jenjang --</option>
                        @foreach($degreeLevels as $level)
                            <option value="{{ $level->id }}" 
                                {{ old('id_level', $studyProgram->id_level) == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_level')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $studyProgram->email) }}"
                           placeholder="Contoh: prodi@university.ac.id">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('master-data.index', ['tab' => 'study-programs']) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#id_univ, #id_level').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endpush
@endsection
