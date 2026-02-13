{{-- resources/views/de/pelaporan-banding/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Pelaporan Banding')

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

    .badge-laporan {
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
            <li class="breadcrumb-item active">Pelaporan Banding</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-file-earmark-ruled"></i> Pelaporan Banding</h4>
            <p class="text-muted mb-0">Kelola pelaporan hasil pelaksanaan banding akreditasi</p>
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
                            <small class="opacity-75">Total yang perlu dilaporkan</small>
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
                    <h6 class="mb-1 opacity-75">Belum Dilaporkan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['belum_dilaporkan'] }}</h2>
                            <small class="opacity-75">Menunggu upload laporan</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Sudah Dilaporkan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['sudah_dilaporkan'] }}</h2>
                            <small class="opacity-75">Laporan sudah diupload</small>
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
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Banding" :value="$stats['total']" description="Total yang perlu dilaporkan" icon="file-earmark-break" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Dilaporkan" :value="$stats['belum_dilaporkan']" description="Menunggu upload laporan" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Dilaporkan" :value="$stats['sudah_dilaporkan']" description="Laporan sudah diupload" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
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
                    <form method="GET" action="{{ route('de.pelaporan-banding') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Pengajuan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status Pelaporan</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN ? 'selected' : '' }}>
                                    Belum Dilaporkan
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
                            <a href="{{ route('de.pelaporan-banding') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Pelaporan Banding</h5>
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
                                    <th width="10%">Hasil Banding</th>
                                    <th width="13%">Tgl Pelaksanaan</th>
                                    <th width="12%">Status Laporan</th>
                                    <th width="10%">Dokumen</th>
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
                                            Dibuat pada: {{ $pengajuan->created_at->locale('id')->translatedFormat('d M Y') }}
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
                                        @if($pengajuan->hasil_banding === 'diterima')
                                        <span class="badge badge-laporan bg-success">
                                            <i class="bi bi-check-circle"></i> Diterima
                                        </span>
                                        @else
                                        <span class="badge badge-laporan bg-danger">
                                            <i class="bi bi-x-circle"></i> Ditolak
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_pelaksanaan_banding)
                                        <small>{{ $pengajuan->tanggal_pelaksanaan_banding->locale('id')->translatedFormat('d M Y') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN)
                                        <span class="badge badge-laporan bg-success">
                                            <i class="bi bi-check-circle"></i> Sudah Dilaporkan
                                        </span>
                                        @if($pengajuan->tanggal_pelaporan_banding)
                                        <br><small class="text-muted">
                                            {{ $pengajuan->tanggal_pelaporan_banding->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                        @endif
                                        @else
                                        <span class="badge badge-laporan bg-warning text-dark">
                                            <i class="bi bi-hourglass-split"></i> Belum Dilaporkan
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                        $hasLaporan = $pengajuan->dokumen
                                        ->where('jenis_dokumen', 'laporan_banding')
                                        ->isNotEmpty();
                                        @endphp
                                        @if($hasLaporan)
                                        <span class="badge bg-info">
                                            <i class="bi bi-file-earmark-pdf"></i> Ada
                                        </span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('de.pelaporan-banding.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($hasLaporan)
                                            <a href="{{ route('de.pelaporan-banding.download-laporan', $pengajuan->id) }}" class="btn btn-success action-btn" title="Download Laporan">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @endif

                                            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN)
                                            <button type="button" class="btn btn-primary action-btn" title="Upload Laporan" onclick="showUploadModal({{ $pengajuan->id }}, '{{ $pengajuan->nomor_pengajuan }}')">
                                                <i class="bi bi-upload"></i>
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
                        <p class="text-muted mt-3">Tidak ada data pelaporan banding</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Laporan -->
<div class="modal fade" id="modalUploadLaporan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formUploadLaporan" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-upload"></i> Upload Laporan Banding
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Upload laporan banding untuk:</p>
                    <div class="alert alert-info">
                        <strong id="nomorPengajuan"></strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            File Laporan <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="file_laporan" class="form-control @error('file_laporan') is-invalid @enderror" accept=".pdf,.doc,.docx" required>
                        <small class="text-muted">Format: PDF, DOC, DOCX (Max: 10MB)</small>
                        @error('file_laporan')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Catatan tambahan tentang laporan..."></textarea>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-info-circle"></i>
                        Setelah upload, status akan berubah menjadi <strong>"Banding Dilaporkan"</strong> dan dapat dilanjutkan ke tahap berikutnya.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showUploadModal(id, nomorPengajuan) {
        const modal = new bootstrap.Modal(document.getElementById('modalUploadLaporan'));
        const form = document.getElementById('formUploadLaporan');

        form.action = `{{ route('de.pelaporan-banding') }}/${id}/upload-laporan`;
        document.getElementById('nomorPengajuan').textContent = nomorPengajuan;

        modal.show();
    }

</script>
@endpush
