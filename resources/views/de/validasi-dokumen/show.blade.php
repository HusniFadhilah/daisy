@extends('layouts.template.app')

@section('title', 'Detail Validasi Dokumen')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <h4 class="mb-1">
                        <i class="bi bi-clipboard-check"></i> Detail Validasi Dokumen
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('de.validasi-dokumen') }}">Validasi Dokumen</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $pengajuan->nomor_pengajuan }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('de.validasi-dokumen') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @php
    $allowed = [
    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
    \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
    \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
    \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
    \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
    ]; // ini contoh, bisa dinamis dari config/db/request

    $log = $pengajuan->latestRelevantStatusLog($allowed);
    @endphp
    <!-- Status Alert -->
    @if(in_array($log?->status_to,[\App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,\App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION]))
    <div class="alert alert-info alert-permanent">
        <i class="bi bi-check-circle"></i>
        <strong>Dokumen sedang divalidasi</strong><br>
        Validasi dokumen sedang dalam proses
    </div>
    @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED)
    <div class="alert alert-success alert-permanent">
        <i class="bi bi-check-circle"></i>
        <strong>Dokumen telah divalidasi</strong><br>
        Validasi dokumen telah dilakukan dan dinyatakan lengkap dan sesuai.
    </div>
    @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED)
    <div class="alert alert-warning alert-permanent">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Dokumen memerlukan revisi</strong>
        <br>
        Validator menyatakan dokumen memerlukan revisi. <br> Program studi telah menerima poin revisinya
    </div>
    @endif

    <!-- Progress Summary -->
    @if($progress)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Progres Validasi</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="text-muted small">LED</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $progress['led_percentage'] >= 80 ? 'success' : 'warning' }}" style="width: {{ $progress['led_percentage'] }}%">
                                    {{ $progress['led_percentage'] }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Suplemen</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $progress['suplemen_percentage'] >= 80 ? 'success' : 'warning' }}" style="width: {{ $progress['suplemen_percentage'] }}%">
                                    {{ $progress['suplemen_percentage'] }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">LKPS</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $progress['lkps_percentage'] >= 80 ? 'success' : 'warning' }}" style="width: {{ $progress['lkps_percentage'] }}%">
                                    {{ $progress['lkps_percentage'] }}%
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge bg-{{ $progress['percentage'] >= 100 ? 'success' : 'warning' }}">
                            Total Progres: {{ $progress['percentage'] }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8 mb-4">
            <!-- Assignment Info -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Validasi Dokumen</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tr>
                                <th width="35%">Validator</th>
                                <td>
                                    <strong>{{ $assignment->user->name }}</strong>
                                    <br>
                                    <small class="text-muted text-wrap">{{ $assignment->user->email }}</small>
                                </td>
                            </tr>
                            <tr>
                                <th>Program Studi</th>
                                <td>
                                    <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->studyProgram->university->name }}
                                    </small>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Penugasan Validator</th>
                                <td>
                                    : {{ $pengajuan->tanggal_validasi_borang_assigned
                                    ? $pengajuan->tanggal_validasi_borang_assigned->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Validasi Selesai</th>
                                <td>
                                    : {{ $pengajuan->tanggal_validasi_borang_selesai
                                    ? $pengajuan->tanggal_validasi_borang_selesai->locale('id')->translatedFormat('d M Y H:i')
                                    : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Status Validasi Dokumen</th>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('validasi_dokumen','de','label_long_for') !!}
                                </td>
                            </tr>
                        </table>

                        @if($assignment->status_penawaran === 'accepted')
                        <div class="mt-3">
                            <a href="{{ route('validator.borang.show', $assignment->id) }}" class="btn btn-primary" target="_blank">
                                <i class="bi bi-clipboard-check"></i> Lihat Detail Validasi
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Revision Details -->
            @if($revisionDetails && ($revisionDetails['led'] || $revisionDetails['suplemen'] || $revisionDetails['lkps']))

            {{-- <div class="card mb-4" id="validationCard">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clipboard-check"></i> Hasil Validasi Dokumen
                    </h5>
                    <span class="badge bg-secondary" id="validationBadge">Memuat...</span>
                </div>
                <div class="card-body">
                    <div id="validationLoading" class="text-muted">
                        <span class="spinner-border spinner-border-sm me-2"></span> Memuat data validasi...
                    </div>

                    <div id="validationContent" class="d-none">
                        <div class="mb-2">
                            <small class="text-muted">Validator</small>
                            <div class="fw-semibold" id="validatorName">-</div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">LED</div>
                                    <div class="fw-bold" id="valLedCount">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">Suplemen</div>
                                    <div class="fw-bold" id="valSuplemenCount">-</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded">
                                    <div class="small text-muted">LKPS</div>
                                    <div class="fw-bold" id="valLkpsCount">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Total Progres</small>
                            <div class="progress">
                                <div class="progress-bar" id="valTotalBar" style="width:0%"></div>
                            </div>
                            <div class="small text-muted mt-1">
                                <span id="valTotalText">0%</span> • terakhir update <span id="valUpdatedAt">-</span>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <h6 class="mb-2"><i class="bi bi-list-check"></i> Poin Revisi</h6>

                            <div id="valRevisionSkeleton" class="d-none">
                                <div class="sk-card">
                                    <div class="skeleton sk-line lg sk-w-40"></div>
                                    <div class="skeleton sk-line sk-w-75"></div>
                                    <div class="skeleton sk-line sm sk-w-60"></div>
                                </div>
                                <div class="sk-card">
                                    <div class="skeleton sk-line lg sk-w-30"></div>
                                    <div class="skeleton sk-line sk-w-90"></div>
                                    <div class="skeleton sk-line sm sk-w-60"></div>
                                </div>
                                <div class="sk-card">
                                    <div class="skeleton sk-line lg sk-w-20"></div>
                                    <div class="skeleton sk-line sk-w-75"></div>
                                    <div class="skeleton sk-line sm sk-w-60"></div>
                                </div>
                            </div>

                            <div id="valRevisionList" class="d-none"></div>
                            <div id="valRevisionEmpty" class="text-muted d-none">Tidak ada poin revisi.</div>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">Catatan Validator (Keseluruhan)</small>
                            <div class="border rounded p-2 bg-white" id="valNoteAll">-</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Catatan LED</small>
                            <div class="border rounded p-2 bg-white" id="valNoteLed">-</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Catatan Suplemen</small>
                            <div class="border rounded p-2 bg-white" id="valNoteSuplemen">-</div>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted">Catatan LKPS</small>
                            <div class="border rounded p-2 bg-white" id="valNoteLkps">-</div>
                        </div>
                    </div>

                    <div id="validationEmpty" class="d-none text-muted">
                        Belum ada hasil validasi.
                    </div>

                    <div id="validationError" class="d-none alert alert-danger alert-permanent">
                        Gagal memuat hasil validasi.
                    </div>
                </div>
            </div> --}}
            {{-- <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Item yang Perlu Revisi
                    </h5>
                </div>
                <div class="card-body">
                    @if($revisionDetails['led'])
                    <h6 class="fw-bold">LED:</h6>
                    <ul>
                        @foreach($revisionDetails['led'] as $item)
                        <li>
                            @if(isset($item['elemen']->kode_elemen))
                            <strong>{{ $item['elemen']->kode_elemen }}</strong>: {{ $item['elemen']->pernyataan_elemen }}
            @endif
            <br>
            <span class="badge bg-{{ $item['grade'] === 'A' ? 'danger' : 'warning' }}">
                Grade {{ $item['grade'] }}
            </span>
            @if($item['catatan'])
            <br>
            <small class="text-muted">{{ $item['catatan'] }}</small>
            @endif
            </li>
            @endforeach
            </ul>
            @endif

            @if($revisionDetails['suplemen'])
            <h6 class="fw-bold mt-3">Suplemen:</h6>
            <ul>
                @foreach($revisionDetails['suplemen'] as $item)
                <li>
                    @if(isset($item['elemen']->kode_elemen))
                    <strong>{{ $item['elemen']->kode_elemen }}</strong>: {{ $item['elemen']->pernyataan_elemen }}
                    @endif
                    <br>
                    <span class="badge bg-{{ $item['grade'] === 'A' ? 'danger' : 'warning' }}">
                        Grade {{ $item['grade'] }}
                    </span>
                    @if($item['catatan'])
                    <br>
                    <small class="text-muted">{{ $item['catatan'] }}</small>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif

            @if($revisionDetails['lkps'])
            <h6 class="fw-bold mt-3">LKPS:</h6>
            <ul>
                @foreach($revisionDetails['lkps'] as $item)
                <li>
                    <strong>{{ $item['indikator']->kode_indikator }}</strong>
                    <br>
                    <span class="badge bg-{{ $item['grade'] === 'A' ? 'danger' : 'warning' }}">
                        Grade {{ $item['grade'] }}
                    </span>
                    @if($item['catatan'])
                    <br>
                    <small class="text-muted">{{ $item['catatan'] }}</small>
                    @endif
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div> --}}
    @endif
</div>

<!-- Right Column - Timeline -->
<div class="col-lg-4">
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                <i class="bi bi-clock-history"></i> Riwayat Status
            </h5>
        </div>
        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
            @php
            $filterStatuses = [
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA,
            ];

            $logs = $pengajuan->statusLog
            ->whereIn('status_to', $filterStatuses)
            ->unique('status_to')
            ->sortBy('changed_at');
            @endphp

            @if($logs->count() > 0)
            <div class="timeline">
                @foreach($logs as $log)
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            @php
                            $iconColor = match($log->status_to) {
                            \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED,
                            \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA
                            => 'text-success',
                            \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED
                            => 'text-danger',
                            \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION
                            => 'text-warning',
                            default => 'text-info',
                            };
                            @endphp
                            <i class="bi bi-circle-fill {{ $iconColor }}" style="font-size: 8px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <strong>
                                {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label'] ?? $log->status_to }}
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
            <p class="text-muted text-center mb-0">Belum ada riwayat validasi</p>
            @endif
        </div>
    </div>
</div>
</div>
</div>
@endsection
