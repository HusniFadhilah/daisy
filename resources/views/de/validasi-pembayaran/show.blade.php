@extends('layouts.template.app')

@section('title', 'Detail Pembayaran - ' . $pembayaran->nomor_invoice)

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-credit-card"></i> Detail Pembayaran
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
            @if($pembayaran->status_pembayaran === 'menunggu_pembayaran')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu pembayaran</strong>
                <br>
                Program Studi sedang proses melakukan pembayaran akreditasi
            </div>
            @elseif($pembayaran->status_pembayaran === 'menunggu_verifikasi')
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu validasi pembayaran</strong>
                <br>
                Bagian keuangan sedang proses melakukan validasi pembayaran
            </div>
            @elseif($pembayaran->status_pembayaran == 'terverifikasi')
            <div class="alert alert-success alert-permanent">
                <h5><i class="bi bi-check-circle"></i> Pembayaran Tervalidasi</h5>
                <p class="mb-0">
                    Pembayaran telah divalidasi oleh <strong>{{ $pembayaran->verifier->role_alias }}</strong>
                    pada {{ $pembayaran->tanggal_verifikasi->locale('id')->translatedFormat('d F Y H:i') }}
                </p>
            </div>
            @endif

            <!-- Dokumen Pembayaran -->
            <div class="card my-4">
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
                                {{ $dokumen->original_filename }}
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
                                <div class="d-flex align-items-center gap-2 sensitive-wrapper">
                                    <strong class="text-success fs-5 sensitive-value" data-value="{{ $pembayaran->jumlah_pembayaran }}" data-type="currency" data-hidden="true">
                                        Rp ••••••••
                                    </strong>

                                    <button type="button" class="btn btn-sm btn-light toggle-sensitive" title="Tampilkan/Sembunyikan">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Invoice</th>
                            <td>{{ $pembayaran->created_at->locale('id')->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Jatuh Tempo</th>
                            <td>
                                {{ \App\Libraries\Date::tglIndo($pembayaran->tanggal_jatuh_tempo) }}
                                @if($pembayaran->tanggal_jatuh_tempo && $pembayaran->tanggal_jatuh_tempo < now()) <span class="badge bg-danger ms-2">Terlambat</span>
                                    @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Pembayaran</th>
                            <td>{{ $pembayaran->tanggal_pembayaran ? \App\Libraries\Date::tglIndo($pembayaran->tanggal_pembayaran) : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status Pembayaran</th>
                            <td>
                                @php
                                $statusConfig = [
                                'menunggu_pembayaran' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu Pembayaran'],
                                'menunggu_verifikasi' => ['class' => 'info', 'icon' => 'clock-history', 'text' => 'Menunggu Validasi'],
                                'terverifikasi' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Tervalidasi'],
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
                        <strong>Catatan Validasi:</strong>
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
        </div>

        <!-- Right Column - Actions -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card ">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <div class="timeline">
                        <!-- Invoice Dibuat -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-secondary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Invoice Dibuat</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Pembayaran Dilakukan -->
                        @if($pembayaran->tanggal_pembayaran)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-secondary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>Formulir & Bukti Pembayaran Diupload</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->pengajuan->formulirPembayaran?->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Pembayaran Divalidasi -->
                        @if($pembayaran->tanggal_verifikasi)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill
                                            {{ $pembayaran->status_pembayaran === 'terverifikasi' ? 'text-success' : 'text-danger' }}" style="font-size: 8px;">
                                    </i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        @if($pembayaran->tanggal_ditolak)
                                        Ditolak
                                        @elseif($pembayaran->tanggal_verifikasi && $pembayaran->status_pembayaran == 'terverifikasi')
                                        Pembayaran Divalidasi
                                        @elseif($pembayaran->tanggal_upload_ulang || $pembayaran->status_pembayaran == 'upload_ulang')
                                        Diminta Upload Ulang
                                        @else
                                        -
                                        @endif
                                    </strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->tanggal_verifikasi->locale('id')->translatedFormat('d M Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', initSensitiveToggle);

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
