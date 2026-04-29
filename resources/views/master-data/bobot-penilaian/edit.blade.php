@extends('layouts.template.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Bobot Penilaian</h1>
            <p class="text-muted">Edit bobot penilaian untuk elemen standar</p>
        </div>
        <a href="{{ route('bobot-penilaian.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('bobot-penilaian.update', $bobot->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                    <select name="id_elemen" class="form-select @error('id_elemen') is-invalid @enderror" required>
                        <option value="">-- Pilih Elemen --</option>
                        @foreach($elemens as $elemen)
                        <option value="{{ $elemen->id }}" {{ $bobot->id_elemen == $elemen->id ? 'selected' : '' }}>
                            {{ $elemen->kriteria->kode_kriteria ?? '' }}.{{ $elemen->kode_elemen }} - {{ $elemen->pernyataan_elemen }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_elemen')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="id_category" class="form-select @error('id_category') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $bobot->id_category == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_category')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenjang <span class="text-danger">*</span></label>
                    <select name="id_degree_level" class="form-select @error('id_degree_level') is-invalid @enderror" required>
                        <option value="">-- Pilih Jenjang --</option>
                        @foreach($degreeLevels as $level)
                        <option value="{{ $level->id }}" {{ old('id_degree_level', $bobot->id_degree_level) == $level->id ? 'selected' : '' }}>
                            {{ $level->name ?? $level->code }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_degree_level')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Bobot <span class="text-danger">*</span></label>
                    <input type="number" name="bobot" class="form-control @error('bobot') is-invalid @enderror" value="{{ old('bobot', $bobot->bobot) }}" min="0" max="100" required>
                    <small class="text-muted">Bobot dalam skala 0-100</small>
                    @error('bobot')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $bobot->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">
                            Aktif
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update
                    </button>
                    <a href="{{ route('bobot-penilaian.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('select[name="id_elemen"], select[name="id_category"], select[name="id_degree_level"]').select2({
            theme: 'bootstrap-5'
            , width: '100%'
        });
    });

</script>
@endpush
