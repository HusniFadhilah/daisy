@extends('layouts.template.app')

@section('title', 'Tambah Jenjang Penilaian')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Tambah Jenjang Penilaian</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('jenjang-penilaian.store') }}" method="POST">
                @csrf
                @include('master-data.jenjang-penilaian.partials.form')
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('jenjang-penilaian.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
