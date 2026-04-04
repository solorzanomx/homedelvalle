@extends('layouts.app-sidebar')
@section('title', 'Editar Post')

@section('styles')
<style>
    .post-editor { display: grid; grid-template-columns: 1fr 340px; gap: 1.5rem; align-items: start; }
    .editor-sidebar { position: sticky; top: 72px; }
    .seo-section { border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem; }
    .tags-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; max-height: 200px; overflow-y: auto; padding: 0.5rem 0; }
    .tag-check { display: flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; cursor: pointer; padding: 0.2rem 0.5rem; border: 1px solid var(--border); border-radius: 20px; transition: all 0.15s; }
    .tag-check:has(input:checked) { background: var(--primary); color: #fff; border-color: var(--primary); }
    .tag-check input { display: none; }
    .image-upload-area { border: 2px dashed var(--border); border-radius: var(--radius); padding: 1.25rem; text-align: center; cursor: pointer; transition: all 0.15s; position: relative; }
    .image-upload-area:hover { border-color: var(--primary); background: rgba(59,130,196,0.03); }
    .image-upload-area img { max-width: 100%; max-height: 160px; object-fit: cover; border-radius: 4px; }
    .meta-info { display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.78rem; color: var(--text-muted); padding: 0.5rem 0; }
    .meta-info span { display: flex; align-items: center; gap: 0.3rem; }
    @media (max-width: 1024px) { .post-editor { grid-template-columns: 1fr; } .editor-sidebar { position: static; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Editar Post</h2>
        <div class="meta-info">
            <span>&#128065; {{ number_format($post->views_count ?? 0) }} vistas</span>
            <span>&#128197; Creado: {{ $post->created_at->format('d/m/Y H:i') }}</span>
            <span>&#128221; Actualizado: {{ $post->updated_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">&#8592; Volver</a>
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

<form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="post-editor">
        {{-- Main content --}}
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Titulo <span class="required">*</span></label>
                        <input type="text" name="title" id="titleInput" class="form-input"
                               value="{{ old('title', $post->title) }}" required
                               placeholder="Titulo del post" oninput="generateSlug(this.value)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Slug <span class="required">*</span></label>
                        <input type="text" name="slug" id="slugInput" class="form-input"
                               value="{{ old('slug', $post->slug) }}" required placeholder="url-del-post">
                        <p class="form-hint">URL amigable. Se genera automaticamente del titulo.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contenido <span class="required">*</span></label>
                        <textarea name="body" id="wysiwygEditor" class="form-textarea" rows="18" required
                                  placeholder="Escribe el contenido del post...">{{ old('body', $post->body) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Extracto</label>
                        <textarea name="excerpt" class="form-textarea" rows="3"
                                  placeholder="Breve descripcion del post (opcional)">{{ old('excerpt', $post->excerpt) }}</textarea>
                        <p class="form-hint">Se muestra en listados y tarjetas del blog.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="editor-sidebar">
            {{-- Status --}}
            <div class="card">
                <div class="card-header"><h3>Publicacion</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Estado <span class="required">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Borrador</option>
                            <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="archived" {{ old('status', $post->status) === 'archived' ? 'selected' : '' }}>Archivado</option>
                        </select>
                    </div>

                    @if($post->published_at)
                    <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                        Publicado: {{ $post->published_at->format('d/m/Y H:i') }}
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Categoria</label>
                        <select name="category_id" class="form-select">
                            <option value="">Sin categoria</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $post->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Imagen Destacada</label>
                        <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                            @if($post->featured_image)
                                <img id="imagePreview" src="{{ Storage::url($post->featured_image) }}" alt="">
                                <div id="imagePlaceholder" style="display: none;">
                                    <div style="font-size: 1.5rem; color: var(--text-muted);">&#128247;</div>
                                    <p class="text-muted" style="font-size: 0.78rem; margin-top: 0.25rem;">Click para cambiar imagen</p>
                                </div>
                            @else
                                <img id="imagePreview" style="display: none;" alt="">
                                <div id="imagePlaceholder">
                                    <div style="font-size: 1.5rem; color: var(--text-muted);">&#128247;</div>
                                    <p class="text-muted" style="font-size: 0.78rem; margin-top: 0.25rem;">Click para subir imagen</p>
                                </div>
                            @endif
                            <input type="file" name="featured_image" id="imageInput" accept="image/*"
                                   style="display: none;" onchange="previewImage(this)">
                        </div>
                        <p class="form-hint">JPG, PNG, WebP. Max 2MB.</p>
                    </div>
                </div>
            </div>

            {{-- Tags --}}
            @if($tags->count())
            <div class="card">
                <div class="card-header"><h3>Etiquetas</h3></div>
                <div class="card-body">
                    @php $postTagIds = old('tags', $post->tags->pluck('id')->toArray()); @endphp
                    <div class="tags-grid">
                        @foreach($tags as $tag)
                        <label class="tag-check">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                   {{ in_array($tag->id, $postTagIds) ? 'checked' : '' }}>
                            {{ $tag->name }}
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- SEO --}}
            <div class="card">
                <div class="card-header"><h3>SEO</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Meta Titulo</label>
                        <input type="text" name="meta_title" class="form-input"
                               value="{{ old('meta_title', $post->meta_title) }}"
                               placeholder="Titulo para buscadores">
                        <p class="form-hint">Dejar vacio para usar el titulo del post.</p>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Meta Descripcion</label>
                        <textarea name="meta_description" class="form-textarea" rows="3"
                                  placeholder="Descripcion para buscadores">{{ old('meta_description', $post->meta_description) }}</textarea>
                        <p class="form-hint">Recomendado: 150-160 caracteres.</p>
                    </div>
                </div>
            </div>

            <div class="form-actions" style="border: none; padding-top: 0;">
                <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </div>
    </div>
</form>
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

function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('imagePreview');
            img.src = e.target.result;
            img.style.display = 'block';
            document.getElementById('imagePlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
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
