{{-- resources\views\keuangan\pembayaran\index.blade.php --}}

@extends('layouts.template.app')

@section('title', 'Validasi Pembayaran')

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h3 class="mb-1">
                <i class="bi bi-credit-card"></i>
                Validasi Pembayaran
            </h3>
            <p class="text-muted mb-0">
                Daftar pembayaran yang menunggu validasi
            </p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <x-stat-card title="Total Formulir" :value="$stats['total']" description="Formulir yang telah diupload" icon="file-earmark-check" gradient="purple" iconBg="success-subtle" />
        </div>

        <div class="col">
            <x-stat-card title="Hari Ini" :value="$stats['today']" description="Upload hari ini" icon="calendar-check" gradient="pink" iconBg="success-subtle" />
        </div>

        <div class="col">
            <x-stat-card title="Menunggu Validasi" :value="$stats['menunggu_verifikasi']" description="Perlu divalidasi" icon="clock-history" gradient="orange" iconBg="success-subtle" />
        </div>

        <div class="col">
            <x-stat-card title="Tervalidasi" :value="$stats['terverifikasi']" description="Telah divalidasi" icon="check-circle" gradient="green" iconBg="success-subtle" />
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
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-bordered mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Permohonan Akreditasi</th>
                            <th>Invoice</th>
                            <th>Jumlah</th>
                            <th>Tanggal Pembayaran</th>
                            <th>Status Pembayaran</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- ✅ Iterasi $pembayarans (PengajuanPembayaran) --}}
                        @forelse($pembayarans as $item)
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration + ($pembayarans->currentPage() - 1) * $pembayarans->perPage() }}
                            </td>

                            <td>
                                {{-- Data prodi diakses via relasi pengajuan --}}
                                {!! $item->pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                            </td>

                            <td>
                                <strong>{{ $item->nomor_invoice ?? '-' }}</strong>
                                @if($item->jenis_pembayaran === 'banding')
                                <br><span class="badge bg-info">Banding</span>
                                @endif
                            </td>

                            <td>
                                Rp {{ number_format($item->jumlah_pembayaran ?? 0, 0, ',', '.') }}
                            </td>

                            <td>
                                @if($item->tanggal_pembayaran)
                                {{ \App\Libraries\Date::tglwaktu($item->tanggal_pembayaran) }}
                                @else
                                -
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge bg-{{ $item->status_pembayaran_badge }}">
                                    {{ $item->status_pembayaran_label }}
                                </span>
                            </td>

                            <td class="text-center">
                                {{-- ✅ Route sekarang pakai $item->id (PengajuanPembayaran.id) --}}
                                <a href="{{ route('keuangan.pembayaran.show', $item->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
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

        @if($pembayarans->hasPages())
        <div class="card-footer">
            {{ $pembayarans->onEachSide(1)->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
