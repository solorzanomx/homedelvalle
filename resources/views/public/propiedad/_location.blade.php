@php
    $mapsKey  = config('services.google_maps.key');
    $addrParts = array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
        'México',
    ]);
    $hasLocation = count($addrParts) >= 2 && $mapsKey;
    $addrStr     = implode(', ', $addrParts);
    $addrDisplay = implode(', ', array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
    ]));
    $addrEncoded = urlencode($addrStr);
    $mapsLink    = 'https://www.google.com/maps/search/?api=1&query=' . $addrEncoded;

    // Imagen estática Street View — API que YA funciona
    $svImg = 'https://maps.googleapis.com/maps/api/streetview?' . http_build_query([
        'size'              => '1200x525',
        'location'         => $addrStr,
        'fov'              => '90',
        'pitch'            => '5',
        'key'              => $mapsKey,
        'return_error_code'=> 'true',
    ]);

    // Mapa estático
    $mapImg = 'https://maps.googleapis.com/maps/api/staticmap?' . http_build_query([
        'center'  => $addrStr,
        'zoom'    => '16',
        'size'    => '1200x525',
        'scale'   => '2',
        'markers' => 'color:red|' . $addrStr,
        'key'     => $mapsKey,
    ]);

    $uniqueId = 'loc' . $property->id;
@endphp

@if($hasLocation)
<div class="mt-10" x-data="{ tab: 'street' }" x-intersect.once="$el.classList.add('animate-fade-in-up')">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h2 class="text-xl font-extrabold text-gray-900 tracking-tight flex items-center gap-2">
            <x-icon name="map-pin" class="w-5 h-5 text-brand-500" />
            Ubicación
        </h2>
        <div class="flex items-center gap-1 bg-gray-100 rounded-full p-1 text-sm font-medium">
            <button @click="tab = 'street'"
                    :class="tab === 'street' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 rounded-full transition-all duration-200 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                Vista de calle
            </button>
            <button @click="tab = 'map'"
                    :class="tab === 'map' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 rounded-full transition-all duration-200 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c-.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/>
                </svg>
                Mapa
            </button>
        </div>
    </div>

    <div class="rounded-2xl overflow-hidden border border-gray-200/80 shadow-sm bg-white">

        {{-- ── VISTA DE CALLE ── --}}
        <div x-show="tab === 'street'"
             x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div id="{{ $uniqueId }}-sv-wrap" style="position:relative;aspect-ratio:16/7;background:#f3f4f6;">

                {{-- Imagen estática por defecto --}}
                <img id="{{ $uniqueId }}-sv-img"
                     src="{{ $svImg }}"
                     alt="Vista de calle — {{ $addrDisplay }}"
                     style="width:100%;height:100%;object-fit:cover;display:block;"
                     onerror="document.getElementById('{{ $uniqueId }}-sv-wrap').innerHTML='<div style=\'display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:8px;color:#9ca3af;font-size:.85rem;\'><svg style=\'width:36px;height:36px\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M3 3l18 18M10.584 10.587a2 2 0 002.828 2.83M9.363 5.365A9.466 9.466 0 0112 4.5c4.638 0 8.573 3.007 9.963 7.178a9.506 9.506 0 01-4.654 5.32\'/></svg>Vista de calle no disponible</div>'">

                {{-- Overlay hover: explorar interactivo --}}
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0);transition:background .2s;"
                     onmouseenter="this.style.background='rgba(0,0,0,.18)'"
                     onmouseleave="this.style.background='rgba(0,0,0,0)'">
                    <a href="https://www.google.com/maps/@?api=1&map_action=pano&viewpoint={{ $addrEncoded }}"
                       target="_blank" rel="noopener"
                       style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.95);color:#111827;font-size:.82rem;font-weight:600;padding:.55rem 1.1rem;border-radius:999px;box-shadow:0 4px 16px rgba(0,0,0,.18);text-decoration:none;opacity:0;transition:opacity .2s,transform .2s;transform:translateY(4px);"
                       onmouseenter="this.style.opacity='1';this.style.transform='translateY(0)'"
                       onmouseleave="this.style.opacity='0';this.style.transform='translateY(4px)'">
                        <svg style="width:15px;height:15px;color:#6366f1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                        Explorar en Google Street View
                    </a>
                </div>

                {{-- Badge --}}
                <div style="position:absolute;bottom:10px;left:10px;background:rgba(0,0,0,.55);backdrop-filter:blur(4px);color:#fff;font-size:.7rem;padding:.25rem .65rem;border-radius:999px;display:flex;align-items:center;gap:5px;pointer-events:none;">
                    <svg style="width:11px;height:11px;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    Google Street View
                </div>
            </div>
        </div>

        {{-- ── MAPA estático + link interactivo ── --}}
        <div x-show="tab === 'map'"
             x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div style="position:relative;aspect-ratio:16/7;">
                <img src="{{ $mapImg }}"
                     alt="Mapa — {{ $addrDisplay }}"
                     style="width:100%;height:100%;object-fit:cover;display:block;">
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0);transition:background .2s;"
                     onmouseenter="this.style.background='rgba(0,0,0,.12)'"
                     onmouseleave="this.style.background='rgba(0,0,0,0)'">
                    <a href="{{ $mapsLink }}" target="_blank" rel="noopener"
                       style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.95);color:#111827;font-size:.82rem;font-weight:600;padding:.55rem 1.1rem;border-radius:999px;box-shadow:0 4px 16px rgba(0,0,0,.18);text-decoration:none;opacity:0;transition:opacity .2s,transform .2s;transform:translateY(4px);"
                       onmouseenter="this.style.opacity='1';this.style.transform='translateY(0)'"
                       onmouseleave="this.style.opacity='0';this.style.transform='translateY(4px)'">
                        <svg style="width:15px;height:15px;color:#6366f1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                        </svg>
                        Abrir mapa interactivo
                    </a>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-4 flex items-center justify-between gap-4 border-t border-gray-100 flex-wrap">
            <p class="text-sm text-gray-500 flex items-center gap-1.5 min-w-0">
                <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                </svg>
                <span class="truncate">{{ $addrDisplay }}</span>
            </p>
            <a href="{{ $mapsLink }}" target="_blank" rel="noopener"
               class="shrink-0 inline-flex items-center gap-1.5 text-sm font-medium text-brand-600 hover:text-brand-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
                Abrir en Google Maps
            </a>
        </div>
    </div>
</div>
@endif
