{{-- resources/views/upps/penyampaian-hasil-akreditasi/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penyampaian Hasil Akreditasi')

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
            <li class="breadcrumb-item active">Penyampaian Hasil Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-clipboard-data"></i> Penyampaian Hasil Akreditasi
            </h4>
            <p class="text-muted mb-0">Hasil akreditasi program studi dari LAMDEPILAR</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Penyampaian Hasil Akreditasi</strong><br>
        Penyampaian hasil akreditasi program studi tersedia pada daftar berikut
    </div>

    <!-- Congratulations Alert (if any) -->
    {{-- @if($stats['total'] > 0)
    <div class="alert alert-success alert-permanent border-start border-2 border-success mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-trophy-fill fs-1 me-3 text-success"></i>
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-check-circle-fill"></i> Selamat!
                </h5>
                <p class="mb-2">
                    Program studi Anda telah menyelesaikan proses akreditasi.
                    Total <strong class="fs-5">{{ $stats['total'] }}</strong> hasil akreditasi telah disampaikan.
    </p>
    @if($stats['unggul'] > 0)
    <div class="alert alert-light border border-success mb-0">
        <i class="bi bi-star-fill text-warning"></i>
        <strong>{{ $stats['unggul'] }}</strong> program studi meraih peringkat <strong class="text-success">Unggul</strong>!
    </div>
    @endif
</div>
</div>
</div>
@endif --}}

<!-- Statistics Cards -->
{{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Hasil Akreditasi" :value="$stats['total']" description="Sudah disampaikan" icon="trophy" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Proses Selesai" :value="$stats['selesai']" description="Akreditasi selesai" icon="check-circle" iconBg="success-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Terakreditasi Unggul" :value="$stats['unggul']" description="Prodi unggul" icon="star-fill" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Baik Sekali" :value="$stats['baik_sekali']" description="Prodi baik sekali" icon="award" iconBg="info-subtle" />
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
                    <h5 class="mb-0">Daftar Hasil Akreditasi</h5>
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
                                <th width="15%">Penyampaian Hasil Akreditasi</th>
                                <th width="20%">Status Penyampaian Hasil</th>
                                <th width="20%">Tanggal Penyampaian Hasil</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuans as $index => $pengajuan)
                            @php
                            $peringkatSaatIni = $pengajuan->peringkat_saat_ini;
                            $nilaiSaatIni = $pengajuan->nilai_saat_ini;
                            $hasil = $pengajuan->asesmen->hasil ?? null;
                            @endphp
                            <tr>
                                <td>{{ $pengajuans->firstItem() + $index }}</td>
                                <td>
                                    {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                                </td>
                                <td>
                                    @if($hasil && $hasil->skor_al)
                                    @php
                                    $peringkatAL = $hasil->getPeringkatFromSkor((float)($hasil->skor_al ?? 0), 'al');
                                    @endphp
                                    {{-- <span class="badge bg-light text-dark fs-6 p-2 px-3">{{ number_format($hasil->skor_al, 2) }}</span> --}}
                                    <span class="badge p-2 px-3 my-2 fs-6" style="background-color: {{ $hasil->getPeringkatColor($peringkatAL, 'al') }}; color:#222">{{ $peringkatAL }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('penyampaian_hasil','upps','label_short_for','text-dark') !!}
                                </td>
                                <td>
                                    @if($pengajuan->tanggal_hasil_akreditasi_dikirim)
                                    <small>{{ $pengajuan->tanggal_hasil_akreditasi_dikirim->locale('id')->translatedFormat('d M Y') }}</small>
                                    <br>
                                    <small class="text-muted">
                                        {{ $pengajuan->tanggal_hasil_akreditasi_dikirim->diffForHumans() }}
                                    </small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('upps.penyampaian-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-info btn-sm" title="Lihat Detail">
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
                    {{ $pengajuans->onEachSide(1)->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                    <p class="text-muted mt-3">
                        @if(request()->filled('search') || request()->filled('status') || request()->filled('peringkat'))
                        Tidak ada data yang sesuai dengan filter
                        @else
                        Belum ada hasil akreditasi yang disampaikan
                        @endif
                    </p>
                    @if(request()->filled('search') || request()->filled('status') || request()->filled('peringkat'))
                    <a href="{{ route('upps.penyampaian-hasil-akreditasi') }}" class="btn btn-sm btn-info">
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
