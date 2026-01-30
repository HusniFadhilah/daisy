{{-- resources/views/de/penetapan-hasil-akreditasi/show.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Detail Penetapan Hasil')

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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.penetapan-hasil-akreditasi') }}">Penetapan Hasil</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-award"></i> Detail Penetapan Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            <a href="{{ route('de.penetapan-hasil-akreditasi') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Basic Info Card -->
            <div class="card info-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Pengajuan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Nomor Pengajuan</label>
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
                            <label class="text-muted small">Status</label>
                            <p>
                                @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN)
                                <span class="badge bg-success">Sudah Ditetapkan</span>
                                @else
                                <span class="badge bg-warning text-dark">Menunggu Penetapan</span>
                                @endif
                            </p>
                        </div>
                        @if($pengajuan->tanggal_penetapan)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Tanggal Penetapan</label>
                            <p class="fw-bold">{{ $pengajuan->tanggal_penetapan->format('d F Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Hasil Akreditasi -->
            @php
            if ($pengajuan->hasil_banding === 'diterima') {
            $peringkat = $pengajuan->peringkat_final;
            $skor = $pengajuan->skor_final;
            } else {
            $hasil = $pengajuan->asesmen->hasil ?? null;
            $peringkat = $hasil->peringkat_akreditasi ?? '-';
            $skor = $hasil->skor_final ?? 0;
            }

            $badgeClass = match($peringkat) {
            'Unggul' => 'success',
            'Baik Sekali' => 'primary',
            'Baik' => 'info',
            default => 'secondary'
            };
            @endphp

            <div class="card result-card mb-4">
                <div class="card-header bg-{{ $badgeClass }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-award"></i> Hasil Akreditasi Final
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h6 class="text-muted small mb-2">Peringkat</h6>
                                <h2 class="mb-0">
                                    <span class="badge bg-{{ $badgeClass }} badge-peringkat">
                                        {{ $peringkat }}
                                    </span>
                                </h2>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded">
                                <h6 class="text-muted small mb-2">Skor Final</h6>
                                <h2 class="mb-0 fw-bold">{{ number_format($skor, 2) }}</h2>
                                <small class="text-muted">dari 400</small>
                            </div>
                        </div>
                    </div>

                    @if($pengajuan->hasil_banding === 'diterima')
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Hasil ini merupakan hasil revisi setelah banding diterima.
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons (if not yet penetapan) -->
            @if($pengajuan->status != \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN)
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-check-circle"></i> Tetapkan Hasil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('de.penetapan-hasil-akreditasi.tetapkan', $pengajuan->id) }}">
                        @csrf

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Pastikan hasil akreditasi sudah benar sebelum menetapkan.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Penetapan (Opsional)</label>
                            <textarea name="catatan_penetapan" class="form-control" rows="3" placeholder="Catatan tambahan mengenai penetapan hasil..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Tetapkan Hasil Akreditasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <!-- Batalkan Penetapan (if already penetapan) -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-x-circle"></i> Batalkan Penetapan</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Gunakan dengan hati-hati. Pembatalan penetapan akan mengembalikan status ke sebelumnya.
                    </p>
                    <button type="button" class="btn btn-danger" onclick="batalkanPenetapan({{ $pengajuan->id }}, '{{ $pengajuan->nomor_pengajuan }}')">
                        <i class="bi bi-x-circle"></i> Batalkan Penetapan
                    </button>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Timeline & Info -->
        <div class="col-lg-4">
            <!-- Status Log -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Timeline</h5>
                </div>
                <div class="card-body">
                    @forelse($pengajuan->statusLog->sortByDesc('changed_at')->take(10) as $log)
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="d-block">
                            {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                        </strong>
                        <small class="text-muted">
                            {{ $log->changed_at->format('d M Y H:i') }}
                        </small>
                        @if($log->keterangan)
                        <p class="text-muted small mb-0 mt-1">{{ $log->keterangan }}</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted mb-0">Tidak ada riwayat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Batalkan Penetapan -->
<div class="modal fade" id="modalBatalkanPenetapan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formBatalkanPenetapan" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-x-circle"></i> Batalkan Penetapan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan membatalkan penetapan hasil untuk:</p>
                    <div class="alert alert-danger">
                        <strong id="nomorPengajuanBatal"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Alasan Pembatalan <span class="text-danger">*</span>
                        </label>
                        <textarea name="alasan_pembatalan" class="form-control" rows="3" placeholder="Jelaskan alasan pembatalan penetapan..." required></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Status akan dikembalikan ke sebelum penetapan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-circle"></i> Batalkan Penetapan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function batalkanPenetapan(id, nomorPengajuan) {
        const modal = new bootstrap.Modal(document.getElementById('modalBatalkanPenetapan'));
        const form = document.getElementById('formBatalkanPenetapan');

        form.action = `{{ route('de.penetapan-hasil-akreditasi') }}/${id}/batalkan`;
        document.getElementById('nomorPengajuanBatal').textContent = nomorPengajuan;

        modal.show();
    }

</script>
@endpush
