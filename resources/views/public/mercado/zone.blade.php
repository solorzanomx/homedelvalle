@extends('layouts.public')

@section('meta')
<title>Precios en {{ $zone->name }}, Benito Juárez | Observatorio Home del Valle</title>
<meta name="description" content="Precios por m² en {{ $zone->name }}: {{ $zone->colonias->pluck('name')->join(', ') }}. Datos actualizados de departamentos y casas.">
<link rel="canonical" href="{{ url('/mercado/' . $zone->slug) }}">
@endsection

@section('content')

{{-- Breadcrumb --}}
<div style="background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:.6rem 1.5rem;font-size:.78rem;color:#6b7280;">
    <div style="max-width:960px;margin:0 auto;">
        <a href="{{ route('mercado.index') }}" style="color:#2563eb;">Observatorio</a>
        &nbsp;›&nbsp; {{ $zone->name }}
    </div>
</div>

{{-- Hero de zona --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:2.5rem 1.5rem 2rem;">
    <div style="max-width:960px;margin:0 auto;">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,.45);margin-bottom:.5rem;">
            Benito Juárez · {{ $zone->name }}
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
            <div>
                <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:700;margin-bottom:.4rem;">
                    Mercado inmobiliario en {{ $zone->name }}
                </h1>
                @if($zone->short_description)
                <p style="font-size:.88rem;color:rgba(255,255,255,.65);max-width:520px;">
                    {{ $zone->short_description }}
                </p>
                @endif
            </div>
            @if($zoneAvg)
            <div style="text-align:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:1rem 1.5rem;">
                <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.55;margin-bottom:.3rem;">Precio m² promedio</div>
                <div style="font-size:1.6rem;font-weight:700;">${{ number_format((int)$zoneAvg) }}</div>
                <div style="font-size:.68rem;opacity:.5;margin-top:.2rem;">Departamento · Seminuevo</div>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- Nav entre zonas --}}
<div style="background:#fff;border-bottom:1px solid #e5e7eb;overflow-x:auto;">
    <div style="max-width:960px;margin:0 auto;display:flex;gap:0;padding:0 1.5rem;">
        @foreach($allZones as $z)
        <a href="{{ route('mercado.zone', $z->slug) }}"
           style="display:inline-block;padding:.65rem .9rem;font-size:.78rem;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid {{ $z->id === $zone->id ? '#2563eb' : 'transparent' }};color:{{ $z->id === $zone->id ? '#2563eb' : '#6b7280' }};">
            {{ $z->name }}
        </a>
        @endforeach
    </div>
</div>

