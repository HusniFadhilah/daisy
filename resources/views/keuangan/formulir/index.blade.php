@extends('layouts.template.app')

@section('title', 'Formulir Pembayaran')

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

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .dokumen-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-file-earmark-text"></i>
                Formulir Pembayaran
            </h2>
            <p class="text-muted mb-0">
                Daftar formulir pembayaran yang telah diupload oleh program studi
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Formulir</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Formulir yang sudah diupload</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="bi bi-file-earmark-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-2" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Hari Ini</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['today'] }}</h2>
                            <small class="opacity-75">Formulir yang telah diupload hari ini</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="bi bi-calendar-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('keuangan.formulir.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Cari</label>
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Nomor invoice, pengajuan, atau prodi...">
                    </div>

                    <!-- University -->
                    <div class="col-md-3">
                        <label class="form-label">Universitas</label>
                        <select name="university_id" class="form-select">
                            <option value="">Semua Universitas</option>
                            @foreach($universities as $univ)
                            <option value="{{ $univ->id }}" {{ $university_id == $univ->id ? 'selected' : '' }}>
                                {{ $univ->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Degree Level -->
                    <div class="col-md-2">
                        <label class="form-label">Jenjang</label>
                        <select name="degree_level_id" class="form-select">
                            <option value="">Semua Jenjang</option>
                            @foreach($degreeLevels as $level)
                            <option value="{{ $level->id }}" {{ $degree_level_id == $level->id ? 'selected' : '' }}>
                                {{ $level->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('keuangan.formulir.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Daftar Formulir Pembayaran
            </h5>
            <span class="badge bg-primary">Total: {{ $pengajuan->total() }}</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Nomor Invoice</th>
                            <th>Program Studi</th>
                            <th>Universitas</th>
                            <th>Jenjang</th>
                            <th>Jumlah</th>
                            <th>Dokumen</th>
                            <th>Tanggal Upload</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuan as $item)
                        @php
                        $pembayaran = $item->pembayaran;
                        $dokumenFormulir = $item->dokumen->where('jenis_dokumen', 'formulir_pembayaran')->first();
                        $hasBukti = $pembayaran && $pembayaran->bukti_path;
                        @endphp
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration + ($pengajuan->currentPage() - 1) * $pengajuan->perPage() }}
                            </td>

                            <td>
                                <strong>{{ $pembayaran->nomor_invoice ?? '-' }}</strong>
                                <br>
                                <small class="text-muted">{{ $item->nomor_pengajuan }}</small>
                            </td>

                            <td>
                                <strong>{{ $item->studyProgram->name }}</strong>
                            </td>

                            <td>{{ $item->studyProgram->university->name }}</td>

                            <td>
                                <span class="badge bg-info">
                                    {{ $item->studyProgram->degreeLevel->name }}
                                </span>
                            </td>

                            <td>
                                <strong class="text-success">
                                    Rp {{ number_format($pembayaran->jumlah_pembayaran ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>

                            <td>
                                @if($dokumenFormulir)
                                <span class="badge bg-success dokumen-badge me-1">
                                    <i class="bi bi-file-earmark-text"></i> Formulir dan Bukti Pembayaran
                                </span>
                                @endif
                            </td>

                            <td>
                                @if($dokumenFormulir)
                                {{ $dokumenFormulir->created_at->format('d M Y') }}
                                <br>
                                <small class="text-muted">
                                    {{ $dokumenFormulir->created_at->format('H:i') }}
                                </small>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">
                                    Belum ada formulir pembayaran yang diupload
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($pengajuan->hasPages())
        <div class="card-footer">
            {{ $pengajuan->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto submit on filter change
        $('select[name="status"], select[name="university_id"], select[name="degree_level_id"]').on('change', function() {
            $('#filterForm').submit();
        });
    });

</script>
@endpush
