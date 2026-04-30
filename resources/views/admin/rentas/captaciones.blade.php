@extends('layouts.app-sidebar')
@section('title', 'Captaciones de Renta')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Captaciones de Renta</h1>
        <p class="page-subtitle">Fase 1 — Evaluación y captación de inmuebles para renta</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.rentas.activas') }}" class="btn btn-secondary btn-sm">
            Rentas Activas <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>
</div>

@livewire('admin.rentas-kanban-fase1')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
@endsection
