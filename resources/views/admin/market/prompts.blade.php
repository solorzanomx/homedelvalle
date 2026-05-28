@extends('layouts.app-sidebar')
@section('title', 'Prompts del Observatorio')

@section('styles')
.prompt-grid        { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.prompt-card        { background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem 1.4rem; }
.prompt-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.6rem; }
.prompt-label       { font-size: 0.9rem; font-weight: 700; color: var(--text); }
.prompt-badge       { font-size: 0.65rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
                       padding: 2px 9px; border-radius: 20px; white-space: nowrap; }
.badge-perplexity   { background: #faf5ff; border: 1px solid #d8b4fe; color: #7c3aed; }
.badge-claude       { background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; }
.prompt-desc        { font-size: 0.73rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5; }
.prompt-vars        { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 0.75rem; }
.var-chip           { font-size: 0.65rem; font-family: monospace; background: var(--bg);
                       border: 1px solid var(--border); border-radius: 4px; padding: 1px 6px;
                       color: var(--text-muted); }
textarea.prompt-input {
    width: 100%; min-height: 280px; font-family: monospace; font-size: 0.75rem;
    line-height: 1.6; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px;
    background: var(--bg); color: var(--text); resize: vertical;
}
textarea.prompt-input:focus { outline: 2px solid #6366f1; border-color: transparent; }
.prompt-actions     { display: flex; gap: 0.5rem; margin-top: 0.75rem; justify-content: flex-end; }
.changed-badge      { font-size: 0.68rem; color: #d97706; background: #fffbeb;
                       border: 1px solid #fde68a; border-radius: 4px; padding: 2px 7px;
                       align-self: center; }
@media (max-width: 900px) { .prompt-grid { grid-template-columns: 1fr; } }
@endsection

@section('content')

<div class="page-header">
    <div>
        <h2>Prompts del Observatorio de Precios</h2>
        <p class="text-muted" style="margin:0;">
            Instrucciones enviadas a Perplexity (búsqueda) y Claude (análisis) para obtener precios de mercado.
            Edita sin tocar código — los cambios se aplican en la próxima actualización.
        </p>
    </div>
    <a href="{{ route('admin.market.prices') }}" class="btn btn-outline">← Volver a Precios</a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">
    ✓ {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius);padding:.65rem 1rem;font-size:.8rem;color:#1e40af;margin-bottom:1.5rem;">
    💡 Las variables entre <code>{llaves}</code> son reemplazadas automáticamente por el sistema.
    No las elimines. Puedes cambiar el texto alrededor, agregar portales, ajustar instrucciones, etc.
</div>

<div class="prompt-grid">

@php
$promptMeta = [
    'sale.search'   => ['label' => 'Búsqueda de venta',    'badge' => 'Perplexity · sonar-pro', 'class' => 'badge-perplexity'],
    'sale.analysis' => ['label' => 'Análisis de venta',    'badge' => 'Claude Haiku',           'class' => 'badge-claude'],
    'rent.search'   => ['label' => 'Búsqueda de renta',    'badge' => 'Perplexity · sonar-pro', 'class' => 'badge-perplexity'],
    'rent.analysis' => ['label' => 'Análisis de renta',    'badge' => 'Claude Haiku',           'class' => 'badge-claude'],
];
$variables = App\Models\MarketPromptTemplate::variables();
@endphp

@foreach($promptMeta as $key => $meta)
@php $p = $prompts[$key] ?? null; @endphp
<div class="prompt-card">
    <div class="prompt-card-header">
        <div>
            <div class="prompt-label">{{ $meta['label'] }}</div>
        </div>
        <div style="display:flex;gap:.4rem;align-items:center;">
            @if($p && $p->prompt_text !== $p->default_text)
            <span class="changed-badge">Modificado</span>
            @endif
            <span class="prompt-badge {{ $meta['class'] }}">{{ $meta['badge'] }}</span>
        </div>
    </div>

    @if(!empty($variables[$key]))
    <div class="prompt-vars">
        @foreach($variables[$key] as $var)
        <span class="var-chip">{{ $var }}</span>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('admin.market.prompts.update', $key) }}">
        @csrf
        @method('PATCH')
        <textarea name="prompt_text"
                  class="prompt-input"
                  spellcheck="false">{{ $p?->prompt_text ?? '' }}</textarea>
        @error('prompt_text')
        <div style="font-size:.75rem;color:#dc2626;margin-top:4px;">{{ $message }}</div>
        @enderror
        <div class="prompt-actions">
            @if($p && $p->prompt_text !== $p->default_text)
            <a href="{{ route('admin.market.prompts.reset', $key) }}"
               class="btn btn-outline btn-sm"
               onclick="return confirm('¿Restaurar el prompt al texto original?')"
               style="font-size:.75rem;">
                ↺ Restaurar default
            </a>
            @endif
            <button type="submit" class="btn btn-primary btn-sm" style="font-size:.75rem;">
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endforeach

</div>
@endsection
