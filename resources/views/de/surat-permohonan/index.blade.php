@extends('layouts.template.app')

@section('title', 'Surat Permohonan dari Program Studi')

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

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Surat Permohonan</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-envelope"></i> Surat Permohonan Akreditasi</h4>
            <p class="text-muted mb-0">Kelola surat permohonan akreditasi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Surat Permohonan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Total surat permohonan akreditasi saat ini</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Menunggu Surat Permohonan</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['menunggu'] }}</h2>
                            <small class="opacity-75">Pengingat sudah dikirim, tetapi PS belum mengajukan surat permohonan akreditasi</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #8ebb0aff 0%, #c0c30dff 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Surat Permohonan Sudah Dikirim (Belum Ditanggapi DE)</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['dikirim'] }}</h2>
                            <small class="opacity-75">Surat permohonan telah dikirim oleh PS, tetapi belum ditanggapi oleh DE</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Surat Permohonan Sudah Ditanggapi DE</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['diterima'] }}</h2>
                            <small class="opacity-75">Surat permohonan telah dikirim oleh PS, dan telah ditanggapi oleh DE</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    {{-- <div class="d-flex align-items-center mb-4">
        <div class="ms-auto text-end">
            <button type="button" class="btn btn-primary mb-1" data-bs-toggle="modal" data-bs-target="#modalKirimInvoice">
                <i class="bi bi-send"></i> Kirim Invoice
            </button>
            <div>
                <small>
                    Terdapat {{ $countPengajuanList }} permohonan akreditasi
    yang perlu dikirimi invoice
    </small>
</div>
</div>
</div> --}}

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
                <form method="GET" action="{{ route('de.surat-permohonan') }}">
                    <!-- Search -->
                    <div class="mb-3">
                        <label class="form-label text-white">Cari Permohonan Akreditasi</label>
                        <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="mb-3">
                        <label class="form-label text-white">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM ? 'selected' : '' }}>
                                Menunggu Surat
                            </option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM ? 'selected' : '' }}>
                                Surat Dikirim PS
                            </option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA ? 'selected' : '' }}>
                                Surat Diterima DE
                            </option>
                            <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITOLAK ? 'selected' : '' }}>
                                Surat Belum Diterima DE
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
                        <a href="{{ route('de.surat-permohonan') }}" class="btn btn-outline-light">
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
                    <h5 class="mb-0">Daftar Permohonan Akreditasi</h5>
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
                                <th width="15%">Nomor Permohonan Akreditasi</th>
                                <th width="25%">Program Studi</th>
                                <th width="12%">Universitas</th>
                                <th width="10%">Tahun</th>
                                <th width="13%">Tanggal Pengingat</th>
                                <th width="10%">Status</th>
                                <th width="10%" class="text-center">Aksi</th>
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
                                            {{ $pengajuan->studyProgram->degreeLevel->name ?? '-' }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <small>{{ $pengajuan->studyProgram->university->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $pengajuan->tahun_akreditasi }}</span>
                                </td>
                                <td>
                                    @if($pengajuan->tanggal_pengingat)
                                    <small>{{ $pengajuan->tanggal_pengingat->format('d M Y') }}</small>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {!! $pengajuan->getCustomBadgeLastStatus('surat_permohonan_ps') !!}
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('de.surat-permohonan.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DIKIRIM)
                                        @php
                                        $hasSurat = $pengajuan->dokumen()
                                        ->where('jenis_dokumen', 'surat_permohonan')
                                        ->where('is_latest', true)
                                        ->exists();
                                        @endphp

                                        @if($hasSurat)
                                        <button type="button" class="btn btn-success action-btn" title="Terima Surat" onclick="terimaSurat({{ $pengajuan->id }}, '{{ $pengajuan->judul }}')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-secondary action-btn" title="Belum ada surat" disabled>
                                            <i class="bi bi-hourglass-split"></i>
                                        </button>
                                        @endif
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
                    <p class="text-muted mt-3">Tidak ada data Permohonan Akreditasi</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal Terima Surat -->
<div class="modal fade" id="modalTerimaSurat" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formTerimaSurat" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Terima Surat Permohonan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menerima surat permohonan untuk:</p>
                    <div class="alert alert-info alert-permanent">
                        <strong id="nomorPengajuan"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle"></i>
                        Status akan berubah menjadi "Surat Permohonan Diterima" dan dapat dilanjutkan ke tahap berikutnya.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Terima Surat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function terimaSurat(id, nomorPengajuan) {
        const modal = new bootstrap.Modal(document.getElementById('modalTerimaSurat'));
        const form = document.getElementById('formTerimaSurat');

        form.action = `{{ route('de.surat-permohonan') }}/${id}/terima`;
        document.getElementById('nomorPengajuan').textContent = nomorPengajuan;

        modal.show();
    }

</script>
@endpush
