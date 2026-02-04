{{-- resources/views/upps/penyampaian-hasil-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penyampaian Hasil Akreditasi')

@push('styles')
<style>
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
            <li class="breadcrumb-item active">Penyampaian Hasil Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-trophy"></i> Penyampaian Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Hasil akreditasi program studi dari LAMDEPILAR</p>
        </div>
    </div>

    <!-- Congratulations Alert (if any) -->
    @if($stats['total'] > 0)
    <div class="alert alert-success alert-permanent border-start border-4 border-success mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-trophy-fill fs-1 me-3 text-success"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-check-circle-fill"></i> Selamat!
                </h5>
                <p class="mb-2">
                    Program studi Anda telah menyelesaikan proses akreditasi.
                    Total <strong class="fs-5">{{ $stats['total'] }}</strong> hasil akreditasi telah disampaikan.
                </p>
                @if($stats['unggul'] > 0)
                <div class="alert alert-light border border-success mb-0">
                    <i class="bi bi-star-fill text-warning"></i>
                    <strong>{{ $stats['unggul'] }}</strong> program studi meraih peringkat <strong class="text-success">Unggul</strong>!
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Hasil Akreditasi" :value="$stats['total']" description="Sudah disampaikan" icon="trophy" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Proses Selesai" :value="$stats['selesai']" description="Akreditasi selesai" icon="check-circle" iconBg="success-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Peringkat Unggul" :value="$stats['unggul']" description="Prodi unggul" icon="star-fill" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Baik Sekali" :value="$stats['baik_sekali']" description="Prodi baik sekali" icon="award" iconBg="info-subtle" />
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
                    <form method="GET" action="{{ route('upps.penyampaian-hasil-akreditasi') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Peringkat -->
                        <div class="mb-3">
                            <label class="form-label text-white">Peringkat Akreditasi</label>
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
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM ? 'selected' : '' }}>
                                    Hasil Disampaikan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH ? 'selected' : '' }}>
                                    Masa Sanggah
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN ? 'selected' : '' }}>
                                    Hasil Ditetapkan
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
                            <a href="{{ route('upps.penyampaian-hasil-akreditasi') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Hasil Akreditasi</h5>
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
                                    <th width="20%">Peringkat</th>
                                    <th width="15%">Nilai</th>
                                    <th width="20%">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $peringkatSaatIni = $pengajuan->peringkat_saat_ini;
                                $nilaiSaatIni = $pengajuan->nilai_saat_ini;
                                $badgeClass = $pengajuan->getPeringkatBadgeClass();
                                $icon = $pengajuan->getPeringkatIcon();
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
                                        @if($peringkatSaatIni)
                                        <span class="badge {{ $badgeClass }} peringkat-badge">
                                            <i class="{{ $icon }}"></i>
                                            {{ $peringkatSaatIni }}
                                        </span>
                                        @if($nilaiSaatIni)
                                        <br>
                                        <small class="text-muted">Nilai: <strong>{{ $nilaiSaatIni }}</strong></small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pengajuan->nilai_akhir)
                                        <strong class="text-primary">{{ $pengajuan->nilai_akhir }}</strong>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $pengajuan->status_badge_class }}">
                                            {{ $pengajuan->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.penyampaian-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            Belum ada hasil akreditasi yang disampaikan
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('peringkat'))
                        <a href="{{ route('upps.penyampaian-hasil-akreditasi') }}" class="btn btn-sm btn-info">
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
