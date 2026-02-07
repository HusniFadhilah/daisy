@extends('layouts.template.app')

@section('title', 'Detail ' . $studyProgram->name)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('de.pemetaan.index') }}">Pengingat Masa Akreditasi</a></li>
            <li class="breadcrumb-item active">Detail Program Studi</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>{{ $studyProgram->name }}</h4>
            <p class="text-muted mb-0">
                {{ $studyProgram->degreeLevel->name }} - {{ $studyProgram->university->name }}
            </p>
        </div>
        {{-- @if(!$activePengajuan && ($studyProgram->status_kedaluwarsa != 'Aktif' || floor(now()->diffInDays($studyProgram->tanggal_kedaluwarsa, false)) <= 180)) <a href="{{ route('pengajuan.create', ['study_program_id' => $studyProgram->id]) }}" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Kirim Pengingat Akreditasi
        </a>
        @endif --}}
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
                            <span class="badge bg-{{ $studyProgram->status_kedaluwarsa == 'Aktif' ? 'success' : ($studyProgram->status_kedaluwarsa == 'Kedaluwarsa' ? 'danger' : 'secondary') }}">
                                {{ $studyProgram->status_kedaluwarsa }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Peringkat</label>
                        <div class="fw-bold">{{ $studyProgram->peringkat_akreditasi ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Tanggal Kedaluwarsa</label>
                        <div class="fw-bold">
                            {{ $studyProgram->tanggal_kedaluwarsa ? $studyProgram->tanggal_kedaluwarsa->format('d F Y') : '-' }}
                        </div>
                    </div>

                    @if($studyProgram->tanggal_kedaluwarsa)
                    <div class="mb-3">
                        <label class="text-muted small">Sisa Waktu</label>
                        {!! $studyProgram->full_days_left !!}
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
                <strong>Ada Permohonan akreditasi aktif:</strong> {{ $activePengajuan->nomor_pengajuan }}
                (Status: {{ $activePengajuan->status_label }})
                <a href="{{ route('pengajuan.show', $activePengajuan->id) }}" class="alert-link">Lihat Detail →</a>
            </div>
            @endif

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Permohonan Akreditasi</h5>
                </div>
                <div class="card-body">
                    @forelse($historyPengajuan as $pengajuan)
                    <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="fw-bold mb-1">{{ $pengajuan->nomor_pengajuan }}</h6>
                                <small class="text-muted">
                                    {{ $pengajuan->jenis_akreditasi_label }} - Tahun {{ $pengajuan->tahun_akreditasi }}
                                </small>
                            </div>
                            <span class="badge {{ $pengajuan->status_badge_class }}">
                                {{ $pengajuan->status_label }}
                            </span>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> {{ \App\Libraries\Date::tglIndo($pengajuan->created_at) }}
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
                    <p class="text-muted mb-0">Belum ada riwayat permohonan akreditasi</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
