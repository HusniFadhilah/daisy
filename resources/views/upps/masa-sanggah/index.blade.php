{{-- resources/views/upps/masa-sanggah/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Masa Sanggah')

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
            <li class="breadcrumb-item active">Masa Sanggah</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-hourglass-split"></i> Masa Sanggah
            </h4>
            <p class="text-muted mb-0">Monitor periode masa sanggah hasil akreditasi</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Masa Sanggah</strong><br>
        Permohonan akreditasi untuk program studi memasuki masa sanggah<br>
        Program studi memiliki masa sanggah selama 1 minggu.<br>
        Untuk melakukan permohonan banding, silahkan menuju ke menu Permohonan Banding atau <a href="{{ route('upps.permohonan-banding') }}" class="text-link">klik link berikut</a>.
    </div>

    <!-- Active Masa Sanggah Alert -->
    @if($stats['aktif'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-2 border-warning mb-4">
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-exclamation-circle-fill"></i> Masa Sanggah Sedang Berlangsung
                </h5>
                <p class="mb-2">
                    Anda memiliki <strong class="text-danger fs-5">{{ $stats['aktif'] }}</strong>
                    masa sanggah yang sedang aktif.
                </p>
                <div class="alert alert-light alert-permanent mb-2">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <strong>Penting:</strong> Jika Anda memiliki keberatan terhadap hasil akreditasi,
                    dapat mengajukan banding selama periode masa sanggah masih berlangsung.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Masa Sanggah" :value="$stats['total']" description="Semua masa sanggah" icon="calendar-range" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Masa Sanggah Aktif" :value="$stats['aktif']" description="Sedang berlangsung" icon="clock-history" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Masa Sanggah Selesai" :value="$stats['selesai']" description="Telah berakhir" icon="check-circle" iconBg="success-subtle" />
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
                                    <th width="25%">Periode Masa Sanggah</th>
                                    <th width="25%">Status Masa Sanggah</th>
                                    <th width="15%" class="text-center">Aksi</th>
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
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Mulai:</strong> {{ $pengajuan->tanggal_masa_sanggah_mulai->locale('id')->translatedFormat('d M Y') }}
                                            <br>
                                            <strong>Selesai:</strong> {{ $pengajuan->tanggal_masa_sanggah_selesai->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                    </td>
                                    <td style="min-width: 150px;">
                                        {!! $pengajuan->getCustomBadgeLastStatus('masa_sanggah','upps','label_short_for','text-dark') !!}
                                        {{-- @if($isAktif)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Aktif ({{ $sisaHari }} hari)
                                        </span>
                                        @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Selesai
                                        </span>
                                        @endif --}}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.masa-sanggah.show', $pengajuan->id) }}" class="btn btn-primary btn-sm" title="Lihat Detail">
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
