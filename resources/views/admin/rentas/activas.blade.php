@extends('layouts.app-sidebar')
@section('title', 'Rentas Activas')

@section('content')
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Rentas Activas</h1>
        <p class="page-subtitle">Fase 2 — Colocación: inmuebles en mercado buscando inquilino</p>
    </div>
    <div class="page-header-right">
        <a href="{{ route('admin.rentas.captaciones') }}" class="btn btn-secondary btn-sm">
            <x-icon name="arrow-left" class="w-4 h-4" /> Captaciones
        </a>
        <a href="{{ route('admin.rentas.gestion') }}" class="btn btn-secondary btn-sm">
            Gestión Post-Cierre <x-icon name="arrow-right" class="w-4 h-4" />
        </a>
    </div>
</div>

{{-- Stats strip --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
    @foreach([
        ['label' => 'Total en mercado', 'value' => $stats['total'],       'color' => '#0f172a'],
        ['label' => 'En búsqueda',      'value' => $stats['en_busqueda'], 'color' => '#0f172a'],
        ['label' => 'Con oferta',        'value' => $stats['con_oferta'],  'color' => '#f59e0b'],
        ['label' => 'Por firmar',        'value' => $stats['por_firmar'],  'color' => '#10b981'],
    ] as $stat)
    <div style="background:#fff;border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem;">
        <p style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);margin-bottom:.25rem;">{{ $stat['label'] }}</p>
        <p style="font-size:1.75rem;font-weight:800;color:{{ $stat['color'] }};">{{ $stat['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Kanban placeholder — PR Rentas-3 lo convierte en kanban Livewire interactivo --}}
<div style="display:flex;gap:.75rem;overflow-x:auto;padding-bottom:1rem;">
    @foreach($stages as $key => $label)
    @php $items = $operaciones->get($key, collect()); @endphp
    <div style="min-width:220px;flex-shrink:0;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .75rem;background:#f8fafc;border:1px solid var(--border);border-radius:8px 8px 0 0;border-bottom:2px solid
            @if(in_array($key, ['contrato','entrega'])) #10b981
            @elseif(in_array($key, ['investigacion','busqueda'])) #f59e0b
            @else #3B82C4 @endif;">
            <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#1e293b;">{{ $label }}</span>
            <span style="background:#e2e8f0;color:#64748b;font-size:.65rem;font-weight:700;border-radius:9999px;padding:.1rem .45rem;">{{ $items->count() }}</span>
        </div>
        <div style="background:#f8fafc;border:1px solid var(--border);border-top:none;border-radius:0 0 8px 8px;min-height:120px;padding:.5rem;display:flex;flex-direction:column;gap:.5rem;">
            @forelse($items as $op)
            <a href="{{ route('rentals.show', $op->id) }}" style="display:block;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:.7rem .85rem;text-decoration:none;transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">
                <p style="font-size:.78rem;font-weight:600;color:#0f172a;margin-bottom:.2rem;line-height:1.3;">
                    {{ $op->property?->address ?? $op->client?->name ?? 'Sin propiedad' }}
                </p>
                @if($op->monthly_rent)
                <p style="font-size:.68rem;font-weight:600;color:#1D4ED8;">${{ number_format($op->monthly_rent) }}/mes</p>
                @endif
                <p style="font-size:.62rem;color:#94a3b8;margin-top:.2rem;">{{ $op->created_at->diffForHumans() }}</p>
            </a>
            @empty
            <p style="font-size:.72rem;color:#cbd5e1;text-align:center;padding:1rem 0;">Vacío</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<p style="font-size:.72rem;color:#94a3b8;margin-top:.75rem;text-align:center;">
    El kanban interactivo con drag & drop y sub-vista por inmueble llega en <strong>PR Rentas-3</strong>.
</p>
@endsection
