{{-- Barra inferior fija (solo celular) del modo inquilino: 4 destinos al alcance del pulgar. Espera: $rental, $docsBadge --}}
@php
    $wa = preg_replace('/\D/', '', $rental->broker?->whatsapp ?: $rental->broker?->phone ?: '');
    if ($wa && strlen($wa) === 10) { $wa = '52' . $wa; }
    $items = [
        ['route' => route('portal.journey'), 'on' => request()->routeIs('portal.journey') || request()->routeIs('portal.dashboard'), 'icon' => '🧭', 'label' => 'Mi camino', 'badge' => 0],
        ['route' => route('portal.documents.index'), 'on' => request()->routeIs('portal.documents.*'), 'icon' => '📄', 'label' => 'Documentos', 'badge' => $docsBadge],
        ! ($isObligado ?? false) ? ['route' => route('portal.rentals.show', $rental->id), 'on' => request()->routeIs('portal.rentals.show'), 'icon' => '🏠', 'label' => 'Mi renta', 'badge' => 0] : null,
        ['route' => $wa ? 'https://wa.me/' . $wa . '?text=' . rawurlencode('Hola, soy ' . Auth::user()->name . '. Tengo una duda sobre mi renta.') : route('portal.account'), 'on' => ! $wa && request()->routeIs('portal.account'), 'icon' => $wa ? '💬' : '⚙️', 'label' => $wa ? 'Asesor' : 'Cuenta', 'badge' => 0, 'external' => (bool) $wa],
    ];
    $items = array_values(array_filter($items));
@endphp
<style>
.tnav { display:none; }
@media (max-width: 768px) {
    .tnav { display:flex; position:fixed; left:0; right:0; bottom:0; z-index:900; background:#fff; border-top:1px solid #e2e8f0; box-shadow:0 -4px 20px rgba(15,23,42,.08); padding-bottom:env(safe-area-inset-bottom); }
    .tnav a { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2px; min-height:58px; text-decoration:none; color:#64748b; font-size:.68rem; font-weight:700; position:relative; }
    .tnav a .ic { font-size:1.35rem; line-height:1; }
    .tnav a.on { color:#1D4ED8; }
    .tnav a.on::before { content:''; position:absolute; top:0; left:25%; right:25%; height:3px; border-radius:0 0 3px 3px; background:#1D4ED8; }
    .tnav .bd { position:absolute; top:6px; left:calc(50% + 8px); min-width:18px; height:18px; padding:0 4px; border-radius:9px; background:#f59e0b; color:#fff; font-size:.65rem; display:flex; align-items:center; justify-content:center; }
    .portal-content { padding-bottom: calc(72px + env(safe-area-inset-bottom)) !important; }
}
</style>
<nav class="tnav" aria-label="Navegación principal">
    @foreach($items as $it)
        <a href="{{ $it['route'] }}" class="{{ $it['on'] ? 'on' : '' }}" @if(! empty($it['external'])) target="_blank" rel="noopener" @endif>
            <span class="ic">{{ $it['icon'] }}</span>{{ $it['label'] }}
            @if($it['badge'] > 0)<span class="bd">{{ $it['badge'] }}</span>@endif
        </a>
    @endforeach
</nav>
