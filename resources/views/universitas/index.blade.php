@extends('layouts.template.app')

@section('title', 'Data Universitas - Daisy')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Universitas</h2>
        <a href="{{ route('universities.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Universitas
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="universitiesTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Nama Universitas</th>
                            <th>Jumlah Prodi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#universitiesTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: "{{ route('universities.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'study_programs_count', name: 'study_programs_count' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]]
        });
    });

    function deleteRecord(id) {
        if (confirm('Yakin ingin menghapus universitas ini?')) {
            $.ajax({
                url: '/universities/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#universitiesTable').DataTable().ajax.reload();
                    alert('Data berhasil dihapus');
                },
                error: function(xhr) {
                    alert('Gagal menghapus data');
                }
            });
        }
    }
</script>
@endpush
            },
            order: [[2, 'asc']]
        });
    });
</script>
@endpush
@endsection
