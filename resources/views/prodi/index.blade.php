@extends('layouts.template.app')

@section('title', 'Daftar Program Studi - Daisy')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2>Daftar Program Studi</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Program Studi</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('study-programs.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Tambah Program Studi
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="studyProgramTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50px">No</th>
                            <th>Kode</th>
                            <th>Nama Program Studi</th>
                            <th>Universitas</th>
                            <th>Bentuk PT</th>
                            <th>Jenjang</th>
                            <th>Kategori</th>
                            <th>Peringkat</th>
                            <th width="100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('#studyProgramTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('study-programs.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'code', name: 'code'},
            {data: 'name', name: 'name'},
            {data: 'university_name', name: 'university.name'},
            {data: 'bentuk_pt', name: 'bentuk_pt'},
            {data: 'degree_level_name', name: 'degreeLevel.name'},
            {data: 'category_name', name: 'category.name'},
            {data: 'peringkat', name: 'peringkat_akreditasi'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
});

function deleteRecord(id) {
    if (confirm('Apakah Anda yakin ingin menghapus program studi ini?')) {
        var form = document.getElementById('deleteForm');
        form.action = '/study-programs/' + id;
        form.submit();
    }
}
</script>
@endpush
