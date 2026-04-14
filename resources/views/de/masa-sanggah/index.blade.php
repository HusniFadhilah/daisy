@extends('layouts.template.app')

@section('title', 'Masa Sanggah Akreditasi')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Masa Sanggah</li>
        </ol>
    </nav>
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-hourglass-split"></i> Masa Sanggah
            </h4>
            <p class="text-muted mb-0">Monitor masa sanggah akreditasi</p>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan Akreditasi" :value="$stats['total']" description="" icon="file-text" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Masa Sanggah Aktif" :value="$stats['aktif']" description="" icon="hourglass-split" iconBg="success-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Hampir Habis" :value="$stats['hampir_habis']" description="" icon="exclamation-triangle" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Ada Banding" :value="$stats['ada_banding']" description="" icon="file-earmark-break" iconBg="danger-subtle" />
        </div>
    </div>

    {{-- Results Table --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Masa Sanggah</h5>
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
                                    <th>Tanggal Penyampaian Hasil</th>
                                    <th width="20%">Periode Masa Sanggah</th>
                                    <th width="20%">Status Masa Sanggah</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $now = now();
                                $isAktif = $now->between($pengajuan->tanggal_masa_sanggah_mulai, $pengajuan->tanggal_masa_sanggah_selesai);
                                $sisaHari = $isAktif ? $now->diffInDays($pengajuan->tanggal_masa_sanggah_selesai, false) : 0;
                                @endphp
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_hasil_akreditasi_dikirim)
                                        {{ $pengajuan->tanggal_hasil_akreditasi_dikirim->locale('id')->translatedFormat('d M Y') }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Mulai:</strong> {{ $pengajuan->tanggal_masa_sanggah_mulai->locale('id')->translatedFormat('d M Y') }}
                                            <br>
                                            <strong>Selesai:</strong> {{ $pengajuan->tanggal_masa_sanggah_selesai->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                    </td>
                                    <td style="min-width: 150px;">
                                        {!! $pengajuan->getCustomBadgeLastStatus('masa_sanggah','de','label_short_for','text-dark') !!}
                                        {{-- @if($isAktif)
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Aktif ({{ $sisaHari }} hari)
                                        </span>
                                        @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Selesai
                                        </span>
                                        @endif --}}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('de.masa-sanggah.show', $pengajuan->id) }}" class="btn btn-primary btn-sm" title="Lihat Detail">
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
                            @if(request()->filled('search') || request()->filled('status_sanggah'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Belum ada masa sanggah
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status_sanggah'))
                        <a href="{{ route('de.masa-sanggah') }}" class="btn btn-sm btn-info">
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
