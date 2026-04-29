<div class="mb-3">
    <label class="form-label">Kriteria <span class="text-danger">*</span></label>
    <select name="id_kriteria" class="form-select @error('id_kriteria') is-invalid @enderror" required>
        <option value="">Pilih Kriteria</option>
        @foreach($kriteria as $k)
        <option value="{{ $k->id }}" {{ old('id_kriteria', $elemenStandar->id_kriteria ?? '') == $k->id ? 'selected' : '' }}>
            {{ $k->kode_kriteria }} - {{ $k->nama_kriteria }}
        </option>
        @endforeach
    </select>
    @error('id_kriteria')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Kode Elemen <span class="text-danger">*</span></label>
    <input type="text" name="kode_elemen" class="form-control @error('kode_elemen') is-invalid @enderror" value="{{ old('kode_elemen', $elemenStandar->kode_elemen ?? '') }}" required>
    @error('kode_elemen')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Pernyataan Elemen <span class="text-danger">*</span></label>
    <textarea name="pernyataan_elemen" class="form-control @error('pernyataan_elemen') is-invalid @enderror" rows="4" required>{{ old('pernyataan_elemen', $elemenStandar->pernyataan_elemen ?? '') }}</textarea>
    @error('pernyataan_elemen')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Keterangan</label>
    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan', $elemenStandar->keterangan ?? '') }}</textarea>
    @error('keterangan')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
