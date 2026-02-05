{{-- resources/views/upps/surat-permohonan/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Permohonan Akreditasi')

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
            <li class="breadcrumb-item active">Permohonan Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-envelope"></i> Permohonan Akreditasi
            </h4>
            <p class="text-muted mb-0">Kelola permohonan akreditasi program studi</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-gear"></i> Menu Permohonan Akreditasi
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Download Template -->
                        <div class="col-md-4">
                            <div class="d-grid">
                                <button type="button" class="btn btn-md btn-info" data-bs-toggle="modal" data-bs-target="#modalDownloadTemplate">
                                    <i class="bi bi-download"></i>
                                    <br>
                                    <span class="small">Download Template Permohonan Akreditasi</span>
                                </button>
                            </div>
                        </div>

                        <!-- Buat Permohonan -->
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('upps.surat-permohonan.create') }}" class="btn btn-md btn-primary">
                                    <i class="bi bi-file-earmark-plus"></i>
                                    <br>
                                    <span class="small">Buat Permohonan Akreditasi</span>
                                </a>
                            </div>
                        </div>

                        <!-- Simpan sebagai Draft (Optional - if needed) -->
                        <div class="col-md-4">
                            <div class="d-grid">
                                <a href="{{ route('upps.surat-permohonan.create') }}?draft=true" class="btn btn-md btn-secondary">
                                    <i class="bi bi-save"></i>
                                    <br>
                                    <span class="small">Simpan Draft Permohonan/Kirim</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan Akreditasi" :value="$stats['total']" description="Total Permohonan Akreditasi keseluruhan" icon="file-earmark-text" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Permohonan Akreditasi Telah Dikirim, tetapi Sedang Proses Ditanggapi" :value="$stats['dikirim']" description="Permohonan Akreditasi telah dikirim, tetapi sedang proses ditanggapi oleh LAMDEPILAR" icon="clock" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Permohonan Akreditasi Telah Ditanggapi" :value="$stats['diterima']" description="Permohonan Akreditasi telah dikirim, dan telah ditanggapi oleh LAMDEPILAR" icon="check-circle" iconBg="success-subtle" />
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
                    <form method="GET" action="{{ route('upps.surat-permohonan') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Permohonan</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM ? 'selected' : '' }}>
                                    Menunggu Tanggapan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA ? 'selected' : '' }}>
                                    Diterima DE
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK ? 'selected' : '' }}>
                                    Ditolak DE
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
                            <a href="{{ route('upps.surat-permohonan') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Permohonan Akreditasi</h5>
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
                                    <th width="25%">Tanggal Permohonan Dikirim</th>
                                    <th width="25%">Status Permohonan Akreditasi</th>
                                    <th width="5%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <p>{{ $pengajuan->judul_short }}</p>
                                        <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                        <br>
                                        <small class="text-muted">Dibuat pada: {{ \App\Libraries\Date::tglIndo($pengajuan->created_at) }}</small>
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_surat_permohonan_dikirim)
                                        <small>{{ $pengajuan->tanggal_surat_permohonan_dikirim->format('d M Y') }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->tanggal_surat_permohonan_dikirim->diffForHumans() }}
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('surat_permohonan_ps','upps','label_short_for') !!}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if($pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_DRAFT)
                                            <a href="{{ route('upps.surat-permohonan.edit', $pengajuan->id) }}" class="btn btn-warning" title="Edit Draft">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @else
                                            {{-- Tombol Lihat Detail untuk yang sudah dikirim --}}
                                            <a href="{{ route('upps.surat-permohonan.show', $pengajuan->id) }}" class="btn btn-info" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @endif

                                            @php
                                            $dokumen = $pengajuan->dokumen->first();
                                            @endphp

                                            @if($dokumen && $pengajuan->status != \App\Models\PengajuanAkreditasi::STATUS_DRAFT)
                                            <a href="{{ route('upps.surat-permohonan.download', $pengajuan->id) }}" class="btn btn-success" title="Lihat File Permohonan Akreditasi">
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
                            Belum ada permohonan akreditasi yang dikirim
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.surat-permohonan') }}" class="btn btn-sm btn-info">
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

@include('upps.surat-permohonan.components.modal-download-template')
@endsection
