@extends('layouts.app-sidebar')
@section('title', $company->name)

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 0 0 0.75rem;
}
.contact-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; border-bottom: 1px solid var(--border); }
.contact-item:last-child { border-bottom: none; }
.contact-avatar {
    width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.78rem; flex-shrink: 0;
}
.contact-info { flex: 1; min-width: 0; }
.contact-name { font-size: 0.85rem; font-weight: 500; }
.contact-meta { font-size: 0.72rem; color: var(--text-muted); }
.charge-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.82rem; }
.charge-item:last-child { border-bottom: none; }
.totals-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
.total-box { text-align: center; padding: 0.85rem; background: var(--bg); border-radius: var(--radius); }
.total-box-val { font-size: 1.15rem; font-weight: 700; }
.total-box-lbl { font-size: 0.72rem; color: var(--text-muted); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $company->name }}</h2>
        <p class="text-muted">{{ $company->type_label }}</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('providers.edit', $company) }}" class="btn btn-outline">Editar</a>
        <a href="{{ route('providers.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="show-layout" style="display:grid; grid-template-columns: 1fr 1.4fr; gap:1.25rem; align-items:start;">
    <div>
        {{-- Datos de contacto --}}
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body">
                <div class="section-title">Datos de contacto</div>
                <div style="font-size:0.85rem; line-height:1.9;">
                    <div><strong>Contacto:</strong> {{ $company->contact_name ?: '—' }}</div>
                    <div><strong>Email:</strong> {{ $company->email ?: '—' }}</div>
                    <div><strong>Teléfono:</strong> {{ $company->phone ?: '—' }}</div>
                    <div><strong>Dirección:</strong> {{ $company->address ?: '—' }}</div>
                    <div><strong>Ciudad:</strong> {{ $company->city ?: '—' }}</div>
                    <div><strong>Estado:</strong> <span class="badge {{ $company->status === 'active' ? 'badge-green' : 'badge-red' }}">{{ $company->status === 'active' ? 'Activo' : 'Inactivo' }}</span></div>
                    @if($company->notes)
                    <div style="margin-top:0.5rem;"><strong>Notas:</strong><br>{{ $company->notes }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Empleados / contactos --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title">Empleados / Contactos ({{ $contacts->count() }})</div>
                @forelse($contacts as $contact)
                <div class="contact-item" id="contact-view-{{ $contact->id }}">
                    <div class="contact-avatar">{{ strtoupper(substr($contact->name, 0, 1)) }}</div>
                    <div class="contact-info">
                        <div class="contact-name">{{ $contact->name }}</div>
                        <div class="contact-meta">
                            {{ $contact->role ?: 'Sin puesto' }}
                            @if($contact->phone) &middot; {{ $contact->phone }} @endif
                            @if($contact->email) &middot; {{ $contact->email }} @endif
                            @if($contact->birth_date) &middot; 🎂 {{ $contact->birth_date->format('d/m') }} @endif
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline" onclick="toggleContactEdit({{ $contact->id }})">Editar</button>
                    <form method="POST" action="{{ route('provider-contacts.destroy', $contact) }}" onsubmit="return confirm('¿Eliminar este contacto?')" style="margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline" style="color:var(--danger);">Eliminar</button>
                    </form>
                </div>
                <form method="POST" action="{{ route('provider-contacts.update', $contact) }}" id="contact-edit-{{ $contact->id }}" style="display:none; gap:0.5rem; flex-wrap:wrap; padding:0.5rem 0; border-bottom:1px solid var(--border);">
                    @csrf @method('PUT')
                    <input type="text" name="name" class="form-input" value="{{ $contact->name }}" placeholder="Nombre" required style="flex:1; min-width:120px;">
                    <input type="text" name="role" class="form-input" value="{{ $contact->role }}" placeholder="Puesto (ej. Notario)" style="flex:1; min-width:120px;">
                    <input type="tel" name="phone" class="form-input" value="{{ $contact->phone }}" placeholder="Teléfono" style="flex:1; min-width:100px;">
                    <input type="email" name="email" class="form-input" value="{{ $contact->email }}" placeholder="Correo" style="flex:1; min-width:140px;">
                    <div style="flex:1; min-width:140px;">
                        <input type="date" name="birth_date" class="form-input" value="{{ $contact->birth_date?->format('Y-m-d') }}" style="width:100%;">
                        <p class="form-hint" style="margin:2px 0 0;">Fecha de nacimiento — para el correo de cumpleaños.</p>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="toggleContactEdit({{ $contact->id }})">Cancelar</button>
                </form>
                @empty
                <p class="text-muted" style="font-size:0.82rem;">Sin empleados registrados todavía.</p>
                @endforelse

                <form method="POST" action="{{ route('providers.contacts.store', $company) }}" style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border); display:flex; gap:0.5rem; flex-wrap:wrap;">
                    @csrf
                    <input type="text" name="name" class="form-input" placeholder="Nombre" required style="flex:1; min-width:120px;">
                    <input type="text" name="role" class="form-input" placeholder="Puesto (ej. Notario)" style="flex:1; min-width:120px;">
                    <input type="tel" name="phone" class="form-input" placeholder="Teléfono" style="flex:1; min-width:100px;">
                    <input type="email" name="email" class="form-input" placeholder="Correo" style="flex:1; min-width:140px;">
                    <div style="flex:1; min-width:140px;">
                        <input type="date" name="birth_date" class="form-input" style="width:100%;">
                        <p class="form-hint" style="margin:2px 0 0;">Fecha de nacimiento (opcional)</p>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>

    <div>
        {{-- Historial de cobros/comisiones --}}
        <div class="card">
            <div class="card-body">
                <div class="section-title">Historial de cobros y comisiones</div>

                <div class="totals-row">
                    <div class="total-box">
                        <div class="total-box-val">${{ number_format($totals['cargo'], 0) }}</div>
                        <div class="total-box-lbl">Total cargos (nos cobra)</div>
                    </div>
                    <div class="total-box">
                        <div class="total-box-val">${{ number_format($totals['comision'], 0) }}</div>
                        <div class="total-box-lbl">Total comisiones (nos comisiona)</div>
                    </div>
                </div>

                @forelse($charges as $charge)
                <div class="charge-item">
                    <div>
                        <div style="font-weight:500;">{{ $charge->service_description }}</div>
                        <div class="text-muted" style="font-size:0.72rem;">
                            @if($charge->operation)
                                {{ $charge->operation->property->title ?? $charge->operation->property->address ?? 'Operation #' . $charge->operation_id }}
                                &middot; <a href="{{ route('operations.show', $charge->operation_id) }}">Ver proceso →</a>
                            @elseif($charge->rentalProcess)
                                {{ $charge->rentalProcess->property->address ?? 'Renta #' . $charge->rental_process_id }}
                                &middot; <a href="{{ route('admin.rentas.gestion.show', $charge->rental_process_id) }}">Ver proceso →</a>
                            @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600;">${{ number_format($charge->amount ?? $charge->calculateCommission(), 0) }}</div>
                        <span class="badge badge-{{ $charge->status_color }}" style="font-size:0.68rem;">{{ $charge->status_label }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted" style="font-size:0.82rem;">Sin cobros registrados todavía. Se agregan desde la ficha de cada proceso.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleContactEdit(id) {
    var view = document.getElementById('contact-view-' + id);
    var edit = document.getElementById('contact-edit-' + id);
    var editing = edit.style.display !== 'none';
    edit.style.display = editing ? 'none' : 'flex';
    view.style.display = editing ? 'flex' : 'none';
}
</script>
@endsection
