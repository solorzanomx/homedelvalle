@extends('layouts.app-sidebar')
@section('title', 'Calculadora de Valor de Mercado')

@section('content')
<div class="page-header">
    <div>
        <h2>📊 Calculadora de Valor de Mercado</h2>
        <p class="text-muted">4 escenarios de valor basados en datos del Observatorio de Precios · Benito Juárez</p>
    </div>
</div>

<livewire:admin.quick-quote />

@endsection
