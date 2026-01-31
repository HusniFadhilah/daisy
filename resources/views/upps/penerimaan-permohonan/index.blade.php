{{-- resources/views/upps/penerimaan-permohonan/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penerimaan Permohonan Akreditasi')

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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penerimaan Permohonan Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-envelope-check"></i> Penerimaan Permohonan Akreditasi
            </h4>
            <p class="text-muted mb-0">Daftar surat penerimaan permohonan akreditasi dari LAMDEPILAR</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        {{-- <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Penerimaan Permohonan Akreditasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
        <small class="opacity-75">Jumlah total penerimaan permohonan akreditasi di UPPS Anda</small>
    </div>
    <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
        <i class="bi bi-envelope-check"></i>
    </div>
</div>
</div>
</div>
</div> --}}

<div class="col-lg-4 col-md-6 mb-3">
    <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
        <div class="card-body text-white">
            <h6 class="mb-2 opacity-75">Menunggu Penerimaan Permohonan Akreditasi</h6>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 fw-bold">{{ $stats['diterima'] }}</h2>
                    <small class="opacity-75">Penerimaan permohonan akreditasi sedang diproses oleh LAMDEPILAR</small>
                </div>
                <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;" class="px-2">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-lg-4 col-md-6 mb-3">
    <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
        <div class="card-body text-white">
            <h6 class="mb-2 opacity-75">Permohonan Akreditasi Diterima</h6>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 fw-bold">{{ $stats['surat_dikirim'] }}</h2>
                    <small class="opacity-75">Penerimaan permohonan akreditasi telah selesai diproses oleh LAMDEPILAR</small>
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
                <form method="GET" action="{{ route('upps.penerimaan-permohonan') }}">
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
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA ? 'selected' : '' }}>
                                Menunggu Permohonan Akreditasi Diterima
                            </option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM ? 'selected' : '' }}>
                                Permohonan Akreditasi Diterima
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
                        <a href="{{ route('upps.penerimaan-permohonan') }}" class="btn btn-outline-light">
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
                    <h5 class="mb-0">Daftar Penerimaan Permohonan Akreditasi</h5>
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
                                <th width="20%">Program Studi</th>
                                <th width="25%">Tanggal Permohonan Akreditasi Diterima</th>
                                <th width="25%">Status Penerimaan Permohonan Akreditasi</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuans as $index => $pengajuan)
                            @php
                            $suratPenerimaan = $pengajuan->dokumen->first();
                            $daysSinceTerima = $pengajuan->tanggal_surat_permohonan_diterima
                            ? floor(\Carbon\Carbon::parse($pengajuan->tanggal_surat_permohonan_diterima)->diffInDays(now()))
                            : 0;
                            @endphp
                            <tr>
                                <td>{{ $pengajuans->firstItem() + $index }}</td>
                                <td>
                                    <p>{{ $pengajuan->judul }}</p>
                                    <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                    <br>
                                    <small class="text-muted">
                                        Dibuat pada: {{ $pengajuan->created_at->format('d M Y') }}
                                    </small>
                                </td>
                                <td>
                                    <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                    </small>
                                    <br><small>{{ $pengajuan->studyProgram->university->name ?? '-' }}</small>
                                </td>
                                <td>
                                    @if($pengajuan->tanggal_surat_permohonan_diterima)
                                    <small>
                                        {{ $pengajuan->tanggal_surat_permohonan_diterima->format('d M Y') }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        ({{ $daysSinceTerima }} hari lalu)
                                    </small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('surat_penerimaan_de','upps','label_short_for') !!}
                                    <br>
                                    @if($suratPenerimaan)
                                    <small class="text-muted">
                                        {{ $suratPenerimaan->created_at->format('d M Y') }}
                                    </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('upps.penerimaan-permohonan.show', $pengajuan->id) }}" class="btn btn-info" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @php
                                        $dokumen = $pengajuan->dokumen->first();
                                        @endphp

                                        @if($dokumen)
                                        <a href="{{ route('upps.penerimaan-permohonan.download', $pengajuan->id) }}" class="btn btn-success" title="Lihat Surat Penerimaan">
                                            <i class="bi bi-file-earmark-pdf"></i>
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
                    {{ $pengajuans->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                    <p class="text-muted mt-3">
                        @if(request()->filled('search') || request()->filled('status'))
                        Tidak ada data yang sesuai dengan filter
                        @else
                        Belum ada penerimaan permohonan akreditasi
                        @endif
                    </p>
                    @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('upps.penerimaan-permohonan') }}" class="btn btn-sm btn-info">
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
