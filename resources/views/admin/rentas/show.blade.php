@extends('layouts.app-sidebar')
@section('title', 'Renta #' . $rental->id)

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">{{ $rental->property?->address ?? 'Renta #' . $rental->id }}</h1>
        <p class="page-subtitle">
            {{ $rental->property?->colony ?? '' }}
            @if($rental->lease_start_date && $rental->lease_end_date)
            &nbsp;·&nbsp; {{ $rental->lease_start_date->format('d/m/Y') }} → {{ $rental->lease_end_date->format('d/m/Y') }}
            @endif
        </p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.rentas.gestion') }}" class="btn btn-secondary btn-sm">← Gestión Post-Cierre</a>
    </div>
</div>

@livewire('admin.rentas-gestion-show', ['rental' => $rental])
@endsection
