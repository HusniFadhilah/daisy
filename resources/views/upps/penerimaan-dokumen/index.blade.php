{{-- resources/views/upps/penerimaan-dokumen/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penerimaan Dokumen')

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
            <li class="breadcrumb-item active">Penerimaan Dokumen</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text"></i> Penerimaan Dokumen
            </h4>
            <p class="text-muted mb-0">Upload dan tracking dokumen akreditasi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Dokumen</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Semua dokumen</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Belum Upload</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['belum_upload'] }}</h2>
                            <small class="opacity-75">Perlu upload</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-upload"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Draft Dikirim</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['draft_dikirim'] }}</h2>
                            <small class="opacity-75">Menunggu DE</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-send"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Draft Diterima</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['draft_diterima'] }}</h2>
                            <small class="opacity-75">Diterima DE</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Dalam Validasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['dalam_validasi'] }}</h2>
                            <small class="opacity-75">Sedang validasi</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #868f96 0%, #596164 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Perlu Revisi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['perlu_revisi'] }}</h2>
                            <small class="opacity-75">Perlu perbaikan</small>
                        </div>
                        <div style="background: rgba(255,255,255,0.2); width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Tervalidasi</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['tervalidasi'] }}</h2>
                            <small class="opacity-75">Valid</small>
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
                    <form method="GET" action="{{ route('upps.penerimaan-dokumen') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   placeholder="Nomor/Nama prodi..."
                                   value="{{ request('search') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Dokumen</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI ? 'selected' : '' }}>
                                    Belum Upload
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DIKIRIM ? 'selected' : '' }}>
                                    Draft Dikirim
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_DRAFT_BORANG_DITERIMA ? 'selected' : '' }}>
                                    Draft Diterima
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BORANG_ONLINE_SELESAI ? 'selected' : '' }}>
                                    Dokumen Diterima Sistem
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATION_PENDING ? 'selected' : '' }}>
                                    Menunggu Validasi
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BORANG_IN_VALIDATION ? 'selected' : '' }}>
                                    Dalam Validasi
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED ? 'selected' : '' }}>
                                    Perlu Revisi
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BORANG_VALIDATED ? 'selected' : '' }}>
                                    Tervalidasi
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA }}"
                                        {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BORANG_FINAL_DITERIMA ? 'selected' : '' }}>
                                    Final Diterima
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
                            <a href="{{ route('upps.penerimaan-dokumen') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Dokumen</h5>
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
                                        <th width="18%">Nomor Permohonan</th>
                                        <th width="20%">Program Studi</th>
                                        <th width="10%">Tahun</th>
                                        <th width="14%">Tanggal Upload</th>
                                        <th width="18%">Status</th>
                                        <th width="15%" class="text-center">Aksi</th>
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
                                                <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $pengajuan->jenis_akreditasi_label }}
                                                </small>
                                            </td>
                                            <td>
                                                <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $pengajuan->tahun_akreditasi }}</span>
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
                                                {!! $pengajuan->getCustomBadgeLastStatus('borang_final', 'upps') !!}
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('upps.penerimaan-dokumen.show', $pengajuan->id) }}"
                                                       class="btn btn-info"
                                                       title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    @php
                                                        $canUpload = in_array($pengajuan->status, [
                                                            \App\Models\PengajuanAkreditasi::STATUS_PEMBAYARAN_DIVERIFIKASI,
                                                            \App\Models\PengajuanAkreditasi::STATUS_BORANG_REVISION_REQUIRED,
                                                        ]);
                                                    @endphp

                                                    @if($canUpload)
                                                        <a href="{{ route('upps.penerimaan-dokumen.upload.form', $pengajuan->id) }}"
                                                           class="btn btn-success"
                                                           title="Upload Dokumen">
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
