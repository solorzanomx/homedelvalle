@extends('layouts.app-sidebar')
@section('title', 'Imágenes del artículo')

@section('content')
<style>
/* ── Step bar ─────────────────────────────────────────────────────── */
.step-bar { display:flex; align-items:center; gap:0; margin-bottom:2rem; }
.step      { display:flex; align-items:center; gap:.5rem; font-size:.82rem; font-weight:600; color:#94a3b8; }
.step.done  { color:#10b981; }
.step.active{ color:var(--primary); }
.step-num  { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; background:#e2e8f0; color:#64748b; flex-shrink:0; }
.step.done  .step-num { background:#10b981; color:#fff; }
.step.active .step-num { background:var(--primary); color:#fff; }
.step-line  { flex:1; height:2px; background:#e2e8f0; min-width:32px; }
.step.done + .step-line { background:#10b981; }

/* ── Layout ───────────────────────────────────────────────────────── */
.img-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
@media(max-width:800px) { .img-grid { grid-template-columns:1fr; } }

/* ── Card ─────────────────────────────────────────────────────────── */
.img-card { background:#fff; border-radius:12px; border:1px solid var(--border); overflow:hidden; }
.img-card-header { padding:.75rem 1rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.img-card-label  { font-weight:700; font-size:.88rem; color:#1e293b; }
.img-card-body   { padding:1rem; display:flex; flex-direction:column; gap:.75rem; }

/* ── Image preview ────────────────────────────────────────────────── */
.img-preview-wrap {
    position:relative; width:100%; aspect-ratio:720/405; background:#f1f5f9;
    border-radius:8px; overflow:hidden; border:1px dashed #d1d5db;
}
.img-preview-wrap img {
    width:100%; height:100%; object-fit:cover; display:block;
    transition:opacity .3s;
}
.img-placeholder {
    width:100%; height:100%; display:flex; flex-direction:column; align-items:center;
    justify-content:center; gap:.5rem; color:#94a3b8; font-size:.82rem;
}
.img-loading-overlay {
    position:absolute; inset:0; background:rgba(255,255,255,.85);
    display:none; align-items:center; justify-content:center; flex-direction:column; gap:.5rem;
    font-size:.82rem; color:#64748b; font-weight:500;
}
.img-loading-overlay.active { display:flex; }
.spinner { display:inline-block; width:32px; height:32px; border:3px solid #e2e8f0; border-top-color:var(--primary); border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

/* ── Prompt ───────────────────────────────────────────────────────── */
.prompt-box {
    background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;
    padding:.5rem .75rem; font-size:.73rem; line-height:1.6; color:#475569;
    font-family:'JetBrains Mono','Fira Code',monospace; max-height:80px;
    overflow-y:auto; word-break:break-word;
}

/* ── Buttons ──────────────────────────────────────────────────────── */
.btn        { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border-radius:8px; font-size:.84rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
.btn-primary{ background:var(--primary); color:#fff; }
.btn-outline{ background:#fff; color:var(--text); border:1px solid var(--border); }
.btn-success{ background:#10b981; color:#fff; }
.btn-danger { background:#ef4444; color:#fff; }
.btn-sm     { padding:.35rem .7rem; font-size:.78rem; }
.btn:disabled { opacity:.5; cursor:not-allowed; }

/* ── Error ────────────────────────────────────────────────────────── */
.img-error { font-size:.75rem; color:#ef4444; display:none; margin-top:.25rem; }
.img-error.active { display:block; }

/* ── Bottom actions ───────────────────────────────────────────────── */
.bottom-bar {
    position:sticky; bottom:0; background:#fff; border-top:1px solid #e5e7eb;
    padding:.85rem 0; margin-top:1.75rem; display:flex; align-items:center;
    justify-content:space-between; gap:1rem;
}
</style>

{{-- ── STEP BAR ─────────────────────────────────────────────────────── --}}
<div class="step-bar">
    <div class="step done">
        <div class="step-num">&#10003;</div>
        <span>Contenido</span>
    </div>
    <div class="step-line"></div>
    <div class="step active">
        <div class="step-num">2</div>
        <span>Imágenes</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
        <div class="step-num">3</div>
        <span>Editar y publicar</span>
    </div>
</div>

{{-- ── PAGE HEADER ──────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.25rem;gap:1rem;">
    <div>
        <h2 style="margin:0;font-size:1.15rem;">Imágenes del artículo</h2>
        <p style="margin:.2rem 0 0;font-size:.82rem;color:var(--text-muted);">
            {{ $post->title }}
        </p>
    </div>
    <button id="btnGenerateAll" onclick="generateAll()" class="btn btn-primary">
        &#9889; Generar todas
    </button>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.65rem 1rem;margin-bottom:1rem;font-size:.84rem;color:#15803d;">
    {{ session('success') }}
</div>
@endif

{{-- ── IMAGE GRID ───────────────────────────────────────────────────── --}}
<div class="img-grid" id="imageGrid">

@php
$keys = ['featured', 'interior_1', 'interior_2', 'interior_3'];
@endphp

@foreach($keys as $key)
@php $img = $imageData[$key]; @endphp
<div class="img-card" id="card-{{ $key }}">
    <div class="img-card-header">
        <span class="img-card-label">{{ $img['label'] }}</span>
        @if($img['url'])
        <span style="font-size:.7rem;font-weight:700;color:#10b981;">&#10003; Generada</span>
        @else
        <span style="font-size:.7rem;font-weight:700;color:#f59e0b;">Pendiente</span>
        @endif
    </div>
    <div class="img-card-body">

        {{-- Preview --}}
        <div class="img-preview-wrap">
            @if($img['url'])
            <img id="img-{{ $key }}" src="{{ $img['url'] }}" alt="{{ $img['label'] }}">
            @else
            <div class="img-placeholder" id="placeholder-{{ $key }}">
                <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                <span>Sin imagen</span>
            </div>
            <img id="img-{{ $key }}" src="" alt="{{ $img['label'] }}" style="display:none;">
            @endif
            <div class="img-loading-overlay" id="loading-{{ $key }}">
                <div class="spinner"></div>
                <span>Generando con DALL-E…</span>
            </div>
        </div>

        {{-- Error --}}
        <div class="img-error" id="error-{{ $key }}"></div>

        {{-- Prompt --}}
        @if($img['prompt'])
        <div>
            <div style="font-size:.67rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.25rem;">Prompt DALL-E</div>
            <div class="prompt-box">{{ $img['prompt'] }}</div>
        </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex;gap:.5rem;">
            <button
                id="btn-{{ $key }}"
                onclick="generateSingle('{{ $key }}')"
                class="btn btn-outline btn-sm"
                style="flex:1;justify-content:center;">
                @if($img['url']) &#8635; Re-generar @else &#9889; Generar @endif
            </button>
        </div>

    </div>
</div>
@endforeach

</div>

{{-- ── BOTTOM BAR ───────────────────────────────────────────────────── --}}
<div class="bottom-bar">
    <a href="{{ route('admin.blog.generator') }}" class="btn btn-outline btn-sm">← Nuevo artículo</a>

    <div style="display:flex;align-items:center;gap:1rem;">
        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-outline btn-sm">
            Saltar imágenes →
        </a>
        <form method="POST" action="{{ route('admin.blog.finalize-images', $post) }}">
            @csrf
            <button type="submit" class="btn btn-success" id="btnFinalize">
                Continuar al editor →
            </button>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF          = '{{ csrf_token() }}';
const URL_ALL       = '{{ route('admin.blog.generate-all-images', $post) }}';
const URL_SINGLE    = '{{ route('admin.blog.regenerate-image', $post) }}';

function setLoading(key, on) {
    document.getElementById(`loading-${key}`).classList.toggle('active', on);
    const btn = document.getElementById(`btn-${key}`);
    if (btn) btn.disabled = on;
}

function showError(key, msg) {
    const el = document.getElementById(`error-${key}`);
    el.textContent = msg;
    el.classList.toggle('active', !!msg);
}

function applyImage(key, url) {
    const img         = document.getElementById(`img-${key}`);
    const placeholder = document.getElementById(`placeholder-${key}`);
    const card        = document.getElementById(`card-${key}`);
    const btn         = document.getElementById(`btn-${key}`);

    img.src = url;
    img.style.display = '';
    if (placeholder) placeholder.style.display = 'none';

    // Update badge
    const header = card.querySelector('.img-card-header span:last-child');
    if (header) { header.textContent = '✓ Generada'; header.style.color = '#10b981'; }

    // Update button label
    if (btn) btn.innerHTML = '↻ Re-generar';
}

async function generateSingle(key) {
    setLoading(key, true);
    showError(key, '');

    try {
        const r = await fetch(URL_SINGLE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ key }),
        });
        const d = await r.json();
        if (d.success) {
            applyImage(key, d.url);
        } else {
            showError(key, d.error ?? 'Error desconocido');
        }
    } catch(e) {
        showError(key, 'Error de red: ' + e.message);
    } finally {
        setLoading(key, false);
    }
}

async function generateAll() {
    const keys = ['featured', 'interior_1', 'interior_2', 'interior_3'];
    keys.forEach(k => { setLoading(k, true); showError(k, ''); });

    const btnAll = document.getElementById('btnGenerateAll');
    btnAll.disabled = true;
    btnAll.textContent = 'Generando…';

    try {
        const r = await fetch(URL_ALL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({}),
        });
        const d = await r.json();
        if (d.success && d.urls) {
            keys.forEach(k => { if (d.urls[k]) applyImage(k, d.urls[k]); });
        } else {
            keys.forEach(k => showError(k, d.error ?? 'Error desconocido'));
        }
    } catch(e) {
        keys.forEach(k => showError(k, 'Error de red'));
    } finally {
        keys.forEach(k => setLoading(k, false));
        btnAll.disabled = false;
        btnAll.innerHTML = '&#9889; Generar todas';
    }
}
</script>
@endsection
