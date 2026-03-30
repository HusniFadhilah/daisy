@extends('layouts.template.app')

@section('title', 'Tambah Indikator Penilaian - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Tambah Indikator Penilaian</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('indikator-penilaian.index') }}">Indikator Penilaian</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('indikator-penilaian.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="id_elemen" class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                    <select class="form-select @error('id_elemen') is-invalid @enderror" id="id_elemen" name="id_elemen" required>
                        <option value="">-- Pilih Elemen Standar --</option>
                        @foreach($elemenStandars as $elemen)
                        <option value="{{ $elemen->id }}" {{ old('id_elemen', request('elemen')) == $elemen->id ? 'selected' : '' }}>
                            {{ $elemen->kode_elemen }} - {{ $elemen->pernyataan_elemen }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_elemen')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="id_jenjang_penilaian" class="form-label">Jenjang Penilaian <span class="text-danger">*</span></label>
                    <select class="form-select @error('id_jenjang_penilaian') is-invalid @enderror" id="id_jenjang_penilaian" name="id_jenjang_penilaian" required>
                        <option value="">-- Pilih Jenjang Penilaian --</option>
                        @foreach($jenjangPenilaians as $jenjang)
                        <option value="{{ $jenjang->id }}" {{ old('id_jenjang_penilaian') == $jenjang->id ? 'selected' : '' }}>
                            Skor {{ $jenjang->skor }} - {{ $jenjang->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('id_jenjang_penilaian')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="deskripsi_penilaian" class="form-label">Deskripsi Penilaian <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('deskripsi_penilaian') is-invalid @enderror" id="deskripsi_penilaian" name="deskripsi_penilaian" rows="4" placeholder="Masukkan deskripsi kriteria penilaian..." required>{{ old('deskripsi_penilaian') }}</textarea>
                    @error('deskripsi_penilaian')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="2" placeholder="Masukkan keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <a href="{{ route('indikator-penilaian.index') }}" class="btn btn-secondary">
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
        $('#id_elemen, #id_jenjang_penilaian').select2({
            theme: 'bootstrap-5'
            , width: '100%'
        });
    });

</script>
@endpush
@endsection
