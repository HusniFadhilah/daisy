{{-- resources/views/upps/pelaksanaan-banding/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaksanaan Banding')

@push('styles')
<style>
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .filter-card {
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
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
            <li class="breadcrumb-item active">Pelaksanaan Banding</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-play-circle"></i> Pelaksanaan Banding
            </h4>
            <p class="text-muted mb-0">Monitor proses pelaksanaan banding hasil akreditasi</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Pelaksanaan Banding</strong><br>
        Pelaksanaan banding tersedia pada daftar berikut
    </div>

    <!-- Active Alert -->
    @if($stats['sedang_berlangsung'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-4 border-warning mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-play-circle-fill fs-1 me-3 text-warning"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-play-circle"></i> Pelaksanaan Banding Sedang Berlangsung
                </h5>
                <p class="mb-0">
                    Anda memiliki <strong class="fs-5">{{ $stats['sedang_berlangsung'] }}</strong>
                    pelaksanaan banding yang sedang berlangsung oleh LAMDEPILAR.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Pelaksanaan Banding" :value="$stats['total']" description="Banding dalam proses" icon="play-circle" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Menunggu Pelaksanaan" :value="$stats['menunggu']" description="Belum dilaksanakan" icon="hourglass-split" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sedang Berlangsung" :value="$stats['sedang_berlangsung']" description="Dalam pelaksanaan" icon="arrow-repeat" iconBg="success-subtle" />
        </div>
    </div> --}}

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar -->

        <!-- Main Content -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Pelaksanaan Banding</h5>
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
                                    <th width="15%">Tanggal Pengajuan</th>
                                    <th width="20%">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
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
                                            Peringkat: <strong>{{ $pengajuan->peringkat_hasil ?? '-' }}</strong>
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
                                            {{ $pengajuan->tanggal_banding
                                                        ? $pengajuan->tanggal_banding->format('d M Y')
                                                        : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $pengajuan->status_badge_class }}">
                                            {{ $pengajuan->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.pelaksanaan-banding.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            Belum ada pelaksanaan banding
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.pelaksanaan-banding') }}" class="btn btn-sm btn-info">
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
