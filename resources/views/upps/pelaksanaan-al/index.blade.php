{{-- resources/views/upps/pelaksanaan-al/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaksanaan AL & Berita Acara')

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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pelaksanaan AL & Berita Acara</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-geo-alt"></i> Pelaksanaan AL & Berita Acara
            </h4>
            <p class="text-muted mb-0">Monitor pelaksanaan AL dan persetujuan berita acara AL</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Proses Pelaksanaan AL</strong><br>
        Pelaksanaan AL pada permohonan akreditasi program studi tersedia pada daftar berikut<br>
        Program studi dimohon memeriksa Laporan Hasil Asesmen (LHA) dan selanjutnya melakukan persetujuan
    </div>

    <!-- Pending Approval Alert -->
    @if($stats['pending_approval'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-2 border-warning mb-4">
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-exclamation-circle-fill"></i> Laporan Hasil AL Menunggu Persetujuan
                </h5>
                <p class="mb-2">
                    Anda memiliki <strong class="text-danger fs-5">{{ $stats['pending_approval'] }}</strong>
                    Laporan Hasil Asesmen Lapangan yang menunggu persetujuan.
                </p>
                <div class="alert alert-light alert-permanent mb-2">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <strong>Penting:</strong> Mohon segera tinjau dan setujui LHA untuk melanjutkan proses akreditasi.
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    {{-- <h4>Informasi Pelaksanaan dan Berita Acara AL Keseluruhan</h4>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
    <div class="col mb-3">
        <x-stat-card title="Total Dokumen" :value="$stats['total']" description="Total permohonan akreditasi yang telah sampai pada tahap Pelaksanaan AL & Berita Acara" icon="person-check" iconBg="primary-subtle" />
    </div>

    <div class="col mb-3">
        <x-stat-card title="Berita Acara AL Menunggu Persetujuan" :value="$stats['pending_approval']" description="Total berita acara AL yang membutuhkan persetujuan" icon="hourglass-split" iconBg="warning-subtle" />
    </div>

    <div class="col mb-3">
        <x-stat-card title="Berita Acara AL Disetujui" :value="$stats['berita_acara']" description="Total berita acara AL yang telah disetujui" icon="file-earmark-text" iconBg="success-subtle" />
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
                        <h5 class="mb-0">Daftar Pelaksanaan AL & Berita Acara</h5>
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
                                    <th width="25%">Tanggal Pelaksanaan AL</th>
                                    <th width="20%">Status Pelaksanaan AL</th>
                                    <th width="20%">Tanggal AL Selesai</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                                    </td>
                                    <td>
                                        <span>Tanggal Mulai AL</span>
                                        <small>
                                            : {{ $pengajuan->asesmen->asesmenLapangan->tanggal_mulai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_mulai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                                        </small>
                                        <br><span>Tanggal Selesai AL</span>
                                        <small>
                                            : {{ $pengajuan->asesmen->asesmenLapangan->tanggal_selesai
                                    ? \Carbon\Carbon::parse($pengajuan->asesmen->asesmenLapangan->tanggal_selesai)->locale('id')->translatedFormat('d M Y')
                                    : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('pelaksanaan_al', 'upps', 'label_short_for') !!}

                                        @php
                                        $pendingLHA = $pengajuan->asesmen->lhaDocuments
                                        ->whereIn('status_persetujuan_prodi', ['pending', 'revision_required'])
                                        ->first();
                                        @endphp

                                        @if($pendingLHA)
                                        <br>
                                        <span class="badge bg-warning text-dark mt-1">
                                            <i class="bi bi-bell"></i>
                                            {{ match($pendingLHA->status_persetujuan_prodi) {
                                                'pending' => 'LHA Menunggu Persetujuan',
                                                'revision_required' => 'Permintaan Revisi LHA Diproses',
                                                default => '-'
                                            } }}
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_al_selesai)
                                        <small>{{ $pengajuan->tanggal_al_selesai->locale('id')->translatedFormat('d M Y') }}</small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $pengajuan->tanggal_al_selesai->diffForHumans() }}
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('upps.pelaksanaan-al.show', $pengajuan->id) }}" class="btn btn-primary btn-sm" title="Lihat Detail">
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
                            Belum ada data pelaksanaan AL
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('upps.pelaksanaan-al') }}" class="btn btn-sm btn-info">
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
