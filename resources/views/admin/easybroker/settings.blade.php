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
@endsection
