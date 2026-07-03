@extends('layouts.portal')
@section('title', 'En el mercado')

@section('styles')
.mercado-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.mercado-stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem 1rem;
    text-align: center;
}
.mercado-stat-val {
    font-size: 1.75rem;
    font-weight: 800;
    color: #0E304B;
    line-height: 1;
    margin-bottom: .35rem;
}
.mercado-stat-lbl {
    font-size: .72rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .4px;
}
.mercado-tag {
    display: inline-block;
    background: #EFF6FF;
    color: #1D4ED8;
    font-size: .78rem;
    font-weight: 600;
    padding: .3rem .7rem;
    border-radius: 999px;
    margin: .2rem .3rem .2rem 0;
}
.mercado-point {
    display: flex;
    gap: .5rem;
    align-items: flex-start;
    padding: .4rem 0;
    font-size: .88rem;
}
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2>En el mercado</h2>
        @if($property)
        <p class="text-muted" style="font-size:.85rem;">
            {{ $property->address }}@if($property->colony), {{ $property->colony }}@endif
        </p>
        @endif
    </div>
</div>

@php
    $stageCopy = [
        'mejoras'         => ['title' => 'Preparando tu inmueble', 'text' => 'Estamos coordinando los arreglos y mejoras necesarias antes de las fotos.'],
        'fotos_video'     => ['title' => 'Sesión de fotos y video', 'text' => 'Estamos preparando el material fotográfico y de video para promover tu propiedad.'],
        'carpeta_lista'   => ['title' => 'Últimos detalles antes de publicar', 'text' => 'Estamos afinando la descripción, fotos y estrategia antes de salir al mercado.'],
        'publicacion'     => ['title' => '¡Tu propiedad ya está publicada!', 'text' => 'Tu propiedad es visible para compradores potenciales.'],
        'candidatos'      => ['title' => 'Tenemos interesados', 'text' => 'Estamos recibiendo y evaluando ofertas por tu propiedad.'],
        'oferta_aceptada' => ['title' => '¡Aceptaste una oferta!', 'text' => 'Estamos verificando la documentación del comprador antes de firmar.'],
        'investigacion'   => ['title' => 'Verificando al comprador', 'text' => 'Tu asesor está confirmando la documentación del comprador.'],
        'contrato'        => ['title' => 'Firmando el contrato', 'text' => 'Estamos en el proceso de firma del contrato de compraventa.'],
        'entrega'         => ['title' => 'Preparando la entrega', 'text' => 'Ya casi — estamos coordinando la entrega de tu inmueble.'],
        'cierre'          => ['title' => '¡Venta cerrada con éxito!', 'text' => 'Gracias por tu confianza en Home del Valle.'],
    ];
    $copy = $stageCopy[$stage] ?? ['title' => 'En proceso', 'text' => ''];
    $isPreparacion = in_array($stage, ['mejoras', 'fotos_video', 'carpeta_lista']);
@endphp

<div class="card" style="margin-bottom:1.25rem;">
    <div class="card-body" style="padding:1.1rem 1.25rem;">
        <div style="font-size:1.05rem; font-weight:700; color:#0E304B; margin-bottom:.3rem;">{{ $copy['title'] }}</div>
        <div style="font-size:.88rem; color:var(--text-muted);">{{ $copy['text'] }}</div>
    </div>
</div>

@if($isPreparacion)
    <div class="mercado-stats">
        <div class="mercado-stat-card">
            <div class="mercado-stat-val">{{ $photosCount }}</div>
            <div class="mercado-stat-lbl">Fotos subidas</div>
        </div>
        <div class="mercado-stat-card">
            <div class="mercado-stat-val">{{ $hasVideo ? '✓' : '—' }}</div>
            <div class="mercado-stat-lbl">Recorrido en video</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Estrategia de promoción</strong></div>
        <div class="card-body">
            @if($strategy)
                @if(!empty($strategy->target_audience['perfil']))
                <p style="font-size:.88rem; margin-bottom:.9rem;">
                    <strong>¿A quién le vamos a mostrar tu propiedad?</strong><br>
                    {{ $strategy->target_audience['perfil'] }}
                </p>
                @endif
                @if($strategy->positioning_summary)
                <p style="font-size:.88rem; margin-bottom:.9rem;">
                    <strong>Cómo la vamos a posicionar</strong><br>
                    {{ $strategy->positioning_summary }}
                </p>
                @endif
                @if(!empty($strategy->key_selling_points))
                <div style="margin-bottom:.9rem;">
                    <strong style="font-size:.88rem;">Lo que más va a destacar</strong>
                    @foreach($strategy->key_selling_points as $point)
                    <div class="mercado-point">✓ {{ $point }}</div>
                    @endforeach
                </div>
                @endif
                @if(!empty($strategy->recommended_channels))
                <div>
                    <strong style="font-size:.88rem; display:block; margin-bottom:.4rem;">Dónde la vamos a promover</strong>
                    @foreach($strategy->recommended_channels as $channel)
                    <span class="mercado-tag">{{ $channel }}</span>
                    @endforeach
                </div>
                @endif
            @else
                <p style="font-size:.88rem; color:var(--text-muted);">Tu asesor está preparando el material y la estrategia de promoción de tu propiedad. Te avisaremos en cuanto esté lista.</p>
            @endif
        </div>
    </div>
@endif

@if($stage === 'publicacion')
    <div class="mercado-stats">
        <div class="mercado-stat-card">
            <div class="mercado-stat-val">{{ $viewsTotal ?? 0 }}</div>
            <div class="mercado-stat-lbl">Vistas (30 días)</div>
        </div>
        <div class="mercado-stat-card">
            <div class="mercado-stat-val">{{ $viewsUnique ?? 0 }}</div>
            <div class="mercado-stat-lbl">Visitantes únicos</div>
        </div>
    </div>
    @if($property?->easybroker_public_url)
    <div class="card">
        <div class="card-body">
            <a href="{{ $property->easybroker_public_url }}" target="_blank" class="btn btn-primary btn-sm">Ver mi propiedad publicada →</a>
        </div>
    </div>
    @endif
@endif

@if($stage === 'candidatos')
    <div class="mercado-stats">
        <div class="mercado-stat-card">
            <div class="mercado-stat-val">{{ $pendingOffersCount ?? 0 }}</div>
            <div class="mercado-stat-lbl">Personas interesadas</div>
        </div>
    </div>
@endif

@if($stage === 'oferta_aceptada' && $acceptedOffer)
    <div class="card">
        <div class="card-body">
            <p style="font-size:.88rem;">Oferta aceptada por <strong>${{ number_format($acceptedOffer->precio_ofertado, 0) }} MXN</strong>.</p>
        </div>
    </div>
@endif

@endsection
