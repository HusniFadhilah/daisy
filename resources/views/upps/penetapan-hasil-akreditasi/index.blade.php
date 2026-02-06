{{-- resources/views/upps/penetapan-hasil-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penetapan Hasil Akreditasi')

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
            <li class="breadcrumb-item active">Penetapan Hasil Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-award"></i> Penetapan Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Hasil akhir akreditasi yang telah ditetapkan oleh LAMDEPILAR</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Penetapan Hasil Akreditasi</strong><br>
        Penetapan hasil akreditasi dapat dilihat pada link berikut.<br>
    </div>

    <!-- Success Alert -->
    @if($stats['total'] > 0)
    <div class="alert alert-success alert-permanent border-start border-4 border-success mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-award-fill fs-1 me-3 text-success"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-check-circle-fill"></i> Hasil Akreditasi Telah Ditetapkan
                </h5>
                <p class="mb-2">
                    Total <strong class="fs-5">{{ $stats['total'] }}</strong> hasil akreditasi
                    telah ditetapkan sebagai hasil akhir resmi dari LAMDEPILAR.
                </p>
                @if($stats['diumumkan'] > 0)
                <div class="alert alert-light border border-success mb-0">
                    <i class="bi bi-megaphone-fill text-success"></i>
                    <strong>{{ $stats['diumumkan'] }}</strong> hasil telah diumumkan secara resmi.
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Hasil Ditetapkan" :value="$stats['total']" description="Hasil akhir akreditasi" icon="award" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Diumumkan" :value="$stats['diumumkan']" description="Pengumuman resmi" icon="megaphone" iconBg="success-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Proses Selesai" :value="$stats['selesai']" description="Akreditasi selesai" icon="check-circle" iconBg="info-subtle" />
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
                        <h5 class="mb-0">Daftar Penetapan Hasil Akreditasi</h5>
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
                                    <th width="20%">Program Studi</th>
                                    <th width="15%">Peringkat Akhir</th>
                                    <th width="15%">Tanggal Penetapan</th>
                                    <th width="10%">Status</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                // Peringkat akhir: prioritas hasil banding jika ada
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
                                        <p class="mb-1"><strong>{{ $pengajuan->judul }}</strong></p>
                                        <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                        <br>
                                        <small class="text-muted">
                                            Dibuat: {{ $pengajuan->created_at->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                        </small>
                                        <br>
                                        <small>{{ $pengajuan->studyProgram->university->name ?? '-' }}</small>
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
                                            {{ $pengajuan->tanggal_penetapan
                                                        ? $pengajuan->tanggal_penetapan->format('d M Y')
                                                        : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $pengajuan->status_badge_class }}">
                                            {{ $pengajuan->status_label }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.penetapan-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                            Belum ada penetapan hasil akreditasi
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('peringkat'))
                        <a href="{{ route('upps.penetapan-hasil-akreditasi') }}" class="btn btn-sm btn-info">
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
