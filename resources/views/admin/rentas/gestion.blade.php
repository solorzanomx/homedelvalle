@extends('layouts.app-sidebar')
@section('title', 'Gestión Post-Cierre')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Gestión Post-Cierre</h1>
        <p class="page-subtitle">Fase 3 — Rentas activas, renovaciones y salidas</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.rentas.activas') }}" class="btn btn-secondary btn-sm">
            <x-icon name="arrow-left" class="w-4 h-4" /> Colocación Activa
        </a>
    </div>
</div>

{{-- Stats strip --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.25rem;">Rentas activas</p>
        <p style="font-size:1.75rem;font-weight:800;color:#0f172a;">{{ $stats['activas'] }}</p>
    </div>
    <div style="background:#fff;border:1px solid {{ $stats['renovacion'] > 0 ? '#f59e0b' : '#e2e8f0' }};border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.25rem;">Por renovar (&lt;60 días)</p>
        <p style="font-size:1.75rem;font-weight:800;color:{{ $stats['renovacion'] > 0 ? '#f59e0b' : '#0f172a' }};">{{ $stats['renovacion'] }}</p>
    </div>
    <div style="background:#fff;border:1px solid {{ $stats['moveout'] > 0 ? '#3B82C4' : '#e2e8f0' }};border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.25rem;">Salidas programadas</p>
        <p style="font-size:1.75rem;font-weight:800;color:{{ $stats['moveout'] > 0 ? '#3B82C4' : '#0f172a' }};">{{ $stats['moveout'] }}</p>
    </div>
</div>

{{-- Tabs — vanilla JS, sin Alpine ─────────────────────────────────────────────── --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">

    {{-- Tab nav --}}
    <div style="display:flex;border-bottom:1px solid #e2e8f0;background:#f8fafc;">
        @php
        $tabs = [
            'activas'    => 'Activas (' . $stats['activas'] . ')',
            'renovacion' => '⏰ Renovación (' . $stats['renovacion'] . ')',
            'moveout'    => 'Move-out (' . $stats['moveout'] . ')',
            'cerradas'   => 'Cerradas',
        ];
        @endphp
        @foreach($tabs as $key => $lbl)
        <button
            onclick="switchTab('{{ $key }}')"
            id="tab-btn-{{ $key }}"
            style="padding:.75rem 1.25rem;font-size:.8rem;font-weight:600;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;color:#64748b;transition:all .15s;white-space:nowrap;"
        >{{ $lbl }}</button>
        @endforeach
    </div>

    {{-- Tab panels --}}
    @foreach(['activas' => $activas, 'renovacion' => $renovacion, 'moveout' => $moveout, 'cerradas' => $cerradas] as $key => $rentas)
    <div id="tab-{{ $key }}" style="display:none;">
        @php
        $emptyMessages = [
            'activas'    => 'No hay rentas activas.',
            'renovacion' => 'No hay contratos próximos a vencer.',
            'moveout'    => 'No hay salidas programadas.',
            'cerradas'   => 'Sin rentas cerradas aún.',
        ];
        @endphp
        @include('admin.rentas._tabla-rentas', ['rentas' => $rentas, 'empty' => $emptyMessages[$key]])
    </div>
    @endforeach

</div>

@endsection

@section('scripts')
<script>
function switchTab(name) {
    // Ocultar todos los panels
    ['activas','renovacion','moveout','cerradas'].forEach(function(t) {
        var panel = document.getElementById('tab-' + t);
        var btn   = document.getElementById('tab-btn-' + t);
        if (panel) panel.style.display = 'none';
        if (btn) {
            btn.style.color = '#64748b';
            btn.style.borderBottomColor = 'transparent';
            btn.style.background = 'none';
        }
    });
    // Mostrar el seleccionado
    var active = document.getElementById('tab-' + name);
    var activeBtn = document.getElementById('tab-btn-' + name);
    if (active) active.style.display = 'block';
    if (activeBtn) {
        activeBtn.style.color = '#1D4ED8';
        activeBtn.style.borderBottomColor = '#1D4ED8';
        activeBtn.style.background = '#fff';
    }
}
// Activar tab inicial
document.addEventListener('DOMContentLoaded', function() { switchTab('activas'); });
</script>
@endsection
