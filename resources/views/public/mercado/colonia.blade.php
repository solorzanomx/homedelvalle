@extends('layouts.public')

@section('meta')
<title>Precios en {{ $colonia->name }}, {{ $zone->name }} | Observatorio Home del Valle</title>
<meta name="description" content="Precio por m² en {{ $colonia->name }}: departamentos desde ${{ number_format($snapshots->flatten()->min('price_m2_low') ?? 0) }}/m². Datos actualizados de mercado.">
<link rel="canonical" href="{{ url('/mercado/' . $zone->slug . '/' . $colonia->slug) }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div style="background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:.6rem 1.5rem;font-size:.78rem;color:#6b7280;">
    <div style="max-width:960px;margin:0 auto;">
        <a href="{{ route('mercado.index') }}" style="color:#2563eb;">Observatorio</a>
        &nbsp;›&nbsp;
        <a href="{{ route('mercado.zone', $zone->slug) }}" style="color:#2563eb;">{{ $zone->name }}</a>
        &nbsp;›&nbsp; {{ $colonia->name }}
    </div>
</div>

{{-- Hero --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:2.5rem 1.5rem 2rem;">
    <div style="max-width:960px;margin:0 auto;">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.45);margin-bottom:.5rem;">
            Benito Juárez · {{ $zone->name }}
            @if($colonia->cp) · CP {{ $colonia->cp }} @endif
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <div>
                <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:700;margin-bottom:.4rem;">
                    Colonia {{ $colonia->name }}
                </h1>
                @if($colonia->short_description)
                <p style="font-size:.88rem;color:rgba(255,255,255,.65);max-width:520px;">
                    {{ $colonia->short_description }}
                </p>
                @endif
            </div>
            @php $aptMidHero = $snapshots->get('apartment_mid'); @endphp
            @if($aptMidHero)
            <div style="text-align:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:1rem 1.5rem;">
                <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.55;margin-bottom:.3rem;">Precio m² promedio</div>
                <div style="font-size:1.6rem;font-weight:700;">${{ number_format($aptMidHero->price_m2_avg) }}</div>
                <div style="font-size:.68rem;opacity:.5;margin-top:.2rem;">Departamento · Seminuevo</div>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Precios detallados --}}
