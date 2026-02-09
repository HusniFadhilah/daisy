{{-- resources/views/asesmen/pelaporan/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pelaporan')

@push('styles')
<style>
    .type-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid #e0e0e0;
    }

    .type-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .type-card.dokumen:hover {
        border-color: #0d6efd;
    }

    .type-card.ak:hover {
        border-color: #0dcaf0;
    }

    .type-card.al:hover {
        border-color: #198754;
    }

    .type-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="welcome-section mb-4">
        <div class="welcome-content">
            <h2><i class="bi bi-file-earmark-text"></i> Pelaporan</h2>
            <p class="mb-0">Pilih jenis pelaporan yang ingin Anda kelola</p>
        </div>
    </div>

    <!-- Type Cards -->
    <div class="row">
        <!-- Pelaporan Validasi Dokumen -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('pelaporan.indexDokumen') }}" class="text-decoration-none">
                <div class="card type-card dokumen h-100">
                    <div class="card-body text-center py-5">
                        <div class="type-icon text-primary">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <h4 class="mb-3">Pelaporan Validasi Dokumen</h4>
                        <p class="text-muted mb-4">
                            Laporan Kesiapan LED Program Studi (LKLED)
                        </p>

                        <div class="row g-2">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-primary">{{ $stats['dokumen']['total'] }}</h5>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-warning">{{ $stats['dokumen']['pending'] }}</h5>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-success">{{ $stats['dokumen']['completed'] }}</h5>
                                    <small class="text-muted">Selesai</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="btn btn-primary">
                                <i class="bi bi-arrow-right"></i> Kelola Pelaporan
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pelaporan AK -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('pelaporan.indexValidasiAK') }}" class="text-decoration-none">
                <div class="card type-card ak h-100">
                    <div class="card-body text-center py-5">
                        <div class="type-icon text-info">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <h4 class="mb-3">Pelaporan AK</h4>
                        <p class="text-muted mb-4">
                            Laporan Penilaian Kecukupan LED (LHK)
                        </p>

                        <div class="row g-2">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-info">{{ $stats['ak']['total'] }}</h5>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-warning">{{ $stats['ak']['pending'] }}</h5>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-success">{{ $stats['ak']['completed'] }}</h5>
                                    <small class="text-muted">Selesai</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="btn btn-info">
                                <i class="bi bi-arrow-right"></i> Kelola Pelaporan
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pelaporan AL -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('pelaporan.indexAL') }}" class="text-decoration-none">
                <div class="card type-card al h-100">
                    <div class="card-body text-center py-5">
                        <div class="type-icon text-success">
                            <i class="bi bi-building-check"></i>
                        </div>
                        <h4 class="mb-3">Pelaporan AL</h4>
                        <p class="text-muted mb-4">
                            Laporan Hasil Asesmen Lapangan (LHA)
                        </p>

                        <div class="row g-2">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-success">{{ $stats['al']['total'] }}</h5>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-warning">{{ $stats['al']['pending'] }}</h5>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <h5 class="mb-0 text-success">{{ $stats['al']['completed'] }}</h5>
                                    <small class="text-muted">Selesai</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <span class="btn btn-success">
                                <i class="bi bi-arrow-right"></i> Kelola Pelaporan
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity (Optional) -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aktivitas Terbaru</h5>
        </div>
        <div class="card-body">
            @php
            $recentActivities = collect()
            ->merge($byType['dokumen']->map(fn($a) => [
            'type' => 'dokumen',
            'assignment' => $a,
            'updated_at' => $a->updated_at
            ]))
            ->merge($byType['ak']->map(fn($a) => [
            'type' => 'ak',
            'assignment' => $a,
            'updated_at' => $a->updated_at
            ]))
            ->merge($byType['al']->map(fn($a) => [
            'type' => 'al',
            'assignment' => $a,
            'updated_at' => $a->updated_at
            ]))
            ->sortByDesc('updated_at')
            ->take(5);
            @endphp

            @if($recentActivities->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Jenis</th>
                            <th>Program Studi</th>
                            <th>Status</th>
                            <th>Update Terakhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $activity)
                        @php
                        $a = $activity['assignment'];
                        $pengajuan = $a->asesmen->pengajuan;
                        @endphp
                        <tr>
                            <td>
                                @if($activity['type'] === 'dokumen')
                                <span class="badge bg-primary">Dokumen</span>
                                @elseif($activity['type'] === 'ak')
                                <span class="badge bg-info">AK</span>
                                @else
                                <span class="badge bg-success">AL</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $pengajuan ? $pengajuan->judul : $a->asesmen->name }}
                                </div>
                                <small class="text-muted">
                                    {{ $a->asesmen->studyProgram->university->name ?? 'N/A' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $a->status_badge }}">
                                    {{ $a->status_label }}
                                </span>
                            </td>
                            <td>
                                <small>{{ \App\Libraries\Date::tglWaktu($a->updated_at) }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center mb-0">Belum ada aktivitas</p>
            @endif
        </div>
    </div>
</div>
@endsection
