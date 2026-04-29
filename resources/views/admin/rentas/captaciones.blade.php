@extends('layouts.app-sidebar')
@section('title', 'Captaciones de Renta')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Captaciones de Renta</h1>
        <p class="page-subtitle">Fase 1 — Evaluación y captación de inmuebles para renta</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.rentas.activas') }}" class="btn btn-secondary btn-sm">
            <x-icon name="arrow-right" class="w-4 h-4" /> Ver Rentas Activas
        </a>
    </div>
</div>

{{-- Stats strip --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem;">
    <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">Total captaciones</p>
        <p style="font-size:1.75rem;font-weight:800;color:var(--text);">{{ $stats['total'] }}</p>
    </div>
    <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">Esta semana</p>
        <p style="font-size:1.75rem;font-weight:800;color:var(--text);">{{ $stats['esta_sem'] }}</p>
    </div>
    <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">Sin asignar</p>
        <p style="font-size:1.75rem;font-weight:800;color:{{ $stats['sin_asig'] > 0 ? '#ef4444' : 'var(--text)' }};">{{ $stats['sin_asig'] }}</p>
    </div>
</div>

{{-- Kanban placeholder — PR Rentas-2 lo convierte en kanban interactivo con Livewire --}}
<div style="display:flex;gap:.75rem;overflow-x:auto;padding-bottom:1rem;">
    @foreach($stages as $key => $label)
    @php $items = $captaciones->get($key, collect()); @endphp
    <div style="min-width:220px;flex-shrink:0;">
        {{-- Header de columna --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;background:#f8fafc;border:1px solid var(--border);border-radius:8px 8px 0 0;border-bottom:2px solid #3B82C4;">
            <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#1e293b;">{{ $label }}</span>
            <span style="background:#e2e8f0;color:#64748b;font-size:.65rem;font-weight:700;border-radius:9999px;padding:.1rem .45rem;">{{ $items->count() }}</span>
        </div>
        {{-- Cards --}}
        <div style="background:#f8fafc;border:1px solid var(--border);border-top:none;border-radius:0 0 8px 8px;min-height:120px;padding:.5rem;display:flex;flex-direction:column;gap:.5rem;">
            @forelse($items as $op)
            <a href="#" style="display:block;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:.7rem .85rem;text-decoration:none;transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
                <p style="font-size:.78rem;font-weight:600;color:#0f172a;margin-bottom:.2rem;line-height:1.3;">
                    {{ $op->client?->name ?? 'Sin cliente' }}
                </p>
                @if($op->property)
                <p style="font-size:.68rem;color:#64748b;">{{ $op->property->colony ?? $op->property->address ?? '—' }}</p>
                @endif
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.4rem;">
                    @if($op->monthly_rent)
                    <span style="font-size:.68rem;font-weight:600;color:#1D4ED8;">${{ number_format($op->monthly_rent) }}/mes</span>
                    @else
                    <span style="font-size:.68rem;color:#94a3b8;">Sin precio</span>
                    @endif
                    <span style="font-size:.62rem;color:#94a3b8;">{{ $op->created_at->diffForHumans() }}</span>
                </div>
            </a>
            @empty
            <p style="font-size:.72rem;color:#cbd5e1;text-align:center;padding:1rem 0;">Vacío</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<p style="font-size:.72rem;color:#94a3b8;margin-top:.75rem;text-align:center;">
    El kanban interactivo con drag & drop llega en <strong>PR Rentas-2</strong>.
</p>
@endsection
