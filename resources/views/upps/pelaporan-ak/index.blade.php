{{-- resources/views/upps/pelaporan-ak/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaporan AK')

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
            <li class="breadcrumb-item active">Pelaporan AK</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-bar-graph"></i> Pelaporan AK
            </h4>
            <p class="text-muted mb-0">Monitor pelaporan AK</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Pelaporan AK</strong><br>
        Pelaporan AK pada permohonan akreditasi program studi tersedia pada daftar berikut
    </div>

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Dokumen" :value="$stats['total']" description="Total dokumen yang telah sampai pada tahap Pelaporan AK" icon="file-earmark-text" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Penilaian AK Tervalidasi" :value="$stats['tervalidasi']" description="Penilaian AK yang telah divalidasi" icon="patch-check" iconBg="success-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Pelaporan AK" :value="$stats['dilaporkan']" description="Dapat dilanjutkan ke tahap berikutnya (Asesmen Lapangan)" icon="send-check" iconBg="info-subtle" />
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
                        <h5 class="mb-0">Daftar Pelaporan AK</h5>
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
                                    <th width="25%">Tanggal Pelaporan AK</th>
                                    <th width="25%">Status Pelaporan AK</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_pelaporan_ak)
                                        <small>{{ $pengajuan->tanggal_pelaporan_ak->locale('id')->translatedFormat('d M Y') }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->tanggal_pelaporan_ak->diffForHumans() }}
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_ak', 'upps', 'label_short_for') !!}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.pelaporan-ak.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                        {{ $pengajuans->onEachSide(1)->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                        <p class="text-muted mt-3">
                            @if(request()->filled('search') || request()->filled('status'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada permohonan akreditasi yang masuk tahap pelaporan hasil AK
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.pelaporan-ak') }}" class="btn btn-sm btn-info">
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
