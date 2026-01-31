{{-- resources/views/upps/pelaksanaan-al/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaksanaan Asesmen Lapangan')

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pelaksanaan Asesmen Lapangan</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Pelaksanaan Asesmen Lapangan
            </h4>
            <p class="text-muted mb-0">Tracking pelaksanaan dan persetujuan berita acara Asesmen Lapangan</p>
        </div>
    </div>

    <!-- Pending Approval Alert -->
    @if($stats['pending_approval'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-4 border-warning mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-bell-fill fs-1 me-3 text-warning"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-exclamation-circle-fill"></i> Berita Acara Menunggu Persetujuan
                </h5>
                <p class="mb-2">
                    Anda memiliki <strong class="text-danger fs-5">{{ $stats['pending_approval'] }}</strong>
                    berita acara Asesmen Lapangan yang menunggu persetujuan.
                </p>
                <div class="alert alert-light mb-2">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <strong>Penting:</strong> Segera tinjau dan setujui berita acara untuk melanjutkan proses akreditasi.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Penugasan Asesor AL</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Asesor ditugaskan</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-person-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Berita Acara AL</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['berita_acara'] }}</h2>
                            <small class="opacity-75">Sudah diupload</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                <div class="card-body text-white position-relative">
                    <h6 class="mb-2 opacity-75">Menunggu Persetujuan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['pending_approval'] }}</h2>
                            <small class="opacity-75">Perlu ditinjau</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                    @if($stats['pending_approval'] > 0)
                    <span class="position-absolute top-0 end-0 m-2 badge bg-danger rounded-pill">
                        {{ $stats['pending_approval'] }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card filter-card">
                <div class="card-header border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Filter & Pencarian
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('upps.pelaksanaan-al') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Pelaksanaan</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED ? 'selected' : '' }}>
                                    Asesor Ditugaskan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS ? 'selected' : '' }}>
                                    Sedang Berlangsung
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI ? 'selected' : '' }}>
                                    Selesai
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN ? 'selected' : '' }}>
                                    Dilaporkan
                                </option>
                            </select>
                        </div>

                        <!-- Tahun -->
                        <div class="mb-3">
                            <label class="form-label text-white">Tahun Akreditasi</label>
                            <select name="tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('upps.pelaksanaan-al') }}" class="btn btn-outline-light">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Pelaksanaan Asesmen Lapangan</h5>
                        <div>
                            <span class="text-muted">Total: <strong>{{ $pengajuans->total() }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($pengajuans->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Permohonan Akreditasi</th>
                                    <th width="25%">Program Studi</th>
                                    <th width="15%">Asesor AL</th>
                                    <th width="15%">Status</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <p class="mb-1"><strong>{{ $pengajuan->judul }}</strong></p>
                                        <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                        <br>
                                        <small class="text-muted">
                                            Dibuat pada: {{ $pengajuan->created_at->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                        </small>
                                        <br>
                                        <small>{{ $pengajuan->studyProgram->university->name ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($pengajuan->asesorAL)
                                        <small>
                                            {{ $pengajuan->asesorAL->name }}
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('asesmen_lapangan', 'upps') !!}

                                        @php
                                        $pendingBA = $pengajuan->asesmen->beritaAcaraAL
                                        ->whereIn('status_persetujuan_prodi', ['pending', 'revision_required'])
                                        ->count();
                                        @endphp

                                        @if($pendingBA > 0)
                                        <br>
                                        <span class="badge bg-warning text-dark mt-1">
                                            <i class="bi bi-bell"></i> {{ $pendingBA }} BA Pending
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.pelaksanaan-al.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer bg-white">
                        {{ $pengajuans->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                        <p class="text-muted mt-3">
                            @if(request()->filled('search') || request()->filled('status'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada pelaksanaan asesmen lapangan
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.pelaksanaan-al') }}" class="btn btn-sm btn-info">
                            <i class="bi bi-arrow-clockwise"></i> Reset Filter
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
