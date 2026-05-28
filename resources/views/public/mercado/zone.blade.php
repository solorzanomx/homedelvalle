@extends('layouts.public')

@section('meta')
<title>Precios inmuebles en {{ $zone->name }}, Benito Juárez {{ now()->format('Y') }} | Home del Valle</title>
<meta name="description" content="Precios por m² en {{ $zone->name }}: departamentos
  @if(!empty($saleSnaps['apartment']['mid'])) desde ${{ number_format($saleSnaps['apartment']['mid']->price_m2_low) }}/m² @endif.
  Datos actualizados {{ now()->isoFormat('MMMM Y') }}. Venta y renta.">
<link rel="canonical" href="{{ url('/mercado/' . $zone->slug) }}">

{{-- FAQ Schema.org para rich results en Google --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    @if(!empty($saleSnaps['apartment']['mid']))
    {
      "@type": "Question",
      "name": "¿Cuánto cuesta un departamento en {{ $zone->name }}, Benito Juárez?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "El precio promedio de un departamento seminuevo (10-25 años) en {{ $zone->name }} es de ${{ number_format($saleSnaps['apartment']['mid']->price_m2_avg) }} por m² de construcción. El rango de mercado va de ${{ number_format($saleSnaps['apartment']['mid']->price_m2_low) }} a ${{ number_format($saleSnaps['apartment']['mid']->price_m2_high) }}/m². Un departamento nuevo puede costar @if(!empty($saleSnaps['apartment']['new'])) ${{ number_format($saleSnaps['apartment']['new']->price_m2_avg) }}/m² @else más @endif. Fuente: Observatorio de Precios HDV, {{ \Carbon\Carbon::parse($saleMeta['last_period'])->translatedFormat('F Y') }}."
      }
    },
    @endif
    {
      "@type": "Question",
      "name": "¿Qué colonias forman la zona {{ $zone->name }} en Benito Juárez?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "La zona {{ $zone->name }} incluye las colonias: {{ $colonias->pluck('name')->join(', ') }}. Todas se ubican en la alcaldía Benito Juárez, Ciudad de México."
      }
    },
    @if(!empty($rentSnaps['apartment']['mid']))
    {
      "@type": "Question",
      "name": "¿Cuánto cuesta rentar un departamento en {{ $zone->name }}?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "La renta por m² de un departamento seminuevo en {{ $zone->name }} es de aproximadamente ${{ number_format($rentSnaps['apartment']['mid']->price_m2_avg) }}/m²/mes. Fuente: Observatorio de Precios HDV, {{ \Carbon\Carbon::parse($rentMeta['last_period'])->translatedFormat('F Y') }}."
      }
    },
    @endif
    {
      "@type": "Question",
      "name": "¿Son confiables los precios del Observatorio de Home del Valle?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Los precios se calculan con análisis estadístico de anuncios publicados en portales inmobiliarios (Inmuebles24, Lamudi, Propiedades.com, entre otros). {{ $saleMeta['total_listings'] > 0 ? 'Los datos de ' . $zone->name . ' se basan en ' . $saleMeta['total_listings'] . ' listings analizados.' : '' }} Son referencias de mercado, no avalúos formales. Para una estimación personalizada de tu inmueble, solicita una opinión de valor gratuita."
      }
    }
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Observatorio", "item": "{{ url('/mercado') }}" },
    { "@type": "ListItem", "position": 2, "name": "{{ $zone->name }}", "item": "{{ url('/mercado/' . $zone->slug) }}" }
  ]
}
</script>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════════════
     BREADCRUMB
