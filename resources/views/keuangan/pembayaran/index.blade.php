@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="bi bi-credit-card"></i>
                Validasi Pembayaran
            </h2>
            <p class="text-muted mb-0">
                Daftar pembayaran yang menunggu validasi
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card p-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Total Formulir</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['total'] }}</h2>
                            <small class="opacity-75">Formulir yang telah diupload</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="bi bi-file-earmark-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card p-2" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Hari Ini</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['today'] }}</h2>
                            <small class="opacity-75">Upload hari ini</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="bi bi-calendar-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card p-2" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Menunggu Validasi</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['menunggu_verifikasi'] }}</h2>
                            <small class="opacity-75">Perlu divalidasi</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="bi bi-clock-history fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card stat-card p-2" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 opacity-75">Tervalidasi</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['terverifikasi'] }}</h2>
                            <small class="opacity-75">Telah divalidasi</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('keuangan.pembayaran.index') }}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-10">
                        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari Nomor permohonan, program studi, atau nomor invoice…">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Daftar Validasi Pembayaran (Klik Detail untuk melakukan validasi)
            </h5>
            {{-- <span class="badge bg-warning text-dark">Menunggu Validasi</span> --}}
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Pengajuan</th>
                            <th>Program Studi</th>
                            <th>Universitas</th>
                            <th>Invoice</th>
                            <th>Jumlah</th>
                            <th>Tanggal Bayar</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuan as $item)
                        @php $pembayaran = $item->pembayaran; @endphp
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration + ($pengajuan->currentPage() - 1) * $pengajuan->perPage() }}
                            </td>

                            <td>
                                <p>{{ $item->judul }}</p>
                                <small class="text-muted">{{ $item->nomor_pengajuan }}</small>
                            </td>

                            <td>{{ optional($item->studyProgram)->name ?? '-' }}</td>
                            <td>{{ optional(optional($item->studyProgram)->university)->name ?? '-' }}</td>

                            <td><strong>{{ optional($pembayaran)->nomor_invoice ?? '-' }}</strong></td>

                            <td>
                                Rp {{ number_format(optional($pembayaran)->jumlah_pembayaran ?? 0, 0, ',', '.') }}
                            </td>

                            <td>
                                @if(optional($pembayaran)->tanggal_pembayaran)
                                {{ \App\Libraries\Date::tglwaktu($pembayaran->tanggal_pembayaran) }}
                                @else
                                -
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge bg-warning text-dark">
                                    {{ strtoupper(optional($pembayaran)->status_pembayaran_label ?? '-') }}
                                </span>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('keuangan.pembayaran.show', $item->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox fs-2 text-muted"></i>
                                <p class="mb-0 text-muted mt-2">
                                    Tidak ada pembayaran yang menunggu validasi.
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
