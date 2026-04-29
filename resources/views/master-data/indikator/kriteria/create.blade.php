@extends('layouts.template.app')

@section('title', 'Tambah Kriteria')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tambah Kriteria</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kriteria.store') }}" method="POST">
                @csrf
                @include('master-data.indikator.kriteria.partials.form')
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('kriteria.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
