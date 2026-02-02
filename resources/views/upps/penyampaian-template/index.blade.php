{{-- resources/views/upps/penyampaian-template/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Formulir Pembayaran dan Template Dokumen')

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

    .badge-status {
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            <li class="breadcrumb-item active">Formulir Pembayaran dan Template Dokumen</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-arrow-down"></i> Formulir Pembayaran dan Template Dokumen
            </h4>
            <p class="text-muted mb-0">Formulir dan template dokumen akreditasi dari LAMDEPILAR</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        {{-- <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Formulir Pembayaran dan Template Dokumen</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
        <small class="opacity-75">Jumlah formulir dan template dokumen yang telah diterima</small>
    </div>
    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
        <i class="bi bi-file-earmark-arrow-down"></i>
    </div>
</div>
</div>
</div>
</div> --}}

<div class="col-lg-4 col-md-6 mb-3">
    <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="card-body text-white">
            <h6 class="mb-2 opacity-75">Menunggu Template</h6>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 fw-bold">{{ $stats['menunggu'] }}</h2>
                    <small class="opacity-75">Formulir pembayaran dan template dokumen sedang dalam proses pengiriman oleh LAMDEPILAR</small>
                </div>
                <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-4 col-md-6 mb-3">
    <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
        <div class="card-body text-white">
            <h6 class="mb-2 opacity-75">Formulir Pembayaran dan Template Dokumen Diterima</h6>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 fw-bold">{{ $stats['diterima'] }}</h2>
                    <small class="opacity-75">Jumlah formulir dan template dokumen yang telah diterima</small>
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
                <form method="GET" action="{{ route('upps.penyampaian-template') }}">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label text-white">Cari Permohonan</label>
                        <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label text-white">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM ? 'selected' : '' }}>
                                Menunggu Template
                            </option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM ? 'selected' : '' }}>
                                Template Diterima
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
                        <a href="{{ route('upps.penyampaian-template') }}" class="btn btn-outline-light">
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
                    <h5 class="mb-0">Daftar Formulir Pembayaran dan Template Dokumen</h5>
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
                                <th width="20%">Permohonan</th>
                                <th width="20%">Tanggal Dikirim</th>
                                <th width="25%">Formulir dan Template Dokumen</th>
                                <th width="25%">Status Formulir dan Template</th>
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
                                    <p>{{ $pengajuan->judul_short }}</p>
                                    <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                    <br>
                                    <small class="text-muted">
                                        Dibuat pada: {{ $pengajuan->created_at->format('d M Y') }}
                                    </small>
                                </td>
                                <td>
                                    @if($pengajuan->tanggal_template_led_dikirim)
                                    <small>{{ $pengajuan->tanggal_template_led_dikirim->format('d M Y') }}</small>
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
                                            <span class="text-success">Template Dokumen</span>
                                            @else
                                            <i class="bi bi-x-circle-fill text-muted"></i>
                                            <span class="text-muted">Template Dokumen</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('borang_template','upps','label_short_for') !!}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('upps.penyampaian-template.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                        Belum ada pengiriman formulir dan template dokumen
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
