@extends('layouts.app-sidebar')
@section('title', 'Propiedades publicadas en EasyBroker')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Propiedades publicadas en EasyBroker</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">
            Solo las que están publicadas ahora mismo (la cuenta acumula el histórico completo: vendidas, suspendidas y borradores).
        </p>
    </div>
    <a href="{{ route('admin.easybroker.settings') }}" class="btn btn-outline">← Configuración</a>
</div>

@if(! $result['success'])
    <div class="card" style="padding:1.25rem; color:#991b1b; background:#fef2f2;">
        No se pudo consultar EasyBroker: {{ $result['message'] ?? 'error desconocido' }}
    </div>
@else
    @php $pagination = $result['pagination'] ?? []; @endphp
    <div class="card" style="padding:0;">
        <div style="padding:0.9rem 1.25rem; border-bottom:1px solid var(--border); font-size:0.85rem; color:var(--text-muted);">
            {{ number_format($pagination['total'] ?? count($result['data'])) }} propiedades publicadas
            · página {{ $page }}
        </div>
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID EasyBroker</th>
                        <th>Título</th>
                        <th>Operaciones</th>
                        <th>Ubicación</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($result['data'] as $prop)
                    <tr>
                        <td style="white-space:nowrap; font-weight:600;">{{ $prop['public_id'] ?? '—' }}</td>
                        <td style="max-width:340px;">{{ \Illuminate\Support\Str::limit($prop['title'] ?? '—', 80) }}</td>
                        <td style="white-space:nowrap;">
                            @foreach(($prop['operations'] ?? []) as $op)
                                <span class="badge {{ ($op['type'] ?? '') === 'sale' ? 'badge-blue' : 'badge-green' }}">
                                    {{ ($op['type'] ?? '') === 'sale' ? 'Venta' : 'Renta' }} · {{ $op['formatted_amount'] ?? $op['amount'] ?? '' }}
                                </span>
                            @endforeach
                        </td>
                        <td style="font-size:0.82rem; color:var(--text-muted); max-width:260px;">
                            {{ is_array($prop['location'] ?? null) ? ($prop['location']['name'] ?? '—') : ($prop['location'] ?? '—') }}
                        </td>
                        <td style="white-space:nowrap;">
                            @if(!empty($prop['public_url']))
                                <a href="{{ $prop['public_url'] }}" target="_blank" rel="noopener" class="btn btn-outline" style="padding:0.3rem 0.7rem; font-size:0.78rem;">Ver ficha ↗</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">No hay propiedades publicadas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="display:flex; justify-content:space-between; padding:0.9rem 1.25rem; border-top:1px solid var(--border);">
            @if($page > 1)
                <a href="{{ route('admin.easybroker.properties', ['page' => $page - 1]) }}" class="btn btn-outline">← Anterior</a>
            @else
                <span></span>
            @endif
            @if(!empty($pagination['next_page']))
                <a href="{{ route('admin.easybroker.properties', ['page' => $page + 1]) }}" class="btn btn-outline">Siguiente →</a>
            @endif
        </div>
    </div>
@endif
@endsection
