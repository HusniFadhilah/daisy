@extends('layouts.template.app')

@section('title', 'Elemen Standar - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manajemen Indikator</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createElemenModal">
            <i class="bi bi-plus-circle"></i> Tambah Elemen Standar
        </button>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('kriteria.index') }}">
                <i class="bi bi-list-check"></i> Kriteria
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link active" href="{{ route('elemen-standar.index') }}">
                <i class="bi bi-diagram-3"></i> Elemen Standar
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('indikator.index') }}">
                <i class="bi bi-bar-chart"></i> Indikator
            </a>
        </li>
    </ul>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('elemen-standar.index') }}" method="GET" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari kode elemen, pernyataan, keterangan, atau kriteria..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-4">
                        <select name="id_kriteria" class="form-select">
                            <option value="">-- Semua Kriteria --</option>
                            @foreach($kriteria as $k)
                            <option value="{{ $k->id_kriteria }}" {{ request('id_kriteria') == $k->id_kriteria ? 'selected' : '' }}>
                                {{ $k->kode_kriteria }} - {{ $k->nama_kriteria }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                        <a href="{{ route('elemen-standar.index') }}" class="btn btn-secondary">
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
                            <th width="20%">Kriteria</th>
                            <th width="10%">Kode Elemen</th>
                            <th width="30%">Pernyataan Elemen</th>
                            <th width="20%">Keterangan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($elemenStandar as $item)
                        <tr>
                            <td>
                                {{ $loop->iteration + ($elemenStandar->currentPage() - 1) * $elemenStandar->perPage() }}
                            </td>
                            <td>
                                {{ $item->kriteria ? $item->kriteria->kode_kriteria . ' - ' . $item->kriteria->nama_kriteria : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $item->kode_elemen }}
                                </span>
                            </td>
                            <td>{{ $item->pernyataan_elemen }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($item->keterangan, 50) ?: '-' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#showElemenModal{{ $item->id }}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <a href="{{ route('elemen-standar.edit', $item->id) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form id="form-elemen-{{ $item->id }}" action="{{ route('elemen-standar.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger tombol-hapus" data-id-form="form-elemen-{{ $item->id }}" data-text="elemen standar" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="showElemenModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Elemen Standar</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-3">Kriteria</dt>
                                            <dd class="col-sm-9">
                                                {{ $item->kriteria->kode_kriteria ?? '-' }} - {{ $item->kriteria->nama_kriteria ?? '-' }}
                                            </dd>

                                            <dt class="col-sm-3">Kode Elemen</dt>
                                            <dd class="col-sm-9">{{ $item->kode_elemen }}</dd>

                                            <dt class="col-sm-3">Pernyataan Elemen</dt>
                                            <dd class="col-sm-9">{{ $item->pernyataan_elemen }}</dd>

                                            <dt class="col-sm-3">Keterangan</dt>
                                            <dd class="col-sm-9">{{ $item->keterangan ?? '-' }}</dd>

                                            <dt class="col-sm-3">Pernyataan Standar</dt>
                                            <dd class="col-sm-9">
                                                @if($item->pernyataan && $item->pernyataan->count() > 0)
                                                <ul class="mb-0">
                                                    @foreach($item->pernyataan as $pernyataan)
                                                    <li>
                                                        <strong>{{ $pernyataan->code }}</strong>:
                                                        {{ $pernyataan->pernyataan }}
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                @else
                                                <span class="text-muted">Belum ada pernyataan standar</span>
                                                @endif
                                            </dd>
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
                            <td colspan="6" class="text-center">Belum ada data elemen standar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $elemenStandar->firstItem() ?? 0 }} - {{ $elemenStandar->lastItem() ?? 0 }}
                    dari {{ $elemenStandar->total() }} data
                </div>
                <div>
                    {{ $elemenStandar->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createElemenModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('elemen-standar.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Elemen Standar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kriteria <span class="text-danger">*</span></label>
                        <select name="id_kriteria" class="form-select @error('id_kriteria') is-invalid @enderror" required>
                            <option value="">Pilih Kriteria</option>
                            @foreach($kriteria as $k)
                            <option value="{{ $k->id_kriteria }}" {{ old('id_kriteria') == $k->id_kriteria ? 'selected' : '' }}>
                                {{ $k->kode_kriteria }} - {{ $k->nama_kriteria }}
                            </option>
                            @endforeach
                        </select>
                        @error('id_kriteria')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Elemen <span class="text-danger">*</span></label>
                        <input type="text" name="kode_elemen" class="form-control @error('kode_elemen') is-invalid @enderror" value="{{ old('kode_elemen') }}" required>
                        @error('kode_elemen')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pernyataan Elemen <span class="text-danger">*</span></label>
                        <textarea name="pernyataan_elemen" class="form-control @error('pernyataan_elemen') is-invalid @enderror" rows="3" required>{{ old('pernyataan_elemen') }}</textarea>
                        @error('pernyataan_elemen')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="2">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
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
