@extends('layouts.template.app')

@section('title', 'Detail Program Studi - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Detail Program Studi</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master-data.index', ['tab' => 'study-programs']) }}">Univ & Prodi</a></li>
                <li class="breadcrumb-item active">{{ $studyProgram->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Program Studi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="30%"><strong>Nama Program Studi:</strong></td>
                            <td>{{ $studyProgram->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Nama Lengkap:</strong></td>
                            <td>{{ $studyProgram->full_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Kode:</strong></td>
                            <td>{{ $studyProgram->code }}</td>
                        </tr>
                        <tr>
                            <td><strong>Universitas:</strong></td>
                            <td>
                                @if($studyProgram->university)
                                <a href="{{ route('universities.show', $studyProgram->university->id) }}">
                                    {{ $studyProgram->university->name }}
                                </a>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Jenjang:</strong></td>
                            <td>
                                @if($studyProgram->degreeLevel)
                                <span class="badge bg-info">{{ $studyProgram->degreeLevel->name }}</span>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Kategori:</strong></td>
                            <td>
                                @if($studyProgram->category)
                                <span class="badge bg-secondary">{{ $studyProgram->category->name }}</span>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Bentuk PT:</strong></td>
                            <td>{{ $studyProgram->bentuk_pt ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>
                                @if($studyProgram->email && $studyProgram->email !== '-')
                                <a href="mailto:{{ $studyProgram->email }}">{{ $studyProgram->email }}</a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('study-programs.edit', $studyProgram->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Program Studi
                </a>
                <a href="{{ route('master-data.index', ['tab' => 'study-programs']) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Status Akreditasi</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Status Akreditasi:</strong></td>
                            <td>
                                @if($studyProgram->peringkat_akreditasi)
                                @php
                                $class = match($studyProgram->status_kedaluwarsa) {
                                'Aktif' => 'success',
                                'Kedaluwarsa' => 'warning',
                                default => 'secondary'
                                };
                                @endphp
                                <span class="badge bg-{{ $class }}">{{ $studyProgram->peringkat_akreditasi }}</span>
                                @else
                                <span class="badge bg-secondary">Belum Terakreditasi</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Kedaluwarsa:</strong></td>
                            <td>
                                @if($studyProgram->tanggal_kedaluwarsa)
                                {{ \Carbon\Carbon::parse($studyProgram->tanggal_kedaluwarsa)->locale('id')->translatedFormat('d M Y') }}
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @php
                                $statusClass = match($studyProgram->status_kedaluwarsa) {
                                'Aktif' => 'success',
                                'Kedaluwarsa' => 'danger',
                                default => 'secondary'
                                };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ $studyProgram->status_kedaluwarsa }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($studyProgram->university && $studyProgram->university->logo_path)
            <div class="card mt-3">
                <div class="card-body text-center">
                    <img src="{{ asset('storage/' . $studyProgram->university->logo_path) }}" alt="{{ $studyProgram->university->name }}" class="img-fluid" style="max-height: 150px;">
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
