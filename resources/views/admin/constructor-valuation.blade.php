@extends('layouts.app-sidebar')
@section('title', 'Valuación para Constructor')

@section('content')
<div class="page-header">
    <div>
        <h2>🏗 Valuación para Constructor</h2>
        <p class="text-muted">Análisis de viabilidad COS/CUS · Potencial constructivo · Valor residual del terreno</p>
    </div>
</div>

<livewire:admin.constructor-valuation />

@endsection
