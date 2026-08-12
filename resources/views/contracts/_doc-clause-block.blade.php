{{-- Un bloque de cláusula (declaración o cláusula) dentro de la vista de documento completo. --}}
<div class="doc-block clause-block" id="clause-{{ $clause->id }}">
    <div class="doc-block-controls">
        <button type="button" onclick="toggleEdit({{ $clause->id }})">Editar</button>
        @if(!$clause->is_locked)
        <form method="POST" action="{{ route('contracts.clauses.destroy', [$contract->id, $clause->id]) }}" onsubmit="return confirm('¿Eliminar esta cláusula?')" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit">Eliminar</button>
        </form>
        @endif
    </div>
    <div id="view-{{ $clause->id }}"><span class="ctitle">{{ $clause->title }}</span>{!! $clause->body !!}</div>
    <div class="doc-edit-form" id="edit-{{ $clause->id }}">
        <form method="POST" action="{{ route('contracts.clauses.update', [$contract->id, $clause->id]) }}">
            @csrf @method('PUT')
            <input type="text" name="title" value="{{ $clause->title }}">
            <textarea name="body" id="body-{{ $clause->id }}" class="doc-tinymce">{{ $clause->body }}</textarea>
            <div style="margin-top:6px; display:flex; gap:6px;">
                <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                <button type="button" class="btn btn-sm btn-outline" onclick="toggleEdit({{ $clause->id }})">Cancelar</button>
            </div>
        </form>
    </div>
</div>
