@extends('layouts.template.app')

@section('title', 'Indikator - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manajemen Indikator</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createIndikatorModal">
            <i class="bi bi-plus-circle"></i> Tambah Indikator
        </button>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('kriteria.index') }}">
                <i class="bi bi-list-check"></i> Kriteria
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('elemen-standar.index') }}">
                <i class="bi bi-diagram-3"></i> Elemen Standar
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" href="{{ route('indikator.index') }}">
                <i class="bi bi-bar-chart"></i> Indikator
            </a>
        </li>
    </ul>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('indikator.index') }}" method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari kode, deskripsi, elemen, kriteria, atau jenis..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <select name="id_elemen" class="form-select">
                            <option value="">-- Semua Elemen Standar --</option>
                            @foreach($elemenStandar as $elemen)
                            <option value="{{ $elemen->id }}" {{ request('id_elemen') == $elemen->id ? 'selected' : '' }}>
                                {{ $elemen->kode_elemen }} -
                                {{ \Illuminate\Support\Str::limit($elemen->pernyataan_elemen, 40) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select name="id_jenis" class="form-select">
                            <option value="">-- Semua Jenis Indikator --</option>
                            @foreach($jenisIndikator as $jenis)
                            <option value="{{ $jenis->id }}" {{ request('id_jenis') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama_jenis }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('indikator.index') }}" class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kode</th>
                            <th width="10%">Kriteria</th>
                            <th width="20%">Elemen Standar</th>
                            <th width="20%">Deskripsi Indikator</th>
                            <th width="15%">Jenis</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikator as $item)
                        <tr>
                            <td>
                                {{ $loop->iteration + ($indikator->currentPage() - 1) * $indikator->perPage() }}
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $item->kode_indikator }}
                                </span>
                            </td>
                            <td>
                                {{ $item->elemenStandar && $item->elemenStandar->kriteria
                                        ? $item->elemenStandar->kriteria->kode_kriteria
                                        : '-' }}
                            </td>
                            <td>
                                @if($item->elemenStandar)
                                <div><strong>{{ $item->elemenStandar->kode_elemen }}</strong></div>
                                <small class="text-muted">
                                    {{ \Illuminate\Support\Str::limit($item->elemenStandar->pernyataan_elemen, 50) }}
                                </small>
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $item->deskripsi_indikator }}</td>
                            <td>{{ $item->jenisIndikator->nama_jenis ?? '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#showIndikatorModal{{ $item->id }}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <a href="{{ route('indikator.edit', ['indikator' => $item->id]) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form id="form-indikator-{{ $item->id }}" action="{{ route('indikator.destroy', ['indikator' => $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger tombol-hapus" data-id-form="form-indikator-{{ $item->id }}" data-text="indikator" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="showIndikatorModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Indikator</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-3">Kode Indikator</dt>
                                            <dd class="col-sm-9">{{ $item->kode_indikator }}</dd>

                                            <dt class="col-sm-3">Deskripsi Indikator</dt>
                                            <dd class="col-sm-9">{{ $item->deskripsi_indikator }}</dd>

                                            <dt class="col-sm-3">Kriteria</dt>
                                            <dd class="col-sm-9">
                                                {{ $item->elemenStandar && $item->elemenStandar->kriteria
                                                        ? $item->elemenStandar->kriteria->kode_kriteria . ' - ' . $item->elemenStandar->kriteria->nama_kriteria
                                                        : '-' }}
                                            </dd>

                                            <dt class="col-sm-3">Elemen Standar</dt>
                                            <dd class="col-sm-9">
                                                @if($item->elemenStandar)
                                                <strong>{{ $item->elemenStandar->kode_elemen }}</strong><br>
                                                {{ $item->elemenStandar->pernyataan_elemen }}
                                                @else
                                                -
                                                @endif
                                            </dd>

                                            <dt class="col-sm-3">Jenis Indikator</dt>
                                            <dd class="col-sm-9">{{ $item->jenisIndikator->nama_jenis ?? '-' }}</dd>

                                        </dl>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data indikator</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $indikator->firstItem() ?? 0 }} - {{ $indikator->lastItem() ?? 0 }}
                    dari {{ $indikator->total() }} data
                </div>
                <div>
                    {{ $indikator->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createIndikatorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('indikator.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Indikator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Indikator <span class="text-danger">*</span></label>
                        <input type="text" name="kode_indikator" class="form-control @error('kode_indikator') is-invalid @enderror" value="{{ old('kode_indikator') }}" required>
                        @error('kode_indikator')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Indikator <span class="text-danger">*</span></label>
                        <input type="text" name="deskripsi_indikator" class="form-control @error('deskripsi_indikator') is-invalid @enderror" value="{{ old('deskripsi_indikator') }}" required>
                        @error('deskripsi_indikator')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                        <select name="id_elemen" class="form-select @error('id_elemen') is-invalid @enderror" required>
                            <option value="">Pilih Elemen Standar</option>
                            @foreach($elemenStandar as $elemen)
                            <option value="{{ $elemen->id }}" {{ old('id_elemen') == $elemen->id ? 'selected' : '' }}>
                                {{ $elemen->kode_elemen }} -
                                {{ $elemen->kriteria->kode_kriteria ?? '-' }} -
                                {{ \Illuminate\Support\Str::limit($elemen->pernyataan_elemen, 60) }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_elemen')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Indikator <span class="text-danger">*</span></label>
                        <select name="id_jenis" class="form-select @error('id_jenis') is-invalid @enderror" required>
                            <option value="">Pilih Jenis Indikator</option>
                            @foreach($jenisIndikator as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('id_jenis') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama_jenis }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_jenis')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
