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
            <x-icon name="arrow-left" class="w-4 h-4" /> Rentas en colocación
        </a>
    </div>
</div>

{{-- Stats strip --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">Rentas activas</p>
        <p style="font-size:1.75rem;font-weight:800;color:#0f172a;">{{ $stats['activas'] }}</p>
    </div>
    <div style="background:#fff;border:1px solid {{ $stats['renovacion'] > 0 ? '#f59e0b' : 'var(--border)' }};border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">⏰ Por renovar (&lt;60 días)</p>
        <p style="font-size:1.75rem;font-weight:800;color:{{ $stats['renovacion'] > 0 ? '#f59e0b' : '#0f172a' }};">{{ $stats['renovacion'] }}</p>
    </div>
    <div style="background:#fff;border:1px solid {{ $stats['moveout'] > 0 ? '#3B82C4' : 'var(--border)' }};border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">Salidas programadas</p>
        <p style="font-size:1.75rem;font-weight:800;color:{{ $stats['moveout'] > 0 ? '#3B82C4' : '#0f172a' }};">{{ $stats['moveout'] }}</p>
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: 'activas' }">
    <div style="display:flex;gap:.25rem;border-bottom:2px solid var(--border);margin-bottom:1.25rem;">
        @foreach(['activas' => 'Activas ('.$stats['activas'].')', 'renovacion' => '⏰ Renovación ('.$stats['renovacion'].')', 'moveout' => 'Move-out ('.$stats['moveout'].')', 'cerradas' => 'Cerradas'] as $key => $lbl)
        <button @click="tab = '{{ $key }}'"
                :style="tab === '{{ $key }}' ? 'border-bottom:2px solid #1D4ED8;margin-bottom:-2px;color:#1D4ED8;font-weight:700;' : 'color:#64748b;'"
                style="padding:.5rem 1rem;font-size:.8rem;background:none;border:none;cursor:pointer;border-bottom:2px solid transparent;transition:color .15s;">
            {{ $lbl }}
        </button>
        @endforeach
    </div>

    {{-- Tab: Activas --}}
    <div x-show="tab === 'activas'">
        @include('admin.rentas._tabla-rentas', ['rentas' => $activas, 'empty' => 'No hay rentas activas.'])
    </div>

    {{-- Tab: Renovación --}}
    <div x-show="tab === 'renovacion'">
        @include('admin.rentas._tabla-rentas', ['rentas' => $renovacion, 'empty' => 'No hay contratos próximos a vencer.'])
    </div>

    {{-- Tab: Move-out --}}
    <div x-show="tab === 'moveout'">
        @include('admin.rentas._tabla-rentas', ['rentas' => $moveout, 'empty' => 'No hay salidas programadas.'])
    </div>

    {{-- Tab: Cerradas --}}
    <div x-show="tab === 'cerradas'">
        @include('admin.rentas._tabla-rentas', ['rentas' => $cerradas, 'empty' => 'Sin rentas cerradas aún.'])
    </div>
</div>
@endsection
