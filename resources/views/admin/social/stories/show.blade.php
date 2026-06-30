@extends('layouts.app-sidebar')
@section('title', 'Historia — ' . ($story->headline ?? 'Sin título'))

@section('content')
<style>
.story-grid { display:grid; grid-template-columns:1fr 380px; gap:1.5rem; align-items:start; }
@media(max-width:900px){ .story-grid { grid-template-columns:1fr; } }
.card { background:#fff; border-radius:12px; border:1px solid #e5e7eb; margin-bottom:1.25rem; }
.card-header { padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
.card-title { font-weight:700; font-size:.92rem; display:flex; align-items:center; gap:.4rem; }
.card-body { padding:1.25rem; }
.form-label { display:block; font-size:.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.03em; margin-bottom:.35rem; }
.form-input, .form-textarea, .form-select { width:100%; padding:.55rem .75rem; border:1px solid #d1d5db; border-radius:8px; font-size:.88rem; background:#fff; box-sizing:border-box; }
.form-input:focus, .form-textarea:focus, .form-select:focus { outline:none; border-color:#1d4ed8; box-shadow:0 0 0 2px rgba(29,78,216,.12); }
.form-textarea { resize:vertical; min-height:80px; }
.form-hint { font-size:.75rem; color:#9ca3af; margin-top:.25rem; }
.char-counter { font-size:.72rem; color:#9ca3af; text-align:right; margin-top:.2rem; }
.char-counter.warn { color:#f59e0b; }
.char-counter.limit { color:#dc2626; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:opacity .15s; }
.btn:disabled { opacity:.55; cursor:not-allowed; }
.btn-primary { background:#1d4ed8; color:#fff; }
.btn-outline { background:#fff; color:#374151; border:1px solid #d1d5db; }
.btn-outline:hover { background:#f3f4f6; }
.btn-success { background:#10b981; color:#fff; }
.btn-danger { background:#fef2f2; color:#dc2626; border:1px solid #fca5a5; }
.btn-purple { background:#7c3aed; color:#fff; }
.btn-sm { padding:.35rem .75rem; font-size:.8rem; }
.btn-full { width:100%; justify-content:center; }
.spinner { display:inline-block; width:18px; height:18px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* Story preview — 9:16 ratio */
.preview-wrap { border-radius:10px; overflow:hidden; border:2px solid #e5e7eb; background:#0C1A2E; aspect-ratio:9/16; display:flex; align-items:center; justify-content:center; position:relative; max-width:200px; margin:0 auto; }
.preview-wrap img { width:100%; height:100%; object-fit:cover; display:block; }
.preview-placeholder { text-align:center; color:#6b7280; font-size:.85rem; padding:1.5rem; }
.preview-placeholder-icon { font-size:2.5rem; margin-bottom:.5rem; }

.status-pill { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .65rem; border-radius:999px; font-size:.78rem; font-weight:700; }
.status-yellow { background:#fef9c3; color:#854d0e; }
.status-blue   { background:#dbeafe; color:#1e40af; }
.status-green  { background:#d1fae5; color:#065f46; }
.status-red    { background:#fee2e2; color:#991b1b; }

.error-box { background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:.6rem .9rem; font-size:.82rem; color:#dc2626; margin-top:.5rem; display:none; }
.success-toast { position:fixed; bottom:1.5rem; right:1.5rem; background:#10b981; color:#fff; padding:.65rem 1.25rem; border-radius:10px; font-size:.85rem; font-weight:600; box-shadow:0 4px 16px rgba(0,0,0,.15); transform:translateY(100px); opacity:0; transition:all .3s; z-index:999; }
.success-toast.show { transform:translateY(0); opacity:1; }
.published-box { background:#f0fdf4; border:1.5px solid #86efac; border-radius:10px; padding:1rem 1.25rem; }
.divider { height:1px; background:#f3f4f6; margin:.75rem 0; }
.tag-chips { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.4rem; }
.tag-chip { background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:600; padding:.2rem .55rem; border-radius:999px; }
.hashtags-input-wrap { display:flex; gap:.5rem; align-items:center; }
.hashtag-row { display:flex; align-items:center; gap:.35rem; padding:.25rem 0; }
.hashtag-row-tag { background:#eff6ff; color:#1d4ed8; font-size:.8rem; font-weight:600; padding:.2rem .55rem; border-radius:999px; flex:1; }
.hashtag-row-del { background:none; border:none; cursor:pointer; color:#9ca3af; font-size:1rem; padding:0 .25rem; }
.hashtag-row-del:hover { color:#dc2626; }
</style>

@php
    $statusColors = [
        'draft'     => 'yellow',
        'scheduled' => 'blue',
        'published' => 'green',
        'failed'    => 'red',
    ];
    $statusColor = $statusColors[$story->status] ?? 'gray';
@endphp

<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="{{ route('admin.social.stories.index') }}" class="btn btn-outline btn-sm">&#8592; Historias</a>
    <span class="status-pill status-{{ $statusColor }}">
        {{ $story->status_label }}
    </span>
    <span style="font-size:.82rem;color:#6b7280;">
        {{ $story->platform_label }} · {{ $story->media_type_label }}
        @if($story->scheduled_at) · Programada: {{ $story->scheduled_at->format('d M Y H:i') }} @endif
        @if($story->published_at) · Publicada: {{ $story->published_at->format('d M Y H:i') }} @endif
    </span>
    <div style="margin-left:auto;display:flex;gap:.4rem;">
        @if($story->rendered_image_path)
        <a href="{{ route('admin.social.stories.download', $story) }}" class="btn btn-outline btn-sm">&#8681; Descargar</a>
        @endif
        <form action="{{ route('admin.social.stories.destroy', $story) }}" method="POST"
              onsubmit="return confirm('¿Eliminar esta historia? Esta acción no se puede deshacer.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
        </form>
    </div>
</div>

<div class="story-grid">
    {{-- Left panel: editable fields --}}
    <div>
        @if($story->status === 'published')
        <div class="card">
            <div class="card-body published-box">
                <p style="font-weight:700;margin:0 0 .25rem;color:#15803d;">&#10003; Historia Publicada</p>
                <p style="font-size:.83rem;color:#166534;margin:0;">
                    Publicada el {{ $story->published_at->format('d M Y H:i') }}
                    @if($story->platform_story_url)
                    · <a href="{{ $story->platform_story_url }}" target="_blank" rel="noopener" style="color:#1d4ed8;">Ver en {{ $story->platform_label }}</a>
                    @endif
                </p>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header">
                <span class="card-title">&#9998; Contenido</span>
                <button id="btnSave" onclick="saveStory()" class="btn btn-primary btn-sm">Guardar</button>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div>
                        <label class="form-label">Plataforma</label>
                        <select id="fieldPlatform" class="form-select">
                            <option value="instagram" {{ $story->platform==='instagram'?'selected':'' }}>Instagram</option>
                            <option value="facebook"  {{ $story->platform==='facebook' ?'selected':'' }}>Facebook</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Tipo de Media</label>
                        <select id="fieldMediaType" class="form-select">
                            <option value="image" {{ $story->media_type==='image'?'selected':'' }}>Imagen</option>
                            <option value="video" {{ $story->media_type==='video'?'selected':'' }}>Video</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Headline <span style="color:#9ca3af;font-weight:400;">(texto principal)</span></label>
                    <input type="text" id="fieldHeadline" class="form-input" maxlength="100"
                           value="{{ $story->headline }}" placeholder="Texto que aparece sobre la historia"
                           oninput="updateCounter(this, 'cntHeadline', 100)">
                    <div class="char-counter" id="cntHeadline">{{ strlen($story->headline ?? '') }} / 100</div>
                </div>

                <div>
                    <label class="form-label">Caption <span style="color:#9ca3af;font-weight:400;">(para compartir)</span></label>
                    <textarea id="fieldCaption" class="form-textarea" rows="4"
                              placeholder="Texto para acompañar cuando se comparte la historia"
                              oninput="updateCounter(this, 'cntCaption', 2200)">{{ $story->caption }}</textarea>
                    <div class="char-counter" id="cntCaption">{{ strlen($story->caption ?? '') }} / 2200</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="card-title">&#127991; Stickers</span>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label class="form-label">Hashtags del Sticker</label>
                    <div id="hashtagList" style="margin-bottom:.5rem;">
                        @foreach($story->sticker_hashtags ?? [] as $tag)
                        <div class="hashtag-row">
                            <span class="hashtag-row-tag">#{{ ltrim($tag, '#') }}</span>
                            <button type="button" class="hashtag-row-del" onclick="this.parentElement.remove()">&#10005;</button>
                        </div>
                        @endforeach
                    </div>
                    <div style="display:flex;gap:.5rem;">
                        <input type="text" id="newHashtag" class="form-input" placeholder="Añadir hashtag (sin #)" style="flex:1;"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();addHashtag();}">
                        <button type="button" onclick="addHashtag()" class="btn btn-outline btn-sm">+</button>
                    </div>
                </div>
                <div>
                    <label class="form-label">Sticker de Ubicación</label>
                    <input type="text" id="fieldStickerLocation" class="form-input" maxlength="100"
                           value="{{ $story->sticker_location }}" placeholder="Ej: Narvarte, CDMX">
                </div>
                <div>
                    <label class="form-label">Sticker de Link</label>
                    <input type="url" id="fieldStickerLink" class="form-input" maxlength="255"
                           value="{{ $story->sticker_link }}" placeholder="https://...">
                </div>
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div>
        {{-- Preview --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">&#128247; Preview 9:16</span>
                @if($story->render_status === 'done')
                <span style="font-size:.72rem;font-weight:600;color:#15803d;">&#10003; Renderizada</span>
                @elseif($story->render_status === 'failed')
                <span style="font-size:.72rem;font-weight:600;color:#dc2626;">&#10007; Error</span>
                @endif
            </div>
            <div class="card-body">
                <div class="preview-wrap">
                    @if($imageUrl)
                    <img id="previewImg" src="{{ $imageUrl }}" alt="Preview">
                    @elseif($bgUrl)
                    <img id="previewImg" src="{{ $bgUrl }}" alt="Fondo">
                    @else
                    <div class="preview-placeholder" id="previewPlaceholder">
                        <div class="preview-placeholder-icon">&#127775;</div>
                        <div>Sin imagen<br>aún</div>
                    </div>
                    @endif
                </div>
                @if($story->render_status === 'failed' && $story->render_error)
                <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:.6rem;font-size:.75rem;color:#dc2626;margin-top:.75rem;">
                    {{ $story->render_error }}
                </div>
                @endif
            </div>
        </div>

        {{-- Upload background --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">&#128444;&#65039; Imagen de Fondo</span>
            </div>
            <div class="card-body">
                <p style="font-size:.82rem;color:#6b7280;margin:0 0 .75rem;">Sube una imagen que se usará como fondo (se recortará a 1080×1920).</p>
                <label for="bgUpload" class="btn btn-outline btn-sm btn-full" style="cursor:pointer;">
                    &#8679; Subir imagen
                </label>
                <input type="file" id="bgUpload" accept="image/*" style="display:none;" onchange="uploadBackground(this)">
                <div class="error-box" id="bgError"></div>
                @if($story->background_image_path)
                <p style="font-size:.75rem;color:#15803d;margin:.5rem 0 0;">&#10003; Imagen de fondo cargada.</p>
                @endif
            </div>
        </div>

        {{-- Render --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">&#128249; Renderizar</span>
            </div>
            <div class="card-body">
                <p style="font-size:.82rem;color:#6b7280;margin:0 0 .75rem;">Genera la imagen final en 1080×1920px con texto y diseño superpuesto.</p>
                <button id="btnRender" onclick="renderStory()" class="btn btn-primary btn-full"
                        {{ !$story->background_image_path ? 'disabled' : '' }}>
                    &#128249; Renderizar Historia
                </button>
                <div class="error-box" id="renderError"></div>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">&#128337; Programar</span>
            </div>
            <div class="card-body">
                <label class="form-label">Fecha y hora de publicación</label>
                <input type="datetime-local" id="fieldScheduledAt" class="form-input"
                       value="{{ $story->scheduled_at ? $story->scheduled_at->format('Y-m-d\TH:i') : '' }}"
                       min="{{ now()->format('Y-m-d\TH:i') }}">
                <p class="form-hint" style="margin-top:.35rem;">El sistema publicará automáticamente en la fecha indicada.</p>
                <button onclick="scheduleStory()" class="btn btn-purple btn-sm btn-full" style="margin-top:.75rem;">
                    &#128337; Guardar programación
                </button>
                <div class="error-box" id="scheduleError"></div>
            </div>
        </div>

        {{-- Publish --}}
        @if($story->status !== 'published')
        <div class="card">
            <div class="card-header">
                <span class="card-title">&#128241; Publicar Ahora</span>
            </div>
            <div class="card-body">
                <p style="font-size:.82rem;color:#6b7280;margin:0 0 .75rem;">Envía la historia a n8n para publicación inmediata en {{ $story->platform_label }}.</p>
                <button id="btnPublish" onclick="publishStory()" class="btn btn-success btn-full">
                    &#128241; Publicar en {{ $story->platform_label }}
                </button>
                <div class="error-box" id="publishError"></div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="success-toast" id="successToast"></div>

<script>
const storyId = {{ $story->id }};
const updateUrl = '{{ route("admin.social.stories.update", $story) }}';
const uploadBgUrl = '{{ route("admin.social.stories.upload-bg", $story) }}';
const renderUrl = '{{ route("admin.social.stories.render", $story) }}';
const publishUrl = '{{ route("admin.social.stories.publish", $story) }}';
const csrfToken = '{{ csrf_token() }}';

function showToast(msg, isError = false) {
    const t = document.getElementById('successToast');
    t.textContent = msg;
    t.style.background = isError ? '#dc2626' : '#10b981';
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

function updateCounter(el, counterId, max) {
    const len = el.value.length;
    const counter = document.getElementById(counterId);
    if (!counter) return;
    counter.textContent = len + ' / ' + max;
    counter.className = 'char-counter' + (len >= max ? ' limit' : (len >= max * 0.85 ? ' warn' : ''));
}

// Init counters
['fieldHeadline', 'fieldCaption'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { const max = id === 'fieldHeadline' ? 100 : 2200; updateCounter(el, id === 'fieldHeadline' ? 'cntHeadline' : 'cntCaption', max); }
});

function getHashtags() {
    return Array.from(document.querySelectorAll('#hashtagList .hashtag-row-tag'))
        .map(el => el.textContent.replace(/^#/, '').trim())
        .filter(Boolean);
}

function addHashtag() {
    const inp = document.getElementById('newHashtag');
    const val = inp.value.trim().replace(/^#/, '');
    if (!val) return;
    const row = document.createElement('div');
    row.className = 'hashtag-row';
    row.innerHTML = `<span class="hashtag-row-tag">#${val}</span>
        <button type="button" class="hashtag-row-del" onclick="this.parentElement.remove()">&#10005;</button>`;
    document.getElementById('hashtagList').appendChild(row);
    inp.value = '';
}

async function saveStory() {
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    const data = {
        _method: 'PATCH',
        platform: document.getElementById('fieldPlatform').value,
        media_type: document.getElementById('fieldMediaType').value,
        headline: document.getElementById('fieldHeadline').value,
        caption: document.getElementById('fieldCaption').value,
        sticker_hashtags: getHashtags(),
        sticker_location: document.getElementById('fieldStickerLocation').value,
        sticker_link: document.getElementById('fieldStickerLink').value,
    };

    try {
        const res = await fetch(updateUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(data),
        });
        const json = await res.json();
        if (json.success) showToast('Guardado correctamente.');
        else showToast('Error al guardar.', true);
    } catch (e) {
        showToast('Error de red.', true);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar';
    }
}

async function uploadBackground(input) {
    const errEl = document.getElementById('bgError');
    errEl.style.display = 'none';

    if (!input.files[0]) return;
    const formData = new FormData();
    formData.append('image', input.files[0]);
    formData.append('_token', csrfToken);

    const label = input.previousElementSibling;
    label.textContent = 'Subiendo...';

    try {
        const res = await fetch(uploadBgUrl, { method: 'POST', body: formData });
        const json = await res.json();
        if (json.success) {
            document.getElementById('previewImg') ? document.getElementById('previewImg').src = json.url : null;
            const ph = document.getElementById('previewPlaceholder');
            if (ph) {
                const img = document.createElement('img');
                img.id = 'previewImg';
                img.src = json.url;
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                ph.parentElement.replaceChild(img, ph);
            } else {
                const img = document.querySelector('.preview-wrap img');
                if (img) img.src = json.url;
            }
            document.getElementById('btnRender').disabled = false;
            showToast('Imagen de fondo cargada.');
        } else {
            errEl.textContent = json.error ?? 'Error al subir.';
            errEl.style.display = 'block';
        }
    } catch (e) {
        errEl.textContent = 'Error de red.';
        errEl.style.display = 'block';
    } finally {
        label.textContent = '⬆ Subir imagen';
    }
}

async function renderStory() {
    const btn = document.getElementById('btnRender');
    const errEl = document.getElementById('renderError');
    errEl.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Renderizando...';

    try {
        const res = await fetch(renderUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const json = await res.json();
        if (json.success) {
            const img = document.querySelector('.preview-wrap img') ?? document.getElementById('previewImg');
            if (img) img.src = json.url;
            else {
                const wrap = document.querySelector('.preview-wrap');
                wrap.innerHTML = `<img src="${json.url}" style="width:100%;height:100%;object-fit:cover;">`;
            }
            showToast('Historia renderizada correctamente.');
        } else {
            errEl.textContent = json.error ?? 'Error al renderizar.';
            errEl.style.display = 'block';
        }
    } catch (e) {
        errEl.textContent = 'Error de red.';
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '&#128249; Renderizar Historia';
    }
}

async function scheduleStory() {
    const errEl = document.getElementById('scheduleError');
    errEl.style.display = 'none';
    const scheduledAt = document.getElementById('fieldScheduledAt').value;

    try {
        const res = await fetch(updateUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ _method: 'PATCH', scheduled_at: scheduledAt, status: scheduledAt ? 'scheduled' : 'draft' }),
        });
        const json = await res.json();
        if (json.success) showToast(scheduledAt ? 'Historia programada.' : 'Programación eliminada.');
        else { errEl.textContent = json.message ?? 'Error.'; errEl.style.display = 'block'; }
    } catch (e) {
        errEl.textContent = 'Error de red.'; errEl.style.display = 'block';
    }
}

async function publishStory() {
    if (!confirm('¿Publicar esta historia ahora en {{ $story->platform_label }}?')) return;
    const btn = document.getElementById('btnPublish');
    const errEl = document.getElementById('publishError');
    errEl.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Publicando...';

    try {
        const res = await fetch(publishUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const json = await res.json();
        if (json.success) {
            showToast('Historia publicada correctamente.');
            setTimeout(() => location.reload(), 1500);
        } else {
            errEl.textContent = json.error ?? 'Error al publicar.';
            errEl.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '&#128241; Publicar en {{ $story->platform_label }}';
        }
    } catch (e) {
        errEl.textContent = 'Error de red.'; errEl.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '&#128241; Publicar en {{ $story->platform_label }}';
    }
}
</script>
@endsection
