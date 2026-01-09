@extends('layouts.template.app')

@section('content')
<div class="container">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Debug DatasetBorang (Semua)</h3>
        <span class="badge bg-secondary">Total: {{ $total }}</span>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('debug.dataset-borang.index') }}">
        <div class="col-md-6">
            <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Cari kode/nama dataset...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" type="submit">Cari</button>
        </div>
        <div class="col-md-2">
            <a class="btn btn-outline-secondary w-100" href="{{ route('debug.dataset-borang.index') }}">Reset</a>
        </div>
    </form>

    @foreach($items as $item)
    @php
    $d = $item['dataset'];
    $a = $item['analysis'];
    @endphp

    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div>
                <strong>{{ $d->kode ?? ('#'.$d->id) }}</strong> — {{ $d->nama ?? '-' }}
                <div class="text-muted" style="font-size: .9em;">
                    ID: {{ $d->id }} | Expected: {{ $a['expected_count'] }} kolom
                </div>
            </div>
            <div class="text-end">
                <span class="badge {{ $a['has_colspan'] ? 'bg-danger' : 'bg-success' }}">colspan: {{ $a['has_colspan'] ? 'YES' : 'NO' }}</span>
                <span class="badge {{ $a['has_rowspan'] ? 'bg-danger' : 'bg-success' }}">rowspan: {{ $a['has_rowspan'] ? 'YES' : 'NO' }}</span>
                <span class="badge {{ $a['mismatch_rows'] > 0 ? 'bg-danger' : 'bg-success' }}">mismatch rows: {{ $a['mismatch_rows'] }}</span>
            </div>
        </div>

        <div class="card-body">
            <div class="mb-2">
                <strong>Expected Columns:</strong>
                @if($a['expected_count'])
                <code>{{ implode(' | ', $a['expected_columns']) }}</code>
                @else
                <span class="text-muted">-</span>
                @endif
            </div>

            <details class="mb-3">
                <summary><strong>Raw HTML</strong> (klik untuk lihat)</summary>
                <div class="mt-2">
                    @if($item['html'])
                    <div class="mb-2 text-muted">Length: {{ strlen($item['html']) }} chars</div>
                    <textarea class="form-control" rows="6" readonly>{{ $item['html'] }}</textarea>
                    @else
                    <div class="text-muted">Tidak ada HTML tabel di record ini (cek field di controller extractHtmlFromDataset()).</div>
                    @endif
                </div>
            </details>

            <details class="mb-3">
                <summary><strong>Render As-Is</strong> (HTML asli)</summary>
                <div class="mt-2">
                    @if($item['html'])
                    <div class="table-responsive">
                        {!! $item['html'] !!}
                    </div>
                    @else
                    <div class="text-muted">Tidak ada HTML untuk dirender.</div>
                    @endif
                </div>
            </details>

            <details class="mb-3" open>
                <summary><strong>Analisis per Baris</strong> (logical column count)</summary>
                <div class="mt-2 table-responsive">
                    @if(!empty($a['rows']))
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Logical</th>
                                <th>Expected</th>
                                <th>Status</th>
                                <th>Cells</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($a['rows'] as $row)
                            <tr class="{{ $row['mismatch'] ? 'table-danger' : '' }}">
                                <td>{{ $row['row_index'] }}</td>
                                <td>{{ $row['logical_count'] }}</td>
                                <td>{{ $row['expected_count'] }}</td>
                                <td>
                                    @if($row['mismatch'])
                                    <span class="badge bg-danger">Mismatch</span>
                                    @else
                                    <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                                <td style="font-size: .9em;">
                                    @foreach($row['cells'] as $cell)
                                    <div class="mb-1">
                                        <code>{{ \Illuminate\Support\Str::limit($cell['text'], 80) }}</code>
                                        @if(($cell['colspan'] ?? 1) > 1)
                                        <span class="badge bg-warning text-dark">colspan {{ $cell['colspan'] }}</span>
                                        @endif
                                        @if(($cell['rowspan'] ?? 1) > 1)
                                        <span class="badge bg-info text-dark">rowspan {{ $cell['rowspan'] }}</span>
                                        @endif
                                    </div>
                                    @endforeach
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-muted">Tidak ada baris yang bisa dianalisis (HTML kosong atau tidak ada &lt;tr&gt;).</div>
                    @endif
                </div>
            </details>

            <details class="mb-0">
                <summary><strong>Normalized View</strong> (colspan di-expand)</summary>
                <div class="mt-2">
                    @if($item['normalizedTable'])
                    <div class="table-responsive">
                        {!! $item['normalizedTable'] !!}
                    </div>
                    <div class="mt-2 text-muted" style="font-size: .9em;">
                        Baris mismatch diberi outline merah. Cell hasil expand colspan diberi badge.
                    </div>
                    @else
                    <div class="text-muted">Tidak ada normalized view.</div>
                    @endif
                </div>
            </details>

        </div>
    </div>
    @endforeach

</div>
@endsection
