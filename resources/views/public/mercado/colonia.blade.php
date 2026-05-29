@extends('layouts.public')

@section('meta')
<title>Precios en {{ $colonia->name }}, {{ $zone->name }} {{ now()->format('Y') }} | Home del Valle</title>
<meta name="description" content="Precio por m² en {{ $colonia->name }}, {{ $zone->name }}: departamentos
  @if(!empty($saleSnaps['apartment']['mid'])) ~${{ number_format($saleSnaps['apartment']['mid']->price_m2_avg) }}/m² @endif.
  Referencias actualizadas {{ now()->isoFormat('MMMM Y') }}. Home del Valle.">
<link rel="canonical" href="{{ url('/mercado/' . $zone->slug . '/' . $colonia->slug) }}">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Observatorio", "item": "{{ url('/mercado') }}" },
    { "@type": "ListItem", "position": 2, "name": "{{ $zone->name }}", "item": "{{ url('/mercado/' . $zone->slug) }}" },
    { "@type": "ListItem", "position": 3, "name": "{{ $colonia->name }}", "item": "{{ url('/mercado/' . $zone->slug . '/' . $colonia->slug) }}" }
  ]
}
</script>
@endsection

@section('content')

{{-- BREADCRUMB --}}
<div style="background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:.6rem 1.5rem;font-size:.78rem;color:#6b7280;">
    <div style="max-width:960px;margin:0 auto;">
        <a href="{{ route('mercado.index') }}" style="color:#2563eb;">Observatorio</a>
        &nbsp;›&nbsp;
        <a href="{{ route('mercado.zone', $zone->slug) }}" style="color:#2563eb;">{{ $zone->name }}</a>
        &nbsp;›&nbsp; {{ $colonia->name }}
    </div>
</div>

{{-- HERO --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:2.5rem 1.5rem 2rem;">
    <div style="max-width:960px;margin:0 auto;">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.4);margin-bottom:.6rem;">
            Benito Juárez · {{ $zone->name }}@if($colonia->cp) · CP {{ $colonia->cp }}@endif
        </div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1.25rem;">
            <div style="flex:1;min-width:240px;">
                <h1 style="font-size:clamp(1.3rem,3vw,1.9rem);font-weight:700;margin-bottom:.5rem;">
                    Colonia {{ $colonia->name }}
                </h1>
                @if($colonia->short_description)
                <p style="font-size:.88rem;color:rgba(255,255,255,.6);max-width:480px;line-height:1.6;">
                    {{ $colonia->short_description }}
                </p>
                @else
                <p style="font-size:.88rem;color:rgba(255,255,255,.6);max-width:480px;line-height:1.6;">
                    Parte de la zona {{ $zone->name }}, alcaldía Benito Juárez, CDMX.
                </p>
                @endif
            </div>
            @if($heroPriceSale)
            <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:10px;padding:1rem 1.5rem;text-align:center;">
                <div style="font-size:.62rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.4);margin-bottom:.35rem;">Precio m² referencia</div>
                <div style="font-size:1.75rem;font-weight:700;line-height:1;">${{ number_format((int) $heroPriceSale) }}</div>
                <div style="font-size:.68rem;color:rgba(255,255,255,.4);margin-top:.3rem;">Depto seminuevo · Zona {{ $zone->name }}</div>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- NAV ZONAS --}}
<div style="background:#fff;border-bottom:2px solid #e5e7eb;overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <div style="max-width:960px;margin:0 auto;display:flex;gap:0;padding:0 1rem;">
        @foreach($allZones as $z)
        <a href="{{ route('mercado.zone', $z->slug) }}"
           style="display:inline-block;padding:.6rem .8rem;font-size:.75rem;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid {{ $z->id === $zone->id ? '#2563eb' : 'transparent' }};margin-bottom:-2px;color:{{ $z->id === $zone->id ? '#2563eb' : '#6b7280' }};">
            {{ $z->name }}
        </a>
        @endforeach
    </div>
</div>

