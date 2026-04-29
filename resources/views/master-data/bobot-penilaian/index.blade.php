@extends('layouts.template.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Bobot Penilaian</h1>
            <p class="text-muted">Kelola bobot penilaian untuk setiap elemen standar berdasarkan kategori program studi</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBobot">
            <i class="bi bi-plus-lg"></i> Tambah Bobot
        </button>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('bobot-penilaian.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filter Kriteria/Elemen</label>
                    <select name="id_elemen" class="form-select" id="filterElemen">
                        <option value="">Semua Elemen</option>
                        @foreach($elemens as $elemen)
                        <option value="{{ $elemen->id }}" {{ request('id_elemen') == $elemen->id ? 'selected' : '' }}>
                            {{ $elemen->kriteria->kode_kriteria ?? '' }}.{{ $elemen->kode_elemen }} - {{ $elemen->pernyataan_elemen }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter Kategori</label>
                    <select name="id_category" class="form-select" id="filterCategory">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('id_category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter Jenjang</label>
                    <select name="id_degree_level" class="form-select" id="filterDegreeLevel">
                        <option value="">Semua Jenjang</option>
                        @foreach($degreeLevels as $level)
                        <option value="{{ $level->id }}" {{ request('id_degree_level') == $level->id ? 'selected' : '' }}>
                            {{ $level->name ?? $level->code }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary me-2">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('bobot-penilaian.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="bobotTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Elemen Standar</th>
                            <th>Kategori / Jenjang</th>
                            <th>Asesmen</th>
                            <th>Bobot</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Perhitungan Section -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-calculator"></i> Perhitungan Nilai Berbobot</h5>
        </div>
        <div class="card-body">
            <form id="formHitung" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Pilih Asesmen</label>
                    <select name="asesmen_id" class="form-select" required>
                        <option value="">-- Pilih Asesmen --</option>
                        @foreach($asesmens as $asesmen)
                        <option value="{{ $asesmen->id }}">
                            {{ $asesmen->name }} - {{ $asesmen->perguruan_tinggi ?? 'N/A' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Pilih Kategori</label>
                    <select name="id_category" class="form-select" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-calculator"></i> Hitung
                    </button>
                </div>
            </form>

            <div id="hasilHitung" class="mt-4" style="display: none;">
                <hr>
                <h5>Hasil Perhitungan</h5>
                <div id="hasilContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Bobot -->
<div class="modal fade" id="modalBobot" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formBobot" method="POST" action="{{ route('bobot-penilaian.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="bobot_id" id="bobotId">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Bobot Penilaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Elemen Standar <span class="text-danger">*</span></label>
                        <select name="id_elemen" class="form-select" id="inputElemen" required>
                            <option value="">-- Pilih Elemen --</option>
                            @foreach($elemens as $elemen)
                            <option value="{{ $elemen->id }}">
                                {{ $elemen->kriteria->kode_kriteria ?? '' }}.{{ $elemen->kode_elemen }} - {{ $elemen->pernyataan_elemen }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="id_category" class="form-select" id="inputCategory" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenjang <span class="text-danger">*</span></label>
                        <select name="id_degree_level" class="form-select" id="inputDegreeLevel" required>
                            <option value="">-- Pilih Jenjang --</option>
                            @foreach($degreeLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->name ?? $level->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bobot <span class="text-danger">*</span></label>
                        <input type="number" name="bobot" class="form-control" id="inputBobot" min="0" max="100" required placeholder="Masukkan bobot (0-100)">
                        <small class="text-muted">Bobot dalam skala 0-100</small>
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

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@push('scripts')
<!-- jQuery (jika belum ada) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<script>
    console.log('Script loaded');
    console.log('jQuery version:', typeof $ !== 'undefined' ? $.fn.jquery : 'jQuery not found');
    console.log('Select2 exists:', typeof $.fn.select2 !== 'undefined');
    console.log('DataTable exists:', typeof $.fn.DataTable !== 'undefined');

    // Initialize Select2
    $(document).ready(function() {
        console.log('Document ready - initializing components...');
        $('#filterElemen, #filterCategory, #filterDegreeLevel, #inputElemen, #inputCategory, #inputDegreeLevel').select2({
            theme: 'bootstrap-5'
            , width: '100%'
        });

        // Initialize DataTable
        $('#bobotTable').DataTable({
            serverSide: true
            , processing: true
            , ajax: {
                url: "{{ route('bobot-penilaian.index') }}"
                , data: function(d) {
                    d.id_elemen = $('#filterElemen').val();
                    d.id_category = $('#filterCategory').val();
                    d.id_degree_level = $('#filterDegreeLevel').val();
                }
            }
            , columns: [{
                    data: 'DT_RowIndex'
                    , name: 'DT_RowIndex'
                    , orderable: false
                    , searchable: false
                }
                , {
                    data: 'elemen_standar'
                    , name: 'elemen_standar'
                }
                , {
                    data: 'category'
                    , name: 'category'
                }
                , {
                    data: 'asesmen'
                    , name: 'asesmen'
                }
                , {
                    data: 'bobot'
                    , name: 'bobot'
                }
                , {
                    data: 'status'
                    , name: 'status'
                    , orderable: false
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

        // Reload table on filter submit
        $('form[method="GET"]').on('submit', function(e) {
            e.preventDefault();
            $('#bobotTable').DataTable().ajax.reload();
        });
    });

    async function deleteRecord(id) {
        if (await swalConfirmSubmit('warning', 'Yakin ingin menghapus bobot ini?')) {
            $.ajax({
                url: '/bobot-penilaian/' + id
                , type: 'DELETE'
                , data: {
                    _token: '{{ csrf_token() }}'
                }
                , success: function(response) {
                    $('#bobotTable').DataTable().ajax.reload();
                    Swal.fire('Berhasil', 'Data berhasil dihapus', 'success');
                }
                , error: function(xhr) {
                    Swal.fire('Perhatian', 'Gagal menghapus data', 'error');
                }
            });
        }
    }

    function toggleActive(id) {
        $.ajax({
            url: '/bobot-penilaian/' + id + '/toggle'
            , type: 'POST'
            , data: {
                _token: '{{ csrf_token() }}'
            }
            , success: function(response) {
                $('#bobotTable').DataTable().ajax.reload();
            }
            , error: function(xhr) {
                Swal.fire('Perhatian', 'Gagal mengubah status', 'error');
            }
        });
    }

    // Edit Bobot
    function editBobot(id, elemenId, categoryId, degreeLevelId, bobot) {
        $('#modalTitle').text('Edit Bobot Penilaian');
        $('#formMethod').val('PUT');
        $('#formBobot').attr('action', `/bobot-penilaian/${id}`);
        $('#bobotId').val(id);
        $('#inputElemen').val(elemenId).trigger('change');
        $('#inputCategory').val(categoryId).trigger('change');
        $('#inputDegreeLevel').val(degreeLevelId).trigger('change');
        $('#inputBobot').val(bobot);
        $('#modalBobot').modal('show');
    }

    // Reset form saat modal ditutup
    $('#modalBobot').on('hidden.bs.modal', function() {
        $('#modalTitle').text('Tambah Bobot Penilaian');
        $('#formMethod').val('POST');
        $('#formBobot').attr('action', '{{ route("bobot-penilaian.store") }}');
        $('#formBobot')[0].reset();
        $('#inputElemen').val('').trigger('change');
        $('#inputCategory').val('').trigger('change');
        $('#inputDegreeLevel').val('').trigger('change');
    });

    // Hitung nilai berbobot
    $('#formHitung').on('submit', function(e) {
        e.preventDefault();

        const asesmenId = $(this).find('[name="asesmen_id"]').val();
        const categoryId = $(this).find('[name="id_category"]').val();

        if (!asesmenId || !categoryId) {
            Swal.fire('Perhatian', 'Pilih asesmen dan kategori terlebih dahulu', 'warning');
            return;
        }

        // Show loading
        $('#hasilContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Menghitung...</p></div>');
        $('#hasilHitung').show();

        $.ajax({
            url: `/bobot-penilaian/hitung/${asesmenId}/${categoryId}`
            , method: 'GET'
            , dataType: 'json'
            , timeout: 10000, // 10 second timeout
            success: function(response) {
                // Check if redirected to login
                if (typeof response === 'string' && response.includes('login')) {
                    $('#hasilContent').html('<div class="alert alert-warning">Session expired. Silakan refresh halaman dan login kembali.</div>');
                    return;
                }

                let html = '<div class="row">';

                // Per Kriteria
                response.per_kriteria.forEach((kriteria, index) => {
                    html += `
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <strong>${kriteria.kriteria_kode}</strong> - ${kriteria.kriteria_nama}
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Elemen</th>
                                                <th>Skor</th>
                                                <th>Bobot</th>
                                                <th>Nilai Berbobot</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;

                    kriteria.elemen.forEach(elemen => {
                        html += `
                            <tr>
                                <td>${elemen.elemen_kode} - ${elemen.elemen_nama}</td>
                                <td>${elemen.skor}</td>
                                <td>${elemen.bobot}</td>
                                <td><strong>${elemen.nilai_bobot}</strong></td>
                            </tr>
                        `;
                    });

                    html += `
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-active">
                                                <td colspan="3"><strong>Total Kriteria</strong></td>
                                                <td><strong>${kriteria.total}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                });

                // Total Keseluruhan
                html += `
                    </div>
                    <div class="card bg-success text-white mt-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6>Total Nilai Berbobot</h6>
                                    <h3>${response.total_nilai_bobot}</h3>
                                </div>
                                <div class="col-md-4">
                                    <h6>Total Bobot</h6>
                                    <h3>${response.total_bobot}</h3>
                                </div>
                                <div class="col-md-4">
                                    <h6>Nilai Akhir</h6>
                                    <h3>${response.nilai_akhir}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#hasilContent').html(html);
                $('#hasilHitung').slideDown();
            }
            , error: function(xhr) {
                console.error('Error:', xhr);
                let errorMsg = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMsg = xhr.responseText;
                }
                $('#hasilContent').html(`<div class="alert alert-danger">${errorMsg}</div>`);
                Swal.fire('Perhatian', 'Gagal menghitung bobot: ' + errorMsg, 'error');
            }
        });
    });

</script>
@endpush
