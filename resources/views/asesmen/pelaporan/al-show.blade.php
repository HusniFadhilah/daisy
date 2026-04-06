{{-- resources/views/asesmen/pelaporan/al-show.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Detail Pelaporan AL')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('pelaporan.indexAL') }}">Pelaporan AL</a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-geo-alt"></i> Detail Pelaporan Asesmen Lapangan (AL)
            </h5>
            @if($pengajuan)
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
            @else
            <small class="text-muted">{{ $assignment->asesmen->code }}</small>
            @endif
        </div>
        <a href="{{ route('pelaporan.indexAL') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            <!-- Status Alert -->
            @php
            $isReported = $pengajuan && !empty($pengajuan->tanggal_pelaporan_al);
            $canReport = $pengajuan && $pengajuan->canBeReported('al');
            @endphp

            @if($isReported)
            <div class="alert alert-success alert-permanent mb-4">
                <i class="bi bi-check-circle"></i>
                <strong>Pelaporan AL telah selesai</strong>
                <br>
                Dilaporkan pada {{ $pengajuan->tanggal_pelaporan_al->locale('id')->translatedFormat('d M Y H:i') }}
            </div>
            @elseif($canReport)
            <div class="alert alert-warning alert-permanent mb-4">
                <i class="bi bi-hourglass-split"></i>
                <strong>Menunggu Pelaporan AL</strong>
                <br>
                Silakan upload dan finalisasi laporan asesmen lapangan
            </div>
            @else
            <div class="alert alert-info alert-permanent mb-4">
                <i class="bi bi-arrow-repeat"></i>
                <strong>Proses Asesmen Lapangan Sedang Berlangsung</strong>
                <br>
                Pelaporan dapat dilakukan setelah asesmen selesai
            </div>
            @endif

            <!-- File Laporan AL -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-pdf"></i> Laporan Hasil Asesmen Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $dokumen = $assignment->asesmen->documents()
                    ->where('type', 'laporan_al')
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
                                @if($dokumen->version > 1)
                                <br>
                                <small class="badge bg-info">Versi {{ $dokumen->version }}</small>
                                @endif
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('pelaporan.al.download', $assignment->id) }}" class="btn btn-success btn-md">
                                <i class="bi bi-eye"></i> Lihat File
                            </a>
                        </div>
                    </div>

                    @if(!$isReported && $canReport)
                    <div class="alert alert-warning alert-permanent">
                        <i class="bi bi-info-circle"></i>
                        <strong>Perhatian:</strong> Setelah yakin dengan laporan yang diupload, jangan lupa untuk <strong>finalisasi pelaporan</strong> agar status permohonan diperbarui.
                    </div>
                    @endif
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-x" style="font-size: 48px; color: #ddd;"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada file laporan asesmen lapangan</p>
                        @if($canReport)
                        <button type="button" class="btn btn-info mt-3 js-open-pelaporan" data-type="al" data-assignment-id="{{ $assignment->id }}" data-nomor="{{ $pengajuan->nomor_pengajuan ?? $assignment->asesmen->code }}">
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
                    <h5 class="mb-0">Informasi Pelaporan AL</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        @if($pengajuan)
                        <tr>
                            <th>Tanggal Pelaporan AL</th>
                            <td>: {{ \App\Libraries\Date::tglIndo($pengajuan->tanggal_pelaporan_al) }}</td>
                        </tr>
                        <tr>
                            <th>Status Pelaporan AL</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_al','de', 'label_long_for') !!}</td>
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
            \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->unique('status_to')
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

            <!-- Informasi Asesmen Lapangan -->
            @if($assignment->asesmen->asesmenLapangan)
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Info Asesmen Lapangan
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $al = $assignment->asesmen->asesmenLapangan;
                    @endphp
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th width="45%">Status</th>
                            <td>:
                                @if($al->status === 'completed')
                                <span class="badge bg-success">Selesai</span>
                                @elseif($al->status === 'finalized')
                                <span class="badge bg-success">Selesai & Difinalisasi</span>
                                @elseif($al->status === 'in_progress')
                                <span class="badge bg-warning">Berlangsung</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($al->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($al->location)
                        <tr>
                            <th>Lokasi</th>
                            <td>: {{ $al->location }}</td>
                        </tr>
                        @endif
                        @if($al->scheduled_date)
                        <tr>
                            <th>Tanggal Jadwal</th>
                            <td>: {{ \App\Libraries\Date::tglIndo($al->scheduled_date) }}</td>
                        </tr>
                        @endif
                        @if($al->completed_at)
                        <tr>
                            <th>AL Selesai pada</th>
                            <td>: {{ \App\Libraries\Date::tglIndo($al->completed_at) }}</td>
                        </tr>
                        @endif
                        {{-- @if($al->completed_by)
                        <tr>
                            <th>Diselesaikan Oleh</th>
                            <td>: {{ $al->completedBy->name ?? '-' }}</td>
                        </tr>
                        @endif --}}
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
