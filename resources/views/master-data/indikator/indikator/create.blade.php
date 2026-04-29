@extends('layouts.template.app')

@section('title', 'Tambah Indikator')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Tambah Indikator</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('indikator.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Kode Indikator <span class="text-danger">*</span></label>
                    <input type="text" name="kode_indikator" class="form-control @error('kode_indikator') is-invalid @enderror" value="{{ old('kode_indikator') }}" required>
                    @error('kode_indikator')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi Indikator <span class="text-danger">*</span></label>
                    <textarea name="deskripsi_indikator" class="form-control @error('deskripsi_indikator') is-invalid @enderror" rows="4" required>{{ old('deskripsi_indikator') }}</textarea>
                    @error('deskripsi_indikator')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                    <select name="id_elemen" class="form-select @error('id_elemen') is-invalid @enderror" required>
                        <option value="">Pilih Elemen Standar</option>
                        @foreach($elemenStandar as $elemen)
                        <option value="{{ $elemen->id }}" {{ old('id_elemen') == $elemen->id ? 'selected' : '' }}>
                            {{ $elemen->kode_elemen }} - {{ $elemen->kriteria->kode_kriteria ?? '-' }} - {{ \Illuminate\Support\Str::limit($elemen->pernyataan_elemen, 60) }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_elemen')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Indikator <span class="text-danger">*</span></label>
                    <select name="id_jenis" class="form-select @error('id_jenis') is-invalid @enderror" required>
                        <option value="">Pilih Jenis Indikator</option>
                        @foreach($jenisIndikator as $jenis)
                        <option value="{{ $jenis->id }}" {{ old('id_jenis') == $jenis->id ? 'selected' : '' }}>
                            {{ $jenis->nama_jenis }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_jenis')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('indikator.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
