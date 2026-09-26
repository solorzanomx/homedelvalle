@extends('layouts.portal')

@section('title', 'Mis documentos')

@section('styles')
.td-row { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:.85rem .95rem; margin-bottom:.6rem; }
.td-row.open { border-color:#1D4ED8; box-shadow:0 0 0 3px rgba(29,78,216,.10); }
.td-top { display:flex; align-items:center; gap:.7rem; }
.td-dot { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.9rem; flex-shrink:0; font-weight:800; }
.td-label { flex:1; min-width:0; }
.td-label b { display:block; font-size:.9rem; color:#0f172a; line-height:1.3; }
.td-label small { display:block; font-size:.74rem; color:#64748b; line-height:1.35; margin-top:.1rem; }
.td-btn { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:0 1rem; border-radius:12px; font-weight:800; font-size:.85rem; text-decoration:none; border:0; cursor:pointer; white-space:nowrap; }
.td-chip { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:0 1rem; border-radius:12px; border:1.5px solid #1D4ED8; color:#1D4ED8; background:#fff; font-weight:700; font-size:.85rem; text-decoration:none; }
.td-chip.on { background:#1D4ED8; color:#fff; }
.td-reason { margin:.55rem 0 0; font-size:.78rem; color:#991b1b; background:#fef2f2; border-radius:10px; padding:.5rem .7rem; line-height:1.4; }
@media (max-width: 480px) { .td-top { flex-wrap:wrap; } .td-top .td-btn { width:100%; margin-top:.5rem; } }
@endsection

@section('content')
@php
    $states = [
        'falta'    => ['#f59e0b', '#fffbeb', '＋'],
        'parcial'  => ['#f59e0b', '#fffbeb', '◔'],
        'revision' => ['#3b82f6', '#eff6ff', '⏳'],
        'aprobado' => ['#10b981', '#ecfdf5', '✓'],
        'corregir' => ['#ef4444', '#fef2f2', '!'],
    ];
    $totalReq = array_sum($counts);
@endphp

<a href="{{ route('portal.journey') }}" style="display:inline-flex;align-items:center;gap:.4rem;font-size:.82rem;font-weight:600;color:#1D4ED8;text-decoration:none;margin-bottom:.6rem;">← Mi camino</a>
<h1 style="font-size:1.3rem;font-weight:800;color:#0f172a;margin:0 0 .35rem;">Mis documentos</h1>
<div style="display:flex;gap:.45rem;flex-wrap:wrap;margin-bottom:1rem;">
    @foreach(['corregir' => 'por corregir', 'falta' => 'faltan', 'revision' => 'en revisión', 'aprobado' => 'aprobados'] as $k => $txt)
        @if($counts[$k] > 0)
        <span style="font-size:.74rem;font-weight:700;padding:.25rem .65rem;border-radius:9999px;background:{{ $states[$k][1] }};color:{{ $states[$k][0] }};">{{ $counts[$k] }} {{ $txt }}</span>
        @endif
    @endforeach
</div>

@if(session('success'))<div style="background:#ecfdf5;border:1px solid #bbf7d0;color:#166534;border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;">✅ {{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;font-weight:600;">⚠ {{ session('error') }}</div>@endif

@if($next && ! $open)
<a href="{{ route('portal.documents.index', ['open' => $next['key']]) }}#row-{{ $next['key'] }}" style="display:flex;align-items:center;gap:.75rem;background:linear-gradient(135deg,#1e3a8a,#1D4ED8);color:#fff;border-radius:16px;padding:1rem 1.1rem;margin-bottom:1rem;text-decoration:none;">
    <span style="flex:1;font-size:.85rem;line-height:1.4;"><span style="display:block;font-size:.66rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;opacity:.8;">Sigue con</span><strong style="font-size:1rem;">{{ $next['key'] === 'domicilio' ? 'Tu comprobante de domicilio' : ($next['key'] === 'ingresos' ? 'Tu comprobante de ingresos' : $next['label']) }}</strong></span>
    <span style="font-weight:800;">{{ $next['state'] === 'corregir' ? 'Corregir' : 'Subir' }} →</span>
</a>
@elseif($totalReq > 0 && $counts['falta'] === 0 && $counts['corregir'] === 0)
<div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:14px;padding:.9rem 1.1rem;margin-bottom:1rem;font-size:.85rem;color:#166534;line-height:1.5;">
    ✅ <strong>No tienes nada pendiente.</strong> {{ $counts['revision'] > 0 ? 'Tu asesor está revisando lo que subiste.' : 'Todo está aprobado.' }}
</div>
@endif

@foreach($groups as $group)
    <div style="margin:1.1rem 0 .5rem;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#64748b;">{{ $group['icon'] }} {{ $group['title'] }}</div>
    @foreach($group['rows'] as $r)
        @php
            [$c, $bg, $ico] = $states[$r['state']];
            // Identificación sin elegir: la fila 'identificacion' se abre para escoger; ine_*/pasaporte abren su carga.
            $isOpen = $open === $r['key'];
            $wantCat = $openCat ?: ($r['chosen_cat'] ?? null);   // en ingresos, si ya subió un tipo, se sigue con ese
            $chosen = $isOpen && $r['chips'] && $wantCat ? collect($r['chips'])->firstWhere('cat', $wantCat) : null;
        @endphp
        <div class="td-row {{ $isOpen ? 'open' : '' }}" id="row-{{ $r['key'] }}">
            <div class="td-top">
                <div class="td-dot" style="background:{{ $bg }};color:{{ $c }};border:2px solid {{ $c }};">{{ $ico }}</div>
                <div class="td-label">
                    <b>{{ $r['label'] }}@if($r['optional']) <span style="font-weight:500;color:#94a3b8;">(opcional)</span>@endif</b>
                    <small style="color:{{ $c }};font-weight:700;">{{ $r['state_label'] }}@if($r['needed'] > 1 && $r['uploaded'] > 0) · {{ $r['uploaded'] }} de {{ $r['needed'] }} subidos @elseif($r['uploaded'] > 0 && $r['state'] !== 'falta') · {{ $r['uploaded'] }} {{ $r['uploaded'] === 1 ? 'archivo' : 'archivos' }}@endif</small>
                    @if($r['hint'] && ! $isOpen)<small>{{ $r['hint'] }}</small>@endif
                </div>
                @if(! $isOpen && $r['state'] !== 'aprobado')
                    <a class="td-btn" style="background:{{ $r['state'] === 'revision' ? '#eff6ff' : ($r['state'] === 'corregir' ? '#ef4444' : '#1D4ED8') }};color:{{ $r['state'] === 'revision' ? '#1D4ED8' : '#fff' }};"
                       href="{{ route('portal.documents.index', ['open' => $r['key']]) }}#row-{{ $r['key'] }}">
                        {{ $r['state'] === 'corregir' ? 'Corregir' : ($r['state'] === 'revision' ? 'Ver / agregar' : ($r['state'] === 'parcial' ? 'Subir el que falta' : ($r['key'] === 'identificacion' ? 'Elegir' : 'Subir'))) }}
                    </a>
                @endif
            </div>

            @if($r['reason'])<div class="td-reason"><strong>Motivo:</strong> {{ $r['reason'] }}</div>@endif

            @if($isOpen)
                <div style="margin-top:.8rem;border-top:1px dashed #e2e8f0;padding-top:.8rem;">
                    @if($r['hint'])<p style="margin:0 0 .6rem;font-size:.78rem;color:#64748b;">{{ $r['hint'] }}</p>@endif

                    @if($r['chips'] && ! $chosen)
                        <p style="margin:0 0 .5rem;font-size:.82rem;font-weight:700;color:#0f172a;">{{ $r['key'] === 'identificacion' ? '¿Qué identificación vas a usar?' : '¿Cuál vas a subir?' }}</p>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                            @foreach($r['chips'] as $chip)
                                <a class="td-chip" href="{{ isset($chip['open']) ? route('portal.documents.index', ['open' => $chip['open']]) . '#row-' . $chip['open'] : route('portal.documents.index', ['open' => $r['key'], 'cat' => $chip['cat']]) . '#row-' . $r['key'] }}">{{ $chip['label'] }}</a>
                            @endforeach
                        </div>
                    @else
                        @php $cat = $chosen['cat'] ?? $r['cats'][0]; $slots = $chosen['slots'] ?? $r['slots']; @endphp
                        @if($chosen)
                            <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.6rem;">
                                @foreach($r['chips'] as $chip)
                                    <a class="td-chip {{ $chip['cat'] === $cat ? 'on' : '' }}" style="min-height:38px;font-size:.78rem;" href="{{ route('portal.documents.index', ['open' => $r['key'], 'cat' => $chip['cat']]) }}#row-{{ $r['key'] }}">{{ $chip['label'] }}</a>
                                @endforeach
                            </div>
                        @endif
                        @livewire('portal.document-uploader', ['allowedCategories' => [$cat], 'rentalProcessId' => $rental->id, 'maxSlots' => $slots], key('td-' . $r['key'] . '-' . $cat))
                    @endif

                    <a href="{{ route('portal.documents.index') }}" class="td-btn" style="margin-top:.8rem;width:100%;background:#f1f5f9;color:#334155;">Listo, volver a la lista</a>
                </div>
            @endif
        </div>
    @endforeach
@endforeach

@if(! ($forObligado ?? false) && ($rental->tenant_has_aval === true || in_array($rental->guarantee_type, ['aval','aval_pagares'], true)))
<p style="font-size:.78rem;color:#64748b;margin-top:1rem;">Los <strong>datos</strong> de tu aval (nombre, domicilio, escritura) se llenan en <a href="{{ route('portal.expediente') }}" style="color:#1D4ED8;font-weight:700;">Tus datos</a>.</p>
@endif
@endsection

@section('scripts')
<script>
@include('portal._id_camera_js')
@if($open)
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('row-{{ $open }}');
    if (el) el.scrollIntoView({ block: 'start' });
});
@endif
</script>
@endsection
