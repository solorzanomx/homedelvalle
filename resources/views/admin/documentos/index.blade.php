@extends('layouts.app-sidebar')
@section('title', 'Documentos')

@section('styles')
<style>
.doc-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; }
.doc-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: .5rem; }
.doc-card h3 { font-size: 1rem; font-weight: 700; margin: 0; }
.doc-card p.desc { color: var(--text-muted); font-size: .85rem; margin: .25rem 0 0; }
.doc-meta { font-size: .75rem; color: var(--text-muted); }
.doc-generar { font-size: .78rem; color: var(--text-muted); background: var(--bg,#f8fafc); border-radius: 8px; padding: .5rem .75rem; margin-top: .75rem; }
.doc-generar strong { color: var(--text); }
.doc-changelog { margin-top: .75rem; }
.doc-changelog summary { cursor: pointer; font-size: .78rem; color: var(--primary); font-weight: 600; }
.doc-changelog-list { list-style: none; margin-top: .6rem; padding-left: 0; }
.doc-changelog-list li { font-size: .8rem; padding: .4rem 0; border-top: 1px solid var(--border); color: var(--text-muted); }
.doc-changelog-list li:first-child { border-top: none; }
.doc-changelog-list .fecha { font-weight: 700; color: var(--text); margin-right: .4rem; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1.25rem;">
    <h2 style="margin:0;font-size:1.3rem;">&#128220; Documentos</h2>
    <p style="margin:0.2rem 0 0;color:var(--text-muted);font-size:0.85rem;">Los documentos con identidad de marca de Home del Valle — dónde se generan y qué ha cambiado en cada uno.</p>
</div>

@foreach($documents as $doc)
<div class="doc-card">
    <div class="doc-card-header">
        <div>
            <h3>{{ $doc['nombre'] }}</h3>
            <p class="desc">{{ $doc['descripcion'] }}</p>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;flex-shrink:0;">
            @if(!empty($doc['editar_route']) && Route::has($doc['editar_route']))
            <a href="{{ route($doc['editar_route']) }}" class="btn btn-sm btn-outline">Editar cláusulas</a>
            @endif
            @if(!empty($doc['imprimible_route']) && Route::has($doc['imprimible_route']))
            <a href="{{ route($doc['imprimible_route']) }}" target="_blank" class="btn btn-sm btn-outline">Versión imprimible</a>
            @endif
            @if($doc['preview_route'] && Route::has($doc['preview_route']))
            <a href="{{ route($doc['preview_route']) }}" target="_blank" class="btn btn-sm btn-primary">Ver PDF</a>
            @endif
            <span class="doc-meta">Actualizado {{ \Carbon\Carbon::parse($doc['ultima_actualizacion']['fecha'])->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="doc-generar">&#128161; <strong>Dónde generarlo:</strong> {{ $doc['donde_generarlo'] }}</div>

    <details class="doc-changelog">
        <summary>Ver historial de cambios ({{ count($doc['changelog']) }})</summary>
        <ul class="doc-changelog-list">
            @foreach($doc['changelog'] as $entry)
            <li><span class="fecha">{{ \Carbon\Carbon::parse($entry['fecha'])->format('d/m/Y') }}</span>{{ $entry['resumen'] }}</li>
            @endforeach
        </ul>
    </details>
</div>
@endforeach
@endsection
