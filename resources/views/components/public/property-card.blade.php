@props(['property'])

@php
    $photo = $property->photo_url;
    $typeLabels = ['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega','Building'=>'Edificio'];
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
@endphp

<a href="{{ route('propiedades.show', ['id' => $property->id, 'slug' => $property->slug]) }}"
   class="group block bg-white rounded-2xl border border-gray-200/60 overflow-hidden hover:shadow-premium-lg hover:border-brand-100 hover:-translate-y-1 transition-all duration-500">
    {{-- Image --}}
    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
        @if($photo)
            <img src="{{ $photo }}" alt="{{ $property->title }}" class="w-full h-full object-cover img-zoom" loading="lazy">
        @else
            <div class="flex items-center justify-center h-full bg-gradient-to-br from-brand-50 to-brand-100">
                <x-icon name="home" class="w-12 h-12 text-brand-300" />
            </div>
        @endif

        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex gap-2">
            @if($property->operation_type)
            <span class="inline-flex items-center rounded-lg bg-brand-600/90 backdrop-blur-sm px-3 py-1.5 text-xs font-semibold text-white shadow-sm">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
            @endif
            @if($property->property_type)
            <span class="inline-flex items-center rounded-lg bg-white/90 backdrop-blur-sm px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm">{{ $typeLabels[$property->property_type] ?? $property->property_type }}</span>
            @endif
        </div>
    </div>

    {{-- Content --}}
    <div class="p-5">
        <p class="text-xl font-extrabold text-gray-900 tracking-tight">{{ $property->formatted_price }}</p>
        <h3 class="mt-1.5 text-sm font-semibold text-gray-600 line-clamp-1 group-hover:text-brand-600 transition-colors duration-300">{{ $property->title }}</h3>

        @if($property->colony || $property->city)
        <p class="mt-2 text-xs text-gray-400 flex items-center gap-1.5">
            <x-icon name="map-pin" class="w-3.5 h-3.5 shrink-0 text-brand-400" />
            {{ collect([$property->colony, $property->city])->filter()->join(', ') }}
        </p>
        @endif

        {{-- Specs --}}
        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-5 text-xs text-gray-500 font-medium">
            @if($property->bedrooms)
            <span class="flex items-center gap-1.5">
                <x-icon name="bed-double" class="w-4 h-4 text-gray-400" />
                {{ $property->bedrooms }} rec.
            </span>
            @endif
            @if($property->bathrooms)
            <span class="flex items-center gap-1.5">
                <x-icon name="bath" class="w-4 h-4 text-gray-400" />
                {{ $property->bathrooms }} baños
            </span>
            @endif
            @if($property->area)
            <span class="flex items-center gap-1.5">
                <x-icon name="maximize" class="w-4 h-4 text-gray-400" />
                {{ number_format($property->area, 0) }} m²
            </span>
            @endif
            @if($property->parking)
            <span class="flex items-center gap-1.5">
                <x-icon name="car" class="w-4 h-4 text-gray-400" />
                {{ $property->parking }} est.
            </span>
            @endif
        </div>
    </div>
</a>
