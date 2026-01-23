@extends('layouts.template.app')

@section('title', 'Detail Pembayaran - ' . $pembayaran->nomor_invoice)

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-receipt"></i> Detail Pembayaran
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('de.validasi-pembayaran') }}">Validasi Pembayaran</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $pembayaran->nomor_invoice }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('de.validasi-pembayaran') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Left Column - Informasi Pembayaran -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nomor Invoice</th>
                            <td><strong>{{ $pembayaran->nomor_invoice }}</strong></td>
                        </tr>
                        <tr>
                            <th>Program Studi</th>
                            <td>
                                <strong>{{ $pembayaran->pengajuan->studyProgram->name }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $pembayaran->pengajuan->studyProgram->university->name }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Jumlah Pembayaran</th>
                            <td>
                                <strong class="text-success fs-5">
                                    Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Invoice</th>
                            <td>{{ $pembayaran->created_at->format('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>
                                {{ $pembayaran->tanggal_jatuh_tempo?->format('d F Y') ?? '-' }}
                                @if($pembayaran->tanggal_jatuh_tempo && $pembayaran->tanggal_jatuh_tempo < now()) <span class="badge bg-danger ms-2">Terlambat</span>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td>{{ $pembayaran->tanggal_pembayaran?->format('d F Y H:i') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Pembayaran</th>
                            <td>
                                @php
                                $statusConfig = [
                                'menunggu_pembayaran' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu Pembayaran'],
                                'menunggu_verifikasi' => ['class' => 'info', 'icon' => 'clock-history', 'text' => 'Menunggu Verifikasi'],
                                'terverifikasi' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Terverifikasi'],
                                'upload_ulang' => ['class' => 'secondary', 'icon' => 'arrow-repeat', 'text' => 'Upload Ulang'],
                                'ditolak' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                                ];
                                $status = $statusConfig[$pembayaran->status_pembayaran] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                                @endphp
                                <span class="badge bg-{{ $status['class'] }}">
                                    <i class="bi bi-{{ $status['icon'] }}"></i>
                                    {{ $status['text'] }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($pembayaran->catatan_pembayaran)
                    <div class="alert alert-info alert-permanent">
                        <strong>Catatan dari Prodi:</strong>
                        <p class="mb-0">{{ $pembayaran->catatan_pembayaran }}</p>
                    </div>
                    @endif

                    @if($pembayaran->catatan_verifikasi)
                    <div class="alert alert-secondary alert-permanent">
                        <strong>Catatan Verifikasi:</strong>
                        <p class="mb-0">{{ $pembayaran->catatan_verifikasi }}</p>
                    </div>
                    @endif

                    @if($pembayaran->alasan_penolakan)
                    <div class="alert alert-danger alert-permanent">
                        <strong>Alasan Penolakan:</strong>
                        <p class="mb-0">{{ $pembayaran->alasan_penolakan }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Dokumen Pembayaran -->
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Dokumen Terkait</h5>
                </div>
                <div class="card-body">
                    @forelse($dokumenPembayaran as $dokumen)
                    <div class="d-flex justify-content-between align-items-center p-3 mb-2 border rounded">
                        <div>
                            <i class="bi {{ $dokumen->file_icon_class }} me-2"></i>
                            <strong>{{ $dokumen->jenis_dokumen_alias }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $dokumen->original_filename }} ({{ $dokumen->file_size_formatted }})
                            </small>
                        </div>
                        <a href="{{ $dokumen->download_url }}" class="btn btn-sm btn-primary" target="_blank">
                            <i class="bi bi-download"></i> Download
                        </a>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada dokumen pembayaran</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column - Actions -->
        <div class="col-lg-4">
            @if($pembayaran->status_pembayaran == 'menunggu_verifikasi')
            {{-- <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-exclamation"></i> Validasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('de.validasi-pembayaran.validasi', $pembayaran->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-bold">Aksi Validasi</label>
                <select name="action" class="form-select" required onchange="toggleFields(this.value)">
                    <option value="">-- Pilih Aksi --</option>
                    <option value="approve">✅ Setujui Pembayaran</option>
                    <option value="upload_ulang">🔄 Minta Upload Ulang</option>
                    <option value="reject">❌ Tolak Pembayaran</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Catatan Verifikasi</label>
                <textarea name="catatan_verifikasi" class="form-control" rows="3" placeholder="Tambahkan catatan (opsional)"></textarea>
            </div>

            <div class="mb-3 d-none" id="alasanPenolakanField">
                <label class="form-label fw-bold text-danger">Alasan Penolakan *</label>
                <textarea name="alasan_penolakan" class="form-control" rows="3" placeholder="Jelaskan alasan penolakan..."></textarea>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Submit Validasi
                </button>
            </div>
            </form>
        </div>
    </div> --}}
    @elseif($pembayaran->status_pembayaran == 'terverifikasi')
    <div class="alert alert-success alert-permanent">
        <h5><i class="bi bi-check-circle"></i> Pembayaran Terverifikasi</h5>
        <p class="mb-0">
            Pembayaran telah diverifikasi oleh <strong>{{ $pembayaran->verifier->name }}</strong>
            pada {{ $pembayaran->tanggal_verifikasi->format('d F Y H:i') }}
        </p>
    </div>
    @endif

    <!-- Timeline -->
    <div class="card ">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Timeline
            </h5>
        </div>
        <div class="card-body">
            <ul class="list-unstyled timeline">
                <li class="mb-3">
                    <i class="bi bi-circle-fill text-primary"></i>
                    <strong>Invoice Dibuat</strong>
                    <br>
                    <small class="text-muted">{{ $pembayaran->created_at->format('d F Y H:i') }}</small>
                </li>

                @if($pembayaran->tanggal_pembayaran)
                <li class="mb-3">
                    <i class="bi bi-circle-fill text-info"></i>
                    <strong>Pembayaran Dilakukan</strong>
                    <br>
                    <small class="text-muted">{{ $pembayaran->tanggal_pembayaran->format('d F Y H:i') }}</small>
                </li>
                @endif

                @if($pembayaran->tanggal_verifikasi)
                <li class="mb-3">
                    <i class="bi bi-circle-fill text-success"></i>
                    <strong>Pembayaran Diverifikasi</strong>
                    <br>
                    <small class="text-muted">{{ $pembayaran->tanggal_verifikasi->format('d F Y H:i') }}</small>
                    <br>
                    <small class="text-muted">oleh {{ $pembayaran->verifier->name }}</small>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>
</div>
</div>

@push('scripts')
<script>
    function toggleFields(action) {
        const alasanField = document.getElementById('alasanPenolakanField');

        if (action === 'reject') {
            alasanField.classList.remove('d-none');
            alasanField.querySelector('textarea').required = true;
        } else {
            alasanField.classList.add('d-none');
            alasanField.querySelector('textarea').required = false;
        }
    }

</script>
@endpush
@endsection
