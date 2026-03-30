@extends('layouts.template.app')

@section('title', 'Edit Program Studi - Daisy LAMDEPILAR')

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
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $studyProgram->code) }}" placeholder="Contoh: 12345" required>
                    @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Program Studi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $studyProgram->name) }}" placeholder="Contoh: Teknik Informatika" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_university" class="form-label">Universitas <span class="text-danger">*</span></label>
                    <select class="form-select @error('id_university') is-invalid @enderror" id="id_university" name="id_university" required>
                        <option value="">-- Pilih Universitas --</option>
                        @foreach($universities as $university)
                        <option value="{{ $university->id }}" {{ old('id_university', $studyProgram->id_university) == $university->id ? 'selected' : '' }}>
                            {{ $university->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_university')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_degree_level" class="form-label">Jenjang <span class="text-danger">*</span></label>
                    <select class="form-select @error('id_degree_level') is-invalid @enderror" id="id_degree_level" name="id_degree_level" required>
                        <option value="">-- Pilih Jenjang --</option>
                        @foreach($degreeLevels as $level)
                        <option value="{{ $level->id }}" {{ old('id_degree_level', $studyProgram->id_degree_level) == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_degree_level')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bentuk_pt" class="form-label">Bentuk Perguruan Tinggi</label>
                    <select class="form-select @error('bentuk_pt') is-invalid @enderror" id="bentuk_pt" name="bentuk_pt">
                        <option value="">-- Pilih Bentuk PT --</option>
                        <option value="Universitas" {{ old('bentuk_pt', $studyProgram->bentuk_pt) == 'Universitas' ? 'selected' : '' }}>Universitas</option>
                        <option value="Institut" {{ old('bentuk_pt', $studyProgram->bentuk_pt) == 'Institut' ? 'selected' : '' }}>Institut</option>
                        <option value="Sekolah Tinggi" {{ old('bentuk_pt', $studyProgram->bentuk_pt) == 'Sekolah Tinggi' ? 'selected' : '' }}>Sekolah Tinggi</option>
                        <option value="Politeknik" {{ old('bentuk_pt', $studyProgram->bentuk_pt) == 'Politeknik' ? 'selected' : '' }}>Politeknik</option>
                        <option value="Akademi" {{ old('bentuk_pt', $studyProgram->bentuk_pt) == 'Akademi' ? 'selected' : '' }}>Akademi</option>
                    </select>
                    @error('bentuk_pt')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $studyProgram->email) }}" placeholder="Contoh: prodi@university.ac.id">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="peringkat_akreditasi" class="form-label">Status Akreditasi</label>
                    <input type="text" class="form-control @error('peringkat_akreditasi') is-invalid @enderror" id="peringkat_akreditasi" name="peringkat_akreditasi" value="{{ old('peringkat_akreditasi', $studyProgram->peringkat_akreditasi) }}" placeholder="Contoh: Unggul, Baik Sekali, A, B, dll">
                    @error('peringkat_akreditasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tanggal_kedaluwarsa" class="form-label">Tanggal Kedaluwarsa</label>
                    <input type="date" class="form-control @error('tanggal_kedaluwarsa') is-invalid @enderror" id="tanggal_kedaluwarsa" name="tanggal_kedaluwarsa" value="{{ old('tanggal_kedaluwarsa', $studyProgram->tanggal_kedaluwarsa ? $studyProgram->tanggal_kedaluwarsa->format('Y-m-d') : '') }}">
                    @error('tanggal_kedaluwarsa')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status_kedaluwarsa" class="form-label">Status Kedaluwarsa</label>
                    <select class="form-select @error('status_kedaluwarsa') is-invalid @enderror" id="status_kedaluwarsa" name="status_kedaluwarsa">
                        <option value="Belum Terakreditasi" {{ old('status_kedaluwarsa', $studyProgram->status_kedaluwarsa) == 'Belum Terakreditasi' ? 'selected' : '' }}>Belum Terakreditasi</option>
                        <option value="Aktif" {{ old('status_kedaluwarsa', $studyProgram->status_kedaluwarsa) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Kedaluwarsa" {{ old('status_kedaluwarsa', $studyProgram->status_kedaluwarsa) == 'Kedaluwarsa' ? 'selected' : '' }}>Kedaluwarsa</option>
                    </select>
                    @error('status_kedaluwarsa')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                    <a href="{{ route('master-data.index', ['tab' => 'study-programs']) }}" class="btn btn-secondary">
                        <i class="bi bi-x"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#id_university, #id_degree_level').select2({
            theme: 'bootstrap-5'
            , width: '100%'
        });
    });

</script>
@endpush
@endsection
