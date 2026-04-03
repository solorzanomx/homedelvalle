@extends('layouts.app-sidebar')
@section('title', 'Lead: ' . $submission->name)

@section('styles')
<style>
    .lead-detail { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; }
    .lead-field { margin-bottom: 1.25rem; }
    .lead-field label { display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); margin-bottom: 0.25rem; }
    .lead-field p { font-size: 0.92rem; color: var(--text); line-height: 1.5; }
    .meta-card { font-size: 0.82rem; }
    .meta-card .meta-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border); }
    .meta-card .meta-row:last-child { border-bottom: none; }
    .meta-card .meta-label { color: var(--text-muted); }
    @media (max-width: 1024px) { .lead-detail { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $submission->name }}</h2>
        <p class="text-muted">Lead recibido el {{ $submission->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('admin.submissions.index') }}" class="btn btn-outline">&#8592; Volver</a>
        <form method="POST" action="{{ route('admin.submissions.destroy', $submission) }}" onsubmit="return confirm('Eliminar este lead?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn" style="color: var(--danger); border: 1px solid var(--danger); background: transparent;">Eliminar</button>
        </form>
    </div>
</div>

<div class="lead-detail">
    <div>
        <div class="card">
            <div class="card-header"><h3>Mensaje</h3></div>
            <div class="card-body">
                <div class="lead-field">
                    <label>Nombre</label>
                    <p>{{ $submission->name }}</p>
                </div>
                <div class="lead-field">
                    <label>Email</label>
                    <p><a href="mailto:{{ $submission->email }}">{{ $submission->email }}</a></p>
                </div>
                @if($submission->phone)
                <div class="lead-field">
                    <label>Telefono</label>
                    <p><a href="tel:{{ $submission->phone }}">{{ $submission->phone }}</a></p>
                </div>
                @endif
                <div class="lead-field" style="margin-bottom: 0;">
                    <label>Mensaje</label>
                    <p style="white-space: pre-wrap; background: var(--bg); padding: 1rem; border-radius: var(--radius);">{{ $submission->message }}</p>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header"><h3>Detalles</h3></div>
            <div class="card-body meta-card">
                <div class="meta-row">
                    <span class="meta-label">Estado</span>
                    <span>
                        @if($submission->is_read)
                            <span class="badge badge-success">Leido</span>
                        @else
                            <span class="badge badge-primary">Nuevo</span>
                        @endif
                    </span>
                </div>
                @if($submission->property)
                <div class="meta-row">
                    <span class="meta-label">Propiedad</span>
                    <span>{{ Str::limit($submission->property->title, 25) }}</span>
                </div>
                @endif
                <div class="meta-row">
                    <span class="meta-label">Fecha</span>
                    <span>{{ $submission->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($submission->utm_source)
                <div class="meta-row">
                    <span class="meta-label">UTM Source</span>
                    <span>{{ $submission->utm_source }}</span>
                </div>
                @endif
                @if($submission->utm_medium)
                <div class="meta-row">
                    <span class="meta-label">UTM Medium</span>
                    <span>{{ $submission->utm_medium }}</span>
                </div>
                @endif
                @if($submission->utm_campaign)
                <div class="meta-row">
                    <span class="meta-label">UTM Campaign</span>
                    <span>{{ $submission->utm_campaign }}</span>
                </div>
                @endif
                @if($submission->ip_address)
                <div class="meta-row">
                    <span class="meta-label">IP</span>
                    <span>{{ $submission->ip_address }}</span>
                </div>
                @endif
            </div>
        </div>

        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
            <a href="{{ route('clients.create', ['from_submission' => $submission->id, 'name' => $submission->name, 'email' => $submission->email, 'phone' => $submission->phone, 'utm_source' => $submission->utm_source, 'utm_medium' => $submission->utm_medium, 'utm_campaign' => $submission->utm_campaign]) }}" class="btn btn-primary" style="text-align: center; width: 100%;">
                &#128100; Convertir a Cliente
            </a>
            <div style="display: flex; gap: 0.5rem;">
                @if($submission->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $submission->phone) }}" target="_blank" class="btn btn-outline" style="flex: 1; text-align: center;">WhatsApp</a>
                @endif
                <a href="mailto:{{ $submission->email }}" class="btn btn-outline" style="flex: 1; text-align: center;">Email</a>
            </div>
        </div>
    </div>
</div>
@endsection
