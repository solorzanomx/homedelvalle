@extends('layouts.portal')
@section('title', 'Mi Proceso de Compra')

@section('styles')
.compra-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.compra-stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem 1rem;
    text-align: center;
}
.compra-stat-val {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0E304B;
    line-height: 1;
    margin-bottom: .35rem;
}
.compra-stat-lbl {
    font-size: .72rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .4px;
}
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2>Mi Proceso de Compra</h2>
        @if($property)
        <p class="text-muted" style="font-size:.85rem;">
            {{ $property->address }}@if($property->colony), {{ $property->colony }}@endif
        </p>
        @endif
    </div>
</div>

@php
    $stageCopy = [
        'candidatos'      => ['title' => 'Tu oferta fue enviada', 'text' => 'Estamos esperando la respuesta del propietario sobre tu oferta.'],
        'oferta_aceptada' => ['title' => '¡Tu oferta fue aceptada!', 'text' => 'Felicidades — el siguiente paso es verificar tu documentación.'],
        'investigacion'   => ['title' => 'Verificando tu documentación', 'text' => 'Tu asesor está confirmando tus fondos y documentación legal para continuar.'],
        'contrato'        => ['title' => 'Preparando el contrato', 'text' => 'Tu asesor está preparando el contrato de compraventa. Te avisaremos cuando esté listo para firmar.'],
        'entrega'         => ['title' => 'Preparando la entrega', 'text' => 'Ya casi — estamos coordinando la firma ante notario y la entrega de llaves.'],
        'cierre'          => ['title' => '¡Compra cerrada con éxito!', 'text' => 'Gracias por tu confianza en Home del Valle.'],
    ];
    $copy = $stageCopy[$stage] ?? ['title' => 'En proceso', 'text' => ''];
@endphp

<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-body" style="padding:1.1rem 1.25rem;">
        <div style="font-size:1.05rem; font-weight:700; color:#0E304B; margin-bottom:.3rem;">{{ $copy['title'] }}</div>
        <div style="font-size:.88rem; color:var(--text-muted);">{{ $copy['text'] }}</div>
    </div>
</div>

@if($offer)
<div class="compra-stats">
    <div class="compra-stat-card">
        <div class="compra-stat-val">${{ number_format($offer->precio_ofertado, 0) }}</div>
        <div class="compra-stat-lbl">Monto ofertado</div>
    </div>
    <div class="compra-stat-card">
        <div class="compra-stat-val">{{ $offer->status_label }}</div>
        <div class="compra-stat-lbl">Estatus de tu oferta</div>
    </div>
    @if($stage === 'candidatos')
    <div class="compra-stat-card">
        <div class="compra-stat-val">{{ $offer->vigente_hasta->format('d/m/Y') }}</div>
        <div class="compra-stat-lbl">Vigente hasta</div>
    </div>
    @endif
</div>
@endif

@if(in_array($stage, ['oferta_aceptada', 'investigacion']))
<div class="card">
    <div class="card-body">
        <p style="font-size:.88rem; margin-bottom:.75rem;">Para continuar con tu compra necesitamos verificar tu documentación e información de financiamiento.</p>
        <a href="{{ route('portal.expediente') }}" class="btn btn-primary btn-sm">Completar mi expediente →</a>
    </div>
</div>
@endif

@endsection
