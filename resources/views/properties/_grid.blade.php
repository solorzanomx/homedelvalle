@php
    $types = ['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega','Building'=>'Edificio'];
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
@endphp

@if($properties->count())
<div class="p-grid">
    @foreach($properties as $property)
    <div class="p-card">
        <a href="{{ route('properties.show', $property) }}" class="p-card-img">
            @if($property->photo)
                <img src="{{ asset('storage/' . $property->photo) }}" alt="{{ $property->title }}" loading="lazy">
            @else
                <div class="p-card-placeholder">&#8962;</div>
            @endif
            <div class="p-card-badges">
                <span class="p-badge p-badge-type">{{ $types[$property->property_type] ?? $property->property_type ?? '-' }}</span>
                @if($property->operation_type)
                    <span class="p-badge p-badge-op">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
                @endif
            </div>
            <div class="p-card-status p-status-{{ $property->status ?? 'available' }}">
                {{ $property->status === 'sold' ? 'Vendido' : ($property->status === 'rented' ? 'Rentado' : 'Disponible') }}
            </div>
            <div class="p-card-price">${{ number_format($property->price, 0) }} {{ $property->currency ?? 'MXN' }}</div>
        </a>
        <div class="p-card-body">
            <a href="{{ route('properties.show', $property) }}" class="p-card-title">{{ $property->title }}</a>
            <div class="p-card-loc">{{ implode(', ', array_filter([$property->colony, $property->city])) ?: 'Sin ubicacion' }}</div>
            <div class="p-card-specs">
                @if($property->bedrooms !== null)<span title="Recamaras">&#128716; {{ $property->bedrooms }}</span>@endif
                @if($property->bathrooms !== null)<span title="Banos">&#128703; {{ $property->bathrooms }}</span>@endif
                @if($property->area !== null)<span title="Area">&#9633; {{ number_format($property->area, 0) }}m&sup2;</span>@endif
                @if($property->parking !== null)<span title="Estacionamientos">&#127359; {{ $property->parking }}</span>@endif
            </div>
            @if($property->owner)
            <div class="p-card-owner">
                <span class="p-owner-dot"></span>
                {{ $property->owner->name }}
            </div>
            @endif
        </div>
        <div class="p-card-actions" onclick="event.stopPropagation();">
            <a href="{{ route('properties.show', $property) }}" class="btn btn-sm btn-outline" style="flex:1; text-align:center;">Ver</a>
            <a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-outline" style="flex:1; text-align:center;">Editar</a>
            <button type="button" class="btn btn-sm btn-outline" style="flex:1; text-align:center;" onclick="shareProperty({{ $property->id }}, '{{ addslashes($property->title) }}', '{{ route('propiedades.show', [$property->id, $property->slug]) }}')" title="Compartir">&#9993;</button>
        </div>
    </div>
    @endforeach
</div>
@if($properties->hasPages())
<div style="margin-top:1.25rem; text-align:center;">{{ $properties->links() }}</div>
@endif
@else
<div class="p-empty">
    <div class="p-empty-icon">&#8962;</div>
    <div style="font-size:0.95rem; margin-bottom:0.5rem;">No hay propiedades</div>
    <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:1rem;">Ajusta los filtros o crea una nueva propiedad</div>
    <a href="{{ route('properties.create') }}" class="btn btn-primary">+ Nueva Propiedad</a>
</div>
@endif
