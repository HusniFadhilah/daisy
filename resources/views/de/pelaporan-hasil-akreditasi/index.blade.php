{{-- resources/views/de/pelaporan-hasil-akreditasi/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Pelaporan Hasil Akreditasi')

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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .badge-peringkat {
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .doc-indicator {
        font-size: 10px;
        padding: 4px 8px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pelaporan Hasil Akreditasi</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="bi bi-megaphone"></i> Pelaporan Hasil Akreditasi</h4>
            <p class="text-muted mb-0">Kelola pelaporan dan dokumentasi hasil akreditasi program studi</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Hasil Ditetapkan" :value="$stats['total']" description="Siap atau sudah dilaporkan" icon="award" gradient="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Belum Dilaporkan" :value="$stats['belum_dilaporkan']" description="Menunggu upload dokumen" icon="hourglass-split" gradient="linear-gradient(135deg, #f093fb 0%, #f5576c 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Sudah Dilaporkan" :value="$stats['sudah_dilaporkan']" description="Pelaporan selesai" icon="check-circle" gradient="linear-gradient(135deg, #11998e 0%, #38ef7d 100%)" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Punya Sertifikat" :value="$stats['punya_sertifikat']" description="Sertifikat sudah terbit" icon="patch-check" gradient="linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)" />
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
                    <form method="GET" action="{{ route('de.pelaporan-hasil-akreditasi') }}">
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
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DITETAPKAN ? 'selected' : '' }}>
                                    Belum Dilaporkan
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DIUMUMKAN ? 'selected' : '' }}>
                                    Diumumkan (Belum Dilaporkan)
                                </option>
                                <option value="{{ \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN }}" {{ request('status') == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN ? 'selected' : '' }}>
                                    Sudah Dilaporkan
                                </option>
                            </select>
                        </div>

                        <!-- Peringkat Filter -->
                        <div class="mb-3">
                            <label class="form-label text-white">Peringkat</label>
                            <select name="peringkat" class="form-select">
                                <option value="">Semua Peringkat</option>
                                <option value="Unggul" {{ request('peringkat') == 'Unggul' ? 'selected' : '' }}>
                                    Unggul
                                </option>
                                <option value="Baik Sekali" {{ request('peringkat') == 'Baik Sekali' ? 'selected' : '' }}>
                                    Baik Sekali
                                </option>
                                <option value="Baik" {{ request('peringkat') == 'Baik' ? 'selected' : '' }}>
                                    Baik
                                </option>
                                <option value="Tidak Terakreditasi" {{ request('peringkat') == 'Tidak Terakreditasi' ? 'selected' : '' }}>
                                    Tidak Terakreditasi
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
                            <a href="{{ route('de.pelaporan-hasil-akreditasi') }}" class="btn btn-outline-light">
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
                        <h5 class="mb-0">Daftar Hasil Akreditasi</h5>
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
                                    <th width="18%">Program Studi</th>
                                    <th width="10%">Peringkat</th>
                                    <th width="8%">Skor</th>
                                    <th width="12%">Dokumen</th>
                                    <th width="12%">Status</th>
                                    <th width="10%">Tgl Laporan</th>
                                    <th width="10%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pengajuans as $index => $pengajuan)
                                @php
                                $peringkat = $pengajuan->peringkat_final;
                                $skor = $pengajuan->skor_final;

                                $badgeClass = match($peringkat) {
                                'Unggul' => 'success',
                                'Baik Sekali' => 'primary',
                                'Baik' => 'info',
                                'Tidak Terakreditasi' => 'danger',
                                default => 'secondary'
                                };

                                $hasLaporan = $pengajuan->dokumen->where('jenis_dokumen', 'laporan_hasil')->isNotEmpty();
                                $hasSertifikat = $pengajuan->dokumen->where('jenis_dokumen', 'sertifikat')->isNotEmpty();
                                @endphp
                                <tr>
                                    <td>{{ $pengajuans->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $pengajuan->nomor_pengajuan }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $pengajuan->tahun_akreditasi }}</small>
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
                                        <span class="badge badge-peringkat bg-{{ $badgeClass }}">
                                            {{ $peringkat }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($skor, 2) }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if($hasLaporan)
                                            <span class="badge bg-info doc-indicator" title="Punya Laporan">
                                                <i class="bi bi-file-text"></i> L
                                            </span>
                                            @endif
                                            @if($hasSertifikat)
                                            <span class="badge bg-success doc-indicator" title="Punya Sertifikat">
                                                <i class="bi bi-patch-check"></i> S
                                            </span>
                                            @endif
                                            @if(!$hasLaporan && !$hasSertifikat)
                                            <span class="text-muted">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($pengajuan->status == \App\Models\PengajuanAkreditasi::STATUS_HASIL_DILAPORKAN)
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Dilaporkan
                                        </span>
                                        @else
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass-split"></i> Belum
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pengajuan->tanggal_pelaporan_hasil)
                                        <small>{{ $pengajuan->tanggal_pelaporan_hasil->format('d M Y') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('de.pelaporan-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-info action-btn" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
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
                        <p class="text-muted mt-3">Tidak ada hasil akreditasi yang siap untuk dilaporkan</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
