@extends('layouts.template.app')

@section('title', 'Pengajuan Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-file-earmark-text"></i> Pengajuan Akreditasi</h2>
            <p class="text-muted mb-0">Kelola pengajuan akreditasi program studi</p>
        </div>
        <a href="{{ route('pengajuan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Ajukan Akreditasi Baru
        </a>
    </div>

    <!-- Alert untuk pengingat yang belum dilengkapi -->
    @php
    $pengingatBelumLengkap = $pengajuans->filter(function($p) {
    return $p->status === 'pengingat_dikirim' && is_null($p->id_user_pengaju);
    });
    @endphp

    @if($pengingatBelumLengkap->count() > 0)
    <div class="alert alert-warning alert-permanent fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Perhatian!</strong> Anda memiliki {{ $pengingatBelumLengkap->count() }} pengingat akreditasi yang belum dilengkapi.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Cari nomor pengajuan..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="pengingat_dikirim" {{ request('status') == 'pengingat_dikirim' ? 'selected' : '' }}>Pengingat Dikirim</option>
                        <option value="surat_permohonan_diterima" {{ request('status') == 'surat_permohonan_diterima' ? 'selected' : '' }}>Surat Diterima</option>
                        <option value="borang_dikirim" {{ request('status') == 'borang_dikirim' ? 'selected' : '' }}>Borang Dikirim</option>
                        <option value="draft_borang_diterima" {{ request('status') == 'draft_borang_diterima' ? 'selected' : '' }}>Draft Diterima</option>
                        <option value="review_kesiapan_siap" {{ request('status') == 'review_kesiapan_siap' ? 'selected' : '' }}>Review: Siap</option>
                        <option value="review_kesiapan_belum_siap" {{ request('status') == 'review_kesiapan_belum_siap' ? 'selected' : '' }}>Review: Belum Siap</option>
                        <option value="menunggu_pembayaran" {{ request('status') == 'menunggu_pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                        <option value="pembayaran_diterima" {{ request('status') == 'pembayaran_diterima' ? 'selected' : '' }}>Pembayaran Diterima</option>
                        <option value="borang_final_diterima" {{ request('status') == 'borang_final_diterima' ? 'selected' : '' }}>Borang Final Diterima</option>
                        <option value="lanjut_ke_ak" {{ request('status') == 'lanjut_ke_ak' ? 'selected' : '' }}>Lanjut ke AK</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('pengajuan') }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="row">
        @forelse($pengajuans as $pengajuan)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 {{ $pengajuan->status === 'pengingat_dikirim' && is_null($pengajuan->id_user_pengaju) ? 'border-warning' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0">{{ $pengajuan->nomor_pengajuan }}</h5>
                        <span class="badge {{ $pengajuan->status_badge_class }}">
                            {{ $pengajuan->status_label }}
                        </span>
                    </div>

                    <p class="mb-2">
                        <strong>{{ $pengajuan->studyProgram->name }}</strong>
                    </p>

                    <div class="mb-3">
                        @if($pengajuan->tahun_akreditasi)
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar"></i>
                            Tahun: {{ $pengajuan->tahun_akreditasi }}
                        </small>
                        @endif

                        @if($pengajuan->jenis_akreditasi)
                        <small class="text-muted d-block">
                            <i class="bi bi-tag"></i>
                            {{ ucfirst($pengajuan->jenis_akreditasi) }}
                        </small>
                        @endif

                        <small class="text-muted">
                            <i class="bi bi-clock"></i>
                            {{ $pengajuan->created_at->format('d M Y') }}
                        </small>

                        @if($pengajuan->deskEvaluator)
                        <small class="text-muted d-block">
                            <i class="bi bi-person"></i>
                            DE: {{ $pengajuan->deskEvaluator->name }}
                        </small>
                        @endif
                    </div>

                    {{-- ✅ BUTTON BERBEDA UNTUK STATUS PENGINGAT_DIKIRIM --}}
                    @if($pengajuan->status === 'pengingat_dikirim' && is_null($pengajuan->id_user_pengaju))
                    <a href="{{ route('pengajuan.create', ['pengajuan_id' => $pengajuan->id]) }}" class="btn btn-warning btn-sm w-100">
                        <i class="bi bi-pencil-square"></i> Lengkapi Data Pengajuan
                    </a>
                    @else
                    <a href="{{ route('pengajuan.show', $pengajuan->id) }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-eye"></i> Lihat Detail
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada pengajuan akreditasi</p>
                    <a href="{{ route('pengajuan.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Ajukan Sekarang
                    </a>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($pengajuans->hasPages())
    <div class="d-flex justify-content-center">
        {{ $pengajuans->links() }}
    </div>
    @endif
</div>
@endsection
