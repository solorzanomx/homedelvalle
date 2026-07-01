@extends('layouts.app-sidebar')
@section('title', 'Nueva captación desde llamada')

@section('content')
<div class="content-body">

    {{-- Encabezado --}}
    <div class="page-header">
        <div>
            <h2 style="display:flex;align-items:center;gap:.5rem;">
                <x-icon name="phone" class="w-5 h-5" style="color:var(--primary);" />
                Nueva captación desde llamada
            </h2>
            <p style="font-size:.83rem;color:var(--text-muted);margin-top:.25rem;">
                Captura los datos en 3 minutos y genera la presentación inicial para el propietario.
            </p>
        </div>
        <a href="{{ route('admin.captaciones.pipeline') }}" class="btn btn-outline btn-sm">
            <x-icon name="arrow-left" class="w-4 h-4" />
            Ver todas las captaciones
        </a>
    </div>

    {{-- Wizard Livewire --}}
    @livewire('admin.create-captacion-from-call')

</div>
@endsection
