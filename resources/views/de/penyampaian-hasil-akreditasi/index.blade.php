@extends('layouts.template.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-clipboard-data text-primary"></i>
                Penyampaian Hasil Akreditasi
            </h1>
            <p class="text-muted mb-0">Kelola dan finalisasi hasil akreditasi program studi</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('de.penyampaian-hasil-akreditasi') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nomor permohonan atau nama prodi" value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status Pengajuan</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="{{ App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI }}" {{ request('status') == App\Models\PengajuanAkreditasi::STATUS_AL_SELESAI ? 'selected' : '' }}>
                            AL Selesai
                        </option>
                        <option value="{{ App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN }}" {{ request('status') == App\Models\PengajuanAkreditasi::STATUS_AL_DILAPORKAN ? 'selected' : '' }}>
                            AL Dilaporkan
                        </option>
                        <option value="{{ App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM }}" {{ request('status') == App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM ? 'selected' : '' }}>
                            Hasil Dikirim
                        </option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Peringkat</label>
                    <select name="peringkat" class="form-select">
                        <option value="">Semua Peringkat</option>
                        <option value="Unggul" {{ request('peringkat') == 'Unggul' ? 'selected' : '' }}>Unggul</option>
                        <option value="Baik Sekali" {{ request('peringkat') == 'Baik Sekali' ? 'selected' : '' }}>Baik Sekali</option>
                        <option value="Baik" {{ request('peringkat') == 'Baik' ? 'selected' : '' }}>Baik</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('de.penyampaian-hasil-akreditasi') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card">
        <div class="card-body">
            @if($pengajuans->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">Belum ada pengajuan yang siap untuk penyampaian hasil</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Permohonan Akreditasi</th>
                            <th>Status</th>
                            <th>Skor AL</th>
                            <th>Peringkat</th>
                            <th>Status Hasil</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuans as $pengajuan)
                        @php
                        $hasil = $pengajuan->asesmen->hasil ?? null;
                        @endphp
                        <tr>

                            <td>
                                {!! $pengajuan->getPermohonanAkreditasiSectionFor('de') !!}
                            </td>
                            <td>
                                {{-- <span class="badge {{ $pengajuan->status_badge_class }}">
                                {{ $pengajuan->status_label }}
                                </span> --}}
                            </td>
                            <td>
                                @if($hasil && $hasil->skor_al)
                                <span class="badge bg-info">{{ number_format($hasil->skor_al, 2) }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($hasil && $hasil->peringkat_akreditasi)
                                @php
                                $badgeClass = match($hasil->peringkat_akreditasi) {
                                'Unggul' => 'bg-success',
                                'Baik Sekali' => 'bg-primary',
                                'Baik' => 'bg-info',
                                default => 'bg-secondary'
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ $hasil->peringkat_akreditasi }}
                                </span>
                                @else
                                <span class="text-muted">Belum dihitung</span>
                                @endif
                            </td>
                            <td>
                                @if($hasil)
                                @if($hasil->isAlFinalized())
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Difinalisasi
                                </span>
                                @else
                                <span class="badge bg-warning">
                                    <i class="bi bi-clock"></i> Draft
                                </span>
                                @endif
                                @else
                                <span class="badge bg-secondary">Belum dihitung</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('de.penyampaian-hasil-akreditasi.show', $pengajuan->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $pengajuans->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
