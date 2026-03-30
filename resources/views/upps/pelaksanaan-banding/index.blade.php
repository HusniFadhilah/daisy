{{-- resources/views/upps/pelaksanaan-banding/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaksanaan Banding')

@push('styles')
<style>
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pelaksanaan Banding</li>
        </ol>
    </nav>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-play-circle"></i> Pelaksanaan Banding</h4>
            <p class="text-muted mb-0">Monitor pembayaran dan proses pelaksanaan banding</p>
        </div>
    </div>

    <div class="alert alert-info alert-permanent">
        <i class="bi bi-bell-fill"></i>
        <strong>Proses Pelaksanaan Banding</strong><br>
        Pelaksanaan Banding pada permohonan akreditasi program studi tersedia pada daftar berikut<br>
        Program studi dimohon memeriksa Laporan Hasil Surveillance Banding dan selanjutnya melakukan persetujuan
    </div>

    {{-- Alert banding sedang berlangsung --}}
    @if($stats['sedang_berlangsung'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-2 border-warning mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-play-circle-fill fs-2 me-3 text-warning"></i>
            <div>
                <h5 class="mb-1 fw-bold">Pelaksanaan Banding Sedang Berlangsung</h5>
                <p class="mb-0">
                    Terdapat <strong>{{ $stats['sedang_berlangsung'] }}</strong>
                    pelaksanaan banding yang sedang diproses LAMDEPILAR.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Alert menunggu bayar --}}
    @if($stats['menunggu_bayar'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-2 border-warning mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-credit-card fs-2 me-3 text-danger"></i>
            <div>
                <h5 class="mb-1 fw-bold">Perlu Pembayaran</h5>
                <p class="mb-0">
                    Terdapat <strong>{{ $stats['menunggu_bayar'] }}</strong>
                    banding yang menunggu pembayaran atau validasi pembayaran.
                    Segera lakukan pembayaran agar proses banding dapat dilanjutkan.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Approval Alert -->
    @if($stats['pending_approval'] > 0)
    <div class="alert alert-warning alert-permanent border-start border-2 border-warning mb-4">
        <div class="d-flex align-items-start">
            <div class="flex-grow-1">
                <h5 class="mb-2 fw-bold">
                    <i class="bi bi-exclamation-circle-fill"></i> Laporan Hasil Surveillance Banding Menunggu Persetujuan
                </h5>
                <p class="mb-2">
                    Anda memiliki <strong class="text-danger fs-5">{{ $stats['pending_approval'] }}</strong>
                    Laporan Hasil Surveillance Banding yang menunggu persetujuan.
                </p>
                <div class="alert alert-light alert-permanent mb-2">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <strong>Penting:</strong> Mohon segera tinjau dan setujui laporan tersebut untuk melanjutkan proses akreditasi.
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stat Cards --}}
    {{-- <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Pelaksanaan" :value="$stats['total']" description="Banding dalam proses" icon="play-circle" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Menunggu/Proses Bayar" :value="$stats['menunggu_bayar']" description="Belum bayar atau dalam validasi" icon="credit-card" gradient="linear-gradient(135deg, #f5576c 0%, #f093fb 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Sedang Berlangsung" :value="$stats['sedang_berlangsung']" description="Dalam pelaksanaan" icon="arrow-repeat" gradient="linear-gradient(135deg, #8ebb0a 0%, #c0c30d 100%)" />
        </div>
        <div class="col mb-3">
            <x-stat-card title="Selesai Dilaporkan" :value="$stats['selesai']" description="Hasil banding sudah dilaporkan" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div> --}}

    {{-- Table --}}
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pelaksanaan Banding</h5>
                <span class="text-muted">Total: <strong>{{ $pengajuans->total() }}</strong></span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($pengajuans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="23%">Permohonan Akreditasi</th>
                            <th width="18%">Pembayaran Banding</th>
                            <th width="20%">Status Pelaksanaan Banding</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuans as $index => $pengajuan)
                        @php
                        $pb = $pengajuan->pembayaranBanding;
                        $bayarLunas = $pb && $pb->status_pembayaran === 'terverifikasi';
                        @endphp
                        <tr class="{{ !$bayarLunas && $pb ? 'table-warning' : '' }}">
                            <td>{{ $pengajuans->firstItem() + $index }}</td>
                            <td>
                                {!! $pengajuan->getPermohonanAkreditasiSectionFor('upps') !!}
                            </td>
                            <td>
                                @if(!$pb)
                                {{-- Invoice belum dibuat DE --}}
                                <span class="badge bg-secondary">
                                    <i class="bi bi-hourglass"></i> Menunggu Invoice
                                </span>
                                @else
                                @php
                                $cfg = [
                                'menunggu_pembayaran' => ['warning', 'credit-card', 'Perlu Bayar'],
                                'menunggu_verifikasi' => ['info', 'clock-history', 'Validasi Keuangan'],
                                'upload_ulang' => ['secondary','arrow-repeat', 'Upload Ulang'],
                                'terverifikasi' => ['success', 'check-circle', 'Tervalidasi'],
                                'ditolak' => ['danger', 'x-circle', 'Ditolak'],
                                ][$pb->status_pembayaran] ?? ['secondary','question-circle','—'];
                                @endphp
                                <span class="badge bg-{{ $cfg[0] }}">
                                    <i class="bi bi-{{ $cfg[1] }}"></i> {{ $cfg[2] }}
                                </span><br>
                                <small class="text-muted">
                                    Rp {{ number_format($pb->jumlah_pembayaran, 0, ',', '.') }}
                                </small>
                                @endif
                            </td>
                            <td>
                                {!! $pengajuan->getCustomBadgeLastStatus('pelaksanaan_banding','upps','label_short_for') !!}

                                @php
                                $pendingLHA = $pengajuan->asesmen->lhaDocumentsBanding
                                ->whereIn('status_persetujuan_prodi', ['pending', 'revision_required'])
                                ->first();
                                @endphp

                                @if($pendingLHA)
                                <br>
                                <span class="badge bg-warning text-dark mt-1">
                                    <i class="bi bi-bell"></i>
                                    {{ match($pendingLHA->status_persetujuan_prodi) {
                                                'pending' => 'Laporan Banding Menunggu Persetujuan',
                                                'revision_required' => 'Permintaan Revisi Laporan Diproses',
                                                default => '-'
                                            } }}
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('upps.pelaksanaan-banding.show', $pengajuan->id) }}" class="btn btn-sm btn-primary" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $pengajuans->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 64px; color: #ddd;"></i>
                <p class="text-muted mt-3">Belum ada pelaksanaan banding</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
