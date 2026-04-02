@extends('layouts.app-sidebar')
@section('title', 'Editar Pagina')

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Pagina</h2>
        <p class="text-muted">Modificar la pagina: {{ $page->title }}</p>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline">&#8592; Volver</a>
</div>

@if($errors->any())
<div class="alert alert-error">
    <div>
        <strong>Corrige los siguientes errores:</strong>
        <ul style="margin: 0.25rem 0 0 1rem; font-size: 0.82rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.pages.update', $page) }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Titulo <span class="required">*</span></label>
                    <input type="text" name="title" id="titleInput" class="form-input"
                           value="{{ old('title', $page->title) }}" required
                           placeholder="Titulo de la pagina" oninput="generateSlug(this.value)">
                </div>

                <div class="form-group">
                    <label class="form-label">Slug <span class="required">*</span></label>
                    <input type="text" name="slug" id="slugInput" class="form-input"
                           value="{{ old('slug', $page->slug) }}" required placeholder="url-de-la-pagina">
                    <p class="form-hint">URL amigable. Se genera automaticamente del titulo.</p>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Contenido <span class="required">*</span></label>
                    <textarea name="body" id="wysiwygEditor" class="form-textarea" rows="15" required
                              placeholder="Escribe el contenido de la pagina...">{{ old('body', $page->body) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1"
                               {{ old('is_published', $page->is_published) ? 'checked' : '' }}
                               style="width: 16px; height: 16px; accent-color: var(--primary);">
                        Publicar pagina
                    </label>
                    <p class="form-hint">La pagina sera visible publicamente si esta marcada.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Orden</label>
                    <input type="number" name="sort_order" class="form-input"
                           value="{{ old('sort_order', $page->sort_order ?? 0) }}" placeholder="0" min="0">
                    <p class="form-hint">Menor numero = aparece primero.</p>
                </div>
            </div>

            </div>

            {{-- SEO --}}
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header"><h3>SEO</h3></div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Meta Titulo</label>
                            <input type="text" name="meta_title" class="form-input"
                                   value="{{ old('meta_title', $page->meta_title) }}"
                                   placeholder="Titulo para buscadores">
                            <p class="form-hint">Dejar vacio para usar el titulo de la pagina.</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Meta Descripcion</label>
                            <textarea name="meta_description" class="form-textarea" rows="3"
                                      placeholder="Descripcion para buscadores">{{ old('meta_description', $page->meta_description) }}</textarea>
                            <p class="form-hint">Recomendado: 150-160 caracteres.</p>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.pages._nav-fields')

            @include('admin.pages._section-builder', ['page' => $page])

            @include('admin.pages._landing-fields')

            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border); margin-top: 0.5rem;">
                <span class="text-muted" style="font-size: 0.78rem;">
                    Creada: {{ $page->created_at->format('d/m/Y H:i') }}
                    &middot; Actualizada: {{ $page->updated_at->format('d/m/Y H:i') }}
                </span>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
function generateSlug(text) {
    var slug = text.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    document.getElementById('slugInput').value = slug;
}

tinymce.init({
    selector: '#wysiwygEditor',
    height: 500,
    menubar: 'edit insert format table',
    plugins: 'lists link image table code fullscreen preview autolink',
    toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image imagegallery | table | code fullscreen',
    content_style: 'body { font-family: Inter, Arial, sans-serif; font-size: 14px; padding: 8px; }',
    branding: false,
    license_key: 'gpl',
    relative_urls: false,
    remove_script_host: false,
    images_upload_handler: function(blobInfo) {
        return new Promise(function(resolve, reject) {
            var formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            fetch('{{ route("admin.cms.upload-image") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) { data.url ? resolve(data.url) : reject('Upload failed'); })
            .catch(function() { reject('Upload failed'); });
        });
    },
    setup: function(editor) {
        editor.on('submit', function() { editor.save(); });
        editor.ui.registry.addButton('imagegallery', {
            icon: 'gallery',
            tooltip: 'Insertar desde biblioteca',
            onAction: function() {
                window.mediaBrowserCallback = function(url, alt) {
                    editor.insertContent('<img src="' + url + '" alt="' + alt + '" style="max-width:100%;height:auto;">');
                };
                openMediaBrowser();
            }
        });
    }
});

document.querySelector('form').addEventListener('submit', function() {
    if (tinymce.get('wysiwygEditor')) tinymce.get('wysiwygEditor').save();
});
</script>
@include('admin.media._browser-modal')
@endsection
