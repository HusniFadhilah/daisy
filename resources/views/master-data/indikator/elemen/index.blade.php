@extends('layouts.template.app')

@section('title', 'Elemen Standar - Daisy')

@section('content')
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

    <!-- Tabel Elemen Standar -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="elemenStandarTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kriteria</th>
                            <th>Kode Elemen</th>
                            <th>Pernyataan Elemen</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="showElemenModal{{ $item->id_elemen }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
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

@push('scripts')
<script>
    $(document).ready(function() {
        $('#elemenStandarTable').DataTable({
            serverSide: true
            , processing: true
            , ajax: "{{ route('elemen-standar.index') }}"
            , columns: [{
                    data: 'DT_RowIndex'
                    , name: 'DT_RowIndex'
                    , orderable: false
                    , searchable: false
                }
                , {
                    data: 'kriteria_nama'
                    , name: 'kriteria_nama'
                }
                , {
                    data: 'kode_elemen'
                    , name: 'kode_elemen'
                }
                , {
                    data: 'pernyataan_elemen'
                    , name: 'pernyataan_elemen'
                }
                , {
                    data: 'keterangan'
                    , name: 'keterangan'
                }
                , {
                    data: 'action'
                    , name: 'action'
                    , orderable: false
                    , searchable: false
                }
            ]
            , language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            }
            , pageLength: 25
            , lengthMenu: [
                [10, 25, 50, 100, -1]
                , [10, 25, 50, 100, "Semua"]
            ]
        });
    });

    function deleteRecord(id) {
        if (confirm('Yakin ingin menghapus elemen standar ini?')) {
            $.ajax({
                url: '/elemen-standar/' + id
                , type: 'DELETE'
                , data: {
                    _token: '{{ csrf_token() }}'
                }
                , success: function(response) {
                    $('#elemenStandarTable').DataTable().ajax.reload();
                    alert('Data berhasil dihapus');
                }
                , error: function(xhr) {
                    alert('Gagal menghapus data');
                }
            });
        }
    }

</script>
@endpush
