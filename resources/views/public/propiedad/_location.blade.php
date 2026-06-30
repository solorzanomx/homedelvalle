@php
    $mapsKey  = config('services.google_maps.key');
    $addrParts = array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
        'México',
    ]);
    $hasLocation = count($addrParts) >= 2;
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

    // Street View estático (API funcional, misma que captacion)
    $svStaticUrl = ($hasLocation && $mapsKey)
        ? 'https://maps.googleapis.com/maps/api/streetview?' . http_build_query([
            'size'              => '1200x525',
            'location'         => $addrStr,
            'fov'              => '90',
            'pitch'            => '5',
            'key'              => $mapsKey,
            'return_error_code'=> 'true',
          ])
        : null;

    // Enlace a Street View interactivo en Google Maps
    $svGoogleLink = $hasLocation
        ? 'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' . $addrEncoded
        : null;

    // Mapa embed — maps.google.com acepta texto sin necesitar coordenadas
    $mapEmbed = $hasLocation
        ? 'https://maps.google.com/maps?q=' . $addrEncoded . '&output=embed&z=16'
        : null;
@endphp

@if($hasLocation)
<div class="mt-10" x-data="{ tab: 'street', svError: false }" x-intersect.once="$el.classList.add('animate-fade-in-up')">

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

        {{-- ── STREET VIEW (imagen estática + botón explorar) ── --}}
        <div x-show="tab === 'street'"
             x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            {{-- Sin imagery disponible --}}
            <template x-if="svError">
                <div class="flex flex-col items-center justify-center gap-3 py-16 bg-gray-50 text-gray-400 text-sm">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 3l18 18"/></svg>
                    Vista de calle no disponible para esta dirección
                </div>
            </template>

            @if($svStaticUrl)
            <div class="relative" x-show="!svError" style="aspect-ratio:16/7;">
                {{-- Imagen estática con overlay para abrir Street View real --}}
                <img src="{{ $svStaticUrl }}"
                     alt="Vista de calle — {{ $addrDisplay }}"
                     class="w-full h-full object-cover"
                     onerror="this.closest('[x-data]').__x.$data.svError = true; this.style.display='none'">

                {{-- Overlay central: botón explorar --}}
                <div class="absolute inset-0 flex items-center justify-center bg-black/0 hover:bg-black/20 transition-colors group">
                    <a href="{{ $svGoogleLink }}" target="_blank" rel="noopener"
                       class="flex items-center gap-2 bg-white/95 hover:bg-white text-gray-800 text-sm font-semibold px-5 py-2.5 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-200 -translate-y-1 group-hover:translate-y-0">
                        <svg class="w-4 h-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                        Explorar en Google Street View
                    </a>
                </div>

                {{-- Badge bottom-left --}}
                <div class="absolute bottom-3 left-3 bg-black/55 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-full flex items-center gap-1.5 pointer-events-none">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    Google Street View
                </div>
            </div>
            @endif
        </div>

        {{-- ── MAPA interactivo (iframe embed clásico) ── --}}
        <div x-show="tab === 'map'"
             x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             style="aspect-ratio:16/7;">
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
