@extends('layouts.template.app')

@section('title', 'Data User - Daisy')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Kelola Pengguna</h2>
            <p class="text-muted mb-0">Manajemen data pengguna sistem</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm shadow-sm">
                <i class="fas fa-user-plus me-2"></i> Tambah Pengguna
            </a>
            <div class="btn-group shadow-sm" role="group">
                <a href="{{ route('users.export') }}" class="btn btn-success" title="Export ke Excel">
                    <i class="fas fa-file-excel me-1"></i> Export
                </a>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal" title="Import dari Excel">
                    <i class="fas fa-file-upload me-1"></i> Import
                </button>
                <a href="{{ route('users.template') }}" class="btn btn-outline-secondary" title="Download Template Excel">
                    <i class="fas fa-download me-1"></i> Template
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="users-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Role Aktif</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih File Excel</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">
                                Format: .xlsx, .xls, atau .csv (maksimal 2MB)<br>
                                <a href="{{ route('users.template') }}" class="text-primary">
                                    <i class="fas fa-download"></i> Download template untuk melihat format yang benar
                                </a>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <strong>Catatan:</strong>
                            <ul class="mb-0">
                                <li>Kolom wajib: <strong>nama, email</strong></li>
                                <li>Role aktif harus salah satu: super_admin, asesi, asesor, validator, verifikator, admin_univ, admin_prodi, default</li>
                                <li>Semua roles dipisah dengan koma (contoh: asesi,asesor)</li>
                                <li>Password default: <strong>password123</strong> jika tidak diisi</li>
                                <li>Email yang sudah ada akan di-update</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload & Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#users-table').DataTable({
            processing: true
            , serverSide: true
            , ajax: {
                url: '{{ route('
                users.index ') }}'
                , error: function(xhr, error, thrown) {
                    console.error('DataTables Error:', error, thrown);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading data. Check console for details.');
                }
            }
            , columns: [{
                    data: 'DT_RowIndex'
                    , name: 'DT_RowIndex'
                    , orderable: false
                    , searchable: false
                }
                , {
                    data: 'name'
                    , name: 'name'
                }
                , {
                    data: 'email'
                    , name: 'email'
                }
                , {
                    data: 'role'
                    , name: 'role'
                }
                , {
                    data: 'role_selected'
                    , name: 'role_selected'
                    , render: function(data, type, row) {
                        let html = '<span class="badge bg-info">' + (row.role_alias || data) + '</span>';
                        if (row.is_multiple_role) {
                            html += '<br><small class="text-muted"><i class="bi bi-person-badge"></i> Multiple</small>';
                        }
                        return html;
                    }
                }
                , {
                    data: 'created_at'
                    , name: 'created_at'
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
        });
    });

    function deleteRecord(id) {
        if (confirm('Yakin ingin menghapus pengguna ini?')) {
            $.ajax({
                url: '{{ url('
                users ') }}/' + id
                , type: 'DELETE'
                , data: {
                    _token: '{{ csrf_token() }}'
                }
                , success: function(result) {
                    $('#users-table').DataTable().ajax.reload();
                    alert('Pengguna berhasil dihapus');
                }
                , error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        }
    }

</script>
@endpush
