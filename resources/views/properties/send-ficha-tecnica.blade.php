@extends('layouts.app-sidebar')
@section('title', 'Enviar ficha técnica')

@section('styles')
<style>
.contact-pick { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 0.5rem; cursor: pointer; }
.contact-pick:hover { border-color: var(--primary); }
.contact-pick input { margin-top: 0.2rem; }
.contact-pick-name { font-weight: 600; font-size: 0.88rem; }
.contact-pick-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.1rem; }
.zone-tag { display: inline-block; font-size: 0.66rem; padding: 0.08rem 0.4rem; border-radius: 10px; background: rgba(102,126,234,0.1); color: var(--primary); margin: 0.15rem 0.2rem 0 0; }
.no-email-warning { background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: var(--radius); padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.82rem; color: #92400e; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Enviar ficha técnica</h2>
        <p class="text-muted">{{ $property->title }}</p>
    </div>
    <div class="action-btns">
        <a href="{{ route('properties.ficha-tecnica.pdf', $property) }}" target="_blank" class="btn btn-outline">Ver ficha PDF</a>
        <a href="{{ route('properties.show', $property) }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@if($contacts->isEmpty())
<div class="no-email-warning">
    No hay contactos de constructoras con correo registrado todavía. Ve a <a href="{{ route('developer-contacts.create') }}">Contactos de Constructoras</a> para agregar alguno.
</div>
@else
<div class="card" style="max-width:640px;">
    <div class="card-body">
        <form method="POST" action="{{ route('properties.ficha-tecnica.send', $property) }}">
            @csrf

            <label class="form-label">Elige a quién enviarle la ficha</label>
            <div style="max-height:400px; overflow-y:auto; margin:0.5rem 0 1.25rem;">
                @foreach($contacts as $contact)
                <label class="contact-pick">
                    <input type="checkbox" name="contact_ids[]" value="{{ $contact->id }}">
                    <div>
                        <div class="contact-pick-name">{{ $contact->name }}</div>
                        <div class="contact-pick-meta">
                            {{ $contact->developerCompany?->name ?? 'Inversionista independiente' }}
                            @if($contact->role) &middot; {{ $contact->role }} @endif
                            &middot; {{ $contact->email }}
                        </div>
                        @if($contact->interest_zones)
                        <div style="margin-top:0.25rem;">
                            @foreach($contact->interest_zones as $zone)
                                <span class="zone-tag">{{ $zone }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>

            <div class="form-group">
                <label class="form-label">Mensaje adicional (opcional)</label>
                <textarea name="message" class="form-textarea" rows="3" placeholder="Ej. Terreno con potencial H4/30, dueño ya validó precio de salida."></textarea>
            </div>

            <div class="form-actions">
                <a href="{{ route('properties.show', $property) }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Enviar ficha técnica</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
