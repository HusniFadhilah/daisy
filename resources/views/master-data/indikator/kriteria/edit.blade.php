@extends('layouts.template.app')

@section('title', 'Edit Kriteria')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Kriteria</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('kriteria.update', $kriteria->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('master-data.indikator.kriteria.partials.form', ['kriteria' => $kriteria])
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('kriteria.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
