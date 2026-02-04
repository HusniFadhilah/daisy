{{-- resources/views/upps/penugasan-al/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penugasan Asesor AL')

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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penugasan Asesor AL</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-person-check"></i> Penugasan Asesor AL
            </h4>
            <p class="text-muted mb-0">Monitor penugasan asesor untuk AL (Asesmen Lapangan)</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Dokumen" :value="$stats['total']" description="Total permohonan akreditasi yang telah sampai pada tahap Penugasan Asesor AL" icon="file-earmark-text" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Dokumen Pelaporan AK & Menunggu Penugasan Asesor AL" :value="$stats['ak_dilaporkan']" description="Dokumen penilaian AK telah terlaporkan dan sedang menunggu penugasan asesor AL" icon="send-check" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Penugasan Asesor AL" :value="$stats['asesor_ditugaskan']" description="Asesor AL telah ditugaskan" icon="person-check" iconBg="success-subtle" />
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
                    <form method="GET" action="{{ route('upps.penugasan-al') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Penugasan</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_AK_DILAPORKAN ? 'selected' : '' }}>
                                    Menunggu Penugasan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_ASESOR_AL_ASSIGNED ? 'selected' : '' }}>
                                    Asesor Ditugaskan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_AL_IN_PROGRESS ? 'selected' : '' }}>
                                    Asesmen Berlangsung
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI ? 'selected' : '' }}>
                                    Asesmen Selesai
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN ? 'selected' : '' }}>
                                    Hasil Dilaporkan
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
                            <a href="{{ route('upps.penugasan-al') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Penugasan Asesor AL</h5>
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
                                    <th width="25%">Tanggal Penugasan Asesor AL</th>
                                    <th width="25%">Status Penugasan Asesor AL</th>
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
                                        @if($pengajuan->tanggal_penugasan_asesor_al)
                                        <small>{{ $pengajuan->tanggal_penugasan_asesor_al->format('d M Y') }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->tanggal_penugasan_asesor_al->diffForHumans() }}
                                        </small>
                                        @else
                                        <span class="text-muted">Asesor AL sedang dalam proses penugasan</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('penugasan_asesor_al', 'upps', 'label_short_for') !!}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.penugasan-al.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            Belum ada permohonan akreditasi yang masuk tahap penugasan asesor AL
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.penugasan-al') }}" class="btn btn-sm btn-info">
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
