@extends('layouts.template.app')

@section('title', 'Kriteria - Daisy LAMDEPILAR')

@section('content')
<div class="container-fluid">
    <!-- Header dengan Tabs -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manajemen Indikator</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKriteriaModal">
            <i class="bi bi-plus-circle"></i> Tambah Kriteria
        </button>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" href="{{ route('kriteria.index') }}">
                <i class="bi bi-list-check"></i> Kriteria
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('elemen-standar.index') }}">
                <i class="bi bi-diagram-3"></i> Elemen Standar
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" href="{{ route('indikator.index') }}">
                <i class="bi bi-bar-chart"></i> Indikator
            </a>
        </li>
    </ul>

    <!-- Tabel Kriteria -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Kode</th>
                            <th width="40%">Nama Kriteria</th>
                            <th width="25%">Keterangan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kriteria as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($kriteria->currentPage() - 1) * $kriteria->perPage() }}</td>
                            <td><span class="badge bg-info text-dark">{{ $item->kode_kriteria }}</span></td>
                            <td><strong>{{ $item->nama_kriteria }}</strong></td>
                            <td>{{ Str::limit($item->keterangan, 50) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#showKriteriaModal{{ $item->id }}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editKriteriaModal{{ $item->id }}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form id="form-kriteria-{{ $item->id }}" action="{{ route('kriteria.destroy', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger tombol-hapus" data-id-form="form-kriteria-{{ $item->id }}" data-text="kriteria" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Detail -->
                        <div class="modal fade" id="showKriteriaModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Kriteria</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-4">Kode Kriteria</dt>
                                            <dd class="col-sm-8">{{ $item->kode_kriteria }}</dd>

                                            <dt class="col-sm-4">Nama Kriteria</dt>
                                            <dd class="col-sm-8">{{ $item->nama_kriteria }}</dd>

                                            <dt class="col-sm-4">Keterangan</dt>
                                            <dd class="col-sm-8">{{ $item->keterangan ?? '-' }}</dd>
                                        </dl>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editKriteriaModal{{ $item->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('kriteria.update', $item->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Kriteria</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Kode Kriteria <span class="text-danger">*</span></label>
                                                <input type="text" name="kode_kriteria" class="form-control @error('kode_kriteria') is-invalid @enderror" value="{{ old('kode_kriteria', $item->kode_kriteria) }}" required>
                                                @error('kode_kriteria')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                                                <input type="text" name="nama_kriteria" class="form-control @error('nama_kriteria') is-invalid @enderror" value="{{ old('nama_kriteria', $item->nama_kriteria) }}" required>
                                                @error('nama_kriteria')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Keterangan</label>
                                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan', $item->keterangan) }}</textarea>
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
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data kriteria</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $kriteria->firstItem() ?? 0 }} - {{ $kriteria->lastItem() ?? 0 }} dari {{ $kriteria->total() }} data
                </div>
                <div>
                    {{ $kriteria->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Modal Create -->
<div class="modal fade" id="createKriteriaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('kriteria.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Kriteria <span class="text-danger">*</span></label>
                        <input type="text" name="kode_kriteria" class="form-control @error('kode_kriteria') is-invalid @enderror" value="{{ old('kode_kriteria') }}" required>
                        @error('kode_kriteria')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Kriteria <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kriteria" class="form-control @error('nama_kriteria') is-invalid @enderror" value="{{ old('nama_kriteria') }}" required>
                        @error('nama_kriteria')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan') }}</textarea>
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
