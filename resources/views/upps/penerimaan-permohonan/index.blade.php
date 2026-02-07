{{-- resources/views/upps/penerimaan-permohonan/index.blade.php --}}

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
            <li class="breadcrumb-item active">Penerimaan Permohonan Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-envelope-check"></i> Penerimaan Permohonan Akreditasi
            </h4>
            <p class="text-muted mb-0">Daftar penerimaan permohonan akreditasi</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Penerimaan Permohonan Akreditasi</strong><br>
        Penerimaan permohonan akreditasi program studi tersedia pada daftar berikut
    </div>

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Menunggu Penerimaan Permohonan Akreditasi" :value="$stats['diterima']" description="Penerimaan permohonan akreditasi sedang diproses oleh LAMDEPILAR" icon="hourglass-split" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Permohonan Akreditasi Diterima" :value="$stats['surat_dikirim']" description="Penerimaan permohonan akreditasi telah selesai diproses oleh LAMDEPILAR" icon="check-circle" iconBg="success-subtle" />
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
                        <h5 class="mb-0">Daftar Penerimaan Permohonan Akreditasi</h5>
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
                                    <th width="20%">Permohonan Akreditasi</th>
                                    <th width="25%">Tanggal Permohonan Akreditasi Diterima</th>
                                    <th width="25%">Status Penerimaan Permohonan Akreditasi</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $suratPenerimaan = $pengajuan->dokumen->first();
                                $daysSinceTerima = $pengajuan->tanggal_surat_permohonan_diterima
                                ? floor(\Carbon\Carbon::parse($pengajuan->tanggal_surat_permohonan_diterima)->diffInDays(now()))
                                : 0;
                                @endphp
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_surat_permohonan_diterima)
                                        <small>
                                            {{ $pengajuan->tanggal_surat_permohonan_diterima->format('d M Y') }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            ({{ $daysSinceTerima }} hari lalu)
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('surat_penerimaan_de','upps','label_short_for') !!}
                                        <br>
                                        @if($suratPenerimaan)
                                        <small class="text-muted">
                                            {{ $suratPenerimaan->created_at->format('d M Y') }}
                                        </small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('upps.penerimaan-permohonan.show', $pengajuan->id) }}" class="btn btn-primary" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @php
                                            $dokumen = $pengajuan->dokumen->first();
                                            @endphp

                                            @if($dokumen)
                                            <a href="{{ route('upps.penerimaan-permohonan.download', $pengajuan->id) }}" class="btn btn-success" title="Lihat Surat Penerimaan">
                                                <i class="bi bi-file-earmark-pdf"></i>
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
                            @if(request()->filled('search') || request()->filled('status'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada penerimaan permohonan akreditasi
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.penerimaan-permohonan') }}" class="btn btn-sm btn-info">
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
