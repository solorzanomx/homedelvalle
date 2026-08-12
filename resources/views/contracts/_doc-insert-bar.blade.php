{{-- Barra delgada entre cláusulas para insertar una nueva justo ahí. --}}
<div class="insert-bar">
    <button type="button" class="insert-btn" onclick="toggleInsert({{ $afterClauseId }})">+ Agregar cláusula aquí</button>
</div>
<div class="insert-form" id="insert-form-{{ $afterClauseId }}">
    <form method="POST" action="{{ route('contracts.clauses.store', $contract->id) }}">
        @csrf
        <input type="hidden" name="section" value="{{ $section }}">
        <input type="hidden" name="insert_after_clause_id" value="{{ $afterClauseId }}">
        <input type="text" name="title" placeholder="Título de la cláusula (ej: Décima Séptima. – ...)" required>
        <textarea name="body" id="insert-body-{{ $afterClauseId }}" class="doc-tinymce" placeholder="Texto de la cláusula..."></textarea>
        <div style="margin-top:6px; display:flex; gap:6px;">
            <button type="submit" class="btn btn-sm btn-primary">Agregar</button>
            <button type="button" class="btn btn-sm btn-outline" onclick="toggleInsert({{ $afterClauseId }})">Cancelar</button>
        </div>
    </form>
</div>
