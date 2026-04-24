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
    .image-upload-area:hover { border-color: var(--primary); background: rgba(102,126,234,0.03); }
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
    @if($post->ai_generated)
    <a href="{{ route('admin.blog.images', $post) }}" class="btn btn-outline" style="font-size:.82rem;">&#128444; Re-generar imágenes</a>
    @endif
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
                        <textarea name="body" id="wysiwygEditor" class="form-textarea" rows="18"
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

            @include('admin.posts._cta-fields', ['post' => $post])
        </div>

        {{-- Sidebar --}}
        <div class="editor-sidebar">
            {{-- Status --}}
            <div class="card">
                <div class="card-header"><h3>Publicacion</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Estado <span class="required">*</span></label>
                        <select name="status" class="form-select" required id="statusSelect">
                            <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Borrador</option>
                            <option value="scheduled" {{ old('status', $post->status) === 'scheduled' ? 'selected' : '' }}>Programado</option>
                            <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Publicado</option>
                            <option value="archived" {{ old('status', $post->status) === 'archived' ? 'selected' : '' }}>Archivado</option>
                        </select>
                    </div>

                    <div class="form-group" id="scheduleDateGroup">
                        <label class="form-label">Fecha de Publicacion</label>
                        <input type="datetime-local" name="published_at" id="publishedAtInput" class="form-input"
                               value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\\TH:i') : '') }}">
                        <p class="form-hint">Si esta en "Programado", se publica automaticamente en esta fecha.</p>
                    </div>

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
                    <div class="form-group">
                        <label class="form-label">Meta Descripcion</label>
                        <textarea name="meta_description" class="form-textarea" rows="3"
                                  placeholder="Descripcion para buscadores">{{ old('meta_description', $post->meta_description) }}</textarea>
                        <p class="form-hint">Recomendado: 150-160 caracteres.</p>
                    </div>
                    @if($post->focus_keyword)
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Keyword principal</label>
                        <input type="text" name="focus_keyword" class="form-input" value="{{ old('focus_keyword', $post->focus_keyword) }}">
                    </div>
                    @endif
                </div>
            </div>

            {{-- AI SEO Data --}}
            @if($post->ai_generated)
            <div class="card">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 style="margin:0;">&#9997; Datos SEO generados por IA</h3>
                    @if($post->seo_score)
                    @php $sc = $post->seo_score; $scColor = $sc >= 80 ? '#10b981' : ($sc >= 60 ? '#f59e0b' : '#ef4444'); @endphp
                    <span style="background:{{ $scColor }}20;color:{{ $scColor }};font-size:.78rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;">SEO {{ $sc }}/100</span>
                    @endif
                </div>
                <div class="card-body" style="font-size:.82rem;">

                    @if($post->reading_time || $post->schema_type)
                    <div style="display:flex;gap:.75rem;margin-bottom:.75rem;flex-wrap:wrap;">
                        @if($post->reading_time)
                        <span style="background:#f1f5f9;padding:.2rem .6rem;border-radius:6px;color:#64748b;">&#8987; {{ $post->reading_time }} min lectura</span>
                        @endif
                        @if($post->schema_type)
                        <span style="background:#eff6ff;padding:.2rem .6rem;border-radius:6px;color:#1d4ed8;">Schema: {{ $post->schema_type }}</span>
                        @endif
                        @if($post->ai_generation_status === 'done')
                        <span style="background:#f0fdf4;padding:.2rem .6rem;border-radius:6px;color:#15803d;">&#10003; Generado con IA</span>
                        @endif
                    </div>
                    @endif

                    @if($post->secondary_keywords)
                    <div style="margin-bottom:.75rem;">
                        <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:.3rem;">Keywords secundarias</div>
                        <div style="display:flex;flex-wrap:wrap;gap:.3rem;">
                            @foreach($post->secondary_keywords as $kw)
                            <span style="background:#eff6ff;color:#1d4ed8;padding:.15rem .5rem;border-radius:999px;font-size:.75rem;">{{ $kw }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($post->internal_links)
                    <div style="margin-bottom:.75rem;">
                        <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:.3rem;">Interlinking sugerido</div>
                        @foreach($post->internal_links as $link)
                        <div style="display:flex;align-items:center;gap:.4rem;margin-bottom:.25rem;">
                            <code style="font-size:.75rem;background:#f8fafc;padding:.1rem .4rem;border-radius:4px;">{{ $link['url'] ?? '' }}</code>
                            <span style="color:var(--text-muted);">→</span>
                            <span style="font-style:italic;">"{{ $link['anchor'] ?? '' }}"</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($post->image_prompts)
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.03em;margin-bottom:.5rem;">Prompts DALL-E para imágenes</div>
                        @php
                            $promptLabels = ['featured' => '&#127968; Imagen principal', 'interior_1' => '&#128444; Interior 1', 'interior_2' => '&#128444; Interior 2', 'interior_3' => '&#128444; Interior 3'];
                        @endphp
                        @foreach($promptLabels as $key => $label)
                        @if(!empty($post->image_prompts[$key]))
                        <div style="margin-bottom:.6rem;">
                            <div style="font-size:.75rem;font-weight:600;margin-bottom:.2rem;">{!! $label !!}</div>
                            <div style="background:#f8fafc;border:1px solid var(--border);border-radius:6px;padding:.5rem .65rem;font-size:.75rem;color:#374151;line-height:1.5;cursor:pointer;" onclick="copyPrompt(this)" title="Clic para copiar">{{ $post->image_prompts[$key] }}</div>
                        </div>
                        @endif
                        @endforeach
                        <p style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;">Clic en cada prompt para copiarlo → úsalo en DALL-E 3 o Midjourney</p>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            {{-- Preview --}}
            <div class="card">
                <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <h3>Vista Previa</h3>
                    <button type="button" class="btn btn-sm btn-outline" onclick="togglePreview()">Actualizar</button>
                </div>
                <div class="card-body" id="previewPanel" style="padding:0;">
                    <div id="previewContent" style="padding:0.75rem;font-size:0.82rem;max-height:400px;overflow-y:auto;">
                        <p class="text-muted text-center" style="padding:1rem;">Haz click en "Actualizar" para ver la vista previa.</p>
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
@php
$aiImagePrompts = $post->image_prompts ?? [];
$aiImagesJs = [
    ['label' => 'Imagen destacada', 'url' => isset($aiImagePrompts['path_featured'])   ? \Illuminate\Support\Facades\Storage::disk('public')->url($aiImagePrompts['path_featured'])   : null],
    ['label' => 'Interior 1',       'url' => isset($aiImagePrompts['path_interior_1']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($aiImagePrompts['path_interior_1']) : null],
    ['label' => 'Interior 2',       'url' => isset($aiImagePrompts['path_interior_2']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($aiImagePrompts['path_interior_2']) : null],
    ['label' => 'Interior 3',       'url' => isset($aiImagePrompts['path_interior_3']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($aiImagePrompts['path_interior_3']) : null],
];
@endphp
<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
const AI_IMAGES = @json($aiImagesJs);

function copyPrompt(el) {
    navigator.clipboard.writeText(el.textContent.trim()).then(function() {
        var orig = el.style.background;
        el.style.background = '#d1fae5';
        setTimeout(function() { el.style.background = orig; }, 800);
    });
}

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
    toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image imagegallery | insertcta insertaiimg | table | code fullscreen',
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
        editor.ui.registry.addMenuButton('insertcta', {
            text: 'CTA',
            tooltip: 'Insertar Call-to-Action',
            fetch: function(callback) {
                callback([
                    { type: 'menuitem', text: 'CTA 1', onAction: function() { editor.insertContent('@{{CTA1}}'); } },
                    { type: 'menuitem', text: 'CTA 2', onAction: function() { editor.insertContent('@{{CTA2}}'); } },
                    { type: 'menuitem', text: 'CTA 3', onAction: function() { editor.insertContent('@{{CTA3}}'); } }
                ]);
            }
        });
        editor.ui.registry.addMenuButton('insertaiimg', {
            text: 'Imágenes IA',
            tooltip: 'Insertar imagen generada por IA',
            fetch: function(callback) {
                var items = AI_IMAGES.map(function(img) {
                    return {
                        type: 'menuitem',
                        text: img.label + (img.url ? '' : ' (sin generar)'),
                        enabled: !!img.url,
                        onAction: function() {
                            editor.insertContent(
                                '<figure class="blog-img">' +
                                '<img src="' + img.url + '" alt="" width="720" ' +
                                'style="width:720px;max-width:100%;height:auto;" loading="lazy">' +
                                '</figure>'
                            );
                        }
                    };
                });
                callback(items);
            }
        });
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

document.querySelector('form').addEventListener('submit', function(e) {
    var editor = typeof tinymce !== 'undefined' ? tinymce.get('wysiwygEditor') : null;
    if (editor) {
        editor.save();
        var content = editor.getContent();
        if (content) document.getElementById('wysiwygEditor').value = content;
    }
});

// Preview
function togglePreview() {
    var editor = typeof tinymce !== 'undefined' ? tinymce.get('wysiwygEditor') : null;
    var content = editor ? editor.getContent() : document.getElementById('wysiwygEditor').value;
    var title = document.getElementById('titleInput').value;
    var el = document.getElementById('previewContent');
    if (!content && !title) {
        el.innerHTML = '<p class="text-muted text-center" style="padding:1rem;">Sin contenido para previsualizar.</p>';
        return;
    }
    var html = '';
    if (title) html += '<h2 style="font-size:1.1rem;font-weight:700;margin-bottom:0.75rem;">' + title.replace(/</g, '&lt;') + '</h2>';
    if (content) html += '<div class="preview-body" style="line-height:1.6;">' + content + '</div>';
    el.innerHTML = html;
}
</script>
@include('admin.media._browser-modal')
@endsection
