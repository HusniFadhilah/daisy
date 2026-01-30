{{-- resources/views/de/pelaksanaan-banding/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Pelaksanaan Banding')

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

    .badge-banding {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
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
            <li class="breadcrumb-item active">Pelaksanaan Banding</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-arrow-repeat"></i> Pelaksanaan Banding</h4>
            <p class="text-muted mb-0">Kelola pelaksanaan banding hasil akreditasi program studi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Banding</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Total pengajuan banding yang masuk</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-file-earmark-break"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Banding Diajukan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['diajukan'] }}</h2>
                            <small class="opacity-75">Menunggu untuk dilaksanakan</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Sedang Dilaksanakan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['sedang_dilaksanakan'] }}</h2>
                            <small class="opacity-75">Proses banding sedang berlangsung</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-arrow-repeat"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Banding Selesai</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['selesai'] }}</h2>
                            <small class="opacity-75">Banding telah selesai dan dilaporkan</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hasil Banding Statistics -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Banding Diterima</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['diterima'] }}</h2>
                            <small class="opacity-75">Hasil akreditasi direvisi sesuai banding</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Banding Ditolak</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['ditolak'] }}</h2>
                            <small class="opacity-75">Hasil akreditasi tetap sesuai awal</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-x-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Content -->
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card filter-card">
                <div class="card-header border-0">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Filter & Pencarian
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('de.pelaksanaan-banding') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Pengajuan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN ? 'selected' : '' }}>
                                    Banding Diajukan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN ? 'selected' : '' }}>
                                    Sedang Dilaksanakan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN ? 'selected' : '' }}>
                                    Sudah Dilaporkan
                                </option>
                            </select>
                        </div>

                        <!-- Hasil Banding Filter -->
                        <div class="mb-3">
                            <label class="form-label text-white">Hasil Banding</label>
                            <select name="hasil_banding" class="form-select">
                                <option value="">Semua Hasil</option>
                                <option value="diterima" {{ request('hasil_banding') == 'diterima' ? 'selected' : '' }}>
                                    Diterima
                                </option>
                                <option value="ditolak" {{ request('hasil_banding') == 'ditolak' ? 'selected' : '' }}>
                                    Ditolak
                                </option>
                            </select>
                        </div>

                        <!-- University -->
                        <div class="mb-3">
                            <label class="form-label text-white">Universitas</label>
                            <select name="university_id" class="form-select">
                                <option value="">Semua Universitas</option>
                                @foreach($universities as $univ)
                                <option value="{{ $univ->id }}" {{ request('university_id') == $univ->id ? 'selected' : '' }}>
                                    {{ $univ->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tahun -->
                        <div class="mb-3">
                            <label class="form-label text-white">Tahun Akreditasi</label>
                            <select name="tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                @foreach($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ request('tahun') == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-light">
                                <i class="bi bi-search"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('de.pelaksanaan-banding') }}" class="btn btn-outline-light">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Pengajuan Banding</h5>
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
                                    <th width="15%">Permohonan Akreditasi</th>
                                    <th width="20%">Program Studi</th>
                                    <th width="12%">Hasil Awal</th>
                                    <th width="13%">Tanggal Banding</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Hasil Banding</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <p>{{ $pengajuan->judul }}</p>
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
                                                {{ $pengajuan->studyProgram->university->name ?? '-' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($pengajuan->asesmen && $pengajuan->asesmen->hasil)
                                        @php
                                        $hasil = $pengajuan->asesmen->hasil;
                                        $peringkat = $hasil->peringkat_akreditasi ?? '-';
                                        $skor = $hasil->skor_final ?? 0;

                                        $badgeClass = match($peringkat) {
                                        'Unggul' => 'bg-success',
                                        'Baik Sekali' => 'bg-primary',
                                        'Baik' => 'bg-info',
                                        default => 'bg-secondary'
                                        };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $peringkat }}</span>
                                        <br>
                                        <small class="text-muted">Skor: {{ number_format($skor, 2) }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_banding)
                                        <small>{{ $pengajuan->tanggal_banding->format('d M Y') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                        $statusBadge = match($pengajuan->status) {
                                        \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN =>
                                        '<span class="badge badge-banding bg-warning text-dark">Diajukan</span>',
                                        \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN =>
                                        '<span class="badge badge-banding bg-info">Sedang Dilaksanakan</span>',
                                        \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN =>
                                        '<span class="badge badge-banding bg-success">Selesai</span>',
                                        default =>
                                        '<span class="badge badge-banding bg-secondary">-</span>'
                                        };
                                        @endphp
                                        {!! $statusBadge !!}
                                    </td>
                                    <td>
                                        @if($pengajuan->hasil_banding)
                                        @if($pengajuan->hasil_banding === 'diterima')
                                        <span class="badge badge-banding bg-success">
                                            <i class="bi bi-check-circle"></i> Diterima
                                        </span>
                                        @else
                                        <span class="badge badge-banding bg-danger">
                                            <i class="bi bi-x-circle"></i> Ditolak
                                        </span>
                                        @endif
                                        @else
                                        <span class="text-muted">Belum ada</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('de.pelaksanaan-banding.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN)
                                            <button type="button" class="btn btn-primary action-btn" title="Mulai Pelaksanaan" onclick="mulaiPelaksanaan({{ $pengajuan->id }}, '{{ $pengajuan->nomor_pengajuan }}')">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                            @endif
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
                        <p class="text-muted mt-3">Tidak ada pengajuan banding</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mulai Pelaksanaan -->
<div class="modal fade" id="modalMulaiPelaksanaan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formMulaiPelaksanaan" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-play-circle"></i> Mulai Pelaksanaan Banding
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan memulai pelaksanaan banding untuk:</p>
                    <div class="alert alert-info">
                        <strong id="nomorPengajuan"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan pelaksanaan banding..."></textarea>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle"></i>
                        Status akan berubah menjadi "Banding Dilaksanakan".
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-circle"></i> Mulai Pelaksanaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function mulaiPelaksanaan(id, nomorPengajuan) {
        const modal = new bootstrap.Modal(document.getElementById('modalMulaiPelaksanaan'));
        const form = document.getElementById('formMulaiPelaksanaan');

        form.action = `{{ route('de.pelaksanaan-banding') }}/${id}/mulai-pelaksanaan`;
        document.getElementById('nomorPengajuan').textContent = nomorPengajuan;

        modal.show();
    }

</script>
@endpush
