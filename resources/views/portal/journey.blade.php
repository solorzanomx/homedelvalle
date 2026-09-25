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
        Tu camino a rentar{{ $address ? ' en ' . $address : '' }}
    </h1>
    <div style="display:flex;align-items:center;gap:.6rem;margin-top:.6rem;">
        <div style="flex:1;height:8px;border-radius:9999px;background:#e2e8f0;overflow:hidden;"><div style="width:{{ $pct }}%;height:100%;background:#10b981;"></div></div>
        <span style="font-size:.75rem;font-weight:700;color:#475569;">{{ $doneCount }} de {{ $total }} pasos</span>
    </div>
</div>

@if(session('success'))<div style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;">⚠ {{ session('error') }}</div>@endif

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
