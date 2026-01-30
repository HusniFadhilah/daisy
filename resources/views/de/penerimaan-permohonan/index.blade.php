{{-- resources/views/de/penerimaan-permohonan/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penerimaan Permohonan Akreditasi')

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }

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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .badge-status {
        padding: 8px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .priority-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .priority-high {
        background-color: #dc3545;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
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
            <li class="breadcrumb-item active">Penerimaan Permohonan Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-send-check"></i> Penerimaan Permohonan Akreditasi
            </h4>
            <p class="text-muted mb-0">Kirim penerimaan permohonan akreditasi ke PS yang telah mengajukan permohonan akreditasi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    @include('de.penerimaan-permohonan.components.stats-cards', ['stats' => $stats])

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            @include('de.penerimaan-permohonan.components.filter-sidebar', [
            'universities' => $universities,
            'tahunList' => $tahunList
            ])
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Penerimaan Permohonan Akreditasi</h5>
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
                                    <th width="3%">#</th>
                                    <th width="15%">Permohonan Akreditasi</th>
                                    <th width="20%">Program Studi</th>
                                    <th width="25%">Tanggal Permohonan Akreditasi Diterima</th>
                                    <th width="20%">Status Penerimaan Permohonan</th>
                                    <th width="8%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $suratPenerimaan = $pengajuan->dokumen->first();
                                $daysSinceTerima = $pengajuan->tanggal_surat_permohonan_diterima
                                ? floor(\Carbon\Carbon::parse($pengajuan->tanggal_surat_permohonan_diterima)->diffInDays(now()))
                                : 0;
                                $isUrgent = !$suratPenerimaan && $daysSinceTerima > 3;
                                @endphp
                                <tr class="{{ $isUrgent ? 'table-warning' : '' }}">
                                    <td>
                                        @if($isUrgent)
                                        <span class="priority-indicator priority-high" title="Sudah {{ $daysSinceTerima }} hari belum terkirim"></span>
                                        @endif
                                        {{ $pengajuans->firstItem() + $index }}
                                    </td>
                                    <td>
                                        <p class="mb-1">{{ $pengajuan->judul }}</p>
                                        <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                        <br>
                                        <small class="text-muted">
                                            Dibuat pada: {{ $pengajuan->created_at->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $pengajuan->studyProgram->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                            </small>
                                            <br>
                                            <small>{{ $pengajuan->studyProgram->university->name ?? '-' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_surat_permohonan_diterima)
                                        <small>
                                            {{ $pengajuan->tanggal_surat_permohonan_diterima->format('d M Y') }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            ({{ $daysSinceTerima }} hari lalu)
                                        </small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($suratPenerimaan)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Telah Dikirim
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $suratPenerimaan->created_at->format('d M Y') }}
                                        </small>
                                        @else
                                        <span class="badge bg-warning">
                                            <i class="bi bi-hourglass-split"></i> Belum Dikirim
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('de.penerimaan-permohonan.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="{{ $suratPenerimaan ? 'Lihat Detail' : 'Kirim Penerimaan Permohonan Akreditasi' }}">
                                                <i class="bi bi-{{ $suratPenerimaan ? 'eye' : 'send' }}"></i>
                                            </a>

                                            {{-- @if($suratPenerimaan)
                                            <a href="{{ route('de.penerimaan-permohonan.download', $pengajuan->id) }}" class="btn btn-success action-btn" title="Download Surat">
                                            <i class="bi bi-download"></i>
                                            </a>
                                            @endif --}}
                                        </div>
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
                            @if(request()->filled('search') || request()->filled('status_penerimaan'))
                            Tidak ada data yang sesuai dengan filter
                            @else
                            Tidak ada permohonan yang perlu penerimaan akreditasi
                            @endif
                        </p>
                        @if(request()->filled('search') || request()->filled('status_penerimaan'))
                        <a href="{{ route('de.penerimaan-permohonan') }}" class="btn btn-sm btn-primary">
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

@push('scripts')
<script>
    // Auto-refresh badge counts jika ada
    setInterval(() => {
        // Could implement real-time updates here
    }, 60000); // Every minute

</script>
@endpush
