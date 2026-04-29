@extends('layouts.template.app')

@section('title', 'Tambah Jenis Indikator')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tambah Jenis Indikator</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('jenis-indikator.store') }}" method="POST">
                @csrf
                @include('master-data.indikator.jenis.partials.form')
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('jenis-indikator.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
