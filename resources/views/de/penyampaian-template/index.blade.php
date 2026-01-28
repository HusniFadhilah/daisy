{{-- resources/views/de/penyampaian-template/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Penyampaian Template Dokumen')

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
            <li class="breadcrumb-item active">Penyampaian Template</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-file-earmark-arrow-down"></i> Penyampaian Template Dokumen</h4>
            <p class="text-muted mb-0">Kirim template dokumen akreditasi kepada program studi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-2 opacity-75">Total Sampai Tahap Penyampaian Template</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-2 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Total permohonan akreditasi PS yang aktif sampai tahap Penyampaian Template saat ini</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Belum Dikirim Template</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['belum_dikirim'] }}</h2>
                            <small class="opacity-75">Permohonan akreditasi dari PS yang menunggu pengiriman template</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card stat-card p-0" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <h6 class="mb-1 opacity-75">Sudah Dikirim Template</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0 fw-bold">{{ $stats['sudah_dikirim'] }}</h2>
                            <small class="opacity-75">Jumlah template yang telah dikirim ke PS</small>
                        </div>
                        <div class="stat-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-check-circle"></i>
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
                    <form method="GET" action="{{ route('de.penyampaian-template') }}">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label text-white">Cari Permohonan</label>
                            <input type="text" name="search" class="form-control" placeholder="Nomor/Nama prodi..." value="{{ request('search') }}">
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-3">
                            <label class="form-label text-white">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA ? 'selected' : '' }}>
                                    Belum Dikirim Template
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM ? 'selected' : '' }}>
                                    Sudah Dikirim Template
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
                            <a href="{{ route('de.penyampaian-template') }}" class="btn btn-outline-light">
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
                                    <th width="13%">Nomor Permohonan Akreditasi</th>
                                    <th width="20%">Program Studi</th>
                                    <th width="12%">Universitas</th>
                                    <th width="7%">Tahun</th>
                                    <th width="16%">Tanggal Surat</th>
                                    <th width="14%">Status</th>
                                    <th width="13%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <p>{{ $pengajuan->judul }}</p>
                                        <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
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
                                        @if($pengajuan->tanggal_surat_permohonan_dikirim)
                                        <small class="text-muted">
                                            <i class="bi bi-send"></i> Dikirim:
                                            {{ $pengajuan->tanggal_surat_permohonan_dikirim->format('d M Y') }}
                                        </small>
                                        <br>
                                        @endif
                                        @if($pengajuan->tanggal_surat_permohonan_diterima)
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> Diterima:
                                            {{ $pengajuan->tanggal_surat_permohonan_diterima->format('d M Y') }}
                                        </small>
                                        @endif
                                        @if($pengajuan->tanggal_surat_permohonan_ditolak)
                                        <small class="text-danger">
                                            <i class="bi bi-x-circle"></i> Ditolak:
                                            {{ $pengajuan->tanggal_surat_permohonan_ditolak->format('d M Y') }}
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('borang_template') !!}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('de.penyampaian-template.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PERMOHONAN_DITERIMA)
                                            <button type="button" class="btn btn-primary action-btn" title="Kirim Template" onclick="kirimTemplate({{ $pengajuan->id }}, '{{ $pengajuan->judul }}')">
                                                <i class="bi bi-send"></i>
                                            </button>
                                            @elseif($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM)
                                            @php
                                            $template = $pengajuan->dokumen()
                                            ->where('jenis_dokumen', 'borang_template')
                                            ->where('is_latest', true)
                                            ->first();
                                            @endphp
                                            @if($template)
                                            <a href="{{ route('de.penyampaian-template.download', ['id' => $pengajuan->id, 'jenis' => 'borang_template']) }}" class="btn btn-success action-btn" title="Download Template LED">
                                                <i class="bi bi-download"></i>
                                            </a>
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
                        <p class="text-muted mt-3">Tidak ada data permohonan akreditasi</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kirim Template -->
<div class="modal fade" id="modalKirimTemplate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-send"></i> Kirim Template Dokumen serta Formulir Pembayaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info alert-permanent mb-4">
                    <strong id="judulPermohonan"></strong>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-content" type="button">
                            <i class="bi bi-link-45deg"></i> Via Link
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-content" type="button">
                            <i class="bi bi-upload"></i> Upload File
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab Link -->
                    <div class="tab-pane fade show active" id="link-content">
                        <form id="formKirimLink" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-text"></i> Link Template Dokumen
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="url" name="template_led_link" class="form-control" placeholder="https://drive.google.com/..." required>
                                <small class="text-muted">
                                    Link untuk Template Dokumen (Google Drive, Dropbox, dll)
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Link Template Formulir Pembayaran
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="url" name="template_pembayaran_link" class="form-control" placeholder="https://drive.google.com/..." required>
                                <small class="text-muted">
                                    Link untuk Template Formulir Pembayaran
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan (Opsional)</label>
                                <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan untuk PS..."></textarea>
                            </div>

                            <div class="alert alert-warning alert-permanent">
                                <i class="bi bi-info-circle"></i>
                                <small>Pastikan kedua link dapat diakses oleh PS</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Kirim Kedua Template via Link
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab Upload -->
                    <div class="tab-pane fade" id="upload-content">
                        <form id="formKirimUpload" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-text"></i> File Template Dokumen
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_template_led" class="form-control" accept=".pdf,.zip,.rar,.docx" required>
                                <small class="text-muted">
                                    Format: PDF, ZIP, RAR, DOCX. Maksimal 50MB
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> File Template Formulir Pembayaran
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_template_pembayaran" class="form-control" accept=".pdf,.docx,.xlsx,.xls" required>
                                <small class="text-muted">
                                    Format: PDF, DOCX, XLSX, XLS. Maksimal 10MB
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan (Opsional)</label>
                                <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan untuk PS..."></textarea>
                            </div>

                            <div class="alert alert-warning alert-permanent">
                                <i class="bi bi-info-circle"></i>
                                <small>Kedua file wajib diupload</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Upload dan Kirim Kedua Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function kirimTemplate(id, judul) {
        const modal = new bootstrap.Modal(document.getElementById('modalKirimTemplate'));
        const formLink = document.getElementById('formKirimLink');
        const formUpload = document.getElementById('formKirimUpload');

        formLink.action = `{{ route('de.penyampaian-template') }}/${id}/kirim-link`;
        formUpload.action = `{{ route('de.penyampaian-template') }}/${id}/kirim-upload`;
        document.getElementById('judulPermohonan').textContent = judul;

        modal.show();
    }

</script>
@endpush