<section style="max-width:960px;margin:0 auto;padding:2.5rem 1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.3rem;">Precios por tipo y antigüedad</h2>
    <p style="font-size:.83rem;color:#9ca3af;margin-bottom:1.75rem;">
        Precio por m² · {{ now()->isoFormat('MMMM YYYY') }} · Referencias de mercado, no avalúos formales.
    </p>

    @php
        $aptNew   = $snapshots->get('apartment_new');
        $aptMid   = $snapshots->get('apartment_mid');
        $aptOld   = $snapshots->get('apartment_old');
        $houseNew = $snapshots->get('house_new');
        $houseMid = $snapshots->get('house_mid');
        $houseOld = $snapshots->get('house_old');
        $hasApt   = $aptNew || $aptMid || $aptOld;
        $hasHouse = $houseNew || $houseMid || $houseOld;
    @endphp

    @if($hasApt || $hasHouse)
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:.65rem 1rem;text-align:left;color:#9ca3af;font-size:.7rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;border-bottom:1px solid #e5e7eb;">Tipo de inmueble</th>
                        <th style="padding:.65rem 1rem;text-align:center;color:#9ca3af;font-size:.7rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;border-bottom:1px solid #e5e7eb;">Nuevo<br><span style="font-weight:400;text-transform:none;font-size:.65rem;">0–10 años</span></th>
                        <th style="padding:.65rem 1rem;text-align:center;color:#1d4ed8;font-size:.7rem;text-transform:uppercase;letter-spacing:.3px;font-weight:700;border-bottom:1px solid #e5e7eb;background:#eff6ff;">Seminuevo<br><span style="font-weight:400;text-transform:none;font-size:.65rem;">10–30 años</span></th>
                        <th style="padding:.65rem 1rem;text-align:center;color:#9ca3af;font-size:.7rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;border-bottom:1px solid #e5e7eb;">Antiguo<br><span style="font-weight:400;text-transform:none;font-size:.65rem;">30+ años</span></th>
                    </tr>
                </thead>
                <tbody>
                    @if($hasApt)
                    <tr>
                        <td style="padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;font-weight:600;color:#111827;">
                            🏢 Departamento
                        </td>
                        <td style="padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;text-align:center;">
                            @if($aptNew)
                            <div style="font-weight:700;font-size:.95rem;">${{ number_format($aptNew->price_m2_avg) }}</div>
                            <div style="font-size:.72rem;color:#9ca3af;margin-top:.15rem;">${{ number_format($aptNew->price_m2_low) }} – ${{ number_format($aptNew->price_m2_high) }}</div>
                            @else
                            <span style="color:#d1d5db;font-size:.85rem;">Sin datos</span>
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;text-align:center;background:#f0f7ff;">
                            @if($aptMid)
                            <div style="font-weight:700;font-size:1.05rem;color:#1d4ed8;">${{ number_format($aptMid->price_m2_avg) }}</div>
                            <div style="font-size:.72rem;color:#6b7280;margin-top:.15rem;">${{ number_format($aptMid->price_m2_low) }} – ${{ number_format($aptMid->price_m2_high) }}</div>
                            @else
                            <span style="color:#d1d5db;font-size:.85rem;">Sin datos</span>
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;text-align:center;">
                            @if($aptOld)
                            <div style="font-weight:700;font-size:.95rem;">${{ number_format($aptOld->price_m2_avg) }}</div>
                            <div style="font-size:.72rem;color:#9ca3af;margin-top:.15rem;">${{ number_format($aptOld->price_m2_low) }} – ${{ number_format($aptOld->price_m2_high) }}</div>
                            @else
                            <span style="color:#d1d5db;font-size:.85rem;">Sin datos</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                    @if($hasHouse)
                    <tr>
                        <td style="padding:.75rem 1rem;font-weight:600;color:#111827;">
                            🏠 Casa
                        </td>
                        <td style="padding:.75rem 1rem;text-align:center;">
                            @if($houseNew)
                            <div style="font-weight:700;font-size:.95rem;">${{ number_format($houseNew->price_m2_avg) }}</div>
                            <div style="font-size:.72rem;color:#9ca3af;margin-top:.15rem;">${{ number_format($houseNew->price_m2_low) }} – ${{ number_format($houseNew->price_m2_high) }}</div>
                            @else
                            <span style="color:#d1d5db;font-size:.85rem;">Sin datos</span>
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;text-align:center;background:#f0f7ff;">
                            @if($houseMid)
                            <div style="font-weight:700;font-size:1.05rem;color:#1d4ed8;">${{ number_format($houseMid->price_m2_avg) }}</div>
                            <div style="font-size:.72rem;color:#6b7280;margin-top:.15rem;">${{ number_format($houseMid->price_m2_low) }} – ${{ number_format($houseMid->price_m2_high) }}</div>
                            @else
                            <span style="color:#d1d5db;font-size:.85rem;">Sin datos</span>
                            @endif
                        </td>
                        <td style="padding:.75rem 1rem;text-align:center;">
                            @if($houseOld)
                            <div style="font-weight:700;font-size:.95rem;">${{ number_format($houseOld->price_m2_avg) }}</div>
                            <div style="font-size:.72rem;color:#9ca3af;margin-top:.15rem;">${{ number_format($houseOld->price_m2_low) }} – ${{ number_format($houseOld->price_m2_high) }}</div>
                            @else
                            <span style="color:#d1d5db;font-size:.85rem;">Sin datos</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div style="padding:.75rem 1rem;background:#fafafa;border-top:1px solid #f0f2f5;font-size:.72rem;color:#9ca3af;">
            Rango: precio mínimo – máximo observado en oferta publicada. Actualizado {{ now()->isoFormat('MMMM YYYY') }}.
        </div>
    </div>
    @else
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:2rem;text-align:center;color:#9ca3af;">
        Aún no tenemos datos de precios registrados para esta colonia.
    </div>
    @endif

    {{-- ¿Qué significa esto? --}}
    <div style="margin-top:1.5rem;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.8rem;color:#92400e;line-height:1.55;">
            <strong>¿Qué significa esto?</strong>
            Los precios son referencias de mercado basadas en anuncios publicados y transacciones recientes en {{ $colonia->name }}.
            El precio final de un inmueble específico depende de sus características: antigüedad exacta, estado de conservación, piso, estacionamiento y amenidades.
            Para una estimación personalizada, solicita una opinión de valor.
        </p>
    </div>
</section>

{{-- Colonias vecinas --}}
@if($siblings->count() > 0)
<section style="max-width:960px;margin:0 auto;padding:0 1.5rem 2.5rem;">
    <h2 style="font-size:1rem;font-weight:700;margin-bottom:1rem;color:#374151;">Otras colonias en {{ $zone->name }}</h2>
    <div style="display:flex;flex-wrap:wrap;gap:.6rem;">
        @foreach($siblings as $sibling)
        <a href="{{ route('mercado.colonia', [$zone->slug, $sibling->slug]) }}"
           style="display:inline-block;background:#fff;border:1px solid #e5e7eb;border-radius:20px;padding:.4rem .9rem;font-size:.8rem;color:#374151;text-decoration:none;transition:background .15s,border-color .15s;"
           onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe'"
           onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb'">
            {{ $sibling->name }}
        </a>
        @endforeach
        <a href="{{ route('mercado.zone', $zone->slug) }}"
           style="display:inline-block;background:#eff6ff;border:1px solid #bfdbfe;border-radius:20px;padding:.4rem .9rem;font-size:.8rem;color:#2563eb;text-decoration:none;font-weight:500;">
            Ver zona completa →
        </a>
    </div>
</section>
@endif

{{-- CTA -- Inline form --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:3rem 1.5rem;">
    <div style="max-width:680px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:2rem;">
            <h2 style="font-size:1.3rem;font-weight:700;margin-bottom:.5rem;">
                ¿Tienes un inmueble en {{ $colonia->name }}?
            </h2>
            <p style="font-size:.88rem;color:rgba(255,255,255,.65);">
                Te preparamos una opinión de valor personalizada con los factores específicos de tu inmueble.
            </p>
        </div>

        <a href="{{ route('mercado.opinion') }}?colonia={{ $colonia->id }}"
           style="display:block;text-align:center;background:#2563eb;color:#fff;padding:.85rem 2rem;border-radius:8px;font-weight:600;font-size:.92rem;text-decoration:none;max-width:320px;margin:0 auto;">
            Solicitar opinión de valor →
        </a>
    </div>
</section>

@endsection
