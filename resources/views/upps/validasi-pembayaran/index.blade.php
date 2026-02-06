{{-- resources/views/upps/validasi-pembayaran/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran')

@push('styles')
<style>
    .stat-card-link {
        text-decoration: none;
    }

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
            <li class="breadcrumb-item active">Validasi Pembayaran</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-credit-card"></i> Validasi Pembayaran
            </h4>
            <p class="text-muted mb-0">Invoice dan status pembayaran akreditasi</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Validasi Pembayaran</strong><br>
        Validasi pembayaran permohonan akreditasi program studi dapat dilihat pada daftar berikut<br>
    </div>

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <a class="stat-card-link" href="{{ route('upps.validasi-pembayaran', array_merge($baseQuery, ['quick' => 'menunggu_pembayaran'])) }}">
    <x-stat-card title="Total Invoice" :value="$stats['menunggu_pembayaran']" description="Total invoice yang perlu dibayar" icon="receipt" iconBg="danger-subtle" />
    </a>
</div>
<div class="col mb-3">
    <a class="stat-card-link" href="{{ route('upps.validasi-pembayaran', array_merge($baseQuery, ['quick' => 'total_invoice_dibayar'])) }}">
        <x-stat-card title="Total Invoice Dibayar" :value="$stats['total_invoice_dibayar']" description="Total invoice yang telah dibayar" icon="hourglass-split" iconBg="primary-subtle" />
    </a>
</div>
<div class="col mb-3">
    <a class="stat-card-link" href="{{ route('upps.validasi-pembayaran', array_merge($baseQuery, ['quick' => 'total_invoice_belum_tervalidasi'])) }}">
        <x-stat-card title="Total Invoice Belum Tervalidasi" :value="$stats['total_invoice_belum_tervalidasi']" description="Telah upload formulir & bukti pembayaran, menunggu validasi" icon="clock-history" iconBg="warning-subtle" />
    </a>
</div>
<div class="col mb-3">
    <a class="stat-card-link" href="{{ route('upps.validasi-pembayaran', array_merge($baseQuery, ['quick' => 'terverifikasi'])) }}">
        <x-stat-card title="Total Invoice Telah Tervalidasi" :value="$stats['terverifikasi']" description="Pembayaran telah divalidasi" icon="check-circle" iconBg="success-subtle" />
    </a>
</div>
</div> --}}

<!-- Filters & Content -->
<div class="row">
    <!-- Filters Sidebar -->

    <!-- Main Content -->
    <div class="col-lg-12">
        @if(request('quick'))
        <div class="alert alert-info alert-permanent mb-3 rounded-0 d-flex align-items-center">
            <span>
                Filter aktif: <strong>{{ request('quick') }}</strong>
            </span>

            <a class="ms-auto text-decoration-none" href="{{ route('upps.validasi-pembayaran', request()->except(['quick','page'])) }}">
                Hapus filter
            </a>
        </div>
        @endif
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Invoice Pembayaran</h5>
                    <div>
                        <span class="text-muted">Total: <strong>{{ $pembayarans->total() }}</strong></span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($pembayarans->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Nomor Invoice</th>
                                <th width="20%">Program Studi</th>
                                <th width="12%">Jumlah</th>
                                <th width="12%">Jatuh Tempo</th>
                                <th width="15%">Status Pembayaran</th>
                                <th width="11%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pembayarans as $index => $pembayaran)
                            <tr>
                                <td>{{ $pembayarans->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $pembayaran->nomor_invoice }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->created_at->format('d M Y') }}
                                    </small>
                                </td>
                                <td>
                                    <strong>{{ $pembayaran->pengajuan->studyProgram->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pembayaran->pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                    </small>
                                </td>
                                <td>
                                    <strong class="text-success">
                                        Rp {{ number_format($pembayaran->jumlah_pembayaran, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td>
                                    @if($pembayaran->tanggal_jatuh_tempo)
                                    {{ $pembayaran->tanggal_jatuh_tempo->format('d M Y') }}
                                    <br>
                                    @if($pembayaran->tanggal_jatuh_tempo < now() && in_array($pembayaran->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang']))
                                        <span class="badge bg-danger">Terlambat</span>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                </td>
                                <td>
                                    @php
                                    $statusConfig = [
                                    'menunggu_pembayaran' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Perlu Memproses Pembayaran'],
                                    'menunggu_verifikasi' => ['class' => 'info', 'icon' => 'clock-history', 'text' => 'Menunggu Validasi'],
                                    'terverifikasi' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Tervalidasi'],
                                    'upload_ulang' => ['class' => 'secondary', 'icon' => 'arrow-repeat', 'text' => 'Upload Ulang'],
                                    'ditolak' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Ditolak'],
                                    ];
                                    $status = $statusConfig[$pembayaran->status_pembayaran] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => 'Unknown'];
                                    @endphp
                                    <span class="badge bg-{{ $status['class'] }}">
                                        <i class="bi bi-{{ $status['icon'] }}"></i>
                                        {{ $status['text'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('upps.validasi-pembayaran.show', $pembayaran->id) }}" class="btn btn-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if(in_array($pembayaran->status_pembayaran, ['menunggu_pembayaran', 'upload_ulang']))
                                        <a href="{{ route('upps.validasi-pembayaran.upload.form', $pembayaran->id) }}" class="btn btn-success" title="Upload Formulir & Bukti Pembayaran">
                                            <i class="bi bi-upload"></i>
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
                    {{ $pembayarans->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                    <p class="text-muted mt-3">
                        @if(request()->filled('search') || request()->filled('status'))
                        Tidak ada data yang sesuai dengan filter
                        @else
                        Belum ada invoice pembayaran
                        @endif
                    </p>
                    @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('upps.validasi-pembayaran') }}" class="btn btn-sm btn-info">
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
