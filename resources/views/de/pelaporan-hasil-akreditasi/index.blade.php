{{-- resources/views/de/pelaporan-hasil-akreditasi/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Pelaporan Hasil Akreditasi')

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

    .doc-indicator {
        font-size: 10px;
        padding: 4px 8px;
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
            <h4 class="mb-1"><i class="bi bi-megaphone"></i> Pelaporan Hasil Akreditasi</h4>
            <p class="text-muted mb-0">Kelola pelaporan dan dokumentasi hasil akreditasi program studi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Hasil Ditetapkan" :value="$stats['total']" description="Siap atau sudah dilaporkan" icon="award" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Belum Dilaporkan" :value="$stats['belum_dilaporkan']" description="Menunggu upload dokumen" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Sudah Dilaporkan" :value="$stats['sudah_dilaporkan']" description="Pelaporan selesai" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Punya Sertifikat" :value="$stats['punya_sertifikat']" description="Sertifikat sudah terbit" icon="patch-check" gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" />
        </div>
    </div>

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar (tetap) -->

        <!-- Main Content (dibuat mirip "penetapan") -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bi bi-table"></i> Daftar Pelaporan Hasil Akreditasi
                    </h6>
                    <span class="badge bg-primary">Total: {{ $pengajuans->total() }}</span>
                </div>

                <div class="card-body p-0">
                    @if($pengajuans->count() > 0)
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Permohonan Akreditasi</th>
                                <th width="20%">Peringkat Akhir</th>
                                <th width="20%">Status Pelaporan</th>
                                <th width="20%">Tanggal Pelaporan</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuans as $index => $pengajuan)
                            @php
                            $peringkatAkhir = $pengajuan->peringkat_hasil_banding ?? $pengajuan->peringkat_hasil;
                            $nilaiAkhir = $pengajuan->nilai_akhir_banding ?? $pengajuan->nilai_akhir;
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
                                    $peringkatFinal = $hasil->getPeringkatFromSkorAL($hasil->skor_final);
                                    @endphp
                                    <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkatFinal) }}; color:#222">
                                        {{ $peringkatFinal }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('pelaporan_hasil','de','label_short_for','text-dark') !!}
                                </td>
                                <td>
                                    <small>
                                        {{ $pengajuan->tanggal_pelaporan_hasil
                                                        ? $pengajuan->tanggal_pelaporan_hasil->format('d M Y')
                                                        : '-' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('de.pelaporan-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                        <p class="text-muted mt-3 mb-0">Tidak ada data pelaporan hasil akreditasi</p>
                    </div>
                    @endif
                </div>

                @if($pengajuans->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Menampilkan {{ $pengajuans->firstItem() }} - {{ $pengajuans->lastItem() }} dari {{ $pengajuans->total() }} data
                        </div>
                        <div>
                            {{ $pengajuans->links() }}
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection
