@extends('layouts.portal')

@section('title', 'Mi camino')

@section('content')
@php
    $first = explode(' ', trim($client->name))[0];
    $doneCount = collect($roadmap['steps'])->where('state', 'done')->count();
    $total = count($roadmap['steps']);
    $pct = (int) round($doneCount / max(1, $total) * 100);
    $address = $rental->property?->address;
@endphp

<div style="margin-bottom:1rem;">
    <div style="font-size:.78rem;color:var(--text-muted);">Hola, {{ $first }} 👋</div>
    <h1 style="font-size:1.35rem;font-weight:800;margin:.1rem 0 0;color:#0f172a;line-height:1.25;">
        {{ $rental->is_active_under_management ? 'Tu renta' : 'Tu camino a rentar' }}{{ $address ? ' en ' . $address : '' }}
    </h1>
    @unless($rental->is_active_under_management)
    <div style="display:flex;align-items:center;gap:.6rem;margin-top:.6rem;">
        <div style="flex:1;height:8px;border-radius:9999px;background:#e2e8f0;overflow:hidden;"><div style="width:{{ $pct }}%;height:100%;background:#10b981;"></div></div>
        <span style="font-size:.75rem;font-weight:700;color:#475569;">{{ $doneCount }} de {{ $total }} pasos</span>
    </div>
    @endunless
</div>

@if(session('success'))<div style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;">⚠ {{ session('error') }}</div>@endif

@if($rental->is_active_under_management)
    {{-- Renta activa bajo administración de Home del Valle (2026-10-04): aquí ya no tiene caso
         mostrar el camino de cierre (apartado/datos/documentos/garantía/contrato/entrega) — todo
         eso ya pasó. En su lugar, lo que de verdad le importa a alguien que ya vive ahí: su renta,
         cuándo vence el contrato, y cómo reportar algo. Mientras NO haya administración contratada,
         el trato se cierra solo unos días después de la entrega (rentals:close-after-entrega) y el
         inquilino deja de ver "Mi camino" por completo (activeTenantRental ya no lo encuentra). --}}
    @php
        $diasVencimiento = $rental->lease_end_date ? now()->diffInDays($rental->lease_end_date, false) : null;
        $porVencer = $diasVencimiento !== null && $diasVencimiento >= 0 && $diasVencimiento <= 60;
        $vencido = $diasVencimiento !== null && $diasVencimiento < 0;
    @endphp
    <div style="background:linear-gradient(135deg,#166534,#16a34a);border-radius:18px;padding:1.25rem;color:#fff;margin-bottom:1rem;box-shadow:0 10px 30px rgba(22,101,52,.25);">
        <div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;opacity:.85;">🏠 Tu renta está activa</div>
        <div style="font-size:1.1rem;font-weight:800;margin:.3rem 0 .6rem;">Home del Valle administra tu renta</div>
        <div style="display:grid;gap:.5rem;font-size:.85rem;">
            @if($rental->monthly_rent)
            <div style="display:flex;justify-content:space-between;background:rgba(255,255,255,.14);border-radius:10px;padding:.55rem .8rem;">
                <span>Renta mensual</span>
                <strong>${{ number_format($rental->monthly_rent, 0) }} {{ $rental->currency ?? 'MXN' }}{{ $rental->payment_day ? ' · día ' . $rental->payment_day : '' }}</strong>
            </div>
            @endif
            @if($rental->lease_end_date)
            <div style="display:flex;justify-content:space-between;background:rgba(255,255,255,.14);border-radius:10px;padding:.55rem .8rem;">
                <span>Vigencia del contrato</span>
                <strong>Hasta {{ $rental->lease_end_date->format('d/m/Y') }}</strong>
            </div>
            @endif
        </div>
        @if($vencido)
        <div style="margin-top:.75rem;font-size:.78rem;background:rgba(255,255,255,.18);border-radius:10px;padding:.6rem .8rem;">⚠ Tu contrato ya venció — tu asesor se pondrá en contacto para la renovación.</div>
        @elseif($porVencer)
        <div style="margin-top:.75rem;font-size:.78rem;background:rgba(255,255,255,.18);border-radius:10px;padding:.6rem .8rem;">⏳ Tu contrato vence en {{ $diasVencimiento }} días — tu asesor te contactará para la renovación.</div>
        @endif
    </div>

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem 1.1rem;margin-bottom:1rem;">
        <details>
            <summary style="cursor:pointer;font-weight:700;font-size:.9rem;color:#0f172a;">📩 Reportar una incidencia</summary>
            <p style="font-size:.78rem;color:#64748b;margin:.5rem 0 .7rem;">Avería, mantenimiento, algo que no funciona — cuéntanos qué pasa y tu asesor le dará seguimiento.</p>
            <form method="POST" action="{{ route('portal.rentals.report-issue', $rental->id) }}">
                @csrf
                <textarea name="description" class="form-textarea" rows="3" required placeholder="Describe lo que está pasando..." style="width:100%;border:1px solid #cbd5e1;border-radius:10px;padding:.6rem .8rem;font-size:.85rem;"></textarea>
                <button type="submit" style="margin-top:.6rem;min-height:44px;width:100%;border:0;border-radius:10px;background:#1D4ED8;color:#fff;font-weight:800;font-size:.85rem;cursor:pointer;">Enviar reporte</button>
            </form>
        </details>
    </div>
@else
    {{-- TU SIGUIENTE PASO --}}
    <div style="background:linear-gradient(135deg,#1e3a8a,#1D4ED8);border-radius:18px;padding:1.25rem 1.25rem 1.35rem;color:#fff;margin-bottom:1rem;box-shadow:0 10px 30px rgba(29,78,216,.25);">
        <div style="font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;opacity:.8;">Tu siguiente paso</div>
        <div style="font-size:1.2rem;font-weight:800;margin:.25rem 0 .35rem;line-height:1.3;">{{ $next['title'] }}</div>
        <div style="font-size:.85rem;opacity:.92;line-height:1.5;">{{ $next['body'] }}</div>
        @if($next['cta_url'])
            <a href="{{ $next['cta_url'] }}" style="display:flex;align-items:center;justify-content:center;gap:.5rem;margin-top:1rem;min-height:48px;background:#fff;color:#1D4ED8;border-radius:12px;font-weight:800;font-size:.95rem;text-decoration:none;">
                {{ $next['cta_label'] }} →
            </a>
            @if($next['minutes'])<div style="text-align:center;font-size:.72rem;opacity:.85;margin-top:.5rem;">⏱ Unos {{ $next['minutes'] }} min</div>@endif
        @else
            <div style="margin-top:.9rem;font-size:.78rem;background:rgba(255,255,255,.14);border-radius:10px;padding:.6rem .8rem;">⏳ Por ahora no tienes nada pendiente. Te avisamos en cuanto haya novedades.</div>
        @endif
    </div>

    @if($next['secondary'])
    <a href="{{ $next['secondary']['cta_url'] }}" style="display:flex;align-items:center;gap:.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:.85rem 1rem;margin-bottom:1rem;text-decoration:none;color:#78350f;">
        <span style="font-size:1.3rem;">🔑</span>
        <span style="flex:1;font-size:.82rem;line-height:1.4;"><strong>{{ $next['secondary']['title'] }}</strong><br>{{ $next['secondary']['body'] }}</span>
        <span style="font-weight:800;font-size:.8rem;">{{ $next['secondary']['cta_label'] }} →</span>
    </a>
    @endif

    {{-- EL CAMINO --}}
    @include('portal._tenant_roadmap', ['rental' => $rental, 'roadmap' => $roadmap, 'compact' => true])
@endif

{{-- AYUDA --}}
@php
    $advisor = $rental->broker;
    $wa = preg_replace('/\D/', '', $advisor?->whatsapp ?: $advisor?->phone ?: '');
    if ($wa && strlen($wa) === 10) { $wa = '52' . $wa; }
@endphp
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:1rem 1.1rem;display:flex;align-items:center;gap:.9rem;flex-wrap:wrap;">
    <div style="flex:1;min-width:200px;">
        <div style="font-weight:700;font-size:.9rem;color:#0f172a;">¿Dudas? Estamos contigo</div>
        <div style="font-size:.78rem;color:#64748b;">{{ $advisor?->name ? 'Tu asesor: ' . $advisor->name . '.' : '' }} Escríbele cuando quieras.</div>
    </div>
    @if($wa)
        <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Hola, soy ' . $client->name . '. Tengo una duda sobre mi renta.') }}" target="_blank" rel="noopener"
           style="display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 1.1rem;border-radius:12px;background:#16a34a;color:#fff;font-weight:700;font-size:.85rem;text-decoration:none;">💬 WhatsApp</a>
    @endif
</div>
@endsection
