{{-- resources/views/upps/penyampaian-template/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Formulir Pembayaran dan Templat Dokumen')

@push('styles')
<style>
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

    .dokumen-indicator {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
    }

    .dokumen-indicator i {
        font-size: 14px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Formulir Pembayaran dan Templat Dokumen</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark"></i> Formulir Pembayaran dan Templat Dokumen
            </h4>
            <p class="text-muted mb-0">Formulir dan templat dokumen akreditasi dari LAMDEPILAR</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Formulir Pembayaran dan Templat Dokumen</strong><br>
        Formulir pembayaran dan templat dokumen akreditasi program studi tersedia pada link berikut<br>
        Catatan:
        <ol>
            <li>Program studi dimohon dapat melakukan pengisian formulir dan melakukan pembayaran</li>
            <li>Setelah melakukan pembayaran, program studi dapat melakukan pengisian dokumen akreditasi</li>
        </ol>
        <br>
        Formulir pembayaran dan templat dokumen dapat dilihat pada daftar berikut
    </div>

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Menunggu Templat" :value="$stats['menunggu']" description="Formulir pembayaran dan templat dokumen sedang dalam proses pengiriman oleh LAMDEPILAR" icon="hourglass-split" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Formulir Pembayaran dan Templat Dokumen Diterima" :value="$stats['diterima']" description="Jumlah formulir dan templat dokumen yang telah diterima" icon="check-circle" iconBg="success-subtle" />
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
                        <h5 class="mb-0">Daftar Formulir Pembayaran dan Templat Dokumen</h5>
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
                                    <th width="20%">Tanggal Dikirim</th>
                                    <th width="25%">Formulir dan Templat Dokumen</th>
                                    <th width="25%">Status Formulir dan Templat</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $hasTemplateLed = $pengajuan->dokumen
                                ->where('jenis_dokumen', 'borang_template')
                                ->where('is_latest', true)
                                ->isNotEmpty();

                                $hasFormulirPembayaran = $pengajuan->dokumen
                                ->where('jenis_dokumen', 'template_formulir_pembayaran')
                                ->where('is_latest', true)
                                ->isNotEmpty();
                                @endphp
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_template_led_dikirim)
                                        <small>{{ $pengajuan->tanggal_template_led_dikirim->locale('id')->translatedFormat('d M Y') }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->tanggal_template_led_dikirim->diffForHumans() }}
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="dokumen-indicator">
                                                @if($hasFormulirPembayaran)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                                <span class="text-success">Formulir Pembayaran</span>
                                                @else
                                                <i class="bi bi-x-circle-fill text-muted"></i>
                                                <span class="text-muted">Formulir Pembayaran</span>
                                                @endif
                                            </div>
                                            <div class="dokumen-indicator">
                                                @if($hasTemplateLed)
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                                <span class="text-success">Templat Dokumen</span>
                                                @else
                                                <i class="bi bi-x-circle-fill text-muted"></i>
                                                <span class="text-muted">Templat Dokumen</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('borang_template','upps','label_short_for') !!}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.penyampaian-template.show', $pengajuan->id) }}" class="btn btn-primary btn-sm" title="Lihat Detail">
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
                            Belum ada pengiriman formulir dan templat dokumen
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.penyampaian-template') }}" class="btn btn-sm btn-info">
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
