<div class="mb-3">
    <label class="form-label">Nama Jenis <span class="text-danger">*</span></label>
    <input type="text" name="nama_jenis" class="form-control @error('nama_jenis') is-invalid @enderror" value="{{ old('nama_jenis', $jenisIndikator->nama_jenis ?? '') }}" required>
    @error('nama_jenis')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Keterangan</label>
    <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan', $jenisIndikator->keterangan ?? '') }}</textarea>
    @error('keterangan')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