════════════════════════════════════════════════════════ --}}
<div style="background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:.6rem 1.5rem;font-size:.78rem;color:#6b7280;">
    <div style="max-width:960px;margin:0 auto;">
        <a href="{{ route('mercado.index') }}" style="color:#2563eb;">Observatorio</a>
        &nbsp;›&nbsp; {{ $zone->name }}
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════ --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:3rem 1.5rem 2.5rem;">
    <div style="max-width:960px;margin:0 auto;">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.4);margin-bottom:.6rem;">
            Benito Juárez · CDMX · {{ $zone->colonias->count() }} colonias
        </div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1.5rem;">
            <div style="flex:1;min-width:260px;">
                <h1 style="font-size:clamp(1.5rem,3.5vw,2.2rem);font-weight:700;margin-bottom:.6rem;line-height:1.2;">
                    Precios de inmuebles en<br>{{ $zone->name }}
                </h1>
                @if($zone->short_description)
                <p style="font-size:.9rem;color:rgba(255,255,255,.65);max-width:500px;line-height:1.6;margin-bottom:1.25rem;">
                    {{ $zone->short_description }}
                </p>
                @endif
                <a href="{{ route('mercado.opinion') }}"
                   style="display:inline-flex;align-items:center;gap:.5rem;background:#2563eb;color:#fff;padding:.65rem 1.4rem;border-radius:8px;font-weight:600;font-size:.88rem;text-decoration:none;">
                    ¿Cuánto vale tu inmueble aquí? →
                </a>
            </div>

            {{-- Price badge --}}
            @if($heroPriceSale)
            <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.14);border-radius:12px;padding:1.25rem 1.75rem;text-align:center;min-width:180px;">
                <div style="font-size:.63rem;text-transform:uppercase;letter-spacing:1.2px;color:rgba(255,255,255,.4);margin-bottom:.4rem;">Precio m² promedio</div>
                <div style="font-size:2rem;font-weight:700;line-height:1;">${{ number_format((int) $heroPriceSale) }}</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.45);margin-top:.35rem;">Depto seminuevo · Venta</div>
                @if($saleMeta['total_listings'] > 0)
                <div style="font-size:.65rem;color:rgba(255,255,255,.3);margin-top:.3rem;">
                    Basado en {{ $saleMeta['total_listings'] }} listings
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════
     NAV ZONAS
════════════════════════════════════════════════════════ --}}
<div style="background:#fff;border-bottom:2px solid #e5e7eb;overflow-x:auto;-webkit-overflow-scrolling:touch;">
    <div style="max-width:960px;margin:0 auto;display:flex;gap:0;padding:0 1rem;">
        @foreach($allZones as $z)
        <a href="{{ route('mercado.zone', $z->slug) }}"
           style="display:inline-block;padding:.65rem .85rem;font-size:.78rem;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid {{ $z->id === $zone->id ? '#2563eb' : 'transparent' }};margin-bottom:-2px;color:{{ $z->id === $zone->id ? '#2563eb' : '#6b7280' }};">
            {{ $z->name }}
        </a>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     TABLA DE PRECIOS — VENTA + RENTA con tabs Alpine
