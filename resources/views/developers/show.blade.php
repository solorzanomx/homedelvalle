@extends('layouts.app-sidebar')
@section('title', $company->name)

@section('styles')
<style>
.section-title { font-size: 0.9rem; font-weight: 600; color: var(--text); margin: 0 0 0.75rem; }
.contact-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0; border-bottom: 1px solid var(--border); }
.contact-item:last-child { border-bottom: none; }
.contact-avatar {
    width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.78rem; flex-shrink: 0;
}
.contact-info { flex: 1; min-width: 0; }
.contact-name { font-size: 0.85rem; font-weight: 500; }
.contact-meta { font-size: 0.72rem; color: var(--text-muted); }
.zone-tag { display: inline-block; font-size: 0.68rem; padding: 0.1rem 0.45rem; border-radius: 10px; background: rgba(102,126,234,0.1); color: var(--primary); margin: 0.15rem 0.2rem 0 0; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $company->name }}</h2>
        <p class="text-muted">{{ $company->type_label }}</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('developers.edit', $company) }}" class="btn btn-outline">Editar</a>
        <a href="{{ route('developers.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="show-layout" style="display:grid; grid-template-columns: 1fr 1.4fr; gap:1.25rem; align-items:start;">
    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title">Datos de la empresa</div>
                <div style="font-size:0.85rem; line-height:1.9;">
                    <div><strong>RFC:</strong> {{ $company->rfc ?: '—' }}</div>
                    <div><strong>Sitio web:</strong> {{ $company->website ?: '—' }}</div>
                    <div><strong>Estado:</strong> <span class="badge {{ $company->status === 'active' ? 'badge-green' : 'badge-red' }}">{{ $company->status === 'active' ? 'Activo' : 'Inactivo' }}</span></div>
                    @if($company->notes)
                    <div style="margin-top:0.5rem;"><strong>Notas:</strong><br>{{ $company->notes }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                    <div class="section-title" style="margin:0;">Contactos ({{ $contacts->count() }})</div>
                    <a href="{{ route('developer-contacts.create', ['developer_company_id' => $company->id]) }}" class="btn btn-sm btn-primary">+ Agregar contacto</a>
                </div>
                @forelse($contacts as $contact)
                <div class="contact-item">
                    <div class="contact-avatar">{{ strtoupper(substr($contact->name, 0, 1)) }}</div>
                    <div class="contact-info">
                        <div class="contact-name">{{ $contact->name }}</div>
                        <div class="contact-meta">
                            {{ $contact->role ?: 'Sin puesto' }}
                            @if($contact->phone) &middot; {{ $contact->phone }} @endif
                            @if($contact->email) &middot; {{ $contact->email }} @endif
                        </div>
                        @if($contact->interest_zones)
                        <div style="margin-top:0.25rem;">
                            @foreach($contact->interest_zones as $zone)
                                <span class="zone-tag">{{ $zone }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('developer-contacts.edit', $contact) }}" class="btn btn-sm btn-outline">Editar</a>
                </div>
                @empty
                <p class="text-muted" style="font-size:0.82rem;">Sin contactos registrados todavía.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
