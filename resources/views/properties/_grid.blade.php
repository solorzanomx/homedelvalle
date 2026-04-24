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
                @if($property->is_featured)
                    <span class="p-badge" style="background:rgba(245,158,11,0.9); color:#fff;">&#9733; Destacada</span>
                @endif
            </div>
            <div class="p-card-status p-status-{{ $property->status ?? 'captacion' }}">
                {{ match($property->status) {
                    'available'  => 'Disponible',
                    'captacion'  => 'En Captación',
                    'reserved'   => 'Reservada',
                    'sold'       => 'Vendida',
                    'rented'     => 'Rentada',
                    default      => 'En Captación',
                } }}
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
            <form method="POST" action="{{ route('properties.toggle-featured', $property) }}" style="flex:1;">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm btn-outline" style="width:100%; {{ $property->is_featured ? 'color:#f59e0b; border-color:#f59e0b;' : '' }}" title="{{ $property->is_featured ? 'Quitar de destacadas' : 'Destacar en home' }}">&#9733;</button>
            </form>
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
