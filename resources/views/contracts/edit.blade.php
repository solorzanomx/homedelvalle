@extends('layouts.app-sidebar')
@section('title', 'Editar Documento — ' . $contract->title)

@php
    $dealUrl = $contract->operation_id
        ? route('operations.show', $contract->operation_id)
        : route('rentals.show', $contract->rental_process_id);
    $generateVersionUrl = $contract->operation_id
        ? route('operations.contracts.generate-version', [$contract->operation_id, $contract->id])
        : route('rentals.contracts.generate-version', [$contract->rental_process_id, $contract->id]);
    $caratula = $contract->clauses->firstWhere('section', 'caratula');
    $declaraciones = $contract->clauses->where('section', 'declaracion')->sortBy('sort_order')->values();
    $clausulas = $contract->clauses->where('section', 'clausula')->sortBy('sort_order')->values();
    $firma = $contract->clauses->firstWhere('section', 'firma');
@endphp

@section('styles')
<style>
@include('pdf._contract_style')

.doc-toolbar { display:flex; align-items:center; gap:0.6rem; margin-bottom:1rem; flex-wrap:wrap; }
.doc-sheet { max-width: 820px; margin: 0 auto; background:#fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,.08), 0 8px 24px rgba(0,0,0,.06); padding: 40px 56px; color:#1e293b; }
@media (prefers-color-scheme: dark) { .doc-sheet { color:#1e293b; } }

.doc-block { position: relative; padding: 4px 0; border-radius: 6px; }
.doc-block:hover { background: rgba(37,99,235,0.04); }
.doc-block-controls { position: absolute; top: 2px; right: -4px; display:none; gap:4px; }
.doc-block:hover .doc-block-controls { display:flex; }
.doc-block-controls button { font-size: 0.68rem; padding: 2px 7px; border-radius: 4px; border:1px solid var(--border); background:#fff; cursor:pointer; }
.doc-block-controls button:hover { border-color: var(--primary); color: var(--primary); }

.doc-edit-form { display:none; margin: 6px 0 12px; padding: 10px; border:1px solid var(--border); border-radius: 8px; background: var(--bg); }
.doc-edit-form input[type=text] { width:100%; margin-bottom:6px; padding:6px 8px; border:1px solid var(--border); border-radius:6px; font-size:0.85rem; }

.insert-bar { position: relative; height: 14px; margin: 2px 0; text-align:center; }
.insert-bar .insert-btn { display:none; font-size: 0.68rem; padding: 1px 8px; border-radius: 10px; border:1px dashed var(--primary); background:#fff; color:var(--primary); cursor:pointer; }
.insert-bar:hover .insert-btn { display:inline-block; }
.insert-form { display:none; margin: 4px 0 10px; padding: 10px; border:1px dashed var(--primary); border-radius: 8px; background: rgba(37,99,235,0.03); }
.insert-form input[type=text] { width:100%; margin-bottom:6px; padding:6px 8px; border:1px solid var(--border); border-radius:6px; font-size:0.85rem; }
</style>
@endsection

@section('content')
<div class="doc-toolbar">
    <a href="{{ $dealUrl }}" class="btn btn-outline btn-sm">&#8592; Volver al trato</a>
    <div style="flex:1;"></div>
    <form method="POST" action="{{ $generateVersionUrl }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('¿Generar una nueva versión del PDF con las cláusulas actuales?')">Generar nueva versión</button>
    </form>
</div>

<div class="doc-sheet">

    {{-- Carátula --}}
    <div class="doc-block" id="clause-{{ $caratula?->id }}">
        @if($caratula)
        <div class="doc-block-controls">
            <button type="button" onclick="toggleEdit({{ $caratula->id }})">Editar carátula</button>
        </div>
        <div id="view-{{ $caratula->id }}">{!! $caratula->body !!}</div>
        <div class="doc-edit-form" id="edit-{{ $caratula->id }}">
            <form method="POST" action="{{ route('contracts.clauses.update', [$contract->id, $caratula->id]) }}">
                @csrf @method('PUT')
                <input type="hidden" name="title" value="{{ $caratula->title }}">
                <textarea name="body" id="body-{{ $caratula->id }}" class="doc-tinymce">{{ $caratula->body }}</textarea>
                <div style="margin-top:6px; display:flex; gap:6px;">
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="toggleEdit({{ $caratula->id }})">Cancelar</button>
                </div>
            </form>
        </div>
        @else
        <p style="color:var(--text-muted); font-size:0.85rem;">Este contrato no tiene carátula editable (fue generado antes de esta función) — se sigue mostrando automática en el PDF.</p>
        @endif
    </div>

    {{-- Declaraciones --}}
    @if($declaraciones->isNotEmpty())
    <div class="section-title first">Declaraciones</div>
    @foreach($declaraciones as $clause)
        @include('contracts._doc-clause-block', ['clause' => $clause, 'contract' => $contract])
        @include('contracts._doc-insert-bar', ['afterClauseId' => $clause->id, 'section' => 'declaracion', 'contract' => $contract])
    @endforeach
    @endif

    {{-- Cláusulas --}}
    @if($clausulas->isNotEmpty())
    <div class="section-title">Cláusulas</div>
    @foreach($clausulas as $clause)
        @include('contracts._doc-clause-block', ['clause' => $clause, 'contract' => $contract])
        @include('contracts._doc-insert-bar', ['afterClauseId' => $clause->id, 'section' => 'clausula', 'contract' => $contract])
    @endforeach
    @endif

    {{-- Firma --}}
    @if($firma)
    <div class="doc-block" id="clause-{{ $firma->id }}">
        <div class="doc-block-controls">
            <button type="button" onclick="toggleEdit({{ $firma->id }})">Editar</button>
        </div>
        <div id="view-{{ $firma->id }}"><span class="ctitle">{{ $firma->title }}</span>{!! $firma->body !!}</div>
        <div class="doc-edit-form" id="edit-{{ $firma->id }}">
            <form method="POST" action="{{ route('contracts.clauses.update', [$contract->id, $firma->id]) }}">
                @csrf @method('PUT')
                <input type="text" name="title" value="{{ $firma->title }}">
                <textarea name="body" id="body-{{ $firma->id }}" class="doc-tinymce">{{ $firma->body }}</textarea>
                <div style="margin-top:6px; display:flex; gap:6px;">
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="toggleEdit({{ $firma->id }})">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
function initDocTinyMCE(id) {
    if (tinymce.get(id)) return;
    tinymce.init({
        selector: '#' + id,
        height: 200,
        menubar: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | bold italic underline | bullist numlist | code',
        content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 13px; padding: 6px; }',
        branding: false,
        license_key: 'gpl',
        relative_urls: false,
        setup: function(editor) {
            var form = editor.getElement().closest('form');
            if (form) { form.addEventListener('submit', function() { editor.save(); }); }
        }
    });
}

function toggleEdit(clauseId) {
    var view = document.getElementById('view-' + clauseId);
    var edit = document.getElementById('edit-' + clauseId);
    var isHidden = edit.style.display === 'none' || !edit.style.display;
    edit.style.display = isHidden ? 'block' : 'none';
    view.style.display = isHidden ? 'none' : 'block';
    if (isHidden) { initDocTinyMCE('body-' + clauseId); }
}

function toggleInsert(afterId) {
    var wrap = document.getElementById('insert-form-' + afterId);
    var isHidden = wrap.style.display === 'none' || !wrap.style.display;
    wrap.style.display = isHidden ? 'block' : 'none';
    if (isHidden) { initDocTinyMCE('insert-body-' + afterId); }
}
</script>
@endsection
