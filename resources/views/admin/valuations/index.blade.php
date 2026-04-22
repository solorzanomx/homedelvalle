@extends('layouts.app-sidebar')
@section('title', 'Opiniones de Valor')

@section('content')
<div class="page-header">
    <div>
        <h2>Opiniones de Valor</h2>
        <p class="text-muted" style="font-size:.83rem;margin-top:4px;">
            Valuaciones comerciales de inmuebles
        </p>
    </div>
    <a href="{{ route('admin.valuations.create') }}" class="btn btn-primary">+ Nueva valuación</a>
</div>

{{-- Filtros --}}
<div style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    @foreach([''=>'Todas', 'draft'=>'Borrador', 'final'=>'Finales', 'delivered'=>'Entregadas'] as $val => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $val]) }}"
       class="btn btn-sm {{ request('status', '') === $val ? 'btn-primary' : 'btn-outline' }}">
        {{ $label }}
    </a>
    @endforeach
    <div style="flex:1;"></div>
    @foreach([''=>'Todos', 'on_market'=>'En mercado', 'above_market'=>'Arriba', 'opportunity'=>'Oportunidad'] as $val => $label)
    <a href="{{ request()->fullUrlWithQuery(['diagnosis' => $val]) }}"
       class="btn btn-sm {{ request('diagnosis', '') === $val ? 'btn-primary' : 'btn-outline' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        @if($valuations->isEmpty())
            <div style="text-align:center;padding:3rem 2rem;color:#9ca3af;">
                <div style="font-size:2rem;margin-bottom:.5rem;">📊</div>
                <p style="font-weight:500;color:#374151;">Sin valuaciones todavía</p>
                <p style="font-size:.83rem;margin-top:.25rem;">
                    <a href="{{ route('admin.valuations.create') }}" style="color:#2563eb;">Crear la primera valuación</a>
                </p>
            </div>
        @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Inmueble / Colonia</th>
                    <th>Tipo</th>
                    <th>m²</th>
                    <th>Precio sugerido</th>
                    <th>Diagnóstico</th>
                    <th>Confianza</th>
                    <th>Estado</th>
                    <th>Creada</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($valuations as $v)
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:.85rem;">
                            {{ $v->property?->title ?? 'Standalone' }}
                        </div>
                        <div style="font-size:.75rem;color:#6b7280;">
                            {{ $v->colonia?->name ?? $v->input_colonia_raw ?? '—' }}
                            @if($v->colonia?->zone)
                                · {{ $v->colonia->zone->name }}
                            @endif
                        </div>
                    </td>
                    <td style="font-size:.82rem;">{{ $v->type_label }}</td>
                    <td style="font-size:.82rem;">{{ number_format($v->input_m2_total) }}</td>
                    <td style="font-size:.85rem;font-weight:600;">
                        @if($v->suggested_list_price)
                            ${{ number_format($v->suggested_list_price) }}
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($v->diagnosis)
                        <span class="badge badge-{{ $v->diagnosis_color }}" style="font-size:.7rem;">
                            {{ $v->diagnosis_label }}
                        </span>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($v->confidence)
                        <span class="badge badge-{{ $v->confidence === 'high' ? 'green' : ($v->confidence === 'medium' ? 'yellow' : 'red') }}"
                              style="font-size:.7rem;">
                            {{ ['high'=>'Alta','medium'=>'Media','low'=>'Baja'][$v->confidence] ?? '—' }}
                        </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $v->status_color }}" style="font-size:.7rem;">
                            {{ $v->status_label }}
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:#6b7280;">
                        {{ $v->created_at->format('d/m/Y') }}
                        <div>{{ $v->creator?->name ?? '—' }}</div>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.valuations.show', $v) }}" class="btn btn-sm btn-outline">Ver</a>
                            <a href="{{ route('admin.valuations.edit', $v) }}" class="btn btn-sm btn-outline">Editar</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="padding:1rem 1.5rem;">
            {{ $valuations->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
