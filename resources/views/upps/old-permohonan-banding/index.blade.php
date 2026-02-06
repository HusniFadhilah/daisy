{{-- resources/views/upps/permohonan-banding/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Permohonan Banding')

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
            <li class="breadcrumb-item active">Permohonan Banding</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-arrow-repeat"></i> Permohonan Banding Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Monitor permohonan banding terhadap hasil akreditasi</p>
        </div>
    </div>

    <!-- Info Alert -->
    @if($stats['dalam_proses'] > 0)
    <div class="alert alert-info alert-permanent border-start border-4 border-info mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-info-circle-fill fs-1 me-3 text-info"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-arrow-repeat"></i> Banding Dalam Proses
                </h5>
                <p class="mb-0">
                    Anda memiliki <strong class="fs-5">{{ $stats['dalam_proses'] }}</strong>
                    permohonan banding yang sedang diproses oleh LAMDEPILAR.
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> Menu Permohonan Banding
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Download Template -->
                        <div class="col-md-4">
                            <div class="d-grid">
                                <button type="button" class="btn btn-md btn-info" data-bs-toggle="modal" data-bs-target="#modalDownloadTemplate">
                                    <i class="bi bi-download"></i>
                                    <br>
                                    <span class="small">Download Template Permohonan Banding</span>
                                </button>
                            </div>
                        </div>

                        <!-- Buat Permohonan -->
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('upps.permohonan-banding.create') }}" class="btn btn-md btn-primary">
                                    <i class="bi bi-file-earmark-plus"></i>
                                    <br>
                                    <span class="small">Buat Permohonan Banding</span>
                                </a>
                            </div>
                        </div>

                        <!-- Simpan sebagai Draft (Optional - if needed) -->
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('upps.permohonan-banding.create') }}?draft=true" class="btn btn-md btn-secondary">
                                    <i class="bi bi-save"></i>
                                    <br>
                                    <span class="small">Simpan Draft Permohonan Banding/Kirim</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan Banding" :value="$stats['total']" description="Banding diajukan" icon="arrow-repeat" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Dalam Proses" :value="$stats['dalam_proses']" description="Sedang diproses" icon="hourglass-split" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Selesai" :value="$stats['selesai']" description="Banding dilaporkan" icon="check-circle" iconBg="success-subtle" />
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
                        <h5 class="mb-0">Daftar Permohonan Banding</h5>
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
                                    <th width="15%">Tanggal Banding</th>
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
                                            Peringkat Awal:
                                            @if($pengajuan->peringkat_hasil)
                                            <strong>{{ $pengajuan->peringkat_hasil }}</strong>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
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
                                                        ? $pengajuan->tanggal_banding->format('d M Y H:i')
                                                        : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $pengajuan->status_badge_class }}">
                                            {{ $pengajuan->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.permohonan-banding.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            Belum ada permohonan banding
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.permohonan-banding') }}" class="btn btn-sm btn-info">
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
