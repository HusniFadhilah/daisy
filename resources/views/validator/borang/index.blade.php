{{-- resources/views/validator/borang/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Borang')

@section('content')
<div class="container-fluid py-3">
    {{-- Header --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">Validasi Borang Akreditasi</h3>
                    <p class="text-muted mb-0">Daftar pengajuan yang Anda validasi sebagai Validator Borang</p>
                </div>
                <a href="{{ route('penawaran') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Menunggu Review</h6>
                            <h2 class="mb-0">{{ $stats['pending'] }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-clock-history text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Sedang Review</h6>
                            <h2 class="mb-0">{{ $stats['in_review'] }}</h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-eye text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Perlu Revisi</h6>
                            <h2 class="mb-0">{{ $stats['revision'] }}</h2>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Disetujui</h6>
                            <h2 class="mb-0">{{ $stats['approved'] }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assignment List --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Daftar Borang</h5>
        </div>
        <div class="card-body">
            @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor Pengajuan</th>
                            <th>Program Studi</th>
                            <th>Jenjang</th>
                            <th>Status Borang</th>
                            <th>Status Validasi</th>
                            <th>Tanggal Ditugaskan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                        @php
                        $pengajuan = $assignment->pengajuan;
                        $statusBadge = [
                        'not_started' => '<span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Belum Dimulai</span>',
                        'in_progress' => '<span class="badge bg-info"><i class="bi bi-eye"></i> Sedang Review</span>',
                        'revision_required' => '<span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> Perlu Revisi</span>',
                        'approved' => '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Disetujui</span>',
                        ];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                            </td>
                            <td>
                                {{ $pengajuan->studyProgram->name }}
                                <br>
                                <small class="text-muted">{{ $pengajuan->studyProgram->university->name }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $pengajuan->studyProgram->degreeLevel->name }}</span>
                            </td>
                            <td>
                                @if($pengajuan->status === 'borang_online_selesai')
                                <span class="badge bg-success">Selesai Diisi</span>
                                @elseif($pengajuan->status === 'borang_revision_required')
                                <span class="badge bg-warning text-dark">Perlu Revisi</span>
                                @else
                                <span class="badge bg-secondary">{{ ucfirst($pengajuan->status) }}</span>
                                @endif
                            </td>
                            <td>{!! $statusBadge[$assignment->status_pekerjaan] !!}</td>
                            <td>
                                {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y H:i') : '-' }}
                            </td>
                            <td>
                                <a href="{{ route('validator.borang.show', $assignment->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $assignments->links() }}
            </div>
            @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Belum ada borang yang ditugaskan untuk Anda validasi.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
