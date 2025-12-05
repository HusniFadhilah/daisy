@include('layouts.header')

@include('layouts.navbar')

@include('layouts.sidebar')

<!-- Main Content -->
<main class="main-content">
<div class="container-fluid">
    <!-- Header dengan Tabs -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manajemen Indikator</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createIndikatorModal">
            <i class="bi bi-plus-circle"></i> Tambah Indikator
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
    @endif>

    <!-- Tabel Indikator -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Kriteria</th>
                            <th width="15%">Elemen</th>
                            <th width="10%">Kode</th>
                            <th width="10%">Jenis</th>
                            <th width="35%">Deskripsi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikator as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($indikator->currentPage() - 1) * $indikator->perPage() }}</td>
                            <td><span class="badge bg-primary">{{ $item->elemenStandar->kriteria->kode_kriteria ?? '-' }}</span></td>
                            <td><span class="badge bg-info text-dark">{{ $item->elemenStandar->kode_elemen ?? '-' }}</span></td>
                            <td><strong>{{ $item->kode_indikator }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $item->jenisIndikator->nama_jenis == 'Kualitatif' ? 'warning' : 'success' }}">
                                    {{ $item->jenisIndikator->nama_jenis ?? '-' }}
                                </span>
                            </td>
                            <td>{{ Str::limit($item->deskripsi_indikator, 60) }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#showIndikatorModal{{ $item->id_indikator }}" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning text-white" data-bs-toggle="modal" data-bs-target="#editIndikatorModal{{ $item->id_indikator }}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('indikator.destroy', $item->id_indikator) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus indikator ini?')">
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
                        <div class="modal fade" id="showIndikatorModal{{ $item->id_indikator }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Indikator</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <dl class="row">
                                            <dt class="col-sm-3">Kriteria</dt>
                                            <dd class="col-sm-9">{{ $item->elemenStandar->kriteria->kode_kriteria ?? '-' }} - {{ $item->elemenStandar->kriteria->nama_kriteria ?? '-' }}</dd>
                                            
                                            <dt class="col-sm-3">Elemen Standar</dt>
                                            <dd class="col-sm-9">{{ $item->elemenStandar->kode_elemen ?? '-' }} - {{ $item->elemenStandar->pernyataan_elemen ?? '-' }}</dd>
                                            
                                            <dt class="col-sm-3">Kode Indikator</dt>
                                            <dd class="col-sm-9">{{ $item->kode_indikator }}</dd>
                                            
                                            <dt class="col-sm-3">Jenis</dt>
                                            <dd class="col-sm-9">
                                                <span class="badge bg-{{ $item->jenisIndikator->nama_jenis == 'Kualitatif' ? 'warning' : 'success' }} fs-6">
                                                    {{ $item->jenisIndikator->nama_jenis ?? '-' }}
                                                </span>
                                            </dd>
                                            
                                            <dt class="col-sm-3">Deskripsi</dt>
                                            <dd class="col-sm-9">{!! nl2br(e($item->deskripsi_indikator)) !!}</dd>
                                        </dl>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editIndikatorModal{{ $item->id_indikator }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('indikator.update', $item->id_indikator) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Indikator</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                                                <select name="id_elemen" class="form-select @error('id_elemen') is-invalid @enderror" required>
                                                    <option value="">Pilih Elemen Standar</option>
                                                    @php
                                                        $elemenStandar = \App\Models\ElemenStandar::with('kriteria')->get();
                                                    @endphp
                                                    @foreach($elemenStandar as $elemen)
                                                        <option value="{{ $elemen->id_elemen }}" {{ old('id_elemen', $item->id_elemen) == $elemen->id_elemen ? 'selected' : '' }}>
                                                            {{ $elemen->kriteria->kode_kriteria ?? '' }} - {{ $elemen->kode_elemen }} - {{ Str::limit($elemen->pernyataan_elemen, 50) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('id_elemen')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kode Indikator <span class="text-danger">*</span></label>
                                                        <input type="text" name="kode_indikator" class="form-control @error('kode_indikator') is-invalid @enderror" value="{{ old('kode_indikator', $item->kode_indikator) }}" required>
                                                        @error('kode_indikator')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                                        <select name="id_jenis" class="form-select @error('id_jenis') is-invalid @enderror" required>
                                                            <option value="">Pilih Jenis</option>
                                                            @php
                                                                $jenisIndikator = \App\Models\JenisIndikator::all();
                                                            @endphp
                                                            @foreach($jenisIndikator as $jenis)
                                                                <option value="{{ $jenis->id_jenis }}" {{ old('id_jenis', $item->id_jenis) == $jenis->id_jenis ? 'selected' : '' }}>
                                                                    {{ $jenis->nama_jenis }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('id_jenis')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Deskripsi Indikator <span class="text-danger">*</span></label>
                                                <textarea name="deskripsi_indikator" class="form-control @error('deskripsi_indikator') is-invalid @enderror" rows="5" required>{{ old('deskripsi_indikator', $item->deskripsi_indikator) }}</textarea>
                                                <small class="form-text text-muted">Gunakan Enter untuk membuat baris baru</small>
                                                @error('deskripsi_indikator')
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
                            <td colspan="7" class="text-center">Belum ada data indikator</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $indikator->firstItem() ?? 0 }} - {{ $indikator->lastItem() ?? 0 }} dari {{ $indikator->total() }} data
                </div>
                <div>
                    {{ $indikator->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Modal Create -->
<div class="modal fade" id="createIndikatorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('indikator.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Indikator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                        <select name="id_elemen" class="form-select @error('id_elemen') is-invalid @enderror" required>
                            <option value="">Pilih Elemen Standar</option>
                            @php
                                $elemenStandar = \App\Models\ElemenStandar::with('kriteria')->get();
                            @endphp
                            @foreach($elemenStandar as $elemen)
                                <option value="{{ $elemen->id_elemen }}" {{ old('id_elemen') == $elemen->id_elemen ? 'selected' : '' }}>
                                    {{ $elemen->kriteria->kode_kriteria ?? '' }} - {{ $elemen->kode_elemen }} - {{ Str::limit($elemen->pernyataan_elemen, 50) }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_elemen')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Kode Indikator <span class="text-danger">*</span></label>
                                <input type="text" name="kode_indikator" class="form-control @error('kode_indikator') is-invalid @enderror" value="{{ old('kode_indikator') }}" required>
                                @error('kode_indikator')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                <select name="id_jenis" class="form-select @error('id_jenis') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis</option>
                                    @php
                                        $jenisIndikator = \App\Models\JenisIndikator::all();
                                    @endphp
                                    @foreach($jenisIndikator as $jenis)
                                        <option value="{{ $jenis->id_jenis }}" {{ old('id_jenis') == $jenis->id_jenis ? 'selected' : '' }}>
                                            {{ $jenis->nama_jenis }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Indikator <span class="text-danger">*</span></label>
                        <textarea name="deskripsi_indikator" class="form-control @error('deskripsi_indikator') is-invalid @enderror" rows="5" required>{{ old('deskripsi_indikator') }}</textarea>
                        <small class="form-text text-muted">Gunakan Enter untuk membuat baris baru</small>
                        @error('deskripsi_indikator')
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
