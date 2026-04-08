{{-- resources/views/de/permohonan-banding/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Permohonan Banding')

@push('styles')
<style>
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
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
        color: white;
    }

    .priority-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    .priority-high {
        background-color: #dc3545;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Permohonan Banding</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-send-check"></i> Permohonan Banding
            </h4>
            <p class="text-muted mb-0">Kirim surat penerimaan atas permohonan banding dari PS/UPPS</p>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row row-cols-1 row-cols-md-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan Banding" :value="$stats['total']" description="Seluruh permohonan banding yang masuk" icon="file-earmark-text" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Belum Dikirim Penerimaan" :value="$stats['belum_terkirim']" description="Perlu segera mengirim penerimaan permohonan banding" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Penerimaan Telah Dikirim" :value="$stats['terkirim']" description="Surat penerimaan banding sudah dikirim ke PS" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    {{-- Filters & Content --}}
    <div class="row">

        {{-- Filter Sidebar --}}
        <div class="col-lg-3 mb-4">
            <div class="card filter-card">
                <div class="card-header border-0">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter & Pencarian</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('de.permohonan-banding') }}">
                        <div class="mb-3">
                            <label class="form-label text-white">Cari</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor / Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Status Penerimaan</label>
                            <select name="status_penerimaan" class="form-select">
                                <option value="">Semua</option>
                                <option value="belum_terkirim" {{ request('status_penerimaan') === 'belum_terkirim' ? 'selected' : '' }}>
                                    Belum Dikirim
                                </option>
                                <option value="terkirim" {{ request('status_penerimaan') === 'terkirim' ? 'selected' : '' }}>
                                    Sudah Dikirim
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white">Universitas</label>
                            <select name="university_id" class="form-select">
                                <option value="">Semua Universitas</option>
                                @foreach($universities as $id => $name)
                                <option value="{{ $id }}" {{ request('university_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

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

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search"></i> Terapkan
                            </button>
                            <a href="{{ route('de.permohonan-banding') }}" class="btn btn-outline-light">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Permohonan Banding</h5>
                        <span class="text-muted">Total: <strong>{{ $pengajuans->total() }}</strong></span>
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
                                    <th width="25%">Tanggal Banding Diajukan</th>
                                    <th width="20%">Status Penerimaan Banding</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $suratPenerimaan = $pengajuan->dokumen
                                ->firstWhere('jenis_dokumen', 'surat_penerimaan_banding_de');

                                $daysSinceBanding = $pengajuan->tanggal_permohonan_banding
                                ? floor(\Carbon\Carbon::parse($pengajuan->tanggal_permohonan_banding)->diffInDays(now()))
                                : 0;

                                $isUrgent = !$suratPenerimaan && $daysSinceBanding > 2;
                                @endphp
                                <tr class="{{ $isUrgent ? 'table-warning' : '' }}">
                                    <td>
                                        @if($isUrgent)
                                        <span class="priority-indicator priority-high" title="Sudah {{ $daysSinceBanding }} hari belum ditanggapi"></span>
                                        @endif
                                        {{ $pengajuans->firstItem() + $index }}
                                    </td>
                                    <td>
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_permohonan_banding)
                                        <small>
                                            {{ $pengajuan->tanggal_permohonan_banding->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                        <br>
                                        <small class="text-muted">({{ $daysSinceBanding }} hari lalu)</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($suratPenerimaan)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Telah Dikirim
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $suratPenerimaan->created_at->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                        @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-hourglass-split"></i> Belum Dikirim
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('de.permohonan-banding.show', $pengajuan->id) }}" class="btn btn-sm btn-primary action-btn" title="{{ $suratPenerimaan ? 'Lihat Detail' : 'Kirim Penerimaan' }}">
                                            <i class="bi bi-{{ $suratPenerimaan ? 'eye' : 'send' }}"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-white">
                        {{ $pengajuans->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                        <p class="text-muted mt-3">
                            @if(request()->filled('search') || request()->filled('status_penerimaan'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada permohonan banding yang masuk
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status_penerimaan'))
                        <a href="{{ route('de.permohonan-banding') }}" class="btn btn-sm btn-primary">
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
