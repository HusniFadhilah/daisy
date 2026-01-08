@extends('layouts.template.app')

@section('title', isset($pengajuan) ? 'Lengkapi Pengajuan Akreditasi' : 'Ajukan Akreditasi Baru')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="mb-4">
        <h2>
            <i class="bi bi-file-earmark-plus"></i>
            {{ isset($pengajuan) ? 'Lengkapi Pengajuan Akreditasi' : 'Ajukan Akreditasi Baru' }}
        </h2>
        <p class="text-muted">
            {{ isset($pengajuan) ? 'Lengkapi data pengajuan yang telah dibuat oleh DE' : 'Lengkapi formulir di bawah untuk mengajukan permohonan akreditasi' }}
        </p>
    </div>

    @if(isset($pengajuan))
    <div class="alert alert-info alert-permanent">
        <i class="bi bi-info-circle"></i>
        <strong>Informasi:</strong> Pengajuan ini telah dibuat oleh <strong>{{ $pengajuan->deskEvaluator->name }}</strong>
        pada {{ $pengajuan->tanggal_pengingat->format('d F Y') }}.
        Silakan lengkapi data di bawah untuk melanjutkan proses akreditasi.
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i>
                        Formulir Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- ✅ Hidden field untuk pengajuan_id jika update --}}
                        @if(isset($pengajuan))
                        <input type="hidden" name="pengajuan_id" value="{{ $pengajuan->id }}">

                        {{-- Show existing nomor pengajuan --}}
                        <div class="alert alert-secondary alert-permanent">
                            <strong>Nomor Pengajuan:</strong> {{ $pengajuan->nomor_pengajuan }}
                        </div>
                        @endif

                        <!-- Program Studi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Program Studi <span class="text-danger">*</span>
                            </label>
                            <select name="id_program_studi" class="form-select @error('id_program_studi') is-invalid @enderror" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @if($prodiUser)
                                <option value="{{ $prodiUser->id }}" selected>{{ $prodiUser->full_name }}</option>
                                @else
                                @foreach ($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ ((request('study_program_id') ?? $pengajuan->id_program_studi ?? old('id_program_studi')) == $prodi->id) ? 'selected' : '' }}>
                                    {{ $prodi->full_name }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                            @error('id_program_studi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(isset($pengajuan))
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Prodi ini dipilih oleh DE. Anda bisa mengubahnya jika tidak sesuai.
                            </small>
                            @endif
                        </div>

                        <!-- Tahun Akreditasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Tahun Akreditasi <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="tahun_akreditasi" class="form-control @error('tahun_akreditasi') is-invalid @enderror" value="{{ isset($pengajuan) ? $pengajuan->tahun_akreditasi : old('tahun_akreditasi', date('Y')) }}" min="2024" required>
                            @error('tahun_akreditasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jenis Akreditasi -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Jenis Akreditasi <span class="text-danger">*</span>
                            </label>
                            <select name="jenis_akreditasi" class="form-select @error('jenis_akreditasi') is-invalid @enderror" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="baru" {{ old('jenis_akreditasi') == 'baru' ? 'selected' : '' }}>
                                    Akreditasi Baru
                                </option>
                                <option value="perpanjangan" {{ old('jenis_akreditasi') == 'perpanjangan' ? 'selected' : '' }}>
                                    Perpanjangan
                                </option>
                                <option value="re-akreditasi" {{ old('jenis_akreditasi') == 're-akreditasi' ? 'selected' : '' }}>
                                    Re-Akreditasi
                                </option>
                            </select>
                            @error('jenis_akreditasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Surat Permohonan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Surat Permohonan (PDF) <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="surat_permohonan" class="form-control @error('surat_permohonan') is-invalid @enderror" accept=".pdf" required>
                            <small class="text-muted">
                                Format: PDF | Maksimal: 5 MB
                            </small>
                            @error('surat_permohonan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Catatan -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Catatan/Keterangan Tambahan
                            </label>
                            <textarea name="catatan_pengaju" class="form-control @error('catatan_pengaju') is-invalid @enderror" rows="4" placeholder="Masukkan catatan atau keterangan tambahan jika ada...">{{ old('catatan_pengaju') }}</textarea>
                            @error('catatan_pengaju')
                            <span class="invalid-feedback" role="alert">
                                {{ $message }}
                            </span>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('pengajuan') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i>
                                {{ isset($pengajuan) ? 'Lengkapi & Submit' : 'Submit Permohonan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Alur Pengajuan Akreditasi</h6>
                    <ol class="ps-3">
                        <li class="mb-2">Submit surat permohonan oleh prodi</li>
                        <li class="mb-2">DE LAMDEPILAR mengirim form borang</li>
                        <li class="mb-2">Upload draft LED oleh prodi</li>
                        <li class="mb-2">DE LAMDEPILAR melakukan review kesiapan</li>
                        <li class="mb-2">Jika dinyatakan siap: lakukan pembayaran</li>
                        <li class="mb-2">Upload borang final oleh prodi</li>
                        <li class="mb-2">DE LAMDEPILAR menyatakan Lanjut ke tahap Asesmen Kecukupan (AK)</li>
                    </ol>

                    <hr>

                    <h6 class="fw-bold">Persyaratan Dokumen</h6>
                    <ul class="ps-3">
                        <li>Surat permohonan resmi (PDF)</li>
                        <li>Format surat sesuai template</li>
                        <li>Ditandatangani oleh pejabat berwenang</li>
                    </ul>

                    <hr>

                    <div class="alert alert-warning alert-permanent mb-0">
                        <small>
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Perhatian:</strong> Pastikan semua data yang diisi sudah benar
                            sebelum submit permohonan.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
