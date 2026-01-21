@extends('layouts.template.app')

@section('title', 'Permohonan Akreditasi')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-file-earmark-text"></i> Permohonan Akreditasi</h2>
            <p class="text-muted mb-0">Kelola permohonan akreditasi program studi</p>
        </div>
        <a href="{{ route('pengajuan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Permohonan Akreditasi Baru
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
                        @foreach (\App\Models\PengajuanAkreditasi::statusMap() as $key => $status)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                            {{ $status['label'] }}
                        </option>
                        @endforeach
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
        @php
        $timeline = $pengajuan->timelineItems();
        $currentItem = collect($timeline)->firstWhere('state', 'done');

        // fallback aman
        if(!$currentItem){
        $currentItem = collect($timeline)->lastWhere('state', 'done') ?? collect($timeline)->first();
        }

        $isDone = ($currentItem['state'] ?? null) === 'done';
        $isCurrent = ($currentItem['state'] ?? null) === 'current';
        $itemColor = $currentItem['color'] ?? 'secondary';

        // icon sesuai pola yang kamu mau
        $iconClass = $isDone ? 'bi-check-circle-fill' : ($isCurrent ? 'bi-hourglass-split' : 'bi-circle');
        $iconColor = $isDone ? 'text-success' : ($isCurrent ? 'text-' . $itemColor : 'text-muted');
        $judulPengajuan = $pengajuan->judul;
        @endphp
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 {{ $pengajuan->status === \App\Models\PengajuanAkreditasi::STATUS_PENGINGAT_DIKIRIM && is_null($pengajuan->id_user_pengaju) ? 'border-warning' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="me-2" style="min-width:0;">
                            <h5 class="card-title mb-1 text-wrap" title="{{ $judulPengajuan }}">
                                {{ $judulPengajuan }}
                            </h5>

                            <small class="text-muted d-block text-wrap" title="{{ $pengajuan->studyProgram->university->name ?? '' }}">
                                {{ $pengajuan->studyProgram->university->name ?? '' }}
                            </small>

                            <small class="text-muted d-block">
                                <i class="bi bi-hash"></i> {{ $pengajuan->nomor_pengajuan }}
                            </small>
                        </div>
                    </div>

                    {{-- Badge status: dibatasi supaya tidak keluar kotak --}}
                    <span class="badge bg-{{ $itemColor }} text-wrap text-center" style="white-space: normal; line-height: 1.1;" title="{{ $currentItem['label'] }}">
                        <i class="bi {{ $iconClass }} text-white me-1"></i>
                        {{ $currentItem['label'] }}
                    </span>

                    <div class="my-3">
                        @if($pengajuan->jenis_akreditasi)
                        <small class="text-muted d-block">
                            <i class="bi bi-tag"></i>
                            {{ ucfirst($pengajuan->jenis_akreditasi) }}
                        </small>
                        @endif

                        @if($pengajuan->tahun_akreditasi)
                        <small class="text-muted d-block">
                            <i class="bi bi-calendar"></i>
                            Tahun: {{ $pengajuan->tahun_akreditasi }}
                        </small>
                        @endif

                        {{-- Tampilkan tanggal hanya sekali --}}
                        <small class="text-muted d-block">
                            <i class="bi bi-clock"></i>
                            Diajukan: {{ \App\Libraries\Date::tglIndo($pengajuan->created_at) }}
                        </small>

                        {{-- @if($pengajuan->deskEvaluator)
                        <small class="text-muted d-block">
                            <i class="bi bi-person"></i>
                            DE: {{ $pengajuan->deskEvaluator->name }}
                        </small>
                        @endif --}}
                    </div>

                    {{-- ✅ BUTTON BERBEDA UNTUK STATUS PENGINGAT_DIKIRIM --}}
                    @if($pengajuan->status === 'pengingat_dikirim' && is_null($pengajuan->id_user_pengaju))
                    <a href="{{ route('pengajuan.create', ['pengajuan_id' => $pengajuan->id]) }}" class="btn btn-warning btn-sm w-100">
                        <i class="bi bi-pencil-square"></i> Lengkapi Data Permohonan Akreditasi
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
                    <p class="text-muted mt-3">Belum ada permohonan akreditasi</p>
                    <a href="{{ route('pengajuan.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Buat Permohonan Akreditasi
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
