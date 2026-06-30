@php
    $mapsKey  = config('services.google_maps.key');
    $addrParts = array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
        'México',
    ]);
    $hasLocation = $mapsKey && count($addrParts) >= 2;
    $addrStr     = implode(', ', $addrParts);
    $addrEncoded = $hasLocation ? urlencode($addrStr) : null;
    $addrDisplay = implode(', ', array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
    ]));
    $mapsLink = $hasLocation
        ? 'https://www.google.com/maps/search/?api=1&query=' . $addrEncoded
        : null;

    // Google Maps Embed API URLs (interactive)
    $streetViewEmbed = $hasLocation
        ? 'https://www.google.com/maps/embed/v1/streetview?key=' . $mapsKey
            . '&location=' . $addrEncoded
            . '&fov=90&pitch=5'
        : null;

    $mapEmbed = $hasLocation
        ? 'https://www.google.com/maps/embed/v1/place?key=' . $mapsKey
            . '&q=' . $addrEncoded
            . '&zoom=16&maptype=roadmap'
        : null;
@endphp

@if($hasLocation)
<div class="mt-10" x-data="{ tab: 'street' }" x-intersect.once="$el.classList.add('animate-fade-in-up')">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h2 class="text-xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
            <x-icon name="map-pin" class="w-5 h-5 text-brand-500" />
            Ubicación
        </h2>
        {{-- Tab pills --}}
        <div class="flex items-center gap-1 bg-gray-100 rounded-full p-1 text-sm font-medium">
            <button @click="tab = 'street'"
                    :class="tab === 'street' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 rounded-full transition-all duration-200 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Vista de calle
            </button>
            <button @click="tab = 'map'"
                    :class="tab === 'map' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 rounded-full transition-all duration-200 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                </svg>
                Mapa
            </button>
        </div>
    </div>

    {{-- Card --}}
    <div class="rounded-2xl overflow-hidden border border-gray-200/80 shadow-sm bg-white">

        {{-- Street View panel (interactive iframe) --}}
        <div x-show="tab === 'street'"
             x-transition:enter="transition-opacity duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="aspect-ratio:16/7;position:relative;">
            <iframe
                src="{{ $streetViewEmbed }}"
                width="100%" height="100%"
                style="border:0;display:block;"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Vista de calle — {{ $addrDisplay }}">
            </iframe>
        </div>

        {{-- Map panel (interactive iframe) --}}
        <div x-show="tab === 'map'"
             x-transition:enter="transition-opacity duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             style="aspect-ratio:16/7;position:relative;">
            <iframe
                src="{{ $mapEmbed }}"
                width="100%" height="100%"
                style="border:0;display:block;"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Mapa — {{ $addrDisplay }}">
            </iframe>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 flex items-center justify-between gap-4 border-t border-gray-100 flex-wrap">
            <p class="text-sm text-gray-500 flex items-center gap-1.5 min-w-0">
                <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span class="truncate">{{ $addrDisplay }}</span>
            </p>
            <a href="{{ $mapsLink }}" target="_blank" rel="noopener"
               class="shrink-0 inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                Abrir en Google Maps
            </a>
        </div>
    </div>

</div>
@endif
