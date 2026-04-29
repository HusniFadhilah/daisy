<div class="mb-3">
    <label class="form-label">Nama <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $jenjang->name ?? '') }}" required>
    @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Skor <span class="text-danger">*</span></label>
    <input type="number" name="skor" class="form-control @error('skor') is-invalid @enderror" value="{{ old('skor', $jenjang->skor ?? '') }}" min="0" max="4" required>
    @error('skor')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Warna <span class="text-danger">*</span></label>
    <input type="color" name="color" class="form-control form-control-color @error('color') is-invalid @enderror" value="{{ old('color', $jenjang->color ?? '#cccccc') }}" required>
    @error('color')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
