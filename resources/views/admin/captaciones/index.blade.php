@extends('layouts.app-sidebar')
@section('title', 'Captaciones')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700;">Captaciones</h1>
        <p style="color:var(--text-muted); font-size:.85rem;">Procesos de evaluación y captación de inmuebles</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body" style="padding:0;">
        @if($captaciones->isEmpty())
        <div style="text-align:center; padding:3rem; color:var(--text-muted);">
            <p>No hay captaciones activas.</p>
        </div>
        @else
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Etapa</th>
                        <th>Docs Aprobados</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($captaciones as $cap)
                    @php
                        $approvedCount = $cap->documents->where('captacion_status', 'aprobado')->count();
                        $totalDocs     = $cap->documents->count();
                    @endphp
                    <tr>
                        <td style="font-weight:500;">
                            <a href="{{ route('admin.captaciones.show', $cap) }}" style="color:var(--primary);">{{ $cap->client->name ?? '—' }}</a>
                            @if($cap->client->email ?? false)
                            <div style="font-size:.75rem; color:var(--text-muted);">{{ $cap->client->email }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $cap->portal_etapa >= 4 ? 'green' : 'blue' }}">
                                Etapa {{ $cap->portal_etapa }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size:.85rem;">{{ $approvedCount }} / {{ $totalDocs }}</span>
                        </td>
                        <td>{{ $cap->precio_acordado ? '$' . number_format($cap->precio_acordado, 0) : '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $cap->status === 'completado' ? 'green' : ($cap->status === 'cancelado' ? 'red' : 'yellow') }}">
                                {{ ucfirst($cap->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.captaciones.show', $cap) }}" class="btn btn-sm btn-outline">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:1rem;">
            {{ $captaciones->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
