@extends('layouts.template.app')

@section('title', 'Detail Validasi AK - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('de.validasi-ak') }}">
                    <i class="bi bi-arrow-left"></i> Monitoring Validasi AK
                </a>
            </li>
            <li class="breadcrumb-item active">Detail</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Detail Validasi AK
            </h4>
            <p class="text-muted mb-0">{{ $pengajuan->nomor_pengajuan }}</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Info -->
        <div class="col-lg-4">
            <!-- Program Studi Info -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Program Studi
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Program Studi</td>
                            <td><strong>{{ $pengajuan->studyProgram->name }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Universitas</td>
                            <td>{{ $pengajuan->studyProgram->university->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenjang</td>
                            <td>{{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <span class="badge bg-info text-wrap">
                                    {{ $pengajuan->status_label }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Validation Summary -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-check-circle"></i> Ringkasan Validasi
                    </h6>
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

            <!-- AK Schedule -->
            @if($pengajuan->asesmen?->asesmenKecukupan)
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar-range"></i> Jadwal AK
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        @if($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai)
                        <tr>
                            <td class="text-muted" width="40%">Tanggal Mulai</td>
                            <td><strong>{{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)
                        <tr>
                            <td class="text-muted">Tanggal Selesai</td>
                            <td><strong>{{ \Carbon\Carbon::parse($pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)->format('d M Y') }}</strong></td>
                        </tr>
                        @endif
                        @if($pengajuan->asesmen->asesmenKecukupan->tanggal_mulai && $pengajuan->asesmen->asesmenKecukupan->tanggal_selesai)
                        <tr>
                            <td class="text-muted">Durasi</td>
                            <td>
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

        <!-- Right Column: Progress Details -->
        <div class="col-lg-8">
            <!-- Progress Per User -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Progress Per Asesor & Validator</h6>
                </div>
                <div class="card-body p-0">
                    @if($pengajuan->asesmen && $pengajuan->asesmen->asesmenUserRoles->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
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
                                            {{ ucfirst($progress['role']) }}
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
                        <p class="text-muted mt-3 mb-0">Belum ada penugasan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
