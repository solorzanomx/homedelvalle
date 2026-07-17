@extends('layouts.app-sidebar')
@section('title', 'Configuracion EasyBroker')

@section('content')
<div class="page-header">
    <div>
        <h2>EasyBroker</h2>
        <p class="text-muted">Configura la integracion con EasyBroker para publicar propiedades automaticamente</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.easybroker.properties') }}" class="btn btn-outline">&#128203; Publicadas en EasyBroker</a>
        <a href="{{ route('properties.index') }}" class="btn btn-outline">&#127968; Propiedades</a>
    </div>
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
                            Ubicacion por defecto (opcional)
                        </div>
                        <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:1rem;">
                            EasyBroker ubica por nombre de catálogo (colonia o alcaldía), no por IDs.
                            Esta ubicación se usa cuando una propiedad no tiene colonia asignada; si se
                            deja vacía se usa "Benito Juárez, Ciudad de México".
                        </div>

                        {{-- Auto-detect from existing EB properties --}}
                        <div style="margin-bottom:1rem;">
                            <button type="button" id="eb-detect-btn" class="btn btn-outline" style="width:100%; justify-content:center;" onclick="ebDetectLocation()">
                                &#128270; Detectar desde mis propiedades en EasyBroker
                            </button>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.4rem;">Si ya tienes propiedades en EasyBroker, toma la ubicación de la primera.</p>
                        </div>

                        {{-- Location search --}}
                        <div class="form-group">
                            <label class="form-label">O buscar en el catálogo de EasyBroker</label>
                            <div style="display:flex; gap:0.5rem;">
                                <input type="text" id="eb-location-search" class="form-input" placeholder="Ej: Benito Juárez, Ciudad de México" style="flex:1;">
                                <button type="button" class="btn btn-outline" onclick="ebSearchLocations()">Buscar</button>
                            </div>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.4rem;">Busca una alcaldía o ciudad para ver sus colonias; haz clic en una para seleccionarla.</p>
                            <div id="eb-location-results" style="display:none; margin-top:0.5rem; border:1px solid var(--border); border-radius:8px; max-height:200px; overflow-y:auto; background:#fff;"></div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ubicación por defecto (formato EasyBroker)</label>
                            <input type="text" name="default_location_name" id="eb-location-name" class="form-input"
                                value="{{ old('default_location_name', $ebSettings->default_location_name ?? '') }}"
                                placeholder="Benito Juárez, Ciudad de México">
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
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

                    <div class="form-group">
                        <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                            <input type="hidden" name="auto_sync_leads" value="0">
                            <input type="checkbox" name="auto_sync_leads" value="1"
                                   {{ old('auto_sync_leads', $ebSettings->auto_sync_leads ?? false) ? 'checked' : '' }}
                                   style="width:16px; height:16px; accent-color:var(--primary);">
                            <span class="form-label" style="margin:0;">Sincronización automática de leads (cada 30 min)</span>
                        </label>
                        <p class="form-hint" style="margin-top:0.35rem;">Apagado por defecto: los portales generan mucho volumen de consultas. Usa primero el botón de prueba manual (panel derecho) para ver cuántos llegan.</p>
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
            <div class="card-header"><h3>Leads de portales (prueba manual)</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.easybroker.sync-leads') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width:100%; justify-content:center;">
                        &#128229; Traer leads de los últimos 7 días
                    </button>
                </form>
                <p class="form-hint" style="margin-top:0.75rem;">
                    Importa las solicitudes de contacto recientes de EasyBroker a
                    <a href="{{ route('admin.form-submissions.index') }}">Leads &amp; Formularios</a>
                    (tipo "EasyBroker"). No envía ningún correo a los leads y nunca duplica.
                    Cuando estés conforme, enciende la sincronización automática (panel izquierdo).
                </p>
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
        .then(resp => {
            // La API devuelve la ubicación raíz encontrada y sus hijas
            // (localities): país→estados, estado→ciudades, ciudad→colonias.
            const root = resp.data ?? {};
            const options = [];
            if (root.full_name) options.push(root);
            (root.localities ?? []).forEach(l => options.push(l));

            if (!options.length) {
                const msg = resp.message ? ' (' + resp.message + ')' : '';
                resultsDiv.innerHTML = '<div style="padding:0.75rem; color:var(--text-muted); font-size:0.85rem;">Sin resultados' + msg + '</div>';
                return;
            }
            resultsDiv.innerHTML = options.map(loc => `
                <div style="padding:0.6rem 0.75rem; cursor:pointer; border-bottom:1px solid var(--border); font-size:0.85rem;"
                    onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background=''"
                    onclick="ebSelectLocation(this.dataset.fullName)" data-full-name="${(loc.full_name ?? '').replace(/"/g, '&quot;')}">
                    <span style="font-weight:500;">${loc.name ?? ''}</span>
                    <span style="color:var(--text-muted); margin-left:0.5rem; font-size:0.78rem;">
                        ${loc.full_name ?? ''} &bull; ${loc.type ?? ''}
                    </span>
                </div>
            `).join('');
        })
        .catch((e) => {
            resultsDiv.innerHTML = '<div style="padding:0.75rem; color:#dc2626; font-size:0.85rem;">Error al buscar: ' + e.message + '</div>';
        });
}

function ebSelectLocation(fullName) {
    if (!fullName) return;
    document.getElementById('eb-location-name').value = fullName;
    document.getElementById('eb-location-results').style.display = 'none';
}

function ebDetectLocation() {
    const btn = document.getElementById('eb-detect-btn');
    btn.textContent = 'Detectando...';
    btn.disabled = true;

    fetch('{{ route('admin.easybroker.detect-location') }}')
        .then(r => r.json())
        .then(resp => {
            if (resp.success && resp.data) {
                const d = resp.data;
                if (d.location_name) document.getElementById('eb-location-name').value = d.location_name;
                btn.textContent = '✓ Detectado: ' + (d.location_name ?? '') + ' (desde: ' + (d.source ?? 'EasyBroker') + ')';
                btn.style.color = 'var(--success)';
                btn.style.borderColor = 'var(--success)';
            } else {
                btn.textContent = resp.message ?? 'No se pudo detectar';
                btn.style.color = 'var(--danger)';
                setTimeout(() => { btn.textContent = 'Detectar desde mis propiedades'; btn.disabled = false; btn.style.color=''; btn.style.borderColor=''; }, 3000);
                return;
            }
            btn.disabled = false;
        })
        .catch(() => {
            btn.textContent = 'Error de conexión';
            btn.style.color = 'var(--danger)';
            setTimeout(() => { btn.textContent = 'Detectar desde mis propiedades'; btn.disabled = false; btn.style.color=''; btn.style.borderColor=''; }, 3000);
        });
}

document.getElementById('eb-location-search')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); ebSearchLocations(); }
});
</script>
@endsection
