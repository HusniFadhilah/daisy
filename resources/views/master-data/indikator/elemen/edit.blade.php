@extends('layouts.template.app')

@section('title', 'Edit Elemen Standar')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Elemen Standar</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('elemen-standar.update', $elemenStandar->id) }}" method="POST">
                @csrf
                @method('PUT')
                @include('master-data.indikator.elemen.partials.form', ['elemenStandar' => $elemenStandar])
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('elemen-standar.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
