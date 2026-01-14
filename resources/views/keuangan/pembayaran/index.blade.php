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
                Daftar pembayaran yang menunggu verifikasi oleh bagian Keuangan
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-permanent">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-permanent">
        <i class="bi bi-x-circle"></i> {{ session('error') }}
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('keuangan.pembayaran.index') }}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-10">
                        <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari nomor pengajuan, program studi, atau nomor invoice…">
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
                Daftar Pembayaran
            </h5>
            <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Nomor Pengajuan</th>
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
                                <strong>{{ $item->nomor_pengajuan }}</strong><br>
                                <small class="text-muted">{{ $item->tahun_akreditasi }}</small>
                            </td>

                            <td>{{ optional($item->studyProgram)->name ?? '-' }}</td>
                            <td>{{ optional(optional($item->studyProgram)->university)->name ?? '-' }}</td>

                            <td><strong>{{ optional($pembayaran)->nomor_invoice ?? '-' }}</strong></td>

                            <td>
                                Rp {{ number_format(optional($pembayaran)->jumlah_pembayaran ?? 0, 0, ',', '.') }}
                            </td>

                            <td>
                                @if(optional($pembayaran)->tanggal_pembayaran)
                                {{ \App\Libraries\Date::tglIndo($pembayaran->tanggal_pembayaran) }}
                                @else
                                -
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge bg-warning text-dark">
                                    {{ strtoupper(optional($pembayaran)->status_pembayaran ?? '-') }}
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
                                    Tidak ada pembayaran yang menunggu verifikasi.
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
