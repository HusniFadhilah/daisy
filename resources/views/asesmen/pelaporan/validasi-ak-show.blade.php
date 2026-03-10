{{-- resources/views/asesmen/pelaporan/validasi-ak-show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan Validasi AK')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('pelaporan.indexValidasiAK') }}">Pelaporan Validasi AK</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Detail Pelaporan Validasi AK
            </h5>
            @if($pengajuan)
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
            @else
            <small class="text-muted">{{ $assignment->asesmen->code }}</small>
            @endif
        </div>
        <a href="{{ route('pelaporan.indexValidasiAK') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $isReported = $pengajuan && !empty($pengajuan->tanggal_pelaporan_ak);
            $canReport = $pengajuan && $pengajuan->canBeReported('ak');
            @endphp

            @if($isReported)
            <div class="alert alert-success alert-permanent mb-4">
                <i class="bi bi-check-circle"></i>
                <strong>Pelaporan Validasi AK telah selesai</strong>
                <br>
                Dilaporkan pada {{ $pengajuan->tanggal_pelaporan_ak->locale('id')->translatedFormat('d M Y H:i') }}
            </div>
            @elseif($canReport)
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu Pelaporan Validasi AK</strong>
                <br>
                Silakan upload dan finalisasi laporan validasi AK
            </div>
            @else
            <div class="alert alert-info alert-permanent mb-4">
                <i class="bi bi-arrow-repeat"></i>
                <strong>Proses Asesmen AK Sedang Berlangsung</strong>
                <br>
                Pelaporan dapat dilakukan setelah proses asesmen selesai
            </div>
            @endif

            <!-- File Laporan Validasi AK -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> Laporan Penilaian Kecukupan LED Program Studi (LHK)
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumen = $assignment->asesmen->documents()
                    ->where('type', 'laporan_validasi_ak')
                    ->where('is_active', true)
                    ->latest('id')
                    ->first();
                    @endphp

                    @if($dokumen)
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center rounded gap-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-file-earmark-pdf text-danger me-3" style="font-size: 48px;"></i>
                            <div>
                                <strong>{{ $dokumen->title }}</strong>
                                <br>
                                <small class="text-muted">{{ $dokumen->original_name }}</small>
                                <br>
                                <small class="text-muted">
                                    {{ number_format($dokumen->size / 1024, 2) }} KB •
                                    Diupload: {{ $dokumen->uploaded_at->locale('id')->translatedFormat('d M Y H:i') }}
                                </small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('pelaporan.validasiAk.download', $assignment->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada file laporan validasi AK</p>
                        @if($canReport)
                        <button type="button" class="btn btn-info mt-3 js-open-pelaporan" data-type="ak" data-assignment-id="{{ $assignment->id }}" data-nomor="{{ $pengajuan->nomor_pengajuan ?? $assignment->asesmen->code }}">
                            <i class="bi bi-upload"></i> Upload Laporan
                        </button>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Asesmen -->
            <div class="card mt-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pelaporan Validasi AK</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        @if($pengajuan)
                        <tr>
                            <th>Tanggal Pelaporan Validasi AK</th>
                            <td>: {{ \App\Libraries\Date::tglIndo($pengajuan->tanggal_pelaporan_ak) }}</td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan Validasi AK</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_ak','validator') !!}</td>
                        </tr>
                        @endif
                        <tr>
                            <th style="width: 40%;">Program Studi</th>
                            <td>: {{ $pengajuan->studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <th>Universitas</th>
                            <td>: {{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Riwayat Status -->
            @if($pengajuan)
            @php
            $filterStatuses = [
            //\App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            //\App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->sortBy('changed_at');
            @endphp

            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-circle-fill text-secondary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->locale('id')->translatedFormat('d M Y H:i') }}</small>
                                    {{-- @if($log->keterangan)
                                    <br>
                                    <small class="text-muted fst-italic">{{ $log->keterangan }}</small>
                                    @endif --}}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted text-center mb-0">Belum ada riwayat</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informasi Asesmen Kecukupan -->
            @if($assignment->asesmen->asesmenKecukupan)
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Info Asesmen Kecukupan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $ak = $assignment->asesmen->asesmenKecukupan;
                    @endphp
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="45%">Status</th>
                            <td>:
                                @if($ak->status === 'completed')
                                <span class="badge bg-success">Selesai</span>
                                @elseif($ak->status === 'in_progress')
                                <span class="badge bg-warning">Berlangsung</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($ak->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($ak->completed_at)
                        <tr>
                            <th>AK Selesai pada</th>
                            <td>: {{ \App\Libraries\Date::tglIndo($ak->completed_at) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/validasi.js') }}"></script>
@endpush
