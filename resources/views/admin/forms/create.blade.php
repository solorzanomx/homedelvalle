@extends('layouts.app-sidebar')
@section('title', 'Nuevo Formulario')

@section('content')
<div class="page-header">
    <div>
        <h2>Nuevo Formulario</h2>
        <p class="text-muted">Crea un formulario personalizado</p>
    </div>
    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

@include('admin.forms._form', ['form' => null])
@endsection
