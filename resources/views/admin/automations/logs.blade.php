@extends('layouts.app-sidebar')
@section('title', 'Logs de Automatizacion')

@section('content')
@php
    $statusBadge = ['success' => 'badge-green', 'failed' => 'badge-red', 'skipped' => 'badge-yellow'];
    $statusLabel = ['success' => 'Exitoso', 'failed' => 'Fallido', 'skipped' => 'Omitido'];
@endphp

<div class="page-header">
    <div>
        <h2>Logs de Automatizacion</h2>
        <p class="text-muted">Historial de ejecuciones de las reglas de automatizacion</p>
    </div>
    <a href="{{ route('admin.automations.index') }}" class="btn btn-outline">&#8592; Volver a Reglas</a>
</div>

{{-- Filtros --}}
<div class="card">
    <div class="card-body" style="padding:1rem 1.5rem;">
        <form method="GET" action="{{ route('admin.automations.logs') }}" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
            <div class="form-group" style="margin-bottom:0; flex:1; min-width:200px;">
                <label class="form-label">Regla</label>
                <select name="rule_id" class="form-select">
                    <option value="">Todas las reglas</option>
                    @foreach($rules as $rule)
                        <option value="{{ $rule->id }}" {{ request('rule_id') == $rule->id ? 'selected' : '' }}>{{ $rule->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0; flex:1; min-width:160px;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Exitoso</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Fallido</option>
                    <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Omitido</option>
                </select>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('admin.automations.logs') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </div>
</div>

{{-- Logs Table --}}
<div class="card">
    <div class="card-header">
        <h3>Registros ({{ $logs->total() }})</h3>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Regla</th>
                    <th>Trigger Data</th>
                    <th>Resultado</th>
                    <th>Estado</th>
                    <th>Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                        @if($log->rule)
                            <a href="{{ route('admin.automations.edit', $log->rule) }}" style="color:var(--primary); font-weight:500;">{{ $log->rule->name }}</a>
                        @else
                            <span class="text-muted">(eliminada)</span>
                        @endif
                    </td>
                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ json_encode($log->trigger_data) }}">
                        {{ \Illuminate\Support\Str::limit(json_encode($log->trigger_data), 45) }}
                    </td>
                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ json_encode($log->action_result) }}">
                        {{ \Illuminate\Support\Str::limit(json_encode($log->action_result), 45) }}
                    </td>
                    <td>
                        <span class="badge {{ $statusBadge[$log->status] ?? 'badge-blue' }}">{{ $statusLabel[$log->status] ?? $log->status }}</span>
                    </td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        @if($log->error_message)
                            <span style="color:var(--danger);" title="{{ $log->error_message }}">{{ \Illuminate\Support\Str::limit($log->error_message, 60) }}</span>
                        @else
                            <span class="text-muted">&mdash;</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted" style="padding:2rem;">
                        No hay logs de automatizacion registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
<div style="display:flex; justify-content:center; padding:1rem 0;">
    {{ $logs->links() }}
</div>
@endif
@endsection
