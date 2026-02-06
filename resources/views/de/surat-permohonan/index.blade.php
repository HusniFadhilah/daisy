@extends('layouts.template.app')

@section('title', 'Permohonan Akreditasi dari Program Studi')

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
            <li class="breadcrumb-item active">Permohonan Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-envelope"></i> Permohonan Akreditasi</h4>
            <p class="text-muted mb-0">Kelola permohonan Akreditasi</p>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-warning alert-permanent fade show" style="border-left: 4px solid #ffc107;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{-- <strong>Perhatian!</strong>
                Ada <strong>{{ $stats['pengingat_bulan_target'] }}</strong> PS yang perlu diingatkan tentang masa akreditasi berakhir dalam 7 bulan dari sekarang ({{ $stats['pengingat']['target_month_label'] }}).
                <a href="javascript:void(0)" class="mt-2 btn-reminder-bg" onclick="openReminderModal()">
                    Kirim Pengingat →
                </a> --}}
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Permohonan Akreditasi" :value="$stats['total']" description="Total Permohonan Akreditasi saat ini" icon="file-earmark-text" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="PS Belum Mengajukan Permohonan Akreditasi" :value="$stats['menunggu']" description="Pengingat masa akreditasi telah dikirim, tetapi PS belum mengajukan Permohonan Akreditasi" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Permohonan Akreditasi Telah Dikirim, tetapi Belum Ditanggapi" :value="$stats['dikirim']" description="Permohonan Akreditasi telah dikirim oleh PS, tetapi belum ditanggapi" icon="clock" gradient="linear-gradient(135deg, #8ebb0aff 0%, #c0c30dff 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Permohonan Akreditasi Telah Ditanggapi" :value="$stats['diterima']" description="Permohonan Akreditasi telah dikirim oleh PS, dan telah ditanggapi" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
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
                                    <th width="30%">Permohonan Akreditasi</th>
                                    <th width="20%">Tanggal Pengingat Masa Akreditasi</th>
                                    <th width="25%">Status Permohonan Akreditasi</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <p>{{ $pengajuan->judul_short }}</p>
                                        <small>{{ $pengajuan->studyProgram->university->name ?? '-' }}</small>
                                        <br>
                                        <small class="text-muted">{{ $pengajuan->nomor_pengajuan }}</small>
                                        <br>
                                        <small class="text-muted">Dibuat pada: {{ \App\Libraries\Date::tglIndo($pengajuan->created_at) }}</small>
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_pengingat)
                                        <small>{{ $pengajuan->tanggal_pengingat->format('d M Y') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $pengajuan->getCustomBadgeLastStatus('surat_permohonan_ps','de','label_short_for') !!}
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
                        <i class="bi bi-check-circle"></i> Tanggapi Permohonan Akreditasi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Anda akan menanggapi permohonan akreditasi:</p>
                    <div class="alert alert-info alert-permanent">
                        <strong id="nomorPengajuan"></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-info-circle"></i>
                        Status akan berubah menjadi "Permohonan Akreditasi Ditanggapi" dan dapat dilanjutkan ke tahap berikutnya.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Tanggapi Surat
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
