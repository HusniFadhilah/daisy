{{-- resources/views/upps/pelaporan-hasil-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaporan Hasil Akreditasi')

@push('styles')
<style>
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .filter-card {
        background: linear-gradient(135deg, #932136 0%, #870820 100%);
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

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Pelaporan Hasil Akreditasi</strong><br>
        Pelaporan hasil akreditasi dapat dilihat pada daftar berikut.<br>
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
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Hasil Dilaporkan" :value="$stats['total']" description="Pelaporan hasil" icon="megaphone" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Arsip Disimpan" :value="$stats['arsip_disimpan']" description="Dokumen diarsipkan" icon="archive" iconBg="success-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Proses Selesai" :value="$stats['selesai']" description="Akreditasi selesai" icon="patch-check" iconBg="info-subtle" />
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
