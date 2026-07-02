@extends('layouts.app-sidebar')
@section('title', 'Editar cláusulas — Carta Oferta de Compra')

@section('styles')
<style>
.clause-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; }
.clause-card label { display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 0.4rem; }
.clause-card textarea { width: 100%; min-height: 110px; padding: 0.6rem 0.75rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.85rem; line-height: 1.5; background: var(--bg,#fff); color: var(--text); resize: vertical; }
.clause-hint { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.4rem; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1.25rem;">
    <h2 style="margin:0;font-size:1.3rem;">&#9998; Editar cláusulas — Carta Oferta de Compra</h2>
    <p style="margin:0.2rem 0 0;color:var(--text-muted);font-size:0.85rem;">
        Estas 5 cláusulas se usan en toda oferta que se genere de aquí en adelante — no afecta a ofertas ya generadas.
        @if($lastUpdated)
            Última edición: {{ $lastUpdated->updated_at->format('d/m/Y H:i') }}{{ $lastUpdated->updatedBy ? ' por ' . $lastUpdated->updatedBy->name : '' }}.
        @endif
    </p>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="alert" style="background:#fffbeb;border:1px solid #fde68a;color:#78350f;padding:.75rem 1rem;border-radius:8px;margin-bottom:1.25rem;font-size:.82rem;">
    &#9888;&#65039; Se recomienda que un abogado revise cualquier cambio a estas cláusulas antes de usarlas con compradores reales — especialmente la de apartado.
    Puedes usar <code>&lt;strong&gt;texto&lt;/strong&gt;</code> para negritas. La cláusula de Vigencia acepta <code>@{{vigencia_dias}}</code> y <code>@{{vigencia_hasta}}</code>, que se rellenan automáticamente con los datos de cada oferta.
</div>

<form method="POST" action="{{ route('admin.documentos.oferta-compra.clausulas.update') }}">
    @csrf
    @foreach($clauses as $clause)
    <div class="clause-card">
        <label for="clause-{{ $clause['key'] }}">{{ $clause['label'] }}</label>
        <textarea id="clause-{{ $clause['key'] }}" name="{{ $clause['key'] }}">{{ old($clause['key'], $clause['value']) }}</textarea>
        @if($clause['key'] === 'vigencia')
        <p class="clause-hint">Tokens disponibles: <code>@{{vigencia_dias}}</code>, <code>@{{vigencia_hasta}}</code></p>
        @endif
    </div>
    @endforeach

    <div style="display:flex;gap:.6rem;">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="{{ route('admin.documentos.index') }}" class="btn btn-outline">Cancelar</a>
    </div>
</form>
@endsection
