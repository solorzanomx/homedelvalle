{{--
    Editor de cláusulas reutilizable — usado tanto para las cláusulas de una
    ContractTemplate (compartida) como para las cláusulas de un Contract
    individual (por trato). El caller define las rutas via $clauseRoutes:
      ['store' => ..., 'update' => fn($clauseId) => ..., 'destroy' => fn($clauseId) => ..., 'reorder' => ...]
    y pasa $clauses (colección ordenada de ContractClause).
--}}
<div class="clause-editor" data-reorder-url="{{ $clauseRoutes['reorder'] }}">
    <div class="clause-list" id="clause-list-{{ $editorId }}">
        @foreach($clauses as $clause)
        <div class="clause-row" data-id="{{ $clause->id }}" style="border:1px solid var(--border); border-radius:8px; margin-bottom:0.65rem; overflow:hidden;">
            <div style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; background:var(--bg);">
                <span style="font-size:0.7rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); background:var(--surface); border:1px solid var(--border); border-radius:4px; padding:0.1rem 0.4rem;">{{ \App\Models\ContractClause::SECTIONS[$clause->section] ?? $clause->section }}</span>
                <span style="flex:1; font-weight:600; font-size:0.85rem;">{{ $clause->title }}</span>
                <button type="button" class="btn btn-sm btn-outline move-up" title="Subir">&#9650;</button>
                <button type="button" class="btn btn-sm btn-outline move-down" title="Bajar">&#9660;</button>
                <button type="button" class="btn btn-sm btn-outline toggle-edit">Editar</button>
                @if(!$clause->is_locked)
                <form method="POST" action="{{ $clauseRoutes['destroy']($clause->id) }}" onsubmit="return confirm('¿Eliminar esta cláusula?')" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                </form>
                @endif
            </div>
            <div class="clause-edit-form" style="display:none; padding:0.75rem; border-top:1px solid var(--border);">
                <form method="POST" action="{{ $clauseRoutes['update']($clause->id) }}">
                    @csrf @method('PUT')
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <label class="form-label" style="font-size:0.72rem;">Título</label>
                        <input type="text" name="title" class="form-input" value="{{ $clause->title }}" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0.5rem;">
                        <textarea name="body" id="clause-body-{{ $clause->id }}" class="form-textarea clause-tinymce" rows="8">{{ $clause->body }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar cláusula</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <form method="POST" action="{{ $clauseRoutes['reorder'] }}" id="reorder-form-{{ $editorId }}" style="display:none;">
        @csrf
        <div id="reorder-inputs-{{ $editorId }}"></div>
    </form>

    <button type="button" class="btn btn-sm btn-outline toggle-new-clause">+ Agregar cláusula</button>
    <div class="new-clause-form" style="display:none; margin-top:0.65rem; border:1px solid var(--border); border-radius:8px; padding:0.75rem;">
        <form method="POST" action="{{ $clauseRoutes['store'] }}">
            @csrf
            <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem;">
                <div class="form-group" style="flex:1; margin:0;">
                    <label class="form-label" style="font-size:0.72rem;">Título</label>
                    <input type="text" name="title" class="form-input" placeholder="Ej: Décima Séptima. – Cláusula especial" required>
                </div>
                <div class="form-group" style="width:160px; margin:0;">
                    <label class="form-label" style="font-size:0.72rem;">Sección</label>
                    <select name="section" class="form-select">
                        @foreach(\App\Models\ContractClause::SECTIONS as $key => $label)
                        <option value="{{ $key }}" {{ $key === 'clausula' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:0.5rem;">
                <textarea name="body" id="new-clause-body-{{ $editorId }}" class="form-textarea clause-tinymce" rows="6" placeholder="Texto de la cláusula..."></textarea>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">Agregar</button>
        </form>
    </div>
</div>

@once
<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
function initClauseTinyMCE(selector) {
    tinymce.init({
        selector: selector,
        height: 220,
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
document.querySelectorAll('.clause-editor').forEach(function(container) {
    container.querySelectorAll('.toggle-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var row = btn.closest('.clause-row');
            var formWrap = row.querySelector('.clause-edit-form');
            var isHidden = formWrap.style.display === 'none';
            formWrap.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                var textarea = formWrap.querySelector('textarea.clause-tinymce');
                if (textarea && !tinymce.get(textarea.id)) { initClauseTinyMCE('#' + textarea.id); }
            }
        });
    });

    var toggleNew = container.querySelector('.toggle-new-clause');
    if (toggleNew) {
        toggleNew.addEventListener('click', function() {
            var wrap = container.querySelector('.new-clause-form');
            var isHidden = wrap.style.display === 'none';
            wrap.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                var textarea = wrap.querySelector('textarea.clause-tinymce');
                if (textarea && !tinymce.get(textarea.id)) { initClauseTinyMCE('#' + textarea.id); }
            }
        });
    }

    var list = container.querySelector('.clause-list');
    function submitReorder() {
        var rows = list.querySelectorAll('.clause-row');
        var reorderForm = document.getElementById(list.id.replace('clause-list-', 'reorder-form-'));
        var inputsWrap = document.getElementById(list.id.replace('clause-list-', 'reorder-inputs-'));
        inputsWrap.innerHTML = '';
        rows.forEach(function(row) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order[]';
            input.value = row.dataset.id;
            inputsWrap.appendChild(input);
        });
        reorderForm.submit();
    }

    container.querySelectorAll('.move-up').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var row = btn.closest('.clause-row');
            var prev = row.previousElementSibling;
            if (prev) { list.insertBefore(row, prev); submitReorder(); }
        });
    });
    container.querySelectorAll('.move-down').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var row = btn.closest('.clause-row');
            var next = row.nextElementSibling;
            if (next) { list.insertBefore(next, row); submitReorder(); }
        });
    });
});
</script>
@endonce
