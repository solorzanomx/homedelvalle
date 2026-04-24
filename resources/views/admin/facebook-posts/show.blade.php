@extends('layouts.app-sidebar')
@section('title', 'Post Facebook — ' . $post->title)

@section('content')
<style>
.fb-grid   { display:grid; grid-template-columns:1fr 420px; gap:1.5rem; align-items:start; }
.card      { background:#fff; border-radius:12px; border:1px solid var(--border); margin-bottom:1.25rem; }
.card-header { padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.card-title  { font-weight:700; font-size:.92rem; }
.card-body   { padding:1.25rem; }
.form-label  { display:block; font-size:.78rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.03em; margin-bottom:.35rem; }
.form-input, .form-textarea, .form-select { width:100%; padding:.55rem .75rem; border:1px solid var(--border); border-radius:8px; font-size:.88rem; background:#fff; }
.form-textarea { resize:vertical; min-height:80px; }
.form-hint   { font-size:.75rem; color:var(--text-muted); margin-top:.25rem; }
.btn         { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-outline { background:#fff; color:var(--text); border:1px solid var(--border); }
.btn-success { background:#10b981; color:#fff; }
.btn-danger  { background:#fef2f2; color:#dc2626; border:1px solid #fca5a5; }
.btn-sm      { padding:.35rem .75rem; font-size:.8rem; }
.btn-full    { width:100%; justify-content:center; }
.tpl-cards   { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; }
.tpl-card    { border:2px solid var(--border); border-radius:10px; padding:.75rem; cursor:pointer; transition:all .15s; }
.tpl-card:hover { border-color:var(--primary); background:#f8faff; }
.tpl-card.selected { border-color:var(--primary); background:#eff6ff; }
.tpl-card input  { display:none; }
.tpl-name { font-weight:700; font-size:.85rem; }
.tpl-desc { font-size:.75rem; color:var(--text-muted); margin-top:.2rem; }
.tpl-swatch { height:32px; border-radius:5px; margin-bottom:.5rem; }
.preview-wrap  { border-radius:10px; overflow:hidden; border:2px solid var(--border); background:#f8fafc; aspect-ratio:1200/628; display:flex; align-items:center; justify-content:center; }
.preview-wrap img { width:100%; height:auto; display:block; }
.preview-placeholder { text-align:center; color:var(--text-muted); font-size:.85rem; padding:2rem; }
.spinner { display:inline-block; width:18px; height:18px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
.source-pane { display:none; }
.source-pane.active { display:block; }
.caption-box { background:#fafafa; border:1px solid var(--border); border-radius:8px; padding:.75rem; font-size:.85rem; line-height:1.6; white-space:pre-wrap; min-height:60px; }
.tag-chips   { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.4rem; }
.tag-chip    { background:#eff6ff; color:#1d4ed8; font-size:.75rem; font-weight:600; padding:.2rem .55rem; border-radius:999px; }
.error-box   { background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:.6rem .9rem; font-size:.82rem; color:#dc2626; margin-top:.5rem; display:none; }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
    <div>
        <h2 style="margin:0;">&#128241; {{ $post->title }}</h2>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.2rem;">Post Facebook · 1200×628px</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        @if($post->render_status === 'done')
        <a href="{{ route('admin.facebook.download', $post) }}" class="btn btn-outline">&#8659; Descargar PNG</a>
        @endif
        <a href="{{ route('admin.facebook.index') }}" class="btn btn-outline">← Posts</a>
    </div>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#15803d;">{{ session('success') }}</div>
@endif

<div class="fb-grid">

    {{-- ══ LEFT COLUMN ══ --}}
    <div>

        {{-- FUENTE --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">1 — Fuente de contenido</span>
            </div>
            <div class="card-body">
                <div style="display:flex;gap:.5rem;margin-bottom:1rem;border-bottom:1px solid var(--border);padding-bottom:.75rem;">
                    <button type="button" class="btn btn-sm {{ $post->source_type === 'blog_post' ? 'btn-primary' : 'btn-outline' }}" onclick="switchSource('blog_post')">Blog post</button>
                    <button type="button" class="btn btn-sm {{ $post->source_type === 'perplexity' ? 'btn-primary' : 'btn-outline' }}" onclick="switchSource('perplexity')">Perplexity / web</button>
                    <button type="button" class="btn btn-sm {{ $post->source_type === 'manual' ? 'btn-primary' : 'btn-outline' }}" onclick="switchSource('manual')">Manual</button>
                </div>

                <div id="pane-blog_post" class="source-pane {{ $post->source_type === 'blog_post' ? 'active' : '' }}">
                    <label class="form-label">Selecciona un post de blog</label>
                    <select id="blogSelect" class="form-select">
                        <option value="">— Elige un post —</option>
                        @foreach($blogPosts as $bp)
                        <option value="{{ $bp->id }}" {{ $post->source_id == $bp->id ? 'selected' : '' }}>{{ $bp->title }}</option>
                        @endforeach
                    </select>
                    <div class="form-hint">Claude leerá el título y cuerpo del artículo para generar el copy</div>
                </div>

                <div id="pane-perplexity" class="source-pane {{ $post->source_type === 'perplexity' ? 'active' : '' }}">
                    <label class="form-label">Pega el contenido de Perplexity / tema</label>
                    <textarea id="perplexityContent" class="form-textarea" rows="5" placeholder="Pega aquí el texto o resumen del tema…"></textarea>
                </div>

                <div id="pane-manual" class="source-pane {{ $post->source_type === 'manual' ? 'active' : '' }}">
                    <label class="form-label">Escribe el contenido</label>
                    <textarea id="manualContent" class="form-textarea" rows="5" placeholder="Escribe aquí el mensaje principal que quieres comunicar…"></textarea>
                </div>

                <div style="margin-top:1rem;">
                    <button type="button" class="btn btn-primary btn-full" onclick="generateCopy()" id="generateBtn">
                        <span id="generateLabel">&#9889; Generar copy con Claude</span>
                    </button>
                </div>
                <div id="generateError" class="error-box"></div>
            </div>
        </div>

        {{-- COPY --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">2 — Copy de la imagen</span>
                <span style="font-size:.75rem;color:var(--text-muted);">Se muestra dentro de la imagen</span>
            </div>
            <div class="card-body">
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">Headline <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="fldHeadline" class="form-input" value="{{ $post->headline }}" placeholder="Título corto e impactante (máx. 8 palabras)" onchange="saveField('headline', this.value)">
                </div>
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">Subheadline</label>
                    <input type="text" id="fldSubheadline" class="form-input" value="{{ $post->subheadline }}" placeholder="Complementa el headline…" onchange="saveField('subheadline', this.value)">
                </div>
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">Texto complementario</label>
                    <textarea id="fldBodyText" class="form-textarea" rows="2" placeholder="1-2 oraciones de apoyo…" onchange="saveField('body_text', this.value)">{{ $post->body_text }}</textarea>
                </div>
            </div>
        </div>

        {{-- CAPTION --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">3 — Caption para Facebook</span>
                <span style="font-size:.75rem;color:var(--text-muted);">Texto del post (no va en la imagen)</span>
            </div>
            <div class="card-body">
                <div style="margin-bottom:.85rem;">
                    <label class="form-label">Caption</label>
                    <textarea id="fldCaption" class="form-textarea" rows="4" placeholder="Texto conversacional para el post de Facebook…" onchange="saveField('caption', this.value)">{{ $post->caption }}</textarea>
                </div>
                <div>
                    <label class="form-label">Hashtags</label>
                    <div class="tag-chips" id="hashtagChips">
                        @foreach($post->hashtags ?? [] as $ht)
                        <span class="tag-chip">#{{ ltrim($ht, '#') }}</span>
                        @endforeach
                    </div>
                    <input type="text" id="fldHashtags" class="form-input" style="margin-top:.5rem;" value="{{ implode(', ', array_map(fn($h) => ltrim($h, '#'), $post->hashtags ?? [])) }}" placeholder="Ej: InmueblesDF, CDMX, HomedelValle" onchange="saveHashtags(this.value)">
                    <div class="form-hint">Separados por coma</div>
                </div>
                <div style="margin-top:1rem;display:flex;gap:.5rem;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="copyCaptionFull()">&#128203; Copiar caption + hashtags</button>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ RIGHT COLUMN ══ --}}
    <div>

        {{-- TEMPLATE --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Template</span></div>
            <div class="card-body">
                <div class="tpl-cards" id="tplCards">
                    @foreach(\App\Models\FacebookPost::TEMPLATES as $key => $label)
                    @php
                        [$tplName, $tplDesc] = explode(' — ', $label . ' — ');
                        $swatches = [
                            'fb-dark'     => 'background:#0C1A2E;',
                            'fb-light'    => 'background:#ffffff;border:1px solid #e2e8f0;',
                            'fb-foto'     => 'background:linear-gradient(135deg,#0C1A2E,#1d4ed8);',
                            'fb-gradient' => 'background:linear-gradient(135deg,#1e3a8a,#3b82f6);',
                        ];
                    @endphp
                    <label class="tpl-card {{ $post->template === $key ? 'selected' : '' }}" onclick="selectTemplate('{{ $key }}', this)">
                        <input type="radio" name="template" value="{{ $key }}" {{ $post->template === $key ? 'checked' : '' }}>
                        <div class="tpl-swatch" style="{{ $swatches[$key] ?? '' }}"></div>
                        <div class="tpl-name">{{ $tplName }}</div>
                        <div class="tpl-desc">{{ $tplDesc }}</div>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- IMAGEN DE FONDO --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Imagen de fondo</span><span style="font-size:.73rem;color:var(--text-muted);">Opcional</span></div>
            <div class="card-body">
                @if($post->background_image_path)
                <img id="bgPreview" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->background_image_path) }}" style="width:100%;border-radius:8px;margin-bottom:.75rem;" alt="Fondo">
                @else
                <div id="bgPreview" style="height:80px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:.8rem;margin-bottom:.75rem;">Sin imagen de fondo</div>
                @endif

                <label class="form-label">Prompt para generar con IA</label>
                <textarea id="bgPrompt" class="form-textarea" rows="3" placeholder="Ej: Departamento moderno en Polanco, sala de estar con vista a la ciudad, luz natural…">{{ old('bg_prompt') }}</textarea>
                <div style="display:flex;gap:.5rem;margin-top:.6rem;">
                    <button type="button" class="btn btn-primary btn-sm" onclick="generateBackground()" id="bgGenBtn">
                        <span id="bgGenLabel">&#9889; Generar con IA</span>
                    </button>
                    <label class="btn btn-outline btn-sm" style="cursor:pointer;">
                        &#8679; Subir foto
                        <input type="file" id="bgUpload" accept="image/*" style="display:none;" onchange="uploadBackground(this)">
                    </label>
                </div>
                <div id="bgError" class="error-box"></div>
            </div>
        </div>

        {{-- PREVIEW --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Preview</span>
                <span style="font-size:.73rem;color:var(--text-muted);">1200×628px</span>
            </div>
            <div class="card-body" style="padding:.75rem;">
                <div class="preview-wrap" id="previewWrap">
                    @if($imageUrl)
                    <img id="previewImg" src="{{ $imageUrl }}" alt="Preview">
                    @else
                    <div class="preview-placeholder" id="previewPlaceholder">
                        &#128444; Renderiza la imagen para ver el preview
                    </div>
                    @endif
                </div>
                <button type="button" class="btn btn-success btn-full" style="margin-top:.75rem;" onclick="renderImage()" id="renderBtn">
                    <span id="renderLabel">&#128247; Renderizar imagen</span>
                </button>
                <div id="renderError" class="error-box"></div>
                @if($post->rendered_image_path)
                <div style="text-align:center;margin-top:.5rem;">
                    <a href="{{ route('admin.facebook.download', $post) }}" class="btn btn-outline btn-sm">&#8659; Descargar PNG</a>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
const POST_ID   = {{ $post->id }};
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
let   sourceType = '{{ $post->source_type }}';

// ── Source switcher ────────────────────────────────────────────────
function switchSource(type) {
    sourceType = type;
    document.querySelectorAll('.source-pane').forEach(p => p.classList.remove('active'));
    document.getElementById('pane-' + type).classList.add('active');
    document.querySelectorAll('[onclick^="switchSource"]').forEach(b => {
        b.className = 'btn btn-sm ' + (b.getAttribute('onclick').includes("'" + type + "'") ? 'btn-primary' : 'btn-outline');
    });
}

// ── Template selector ──────────────────────────────────────────────
function selectTemplate(key, card) {
    document.querySelectorAll('.tpl-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    saveField('template', key);
    // Mark render as stale visually
    document.getElementById('renderLabel').textContent = '🎨 Re-renderizar con nuevo template';
}

// ── Field auto-save ────────────────────────────────────────────────
function saveField(field, value) {
    const body = new FormData();
    body.append('_method', 'PATCH');
    body.append('_token', CSRF);
    body.append(field, value);
    fetch(`/admin/facebook-posts/${POST_ID}`, { method: 'POST', body });
}

function saveHashtags(value) {
    const tags = value.split(',').map(t => t.trim().replace(/^#/, '')).filter(Boolean);
    const body = new FormData();
    body.append('_method', 'PATCH');
    body.append('_token', CSRF);
    tags.forEach(t => body.append('hashtags[]', t));
    fetch(`/admin/facebook-posts/${POST_ID}`, { method: 'POST', body });

    const chips = document.getElementById('hashtagChips');
    chips.innerHTML = tags.map(t => `<span class="tag-chip">#${t}</span>`).join('');
}

// ── Generate copy with Claude ──────────────────────────────────────
async function generateCopy() {
    let content = '';

    if (sourceType === 'blog_post') {
        const sel = document.getElementById('blogSelect');
        if (!sel.value) { alert('Selecciona un post de blog.'); return; }
        content = 'Blog post ID: ' + sel.value + '\nTítulo: ' + sel.options[sel.selectedIndex].text;
        // Save source_id
        saveField('source_id', sel.value);
        saveField('source_type', 'blog_post');
    } else if (sourceType === 'perplexity') {
        content = document.getElementById('perplexityContent').value.trim();
        if (!content) { alert('Escribe o pega el contenido de Perplexity.'); return; }
        saveField('source_type', 'perplexity');
    } else {
        content = document.getElementById('manualContent').value.trim();
        if (!content) { alert('Escribe el contenido.'); return; }
        saveField('source_type', 'manual');
    }

    const btn   = document.getElementById('generateBtn');
    const label = document.getElementById('generateLabel');
    const errEl = document.getElementById('generateError');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Generando con Claude…';
    errEl.style.display = 'none';

    try {
        const res  = await fetch(`/admin/facebook-posts/${POST_ID}/generate`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ content }),
        });
        const data = await res.json();

        if (!data.success) throw new Error(data.error || 'Error desconocido');

        document.getElementById('fldHeadline').value    = data.headline    || '';
        document.getElementById('fldSubheadline').value = data.subheadline || '';
        document.getElementById('fldBodyText').value    = data.body_text   || '';
        document.getElementById('fldCaption').value     = data.caption     || '';

        const tags = data.hashtags || [];
        document.getElementById('fldHashtags').value = tags.join(', ');
        document.getElementById('hashtagChips').innerHTML = tags.map(t => `<span class="tag-chip">#${t}</span>`).join('');

        if (data.bg_prompt) {
            document.getElementById('bgPrompt').value = data.bg_prompt;
            document.getElementById('bgPrompt').style.borderColor = 'var(--primary)';
            setTimeout(() => document.getElementById('bgPrompt').style.borderColor = '', 2000);
        }
    } catch (e) {
        errEl.textContent = e.message;
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        label.textContent = '⚡ Generar copy con Claude';
    }
}

// ── Generate background with Gemini ──────────────────────────────
async function generateBackground() {
    const prompt = document.getElementById('bgPrompt').value.trim();
    if (!prompt) { alert('Escribe un prompt para la imagen de fondo.'); return; }

    const btn   = document.getElementById('bgGenBtn');
    const label = document.getElementById('bgGenLabel');
    const errEl = document.getElementById('bgError');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Generando…';
    errEl.style.display = 'none';

    try {
        const res  = await fetch(`/admin/facebook-posts/${POST_ID}/generate-bg`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify({ prompt }),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Error');

        const preview = document.getElementById('bgPreview');
        preview.outerHTML = `<img id="bgPreview" src="${data.url}" style="width:100%;border-radius:8px;margin-bottom:.75rem;" alt="Fondo">`;
        document.getElementById('renderLabel').textContent = '🎨 Re-renderizar';
    } catch (e) {
        errEl.textContent = e.message;
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        label.textContent = '⚡ Generar con IA';
    }
}

// ── Upload background ─────────────────────────────────────────────
async function uploadBackground(input) {
    if (!input.files[0]) return;
    const errEl = document.getElementById('bgError');
    errEl.style.display = 'none';

    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('image', input.files[0]);

    try {
        const res  = await fetch(`/admin/facebook-posts/${POST_ID}/upload-bg`, { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Error');

        const preview = document.getElementById('bgPreview');
        preview.outerHTML = `<img id="bgPreview" src="${data.url}" style="width:100%;border-radius:8px;margin-bottom:.75rem;" alt="Fondo">`;
    } catch (e) {
        errEl.textContent = e.message;
        errEl.style.display = 'block';
    }
}

// ── Render image ──────────────────────────────────────────────────
async function renderImage() {
    // Auto-save editable fields first
    saveField('headline',    document.getElementById('fldHeadline').value);
    saveField('subheadline', document.getElementById('fldSubheadline').value);
    saveField('body_text',   document.getElementById('fldBodyText').value);

    const btn   = document.getElementById('renderBtn');
    const label = document.getElementById('renderLabel');
    const errEl = document.getElementById('renderError');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Renderizando… (~10 seg)';
    errEl.style.display = 'none';

    try {
        const res  = await fetch(`/admin/facebook-posts/${POST_ID}/render`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Error al renderizar');

        const wrap = document.getElementById('previewWrap');
        wrap.innerHTML = `<img id="previewImg" src="${data.url}" alt="Preview" style="width:100%;height:auto;display:block;">`;
    } catch (e) {
        errEl.textContent = e.message;
        errEl.style.display = 'block';
    } finally {
        btn.disabled = false;
        label.textContent = '📷 Renderizar imagen';
    }
}

// ── Copy caption ──────────────────────────────────────────────────
function copyCaptionFull() {
    const caption  = document.getElementById('fldCaption').value.trim();
    const hashtags = document.getElementById('fldHashtags').value.trim()
        .split(',').map(t => '#' + t.trim().replace(/^#/, '')).filter(Boolean).join(' ');
    const full = [caption, hashtags].filter(Boolean).join('\n\n');
    navigator.clipboard.writeText(full).then(() => {
        const btn = event.target.closest('button');
        const orig = btn.textContent;
        btn.textContent = '✓ Copiado';
        setTimeout(() => btn.textContent = orig, 2000);
    });
}
</script>
@endsection
