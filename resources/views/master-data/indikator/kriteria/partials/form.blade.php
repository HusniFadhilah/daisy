<div class="mb-3">
    <label class="form-label">Kode Kriteria <span class="text-danger">*</span></label>
    <input type="text" name="kode_kriteria" class="form-control @error('kode_kriteria') is-invalid @enderror" value="{{ old('kode_kriteria', $kriteria->kode_kriteria ?? '') }}" required>
    @error('kode_kriteria')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
    <input type="text" name="nama_kriteria" class="form-control @error('nama_kriteria') is-invalid @enderror" value="{{ old('nama_kriteria', $kriteria->nama_kriteria ?? '') }}" required>
    @error('nama_kriteria')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Keterangan</label>
    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan', $kriteria->keterangan ?? '') }}</textarea>
    @error('keterangan')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
