@extends('layouts.template.app')

@section('title', 'Edit Jenis Indikator')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Jenis Indikator</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('jenis-indikator.update', $jenisIndikator->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('master-data.indikator.jenis.partials.form', ['jenisIndikator' => $jenisIndikator])
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('jenis-indikator.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