════════════════════════════════════════════════════════ --}}
<section style="max-width:960px;margin:0 auto;padding:2.5rem 1.5rem 1.5rem;">

    <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
        <div>
            <h2 style="font-size:1.15rem;font-weight:700;margin-bottom:.25rem;">Precios de referencia · {{ $zone->name }}</h2>
            <p style="font-size:.82rem;color:#9ca3af;">
                Por m² de construcción · Actualizado
                @if($saleMeta['last_period']) {{ \Carbon\Carbon::parse($saleMeta['last_period'])->translatedFormat('F Y') }} @else {{ now()->isoFormat('MMMM YYYY') }} @endif
                · Referencias de mercado, no avalúos
            </p>
        </div>
        @if($saleMeta['total_listings'] > 0)
        <div style="display:flex;align-items:center;gap:.4rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.35rem .75rem;font-size:.72rem;color:#166534;">
            <span style="width:7px;height:7px;border-radius:50%;background:{{ match($saleMeta['confidence'] ?? '') {'high'=>'#16a34a','medium'=>'#d97706',default=>'#94a3b8'} }};flex-shrink:0;display:inline-block;"></span>
            Basado en {{ $saleMeta['total_listings'] }} listings ·
            {{ match($saleMeta['confidence'] ?? '') {'high'=>'Alta confianza','medium'=>'Confianza media',default=>'Confianza estimada'} }}
        </div>
        @endif
    </div>

    @php $hasSale = !empty($saleSnaps); $hasRent = !empty($rentSnaps); @endphp

    <div x-data="{ tab: 'sale' }">
        {{-- Tabs --}}
        @if($hasSale || $hasRent)
        <div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb;margin-bottom:1.5rem;">
            @if($hasSale)
            <button @click="tab='sale'"
                    :style="tab==='sale' ? 'color:#1d4ed8;border-bottom-color:#1d4ed8;' : 'color:#6b7280;border-bottom-color:transparent;'"
                    style="padding:.55rem 1.1rem;font-size:.88rem;font-weight:600;background:none;border:none;border-bottom:2px solid;margin-bottom:-2px;cursor:pointer;transition:color .15s;">
                🏠 Precios de Venta
            </button>
            @endif
            @if($hasRent)
            <button @click="tab='rent'"
                    :style="tab==='rent' ? 'color:#7c3aed;border-bottom-color:#7c3aed;' : 'color:#6b7280;border-bottom-color:transparent;'"
                    style="padding:.55rem 1.1rem;font-size:.88rem;font-weight:600;background:none;border:none;border-bottom:2px solid;margin-bottom:-2px;cursor:pointer;transition:color .15s;">
                🔑 Precios de Renta
            </button>
            @endif
        </div>
        @endif

        {{-- ── TAB VENTA ──────────────────────────────────────── --}}
        @if($hasSale)
        <div x-show="tab==='sale'" x-transition>
            @include('public.mercado._price-table', [
                'snaps'     => $saleSnaps,
                'unitLabel' => '/m²',
                'accentColor'=> '#1d4ed8',
                'accentBg'   => '#eff6ff',
            ])
        </div>
        @endif

        {{-- ── TAB RENTA ──────────────────────────────────────── --}}
        @if($hasRent)
        <div x-show="tab==='rent'" x-transition style="display:none;">
            @if($rentMeta['total_listings'] > 0)
            <div style="display:flex;align-items:center;gap:.4rem;background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:.35rem .75rem;font-size:.72rem;color:#6d28d9;margin-bottom:1rem;width:fit-content;">
                <span style="width:7px;height:7px;border-radius:50%;background:{{ match($rentMeta['confidence'] ?? '') {'high'=>'#16a34a','medium'=>'#d97706',default=>'#94a3b8'} }};flex-shrink:0;display:inline-block;"></span>
                Basado en {{ $rentMeta['total_listings'] }} listings ·
                {{ match($rentMeta['confidence'] ?? '') {'high'=>'Alta confianza','medium'=>'Confianza media',default=>'Confianza estimada'} }}
            </div>
            @endif
            @include('public.mercado._price-table', [
                'snaps'     => $rentSnaps,
                'unitLabel' => '/m²/mes',
                'accentColor'=> '#7c3aed',
                'accentBg'   => '#faf5ff',
                'showOffice' => true,
            ])
        </div>
        @endif

        @if(!$hasSale && !$hasRent)
        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:3rem;text-align:center;color:#9ca3af;">
            <div style="font-size:1.5rem;margin-bottom:.75rem;">📊</div>
            <p>Aún no tenemos datos de precios para esta zona.</p>
            <p style="font-size:.82rem;margin-top:.5rem;">Actualizamos mensualmente. Regresa pronto.</p>
        </div>
        @endif
    </div>

    {{-- Nota metodológica --}}
    <div style="margin-top:1.25rem;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:.85rem 1.1rem;font-size:.78rem;color:#92400e;line-height:1.55;">
        <strong>¿Qué son estos precios?</strong>
        Son referencias de mercado calculadas a partir de anuncios publicados en portales inmobiliarios (Inmuebles24, Lamudi, Propiedades.com, entre otros).
        El precio real de tu inmueble depende de sus características específicas: superficie exacta, estado de conservación, piso, estacionamiento y amenidades.
        <a href="{{ route('mercado.opinion') }}" style="color:#92400e;font-weight:600;">Solicita una opinión de valor personalizada →</a>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════
     CTA 1 — Intermedio
