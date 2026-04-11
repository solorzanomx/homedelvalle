@extends('layouts.app-sidebar')
@section('title', 'Mensajes')

@section('styles')
<style>
.msg-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
.msg-stat { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 0.75rem 1rem; text-align: center; }
.msg-stat-value { font-size: 1.4rem; font-weight: 700; color: var(--text); }
.msg-stat-label { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }

.msg-filters { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; align-items: flex-end; }
.msg-filters .form-group { margin: 0; }
.msg-filters .form-select, .msg-filters .form-input { font-size: 0.8rem; padding: 0.4rem 0.65rem; }

.msg-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.msg-table th { padding: 0.6rem 0.75rem; text-align: left; font-weight: 600; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid var(--border); }
.msg-table td { padding: 0.6rem 0.75rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.msg-table tr:hover { background: var(--bg); }

.badge-channel { padding: 0.15rem 0.5rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
.badge-email { background: #dbeafe; color: #1e40af; }
.badge-whatsapp { background: #d1fae5; color: #065f46; }
.badge-status { padding: 0.15rem 0.5rem; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
.badge-sent { background: #dbeafe; color: #1e40af; }
.badge-opened { background: #d1fae5; color: #065f46; }
.badge-failed { background: #fee2e2; color: #991b1b; }
.badge-queued { background: #fef3c7; color: #92400e; }
.badge-skipped { background: #f3f4f6; color: #6b7280; }
.badge-replied { background: #ede9fe; color: #5b21b6; }

.msg-subject { font-weight: 500; color: var(--text); }
.msg-client { color: var(--primary); text-decoration: none; font-weight: 500; }
.msg-client:hover { text-decoration: underline; }
.msg-dir { font-size: 0.72rem; color: var(--text-muted); }

@media (max-width: 768px) {
    .msg-stats { grid-template-columns: repeat(2, 1fr); }
    .msg-table th:nth-child(4), .msg-table td:nth-child(4),
    .msg-table th:nth-child(5), .msg-table td:nth-child(5) { display: none; }
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h1>Mensajes</h1>
        <p class="page-subtitle">Todos los correos y mensajes enviados desde el CRM y automatizaciones</p>
    </div>
</div>

<div class="msg-stats">
    <div class="msg-stat">
        <div class="msg-stat-value">{{ number_format($stats['total']) }}</div>
        <div class="msg-stat-label">Total</div>
    </div>
    <div class="msg-stat">
        <div class="msg-stat-value">{{ number_format($stats['sent']) }}</div>
        <div class="msg-stat-label">Enviados</div>
    </div>
    <div class="msg-stat">
        <div class="msg-stat-value">{{ number_format($stats['opened']) }}</div>
        <div class="msg-stat-label">Abiertos</div>
    </div>
    <div class="msg-stat">
        <div class="msg-stat-value">{{ number_format($stats['failed']) }}</div>
        <div class="msg-stat-label">Fallidos</div>
    </div>
</div>

<div class="card">
    <form method="GET" class="msg-filters">
        <div class="form-group">
            <select name="channel" class="form-select" onchange="this.form.submit()">
                <option value="">Todos los canales</option>
                <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
            </select>
        </div>
        <div class="form-group">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Enviado</option>
                <option value="opened" {{ request('status') === 'opened' ? 'selected' : '' }}>Abierto</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Fallido</option>
                <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>En cola</option>
                <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Omitido</option>
            </select>
        </div>
        <div class="form-group">
            <select name="direction" class="form-select" onchange="this.form.submit()">
                <option value="">Todas las direcciones</option>
                <option value="outbound" {{ request('direction') === 'outbound' ? 'selected' : '' }}>Saliente</option>
                <option value="inbound" {{ request('direction') === 'inbound' ? 'selected' : '' }}>Entrante</option>
            </select>
        </div>
        <div class="form-group">
            <input type="text" name="search" class="form-input" placeholder="Buscar..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
        @if(request()->hasAny(['channel', 'status', 'direction', 'search']))
        <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-outline">Limpiar</a>
        @endif
    </form>

    <div style="overflow-x:auto;">
        <table class="msg-table">
            <thead>
                <tr>
                    <th>Canal</th>
                    <th>Cliente</th>
                    <th>Asunto / Contenido</th>
                    <th>Estado</th>
                    <th>Enviado por</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr>
                    <td>
                        <span class="badge-channel badge-{{ $msg->channel }}">{{ strtoupper($msg->channel) }}</span>
                        <span class="msg-dir">{{ $msg->direction === 'outbound' ? '↑' : '↓' }}</span>
                    </td>
                    <td>
                        @if($msg->client)
                        <a href="{{ route('clients.show', $msg->client_id) }}" class="msg-client">{{ $msg->client->name }}</a>
                        @else
                        <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>
                        @if($msg->subject)
                        <div class="msg-subject">{{ Str::limit($msg->subject, 50) }}</div>
                        @endif
                        <div style="color:var(--text-muted); font-size:0.75rem;">{{ Str::limit($msg->body, 80) }}</div>
                    </td>
                    <td>
                        @php
                            $statusClass = match($msg->status) {
                                'sent' => 'sent', 'opened' => 'opened', 'failed' => 'failed',
                                'queued' => 'queued', 'skipped' => 'skipped', 'replied' => 'replied',
                                default => 'queued',
                            };
                            $statusLabel = match($msg->status) {
                                'sent' => 'Enviado', 'opened' => 'Abierto', 'failed' => 'Fallido',
                                'queued' => 'En cola', 'skipped' => 'Omitido', 'replied' => 'Respondido',
                                default => $msg->status,
                            };
                        @endphp
                        <span class="badge-status badge-{{ $statusClass }}">{{ $statusLabel }}</span>
                        @if($msg->open_count > 0)
                        <span style="font-size:0.7rem; color:var(--text-muted);">{{ $msg->open_count }}x</span>
                        @endif
                    </td>
                    <td style="font-size:0.78rem; color:var(--text-muted);">{{ $msg->user->name ?? 'Sistema' }}</td>
                    <td style="font-size:0.78rem; color:var(--text-muted); white-space:nowrap;">{{ $msg->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-muted);">No hay mensajes registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">
        {{ $messages->links() }}
    </div>
</div>
@endsection
