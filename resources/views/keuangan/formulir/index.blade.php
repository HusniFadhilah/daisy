{{-- resources/views/keuangan/formulir/index.blade.php --}}
@extends('layouts.template.app')

@section('title', 'Formulir Pembayaran')

@push('styles')
<style>
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .dokumen-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }

</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h3 class="mb-1"><i class="bi bi-file-earmark-text"></i> Formulir Pembayaran</h2>
                <p class="text-muted mb-0">Daftar formulir pembayaran (akreditasi & banding) yang telah diupload oleh program studi</p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4 row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <x-stat-card title="Total Formulir" :value="$stats['total']" description="Akreditasi + banding" icon="file-earmark-check" gradient="purple" iconBg="success-subtle" />
        </div>
        <div class="col">
            <x-stat-card title="Hari Ini" :value="$stats['today']" description="Upload hari ini" icon="calendar-check" gradient="pink" iconBg="success-subtle" />
        </div>
        <div class="col">
            <x-stat-card title="Formulir Akreditasi" :value="$stats['akreditasi']" description="Pembayaran akreditasi biasa" icon="file-earmark-text" gradient="blue" iconBg="success-subtle" />
        </div>
        <div class="col">
            <x-stat-card title="Formulir Banding" :value="$stats['banding']" description="Pembayaran proses banding" icon="file-earmark-arrow-up" gradient="orange" iconBg="success-subtle" />
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('keuangan.formulir.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Cari</label>
                        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Nomor invoice, permohonan, atau prodi...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jenis Pembayaran</label>
                        <select name="jenis" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="akreditasi" @selected(($jenis ?? '' )==='akreditasi' )>Akreditasi</option>
                            <option value="banding" @selected(($jenis ?? '' )==='banding' )>Banding</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Universitas</label>
                        <select name="university_id" class="form-select">
                            <option value="">Semua Universitas</option>
                            @foreach($universities as $univ)
                            <option value="{{ $univ->id }}" @selected($university_id==$univ->id)>{{ $univ->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jenjang</label>
                        <select name="degree_level_id" class="form-select">
                            <option value="">Semua Jenjang</option>
                            @foreach($degreeLevels as $level)
                            <option value="{{ $level->id }}" @selected($degree_level_id==$level->id)>{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('keuangan.formulir.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Daftar Formulir Pembayaran</h5>
            <span class="badge bg-primary">Total: {{ $pengajuan->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="20%">Program Studi</th>
                            <th width="20%">Nomor Invoice</th>
                            <th width="15%">Jumlah</th>
                            <th width="20%">Dokumen</th>
                            <th width="20%">Tanggal Upload</th>
                            {{-- <th width="10%">Aksi</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuan as $item)
                        @php
                        // ✅ Pakai semuaPembayaran (HasMany) — tidak akan null
                        $pmbAkreditasi = $item->semuaPembayaran->firstWhere('jenis_pembayaran', 'akreditasi');
                        $pmbBanding = $item->semuaPembayaran->firstWhere('jenis_pembayaran', 'banding');

                        $fAkreditasi = $item->dokumen->where('jenis_dokumen', 'formulir_pembayaran')->where('is_latest', true)->first();
                        $fBanding = $item->dokumen->where('jenis_dokumen', 'formulir_pembayaran_banding')->where('is_latest', true)->first();

                        $adaAkreditasi = $fAkreditasi !== null;
                        $adaBanding = $fBanding !== null;
                        @endphp
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration + ($pengajuan->currentPage() - 1) * $pengajuan->perPage() }}
                            </td>

                            <td>
                                {!! $item->getPermohonanAkreditasiSectionFor('de') !!}
                            </td>

                            {{-- Invoice — tampil keduanya --}}
                            <td>
                                @if($adaAkreditasi && $pmbAkreditasi)
                                <div class="{{ $adaBanding ? 'mb-2' : '' }}">
                                    @if($adaAkreditasi && $adaBanding)
                                    <span class="badge bg-primary mb-1">Akreditasi</span><br>
                                    @endif
                                    <strong>{{ $pmbAkreditasi->nomor_invoice }}</strong>
                                </div>
                                @endif
                                @if($adaBanding && $pmbBanding)
                                <div>
                                    <span class="badge bg-warning text-dark mb-1">Banding</span><br>
                                    <strong>{{ $pmbBanding->nomor_invoice }}</strong>
                                </div>
                                @endif
                                @if(!$pmbAkreditasi && !$pmbBanding)
                                <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Jumlah — tampil keduanya --}}
                            <td>
                                @if($adaAkreditasi && $pmbAkreditasi)
                                <div class="{{ $adaBanding ? 'mb-2' : '' }}">
                                    @if($adaAkreditasi && $adaBanding)
                                    <small class="text-muted d-block">Akreditasi</small>
                                    @endif
                                    <strong class="text-success">
                                        Rp {{ number_format($pmbAkreditasi->jumlah_pembayaran, 0, ',', '.') }}
                                    </strong>
                                </div>
                                @endif
                                @if($adaBanding && $pmbBanding)
                                <div>
                                    <small class="text-muted d-block">Banding</small>
                                    <strong class="text-warning">
                                        Rp {{ number_format($pmbBanding->jumlah_pembayaran, 0, ',', '.') }}
                                    </strong>
                                </div>
                                @endif
                            </td>

                            {{-- Dokumen badge --}}
                            <td>
                                @if($adaAkreditasi)
                                <a href="{{ route('keuangan.formulir.download', [$item->id, $fAkreditasi->id]) }}" class="text-link text-decoration-none">
                                    <span class="badge bg-success dokumen-badge d-block mb-1">
                                        <i class="bi bi-file-earmark-check"></i> Formulir Pembayaran Akreditasi
                                    </span>
                                </a>
                                @endif
                                @if($adaBanding)
                                <a href="{{ route('keuangan.formulir.download', [$item->id, $fBanding->id]) }}" class="text-link text-decoration-none">
                                    <span class="badge bg-warning text-dark dokumen-badge d-block">
                                        <i class="bi bi-file-earmark-check"></i> Formulir Pembayaran Banding
                                    </span>
                                </a>
                                @endif
                            </td>

                            {{-- Tanggal upload --}}
                            <td>
                                @if($adaAkreditasi)
                                <div class="{{ $adaBanding ? 'mb-2' : '' }}">
                                    @if($adaAkreditasi && $adaBanding)
                                    <small class="text-primary d-block fw-bold">Akreditasi</small>
                                    @endif
                                    {{ $fAkreditasi->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                    {{-- <small class="text-muted">{{ $fAkreditasi->created_at->format('H:i') }}</small> --}}
                                </div>
                                @endif
                                @if($adaBanding)
                                <div>
                                    <small class="text-warning fw-bold d-block">Banding</small>
                                    {{ $fBanding->created_at->locale('id')->translatedFormat('d M Y H:i') }}
                                    {{-- <small class="text-muted">{{ $fBanding->created_at->format('H:i') }}</small> --}}
                                </div>
                                @endif
                                @if(!$adaAkreditasi && !$adaBanding) - @endif
                            </td>

                            {{-- Aksi --}}
                            {{-- <td class="text-center">
                                <a href="{{ route('keuangan.formulir.show', $item->id) }}" class="btn btn-info btn-sm mb-1" title="Detail">
                            <i class="bi bi-eye"></i>
                            </a>
                            @if($adaAkreditasi)
                            <a href="{{ route('keuangan.formulir.download', [$item->id, $fAkreditasi->id]) }}" class="btn btn-primary btn-xs mb-1" title="Download Formulir Akreditasi">
                                <i class="bi bi-eye"></i> {{ $adaAkreditasi && $adaBanding ? 'Lihat Formulir Akreditasi' : 'Lihat' }}
                            </a>
                            @endif
                            @if($adaBanding)
                            <a href="{{ route('keuangan.formulir.download', [$item->id, $fBanding->id]) }}" class="btn btn-warning btn-xs" title="Download Formulir Banding">
                                <i class="bi bi-eye"></i> Lihat Formulir Banding
                            </a>
                            @endif
                            </td> --}}
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-3 mb-0">Belum ada formulir pembayaran yang diupload</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pengajuan->hasPages())
        <div class="card-footer">{{ $pengajuan->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('select[name="jenis"], select[name="university_id"], select[name="degree_level_id"]').on('change', function() {
            $('#filterForm').submit();
        });
    });

</script>
@endpush
