{{-- Container split table — diisi server saat load, diupdate AJAX setelah save --}}
<div id="split-info-container">
    {{--
    Partial ini dirender server saat load pertama.
    Struktur HTML-nya sama persis dengan buildSplitInfoHTML() di JS,
    supaya AJAX replace bisa pakai innerHTML langsung.
--}}
    @if(!$isSubmittedOnly && !$isApproved)
    @php
    $reason = match(true) {
    !$isComplete => 'incomplete',
    $hasRevisionRequests => 'has_revision',
    !$split['allComplete'] => 'other_not_done',
    $hasSplit => 'has_split',
    default => 'none',
    };
    $isShowPage = request()->routeIs('ak.berkas.show*');
    @endphp

    {{-- Label alasan --}}
    <small class="d-block text-muted mt-1">
        <i class="bi bi-info-circle"></i>
        @if($reason === 'incomplete') Selesaikan semua elemen penilaian terlebih dahulu
        @elseif($reason === 'has_revision') Selesaikan semua permintaan revisi terlebih dahulu
        @elseif($reason === 'other_not_done') Menunggu asesor lain menyelesaikan penilaian
        @elseif($reason === 'has_split') Selesaikan diskusi split penilaian terlebih dahulu
        @else Semua asesor selesai &amp; tidak ada split — siap finalisasi
        @endif
    </small>

    {{-- Status asesor lain --}}
    @if($reason === 'other_not_done')
    <div class="mt-2 p-2 bg-light rounded border">
        <small class="text-muted d-block mb-2 fw-semibold">
            <i class="bi bi-people me-1"></i> Status Pengisian Asesor:
        </small>
        @foreach($split['asesors'] as $a)
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge {{ $a['is_me'] ? 'bg-primary' : 'bg-secondary' }}" style="min-width:90px;">
                {{ $a['is_me'] ? 'Anda' : $a['name'] }}
            </span>
            @if($a['is_done'])
            <span class="badge bg-success">
                <i class="bi bi-check-circle"></i> Selesai
            </span>
            @else
            <div class="progress flex-grow-1" style="height:16px;max-width:180px;">
                <div class="progress-bar bg-warning text-dark" style="width:{{ $a['percentage'] }}%;font-size:11px;">
                    {{ $a['percentage'] }}%
                </div>
            </div>
            <small class="text-muted">{{ $a['completed'] }}/{{ $a['total'] }}</small>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Alert + tabel split --}}
    @if($reason === 'has_split')
    <div class="alert alert-warning alert-permanent mt-2 mb-0 py-2 px-3">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div>
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                <strong>{{ $split['splitCount'] }} elemen masih split</strong>
                <small class="d-block text-danger">
                    Selesaikan perbedaan penilaian antar asesor sebelum finalisasi.
                </small>
            </div>
            <a href="{{ route('ak.berkas.cek-split', $asesmen->id) }}" class="btn btn-sm btn-outline-dark" target="_blank">
                <i class="bi bi-arrow-right-circle"></i> Cek Split Lengkap
            </a>
        </div>
    </div>

    @if(!empty($split['splitItems']))
    <div class="mt-2 table-responsive" style="max-height:160px;">
        <table class="table table-sm table-bordered mb-0 bg-white" style="font-size:12px;">
            <thead class="table-dark text-center">
                <tr>
                    <th width="10%">Kriteria</th>
                    <th width="30%">Elemen</th>
                    <th width="30%">Kategori</th>
                    <th width="10%">Selisih</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($split['splitItems'] as $item)
                <tr>
                    <td class="text-center">
                        <span class="badge bg-primary">{{ $item['kodeKriteria'] }}</span>
                    </td>
                    <td>
                        <strong>{{ $item['kodeElemen'] }}</strong>
                        <small class="d-block text-muted">{{ $item['pernyataan'] }}</small>
                    </td>
                    <td>
                        @foreach($item['skors'] as $s)
                        <div class="mb-1">
                            <span class="badge bg-light text-dark">{{ $s['nama'] }}</span>
                            <span class="badge text-dark" style="background:{{ \App\Models\JenjangPenilaian::getSkorColor($s['skor']) }};">
                                {{ \App\Models\JenjangPenilaian::getSkorInfo($s['skor'])['label'] ?? $s['skor'] }}
                            </span>
                        </div>
                        @endforeach
                    </td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark">{{ $item['selisih'] }}</span>
                    </td>
                    <td class="text-center">
                        <a href="{{ $isShowPage
                            ? '#elemen-'.$item['elemenId']
                            : route('ak.berkas.show', $asesmen->id).'#elemen-'.$item['elemenId'] }}" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-arrow-right-circle"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @endif

    @endif
</div>