{{-- Tabla de precios por colonia --}}
<section style="max-width:960px;margin:0 auto;padding:2.5rem 1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.3rem;">Precios por colonia</h2>
    <p style="font-size:.83rem;color:#9ca3af;margin-bottom:1.5rem;">
        Precio por m² en {{ now()->isoFormat('MMMM YYYY') }} · Referencias de mercado, no avalúos formales.
    </p>

    @foreach($colonias as $colonia)
    @php
        $aptNew  = $snapshots->get($colonia->id . '_apartment_new');
        $aptMid  = $snapshots->get($colonia->id . '_apartment_mid');
        $aptOld  = $snapshots->get($colonia->id . '_apartment_old');
        $houseNew= $snapshots->get($colonia->id . '_house_new');
        $houseMid= $snapshots->get($colonia->id . '_house_mid');
        $houseOld= $snapshots->get($colonia->id . '_house_old');
        $hasData = $aptNew || $aptMid || $aptOld;
    @endphp

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;margin-bottom:1.25rem;overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.25rem;background:#f8fafc;border-bottom:1px solid #e5e7eb;">
            <div>
                <a href="{{ route('mercado.colonia', [$zone->slug, $colonia->slug]) }}"
                   style="font-size:1rem;font-weight:700;color:#111827;text-decoration:none;">
                    {{ $colonia->name }}
                </a>
                @if($colonia->cp)
                <span style="font-size:.72rem;color:#9ca3af;margin-left:.5rem;">CP {{ $colonia->cp }}</span>
                @endif
            </div>
            <a href="{{ route('mercado.colonia', [$zone->slug, $colonia->slug]) }}"
               style="font-size:.75rem;color:#2563eb;text-decoration:none;white-space:nowrap;">
                Ver detalle →
            </a>
        </div>

        @if($hasData)
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
                <thead>
                    <tr style="background:#fafafa;">
                        <th style="padding:.5rem .75rem;text-align:left;color:#9ca3af;font-size:.68rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;border-bottom:1px solid #f0f2f5;">Tipo</th>
                        <th style="padding:.5rem .75rem;text-align:center;color:#9ca3af;font-size:.68rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;border-bottom:1px solid #f0f2f5;">Nuevo (0-10 años)</th>
                        <th style="padding:.5rem .75rem;text-align:center;color:#9ca3af;font-size:.68rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;border-bottom:1px solid #f0f2f5;">Seminuevo (10-30)</th>
                        <th style="padding:.5rem .75rem;text-align:center;color:#9ca3af;font-size:.68rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;border-bottom:1px solid #f0f2f5;">Antiguo (30+)</th>
                    </tr>
                </thead>
                <tbody>
                    @if($aptMid)
                    <tr>
                        <td style="padding:.55rem .75rem;border-bottom:1px solid #f8f8f8;font-weight:500;">🏢 Departamento</td>
                        <td style="padding:.55rem .75rem;border-bottom:1px solid #f8f8f8;text-align:center;">
                            @if($aptNew) <span style="font-weight:600;">${{ number_format($aptNew->price_m2_avg) }}</span><br><span style="font-size:.7rem;color:#9ca3af;">${{ number_format($aptNew->price_m2_low) }}–${{ number_format($aptNew->price_m2_high) }}</span> @else <span style="color:#d1d5db;">—</span> @endif
                        </td>
                        <td style="padding:.55rem .75rem;border-bottom:1px solid #f8f8f8;text-align:center;background:#f0f7ff;">
                            <span style="font-weight:700;color:#1d4ed8;">${{ number_format($aptMid->price_m2_avg) }}</span><br>
                            <span style="font-size:.7rem;color:#6b7280;">${{ number_format($aptMid->price_m2_low) }}–${{ number_format($aptMid->price_m2_high) }}</span>
                        </td>
                        <td style="padding:.55rem .75rem;border-bottom:1px solid #f8f8f8;text-align:center;">
                            @if($aptOld) <span style="font-weight:600;">${{ number_format($aptOld->price_m2_avg) }}</span><br><span style="font-size:.7rem;color:#9ca3af;">${{ number_format($aptOld->price_m2_low) }}–${{ number_format($aptOld->price_m2_high) }}</span> @else <span style="color:#d1d5db;">—</span> @endif
                        </td>
                    </tr>
                    @endif
                    @if($houseMid)
                    <tr>
                        <td style="padding:.55rem .75rem;font-weight:500;">🏠 Casa</td>
                        <td style="padding:.55rem .75rem;text-align:center;">
                            @if($houseNew) <span style="font-weight:600;">${{ number_format($houseNew->price_m2_avg) }}</span><br><span style="font-size:.7rem;color:#9ca3af;">${{ number_format($houseNew->price_m2_low) }}–${{ number_format($houseNew->price_m2_high) }}</span> @else <span style="color:#d1d5db;">—</span> @endif
                        </td>
                        <td style="padding:.55rem .75rem;text-align:center;background:#f0f7ff;">
                            <span style="font-weight:700;color:#1d4ed8;">${{ number_format($houseMid->price_m2_avg) }}</span><br>
                            <span style="font-size:.7rem;color:#6b7280;">${{ number_format($houseMid->price_m2_low) }}–${{ number_format($houseMid->price_m2_high) }}</span>
                        </td>
                        <td style="padding:.55rem .75rem;text-align:center;">
                            @if($houseOld) <span style="font-weight:600;">${{ number_format($houseOld->price_m2_avg) }}</span><br><span style="font-size:.7rem;color:#9ca3af;">${{ number_format($houseOld->price_m2_low) }}–${{ number_format($houseOld->price_m2_high) }}</span> @else <span style="color:#d1d5db;">—</span> @endif
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        @else
        <div style="padding:1rem 1.25rem;color:#9ca3af;font-size:.82rem;">Sin datos registrados para esta colonia.</div>
        @endif
    </div>
    @endforeach
</section>

{{-- CTA --}}
<section style="background:#f0f7ff;border-top:1px solid #bfdbfe;padding:2.5rem 1.5rem;text-align:center;">
    <div style="max-width:600px;margin:0 auto;">
        <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:.5rem;">¿Tienes un inmueble en {{ $zone->name }}?</h2>
        <p style="color:#6b7280;font-size:.88rem;margin-bottom:1.25rem;">
            Solicita una opinión de valor personalizada. Te entregamos un análisis detallado con los factores que afectan el precio de tu inmueble específico.
        </p>
        <a href="{{ route('mercado.opinion') }}"
           style="display:inline-block;background:#2563eb;color:#fff;padding:.7rem 1.75rem;border-radius:8px;font-weight:600;font-size:.88rem;text-decoration:none;">
            Solicitar opinión de valor →
        </a>
    </div>
</section>

@endsection
