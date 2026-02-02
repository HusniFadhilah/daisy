{{-- resources/views/upps/masa-sanggah/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Masa Sanggah')

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
            <li class="breadcrumb-item active">Masa Sanggah</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clock-history"></i> Masa Sanggah Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Monitor periode masa sanggah dan pengajuan banding</p>
        </div>
    </div>

    <!-- Active Masa Sanggah Alert -->
    @if($stats['aktif'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-4 border-warning mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-bell-fill fs-1 me-3 text-warning"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-exclamation-circle-fill"></i> Masa Sanggah Sedang Berlangsung
                </h5>
                <p class="mb-2">
                    Anda memiliki <strong class="text-danger fs-5">{{ $stats['aktif'] }}</strong>
                    masa sanggah yang sedang aktif.
                </p>
                <div class="alert alert-light mb-0">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <strong>Penting:</strong> Jika Anda memiliki keberatan terhadap hasil akreditasi,
                    dapat mengajukan banding selama periode masa sanggah masih berlangsung.
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
                    <h6 class="mb-2 opacity-75">Total Masa Sanggah</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Semua masa sanggah</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-calendar-range"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                <div class="card-body text-white position-relative">
                    <h6 class="mb-2 opacity-75">Masa Sanggah Aktif</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['aktif'] }}</h2>
                            <small class="opacity-75">Sedang berlangsung</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    @if($stats['aktif'] > 0)
                    <span class="position-absolute top-0 end-0 m-2 badge bg-danger rounded-pill">
                        {{ $stats['aktif'] }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Masa Sanggah Selesai</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h2>
                            <small class="opacity-75">Telah berakhir</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
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
                    <form method="GET" action="{{ route('upps.masa-sanggah') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status Sanggah -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Masa Sanggah</label>
                            <select name="status_sanggah" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status_sanggah') == 'aktif' ? 'selected' : '' }}>
                                    Sedang Berlangsung
                                </option>
                                <option value="selesai" {{ request('status_sanggah') == 'selesai' ? 'selected' : '' }}>
                                    Sudah Selesai
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
                            <a href="{{ route('upps.masa-sanggah') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Masa Sanggah</h5>
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
                                    <th width="20%">Periode Masa Sanggah</th>
                                    <th width="15%">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $now = now();
                                $isAktif = $now->between($pengajuan->tanggal_masa_sanggah_mulai, $pengajuan->tanggal_masa_sanggah_selesai);
                                $sisaHari = $isAktif ? $now->diffInDays($pengajuan->tanggal_masa_sanggah_selesai, false) : 0;
                                @endphp
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
                                        <small>
                                            <strong>Mulai:</strong> {{ $pengajuan->tanggal_masa_sanggah_mulai->format('d M Y') }}
                                            <br>
                                            <strong>Selesai:</strong> {{ $pengajuan->tanggal_masa_sanggah_selesai->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($isAktif)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Aktif ({{ $sisaHari }} hari)
                                        </span>
                                        @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Selesai
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.masa-sanggah.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            @if(request()->filled('search') || request()->filled('status_sanggah'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada masa sanggah
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status_sanggah'))
                        <a href="{{ route('upps.masa-sanggah') }}" class="btn btn-sm btn-info">
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
