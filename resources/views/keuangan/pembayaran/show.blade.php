@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-credit-card"></i>
                Validasi Pembayaran
            </h2>
            <p class="text-muted mb-0">
                {{ $pengajuan->nomor_pengajuan }} — {{ optional($pengajuan->studyProgram)->name ?? '-' }} ({{ $pengajuan->tahun_akreditasi }})
            </p>
        </div>

        <div>
            <span class="badge bg-secondary">
                Status Pembayaran: {{ strtoupper(optional($pengajuan->pembayaran)->status_pembayaran ?? '-') }}
            </span>
        </div>
    </div>

    @php
    $pembayaran = $pengajuan->pembayaran;
    // ✅ Samakan dengan field di DB Anda
    $path = optional($pembayaran)->bukti_path;
    $ext = $path ? strtolower(pathinfo($path, PATHINFO_EXTENSION)) : null;
    $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
    @endphp

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Informasi Invoice</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-permanent">
                        <strong>Invoice:</strong> {{ optional($pembayaran)->nomor_invoice ?? '-' }}<br>
                        <strong>Jumlah:</strong> Rp {{ number_format(optional($pembayaran)->jumlah_pembayaran ?? 0, 0, ',', '.') }}<br>
                        <strong>Tanggal Pembayaran:</strong>
                        @if(optional($pembayaran)->tanggal_pembayaran)
                        {{ \App\Libraries\Date::tglIndo($pembayaran->tanggal_pembayaran) }}
                        @else
                        -
                        @endif
                    </div>

                    @if(optional($pembayaran)->catatan_verifikasi)
                    <div class="mb-2">
                        <label class="text-muted small">Catatan Verifikasi (terakhir)</label>
                        <p class="mb-0">{{ $pembayaran->catatan_verifikasi }}</p>
                    </div>
                    @endif

                    @if(optional($pembayaran)->alasan_penolakan)
                    <div class="mb-2">
                        <label class="text-muted small text-danger">Alasan Penolakan</label>
                        <p class="mb-0 text-danger">{{ $pembayaran->alasan_penolakan }}</p>
                    </div>
                    @endif

                    @if(optional($pembayaran)->verified_by)
                    <div class="mt-3 small text-muted">
                        Diverifikasi oleh: {{ optional(optional($pembayaran)->verifier)->name ?? '-' }}
                    </div>
                    @endif
                </div>
            </div>

            @if((optional($pembayaran)->status_pembayaran ?? null) === 'dibayar')
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Aksi Verifikasi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('keuangan.pembayaran.verify', $pengajuan->id) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Verifikasi <span class="text-danger">*</span></label>
                            <textarea name="catatan_verifikasi" class="form-control @error('catatan_verifikasi') is-invalid @enderror" rows="3" required>{{ old('catatan_verifikasi') }}</textarea>
                            @error('catatan_verifikasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Alasan Penolakan (wajib jika ditolak)</label>
                            <textarea name="alasan_penolakan" class="form-control @error('alasan_penolakan') is-invalid @enderror" rows="2">{{ old('alasan_penolakan') }}</textarea>
                            @error('alasan_penolakan')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="status_pembayaran" value="terverifikasi" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Verifikasi & Setujui
                            </button>
                            <button type="submit" name="status_pembayaran" value="ditolak" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Tolak Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-exclamation-triangle"></i>
                Pembayaran tidak berada pada status <strong>dibayar</strong>, sehingga tidak dapat diproses di halaman ini.
            </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-image"></i> Bukti Pembayaran</h5>

                    {{-- @if($path)
                    <a href="{{ route('keuangan.pembayaran.download-bukti', $pengajuan->id) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-download"></i> Download
                    </a>
                    @endif --}}
                </div>

                <div class="card-body">
                    @if(!$path)
                    <p class="text-muted mb-0">Bukti pembayaran belum diupload.</p>
                    @else
                    @if(in_array($ext, ['jpg','jpeg','png']))
                    <img src="{{ $url }}" class="img-fluid rounded border" alt="Bukti Pembayaran">
                    {{-- @elseif($ext === 'pdf')
                    <div class="ratio ratio-4x3">
                        <iframe src="{{ $url }}" class="border rounded"></iframe>
                </div> --}}
                @else
                {{-- <p class="text-muted mb-2">Format file tidak didukung untuk preview.</p> --}}
                @endif
                Berikut ini adalah file bukti pembayarannya:
                <a href="{{ route('keuangan.pembayaran.download-bukti', $pengajuan->id) }}" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-download"></i> Download Bukti
                </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Info Prodi</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="text-muted small">Universitas</label>
                    <p class="fw-bold mb-0">{{ optional(optional($pengajuan->studyProgram)->university)->name ?? '-' }}</p>
                </div>
                <div class="mb-2">
                    <label class="text-muted small">Program Studi</label>
                    <p class="fw-bold mb-0">{{ optional($pengajuan->studyProgram)->name ?? '-' }}</p>
                </div>
                <div class="mb-0">
                    <label class="text-muted small">Jenjang</label>
                    <p class="fw-bold mb-0">{{ optional(optional($pengajuan->studyProgram)->degreeLevel)->name ?? '-' }}</p>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
@endsection
