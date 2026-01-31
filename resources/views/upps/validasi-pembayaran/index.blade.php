{{-- resources/views/upps/validasi-pembayaran/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran')

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

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

    <!-- Statistics Cards -->
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Invoice</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['menunggu_pembayaran'] }}</h2>
                            <small class="opacity-75">Total invoice yang perlu dibayar</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Invoice Dibayar</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total_invoice_dibayar'] }}</h2>
                            <small class="opacity-75">Total invoice yang telah dibayar</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Invoice Belum Tervalidasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total_invoice_belum_tervalidasi'] }}</h2>
                            <small class="opacity-75">Telah upload formulir & bukti pembayaran, menunggu validasi</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Invoice Telah Tervalidasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['terverifikasi'] }}</h2>
                            <small class="opacity-75">Pembayaran telah divalidasi</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card filter-card">
                <div class="card-header border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Filter & Pencarian
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('upps.validasi-pembayaran') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari</label>
                            <input type="text" name="search" class="form-control" placeholder="Invoice/Nomor permohonan..." value="{{ request('search') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Pembayaran</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="menunggu_pembayaran" {{ request('status') == 'menunggu_pembayaran' ? 'selected' : '' }}>
                                    Menunggu Pembayaran
                                </option>
                                <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>
                                    Menunggu Validasi
                                </option>
                                <option value="terverifikasi" {{ request('status') == 'terverifikasi' ? 'selected' : '' }}>
                                    Tervalidasi
                                </option>
                                <option value="upload_ulang" {{ request('status') == 'upload_ulang' ? 'selected' : '' }}>
                                    Upload Ulang
                                </option>
                                <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>
                                    Ditolak
                                </option>
                            </select>
                        </div>

                        <!-- Tahun -->
                        <div class="mb-3">
                            <label class="form-label text-white">Tahun Akreditasi</label>
                            <select name="tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('upps.validasi-pembayaran') }}" class="btn btn-outline-light">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
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
                                    <th width="15%">Status</th>
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
                                        'menunggu_pembayaran' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Menunggu Pembayaran'],
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
