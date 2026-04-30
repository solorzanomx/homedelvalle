@extends('layouts.app-sidebar')
@section('title', 'Colocación Activa')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Colocación Activa</h1>
        <p class="page-subtitle">Fase 2 — Inmuebles en mercado buscando inquilino</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.rentas.captaciones') }}" class="btn btn-secondary btn-sm">
            <x-icon name="arrow-left" class="w-4 h-4" /> Captaciones
        </a>
        <a href="{{ route('admin.rentas.gestion') }}" class="btn btn-secondary btn-sm">
            Gestión Post-Cierre <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>
</div>

@livewire('admin.rentas-kanban-fase2')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
@endsection
