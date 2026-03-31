{{-- resources/views/de/penyampaian-template/index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Pengiriman Formulir dan Templat Dokumen')

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
            <li class="breadcrumb-item active">Pengiriman Formulir dan Templat Dokumen</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h4 class="mb-1"><i class="bi bi-file-earmark"></i> Pengiriman Formulir dan Templat Dokumen</h4>
            <p class="text-muted mb-0">Kirim templat dokumen akreditasi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Pengiriman Formulir dan Templat Dokumen" :value="$stats['total']" description="Total pengiriman formulir dan templat dokumen saat ini" icon="file-earmark-text" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Dikirim Templat" :value="$stats['belum_dikirim']" description="Permohonan akreditasi dari PS yang menunggu pengiriman formulir dan templat dokumen" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Telah Dikirim Templat" :value="$stats['sudah_dikirim']" description="Jumlah formulir dan templat dokumen yang telah dikirim ke PS" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex align-items-center mb-4">
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
                                    Belum Dikirim Templat
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_TEMPLATE_LED_DIKIRIM ? 'selected' : '' }}>
                                    Telah Dikirim Templat
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
                        <h5 class="mb-0">Daftar Pengiriman Formulir dan Templat Dokumen</h5>
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
                                    <th width="25%">Permohonan Akreditasi</th>
                                    <th width="25%">Tanggal Pengiriman Templat Dokumen</th>
                                    <th width="25%">Status Pengiriman Formulir dan Templat Dokumen</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_template_led_dikirim)
                                        <small class="text-muted">
                                            <i class="bi bi-send"></i> Telah Dikirim:
                                            {{ $pengajuan->tanggal_template_led_dikirim->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                        <br>
                                        @else
                                        <small>-</small>
                                        @endif
                                        @if($pengajuan->tanggal_template_led_dikirim)
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> Telah Diterima:
                                            {{ $pengajuan->tanggal_template_led_dikirim->locale('id')->translatedFormat('d M Y') }}
                                        </small>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('borang_template','de','label_short_for') !!}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('de.penyampaian-template.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_SURAT_PENERIMAAN_DIKIRIM)
                                            <button type="button" class="btn btn-primary action-btn" title="Kirim Templat" onclick="kirimTemplate({{ $pengajuan->id }}, '{{ $pengajuan->judul }}')">
                                                <i class="bi bi-send"></i>
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
                        <p class="text-muted mt-3">Tidak ada data permohonan akreditasi</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kirim Invoice -->
@include('de.penyampaian-template.components.modal-kirim-invoice')

<!-- Modal Kirim Templat -->
<div class="modal fade" id="modalKirimTemplate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-send"></i> Kirim Templat Dokumen serta Formulir Pembayaran
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info alert-permanent mb-4">
                    <strong id="judulPermohonan"></strong>
                </div>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    {{-- <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-content" type="button">
                            <i class="bi bi-link-45deg"></i> Via Link
                        </button>
                    </li> --}}
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-content" type="button">
                            <i class="bi bi-upload"></i> Upload File
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab Link -->
                    {{-- <div class="tab-pane fade show active" id="link-content">
                        <form id="formKirimLink" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-text"></i> Link Templat Dokumen
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="url" name="template_led_link" class="form-control" placeholder="https://drive.google.com/..." required>
                                <small class="text-muted">
                                    Link untuk Templat Dokumen (Google Drive, Dropbox, dll)
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Link Formulir Pembayaran
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="url" name="template_pembayaran_link" class="form-control" placeholder="https://drive.google.com/..." required>
                                <small class="text-muted">
                                    Link untuk Formulir Pembayaran
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
                                    <i class="bi bi-send"></i> Kirim Kedua Templat via Link
                                </button>
                            </div>
                        </form>
                    </div> --}}

                    <!-- Tab Upload -->
                    <div class="tab-pane fade show active" id="upload-content">
                        <form id="formKirimUpload" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> File Formulir Pembayaran
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_template_pembayaran" class="form-control" accept=".pdf,.docx,.xlsx,.xls" required>
                                <small class="text-muted">
                                    {{-- Format: PDF, DOCX, XLSX, XLS. Maksimal 10MB --}}
                                    Format: XLSX/XLS. Maksimal 5MB
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-file-earmark-text"></i> File Templat Dokumen
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_template_led" class="form-control" accept=".pdf,.zip,.rar,.docx" required>
                                <small class="text-muted">
                                    {{-- Format: PDF, ZIP, RAR, DOCX. Maksimal 50MB --}}
                                    Format: ZIP/RAR. Maksimal 5MB
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keterangan (Opsional)</label>
                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3" placeholder="Tambahkan catatan untuk PS..." required>{{ old('keterangan', 'Templat Dokumen LED telah tersedia dalam satu berkas Ms.Word (.docx) (termasuk lembar pengesahan), sedangkan templat LKPS disediakan dalam file Excel (.xlsx).') }}</textarea>
                            </div>

                            <div class="alert alert-warning alert-permanent">
                                <i class="bi bi-info-circle"></i>
                                <small>Kedua file wajib diupload</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Upload dan Kirim Kedua Templat
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

        formUpload.action = `{{ route('de.penyampaian-template') }}/${id}/kirim-upload`;
        document.getElementById('judulPermohonan').textContent = judul;

        modal.show();
    }

</script>
@endpush
