{{-- resources/views/prodi/penerimaan-permohonan/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penerimaan Permohonan Akreditasi')

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

    .badge-status {
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penerimaan Permohonan</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-envelope-check"></i> Penerimaan Permohonan Akreditasi
            </h4>
            <p class="text-muted mb-0">Daftar penerimaan permohonan akreditasi dari LAMDEPILAR</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    @include('prodi.penerimaan-permohonan.components.stats-cards', ['stats' => $stats])

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            @include('prodi.penerimaan-permohonan.components.filter-sidebar', [
            'tahunList' => $tahunList
            ])
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Permohonan Akreditasi</h5>
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
                                    <th width="20%">Nomor Permohonan</th>
                                    <th width="25%">Program Studi</th>
                                    <th width="12%">Tahun</th>
                                    <th width="15%">Tanggal Diterima DE</th>
                                    <th width="13%">Status Penerimaan</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $suratPenerimaan = $pengajuan->dokumen->first();
                                @endphp
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            Dibuat: {{ $pengajuan->created_at->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $pengajuan->tahun_akreditasi }}</span>
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_surat_permohonan_diterima)
                                        <small>
                                            {{ $pengajuan->tanggal_surat_permohonan_diterima->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            ({{ $pengajuan->tanggal_surat_permohonan_diterima->diffForHumans() }})
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($suratPenerimaan)
                                        <span class="badge bg-success badge-status">
                                            <i class="bi bi-check-circle"></i> Diterima
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $suratPenerimaan->created_at->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                        @else
                                        <span class="badge bg-warning badge-status">
                                            <i class="bi bi-hourglass-split"></i> Menunggu
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('prodi.penerimaan-permohonan.show', $pengajuan->id) }}" class="btn btn-primary" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($suratPenerimaan)
                                            <a href="{{ route('prodi.penerimaan-permohonan.download', $pengajuan->id) }}" class="btn btn-success" title="Download File">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @endif
                                        </div>
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
                            @if(request()->filled('search') || request()->filled('status_penerimaan'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada permohonan akreditasi
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status_penerimaan'))
                        <a href="{{ route('prodi.penerimaan-permohonan') }}" class="btn btn-sm btn-primary">
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
