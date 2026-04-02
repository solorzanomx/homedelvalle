@extends('layouts.app-sidebar')
@section('title', 'Editar Cliente')

@section('styles')
<style>
.section-title {
    font-size: 0.9rem; font-weight: 600; color: var(--text);
    margin: 1.5rem 0 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border);
}
.section-title:first-child { margin-top: 0; }
.photo-upload {
    border: 2px dashed var(--border); border-radius: var(--radius);
    padding: 1.5rem; text-align: center; cursor: pointer; transition: border-color 0.2s;
}
.photo-upload:hover { border-color: var(--primary); }
.photo-upload img { max-height: 100px; border-radius: 50%; margin-bottom: 0.5rem; }
.meta-info {
    font-size: 0.78rem; color: var(--text-muted);
    padding: 0.75rem 0; border-top: 1px solid var(--border); margin-top: 1.25rem;
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Cliente</h2>
        <p class="text-muted">{{ $client->name }}</p>
    </div>
    <a href="{{ route('clients.show', $client) }}" class="btn btn-outline">&#8592; Ver Perfil</a>
</div>

<div class="card" style="max-width:700px;">
    <div class="card-body">
        @if($errors->any())
            <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:1.25rem;">
                @foreach($errors->all() as $error)
                    <p style="color:var(--danger); font-size:0.82rem; margin:0.15rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('clients.update', $client) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="section-title">Informacion Personal</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nombre <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $client->name) }}" required>
                    @error('name') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $client->email) }}" required>
                    @error('email') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="tel" name="phone" class="form-input" value="{{ old('phone', $client->phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">WhatsApp</label>
                    <input type="tel" name="whatsapp" class="form-input" value="{{ old('whatsapp', $client->whatsapp) }}" placeholder="55 1234 5678">
                </div>
                <div class="form-group">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="city" class="form-input" value="{{ old('city', $client->city) }}">
                </div>
                <div class="form-group full-width">
                    <label class="form-label">Direccion</label>
                    <textarea name="address" class="form-textarea" rows="2">{{ old('address', $client->address) }}</textarea>
                </div>
            </div>

            <div class="section-title">Clasificacion del Lead</div>
            <div class="form-group">
                <label class="form-label">Tipo de Interes</label>
                @php $clientInterests = old('interest_types', $client->interest_types ?? []); @endphp
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    @foreach(['compra'=>'Compra', 'venta'=>'Venta', 'renta_propietario'=>'Renta (Propietario)', 'renta_inquilino'=>'Renta (Inquilino)'] as $val => $label)
                    <label style="display:flex; align-items:center; gap:0.4rem; font-size:0.88rem; cursor:pointer;">
                        <input type="checkbox" name="interest_types[]" value="{{ $val }}" {{ in_array($val, $clientInterests) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Temperatura del Lead</label>
                    @php $currentTemp = old('lead_temperature', $client->lead_temperature); @endphp
                    <div style="display:flex; gap:0.75rem;">
                        @foreach(['frio'=>'Frio', 'tibio'=>'Tibio', 'caliente'=>'Caliente'] as $val => $label)
                        @php $tempColors = ['frio'=>'#94a3b8', 'tibio'=>'#f59e0b', 'caliente'=>'#ef4444']; @endphp
                        <label style="display:flex; align-items:center; gap:0.35rem; font-size:0.85rem; cursor:pointer; padding:0.35rem 0.7rem; border-radius:20px; border:1px solid var(--border); {{ $currentTemp === $val ? 'background:'.$tempColors[$val].'; color:#fff; border-color:'.$tempColors[$val] : '' }}">
                            <input type="radio" name="lead_temperature" value="{{ $val }}" {{ $currentTemp === $val ? 'checked' : '' }} style="display:none;" onchange="updateTempRadios()">
                            <span style="width:8px; height:8px; border-radius:50%; background:{{ $tempColors[$val] }}; flex-shrink:0;"></span>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Prioridad</label>
                    <select name="priority" class="form-select">
                        <option value="">Sin definir</option>
                        @foreach(['alta'=>'Alta', 'media'=>'Media', 'baja'=>'Baja'] as $val => $label)
                            <option value="{{ $val }}" {{ old('priority', $client->priority) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-title">Asignacion</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Asignado a</label>
                    <div style="padding:0.55rem 0.8rem; background:var(--bg); border-radius:var(--radius); font-size:0.88rem; color:var(--text);">
                        {{ $client->assignedUser?->name ?? Auth::user()->name }} {{ $client->assignedUser?->last_name ?? '' }}
                    </div>
                    <input type="hidden" name="assigned_user_id" value="{{ $client->assigned_user_id ?? Auth::id() }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Broker (opcional)</label>
                    <select name="broker_id" class="form-select">
                        <option value="">Sin asignar</option>
                        @foreach($brokers as $broker)
                            <option value="{{ $broker->id }}" {{ old('broker_id', $client->broker_id) == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="section-title">Preferencias de Busqueda</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de Propiedad</label>
                    <select name="property_type" class="form-select">
                        <option value="">Sin preferencia</option>
                        @foreach(['house'=>'Casa','apartment'=>'Departamento','condo'=>'Condominio','land'=>'Terreno'] as $val => $label)
                            <option value="{{ $val }}" {{ old('property_type', $client->property_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="display:none;"></div>
                <div class="form-group">
                    <label class="form-label">Presupuesto Minimo</label>
                    <input type="number" name="budget_min" class="form-input" value="{{ old('budget_min', $client->budget_min) }}" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label class="form-label">Presupuesto Maximo</label>
                    <input type="number" name="budget_max" class="form-input" value="{{ old('budget_max', $client->budget_max) }}" min="0" step="0.01">
                </div>
            </div>

            <div class="section-title">Notas Iniciales</div>
            <div class="form-group">
                <textarea name="initial_notes" class="form-textarea" rows="3" placeholder="Contexto, necesidades especificas, observaciones...">{{ old('initial_notes', $client->initial_notes) }}</textarea>
            </div>

            <div class="section-title">Origen del Lead</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Canal de Origen</label>
                    <select name="marketing_channel_id" class="form-select" id="channelSelect" onchange="filterCampaigns(this.value)">
                        <option value="">Sin especificar</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->id }}" {{ old('marketing_channel_id', $client->marketing_channel_id) == $channel->id ? 'selected' : '' }}>{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Campana</label>
                    <select name="marketing_campaign_id" class="form-select" id="campaignSelect">
                        <option value="">Sin especificar</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" data-channel="{{ $campaign->marketing_channel_id }}" {{ old('marketing_campaign_id', $client->marketing_campaign_id) == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Costo de Adquisicion</label>
                    <input type="number" name="acquisition_cost" class="form-input" value="{{ old('acquisition_cost', $client->acquisition_cost) }}" min="0" step="0.01" placeholder="0.00">
                </div>
            </div>

            <div class="section-title">Foto</div>
            <div class="form-group">
                <div class="photo-upload" onclick="document.getElementById('photoInput').click()">
                    <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(this)">
                    <div id="photoPreview">
                        @if($client->photo)
                            <img src="{{ asset('storage/' . $client->photo) }}" style="max-height:100px; border-radius:50%;">
                            <p class="form-hint">{{ basename($client->photo) }} — clic para cambiar</p>
                        @else
                            <p class="text-muted" style="margin:0;">Haz clic para seleccionar una foto</p>
                            <p class="form-hint">JPG, PNG, GIF, WebP (max 5MB)</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="meta-info">
                Creado: {{ $client->created_at->format('d/m/Y H:i') }} &middot;
                Actualizado: {{ $client->updated_at->format('d/m/Y H:i') }}
            </div>

            <div class="form-actions">
                <a href="{{ route('clients.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photoPreview').innerHTML = '<img src="' + e.target.result + '" style="max-height:100px; border-radius:50%;"><p class="form-hint" style="margin-top:0.5rem;">' + input.files[0].name + '</p>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function filterCampaigns(channelId) {
    var options = document.querySelectorAll('#campaignSelect option[data-channel]');
    options.forEach(function(opt) {
        opt.style.display = (!channelId || opt.dataset.channel === channelId) ? '' : 'none';
        if (opt.style.display === 'none' && opt.selected) opt.selected = false;
    });
}
function updateTempRadios() {
    var colors = {frio:'#94a3b8', tibio:'#f59e0b', caliente:'#ef4444'};
    document.querySelectorAll('input[name="lead_temperature"]').forEach(function(r) {
        var lbl = r.closest('label');
        if (r.checked) {
            lbl.style.background = colors[r.value]; lbl.style.color = '#fff'; lbl.style.borderColor = colors[r.value];
        } else {
            lbl.style.background = ''; lbl.style.color = ''; lbl.style.borderColor = '';
        }
    });
}
document.addEventListener('DOMContentLoaded', function() {
    var ch = document.getElementById('channelSelect');
    if (ch && ch.value) filterCampaigns(ch.value);
});
</script>
@endsection
