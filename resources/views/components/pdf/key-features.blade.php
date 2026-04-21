{{-- Componente: Características Clave --}}
{{-- Uso: @include('components.pdf.key-features', ['property' => $property]) --}}

<div class="key-features">
    @if($property->terreno_m2)
        <div class="feature-card">
            <span class="feature-icon">📐</span>
            <div class="feature-value">{{ number_format($property->terreno_m2, 0) }}</div>
            <div class="feature-label">M² Terreno</div>
        </div>
    @endif

    @if($property->construccion_m2)
        <div class="feature-card">
            <span class="feature-icon">🏗️</span>
            <div class="feature-value">{{ number_format($property->construccion_m2, 0) }}</div>
            <div class="feature-label">M² Construido</div>
        </div>
    @endif

    @if($property->recamaras)
        <div class="feature-card">
            <span class="feature-icon">🛏️</span>
            <div class="feature-value">{{ $property->recamaras }}</div>
            <div class="feature-label">Recámaras</div>
        </div>
    @endif

    @if($property->baños)
        <div class="feature-card">
            <span class="feature-icon">🚿</span>
            <div class="feature-value">{{ $property->baños }}</div>
            <div class="feature-label">Baños</div>
        </div>
    @endif
</div>
