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
    $addrDisplay = implode(', ', array_filter([
        $property->address ?? null,
        $property->colony  ?? null,
        $property->city    ?? 'Benito Juárez, CDMX',
    ]));
    $addrEncoded = $hasLocation ? urlencode($addrStr) : null;
    $mapsLink    = $hasLocation ? 'https://www.google.com/maps/search/?api=1&query=' . $addrEncoded : null;
    $uniqueId    = 'loc-' . $property->id;
@endphp

@if($hasLocation && $mapsKey)
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/>
                </svg>
                Mapa
            </button>
        </div>
    </div>

    <div class="rounded-2xl overflow-hidden border border-gray-200/80 shadow-sm bg-white">

        {{-- Street View interactivo --}}
        <div x-show="tab === 'street'" style="height:420px;position:relative;">
            <div id="{{ $uniqueId }}-sv" style="width:100%;height:100%;"></div>
            {{-- Estado inicial cargando --}}
            <div id="{{ $uniqueId }}-sv-loading" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;background:#f9fafb;color:#9ca3af;font-size:.85rem;">
                <svg style="width:32px;height:32px;animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="#e5e7eb" stroke-width="3"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="#6366f1" stroke-width="3" stroke-linecap="round"/>
                </svg>
                Cargando vista de calle…
            </div>
            {{-- Sin imagery --}}
            <div id="{{ $uniqueId }}-sv-error" style="display:none;position:absolute;inset:0;flex-direction:column;align-items:center;justify-content:center;gap:8px;background:#f9fafb;color:#9ca3af;font-size:.85rem;">
                <svg style="width:36px;height:36px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 3l18 18"/></svg>
                Vista de calle no disponible para esta dirección
            </div>
        </div>

        {{-- Mapa interactivo --}}
        <div x-show="tab === 'map'" style="height:420px;">
            <div id="{{ $uniqueId }}-map" style="width:100%;height:100%;"></div>
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

{{-- Google Maps JS (carga una sola vez por página) --}}
@once
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
<script>
window._hdvMapsQueue = window._hdvMapsQueue || [];
window._hdvMapsReady = false;

function _hdvInitMaps() {
    window._hdvMapsReady = true;
    window._hdvMapsQueue.forEach(fn => fn());
    window._hdvMapsQueue = [];
}

function _hdvWhenReady(fn) {
    if (window._hdvMapsReady) fn();
    else window._hdvMapsQueue.push(fn);
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&callback=_hdvInitMaps&libraries=geometry" async defer></script>
@endonce

<script>
_hdvWhenReady(function() {
    var addr    = {{ Js::from($addrStr) }};
    var svEl    = document.getElementById('{{ $uniqueId }}-sv');
    var mapEl   = document.getElementById('{{ $uniqueId }}-map');
    var loading = document.getElementById('{{ $uniqueId }}-sv-loading');
    var errEl   = document.getElementById('{{ $uniqueId }}-sv-error');

    if (!svEl || !mapEl) return;

    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: addr }, function(results, status) {
        if (status !== 'OK' || !results[0]) {
            loading.style.display = 'none';
            errEl.style.display = 'flex';
            return;
        }

        var latlng = results[0].geometry.location;

        // ── Mapa ──
        var map = new google.maps.Map(mapEl, {
            center: latlng,
            zoom: 17,
            mapTypeId: 'roadmap',
            disableDefaultUI: false,
            streetViewControl: false,
        });
        new google.maps.Marker({ position: latlng, map: map });

        // ── Street View ──
        var svService = new google.maps.StreetViewService();
        svService.getPanorama({ location: latlng, radius: 80, preference: 'nearest' }, function(data, svStatus) {
            loading.style.display = 'none';
            if (svStatus !== 'OK') {
                errEl.style.display = 'flex';
                return;
            }
            new google.maps.StreetViewPanorama(svEl, {
                position: latlng,
                pov: { heading: 0, pitch: 5 },
                zoom: 1,
                addressControl: false,
                fullscreenControl: true,
                motionTracking: false,
            });
        });
    });
});
</script>
@endif
