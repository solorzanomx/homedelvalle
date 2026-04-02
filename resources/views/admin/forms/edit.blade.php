@extends('layouts.app-sidebar')
@section('title', 'Editar: ' . $form->name)

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Formulario</h2>
        <p class="text-muted">{{ $form->name }}</p>
    </div>
    <a href="{{ route('admin.forms.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

@include('admin.forms._form', ['form' => $form])
@endsection