{{-- PRECIOS --}}
<section style="max-width:960px;margin:0 auto;padding:2.5rem 1.5rem 1.5rem;">

    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
        <div>
            <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.25rem;">Precios de referencia · {{ $colonia->name }}</h2>
            <p style="font-size:.82rem;color:#9ca3af;">
                Precio por m² · Zona {{ $zone->name }} ·
                @if($saleMeta['last_period']) Actualizado {{ \Carbon\Carbon::parse($saleMeta['last_period'])->translatedFormat('F Y') }} @else {{ now()->isoFormat('MMMM YYYY') }} @endif
            </p>
        </div>
    </div>

    {{-- Aviso: datos de zona --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:.6rem 1rem;font-size:.78rem;color:#1e40af;margin-bottom:1.25rem;display:flex;align-items:flex-start;gap:.5rem;">
        <span style="flex-shrink:0;margin-top:.05rem;">ℹ️</span>
        <span>
            Los precios mostrados son la referencia de la <strong>Zona {{ $zone->name }}</strong>, que incluye {{ $zone->colonias->count() }} colonias con mercado similar.
            Las variaciones exactas por colonia pueden consultarse con nuestros agentes.
            <a href="{{ route('mercado.zone', $zone->slug) }}" style="color:#1d4ed8;font-weight:600;">Ver datos completos de la zona →</a>
        </span>
    </div>

    @php
        $hasSale = !empty($saleSnaps);
        $hasRent = !empty($rentSnaps);
        $defaultTab = $hasSale ? 'sale' : 'rent';
    @endphp

    <div x-data="{ tab: '{{ $defaultTab }}' }">
        @if($hasSale || $hasRent)
        <div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:1.5rem;">
            @if($hasSale)
            <button @click="tab='sale'"
                    :style="tab==='sale' ? 'color:#1d4ed8;border-bottom-color:#1d4ed8;' : 'color:#6b7280;border-bottom-color:transparent;'"
                    style="padding:.55rem 1rem;font-size:.85rem;font-weight:600;background:none;border:none;border-bottom:2px solid;margin-bottom:-2px;cursor:pointer;">
                🏠 Venta
            </button>
            @endif
            @if($hasRent)
            <button @click="tab='rent'"
                    :style="tab==='rent' ? 'color:#7c3aed;border-bottom-color:#7c3aed;' : 'color:#6b7280;border-bottom-color:transparent;'"
                    style="padding:.55rem 1rem;font-size:.85rem;font-weight:600;background:none;border:none;border-bottom:2px solid;margin-bottom:-2px;cursor:pointer;">
                🔑 Renta
            </button>
            @endif
        </div>
        @endif

        @if($hasSale)
        <div x-show="tab==='sale'" x-transition>
            @include('public.mercado._price-table', [
                'snaps'      => $saleSnaps,
                'unitLabel'  => '/m²',
                'accentColor'=> '#1d4ed8',
                'accentBg'   => '#eff6ff',
            ])
        </div>
        @endif

        @if($hasRent)
        <div x-show="tab==='rent'" x-transition style="display:none;">
            @include('public.mercado._price-table', [
                'snaps'       => $rentSnaps,
                'unitLabel'   => '/m²/mes',
                'accentColor' => '#7c3aed',
                'accentBg'    => '#faf5ff',
                'showOffice'  => true,
                'showMonthly' => true,
            ])
        </div>
        @endif

        @if(!$hasSale && !$hasRent)
        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:2.5rem;text-align:center;color:#9ca3af;font-size:.85rem;">
            Aún no tenemos datos de precios para esta zona. Actualizamos mensualmente.
        </div>
        @endif
    </div>

    {{-- Nota metodológica --}}
    <div style="margin-top:1.25rem;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.85rem 1.1rem;font-size:.78rem;color:#92400e;line-height:1.55;">
        <strong>Nota:</strong> Estas referencias se calculan a partir de anuncios publicados en portales inmobiliarios.
        El precio de tu inmueble específico depende de m², estado, piso, estacionamiento y amenidades.
        <a href="{{ route('mercado.opinion') }}" style="color:#92400e;font-weight:600;">Solicita una opinión de valor personalizada →</a>
    </div>
</section>

{{-- COLONIAS VECINAS --}}
@if($siblings->count() > 0)
<section style="max-width:960px;margin:0 auto;padding:0 1.5rem 2.5rem;">
    <h2 style="font-size:.95rem;font-weight:700;margin-bottom:.85rem;color:#374151;">
        Otras colonias en {{ $zone->name }}
    </h2>
    <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
        @foreach($siblings as $sib)
        <a href="{{ route('mercado.colonia', [$zone->slug, $sib->slug]) }}"
           style="display:inline-block;background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:.35rem .85rem;font-size:.8rem;color:#374151;text-decoration:none;transition:all .15s;"
           onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe';this.style.color='#1d4ed8'"
           onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb';this.style.color='#374151'">
            {{ $sib->name }}
        </a>
        @endforeach
        <a href="{{ route('mercado.zone', $zone->slug) }}"
           style="display:inline-block;background:#eff6ff;border:1px solid #bfdbfe;border-radius:20px;padding:.35rem .85rem;font-size:.8rem;color:#2563eb;font-weight:600;text-decoration:none;">
            Ver toda la zona →
        </a>
    </div>
</section>
@endif

{{-- CTA FINAL --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:3rem 1.5rem;text-align:center;">
    <div style="max-width:580px;margin:0 auto;">
        <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.5rem;">
            ¿Tienes un inmueble en {{ $colonia->name }}?
        </h2>
        <p style="font-size:.88rem;color:rgba(255,255,255,.6);margin-bottom:1.5rem;line-height:1.65;">
            Preparamos una opinión de valor personalizada con los factores específicos de tu inmueble.
            Sin costo, sin compromiso.
        </p>
        <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:.75rem;">
            <a href="{{ route('mercado.opinion') }}?colonia={{ $colonia->id }}"
               style="display:inline-block;background:#2563eb;color:#fff;padding:.7rem 1.6rem;border-radius:8px;font-weight:600;font-size:.9rem;text-decoration:none;">
                Solicitar opinión de valor →
            </a>
            <a href="{{ route('contacto') }}"
               style="display:inline-block;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.85);padding:.7rem 1.6rem;border-radius:8px;font-size:.88rem;text-decoration:none;">
                Hablar con un agente
            </a>
        </div>
    </div>
</section>

@endsection
