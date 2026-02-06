{{-- resources/views/upps/pengingat-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pengingat Masa Akreditasi')

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
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
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
            <li class="breadcrumb-item active">Pengingat Masa Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-bell"></i> Pengingat Masa Akreditasi
            </h4>
            <p class="text-muted mb-0">Daftar pengingat masa akreditasi dari LAMDEPILAR</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Pengingat Masa Akreditasi</strong><br>
        Masa akreditasi Program studi ... akan habis pada. <br>
        Waktu yang tersedia untuk melakukan reakreditasi adalah
    </div>

    <!-- Expiring Accreditation Alert -->
    {{-- @if($expiringStats['has_expiring']) --}}
    {{-- <div class="alert alert-warning alert-permanent fade show border-start border-2 border-warning" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill fs-1 me-3"></i>

            <div class="w-100">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2">
                    <div>
                        <strong>Perhatian!</strong>
                        Terdapat <strong>{{ $expiringStats['count'] }}</strong> program studi di UPPS Anda
    yang masa akreditasinya akan berakhir pada <strong>{{ $expiringStats['target_month_label'] }}</strong>.
    Mohon menyiapkan permohonan akreditasi minimal <strong>6 bulan</strong> sebelum masa akreditasi habis.
</div>

<button class="btn btn-sm btn-outline-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExpiringDetail" aria-expanded="false" aria-controls="collapseExpiringDetail">
    <i class="bi bi-list-ul"></i> Lihat detail
</button>
</div>

<div class="collapse mt-3" id="collapseExpiringDetail">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <strong class="mb-0">Detail Prodi</strong>
            <small class="text-muted">
                ditampilkan {{ $expiringStats['programs']->count() }} dari {{ $expiringStats['count'] }}
            </small>
        </div>

        <div class="card-body p-0">
            @if($expiringStats['programs']->count() > 0)
            <div class="list-group list-group-flush overflow-auto" style="max-height: 280px;">
                @foreach($expiringStats['programs'] as $prodi)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="fw-semibold">
                                {{ $prodi->name }}
                                <span class="text-muted fw-normal">
                                    ({{ $prodi->degreeLevel->alias ?? '-' }})
                                </span>
                            </div>
                            <div class="text-muted small">
                                {{ $prodi->university->name ?? '-' }}
                            </div>
                        </div>

                        <div class="text-end">
                            <span class="small text-muted">Kedaluwarsa: </span>
                            <span>{!! $prodi->status_badge_kedaluwarsa !!}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($expiringStats['count'] > ($expiringStats['programs_limit'] ?? 10))
            <div class="p-3">
                <small class="text-muted">
                    Masih ada {{ $expiringStats['count'] - ($expiringStats['programs_limit'] ?? 10) }} prodi lainnya.
                    Gunakan pencarian/filter untuk melihat data lebih lengkap.
                </small>
            </div>
            @endif
            @else
            <div class="p-3 text-muted">Tidak ada data detail untuk ditampilkan.</div>
            @endif
        </div>
    </div>
</div>
</div>
</div>
</div> --}}
{{-- @endif --}}

<!-- Statistics Cards -->
{{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
    <div class="col mb-3">
        <x-stat-card title="Total Pengingat Masa Akreditasi" :value="$stats['total']" description="Jumlah keseluruhan pengingat masa akreditasi" icon="bell" iconBg="primary-subtle" />
    </div>

    <div class="col mb-3">
        <x-stat-card title="Pengingat Masa Akreditasi Belum Direspon" :value="$stats['belum_direspon']" description="Jumlah pengingat masa akreditasi yang perlu ditindaklanjuti" icon="exclamation-circle" iconBg="warning-subtle" />
    </div>

    <div class="col mb-3">
        <x-stat-card title="Pengingat Masa Akreditasi Telah Direspon" :value="$stats['direspon']" description="Jumlah pengingat masa akreditasi yang selesai ditindaklanjuti" icon="check-circle" iconBg="success-subtle" />
    </div>
</div> --}}

<!-- Filters & Content -->
<div class="row">
    <!-- Filters Sidebar -->

    <!-- Main Content -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Pengingat Masa Akreditasi</h5>
                    <div>
                        <span class="text-muted">Total: <strong>{{ $pengingatList->total() }}</strong></span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if($pengingatList->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Program Studi</th>
                                <th width="15%">Tanggal Akreditasi Kedaluwarsa</th>
                                <th width="15%">Tanggal Pengingat Dikirim</th>
                                <th width="15%">Pengirim</th>
                                <th width="15%">Status Pengingat Akreditasi</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengingatList as $index => $pengingat)
                            <tr>
                                <td>{{ $pengingatList->firstItem() + $index }}</td>
                                <td>
                                    <strong>{{ $pengingat->studyProgram->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengingat->studyProgram->university->name }}
                                    </small>
                                </td>
                                <td>
                                    {!! $pengingat->studyProgram->status_badge_kedaluwarsa !!}
                                </td>
                                <td>
                                    <small>
                                        {{ $pengingat->tanggal_dikirim->format('d M Y') }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengingat->tanggal_dikirim->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    <small>{{ $pengingat->pengirim->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $pengingat->status_badge_class }} badge-status">
                                        {{ $pengingat->status_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('upps.pengingat-akreditasi.show', $pengingat->id) }}" class="btn btn-primary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($pengingat->status === \App\Models\PengingatAkreditasi::STATUS_BELUM_DIRESPON)
                                        <a href="{{ route('upps.pengingat-akreditasi.respond.form', $pengingat->id) }}" class="btn btn-success" title="Respon Pengingat">
                                            <i class="bi bi-reply-fill"></i>
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
                    {{ $pengingatList->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                    <p class="text-muted mt-3">
                        @if(request()->filled('search') || request()->filled('status'))
                        Tidak ada data yang sesuai dengan filter
                        @else
                        Belum ada pengingat akreditasi
                        @endif
                    </p>
                    @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('upps.pengingat-akreditasi') }}" class="btn btn-sm btn-primary">
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
