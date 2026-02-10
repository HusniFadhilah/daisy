{{-- resources/views/de/pelaksanaan-banding/show.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Detail Pelaksanaan Banding')

@push('styles')
<style>
    .info-card {
        border-left: 4px solid #667eea;
        border-radius: 8px;
    }

    .result-card {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .timeline-item {
        position: relative;
        padding-left: 30px;
        padding-bottom: 20px;
        border-left: 2px solid #e9ecef;
    }

    .timeline-item:last-child {
        border-left: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -9px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.pelaksanaan-banding') }}">Pelaksanaan Banding</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-arrow-repeat"></i> Detail Pelaksanaan Banding
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            <a href="{{ route('de.pelaksanaan-banding') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Info -->
        <div class="col-lg-8 mb-4">
            <!-- Basic Info -->
            <div class="card info-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Permohonan Akreditasi</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nomor Permohonan Akreditasi</label>
                            <p class="fw-bold">{{ $pengajuan->nomor_pengajuan }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tahun Akreditasi</label>
                            <p class="fw-bold">{{ $pengajuan->tahun_akreditasi }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Program Studi</label>
                            <p class="fw-bold">{{ $pengajuan->studyProgram->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Universitas</label>
                            <p class="fw-bold">{{ $pengajuan->studyProgram->university->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Banding Diajukan</label>
                            <p class="fw-bold">
                                {{ $pengajuan->tanggal_permohonan_banding ? $pengajuan->tanggal_permohonan_banding->format('d F Y') : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status Saat Ini</label>
                            <p>
                                <span class="badge bg-{{ $pengajuan->status_badge_class }}">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alasan Banding -->
            <div class="card info-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-chat-left-text"></i> Alasan Banding</h5>
                </div>
                <div class="card-body">
                    @if($pengajuan->alasan_banding)
                    <p class="mb-0">{{ $pengajuan->alasan_banding }}</p>
                    @else
                    <p class="text-muted mb-0">Tidak ada alasan banding yang tercatat.</p>
                    @endif
                </div>
            </div>

            <!-- Hasil Awal Akreditasi -->
            @if($pengajuan->asesmen && $pengajuan->asesmen->hasil)
            <div class="card result-card mb-4">
                <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-award"></i> Hasil Akreditasi Awal (Sebelum Banding)
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $hasil = $pengajuan->asesmen->hasil;
                    @endphp
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-2">Peringkat</h6>
                                @php
                                $peringkat = $hasil->peringkat_akreditasi ?? '-';
                                $badgeClass = match($peringkat) {
                                'Unggul' => 'success',
                                'Baik Sekali' => 'primary',
                                'Baik' => 'info',
                                default => 'secondary'
                                };
                                @endphp
                                <h4 class="mb-0">
                                    <span class="badge bg-{{ $badgeClass }}">{{ $peringkat }}</span>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-2">Skor Final</h6>
                                <h4 class="mb-0 fw-bold">{{ number_format($hasil->skor_final ?? 0, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-2">Skor AK</h6>
                                <p class="mb-0">{{ number_format($hasil->skor_ak ?? 0, 2) }}</p>
                                <h6 class="text-muted small mb-2 mt-2">Skor AL</h6>
                                <p class="mb-0">{{ number_format($hasil->skor_al ?? 0, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Form Hasil Banding (hanya muncul jika status = BANDING_DILAKSANAKAN) -->
            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN && !$pengajuan->hasil_banding)
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Tentukan Hasil Banding</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('de.pelaksanaan-banding.selesaikan', $pengajuan->id) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Hasil Banding <span class="text-danger">*</span></label>
                            <select name="hasil_banding" class="form-select @error('hasil_banding') is-invalid @enderror" required>
                                <option value="">-- Pilih Hasil --</option>
                                <option value="diterima">Diterima - Revisi Hasil Akreditasi</option>
                                <option value="ditolak">Ditolak - Hasil Tetap</option>
                            </select>
                            @error('hasil_banding')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="revisiFields" style="display: none;">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Masukkan hasil akreditasi yang baru setelah banding diterima.
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Peringkat Akreditasi Baru <span class="text-danger">*</span></label>
                                    <select name="peringkat_final" class="form-select @error('peringkat_final') is-invalid @enderror">
                                        <option value="">-- Pilih Peringkat --</option>
                                        <option value="Unggul">Unggul</option>
                                        <option value="Baik Sekali">Baik Sekali</option>
                                        <option value="Baik">Baik</option>
                                        <option value="Tidak Terakreditasi">Tidak Terakreditasi</option>
                                    </select>
                                    @error('peringkat_final')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Skor Final Baru <span class="text-danger">*</span></label>
                                    <input type="number" name="skor_final" class="form-control @error('skor_final') is-invalid @enderror" step="0.01" min="0" max="400" placeholder="0.00">
                                    @error('skor_final')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Hasil</label>
                            <textarea name="catatan_hasil" class="form-control @error('catatan_hasil') is-invalid @enderror" rows="4" placeholder="Penjelasan atau justifikasi hasil banding..."></textarea>
                            @error('catatan_hasil')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Simpan Hasil Banding
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Hasil Banding (jika sudah ada) -->
            @if($pengajuan->hasil_banding)
            <div class="card result-card">
                <div class="card-header bg-{{ $pengajuan->hasil_banding === 'diterima' ? 'success' : 'danger' }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $pengajuan->hasil_banding === 'diterima' ? 'check-circle' : 'x-circle' }}"></i>
                        Hasil Banding: {{ ucfirst($pengajuan->hasil_banding) }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($pengajuan->hasil_banding === 'diterima')
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle"></i>
                        Banding diterima. Hasil akreditasi telah direvisi.
                    </div>

                    <div class="row text-center mb-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-2">Peringkat Baru</h6>
                                <h4 class="mb-0">
                                    <span class="badge bg-success">{{ $pengajuan->peringkat_final ?? '-' }}</span>
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-2">Skor Baru</h6>
                                <h4 class="mb-0 fw-bold">{{ number_format($pengajuan->skor_final ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-danger">
                        <i class="bi bi-info-circle"></i>
                        Banding ditolak. Hasil akreditasi tetap sesuai penilaian awal.
                    </div>
                    @endif

                    @if($pengajuan->catatan_hasil)
                    <hr>
                    <h6 class="mb-2">Catatan:</h6>
                    <p class="mb-0">{{ $pengajuan->catatan_hasil }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Timeline & Documents -->
        <div class="col-lg-4">
            <!-- Status Timeline -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline Status</h5>
                </div>
                <div class="card-body">
                    @forelse($pengajuan->statusLog->sortBy('changed_at')->take(10) as $log)
                    <div class="timeline-item">
                        <div class="timeline-dot bg-{{
                                in_array($log->status_to, [
                                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN
                                ]) ? 'success' : 'primary'
                            }} border-{{
                                in_array($log->status_to, [
                                    \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN
                                ]) ? 'success' : 'primary'
                            }}"></div>
                        <div class="mb-2">
                            <strong class="d-block">
                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                            </strong>
                            <small class="text-muted">
                                {{ $log->changed_at->format('d M Y H:i') }}
                            </small>
                        </div>
                        @if($log->keterangan)
                        <p class="text-muted small mb-0">{{ $log->keterangan }}</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted mb-0">Tidak ada riwayat status.</p>
                    @endforelse
                </div>
            </div>

            <!-- Documents -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Dokumen</h5>
                </div>
                <div class="card-body">
                    @if($pengajuan->dokumen->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($pengajuan->dokumen as $doc)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <i class="{{ $doc->file_icon_class }} me-2"></i>
                                    <strong>{{ $doc->jenis_dokumen_alias }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $doc->created_at->format('d M Y') }}
                                    </small>
                                </div>
                                <a href="{{ route('pengajuan.dokumen.download', $doc->id) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted mb-0">Belum ada dokumen.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hasilBandingSelect = document.querySelector('select[name="hasil_banding"]');
        const revisiFields = document.getElementById('revisiFields');

        if (hasilBandingSelect) {
            hasilBandingSelect.addEventListener('change', function() {
                if (this.value === 'diterima') {
                    revisiFields.style.display = 'block';
                    revisiFields.querySelectorAll('input, select').forEach(el => {
                        el.required = true;
                    });
                } else {
                    revisiFields.style.display = 'none';
                    revisiFields.querySelectorAll('input, select').forEach(el => {
                        el.required = false;
                        el.value = '';
                    });
                }
            });
        }
    });

</script>
@endpush
