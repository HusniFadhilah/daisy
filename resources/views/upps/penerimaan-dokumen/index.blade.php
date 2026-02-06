{{-- resources/views/upps/penerimaan-dokumen/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pengiriman Dokumen')

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
            <li class="breadcrumb-item active">Pengiriman Dokumen</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Pengiriman Dokumen
            </h4>
            <p class="text-muted mb-0">Kirim dan pantau dokumen akreditasi</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Pengiriman Dokumen</strong><br>
        Bukti penerimaan dokumen permohonan akreditasi program studi dapat dilihat pada daftar berikut<br>
    </div>

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Dokumen Harus Dikirim" :value="$stats['dokumen_harus_dikirim']" description="Total dokumen yang harus dikirim" icon="upload" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Total Draft Dokumen" :value="$stats['draft_dokumen']" description="Jumlah draft dokumen yang telah dikirim (menunggu validasi atau sedang diproses)" icon="file-earmark-text" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Total Dokumen Diproses" :value="$stats['dokumen_dikirim']" description="Jumlah dokumen yang telah selesai diproses" icon="send" iconBg="success-subtle" />
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
                        <h5 class="mb-0">Daftar Pengiriman Dokumen</h5>
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
                                    <th width="30%">Permohonan Akreditasi</th>
                                    <th width="25%">Tanggal Pengiriman Dokumen</th>
                                    <th width="25%">Status Pengiriman Dokumen</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $hasDokumen = $pengajuan->dokumen->isNotEmpty();
                                @endphp
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <p>{{ $pengajuan->judul_short }}</p>
                                        <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                        <br>
                                        <small class="text-muted">Dibuat pada: {{ \App\Libraries\Date::tglIndo($pengajuan->created_at) }}</small>
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_draft_borang)
                                        <small>{{ $pengajuan->tanggal_draft_borang->format('d M Y') }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->tanggal_draft_borang->diffForHumans() }}
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('draft_borang', 'upps','label_short_for') !!}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}" class="btn btn-primary" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- @php
                                            $canUpload = in_array($pengajuan->status, [
                                            \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                                            \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                                            ]);
                                            @endphp

                                            @if($canUpload) --}}
                                            <a href="{{ route('pengajuan.borang-online', $pengajuan->id) }}" class="btn btn-success">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="{{ route('upps.penerimaan-dokumen.upload.form', $pengajuan->id) }}" class="btn btn-info">
                                                <i class="bi bi-upload"></i>
                                            </a>
                                            {{-- @endif --}}
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
                            Belum ada dokumen yang perlu diupload
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.penerimaan-dokumen') }}" class="btn btn-sm btn-info">
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