════════════════════════════════════════════════════════ --}}
<section style="max-width:960px;margin:0 auto;padding:0 1.5rem 2.5rem;">
    <div style="background:linear-gradient(135deg,#1e3a5f,#0f172a);border-radius:14px;padding:2rem 2.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.25rem;">
        <div>
            <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.45);margin-bottom:.4rem;">
                Propietarios en {{ $zone->name }}
            </div>
            <h2 style="font-size:1.15rem;font-weight:700;color:#fff;margin-bottom:.35rem;">
                ¿Cuánto vale tu inmueble en {{ $zone->name }}?
            </h2>
            <p style="font-size:.84rem;color:rgba(255,255,255,.6);max-width:440px;">
                Analizamos los datos del mercado y las características específicas de tu inmueble para darte un precio de salida óptimo — sin compromiso.
            </p>
        </div>
        <div style="display:flex;flex-direction:column;gap:.6rem;flex-shrink:0;">
            <a href="{{ route('mercado.opinion') }}"
               style="display:inline-block;background:#2563eb;color:#fff;padding:.65rem 1.5rem;border-radius:8px;font-weight:600;font-size:.88rem;text-decoration:none;text-align:center;white-space:nowrap;">
                Solicitar valuación gratuita →
            </a>
            <a href="{{ route('contacto') }}"
               style="display:inline-block;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);color:rgba(255,255,255,.8);padding:.55rem 1.5rem;border-radius:8px;font-size:.84rem;text-decoration:none;text-align:center;white-space:nowrap;">
                Hablar con un agente
            </a>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════
     COLONIAS DE LA ZONA
════════════════════════════════════════════════════════ --}}
<section style="max-width:960px;margin:0 auto;padding:0 1.5rem 3rem;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:.3rem;">Colonias en {{ $zone->name }}</h2>
    <p style="font-size:.82rem;color:#9ca3af;margin-bottom:1rem;">
        Los precios anteriores son una referencia para toda la zona. Cada colonia puede tener variaciones por su ubicación específica y oferta disponible.
    </p>
    <div style="display:flex;flex-wrap:wrap;gap:.6rem;">
        @foreach($colonias as $colonia)
        <a href="{{ route('mercado.colonia', [$zone->slug, $colonia->slug]) }}"
           style="display:inline-flex;align-items:center;gap:.4rem;background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:.4rem .9rem;font-size:.82rem;color:#374151;text-decoration:none;transition:all .15s;"
           onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe';this.style.color='#1d4ed8'"
           onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb';this.style.color='#374151'">
            {{ $colonia->name }}
            @if($colonia->cp)<span style="font-size:.68rem;color:#9ca3af;"> · CP {{ $colonia->cp }}</span>@endif
        </a>
        @endforeach
    </div>
</section>

