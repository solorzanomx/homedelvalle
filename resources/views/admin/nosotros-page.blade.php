@extends('layouts.app-sidebar')
@section('title', 'Pagina Nosotros')

@section('content')
<div class="page-header">
    <div>
        <h2>Pagina Nosotros</h2>
        <p class="text-muted">Administra el contenido de la pagina publica /nosotros</p>
    </div>
    <a href="{{ url('/nosotros') }}" target="_blank" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:.5rem;">
        <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        Ver pagina
    </a>
</div>

<form method="POST" action="{{ route('admin.nosotros-page.update') }}">
    @csrf

    @php $content = $settings?->nosotros_content ?? []; @endphp

    {{-- 1. Mision y Vision --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Mision y Vision</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Mision</label>
                <textarea name="mission" class="form-textarea" rows="3" placeholder="Nuestra mision...">{{ old('mission', $content['mission'] ?? '') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Vision</label>
                <textarea name="vision" class="form-textarea" rows="3" placeholder="Nuestra vision...">{{ old('vision', $content['vision'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 2. Historia --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Historia</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo de la seccion</label>
                <input type="text" name="story_heading" class="form-input" value="{{ old('story_heading', $content['story_heading'] ?? 'Nuestra Historia') }}" placeholder="Nuestra Historia">
            </div>
            <div class="form-group">
                <label class="form-label">Texto principal</label>
                <textarea name="about_text" class="form-textarea" rows="5" placeholder="Cuenta la historia de la empresa...">{{ old('about_text', $settings->about_text ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 3. Filosofia --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Filosofia</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo de la seccion</label>
                <input type="text" name="philosophy_heading" class="form-input" value="{{ old('philosophy_heading', $content['philosophy_heading'] ?? 'Nuestra Filosofia') }}" placeholder="Nuestra Filosofia">
            </div>
            <div class="form-group">
                <label class="form-label">Texto de filosofia</label>
                <textarea name="philosophy_text" class="form-textarea" rows="4" placeholder="Describe la filosofia de la empresa...">{{ old('philosophy_text', $content['philosophy_text'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 4. Valores --}}
    @php
        $defaultValues = [
            ['title' => '', 'description' => ''],
            ['title' => '', 'description' => ''],
            ['title' => '', 'description' => ''],
            ['title' => '', 'description' => ''],
        ];
        $values = $content['values'] ?? $defaultValues;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Valores</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @foreach($values as $i => $value)
                <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;">
                    <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Valor {{ $i + 1 }}</div>
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="values[{{ $i }}][title]" class="form-input" value="{{ old("values.$i.title", $value['title'] ?? '') }}" placeholder="Nombre del valor">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripcion</label>
                        <input type="text" name="values[{{ $i }}][description]" class="form-input" value="{{ old("values.$i.description", $value['description'] ?? '') }}" placeholder="Descripcion breve del valor">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 5. Estadisticas --}}
    @php
        $defaultStats = [
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
            ['value' => '', 'label' => ''],
        ];
        $stats = $content['stats'] ?? $defaultStats;
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Estadisticas</h3></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @foreach($stats as $i => $stat)
                <div style="background:var(--bg);border-radius:var(--radius);padding:1rem;">
                    <div style="font-weight:600;font-size:0.85rem;color:var(--text-muted);margin-bottom:0.75rem;">Estadistica {{ $i + 1 }}</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Valor</label>
                            <input type="text" name="stats[{{ $i }}][value]" class="form-input" value="{{ old("stats.$i.value", $stat['value'] ?? '') }}" placeholder="Ej: 500+">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Etiqueta</label>
                            <input type="text" name="stats[{{ $i }}][label]" class="form-input" value="{{ old("stats.$i.label", $stat['label'] ?? '') }}" placeholder="Ej: Propiedades vendidas">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 6. Equipo --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>Equipo</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Titulo de la seccion</label>
                <input type="text" name="team_heading" class="form-input" value="{{ old('team_heading', $content['team_heading'] ?? 'Nuestro Equipo') }}" placeholder="Nuestro Equipo">
            </div>
            <div class="form-group">
                <label class="form-label">Subtitulo</label>
                <input type="text" name="team_subheading" class="form-input" value="{{ old('team_subheading', $content['team_subheading'] ?? '') }}" placeholder="Conoce a los profesionales detras de Home del Valle">
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div class="p-save-bar">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
@endsection
