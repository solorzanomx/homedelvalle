@extends('layouts.app-sidebar')
@section('title', 'Leads & Formularios')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Leads & Formularios</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">Solicitudes recibidas en tiempo real</p>
    </div>
    <a href="{{ route('admin.form-submissions.create') }}" class="btn btn-primary">+ Nuevo Lead</a>
</div>

<livewire:admin.form-submissions-table />
@endsection
