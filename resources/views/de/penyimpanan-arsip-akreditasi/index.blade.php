{{-- resources/views/de/penyimpanan-arsip-akreditasi/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Penyimpanan Arsip Akreditasi')

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

    .badge-peringkat {
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-completed {
        animation: pulse-success 2s infinite;
    }

    @keyframes pulse-success {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Penyimpanan Arsip Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-archive"></i> Penyimpanan Arsip Akreditasi</h4>
            <p class="text-muted mb-0">Kelola penyimpanan arsip dan finalisasi proses akreditasi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan Akreditasi" :value="$stats['total']" description="Permohonan akreditasi yang telah sampai tahap penyimpanan arsip" icon="archive" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Diarsipkan" :value="$stats['belum_diarsipkan']" description="Penyimpanan arsip sedang proses dilakukan" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Telah Diarsipkan" :value="$stats['selesai']" description="Dokumen akreditasi telah selesai dan arsip tersimpan" icon="archive-fill" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar -->

        <!-- Main Content -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Penyimpanan Arsip Akreditasi</h5>
                        <div>
                            <span class="text-muted">Total: <strong>{{ $pengajuans->total() }}</strong></span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($pengajuans->count() > 0)
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Permohonan Akreditasi</th>
                                <th width="20%">Status Akreditasi</th>
                                <th width="20%">Status Penyimpanan Arsip Akreditasi</th>
                                <th width="20%">Tanggal Penyimpanan Arsip Akreditasi</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuans as $index => $pengajuan)
                            @php
                            $peringkatAkhir = $pengajuan->peringkat_final;
                            $skorFinal = $pengajuan->skor_final;
                            $hasil = $pengajuan->asesmen->hasil ?? null;
                            @endphp
                            <tr>
                                <td>{{ $pengajuans->firstItem() + $index }}</td>
                                <td>
                                    {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                                </td>
                                <td>
                                    @if($hasil && $hasil->skor_final)
                                    @php
                                    $peringkatFinal = $hasil->getPeringkatFromSkor((float)($hasil->skor_al ?? 0));
                                    @endphp
                                    <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkatFinal) }}; color:#222">
                                        {{ $peringkatFinal }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('penyimpanan_arsip','de','label_short_for','text-dark') !!}
                                </td>
                                <td>
                                    <small>
                                        {{ $pengajuan->tanggal_penyimpanan
                                                        ? $pengajuan->tanggal_penyimpanan->locale('id')->translatedFormat('d M Y')
                                                        : '-' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('de.penyimpanan-arsip-akreditasi.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                        <p class="text-muted mt-3 mb-0">Tidak ada data penyimpanan arsip akreditasi</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
