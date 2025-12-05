@include('layouts.header')

@include('layouts.navbar')

@include('layouts.sidebar')

<!-- Main Content -->
<main class="main-content">
<div class="container-fluid">
    <!-- Header dengan Tabs -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manajemen Indikator</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createElemenModal">
            <i class="bi bi-plus-circle"></i> Tambah Elemen Standar
        </button>
    </div>

    <!-- Navigation Tabs -->
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabel Elemen Standar -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kriteria</th>
                            <th width="10%">Kode</th>
                            <th width="30%">Pernyataan Elemen</th>
                            <th width="20%">Pernyataan Standar</th>
                            <th width="10%">Keterangan</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($elemenStandar as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($elemenStandar->currentPage() - 1) * $elemenStandar->perPage() }}</td>
                            <td><span class="badge bg-primary">{{ $item->kriteria->kode_kriteria ?? '-' }}</span></td>
                            <td><span class="badge bg-info text-dark">{{ $item->kode_elemen }}</span></td>
                            <td><strong>{{ Str::limit($item->pernyataan_elemen, 50) }}</strong></td>
                            <td>
                                @if($item->pernyataan->count() > 0)
                                    <span class="badge bg-success">{{ $item->pernyataan->count() }} pernyataan</span>
                                @else
                                    <span class="badge bg-secondary">Belum ada</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($item->keterangan, 30) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#showElemenModal{{ $item->id_elemen }}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editElemenModal{{ $item->id_elemen }}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('elemen-standar.destroy', $item->id_elemen) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus elemen standar ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Detail -->
                        <div class="modal fade" id="showElemenModal{{ $item->id_elemen }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Elemen Standar</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-3">Kriteria</dt>
                                            <dd class="col-sm-9">{{ $item->kriteria->kode_kriteria ?? '-' }} - {{ $item->kriteria->nama_kriteria ?? '-' }}</dd>
                                            
                                            <dt class="col-sm-3">Kode Elemen</dt>
                                            <dd class="col-sm-9">{{ $item->kode_elemen }}</dd>
                                            
                                            <dt class="col-sm-3">Pernyataan Elemen</dt>
                                            <dd class="col-sm-9">{{ $item->pernyataan_elemen }}</dd>
                                            
                                            <dt class="col-sm-3">Keterangan</dt>
                                            <dd class="col-sm-9">{{ $item->keterangan ?? '-' }}</dd>
                                            
                                            <dt class="col-sm-3">Pernyataan Standar</dt>
                                            <dd class="col-sm-9">
                                                @if($item->pernyataan->count() > 0)
                                                    <ul>
                                                        @foreach($item->pernyataan as $pernyataan)
                                                            <li><strong>{{ $pernyataan->code }}</strong>: {{ $pernyataan->pernyataan }}</li>
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

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editElemenModal{{ $item->id_elemen }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('elemen-standar.update', $item->id_elemen) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Elemen Standar</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Kriteria <span class="text-danger">*</span></label>
                                                <select name="id_kriteria" class="form-select @error('id_kriteria') is-invalid @enderror" required>
                                                    <option value="">Pilih Kriteria</option>
                                                    @php
                                                        $kriteria = \App\Models\Kriteria::all();
                                                    @endphp
                                                    @foreach($kriteria as $k)
                                                        <option value="{{ $k->id_kriteria }}" {{ old('id_kriteria', $item->id_kriteria) == $k->id_kriteria ? 'selected' : '' }}>
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
                                                <input type="text" name="kode_elemen" class="form-control @error('kode_elemen') is-invalid @enderror" value="{{ old('kode_elemen', $item->kode_elemen) }}" required>
                                                @error('kode_elemen')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Pernyataan Elemen <span class="text-danger">*</span></label>
                                                <textarea name="pernyataan_elemen" class="form-control @error('pernyataan_elemen') is-invalid @enderror" rows="3" required>{{ old('pernyataan_elemen', $item->pernyataan_elemen) }}</textarea>
                                                @error('pernyataan_elemen')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Keterangan</label>
                                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="2">{{ old('keterangan', $item->keterangan) }}</textarea>
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
                            <td colspan="7" class="text-center">Belum ada data elemen standar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $elemenStandar->firstItem() ?? 0 }} - {{ $elemenStandar->lastItem() ?? 0 }} dari {{ $elemenStandar->total() }} data
                </div>
                <div>
                    {{ $elemenStandar->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Modal Create -->
<div class="modal fade" id="createElemenModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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
                            @php
                                $kriteria = \App\Models\Kriteria::all();
                            @endphp
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

@include('layouts.footer')
