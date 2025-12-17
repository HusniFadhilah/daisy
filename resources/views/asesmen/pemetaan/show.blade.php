@extends('layouts.template.app')

@section('title', 'Detail ' . $studyProgram->name)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('pemetaan.index') }}">Pemetaan Akreditasi</a></li>
            <li class="breadcrumb-item active">{{ $studyProgram->name }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $studyProgram->name }}</h2>
            <p class="text-muted mb-0">
                {{ $studyProgram->degreeLevel->name }} - {{ $studyProgram->university->name }}
            </p>
        </div>
        @if(!$activePengajuan && ($studyProgram->status_kadaluarsa != 'Aktif' || floor(now()->diffInDays($studyProgram->tanggal_kadaluarsa, false)) <= 180))) <a href="{{ route('pengajuan.create', ['study_program_id' => $studyProgram->id]) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajukan Akreditasi
            </a>
            @endif
    </div>

    <div class="row">
        <!-- Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Akreditasi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            <span class="badge bg-{{ $studyProgram->status_kadaluarsa == 'Aktif' ? 'success' : ($studyProgram->status_kadaluarsa == 'Kadaluarsa' ? 'danger' : 'secondary') }}">
                                {{ $studyProgram->status_kadaluarsa }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Peringkat</label>
                        <div class="fw-bold">{{ $studyProgram->peringkat_akreditasi ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Tanggal Kadaluarsa</label>
                        <div class="fw-bold">
                            {{ $studyProgram->tanggal_kadaluarsa ? $studyProgram->tanggal_kadaluarsa->format('d F Y') : '-' }}
                        </div>
                    </div>

                    @if($studyProgram->tanggal_kadaluarsa)
                    <div class="mb-3">
                        <label class="text-muted small">Sisa Waktu</label>
                        @php
                        $daysLeft = floor(now()->diffInDays($studyProgram->tanggal_kadaluarsa, false));
                        @endphp
                        <div class="fw-bold {{ $daysLeft < 90 ? 'text-danger' : ($daysLeft < 180 ? 'text-warning' : 'text-success') }}">
                            {{ round($daysLeft / 30) }} Bulan ({{ $daysLeft }} hari)
                        </div>
                    </div>
                    @endif

                    <div class="mb-0">
                        <label class="text-muted small">Email Kontak</label>
                        <div>{{ $studyProgram->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pengajuan -->
        <div class="col-md-8">
            @if($activePengajuan)
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Ada pengajuan aktif:</strong> {{ $activePengajuan->nomor_pengajuan }}
                (Status: {{ $activePengajuan->status_label }})
                <a href="{{ route('pengajuan.show', $activePengajuan->id) }}" class="alert-link">Lihat Detail →</a>
            </div>
            @endif

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Pengajuan</h5>
                </div>
                <div class="card-body">
                    @forelse($historyPengajuan as $pengajuan)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $pengajuan->nomor_pengajuan }}</h6>
                                <small class="text-muted">
                                    {{ $pengajuan->jenis_akreditasi }} - Tahun {{ $pengajuan->tahun_akreditasi }}
                                </small>
                            </div>
                            <span class="badge {{ $pengajuan->status_badge_class }}">
                                {{ $pengajuan->status_label }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> {{ $pengajuan->created_at->format('d M Y') }}
                                @if($pengajuan->pengaju)
                                | <i class="bi bi-person"></i> {{ $pengajuan->pengaju->name }}
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                    </div>
                    @empty
                    <p class="text-muted mb-0">Belum ada riwayat pengajuan</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
