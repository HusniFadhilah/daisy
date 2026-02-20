@extends('layouts.template.app')

@section('title', 'Preview LKPS')

@push('styles')
<style>
    .excel-table th,
    .excel-table td {
        font-size: 13px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .excel-table thead th {
        text-align: center;
    }

    .excel-table tbody td {
        background: #fff;
    }

    .excel-table tbody tr:nth-child(even) td {
        background: #f8f9fa;
    }

</style>
@endpush

@section('content')
<div class="container-fluid">

    <h4 class="mb-4">
        Preview LKPS – {{ $pengajuan->studyProgram->name ?? '' }}
    </h4>

    {{-- ========================= --}}
    {{-- TAB SHEET --}}
    {{-- ========================= --}}
    <ul class="nav nav-tabs mb-4">
        @foreach($data as $sheetName => $tables)
        <li class="nav-item">
            <a class="nav-link {{ $sheetName === $activeSheet ? 'active' : '' }}" href="{{ route('pengajuan.borang.lkps.preview', [$pengajuan->id, 'sheet' => $sheetName]) }}">
                {{ $sheetName }}
            </a>
        </li>
        @endforeach
    </ul>

    {{-- ========================= --}}
    {{-- CONTENT SHEET --}}
    {{-- ========================= --}}
    @if(isset($data[$activeSheet]))

    @foreach($data[$activeSheet] as $table)

    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <strong>
                {{ $table->table_title ?? "Tabel {$table->table_index}" }}
            </strong>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0 excel-table">

                    {{-- HEADERS --}}
                    @if(!empty($table->headers))
                    <thead class="table-light">
                        @foreach($table->headers as $headerRow)
                        <tr>
                            @foreach($headerRow as $cell)
                            <th>
                                {!! $cell !== null ? e($cell) : '&nbsp;' !!}
                            </th>
                            @endforeach
                        </tr>
                        @endforeach
                    </thead>
                    @endif

                    {{-- ROWS --}}
                    <tbody>
                        @foreach($table->rows as $row)
                        <tr>
                            @foreach($row as $cell)
                            <td>
                                {{ $cell ?? '' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    @endforeach

    @else
    <div class="alert alert-warning">
        Tidak ada data untuk sheet ini.
    </div>
    @endif

</div>
@endsection
