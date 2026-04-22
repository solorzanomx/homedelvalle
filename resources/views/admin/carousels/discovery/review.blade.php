@extends('layouts.app-sidebar')
@section('title', 'Seleccionar temas')

@section('content')
<div class="page-header">
    <div>
        <h2>Temas sugeridos</h2>
        <p class="text-muted">
            Fuentes: {{ $sourcesUsed->map(fn($s) => ['web'=>'Web','blog'=>'Blog','manual'=>'Manual'][$s] ?? $s)->implode(', ') }}
            &nbsp;·&nbsp; {{ $createdAt->format('d/m/Y H:i') }}
            &nbsp;·&nbsp; {{ $suggestions->count() }} sugerencias
        </p>
    </div>
    <a href="{{ route('admin.carousels.discovery.form') }}" class="btn btn-outline">+ Nueva búsqueda</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.carousels.discovery.generate', $session) }}" id="select-form">
@csrf

{{-- Bulk actions bar --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn btn-sm btn-outline" onclick="selectAll(true)">Seleccionar todos</button>
        <button type="button" class="btn btn-sm btn-outline" onclick="selectAll(false)">Ninguno</button>
    </div>
    <div id="selection-info" style="font-size: 0.85rem; color: #6b7280; font-weight: 500;">
        0 seleccionados
    </div>
</div>

{{-- Suggestions grid --}}
<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
    @foreach($suggestions as $s)
    @php
        $relevanceColor = match(true) {
            $s->relevance_score >= 80 => '#16a34a',
            $s->relevance_score >= 60 => '#d97706',
            default                   => '#6b7280',
        };
        $relevanceBg = match(true) {
            $s->relevance_score >= 80 => '#f0fdf4',
            $s->relevance_score >= 60 => '#fffbeb',
            default                   => '#f9fafb',
        };
        $typeColors = [
            'commercial'  => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'educational' => ['bg' => '#f0fdf4', 'text' => '#15803d'],
            'capture'     => ['bg' => '#faf5ff', 'text' => '#7e22ce'],
            'informative' => ['bg' => '#fefce8', 'text' => '#a16207'],
            'branding'    => ['bg' => '#fdf2f8', 'text' => '#9d174d'],
        ];
        $tc = $typeColors[$s->suggested_type] ?? ['bg' => '#f3f4f6', 'text' => '#374151'];
    @endphp
    <label class="suggestion-card" style="
        display: block;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.25rem;
        cursor: pointer;
        background: #fff;
        transition: border-color 0.15s, background 0.15s;
        position: relative;
    " data-id="{{ $s->id }}">
        <input type="checkbox" name="suggestion_ids[]" value="{{ $s->id }}"
               class="suggestion-checkbox"
               style="position: absolute; top: 1rem; right: 1rem; width: 18px; height: 18px; cursor: pointer;"
               onchange="updateSelection()">

        {{-- Top meta row --}}
        <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.875rem; padding-right: 2rem; flex-wrap: wrap;">
            <span style="
                font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                padding: 3px 10px; border-radius: 20px;
                background: {{ $tc['bg'] }}; color: {{ $tc['text'] }};
            ">{{ $s->type_label }}</span>

            <span style="
                font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                padding: 3px 10px; border-radius: 20px;
                background: {{ $relevanceBg }}; color: {{ $relevanceColor }};
            ">{{ $s->relevance_score }}% relevancia</span>

            <span style="font-size: 0.72rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ ['web'=>'Web','blog'=>'Blog','manual'=>'Manual'][$s->source] ?? $s->source }}
            </span>
        </div>

        {{-- Title --}}
        <h3 style="font-size: 1rem; font-weight: 700; color: #111827; line-height: 1.4; margin-bottom: 0.625rem;">
            {{ $s->title }}
        </h3>

        {{-- Description --}}
        <p style="font-size: 0.85rem; color: #4b5563; line-height: 1.6; margin-bottom: 0.75rem;">
            {{ $s->description }}
        </p>

        {{-- Reasoning toggle --}}
        @if($s->reasoning)
        <div>
            <button type="button" class="reasoning-toggle"
                    style="font-size: 0.75rem; color: #6b7280; background: none; border: none; cursor: pointer; padding: 0; font-weight: 600; text-decoration: underline;"
                    onclick="event.preventDefault(); toggleReasoning(this)">
                Ver razonamiento
            </button>
            <div class="reasoning-text" style="display: none; margin-top: 0.5rem; font-size: 0.8rem; color: #6b7280; line-height: 1.5; font-style: italic; border-top: 1px solid #f0f0f0; padding-top: 0.5rem;">
                {{ $s->reasoning }}
            </div>
        </div>
        @endif

        {{-- Keywords --}}
        @if($s->suggested_keywords)
        <div style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; gap: 0.3rem;">
            @foreach($s->suggested_keywords as $kw)
            <span style="font-size: 0.7rem; color: #9ca3af; background: #f3f4f6; padding: 2px 8px; border-radius: 20px;">#{{ $kw }}</span>
            @endforeach
        </div>
        @endif
    </label>
    @endforeach
</div>

{{-- Sticky footer generate bar --}}
<div style="
    position: sticky; bottom: 0;
    background: #fff;
    border-top: 2px solid #e5e7eb;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.06);
    z-index: 10;
">
    <div style="font-size: 0.9rem; color: #6b7280;">
        <span id="footer-count">0</span> temas seleccionados · Se generará 1 carrusel por tema
    </div>
    <button type="submit" id="generate-btn"
            class="btn btn-primary"
            disabled
            style="padding: 0.7rem 2rem; font-size: 0.95rem;"
            onclick="this.disabled=true; this.textContent='Generando…'; this.form.submit();">
        ✦ Generar carruseles seleccionados
    </button>
</div>

</form>

<style>
.suggestion-card:has(.suggestion-checkbox:checked) {
    border-color: #2563eb;
    background: #f0f5ff;
}
.suggestion-card:hover {
    border-color: #93c5fd;
}
</style>

<script>
function updateSelection() {
    const checked = document.querySelectorAll('.suggestion-checkbox:checked');
    const count = checked.length;
    document.getElementById('selection-info').textContent = count + ' seleccionado' + (count === 1 ? '' : 's');
    document.getElementById('footer-count').textContent = count;
    document.getElementById('generate-btn').disabled = count === 0;
}

function selectAll(state) {
    document.querySelectorAll('.suggestion-checkbox').forEach(cb => cb.checked = state);
    updateSelection();
}

function toggleReasoning(btn) {
    const text = btn.nextElementSibling;
    const isHidden = text.style.display === 'none';
    text.style.display = isHidden ? 'block' : 'none';
    btn.textContent = isHidden ? 'Ocultar razonamiento' : 'Ver razonamiento';
}
</script>
@endsection
