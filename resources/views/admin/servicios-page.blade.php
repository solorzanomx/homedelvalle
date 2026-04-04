@extends('layouts.app-sidebar')
@section('title', 'Pagina de Servicios')

@section('content')
<div class="page-header">
    <div>
        <h2>Pagina de Servicios</h2>
        <p class="text-muted">Administra el contenido de la pagina publica /servicios</p>
    </div>
    <a href="{{ url('/servicios') }}" target="_blank" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:.5rem;">
        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        Ver pagina
    </a>
</div>

<form method="POST" action="{{ route('admin.servicios-page.update') }}">
    @csrf

    @php $content = $settings?->servicios_content ?? []; @endphp

    {{-- General --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Encabezado de Pagina</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo principal</label>
                <input type="text" name="heading" class="form-input" value="{{ old('heading', $content['heading'] ?? 'Nuestros Servicios') }}" placeholder="Nuestros Servicios">
            </div>
            <div class="form-group">
                <label class="form-label">Subtitulo</label>
                <textarea name="subheading" class="form-textarea" rows="2" placeholder="Descripcion breve...">{{ old('subheading', $content['subheading'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Services --}}
    @php
        $defaultServices = [
            ['title' => 'Desarrollo Inmobiliario', 'slug' => 'desarrollo-inmobiliario', 'icon' => 'building', 'description' => '', 'features' => ['','','',''], 'cta_text' => 'Solicitar informacion', 'cta_url' => '/contacto'],
            ['title' => 'Corretaje Premium', 'slug' => 'corretaje-premium', 'icon' => 'key', 'description' => '', 'features' => ['','','',''], 'cta_text' => 'Solicitar informacion', 'cta_url' => '/contacto'],
            ['title' => 'Administracion de Inmuebles', 'slug' => 'administracion', 'icon' => 'chart', 'description' => '', 'features' => ['','','',''], 'cta_text' => 'Solicitar informacion', 'cta_url' => '/contacto'],
            ['title' => 'Legal y Gestoria', 'slug' => 'legal-gestoria', 'icon' => 'shield', 'description' => '', 'features' => ['','','',''], 'cta_text' => 'Solicitar informacion', 'cta_url' => '/contacto'],
            ['title' => 'Property Transformation', 'slug' => 'property-transformation', 'icon' => 'sparkle', 'description' => '', 'features' => ['','','',''], 'cta_text' => 'Solicitar informacion', 'cta_url' => '/contacto'],
        ];
        $services = $content['services'] ?? $defaultServices;
        $iconOptions = ['building' => 'Edificio', 'key' => 'Llave', 'chart' => 'Grafica', 'shield' => 'Escudo', 'sparkle' => 'Estrella'];
    @endphp

    @foreach($services as $i => $service)
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}. {{ $service['title'] ?? 'Servicio ' . ($i+1) }}</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo</label>
                    <input type="text" name="services[{{ $i }}][title]" class="form-input" value="{{ old("services.$i.title", $service['title'] ?? '') }}">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Slug (URL)</label>
                        <input type="text" name="services[{{ $i }}][slug]" class="form-input" value="{{ old("services.$i.slug", $service['slug'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Icono</label>
                        <select name="services[{{ $i }}][icon]" class="form-select">
                            @foreach($iconOptions as $val => $label)
                            <option value="{{ $val }}" {{ ($service['icon'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Descripcion</label>
                <textarea name="services[{{ $i }}][description]" class="form-textarea" rows="3">{{ old("services.$i.description", $service['description'] ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Caracteristicas (una por campo)</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem;">
                    @for($f = 0; $f < 4; $f++)
                    <input type="text" name="services[{{ $i }}][features][]" class="form-input" value="{{ old("services.$i.features.$f", $service['features'][$f] ?? '') }}" placeholder="Caracteristica {{ $f+1 }}">
                    @endfor
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Texto del boton CTA</label>
                    <input type="text" name="services[{{ $i }}][cta_text]" class="form-input" value="{{ old("services.$i.cta_text", $service['cta_text'] ?? 'Solicitar informacion') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">URL del boton CTA</label>
                    <input type="text" name="services[{{ $i }}][cta_url]" class="form-input" value="{{ old("services.$i.cta_url", $service['cta_url'] ?? '/contacto') }}">
                </div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- CTA Section --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Seccion CTA Final</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo CTA</label>
                <input type="text" name="cta_heading" class="form-input" value="{{ old('cta_heading', $content['cta_heading'] ?? '¿Tienes una propiedad?') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Subtitulo CTA</label>
                <textarea name="cta_subheading" class="form-textarea" rows="2">{{ old('cta_subheading', $content['cta_subheading'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div class="p-save-bar">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
@endsection
