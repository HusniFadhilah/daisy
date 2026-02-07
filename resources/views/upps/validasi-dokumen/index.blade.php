{{-- resources/views/upps/validasi-dokumen/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Dokumen')

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
            <li class="breadcrumb-item active">Validasi Dokumen</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-check"></i> Validasi Dokumen
            </h4>
            <p class="text-muted mb-0">Monitor proses validasi dokumen akreditasi</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Validasi Dokumen</strong><br>
        Validasi dokumen permohonan akreditasi program studi dapat dilihat pada daftar berikut<br>
    </div>

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Menunggu Validasi Dokumen" :value="$stats['menunggu_validasi']" description="Validasi dokumen belum dimulai" icon="hourglass-split" iconBg="info-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Dokumen Sedang Divalidasi" :value="$stats['sedang_validasi']" description="Dokumen dalam proses validasi" icon="clock-history" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Dokumen Perlu Revisi" :value="$stats['perlu_revisi']" description="Dokumen perlu perbaikan" icon="arrow-repeat" iconBg="danger-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Dokumen Tervalidasi" :value="$stats['tervalidasi']" description="Dokumen selesai divalidasi, dan dapat dilanjutkan ke tahap berikutnya" icon="patch-check" iconBg="success-subtle" />
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
                        <h5 class="mb-0">Daftar Validasi Dokumen</h5>
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
                                    <th width="25%">Status Validator</th>
                                    <th width="25%">Status Validasi Dokumen</th>
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
                                        @if($pengajuan->validator)
                                        <span class="badge bg-success">Telah ditugaskan</span>
                                        @else
                                        <span class="badge bg-secondary">Belum ditugaskan</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('validasi_dokumen', 'upps','label_short_for') !!}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.validasi-dokumen.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            Belum ada dokumen yang masuk proses validasi
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.validasi-dokumen') }}" class="btn btn-sm btn-info">
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
