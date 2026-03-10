{{-- resources/views/upps/penyimpanan-arsip-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penyimpanan Arsip Akreditasi')

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

    .peringkat-badge {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
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
            <li class="breadcrumb-item active">Penyimpanan Arsip Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-archive"></i> Penyimpanan Arsip Akreditasi
            </h4>
            <p class="text-muted mb-0">Dokumentasi lengkap proses dan hasil akreditasi program studi</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Penyimpanan Arsip Akreditasi Program Studi</strong><br>
        Arsip akreditasi program studi dapat dilihat pada daftar berikut.<br>
    </div>

    <!-- Success Alert -->
    {{-- @if($stats['total'] > 0)
    <div class="alert alert-success alert-permanent border-start border-2 border-success mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-archive-fill fs-1 me-3 text-success"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-check-circle-fill"></i> Arsip Akreditasi Tersimpan
                </h5>
                <p class="mb-2">
                    Total <strong class="fs-5">{{ $stats['total'] }}</strong> arsip pelaksanaan akreditasi
    telah disimpan dengan lengkap untuk dokumentasi dan keperluan masa depan.
    </p>
    @if($stats['selesai'] > 0)
    <div class="alert alert-light border border-success mb-0">
        <i class="bi bi-patch-check-fill text-success"></i>
        <strong>{{ $stats['selesai'] }}</strong> proses akreditasi telah selesai seluruhnya.
    </div>
    @endif
</div>
</div>
</div>
@endif --}}

<!-- Statistics Cards -->
{{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Arsip Tersimpan" :value="$stats['total']" description="Dokumen akreditasi" icon="archive" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Proses Selesai" :value="$stats['selesai']" description="Akreditasi selesai" icon="patch-check" iconBg="success-subtle" />
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
                    <h5 class="mb-0">Daftar Arsip Akreditasi</h5>
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
                                <th width="20%">Status Akreditasi</th>
                                <th width="20%">Status Penyimpanan Arsip</th>
                                <th width="20%">Tanggal Penyimpanan Arsip</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuans as $index => $pengajuan)
                            @php
                            $peringkatAkhir = $pengajuan->peringkat_final;
                            $hasil = $pengajuan->asesmen->hasil ?? null;
                            @endphp
                            <tr>
                                <td>{{ $pengajuans->firstItem() + $index }}</td>
                                <td>
                                    {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                                </td>
                                <td>
                                    @if($hasil && $hasil->skor_final)
                                    @php
                                    $peringkatFinal = $hasil->getPeringkatFromSkor((float)($hasil->skor_al ?? 0));
                                    @endphp
                                    <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkatFinal) }}; color:#222">
                                        {{ $peringkatFinal }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('penyimpanan_arsip','upps','label_short_for','text-dark') !!}
                                </td>
                                <td>
                                    <small>
                                        {{ $pengajuan->tanggal_penyimpanan
                                                        ? $pengajuan->tanggal_penyimpanan->locale('id')->translatedFormat('d M Y')
                                                        : '-' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('upps.penyimpanan-arsip-akreditasi.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                        @if(request()->filled('search') || request()->filled('peringkat'))
                        Tidak ada data yang sesuai dengan filter
                        @else
                        Belum ada arsip akreditasi tersimpan
                        @endif
                    </p>
                    @if(request()->filled('search') || request()->filled('peringkat'))
                    <a href="{{ route('upps.penyimpanan-arsip-akreditasi') }}" class="btn btn-sm btn-info">
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
