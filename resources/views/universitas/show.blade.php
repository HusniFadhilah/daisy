@extends('layouts.template.app')

@section('title', 'Detail Universitas - Daisy')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Detail Universitas</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master-data.index', ['tab' => 'universities']) }}">Univ & Prodi</a></li>
                <li class="breadcrumb-item active">{{ $university->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($university->logo_path)
                        <img src="{{ asset('storage/' . $university->logo_path) }}" 
                             alt="{{ $university->name }}" 
                             class="img-fluid mb-3" 
                             style="max-height: 200px;">
                    @else
                        <div class="bg-light rounded p-4 mb-3">
                            <i class="fas fa-university fa-5x text-muted"></i>
                        </div>
                    @endif
                    <h4>{{ $university->name }}</h4>
                    <p class="text-muted">{{ $university->code }}</p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Informasi Kontak</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="40%"><strong>Email:</strong></td>
                            <td>
                                @if($university->email && $university->email !== '-')
                                    <a href="mailto:{{ $university->email }}">{{ $university->email }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Statistik</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Total Program Studi:</span>
                        <span class="badge bg-primary">{{ $university->study_programs_count }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <a href="{{ route('universities.edit', $university->id) }}" class="btn btn-warning w-100 mb-2">
                    <i class="fas fa-edit"></i> Edit Universitas
                </a>
                <a href="{{ route('master-data.index', ['tab' => 'universities']) }}" class="btn btn-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Program Studi</h5>
                </div>
                <div class="card-body">
                    @if($university->studyPrograms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Program Studi</th>
                                        <th>Jenjang</th>
                                        <th>Email</th>
                                        <th>Akreditasi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($university->studyPrograms as $prodi)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('study-programs.show', $prodi->id) }}">
                                                {{ $prodi->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $prodi->degreeLevel->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($prodi->email && $prodi->email !== '-')
                                                <small>{{ $prodi->email }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($prodi->peringkat_akreditasi)
                                                @php
                                                    $class = match($prodi->status_kedaluwarsa) {
                                                        'Aktif' => 'success',
                                                        'Kedaluwarsa' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $class }}">{{ $prodi->peringkat_akreditasi }}</span>
                                            @else
                                                <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('study-programs.show', $prodi->id) }}" 
                                               class="btn btn-sm btn-info text-white" 
                                               title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Belum ada program studi terdaftar
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
