{{-- Componente: Imagen Hero --}}
{{-- Uso: @include('components.pdf.hero', ['property' => $property]) --}}

<div class="hero-section">
    @if($property->images && $property->images->first())
        <img src="{{ public_path('storage/' . $property->images->first()->path) }}" alt="{{ $property->title }}" class="hero-image">
    @else
        <div class="hero-image-placeholder">
            Imagen no disponible
        </div>
    @endif
</div>
