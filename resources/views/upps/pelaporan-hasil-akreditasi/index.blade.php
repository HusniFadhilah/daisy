{{-- resources/views/upps/pelaporan-hasil-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaporan Hasil Akreditasi')

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
            <li class="breadcrumb-item active">Pelaporan Hasil Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-megaphone"></i> Pelaporan Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Pelaporan hasil akreditasi kepada pemangku kepentingan</p>
        </div>
    </div>

    <!-- Success Alert -->
    @if($stats['total'] > 0)
    <div class="alert alert-success alert-permanent border-start border-4 border-success mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-megaphone-fill fs-1 me-3 text-success"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-check-circle-fill"></i> Hasil Akreditasi Telah Dilaporkan
                </h5>
                <p class="mb-2">
                    Total <strong class="fs-5">{{ $stats['total'] }}</strong> hasil akreditasi
                    telah dilaporkan kepada pemangku kepentingan.
                </p>
                @if($stats['selesai'] > 0)
                <div class="alert alert-light border border-success mb-0">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <strong>{{ $stats['selesai'] }}</strong> proses akreditasi telah selesai seluruhnya.
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Hasil Dilaporkan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Pelaporan hasil</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-megaphone"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Arsip Disimpan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['arsip_disimpan'] }}</h2>
                            <small class="opacity-75">Dokumen diarsipkan</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-archive"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Proses Selesai</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h2>
                            <small class="opacity-75">Akreditasi selesai</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-patch-check"></i>
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
                    <form method="GET" action="{{ route('upps.pelaporan-hasil-akreditasi') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Peringkat -->
                        <div class="mb-3">
                            <label class="form-label text-white">Peringkat Hasil Akhir</label>
                            <select name="peringkat" class="form-select">
                                <option value="">Semua Peringkat</option>
                                <option value="Unggul" {{ request('peringkat') == 'Unggul' ? 'selected' : '' }}>
                                    Unggul
                                </option>
                                <option value="Baik Sekali" {{ request('peringkat') == 'Baik Sekali' ? 'selected' : '' }}>
                                    Baik Sekali
                                </option>
                                <option value="Baik" {{ request('peringkat') == 'Baik' ? 'selected' : '' }}>
                                    Baik
                                </option>
                                <option value="Tidak Terakreditasi" {{ request('peringkat') == 'Tidak Terakreditasi' ? 'selected' : '' }}>
                                    Tidak Terakreditasi
                                </option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN ? 'selected' : '' }}>
                                    Hasil Dilaporkan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN ? 'selected' : '' }}>
                                    Arsip Disimpan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SELESAI }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SELESAI ? 'selected' : '' }}>
                                    Selesai
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
                            <a href="{{ route('upps.pelaporan-hasil-akreditasi') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Pelaporan Hasil Akreditasi</h5>
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
                                    <th width="20%">Peringkat Akhir</th>
                                    <th width="20%">Tanggal Pelaporan</th>
                                    <th width="20%">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $peringkatAkhir = $pengajuan->peringkat_hasil_banding ?? $pengajuan->peringkat_hasil;
                                $nilaiAkhir = $pengajuan->nilai_akhir_banding ?? $pengajuan->nilai_akhir;

                                $badgeClass = match($peringkatAkhir) {
                                'Unggul' => 'bg-warning text-dark',
                                'Baik Sekali' => 'bg-success',
                                'Baik' => 'bg-info',
                                'Tidak Terakreditasi' => 'bg-danger',
                                default => 'bg-secondary',
                                };
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
                                        @if($peringkatAkhir)
                                        <span class="badge {{ $badgeClass }} peringkat-badge">
                                            @if($peringkatAkhir === 'Unggul')
                                            <i class="bi bi-star-fill"></i>
                                            @elseif($peringkatAkhir === 'Baik Sekali')
                                            <i class="bi bi-award-fill"></i>
                                            @elseif($peringkatAkhir === 'Baik')
                                            <i class="bi bi-check-circle-fill"></i>
                                            @endif
                                            {{ $peringkatAkhir }}
                                        </span>
                                        @if($nilaiAkhir)
                                        <br>
                                        <small class="text-muted">Nilai: <strong>{{ $nilaiAkhir }}</strong></small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            {{ $pengajuan->tanggal_pelaporan_hasil
                                                        ? $pengajuan->tanggal_pelaporan_hasil->format('d M Y')
                                                        : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $pengajuan->status_badge_class }}">
                                            {{ $pengajuan->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.pelaporan-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            @if(request()->filled('search') || request()->filled('status') || request()->filled('peringkat'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada pelaporan hasil akreditasi
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('peringkat'))
                        <a href="{{ route('upps.pelaporan-hasil-akreditasi') }}" class="btn btn-sm btn-info">
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
