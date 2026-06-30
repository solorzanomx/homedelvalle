@php
    $mapsKey  = config('services.google_maps.key');
    $addrParts = array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
        'México',
    ]);
    $hasLocation = $mapsKey && count($addrParts) >= 2;
    $addrEncoded = $hasLocation ? urlencode(implode(', ', $addrParts)) : null;
    $addrDisplay = implode(', ', array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
    ]));
    $mapsLink = $hasLocation
        ? 'https://www.google.com/maps/search/?api=1&query=' . $addrEncoded
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
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

        {{-- Street View panel --}}
        <div x-show="tab === 'street'" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="relative" style="aspect-ratio:16/7;">
                <img
                    src="https://maps.googleapis.com/maps/api/streetview?{{ http_build_query(['size'=>'1200x525','location'=>implode(', ',$addrParts),'fov'=>'90','pitch'=>'5','key'=>$mapsKey,'return_error_code'=>'true']) }}"
                    alt="Vista de calle — {{ $addrDisplay }}"
                    class="w-full h-full object-cover"
                    onerror="this.closest('[x-show]').innerHTML='<div class=\'flex items-center justify-center h-full bg-gray-50 text-gray-400 text-sm gap-2 p-8 text-center\'><svg class=\'w-8 h-8 shrink-0\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z\'></path></svg><span>Vista de calle no disponible para esta dirección</span></div>'">
                {{-- Badge Street View --}}
                <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-sm text-white text-xs px-2.5 py-1 rounded-full flex items-center gap-1.5">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    Google Street View
                </div>
            </div>
        </div>

        {{-- Map panel --}}
        <div x-show="tab === 'map'" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="relative" style="aspect-ratio:16/7;">
                <img
                    src="https://maps.googleapis.com/maps/api/staticmap?{{ http_build_query(['center'=>implode(', ',$addrParts),'zoom'=>'16','size'=>'1200x525','scale'=>'2','maptype'=>'roadmap','markers'=>'color:red|'.implode(', ',$addrParts),'style'=>'feature:poi|visibility:simplified','key'=>$mapsKey]) }}"
                    alt="Mapa — {{ $addrDisplay }}"
                    class="w-full h-full object-cover">
            </div>
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
