@extends('layouts.template.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="bi bi-clock-history text-primary"></i>
                Masa Sanggah Hasil Akreditasi
            </h1>
            <p class="text-muted mb-0">Monitor periode sanggah dan pengajuan banding</p>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 mb-4">
        <div class="col mb-3">
            <x-stat-card title="Total Pengajuan" :value="$stats['total']" description="" icon="file-text" iconBg="primary-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Masa Sanggah Aktif" :value="$stats['aktif']" description="" icon="hourglass-split" iconBg="success-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Hampir Habis" :value="$stats['hampir_habis']" description="" icon="exclamation-triangle" iconBg="warning-subtle" />
        </div>

        <div class="col mb-3">
            <x-stat-card title="Ada Banding" :value="$stats['ada_banding']" description="" icon="file-earmark-break" iconBg="danger-subtle" />
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('de.masa-sanggah') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nomor permohonan atau nama prodi" value="{{ request('search') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Status Masa Sanggah</label>
                    <select name="masa_sanggah_status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('masa_sanggah_status') == 'aktif' ? 'selected' : '' }}>
                            Aktif
                        </option>
                        <option value="hampir_habis" {{ request('masa_sanggah_status') == 'hampir_habis' ? 'selected' : '' }}>
                            Hampir Habis (≤2 hari)
                        </option>
                        <option value="selesai" {{ request('masa_sanggah_status') == 'selesai' ? 'selected' : '' }}>
                            Sudah Selesai
                        </option>
                        <option value="ada_banding" {{ request('masa_sanggah_status') == 'ada_banding' ? 'selected' : '' }}>
                            Ada Banding
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Peringkat</label>
                    <select name="peringkat" class="form-select">
                        <option value="">Semua</option>
                        <option value="Unggul" {{ request('peringkat') == 'Unggul' ? 'selected' : '' }}>Unggul</option>
                        <option value="Baik Sekali" {{ request('peringkat') == 'Baik Sekali' ? 'selected' : '' }}>Baik Sekali</option>
                        <option value="Baik" {{ request('peringkat') == 'Baik' ? 'selected' : '' }}>Baik</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status Pengajuan</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="{{ App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM }}" {{ request('status') == App\Models\PengajuanAkreditasi::STATUS_HASIL_AKREDITASI_DIKIRIM ? 'selected' : '' }}>
                            Hasil Dikirim
                        </option>
                        <option value="{{ App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH }}" {{ request('status') == App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH ? 'selected' : '' }}>
                            Masa Sanggah
                        </option>
                        <option value="{{ App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN }}" {{ request('status') == App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN ? 'selected' : '' }}>
                            Banding Diajukan
                        </option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('de.masa-sanggah') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card">
        <div class="card-body">
            @if($pengajuans->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="text-muted mt-3">Belum ada pengajuan dalam masa sanggah</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No. Pengajuan</th>
                            <th>Program Studi</th>
                            <th>Peringkat</th>
                            <th>Skor</th>
                            <th>Tgl Hasil</th>
                            <th>Status Masa Sanggah</th>
                            <th>Countdown</th>
                            <th>Banding</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuans as $pengajuan)
                        @php
                        $hasil = $pengajuan->asesmen->hasil ?? null;

                        // Calculate masa sanggah info
                        $now = now();
                        $masaSanggahInfo = [
                        'is_active' => false,
                        'is_expired' => false,
                        'is_hampir_habis' => false,
                        'days_left' => 0,
                        'badge_class' => 'secondary',
                        'countdown_text' => '-',
                        ];

                        if ($pengajuan->tanggal_masa_sanggah_selesai) {
                        $selesai = \Carbon\Carbon::parse($pengajuan->tanggal_masa_sanggah_selesai);

                        if ($now->lt($selesai)) {
                        $diff = $now->diff($selesai);
                        $masaSanggahInfo['is_active'] = true;
                        $masaSanggahInfo['days_left'] = $diff->days;

                        if ($diff->days <= 2) { $masaSanggahInfo['is_hampir_habis']=true; $masaSanggahInfo['badge_class']='warning' ; } else { $masaSanggahInfo['badge_class']='success' ; } $masaSanggahInfo['countdown_text']=$diff->days > 0
                            ? "{$diff->days} hari {$diff->h} jam"
                            : "{$diff->h} jam {$diff->i} menit";
                            } else {
                            $masaSanggahInfo['is_expired'] = true;
                            $masaSanggahInfo['badge_class'] = 'danger';
                            $masaSanggahInfo['countdown_text'] = 'Sudah lewat';
                            }
                            }

                            $hasBanding = !is_null($pengajuan->tanggal_banding);
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $pengajuan->nomor_pengajuan }}</span>
                                </td>
                                <td>
                                    <div>{{ $pengajuan->studyProgram->name }}</div>
                                    <small class="text-muted">{{ $pengajuan->studyProgram->university->name }}</small>
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
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($hasil && $hasil->skor_final)
                                    <span class="badge bg-info">{{ number_format($hasil->skor_final, 2) }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pengajuan->tanggal_hasil_akreditasi)
                                    {{ $pengajuan->tanggal_hasil_akreditasi->format('d M Y') }}
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pengajuan->status === App\Models\PengajuanAkreditasi::STATUS_MASA_SANGGAH)
                                    <span class="badge bg-{{ $masaSanggahInfo['badge_class'] }}">
                                        @if($masaSanggahInfo['is_active'])
                                        <i class="bi bi-hourglass-split"></i> Aktif
                                        @elseif($masaSanggahInfo['is_expired'])
                                        <i class="bi bi-exclamation-circle"></i> Selesai
                                        @else
                                        Belum Dimulai
                                        @endif
                                    </span>
                                    @elseif(in_array($pengajuan->status, [
                                    App\Models\PengajuanAkreditasi::STATUS_BANDING_DIAJUKAN,
                                    App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAKSANAKAN,
                                    App\Models\PengajuanAkreditasi::STATUS_BANDING_DILAPORKAN,
                                    ]))
                                    <span class="badge bg-danger">
                                        <i class="bi bi-file-earmark-break"></i> Ada Banding
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">Belum Dimulai</span>
                                    @endif
                                </td>
                                <td>
                                    @if($masaSanggahInfo['is_active'] || $masaSanggahInfo['is_expired'])
                                    <span class="text-{{ $masaSanggahInfo['badge_class'] }}">
                                        {{ $masaSanggahInfo['countdown_text'] }}
                                    </span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($hasBanding)
                                    <i class="bi bi-check-circle-fill text-success" title="Ada Banding"></i>
                                    @else
                                    <i class="bi bi-dash-circle text-muted" title="Tidak Ada"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('de.masa-sanggah.show', $pengajuan->id) }}" class="btn btn-sm btn-primary">
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
