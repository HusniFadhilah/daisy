@extends('layouts.template.app')

@section('title', 'Detail Validasi AK - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('de.validasi-ak') }}">Validasi AK</a></li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Detail Validasi AK
            </h5>
            <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <a href="{{ route('de.validasi-ak') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
            @php
            $allowed = [
            \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
            \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
            \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
            \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED,
            ];

            $log = $pengajuan->latestRelevantStatusLog($allowed);
            @endphp

            <!-- Status Alert -->
            @if($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AK_ASSIGNED)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Proses AK Berlangsung</strong><br>
                Asesor dan Validator sedang ditugaskan untuk melakukan penilaian dan validasi AK. <br>Mohon pantau progress penilaian secara berkala
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS)
            <div class="alert alert-info alert-permanent">
                <i class="bi bi-clock-history"></i>
                <strong>Proses Penilaian AK Berlangsung</strong><br>
                Asesor sedang melakukan penilaian kecukupan dokumen. Mohon pantau progress penilaian secara berkala
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION)
            <div class="alert alert-warning alert-permanent">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Menunggu Validasi</strong><br>
                Penilaian asesor telah selesai dan menunggu validasi dari validator. Segera lakukan validasi untuk melanjutkan proses
            </div>
            @elseif($log?->status_to === \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI)
            <div class="alert alert-success alert-permanent">
                <i class="bi bi-check-circle"></i>
                <strong>Validasi AK Selesai</strong><br>
                Proses validasi penilaian kecukupan telah selesai dan hasil telah divalidasi
            </div>
            @endif

            <!-- Progress Per Asesor & Validator -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> Progress Per Asesor & Validator
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($pengajuan->asesmen && $pengajuan->asesmen->asesmenUserRoles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Nama</th>
                                    <th width="15%">Role</th>
                                    <th width="15%">Status</th>
                                    <th width="35%">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuan->asesmen->asesmenUserRoles->sortBy(function($aur) {
                                return $aur->role_selected->name === 'asesor' ? 0 : 1;
                                }) as $index => $assignment)
                                @php
                                $progress = $userProgress[$assignment->id_user] ?? [
                                'role' => $assignment->role_selected->name,
                                'total' => $totalElements,
                                'completed' => 0,
                                'pending' => $totalElements,
                                'percentage' => 0,
                                'status' => $assignment->status_pekerjaan,
                                ];

                                $statusBadge = match($progress['status']) {
                                'not_started' => ['class' => 'secondary', 'text' => 'Belum Mulai'],
                                'in_progress' => ['class' => 'info', 'text' => 'Dalam Proses'],
                                'submitted' => ['class' => 'warning', 'text' => 'Sudah Submit'],
                                'validated' => ['class' => 'success', 'text' => 'Sudah Validasi'],
                                'revision_required' => ['class' => 'danger', 'text' => 'Perlu Revisi'],
                                'approved' => ['class' => 'success', 'text' => 'Approved'],
                                default => ['class' => 'secondary', 'text' => 'Unknown'],
                                };

                                $progressColor = $progress['percentage'] == 100 ? 'success' :
                                ($progress['percentage'] >= 50 ? 'info' : 'warning');
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $assignment->user->name }}</strong>
                                        @if($assignment->urutan_asesor)
                                        <span class="badge bg-secondary">#{{ $assignment->urutan_asesor }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $progress['role'] === 'asesor' ? 'primary' : 'success' }}">
                                            {{ $assignment->role_selected->alias }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusBadge['class'] }}">
                                            {{ $statusBadge['text'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress mb-1" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $progress['percentage'] }}%">
                                                {{ $progress['percentage'] }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            {{ $progress['completed'] }}/{{ $progress['total'] }} elemen
                                            @if($progress['pending'] > 0)
                                            <span class="text-warning">({{ $progress['pending'] }} pending)</span>
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3 mb-0">Belum ada penugasan asesor atau validator</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Validasi AK -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Validasi AK
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Tanggal Penilaian Dimulai</th>
                            <td>
                                : {{ $pengajuan->tanggal_ak_mulai
                                    ? $pengajuan->tanggal_ak_mulai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Validasi AK</th>
                            <td>
                                : {{ $pengajuan->tanggal_validasi_ak
                                    ? $pengajuan->tanggal_validasi_ak->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Penilaian Selesai</th>
                            <td>
                                : {{ $pengajuan->tanggal_ak_selesai
                                    ? $pengajuan->tanggal_ak_selesai->format('d M Y H:i')
                                    : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Status Validasi AK</th>
                            <td>: {!! $pengajuan->getCustomBadgeLastStatus('validasi_ak', 'de', 'label_long_for') !!}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ringkasan Validasi -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle"></i> Ringkasan Validasi
                    </h5>
                </div>
                <div class="card-body">
                    @php
                    $totalPenilaian = $validationSummary->total_penilaian ?? 0;
                    $validatedCount = $validationSummary->validated_count ?? 0;
                    $pendingValidation = $validationSummary->pending_validation ?? 0;
                    $progressPct = $totalElements > 0 ? round(($validatedCount / $totalElements) * 100, 1) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Progress Validasi</span>
                            <strong>{{ $progressPct }}%</strong>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" style="width: {{ $progressPct }}%">
                                {{ $validatedCount }}/{{ $totalElements }}
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><i class="bi bi-check-circle text-success"></i> Sudah Divalidasi</td>
                            <td class="text-end"><strong>{{ $validatedCount }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-clock text-warning"></i> Menunggu Validasi</td>
                            <td class="text-end"><strong>{{ $pendingValidation }}</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-exclamation-circle text-danger"></i> Belum Dinilai</td>
                            <td class="text-end"><strong>{{ $totalElements - $validatedCount - $pendingValidation }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Riwayat Status -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Riwayat Status
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    @php
                    $filterStatuses = [
                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION,
                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI,
                    ];

                    $logs = $pengajuan->statusLog
                    ->whereIn('status_to', $filterStatuses)
                    ->sortBy('created_at')
                    ->unique('status_to')
                    ->values();
                    @endphp

                    @if($logs->count() > 0)
                    <div class="timeline">
                        @foreach($logs as $log)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @php
                                    $iconColor = match($log->status_to) {
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_SELESAI
                                    => 'text-success',
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_ON_VALIDATION
                                    => 'text-warning',
                                    \App\Models\PengajuanAkreditasi::STATUS_AK_IN_PROGRESS
                                    => 'text-info',
                                    default => 'text-secondary',
                                    };
                                    @endphp
                                    <i class="bi bi-circle-fill {{ $iconColor }}" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <strong>
                                        {{ \App\Models\PengajuanAkreditasi::statusMap()[$log->status_to]['label_long_for']['de'] ?? $log->status_to }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $log->created_at->format('d M Y H:i') }}</small>

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

            <!-- Jadwal AK -->
            @if($pengajuan->asesmen?->asesmenKecukupan)
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-range"></i> Jadwal AK
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        @if($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai)
                        <tr>
                            <th class="text-muted" width="45%">Tanggal Mulai</th>
                            <td>: <strong>{{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)
                        <tr>
                            <th class="text-muted">Tanggal Selesai</th>
                            <td>: <strong>{{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai && $pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)
                        <tr>
                            <th class="text-muted">Durasi</th>
                            <td>:
                                @php
                                $start = \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai);
                                $end = \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai);
                                $days = $start->diffInDays($end);
                                @endphp
                                <span class="badge bg-primary">{{ $days }} hari</span>
                            </td>
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
