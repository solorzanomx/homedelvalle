@extends('layouts.app-sidebar')
@section('title', 'Enviar Correo a ' . $client->name)

@section('styles')
<style>
.compose-layout { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; }
.prop-selector { max-height: 400px; overflow-y: auto; }
.prop-check { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 0.5rem; cursor: pointer; transition: all 0.15s; }
.prop-check:hover { border-color: var(--primary); background: rgba(102,126,234,0.04); }
.prop-check.selected { border-color: var(--primary); background: rgba(102,126,234,0.08); }
.prop-check input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--primary); flex-shrink: 0; }
.prop-thumb { width: 48px; height: 48px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: var(--bg); }
.prop-meta { flex: 1; min-width: 0; }
.prop-meta .name { font-size: 0.85rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prop-meta .info { font-size: 0.75rem; color: var(--text-muted); }
.prop-meta .price { font-size: 0.8rem; font-weight: 600; color: var(--primary); }
.smtp-warning { background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius); padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.85rem; color: #92400e; }
.selected-count { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.5rem; }
.search-props { width: 100%; padding: 0.45rem 0.7rem; font-size: 0.82rem; border: 1px solid var(--border); border-radius: var(--radius); margin-bottom: 0.75rem; background: var(--card); outline: none; }
.search-props:focus { border-color: var(--primary); }
@media (max-width: 1024px) { .compose-layout { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Enviar Correo</h2>
        <p class="text-muted">Para: {{ $client->name }} &lt;{{ $client->email }}&gt;</p>
    </div>
    <a href="{{ route('clients.show', $client) }}" class="btn btn-outline">&#8592; Volver al perfil</a>
</div>

@if(!$hasSmtp)
<div class="smtp-warning">
    &#9888; No tienes configurado tu correo de empresa. <a href="{{ route('profile') }}#smtp" style="color:#92400e; font-weight:600;">Configuralo en tu perfil</a> para enviar correos desde tu cuenta @homedelvalle.mx.
</div>
@endif

<form method="POST" action="{{ route('clients.email.send', $client) }}">
    @csrf
    <div class="compose-layout">
        {{-- LEFT: Compose form --}}
        <div>
            <div class="card">
                <div class="card-header"><h3>Redactar Correo</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Asunto <span class="required">*</span></label>
                        <input type="text" name="subject" class="form-input" value="{{ old('subject', 'Propiedades que pueden interesarte') }}" required>
                        @error('subject') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mensaje <span class="required">*</span></label>
                        <textarea name="message" class="form-textarea" rows="8" required placeholder="Escribe tu mensaje para el cliente...">{{ old('message', 'Te comparto algunas propiedades que considero pueden ser de tu interes. Si alguna te llama la atencion, con gusto te puedo agendar una visita.') }}</textarea>
                        <p class="form-hint">Las propiedades seleccionadas se agregan automaticamente debajo del mensaje.</p>
                        @error('message') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-actions">
                        <a href="{{ route('clients.show', $client) }}" class="btn btn-outline">Cancelar</a>
                        <button type="submit" class="btn btn-primary" {{ !$hasSmtp ? 'disabled title=Configura tu SMTP primero' : '' }}>&#9993; Enviar Correo</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Property selector --}}
        <div>
            <div class="card">
                <div class="card-header"><h3>Seleccionar Propiedades</h3></div>
                <div class="card-body">
                    <input type="text" class="search-props" id="searchProps" placeholder="Buscar propiedad..." oninput="filterProps(this.value)">
                    <p class="selected-count" id="selectedCount">0 propiedades seleccionadas</p>
                    <div class="prop-selector" id="propList">
                        @foreach($properties as $prop)
                        <label class="prop-check" data-name="{{ strtolower($prop->title . ' ' . $prop->city) }}">
                            <input type="checkbox" name="property_ids[]" value="{{ $prop->id }}" onchange="updateCount()" {{ in_array($prop->id, old('property_ids', [])) ? 'checked' : '' }}>
                            @if($prop->photo)
                                <img class="prop-thumb" src="{{ asset('storage/' . $prop->photo) }}" alt="">
                            @else
                                <div class="prop-thumb" style="display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:16px;">&#8962;</div>
                            @endif
                            <div class="prop-meta">
                                <div class="name">{{ $prop->title }}</div>
                                <div class="price">{{ $prop->formatted_price }}</div>
                                <div class="info">{{ $prop->operation_label }} &middot; {{ $prop->city ?? 'Sin ciudad' }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
function updateCount() {
    var checked = document.querySelectorAll('#propList input[type=checkbox]:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' propiedad' + (checked !== 1 ? 'es' : '') + ' seleccionada' + (checked !== 1 ? 's' : '');
    document.querySelectorAll('.prop-check').forEach(function(el) {
        el.classList.toggle('selected', el.querySelector('input').checked);
    });
}
function filterProps(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.prop-check').forEach(function(el) {
        el.style.display = el.dataset.name.includes(query) ? '' : 'none';
    });
}
document.addEventListener('DOMContentLoaded', updateCount);
</script>
@endsection
