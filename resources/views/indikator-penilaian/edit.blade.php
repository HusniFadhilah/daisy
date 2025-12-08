@extends('layouts.template.app')

@section('title', 'Edit Indikator Penilaian - Daisy')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Indikator Penilaian</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('indikator-penilaian.index') }}">Indikator Penilaian</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('indikator-penilaian.update', $indikatorPenilaian->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="elemen_standar_id" class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                    <select class="form-select @error('elemen_standar_id') is-invalid @enderror" 
                            id="elemen_standar_id" 
                            name="elemen_standar_id" 
                            required>
                        <option value="">-- Pilih Elemen Standar --</option>
                        @foreach($elemenStandars as $elemen)
                            <option value="{{ $elemen->id_elemen }}" 
                                {{ old('elemen_standar_id', $indikatorPenilaian->elemen_standar_id) == $elemen->id_elemen ? 'selected' : '' }}>
                                {{ $elemen->kode_elemen }} - {{ $elemen->pernyataan_elemen }}
                            </option>
                        @endforeach
                    </select>
                    @error('elemen_standar_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jenjang_penilaian_id" class="form-label">Jenjang Penilaian <span class="text-danger">*</span></label>
                    <select class="form-select @error('jenjang_penilaian_id') is-invalid @enderror" 
                            id="jenjang_penilaian_id" 
                            name="jenjang_penilaian_id" 
                            required>
                        <option value="">-- Pilih Jenjang Penilaian --</option>
                        @foreach($jenjangPenilaians as $jenjang)
                            <option value="{{ $jenjang->id }}" 
                                {{ old('jenjang_penilaian_id', $indikatorPenilaian->jenjang_penilaian_id) == $jenjang->id ? 'selected' : '' }}>
                                Skor {{ $jenjang->skor }} - {{ $jenjang->nama_jenjang }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenjang_penilaian_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi_penilaian" class="form-label">Deskripsi Penilaian <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('deskripsi_penilaian') is-invalid @enderror" 
                              id="deskripsi_penilaian" 
                              name="deskripsi_penilaian" 
                              rows="4"
                              placeholder="Masukkan deskripsi kriteria penilaian..."
                              required>{{ old('deskripsi_penilaian', $indikatorPenilaian->deskripsi_penilaian) }}</textarea>
                    @error('deskripsi_penilaian')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                              id="keterangan" 
                              name="keterangan" 
                              rows="2"
                              placeholder="Masukkan keterangan tambahan (opsional)">{{ old('keterangan', $indikatorPenilaian->keterangan) }}</textarea>
                    @error('keterangan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('indikator-penilaian.index') }}" class="btn btn-secondary">
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
        $('#elemen_standar_id, #jenjang_penilaian_id').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endpush
@endsection
