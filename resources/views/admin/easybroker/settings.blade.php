@extends('layouts.app-sidebar')
@section('title', 'Configuracion EasyBroker')

@section('content')
<div class="page-header">
    <div>
        <h2>EasyBroker</h2>
        <p class="text-muted">Configura la integracion con EasyBroker para publicar propiedades automaticamente</p>
    </div>
    <a href="{{ route('properties.index') }}" class="btn btn-outline">&#127968; Propiedades</a>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start;">
    {{-- API Config --}}
    <div class="card">
        <div class="card-header"><h3>Configuracion de API</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.easybroker.settings.update') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">API Key <span class="required">*</span></label>
                        <input type="password" name="api_key" class="form-input"
                               placeholder="{{ $ebSettings && $ebSettings->api_key ? '••••••••  (dejar vacio para no cambiar)' : 'Ingresa tu API Key de EasyBroker' }}">
                        <p class="form-hint">Encuentrala en tu cuenta de EasyBroker > Integraciones > API.</p>
                        @error('api_key') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group full-width">
                        <label class="form-label">URL Base</label>
                        <input type="url" name="base_url" class="form-input"
                               value="{{ old('base_url', $ebSettings->base_url ?? 'https://api.easybroker.com/v1') }}" required>
                        @error('base_url') <p class="form-hint" style="color:var(--danger)">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Propiedad por Defecto</label>
                        <select name="default_property_type" class="form-input">
                            @foreach(['House' => 'Casa', 'Apartment' => 'Departamento', 'Land' => 'Terreno', 'Office' => 'Oficina', 'Commercial' => 'Comercial', 'Warehouse' => 'Bodega', 'Building' => 'Edificio'] as $val => $label)
                                <option value="{{ $val }}" {{ old('default_property_type', $ebSettings->default_property_type ?? 'House') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tipo de Operacion por Defecto</label>
                        <select name="default_operation_type" class="form-input">
                            @foreach(['sale' => 'Venta', 'rental' => 'Renta', 'temporary_rental' => 'Renta Temporal'] as $val => $label)
                                <option value="{{ $val }}" {{ old('default_operation_type', $ebSettings->default_operation_type ?? 'sale') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Moneda por Defecto</label>
                        <select name="default_currency" class="form-input">
                            @foreach(['MXN' => 'MXN (Peso Mexicano)', 'USD' => 'USD (Dolar)'] as $val => $label)
                                <option value="{{ $val }}" {{ old('default_currency', $ebSettings->default_currency ?? 'MXN') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Location defaults --}}
                    <div style="margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid var(--border);">
                        <div style="font-size:0.8rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:1rem;">
                            Ubicacion por defecto (requerida por EasyBroker)
                        </div>
                        <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:1rem;">
                            Busca tu ciudad y colonia en el campo de abajo para obtener los IDs que EasyBroker requiere.
                        </div>

                        {{-- Location search --}}
                        <div class="form-group">
                            <label class="form-label">Buscar ubicacion</label>
                            <div style="display:flex; gap:0.5rem;">
                                <input type="text" id="eb-location-search" class="form-input" placeholder="Ej: Benito Juarez, Ciudad de Mexico..." style="flex:1;">
                                <button type="button" class="btn btn-outline" onclick="ebSearchLocations()">Buscar</button>
                            </div>
                            <div id="eb-location-results" style="display:none; margin-top:0.5rem; border:1px solid var(--border); border-radius:8px; max-height:200px; overflow-y:auto; background:#fff;"></div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">City ID</label>
                                <input type="text" name="default_city_id" id="eb-city-id" class="form-input"
                                    value="{{ old('default_city_id', $ebSettings->default_city_id ?? '') }}"
                                    placeholder="Ej: 153">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Administrative Division ID</label>
                                <input type="text" name="default_admin_division_id" id="eb-admin-div-id" class="form-input"
                                    value="{{ old('default_admin_division_id', $ebSettings->default_admin_division_id ?? '') }}"
                                    placeholder="Ej: 14">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Latitud por defecto</label>
                                <input type="number" name="default_latitude" class="form-input" step="0.0000001"
                                    value="{{ old('default_latitude', $ebSettings->default_latitude ?? '') }}"
                                    placeholder="Ej: 19.3993552">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Longitud por defecto</label>
                                <input type="number" name="default_longitude" class="form-input" step="0.0000001"
                                    value="{{ old('default_longitude', $ebSettings->default_longitude ?? '') }}"
                                    placeholder="Ej: -99.1703795">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; padding-top:1.5rem;">
                            <input type="hidden" name="auto_publish" value="0">
                            <input type="checkbox" name="auto_publish" value="1"
                                   {{ old('auto_publish', $ebSettings->auto_publish ?? false) ? 'checked' : '' }}
                                   style="width:16px; height:16px; accent-color:var(--primary);">
                            <span class="form-label" style="margin:0;">Auto-publicar al crear</span>
                        </label>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Configuracion</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Status & Test --}}
    <div>
        <div class="card">
            <div class="card-header"><h3>Estado de la Configuracion</h3></div>
            <div class="card-body">
                @if($ebSettings && $ebSettings->api_key)
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                        <div style="width:10px; height:10px; border-radius:50%; background:var(--success);"></div>
                        <span>API Key configurada</span>
                    </div>
                    <div style="font-size:0.85rem; color:var(--text-muted);">
                        <p><strong>URL Base:</strong> {{ $ebSettings->base_url }}</p>
                        <p><strong>Tipo por defecto:</strong> {{ $ebSettings->default_property_type }}</p>
                        <p><strong>Operacion:</strong> {{ $ebSettings->default_operation_type }}</p>
                        <p><strong>Moneda:</strong> {{ $ebSettings->default_currency }}</p>
                    </div>
                @else
                    <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1rem;">
                        <div style="width:10px; height:10px; border-radius:50%; background:var(--danger);"></div>
                        <span>API Key no configurada</span>
                    </div>
                    <p class="text-muted" style="font-size:0.85rem;">Ingresa tu API Key de EasyBroker para comenzar a publicar propiedades.</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Probar Conexion</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.easybroker.settings.test') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width:100%; justify-content:center;">
                        &#9889; Verificar Conexion con EasyBroker
                    </button>
                </form>
                <p class="form-hint" style="margin-top:0.75rem;">Envia una solicitud de prueba al API para verificar que la API Key es valida.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Informacion</h3></div>
            <div class="card-body" style="font-size:0.82rem; color:var(--text-muted);">
                <p style="margin-bottom:0.5rem;"><strong>Mapeo de campos:</strong></p>
                <ul style="margin-left:1rem; margin-bottom:0.75rem;">
                    <li>Titulo &rarr; title</li>
                    <li>Descripcion &rarr; description</li>
                    <li>Colonia + Ciudad &rarr; location.name</li>
                    <li>Direccion &rarr; location.street</li>
                    <li>Codigo Postal &rarr; location.postal_code</li>
                    <li>Precio + Moneda &rarr; operations[0]</li>
                    <li>Area &rarr; construction_size</li>
                    <li>Estacionamiento &rarr; parking_spaces</li>
                </ul>
                <p style="margin-bottom:0.5rem;"><strong>Nota:</strong></p>
                <p>EasyBroker no permite eliminar propiedades. Al "despublicar" se cambia el status a "not_published".</p>
            </div>
        </div>
    </div>
</div>

<script>
function ebSearchLocations() {
    const q = document.getElementById('eb-location-search').value.trim();
    if (q.length < 2) return;

    const resultsDiv = document.getElementById('eb-location-results');
    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = '<div style="padding:0.75rem; color:var(--text-muted); font-size:0.85rem;">Buscando...</div>';

    fetch('{{ route('admin.easybroker.locations') }}?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                resultsDiv.innerHTML = '<div style="padding:0.75rem; color:var(--text-muted); font-size:0.85rem;">Sin resultados</div>';
                return;
            }
            resultsDiv.innerHTML = data.map(loc => `
                <div style="padding:0.6rem 0.75rem; cursor:pointer; border-bottom:1px solid var(--border); font-size:0.85rem;"
                    onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background=''"
                    onclick="ebSelectLocation(${JSON.stringify(loc)})">
                    <span style="font-weight:500;">${loc.name ?? loc.title ?? ''}</span>
                    <span style="color:var(--text-muted); margin-left:0.5rem; font-size:0.78rem;">
                        ID ciudad: ${loc.city_id ?? loc.id ?? '?'} | Div: ${loc.administrative_division_id ?? '?'}
                    </span>
                </div>
            `).join('');
        })
        .catch(() => {
            resultsDiv.innerHTML = '<div style="padding:0.75rem; color:#dc2626; font-size:0.85rem;">Error al buscar</div>';
        });
}

function ebSelectLocation(loc) {
    if (loc.city_id)                    document.getElementById('eb-city-id').value = loc.city_id;
    if (loc.administrative_division_id) document.getElementById('eb-admin-div-id').value = loc.administrative_division_id;
    document.getElementById('eb-location-results').style.display = 'none';
    document.getElementById('eb-location-search').value = loc.name ?? loc.title ?? '';
}

document.getElementById('eb-location-search')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); ebSearchLocations(); }
});
</script>
@endsection
