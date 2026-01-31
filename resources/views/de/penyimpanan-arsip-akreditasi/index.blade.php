{{-- resources/views/de/penyimpanan-arsip-akreditasi/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Penyimpanan Arsip Pelaksanaan Akreditasi')

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

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    .action-btn {
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        transform: scale(1.05);
    }

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .badge-peringkat {
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-completed {
        animation: pulse-success 2s infinite;
    }

    @keyframes pulse-success {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penyimpanan Arsip Pelaksanaan Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-archive"></i> Penyimpanan Arsip Pelaksanaan Akreditasi</h4>
            <p class="text-muted mb-0">Kelola penyimpanan arsip dan finalisasi proses akreditasi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Hasil Dilaporkan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Siap atau telah diarsipkan</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-archive"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Belum Diarsipkan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['belum_diarsipkan'] }}</h2>
                            <small class="opacity-75">Menunggu penyimpanan arsip</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Telah Diarsipkan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['sudah_diarsipkan'] }}</h2>
                            <small class="opacity-75">Arsip tersimpan</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-archive-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Proses Selesai</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h2>
                            <small class="opacity-75">Akreditasi selesai sempurna</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribusi Peringkat -->
    {{-- <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Unggul</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['unggul'] }}</h2>
</div>
<div class="stat-icon" style="background: rgba(255,255,255,0.2);">
    <i class="bi bi-star-fill"></i>
</div>
</div>
</div>
</div>
</div>

<div class="col-lg-3 col-md-6 mb-3">
    <div class="card stat-card p-0" style="background: linear-gradient(135deg, #96fbc4 0%, #f9f586 100%);">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Baik Sekali</h6>
                    <h2 class="mb-0 fw-bold">{{ $stats['baik_sekali'] }}</h2>
                </div>
                <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="bi bi-star"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 mb-3">
    <div class="card stat-card p-0" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Baik</h6>
                    <h2 class="mb-0 fw-bold">{{ $stats['baik'] }}</h2>
                </div>
                <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="bi bi-star-half"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-3 col-md-6 mb-3">
    <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 opacity-75">Tidak Terakreditasi</h6>
                    <h2 class="mb-0 fw-bold">{{ $stats['tidak_terakreditasi'] }}</h2>
                </div>
                <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>
</div> --}}

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
                <form method="GET" action="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi') }}">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label text-white">Cari Pengajuan</label>
                        <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="mb-3">
                        <label class="form-label text-white">Status Arsip</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN ? 'selected' : '' }}>
                                Belum Diarsipkan
                            </option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN ? 'selected' : '' }}>
                                Telah Diarsipkan
                            </option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SELESAI }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SELESAI ? 'selected' : '' }}>
                                Proses Selesai
                            </option>
                        </select>
                    </div>

                    <!-- Peringkat Filter -->
                    <div class="mb-3">
                        <label class="form-label text-white">Peringkat</label>
                        <select name="peringkat" class="form-select">
                            <option value="">Semua Peringkat</option>
                            <option value="Unggul" {{ request('peringkat') == 'Unggul' ? 'selected' : '' }}>Unggul</option>
                            <option value="Baik Sekali" {{ request('peringkat') == 'Baik Sekali' ? 'selected' : '' }}>Baik Sekali</option>
                            <option value="Baik" {{ request('peringkat') == 'Baik' ? 'selected' : '' }}>Baik</option>
                            <option value="Tidak Terakreditasi" {{ request('peringkat') == 'Tidak Terakreditasi' ? 'selected' : '' }}>Tidak Terakreditasi</option>
                        </select>
                    </div>

                    <!-- University -->
                    <div class="mb-3">
                        <label class="form-label text-white">Universitas</label>
                        <select name="university_id" class="form-select">
                            <option value="">Semua Universitas</option>
                            @foreach($universities as $univ)
                            <option value="{{ $univ->id }}" {{ request('university_id') == $univ->id ? 'selected' : '' }}>
                                {{ $univ->name }}
                            </option>
                            @endforeach
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
                        <a href="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi') }}" class="btn btn-outline-light">
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
                                <th width="15%">Nomor Pengajuan</th>
                                <th width="20%">Program Studi</th>
                                <th width="10%">Peringkat</th>
                                <th width="8%">Skor</th>
                                <th width="12%">Status</th>
                                <th width="10%">Tgl Arsip</th>
                                <th width="10%">Dokumen</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuans as $index => $pengajuan)
                            @php
                            $peringkat = $pengajuan->peringkat_final;
                            $skor = $pengajuan->skor_final;

                            $badgeClass = match($peringkat) {
                            'Unggul' => 'success',
                            'Baik Sekali' => 'primary',
                            'Baik' => 'info',
                            'Tidak Terakreditasi' => 'danger',
                            default => 'secondary'
                            };

                            $totalDocs = $pengajuan->dokumen->count();
                            @endphp
                            <tr>
                                <td>{{ $pengajuans->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $pengajuan->tahun_akreditasi }}</small>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->studyProgram->university->name ?? '-' }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-peringkat bg-{{ $badgeClass }}">
                                        {{ $peringkat }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ number_format($skor, 2) }}</strong>
                                </td>
                                <td>
                                    @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_SELESAI)
                                    <span class="badge bg-success status-completed">
                                        <i class="bi bi-check-circle-fill"></i> Selesai
                                    </span>
                                    @elseif($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_ARSIP_DISIMPAN)
                                    <span class="badge bg-info">
                                        <i class="bi bi-archive-fill"></i> Diarsipkan
                                    </span>
                                    @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-hourglass-split"></i> Belum
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($pengajuan->tanggal_penyimpanan)
                                    <small>{{ $pengajuan->tanggal_penyimpanan->format('d M Y') }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-files"></i> {{ $totalDocs }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('de.penyimpanan-arsip-pelaksanaan-akreditasi.download-all', $pengajuan->id) }}" class="btn btn-secondary action-btn" title="Download All">
                                            <i class="bi bi-download"></i>
                                        </a>
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
                    <p class="text-muted mt-3">Tidak ada arsip akreditasi</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
