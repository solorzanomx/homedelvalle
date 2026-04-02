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
                <svg class="w-12 h-12 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
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
            <svg class="w-3.5 h-3.5 shrink-0 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ collect([$property->colony, $property->city])->filter()->join(', ') }}
        </p>
        @endif

        {{-- Specs --}}
        <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-5 text-xs text-gray-500 font-medium">
            @if($property->bedrooms)
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ $property->bedrooms }} rec.
            </span>
            @endif
            @if($property->bathrooms)
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                {{ $property->bathrooms }} baños
            </span>
            @endif
            @if($property->area)
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                {{ number_format($property->area, 0) }} m²
            </span>
            @endif
            @if($property->parking)
            <span class="flex items-center gap-1.5">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 16h22M6 16v4m10-4v4"/></svg>
                {{ $property->parking }} est.
            </span>
            @endif
        </div>
    </div>
</a>