{{-- ══════════════════════════════════════════════════════
     FAQ (SEO + Rich Results)
════════════════════════════════════════════════════════ --}}
<section style="max-width:960px;margin:0 auto;padding:0 1.5rem 3.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">Preguntas frecuentes sobre precios en {{ $zone->name }}</h2>

    <div x-data="{ open: null }">

        @php $faqs = []; @endphp

        @if(!empty($saleSnaps['apartment']['mid']))
        @php $faqs[] = [
            'q' => '¿Cuánto cuesta un departamento en ' . $zone->name . '?',
            'a' => 'El precio promedio de un departamento seminuevo (10–25 años) en ' . $zone->name . ' es de $' . number_format($saleSnaps['apartment']['mid']->price_m2_avg) . ' por m² de construcción. ' .
                   'El rango de mercado va de $' . number_format($saleSnaps['apartment']['mid']->price_m2_low) . ' a $' . number_format($saleSnaps['apartment']['mid']->price_m2_high) . '/m². ' .
                   (!empty($saleSnaps['apartment']['new']) ? 'Un departamento nuevo puede alcanzar $' . number_format($saleSnaps['apartment']['new']->price_m2_avg) . '/m². ' : '') .
                   'Un departamento de 80 m² costaría aproximadamente $' . number_format($saleSnaps['apartment']['mid']->price_m2_avg * 80) . ' MXN como referencia de mercado.',
        ]; @endphp
        @endif

        @if(!empty($rentSnaps['apartment']['mid']))
        @php $faqs[] = [
            'q' => '¿Cuánto cuesta rentar un departamento en ' . $zone->name . '?',
            'a' => 'La renta de un departamento seminuevo en ' . $zone->name . ' tiene un precio de mercado de aproximadamente $' . number_format($rentSnaps['apartment']['mid']->price_m2_avg) . '/m²/mes. ' .
                   'Para un departamento de 80 m², eso equivale a una renta mensual de aproximadamente $' . number_format($rentSnaps['apartment']['mid']->price_m2_avg * 80) . ' MXN.',
        ]; @endphp
        @endif

        @php $faqs[] = [
            'q'  => '¿Qué colonias conforman la zona ' . $zone->name . '?',
            'a'  => 'La zona ' . $zone->name . ' incluye las colonias: ' . $colonias->pluck('name')->join(', ') . '. Todas se ubican dentro de la alcaldía Benito Juárez en la Ciudad de México.',
        ]; @endphp

        @php $faqs[] = [
            'q' => '¿Cuál es la diferencia entre un inmueble nuevo, seminuevo y antiguo?',
            'a' => 'En el mercado inmobiliario de Benito Juárez usamos tres segmentos: ' .
                   'Nuevo (0–10 años de construcción): suele tener amenidades modernas, acabados actuales y menor costo de mantenimiento a corto plazo. ' .
                   'Seminuevo (10–25 años): es el segmento más abundante en BJ con buena relación precio-valor. ' .
                   'Antiguo (más de 25 años): puede ofrecer oportunidades de precio con mayor espacio o ubicaciones privilegiadas, pero puede requerir inversión en renovación.',
        ]; @endphp

        @php $faqs[] = [
            'q' => '¿Cómo calcula Home del Valle estos precios?',
            'a' => 'Los precios del Observatorio se obtienen mediante análisis estadístico de anuncios publicados en portales inmobiliarios (Inmuebles24, Lamudi, Propiedades.com, Metros Cúbicos, entre otros). ' .
                   'Eliminamos valores atípicos, clasificamos por tipo y antigüedad del inmueble, y calculamos medianas y rangos por zona. ' .
                   ($saleMeta['total_listings'] > 0 ? 'Los datos de ' . $zone->name . ' se basan en ' . $saleMeta['total_listings'] . ' listings analizados. ' : '') .
                   'Se actualizan mensualmente. Son referencias de mercado, no avalúos formales.',
        ]; @endphp

        @foreach($faqs as $i => $faq)
        <div style="border:1px solid #e5e7eb;border-radius:10px;margin-bottom:.6rem;overflow:hidden;">
            <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                    style="width:100%;text-align:left;padding:.9rem 1.1rem;background:none;border:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:.75rem;">
                <span style="font-size:.88rem;font-weight:600;color:#111827;">{{ $faq['q'] }}</span>
                <span x-text="open === {{ $i }} ? '−' : '+'"
                      style="font-size:1.1rem;color:#6b7280;flex-shrink:0;"></span>
            </button>
            <div x-show="open === {{ $i }}" x-transition
                 style="display:none;padding:.75rem 1.1rem 1rem;background:#fafafa;border-top:1px solid #f0f2f5;font-size:.84rem;color:#4b5563;line-height:1.65;">
                {{ $faq['a'] }}
            </div>
        </div>
        @endforeach

    </div>
</section>

{{-- ══════════════════════════════════════════════════════
     CTA FINAL
════════════════════════════════════════════════════════ --}}
<section style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:3.5rem 1.5rem;text-align:center;">
    <div style="max-width:600px;margin:0 auto;">
        <div style="font-size:1.75rem;margin-bottom:.75rem;">🏡</div>
        <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:.6rem;">
            ¿Tienes un inmueble en {{ $zone->name }}?
        </h2>
        <p style="color:#6b7280;font-size:.9rem;line-height:1.65;margin-bottom:1.75rem;">
            En Home del Valle somos especialistas en Benito Juárez desde hace más de 30 años.
            Trabajamos con <strong>máximo 20 inmuebles activos</strong> para dar atención real a cada caso.
            Sin anticipos, sin sorpresas.
        </p>
        <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:.75rem;">
            <a href="{{ route('mercado.opinion') }}"
               style="display:inline-block;background:#2563eb;color:#fff;padding:.75rem 1.75rem;border-radius:8px;font-weight:600;font-size:.9rem;text-decoration:none;">
                Solicitar opinión de valor →
            </a>
            <a href="{{ route('landing.renta-tu-propiedad') }}"
               style="display:inline-block;background:#fff;border:1.5px solid #e5e7eb;color:#374151;padding:.75rem 1.75rem;border-radius:8px;font-weight:600;font-size:.9rem;text-decoration:none;">
                Rentar mi propiedad
            </a>
        </div>
        <p style="font-size:.75rem;color:#9ca3af;margin-top:1.25rem;">
            Más de 30 años en Benito Juárez · Sin anticipos · Comisión solo al cierre
        </p>
    </div>
</section>

@endsection
