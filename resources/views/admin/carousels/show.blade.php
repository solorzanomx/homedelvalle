@extends('layouts.app-sidebar')
@section('title', $carousel->title)

@section('styles')
/* ─── Animations ─────────────────────────────────────────────────────── */
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
@keyframes spin    { to { transform: rotate(360deg); } }
@keyframes fadeIn  { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
@keyframes pulse   { 0%,100%{opacity:1} 50%{opacity:.5} }

.skeleton-box {
    background: linear-gradient(90deg, #f0f2f5 25%, #e8eaed 50%, #f0f2f5 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite ease-in-out;
    border-radius: 6px;
}
.spin         { animation: spin 0.85s linear infinite; display:inline-block; }
.anim-fade-in { animation: fadeIn 0.3s ease both; }
.anim-pulse   { animation: pulse 1.5s ease infinite; }

/* ─── Progress stepper ───────────────────────────────────────────────── */
.stepper {
    display:flex; align-items:center; gap:0;
    background:#fff; border:1px solid #e5e7eb; border-radius:10px;
    padding:.75rem 1.5rem; margin-bottom:1.5rem; overflow-x:auto;
}
.step {
    display:flex; align-items:center; gap:.45rem; flex-shrink:0;
    font-size:.8rem; font-weight:500; color:#9ca3af;
    padding:.2rem .4rem; border-radius:6px; transition:all .2s;
}
.step-num {
    width:24px; height:24px; border-radius:50%; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-size:.7rem; font-weight:700;
    border:2px solid #d1d5db; color:#9ca3af; background:#fff;
    transition:all .2s;
}
.step.done .step-num   { background:#10b981; border-color:#10b981; color:#fff; }
.step.active .step-num { background:#2563eb; border-color:#2563eb; color:#fff; }
.step.done             { color:#374151; }
.step.active           { color:#1d4ed8; font-weight:600; }
.step-divider          { flex:1; height:1px; background:#e5e7eb; margin:0 .5rem; min-width:24px; }

/* ─── Slide navigator ────────────────────────────────────────────────── */
.slide-nav-row {
    display:flex; gap:.6rem; padding:1rem 1.25rem;
    overflow-x:auto; border-bottom:1px solid #f0f2f5;
    scrollbar-width:thin; scrollbar-color:#e5e7eb transparent;
}
.slide-nav-item {
    flex-shrink:0; width:72px; cursor:pointer;
    border-radius:7px; overflow:hidden;
    border:2px solid transparent; transition:all .15s;
    position:relative;
}
.slide-nav-item:hover          { border-color:#c7d2fe; }
.slide-nav-item.is-active      { border-color:#2563eb; box-shadow:0 0 0 2px rgba(37,99,235,.15); }
.slide-nav-thumb {
    width:100%; aspect-ratio:4/5;
    background:#f8fafc;
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; position:relative; font-size:.75rem;
    color:#9ca3af; font-weight:700;
}
.slide-nav-thumb img    { width:100%; height:100%; object-fit:cover; }
.slide-nav-badge {
    position:absolute; bottom:3px; right:3px;
    width:14px; height:14px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:.5rem; font-weight:700; border:1.5px solid #fff;
}
.slide-nav-badge.done      { background:#10b981; color:#fff; }
.slide-nav-badge.failed    { background:#ef4444; color:#fff; }
.slide-nav-badge.rendering { background:#f59e0b; color:#fff; }
.slide-nav-badge.stale     { background:#f97316; color:#fff; }
.slide-nav-label {
    padding:3px 5px; font-size:.6rem; font-weight:600;
    color:#6b7280; text-align:center;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    background:#fff;
}

/* ─── Slide editor panel ─────────────────────────────────────────────── */
.editor-panel {
    padding:1.25rem;
    display:grid; grid-template-columns:160px 1fr;
    gap:1.25rem; align-items:start;
}
@media(max-width:640px) { .editor-panel { grid-template-columns:1fr; } }

.editor-preview {
    border-radius:8px; overflow:hidden;
    border:1px solid #e5e7eb; position:relative;
    aspect-ratio:4/5; background:#f8fafc;
    display:flex; align-items:center; justify-content:center;
    cursor:zoom-in;
}
.editor-preview img { width:100%; height:100%; object-fit:cover; }

/* ─── Lightbox ───────────────────────────────────────────────────────── */
.lb-overlay {
    position:fixed; inset:0; z-index:500;
    background:rgba(0,0,0,.88); backdrop-filter:blur(4px);
    display:flex; align-items:center; justify-content:center;
    cursor:zoom-out;
    animation:fadeIn .18s ease;
}
.lb-img {
    max-width:90vw; max-height:92vh;
    object-fit:contain; border-radius:6px;
    box-shadow:0 30px 80px rgba(0,0,0,.5);
    cursor:default;
    animation:fadeIn .2s ease;
}
.lb-close {
    position:absolute; top:1rem; right:1rem;
    width:36px; height:36px; border-radius:50%;
    background:rgba(255,255,255,.12); border:none; cursor:pointer;
    color:#fff; font-size:1.1rem; display:flex; align-items:center; justify-content:center;
    transition:background .15s;
}
.lb-close:hover { background:rgba(255,255,255,.22); }
.lb-label {
    position:absolute; bottom:1rem; left:50%; transform:translateX(-50%);
    font-size:.72rem; color:rgba(255,255,255,.5); letter-spacing:.5px;
    white-space:nowrap;
}

.editor-fields { display:flex; flex-direction:column; gap:.65rem; }

.field-row label {
    display:block; font-size:.7rem; font-weight:600;
    text-transform:uppercase; letter-spacing:.4px;
    color:#9ca3af; margin-bottom:.25rem;
}
.field-row input,
.field-row textarea {
    width:100%; border:1px solid #e5e7eb; border-radius:6px;
    padding:.45rem .7rem; font-size:.85rem; font-family:inherit;
    transition:border-color .15s, box-shadow .15s; resize:vertical;
    background:#fff; color:#111827;
}
.field-row input:focus,
.field-row textarea:focus {
    outline:none; border-color:#6366f1;
    box-shadow:0 0 0 3px rgba(99,102,241,.1);
}
.field-row input:disabled,
.field-row textarea:disabled { opacity:.5; cursor:not-allowed; background:#f9fafb; }

.char-count { font-size:.67rem; color:#9ca3af; float:right; margin-top:2px; }

/* ─── Save status pill ───────────────────────────────────────────────── */
.save-pill {
    display:inline-flex; align-items:center; gap:.3rem;
    font-size:.72rem; font-weight:500; padding:.2rem .6rem;
    border-radius:20px; transition:all .2s;
}
.save-pill.idle       { opacity:0; }
.save-pill.editing    { opacity:1; background:#f3f4f6; color:#6b7280; }
.save-pill.saving     { opacity:1; background:#eff6ff; color:#2563eb; }
.save-pill.saved      { opacity:1; background:#f0fdf4; color:#16a34a; }
.save-pill.failed     { opacity:1; background:#fef2f2; color:#dc2626; }
.save-pill.generating { opacity:1; background:#fffbeb; color:#d97706; }

/* ─── Image controls ─────────────────────────────────────────────────── */
.img-controls {
    display:flex; gap:.4rem; flex-wrap:wrap; margin-top:.5rem;
    padding-top:.75rem; border-top:1px solid #f0f2f5;
}
.img-btn {
    flex:1; min-width:60px; font-size:.7rem; padding:.35rem .5rem;
    border-radius:5px; cursor:pointer; text-align:center;
    white-space:nowrap; border:1px solid; font-weight:500;
    font-family:inherit; transition:opacity .15s;
}
.img-btn:hover   { opacity:.8; }
.img-btn:disabled { opacity:.4; cursor:not-allowed; }
.img-btn-ai   { background:#eff6ff; border-color:#bfdbfe; color:#1d4ed8; }
.img-btn-up   { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
.img-btn-rm   { background:#fef2f2; border-color:#fecaca; color:#dc2626; }
.img-btn-rdr  { background:#f5f3ff; border-color:#ddd6fe; color:#7c3aed; }

/* ─── Caption section ────────────────────────────────────────────────── */
.hashtag-pill {
    display:inline-block; background:#eff6ff; color:#1d4ed8;
    border-radius:20px; padding:.15rem .6rem;
    font-size:.75rem; font-weight:500; margin:.2rem .2rem 0 0;
}

/* ─── Progress sidebar card ──────────────────────────────────────────── */
.progress-metric {
    display:flex; align-items:center; justify-content:space-between;
    padding:.55rem 0; border-bottom:1px solid #f0f2f5; font-size:.83rem;
}
.progress-metric:last-child { border-bottom:none; }
.progress-bar-wrap { flex:1; margin:0 .75rem; height:6px; background:#f0f2f5; border-radius:4px; overflow:hidden; }
.progress-bar-fill { height:100%; border-radius:4px; transition:width .5s ease; }

/* ─── Sticky CTA ─────────────────────────────────────────────────────── */
.sticky-cta {
    position:fixed; bottom:0; left:260px; right:0; z-index:80;
    background:#fff; border-top:1px solid #e5e7eb;
    box-shadow:0 -4px 20px rgba(0,0,0,.07);
    display:flex; align-items:center; justify-content:space-between;
    padding:.7rem 1.5rem;
}
@media(max-width:768px) { .sticky-cta { left:0; } }

/* ─── Generate overlay ───────────────────────────────────────────────── */
.gen-overlay {
    position:fixed; inset:0; z-index:200;
    background:rgba(15,23,42,.65); backdrop-filter:blur(3px);
    display:flex; align-items:center; justify-content:center;
}
.gen-card {
    background:#fff; border-radius:16px; padding:2rem 2.5rem;
    max-width:380px; width:90%; text-align:center;
    box-shadow:0 25px 60px rgba(0,0,0,.25);
}
.gen-spinner {
    width:48px; height:48px; border:3px solid #e0e7ff;
    border-top-color:#4f46e5; border-radius:50%;
    animation:spin .8s linear infinite; margin:0 auto 1.25rem;
}

/* ─── Upload progress ────────────────────────────────────────────────── */
.upload-progress {
    height:4px; background:#e0e7ff; border-radius:4px;
    overflow:hidden; margin-top:.5rem;
}
.upload-progress-fill {
    height:100%; background:#4f46e5; border-radius:4px;
    transition:width .2s;
}
@endsection

@section('content')
@php
    $totalSlides    = $carousel->slides->count();
    $doneSlides     = $carousel->slides->where('render_status','done')->count();
    $failedSlides   = $carousel->slides->where('render_status','failed')->count();
    $hasBg          = $carousel->slides->whereNotNull('background_image_path')->count();
    $pendingRender  = $carousel->slides->whereIn('render_status',['pending','rendering'])->count();
    $firstSlide     = $carousel->slides->first();

    $stepContent = $totalSlides > 0                                            ? 'done'   : 'active';
    $stepImages  = $hasBg > 0                                                  ? 'done'   : ($totalSlides > 0 ? 'active' : '');
    $stepRender  = $totalSlides > 0 && $doneSlides === $totalSlides            ? 'done'   : ($doneSlides > 0 || $pendingRender > 0 ? 'active' : '');
    $stepApprove = in_array($carousel->status,['approved','published'])        ? 'done'   : ($carousel->status === 'review' ? 'active' : '');
@endphp

{{-- ════ PAGE HEADER ════ --}}
<div class="page-header" style="padding-bottom:.75rem;">
    <div style="min-width:0;">
        <h2 style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($carousel->title,70) }}</h2>
        <div style="display:flex;align-items:center;gap:.5rem;margin-top:4px;flex-wrap:wrap;">
            <span class="badge badge-{{ $carousel->status_color }}" style="font-size:.75rem;">{{ $carousel->status_label }}</span>
            <span style="font-size:.8rem;color:#6b7280;">{{ ucfirst($carousel->type) }}</span>
            <span style="font-size:.8rem;color:#d1d5db;">·</span>
            <span style="font-size:.8rem;color:#6b7280;">{{ $totalSlides }} diapositivas</span>
            @if($carousel->template)
                <span style="font-size:.8rem;color:#d1d5db;">·</span>
                <span style="font-size:.8rem;color:#6b7280;">{{ $carousel->template->name }}</span>
            @endif
        </div>
    </div>
    <div style="display:flex;gap:.5rem;flex-shrink:0;">
        <a href="{{ route('admin.carousels.edit',$carousel) }}" class="btn btn-outline btn-sm">Editar datos</a>
        <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline btn-sm">← Carruseles</a>
    </div>
</div>

{{-- ════ STEPPER ════ --}}
<div class="stepper">
    <div class="step {{ $stepContent }}">
        <div class="step-num">{{ $stepContent==='done' ? '✓' : '1' }}</div><span>Contenido</span>
    </div>
    <div class="step-divider"></div>
    <div class="step {{ $stepImages }}">
        <div class="step-num">{{ $stepImages==='done' ? '✓' : '2' }}</div><span>Imágenes</span>
    </div>
    <div class="step-divider"></div>
    <div class="step {{ $stepRender }}">
        <div class="step-num">{{ $stepRender==='done' ? '✓' : '3' }}</div><span>Render</span>
    </div>
    <div class="step-divider"></div>
    <div class="step {{ $stepApprove }}">
        <div class="step-num">{{ $stepApprove==='done' ? '✓' : '4' }}</div><span>Aprobación</span>
    </div>
</div>

{{-- ════ MAIN ALPINE COMPONENT ════ --}}
<div
    x-data="carouselEditor({
        carouselId: {{ $carousel->id }},
        csrf:       '{{ csrf_token() }}',
        statusUrl:  '{{ route('admin.carousels.render.status',$carousel) }}',
        baseUrl:    '{{ url('/admin/carousels/'.$carousel->id) }}',
        generating: false,
        hasPolling: {{ $pendingRender > 0 ? 'true' : 'false' }},
        autoImages: {{ session('auto_images') ? 'true' : 'false' }},
        slides: {{ Js::from($carousel->slides->map(fn($s) => [
            'id'           => $s->id,
            'order'        => $s->order,
            'type'         => $s->type,
            'typeLabel'    => $s->type_label,
            'headline'     => $s->headline ?? '',
            'subheadline'  => $s->subheadline ?? '',
            'body'         => $s->body ?? '',
            'ctaText'      => $s->cta_text ?? '',
            'renderStatus' => $s->render_status,
            'imageUrl'     => $s->rendered_image_path ? ('/storage/' . $s->rendered_image_path) : null,
            'bgPath'       => $s->background_image_path,
            'bgUrl'        => $s->background_image_path ? ('/storage/' . $s->background_image_path) : null,
            'bgGenerating' => false,
        ])) }},
        captionShort: {{ Js::from($carousel->caption_short ?? '') }},
        captionLong:  {{ Js::from($carousel->caption_long ?? '') }},
        hashtags:     {{ Js::from($carousel->hashtags ?? []) }},
        firstSlideId: {{ $firstSlide?->id ?? 'null' }},
    })"
    x-init="init()"
>

{{-- Generate content overlay --}}
<div class="gen-overlay" x-show="generating" x-cloak style="display:none;">
    <div class="gen-card">
        <div class="gen-spinner"></div>
        <div style="font-size:1.05rem;font-weight:600;color:#111827;margin-bottom:.5rem;">Generando carrusel…</div>
        <div style="font-size:.85rem;color:#6b7280;" x-text="genMessage">Claude está escribiendo tu carrusel</div>
        <div style="font-size:.75rem;color:#9ca3af;margin-top:1rem;">Esto puede tardar 15–30 segundos</div>
    </div>
</div>

{{-- Generate images overlay --}}
<div class="gen-overlay" x-show="generatingImages" x-cloak style="display:none;">
    <div class="gen-card">
        <div class="gen-spinner"></div>
        <div style="font-size:1.05rem;font-weight:600;color:#111827;margin-bottom:.5rem;">Generando imágenes con DALL-E…</div>
        <div style="font-size:.85rem;color:#6b7280;" x-text="imgGenMessage">Preparando imágenes…</div>
        <div style="font-size:.75rem;color:#9ca3af;margin-top:1rem;">Cada imagen tarda ~30 segundos. No cierres esta ventana.</div>
        <div style="margin-top:.75rem;background:#f3f4f6;border-radius:6px;height:6px;overflow:hidden;">
            <div style="height:100%;background:#2563eb;transition:width .4s ease;"
                 :style="'width:'+imgGenProgress+'%'"></div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 290px; gap:1.5rem; align-items:start; padding-bottom:80px;">

    {{-- ══ MAIN COLUMN ══ --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- ── SLIDES CARD ── --}}
        <div class="card">
            <div class="card-header" style="align-items:center;">
                <h3 class="card-title">Diapositivas</h3>
                <div style="display:flex;align-items:center;gap:.75rem;margin-left:auto;">

                    {{-- Render progress indicator --}}
                    <span x-show="isPolling" style="display:flex;align-items:center;gap:.35rem;font-size:.78rem;color:#6b7280;">
                        <span class="spin" style="font-size:.9rem;">⟳</span>
                        Renderizando <span x-text="renderDone+'/'+renderTotal"></span>
                    </span>
                    <span x-show="!isPolling && renderDone > 0 && !isPolling" style="font-size:.78rem;color:#16a34a;">
                        ✓ <span x-text="renderDone"></span> listas
                    </span>

                    {{-- Save pill --}}
                    <span class="save-pill" :class="saveState" style="font-size:.72rem;">
                        <span x-show="saveState==='saving'" class="spin" style="font-size:.75rem;">⟳</span>
                        <span x-text="{idle:'',editing:'Editando…',saving:'Guardando…',saved:'Guardado ✓',failed:'Error al guardar',generating:'Generando…'}[saveState]||''"></span>
                    </span>

                    <span style="font-size:.8rem;color:#9ca3af;">{{ $totalSlides }} slides</span>
                </div>
            </div>

            @if($totalSlides > 0)

                {{-- ── NAVIGATOR ROW ── --}}
                <div class="slide-nav-row">
                    <template x-for="s in slides" :key="s.id">
                        <div class="slide-nav-item"
                             :class="{ 'is-active': activeSlideId === s.id }"
                             @click="selectSlide(s.id)">
                            <div class="slide-nav-thumb">
                                    {{-- 1. Render final --}}
                                <img x-show="s.imageUrl" :src="s.imageUrl" loading="lazy"
                                     style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">

                                {{-- 2. Bg generada (sin render) — sin opacidad --}}
                                <img x-show="!s.imageUrl && s.bgUrl" :src="s.bgUrl" loading="lazy"
                                     style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">

                                {{-- 3. Skeleton SOLO cuando no hay ninguna imagen y está renderizando --}}
                                <div x-show="!s.imageUrl && !s.bgUrl && (s.renderStatus==='rendering'||s.renderStatus==='pending')"
                                     class="skeleton-box"
                                     style="position:absolute;inset:0;border-radius:0;">
                                </div>

                                {{-- 4. Número placeholder cuando no hay nada --}}
                                <span x-show="!s.imageUrl && !s.bgUrl && !['rendering','pending'].includes(s.renderStatus)"
                                      x-text="s.order"
                                      style="font-size:.9rem;font-weight:700;color:#9ca3af;position:relative;z-index:1;">
                                </span>

                                {{-- 5. DALL-E overlay --}}
                                <div x-show="s.bgGenerating"
                                     style="position:absolute;inset:0;background:rgba(15,23,42,.75);display:flex;align-items:center;justify-content:center;z-index:5;">
                                    <span class="spin" style="font-size:1rem;color:#818cf8;">⟳</span>
                                </div>

                                {{-- 6. Status badge --}}
                                <span class="slide-nav-badge"
                                      x-show="!s.bgGenerating && s.renderStatus==='done'"
                                      class="done">✓</span>
                            </div>
                            <div class="slide-nav-label" x-text="s.typeLabel"></div>
                        </div>
                    </template>
                </div>

                {{-- ── ACTIVE SLIDE EDITOR ── --}}
                <div x-show="activeSlide" class="editor-panel anim-fade-in">

                    {{-- Preview column --}}
                    <div>
                        <div class="editor-preview"
                             @click="openLightbox(activeSlide)"
                             :style="!(activeSlide?.imageUrl||activeSlide?.bgUrl) ? 'cursor:default' : ''">

                            {{-- 1. Render final PNG (prioridad máxima) --}}
                            <img x-show="activeSlide?.imageUrl"
                                 :src="activeSlide?.imageUrl"
                                 style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">

                            {{-- 2. Imagen de fondo (bg lista, sin render) — sin opacidad, se ve completa --}}
                            <img x-show="!activeSlide?.imageUrl && activeSlide?.bgUrl"
                                 :src="activeSlide?.bgUrl"
                                 style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;">

                            {{-- 3. Skeleton — solo cuando renderizando SIN ninguna imagen --}}
                            <div x-show="!activeSlide?.imageUrl && !activeSlide?.bgUrl && (activeSlide?.renderStatus==='rendering'||activeSlide?.renderStatus==='pending')"
                                 class="skeleton-box"
                                 style="position:absolute;inset:0;border-radius:7px;">
                                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.4rem;">
                                    <span class="spin" style="font-size:1.2rem;color:#9ca3af;">⟳</span>
                                    <span style="font-size:.65rem;color:#9ca3af;">Renderizando…</span>
                                </div>
                            </div>

                            {{-- 4. Error --}}
                            <div x-show="!activeSlide?.imageUrl && !activeSlide?.bgUrl && activeSlide?.renderStatus==='failed'"
                                 style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.35rem;padding:.75rem;text-align:center;">
                                <span style="font-size:1.4rem;">⚠</span>
                                <span style="font-size:.67rem;color:#ef4444;">Error de render</span>
                            </div>

                            {{-- 5. Vacío --}}
                            <div x-show="!activeSlide?.imageUrl && !activeSlide?.bgUrl && !['rendering','pending','failed'].includes(activeSlide?.renderStatus)"
                                 style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.3rem;color:#d1d5db;">
                                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="1.5"/>
                                    <circle cx="8.5" cy="8.5" r="1.5" stroke-width="1.5"/>
                                    <path d="m21 15-5-5L5 21" stroke-width="1.5"/>
                                </svg>
                                <span style="font-size:.65rem;">Sin imagen</span>
                            </div>

                            {{-- 6. Stale badge --}}
                            <div x-show="activeSlide?.renderStatus==='stale' && !activeSlide?.bgGenerating"
                                 style="position:absolute;bottom:0;left:0;right:0;background:rgba(249,115,22,.88);padding:4px;text-align:center;z-index:3;">
                                <span style="font-size:.6rem;color:#fff;font-weight:600;">Cambios sin renderizar</span>
                            </div>

                            {{-- 7. DALL-E generating overlay --}}
                            <div x-show="activeSlide?.bgGenerating"
                                 style="position:absolute;inset:0;background:rgba(15,23,42,.78);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.6rem;z-index:10;">
                                <span class="spin" style="font-size:1.6rem;color:#818cf8;">⟳</span>
                                <span style="font-size:.68rem;color:#e0e7ff;font-weight:600;text-align:center;padding:0 .5rem;">Generando con DALL-E 3…</span>
                                <span style="font-size:.6rem;color:#6b7280;">~30 segundos</span>
                            </div>

                            {{-- Zoom hint cuando hay imagen --}}
                            <div x-show="(activeSlide?.imageUrl || activeSlide?.bgUrl) && !activeSlide?.bgGenerating"
                                 style="position:absolute;top:.4rem;right:.4rem;background:rgba(0,0,0,.45);border-radius:4px;padding:2px 5px;z-index:4;pointer-events:none;">
                                <span style="font-size:.6rem;color:rgba(255,255,255,.8);">🔍</span>
                            </div>

                        </div>
                        {{-- Slide type + order label --}}
                        <div style="margin-top:.5rem;text-align:center;">
                            <span style="font-size:.68rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;"
                                  x-text="'Slide ' + (activeSlide?.order??'') + ' — ' + (activeSlide?.typeLabel??'')">
                            </span>
                        </div>
                    </div>

                    {{-- Fields column --}}
                    <div class="editor-fields" x-show="activeSlide">
                        <div class="field-row">
                            <label>
                                Titular
                                <span class="char-count" x-text="(activeSlide?.headline?.length||0)+'/90'"></span>
                            </label>
                            <input type="text" maxlength="90"
                                   :value="activeSlide?.headline"
                                   @input="onFieldInput('headline', $event.target.value)"
                                   :disabled="saveState==='generating'"
                                   placeholder="Titular principal del slide…">
                        </div>
                        <div class="field-row">
                            <label>
                                Subtítulo
                                <span class="char-count" x-text="(activeSlide?.subheadline?.length||0)+'/120'"></span>
                            </label>
                            <input type="text" maxlength="120"
                                   :value="activeSlide?.subheadline"
                                   @input="onFieldInput('subheadline', $event.target.value)"
                                   :disabled="saveState==='generating'"
                                   placeholder="Subtítulo opcional…">
                        </div>
                        <div class="field-row">
                            <label>
                                Texto
                                <span class="char-count" x-text="(activeSlide?.body?.length||0)+'/280'"></span>
                            </label>
                            <textarea rows="3" maxlength="280"
                                      :value="activeSlide?.body"
                                      @input="onFieldInput('body', $event.target.value)"
                                      :disabled="saveState==='generating'"
                                      placeholder="Texto de cuerpo del slide…"></textarea>
                        </div>
                        <div class="field-row" x-show="activeSlide?.type==='cta'">
                            <label>
                                Texto del CTA
                                <span class="char-count" x-text="(activeSlide?.ctaText?.length||0)+'/60'"></span>
                            </label>
                            <input type="text" maxlength="60"
                                   :value="activeSlide?.ctaText"
                                   @input="onFieldInput('ctaText', $event.target.value)"
                                   :disabled="saveState==='generating'"
                                   placeholder="Agendar visita · Escríbenos…">
                        </div>

                        {{-- Image controls --}}
                        <div class="img-controls">
                            {{-- AI generate image --}}
                            <button type="button" class="img-btn img-btn-ai"
                                    @click="generateSlideImage(activeSlide.id)"
                                    :disabled="activeSlide?.bgGenerating">
                                <span x-show="!activeSlide?.bgGenerating">🎨 Imagen IA</span>
                                <span x-show="activeSlide?.bgGenerating"><span class="spin">⟳</span> Generando…</span>
                            </button>

                            {{-- Upload background --}}
                            <label class="img-btn img-btn-up" style="cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.3rem;">
                                📁 Subir
                                <input type="file" accept="image/*" style="display:none;"
                                       @change="uploadBackground(activeSlide.id, $event)">
                            </label>

                            {{-- Render this slide --}}
                            <button type="button" class="img-btn img-btn-rdr"
                                    @click="renderSingleSlide(activeSlide.id)"
                                    :disabled="!activeSlide?.bgUrl && !activeSlide?.imageUrl">
                                ⬡ Renderizar
                            </button>

                            {{-- Remove background --}}
                            <button type="button" class="img-btn img-btn-rm"
                                    x-show="activeSlide?.bgUrl"
                                    @click="removeBackground(activeSlide.id)">
                                ✕ Quitar foto
                            </button>

                            {{-- Clear render --}}
                            <button type="button" class="img-btn img-btn-rm"
                                    x-show="activeSlide?.imageUrl"
                                    @click="clearRender(activeSlide.id)"
                                    title="Borra el PNG renderizado para poder cambiar de plantilla o re-renderizar">
                                ✕ Borrar render
                            </button>
                        </div>

                        {{-- Background indicator --}}
                        <div x-show="activeSlide?.bgUrl"
                             style="font-size:.7rem;color:#16a34a;display:flex;align-items:center;gap:.3rem;margin-top:.25rem;">
                            <span>✓</span>
                            <span>Imagen de fondo asignada</span>
                        </div>

                        {{-- Render error --}}
                        <div x-show="activeSlide?.renderStatus==='failed'"
                             style="padding:.6rem .8rem;background:#fef2f2;border-radius:5px;font-size:.76rem;color:#dc2626;margin-top:.25rem;">
                            ⚠ Error en el último render.
                            <button type="button" style="text-decoration:underline;background:none;border:none;color:#dc2626;cursor:pointer;font-size:.76rem;"
                                    @click="renderSingleSlide(activeSlide.id)">
                                Reintentar
                            </button>
                        </div>
                    </div>
                </div>

            @else
                {{-- Empty state --}}
                <div style="text-align:center;padding:3rem 2rem;">
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.6rem;max-width:280px;margin:0 auto 1.5rem;opacity:.35;pointer-events:none;">
                        @for($i=0;$i<8;$i++)
                            <div class="skeleton-box" style="aspect-ratio:1;"></div>
                        @endfor
                    </div>
                    @if($carousel->isEditable())
                        <p style="font-weight:600;color:#374151;margin-bottom:.4rem;">Sin diapositivas todavía</p>
                        <p style="font-size:.83rem;color:#9ca3af;margin-bottom:1.25rem;">Genera el contenido con IA para empezar.</p>
                        <a href="{{ route('admin.carousels.generate',$carousel) }}"
                           class="btn btn-primary"
                           @click.prevent="openGenerate">
                            ✦ Generar con IA
                        </a>
                    @else
                        <p style="font-size:.83rem;color:#9ca3af;">Las diapositivas se generarán automáticamente.</p>
                    @endif
                </div>
            @endif
        </div>

        {{-- ── CAPTION CARD ── --}}
        @if($carousel->caption_short || $carousel->caption_long || $carousel->hashtags)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Caption para Instagram</h3>
                <div style="display:flex;gap:.5rem;align-items:center;">
                    <span class="save-pill" :class="captionState" style="font-size:.72rem;">
                        <span x-show="captionState==='saving'" class="spin" style="font-size:.75rem;">⟳</span>
                        <span x-text="{saving:'Guardando…',saved:'Guardado ✓',failed:'Error'}[captionState]||''"></span>
                    </span>
                    <button type="button" class="btn btn-outline btn-sm"
                            @click="regenerateCaption()"
                            :disabled="captionState==='saving'">
                        ↺ Regenerar
                    </button>
                </div>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1rem;">
                {{-- Short caption --}}
                <div>
                    <label style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;color:#9ca3af;margin-bottom:.35rem;display:block;">
                        Caption corto
                        <span style="float:right;font-weight:400;" x-text="(captionShort?.length||0)+'/280'"></span>
                    </label>
                    <textarea rows="2" maxlength="280"
                              x-model="captionShort"
                              @input="onCaptionInput()"
                              style="width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:.55rem .75rem;font-size:.86rem;font-family:inherit;resize:vertical;transition:border-color .15s;"
                              placeholder="Caption corto para Instagram…">{{ $carousel->caption_short }}</textarea>
                </div>
                {{-- Long caption --}}
                <div>
                    <label style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;color:#9ca3af;margin-bottom:.35rem;display:block;">
                        Caption largo
                    </label>
                    <textarea rows="5"
                              x-model="captionLong"
                              @input="onCaptionInput()"
                              style="width:100%;border:1px solid #e5e7eb;border-radius:6px;padding:.55rem .75rem;font-size:.86rem;font-family:inherit;resize:vertical;transition:border-color .15s;"
                              placeholder="Caption extendido para Instagram…">{{ $carousel->caption_long }}</textarea>
                </div>
                {{-- Hashtags --}}
                @if(!empty($carousel->hashtags))
                <div>
                    <div style="font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;color:#9ca3af;margin-bottom:.45rem;display:flex;justify-content:space-between;align-items:center;">
                        <span>Hashtags</span>
                        <button type="button"
                                style="font-size:.7rem;font-weight:500;color:#2563eb;background:none;border:none;cursor:pointer;"
                                @click="copyHashtags()">
                            Copiar todos
                        </button>
                    </div>
                    <div>
                        <template x-for="tag in hashtags" :key="tag">
                            <span class="hashtag-pill" x-text="'#'+tag"></span>
                        </template>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ── VERSION HISTORY ── --}}
        @if($carousel->versions->count() > 0)
        <details class="card" style="padding:0;">
            <summary class="card-header" style="cursor:pointer;list-style:none;user-select:none;">
                <h3 class="card-title">Historial de versiones ({{ $carousel->versions->count() }})</h3>
                <span style="color:#9ca3af;font-size:.8rem;">▾</span>
            </summary>
            <div style="padding:0;">
                <table class="data-table">
                    <thead><tr><th>Versión</th><th>Etiqueta</th><th>Creado por</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach($carousel->versions as $v)
                        <tr>
                            <td><span class="badge badge-gray">v{{ $v->version_number }}</span></td>
                            <td style="font-size:.83rem;">{{ $v->label ?? '—' }}</td>
                            <td style="font-size:.83rem;">{{ $v->creator?->name ?? '—' }}</td>
                            <td style="font-size:.78rem;color:#9ca3af;">{{ $v->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
        @endif

    </div>{{-- /main column --}}

    {{-- ══ SIDEBAR ══ --}}
    <div style="display:flex;flex-direction:column;gap:1rem;position:sticky;top:1.5rem;">

        {{-- ── PROGRESS METRICS ── --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Progreso</h3></div>
            <div class="card-body" style="padding:.5rem 1.25rem;">
                @php
                    $pContent  = $totalSlides > 0 ? 100 : 0;
                    $pImages   = $totalSlides > 0 ? round(($hasBg/$totalSlides)*100) : 0;
                    $pRender   = $totalSlides > 0 ? round(($doneSlides/$totalSlides)*100) : 0;
                @endphp
                <div class="progress-metric">
                    <span style="color:#6b7280;">Contenido</span>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width:{{ $pContent }}%;background:{{ $pContent===100?'#10b981':'#2563eb' }};"></div>
                    </div>
                    <span style="font-size:.78rem;font-weight:600;color:{{ $pContent===100?'#16a34a':'#374151' }};">{{ $totalSlides }}/{{ $totalSlides }}</span>
                </div>
                <div class="progress-metric">
                    <span style="color:#6b7280;">Imágenes</span>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" style="width:{{ $pImages }}%;background:{{ $pImages===100?'#10b981':'#2563eb' }};"></div>
                    </div>
                    <span style="font-size:.78rem;font-weight:600;color:{{ $pImages===100?'#16a34a':'#374151' }};">{{ $hasBg }}/{{ $totalSlides }}</span>
                </div>
                <div class="progress-metric" style="border-bottom:none;">
                    <span style="color:#6b7280;">Renders</span>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill"
                             :style="`width:${renderTotal>0?Math.round((renderDone/renderTotal)*100):{{ $pRender }}}%;background:${ (renderTotal>0?renderDone===renderTotal:{{ $doneSlides===$totalSlides && $totalSlides>0 ? 'true':'false' }}) ?'#10b981':'#2563eb'}`">
                        </div>
                    </div>
                    <span style="font-size:.78rem;font-weight:600;"
                          x-text="(renderTotal>0?renderDone:{{ $doneSlides }})+'/{{ $totalSlides }}'">
                    </span>
                </div>
            </div>
        </div>

        {{-- ── NEXT STEP / SMART CTA ── --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Siguiente paso</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.6rem;">

                @if($totalSlides === 0)
                    <p style="font-size:.82rem;color:#6b7280;margin-bottom:.25rem;">Genera el contenido con IA para crear las diapositivas.</p>
                    <a href="{{ route('admin.carousels.generate',$carousel) }}"
                       class="btn btn-primary"
                       @click.prevent="openGenerate">
                        ✦ Generar con IA
                    </a>

                @elseif($doneSlides < $totalSlides)
                    <p style="font-size:.82rem;color:#6b7280;margin-bottom:.25rem;">
                        {{ $totalSlides - $doneSlides }} slides pendientes de render.
                        Asegúrate de que cada slide tenga imagen de fondo.
                    </p>
                    <button type="button" class="btn btn-primary"
                            @click="renderAll()"
                            :disabled="isPolling">
                        <span x-show="!isPolling">⬡ Renderizar todo</span>
                        <span x-show="isPolling"><span class="spin">⟳</span> Renderizando…</span>
                    </button>
                    <a href="{{ route('admin.carousels.generate',$carousel) }}"
                       class="btn btn-outline btn-sm"
                       @click.prevent="openGenerate">
                        ↺ Re-generar con IA
                    </a>

                @elseif($carousel->status === 'review')
                    <p style="font-size:.82rem;color:#6b7280;margin-bottom:.25rem;">El carrusel está listo. Revisa el preview y aprueba para enviar a publicación.</p>
                    <form method="POST" action="{{ route('admin.carousels.approve',$carousel) }}"
                          @submit.prevent="approveCarousel($el.closest('form'))">
                        @csrf
                        <button type="submit" class="btn btn-primary"
                                style="width:100%;background:#16a34a;border-color:#16a34a;">
                            ✓ Aprobar carrusel
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.carousels.reject',$carousel) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">
                            ✗ Rechazar
                        </button>
                    </form>

                @elseif($carousel->status === 'approved')
                    <div style="display:flex;align-items:center;gap:.4rem;color:#16a34a;font-size:.82rem;font-weight:600;margin-bottom:.25rem;">
                        <span>✓</span><span>Aprobado {{ $carousel->approved_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <a href="{{ route('admin.carousels.download', $carousel) }}"
                       class="btn btn-primary btn-sm"
                       style="width:100%;text-align:center;background:#3b82c4;border-color:#3b82c4;">
                        ↓ Descargar slides (ZIP)
                    </a>
                    <form method="POST" action="{{ route('admin.carousels.webhook',$carousel) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">↗ Re-enviar a n8n</button>
                    </form>
                    <button class="btn btn-outline btn-sm" style="width:100%;opacity:.5;" disabled>↗ Publicar en Instagram</button>

                @else
                    <p style="font-size:.82rem;color:#6b7280;">
                        Completa el render para poder aprobar.
                    </p>
                    <a href="{{ route('admin.carousels.generate',$carousel) }}"
                       class="btn btn-outline"
                       @click.prevent="openGenerate">
                        ↺ Re-generar con IA
                    </a>
                @endif

            </div>
        </div>

        {{-- ── DETAILS ── --}}
        <details class="card" style="padding:0;">
            <summary class="card-header" style="cursor:pointer;list-style:none;user-select:none;">
                <h3 class="card-title">Detalles</h3>
                <span style="color:#9ca3af;font-size:.8rem;">▾</span>
            </summary>
            <div style="padding:0;">
                @php
                    $rows = [
                        'Tipo'       => ucfirst($carousel->type),
                        'Plantilla'  => $carousel->template?->name ?? '—',
                        'Fuente'     => $carousel->source_type ? ucfirst($carousel->source_type) : '—',
                        'CTA'        => $carousel->cta ?? '—',
                        'Creado por' => $carousel->user?->name ?? '—',
                        'Creado'     => $carousel->created_at->format('d/m/Y'),
                    ];
                    if ($carousel->approved_at) {
                        $rows['Aprobado']     = $carousel->approved_at->format('d/m/Y');
                        $rows['Aprobado por'] = $carousel->approvedBy?->name ?? '—';
                    }
                @endphp
                @foreach($rows as $label => $value)
                <div style="display:flex;justify-content:space-between;align-items:baseline;padding:.5rem 1.25rem;border-bottom:1px solid #f0f2f5;font-size:.82rem;">
                    <span style="color:#9ca3af;font-weight:600;text-transform:uppercase;font-size:.7rem;letter-spacing:.3px;">{{ $label }}</span>
                    <span style="color:#1f2937;font-weight:500;text-align:right;max-width:60%;word-break:break-word;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </details>

        {{-- ── PUBLICATIONS ── --}}
        @if($carousel->publications->count() > 0)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Publicaciones</h3></div>
            <div style="padding:0;">
                @foreach($carousel->publications as $pub)
                <div style="padding:.65rem 1.25rem;border-bottom:1px solid #f0f2f5;font-size:.82rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px;">
                        <span style="font-weight:600;">{{ ucfirst($pub->channel) }}</span>
                        <span class="badge badge-{{ $pub->status==='published'?'green':($pub->status==='failed'?'red':'yellow') }}">
                            {{ $pub->status_label ?? $pub->status }}
                        </span>
                    </div>
                    <div style="color:#9ca3af;font-size:.75rem;">{{ $pub->created_at->format('d/m/Y H:i') }}</div>
                    @if($pub->error_message)
                        <div style="color:#ef4444;font-size:.72rem;margin-top:2px;">{{ Str::limit($pub->error_message,80) }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- /sidebar --}}
</div>

{{-- ════ LIGHTBOX ════ --}}
<div class="lb-overlay" x-show="lb.open" x-cloak style="display:none;"
     @click.self="lb.open=false"
     @keydown.escape.window="lb.open=false">
    <button class="lb-close" @click="lb.open=false" title="Cerrar">✕</button>
    <img class="lb-img" :src="lb.src" :alt="lb.label">
    <div class="lb-label" x-text="lb.label"></div>
</div>

{{-- ════ STICKY CTA BAR ════ --}}
<div class="sticky-cta">
    <div style="display:flex;align-items:center;gap:.75rem;">
        <span class="badge badge-{{ $carousel->status_color }}" style="font-size:.75rem;">{{ $carousel->status_label }}</span>
        <span style="font-size:.82rem;color:#6b7280;">
            {{ $totalSlides }} slides
            @if($totalSlides > 0)
                ·
                <span x-text="(renderTotal>0?renderDone:{{ $doneSlides }})+'/{{ $totalSlides }} renders'"></span>
            @endif
        </span>
        <span x-show="isPolling" class="anim-pulse" style="font-size:.75rem;color:#f59e0b;display:flex;align-items:center;gap:.25rem;">
            <span class="spin" style="font-size:.8rem;">⟳</span> Renderizando
        </span>
    </div>

    <div style="display:flex;gap:.5rem;align-items:center;">
        @if($totalSlides === 0)
            <a href="{{ route('admin.carousels.generate',$carousel) }}"
               class="btn btn-primary"
               @click.prevent="openGenerate">✦ Generar con IA</a>
        @elseif($doneSlides < $totalSlides)
            <button type="button" class="btn btn-primary"
                    @click="renderAll()" :disabled="isPolling">
                <span x-show="!isPolling">⬡ Renderizar todo</span>
                <span x-show="isPolling"><span class="spin">⟳</span> En proceso…</span>
            </button>
        @elseif($carousel->status === 'review')
            <form method="POST" action="{{ route('admin.carousels.approve',$carousel) }}"
                  @submit.prevent="approveCarousel($el.closest('form'))">
                @csrf
                <button type="submit" class="btn btn-primary" style="background:#16a34a;border-color:#16a34a;">
                    ✓ Aprobar carrusel
                </button>
            </form>
        @elseif($carousel->status === 'approved')
            <a href="{{ route('admin.carousels.download', $carousel) }}"
               class="btn btn-primary"
               style="background:#3b82c4;border-color:#3b82c4;">
                ↓ Descargar ZIP
            </a>
            <form method="POST" action="{{ route('admin.carousels.webhook',$carousel) }}">
                @csrf
                <button type="submit" class="btn btn-outline">↗ Re-enviar a n8n</button>
            </form>
        @else
            <a href="{{ route('admin.carousels.generate',$carousel) }}"
               class="btn btn-primary"
               @click.prevent="openGenerate">↺ Re-generar con IA</a>
        @endif
    </div>
</div>

</div>{{-- /x-data --}}
@endsection

@section('scripts')
<script>
function carouselEditor(cfg) {
    return {
        // State
        slides:          cfg.slides || [],
        activeSlideId:   cfg.firstSlideId,
        saveState:       'idle',   // idle|editing|saving|saved|failed|generating
        captionShort:    cfg.captionShort,
        captionLong:     cfg.captionLong,
        hashtags:        cfg.hashtags || [],
        captionState:    'idle',
        generating:      cfg.generating,
        genMessage:      'Claude está escribiendo tu carrusel…',
        generatingImages: false,
        imgGenMessage:   '',
        imgGenProgress:  0,
        lb: { open: false, src: '', label: '' },

        // Render polling
        isPolling:  cfg.hasPolling,
        renderDone: {{ $doneSlides }},
        renderTotal:{{ $totalSlides }},
        _pollTimer: null,
        _saveTimer: null,
        _captionTimer: null,

        get activeSlide() {
            return this.slides.find(s => s.id === this.activeSlideId) || null;
        },

        init() {
            if (this.isPolling) this.startPolling();
            // Rotate gen messages
            const msgs = [
                'Claude está escribiendo tu carrusel…',
                'Analizando contexto del mercado…',
                'Generando headline para cada slide…',
                'Construyendo narrativa visual…',
                'Revisando coherencia del copy…',
                'Casi listo, últimos ajustes…',
            ];
            let i = 0;
            setInterval(() => {
                if (!this.generating) return;
                i = (i + 1) % msgs.length;
                this.genMessage = msgs[i];
            }, 3000);

            // Auto-generate images when redirected from content generation
            if (cfg.autoImages && this.slides.length > 0) {
                setTimeout(() => this.generateAllImages(), 800);
            }
        },

        // ── Slide selection ──────────────────────────────────────────────────
        selectSlide(id) {
            this.activeSlideId = id;
        },

        // ── Field autosave ───────────────────────────────────────────────────
        onFieldInput(field, value) {
            const s = this.activeSlide;
            if (!s) return;
            if (field === 'ctaText') s.ctaText = value;
            else s[field] = value;
            this.saveState = 'editing';
            clearTimeout(this._saveTimer);
            this._saveTimer = setTimeout(() => this.saveSlide(s), 800);
        },

        async saveSlide(slide) {
            this.saveState = 'saving';
            try {
                const r = await fetch(`${cfg.baseUrl}/slides/${slide.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':  cfg.csrf,
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify({
                        headline:    slide.headline,
                        subheadline: slide.subheadline,
                        body:        slide.body,
                        cta_text:    slide.ctaText,
                    }),
                });
                if (!r.ok) throw new Error('HTTP ' + r.status);
                const d = await r.json();
                if (d.stale) slide.renderStatus = 'stale';
                this.saveState = 'saved';
                setTimeout(() => this.saveState = 'idle', 2500);
            } catch(e) {
                this.saveState = 'failed';
                window.toast('Error al guardar el slide. Intenta de nuevo.', 'error', 4000);
            }
        },

        // ── Render ───────────────────────────────────────────────────────────
        async renderAll() {
            this.slides.forEach(s => { s.renderStatus = 'rendering'; });
            window.toast('Renderizando todos los slides… (~20s por slide)', 'info', 5000);

            try {
                const r = await fetch(`${cfg.baseUrl}/render`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                });
                const d = await r.json();

                if (r.ok && d.slides) {
                    d.slides.forEach(upd => {
                        const s = this.slides.find(x => x.id === upd.id);
                        if (!s) return;
                        s.renderStatus = upd.render_status;
                        if (upd.image_url) s.imageUrl = upd.image_url + '?t=' + Date.now();
                        if (upd.error)     s.renderError = upd.error;
                    });
                    this.renderDone  = d.done;
                    this.renderTotal = this.slides.length;
                    if (d.failed > 0) {
                        window.toast(`${d.done} renderizados. ${d.failed} con error.`, 'error', 6000);
                    } else {
                        window.toast(`${d.done} slides renderizados ✓`, 'success', 4000);
                    }
                } else {
                    this.slides.forEach(s => { if (s.renderStatus === 'rendering') s.renderStatus = 'failed'; });
                    window.toast('Error al renderizar.', 'error', 4000);
                }
            } catch(e) {
                this.slides.forEach(s => { if (s.renderStatus === 'rendering') s.renderStatus = 'failed'; });
                window.toast('Error de red al renderizar.', 'error', 4000);
            }
        },

        async renderSingleSlide(slideId) {
            const s = this.slides.find(x => x.id === slideId);
            if (s) s.renderStatus = 'rendering';
            window.toast('Renderizando slide… (~20s)', 'info', 4000);

            try {
                const r = await fetch(`${cfg.baseUrl}/slides/${slideId}/render`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                });
                const d = await r.json();

                if (r.ok && d.ok) {
                    if (s) {
                        s.renderStatus = d.render_status;
                        if (d.image_url) s.imageUrl = d.image_url + '?t=' + Date.now();
                    }
                    window.toast('Slide renderizado ✓', 'success', 3000);
                } else {
                    if (s) s.renderStatus = 'failed';
                    window.toast('Error: ' + (d.message || 'No se pudo renderizar.'), 'error', 6000);
                }
            } catch(e) {
                if (s) s.renderStatus = 'failed';
                window.toast('Error de red al renderizar.', 'error', 4000);
            }
        },

        startPolling() {
            clearInterval(this._pollTimer);
            this._pollTimer = setInterval(() => this.poll(), 2500);
        },

        async poll() {
            try {
                const r = await fetch(cfg.statusUrl);
                const d = await r.json();
                this.renderDone  = d.done;
                this.renderTotal = d.total;

                d.slides.forEach(upd => {
                    const s = this.slides.find(x => x.id === upd.id);
                    if (!s) return;
                    s.renderStatus = upd.render_status;
                    if (upd.render_status === 'done' && upd.image_url) {
                        s.imageUrl = upd.image_url + '?t=' + Date.now();
                    }
                    // Update bg_url if it appeared (async image generation finished)
                    if (upd.bg_url && upd.bg_url !== s.bgUrl) {
                        s.bgUrl = upd.bg_url + '?t=' + Date.now();
                        s.bgPath = upd.bg_url;
                        s.bgGenerating = false;
                    }
                });

                if (d.complete) {
                    clearInterval(this._pollTimer);
                    this.isPolling = false;
                    if (d.failed > 0) {
                        window.toast(`${d.done} slides renderizados. ${d.failed} con error.`, 'error', 5000);
                    } else {
                        window.toast(`${d.done}/${d.total} slides renderizados ✓`, 'success', 4000);
                    }
                }
            } catch(e) { /* silently ignore */ }
        },

        // ── Image management ─────────────────────────────────────────────────
        async generateSlideImage(slideId) {
            const s = this.slides.find(x => x.id === slideId);
            if (s) s.bgGenerating = true;
            window.toast('Generando imagen con DALL-E 3… (~30s)', 'info', 5000);

            try {
                const r = await fetch(`${cfg.baseUrl}/slides/${slideId}/generate-image`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                });
                const d = await r.json();

                if (r.ok && d.ok) {
                    if (s && d.bg_url) {
                        s.bgUrl      = d.bg_url + '?t=' + Date.now();
                        s.bgPath     = d.bg_url;
                        s.bgGenerating = false;
                    }
                    window.toast('Imagen generada con DALL-E ✓', 'success', 4000);
                } else {
                    if (s) s.bgGenerating = false;
                    window.toast('Error: ' + (d.message || 'No se pudo generar la imagen.'), 'error', 6000);
                }
            } catch(e) {
                if (s) s.bgGenerating = false;
                window.toast('Error de red al generar imagen.', 'error', 4000);
            }
        },

        async generateAllImages() {
            const total = this.slides.length;
            if (total === 0) return;

            this.generatingImages = true;
            this.imgGenProgress   = 0;

            let done = 0, failed = 0;

            for (const slide of this.slides) {
                this.imgGenMessage = `Generando imagen ${done + 1} de ${total} (slide: ${slide.typeLabel})…`;
                slide.bgGenerating = true;

                try {
                    const r = await fetch(`${cfg.baseUrl}/slides/${slide.id}/generate-image`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                    });
                    const d = await r.json();
                    if (r.ok && d.ok && d.bg_url) {
                        slide.bgUrl        = d.bg_url + '?t=' + Date.now();
                        slide.bgPath       = d.bg_url;
                        slide.bgGenerating = false;
                        done++;
                    } else {
                        slide.bgGenerating = false;
                        failed++;
                    }
                } catch(e) {
                    slide.bgGenerating = false;
                    failed++;
                }

                this.imgGenProgress = Math.round(((done + failed) / total) * 100);
            }

            this.generatingImages = false;

            if (failed === 0) {
                window.toast(`${done} imágenes generadas con DALL-E ✓`, 'success', 5000);
            } else {
                window.toast(`${done} imágenes listas. ${failed} fallaron — puedes regenerarlas individualmente.`, 'error', 7000);
            }
        },

        async uploadBackground(slideId, event) {
            const file = event.target.files[0];
            if (!file) return;
            const s = this.slides.find(x => x.id === slideId);
            window.toast('Subiendo imagen…', 'info', 2000);
            const fd = new FormData();
            fd.append('background', file);
            fd.append('_method', 'POST');
            const r = await fetch(`${cfg.baseUrl}/slides/${slideId}/background`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                body: fd,
            });
            if (r.ok) {
                const d = await r.json();
                if (s && d.bg_url) { s.bgUrl = d.bg_url; s.bgPath = d.bg_url; }
                window.toast('Imagen de fondo actualizada ✓', 'success', 3000);
            } else {
                window.toast('Error al subir la imagen.', 'error', 4000);
            }
            // Reset file input
            event.target.value = '';
        },

        async removeBackground(slideId) {
            const s = this.slides.find(x => x.id === slideId);
            const r = await fetch(`${cfg.baseUrl}/slides/${slideId}/background`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
            });
            if (r.ok) {
                if (s) { s.bgUrl = null; s.bgPath = null; }
                window.toast('Imagen de fondo eliminada.', 'success', 3000);
            }
        },

        async clearRender(slideId) {
            const s = this.slides.find(x => x.id === slideId);
            if (!confirm('¿Eliminar el render de este slide? Podrás re-renderizarlo con otra plantilla.')) return;
            const r = await fetch(`${cfg.baseUrl}/slides/${slideId}/render`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
            });
            if (r.ok) {
                if (s) { s.imageUrl = null; s.renderStatus = 'pending'; }
                window.toast('Render eliminado. Ya puedes cambiar la plantilla y re-renderizar.', 'success', 4000);
            } else {
                window.toast('Error al eliminar el render.', 'error', 3000);
            }
        },

        // ── Caption ──────────────────────────────────────────────────────────
        onCaptionInput() {
            this.captionState = 'editing';
            clearTimeout(this._captionTimer);
            this._captionTimer = setTimeout(() => this.saveCaption(), 1000);
        },

        async saveCaption() {
            this.captionState = 'saving';
            try {
                const r = await fetch(`${cfg.baseUrl}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':  cfg.csrf,
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify({
                        caption_short: this.captionShort,
                        caption_long:  this.captionLong,
                    }),
                });
                if (!r.ok) throw new Error();
                this.captionState = 'saved';
                setTimeout(() => this.captionState = 'idle', 2500);
            } catch(e) {
                this.captionState = 'failed';
            }
        },

        async regenerateCaption() {
            this.captionState = 'saving';
            window.toast('Regenerando caption con Claude…', 'info', 3000);
            try {
                const r = await fetch(`${cfg.baseUrl}/regenerate-caption`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' },
                });
                if (!r.ok) throw new Error();
                const d = await r.json();
                this.captionShort = d.caption_short ?? this.captionShort;
                this.captionLong  = d.caption_long  ?? this.captionLong;
                this.hashtags     = d.hashtags       ?? this.hashtags;
                this.captionState = 'saved';
                setTimeout(() => this.captionState = 'idle', 2500);
                window.toast('Caption regenerado ✓', 'success', 3000);
            } catch(e) {
                this.captionState = 'failed';
                window.toast('Error al regenerar caption.', 'error', 4000);
            }
        },

        copyHashtags() {
            const text = this.hashtags.map(t => '#' + t).join(' ');
            navigator.clipboard.writeText(text)
                .then(() => window.toast('Hashtags copiados ✓', 'success', 2500));
        },

        // ── Lightbox ─────────────────────────────────────────────────────────
        openLightbox(slide) {
            const src = slide?.imageUrl || slide?.bgUrl;
            if (!src || slide?.bgGenerating) return;
            const label = 'Slide ' + (slide.order ?? '') + ' — ' + (slide.typeLabel ?? '');
            this.lb = { open: true, src, label };
        },

        // ── Generate flow ────────────────────────────────────────────────────
        openGenerate() {
            if (this.slides.length > 0) {
                if (!confirm('¿Re-generar el carrusel? Esto reemplazará las {{ $totalSlides }} diapositivas actuales.')) return;
            }
            window.location.href = '{{ route('admin.carousels.generate', $carousel) }}';
        },

        // ── Approve ──────────────────────────────────────────────────────────
        async approveCarousel(form) {
            if (!confirm('¿Aprobar este carrusel y enviar a n8n?')) return;
            form.submit();
        },
    };
}
</script>
@endsection
