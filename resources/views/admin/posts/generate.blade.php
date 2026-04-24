@extends('layouts.app-sidebar')
@section('title', 'Generar artículo con IA')

@section('content')
<style>
.step-bar { display:flex; align-items:center; gap:0; margin-bottom:2rem; }
.step      { display:flex; align-items:center; gap:.5rem; font-size:.82rem; font-weight:600; color:#94a3b8; }
.step.active{ color:var(--primary); }
.step-num  { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; background:#e2e8f0; color:#64748b; flex-shrink:0; }
.step.active .step-num { background:var(--primary); color:#fff; }
.step-line  { flex:1; height:2px; background:#e2e8f0; min-width:32px; }
</style>

<div class="step-bar">
    <div class="step active">
        <div class="step-num">1</div>
        <span>Contenido</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
        <div class="step-num">2</div>
        <span>Imágenes</span>
    </div>
    <div class="step-line"></div>
    <div class="step">
        <div class="step-num">3</div>
        <span>Editar y publicar</span>
    </div>
</div>
<style>
.gen-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
.gen-grid   { display:grid; grid-template-columns:1fr 380px; gap:1.5rem; align-items:start; }
.card       { background:#fff; border-radius:12px; border:1px solid var(--border); }
.card-header{ padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
.card-title { font-weight:700; font-size:.95rem; }
.card-body  { padding:1.25rem; }
.form-label { display:block; font-size:.8rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.03em; margin-bottom:.35rem; }
.form-input,.form-textarea { width:100%; padding:.55rem .75rem; border:1px solid var(--border); border-radius:8px; font-size:.88rem; background:#fff; }
.form-textarea { resize:vertical; min-height:80px; }
.form-hint  { font-size:.75rem; color:var(--text-muted); margin-top:.25rem; }
.btn        { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
.btn-primary{ background:var(--primary); color:#fff; }
.btn-outline{ background:#fff; color:var(--text); border:1px solid var(--border); }
.btn-success{ background:#10b981; color:#fff; }
.btn-sm     { padding:.35rem .75rem; font-size:.8rem; }
.badge      { display:inline-block; padding:.15rem .55rem; border-radius:999px; font-size:.72rem; font-weight:600; }
.badge-blue { background:#eff6ff; color:#1d4ed8; }
.badge-green{ background:#f0fdf4; color:#15803d; }
.badge-amber{ background:#fffbeb; color:#92400e; }
.badge-gray { background:#f1f5f9; color:#64748b; }
.tag-check-gen:has(input:checked) { background:var(--primary); color:#fff; border-color:var(--primary); }
.topic-card { border:1px solid var(--border); border-radius:10px; padding:1rem; cursor:pointer; transition:all .15s; margin-bottom:.75rem; }
.topic-card:hover { border-color:var(--primary); background:#f8faff; }
.topic-card.selected { border-color:var(--primary); background:#eff6ff; }
.topic-title{ font-weight:700; font-size:.92rem; margin-bottom:.25rem; }
.topic-desc { font-size:.8rem; color:var(--text-muted); line-height:1.5; }
.topic-meta { display:flex; align-items:center; gap:.5rem; margin-top:.5rem; flex-wrap:wrap; }
.score-bar  { height:4px; border-radius:2px; background:#e2e8f0; flex:1; max-width:80px; overflow:hidden; }
.score-fill { height:100%; border-radius:2px; background:var(--primary); }
.seo-info   { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:.75rem 1rem; font-size:.82rem; margin-bottom:1rem; }
.status-poll{ text-align:center; padding:2rem; }
.spinner    { display:inline-block; width:36px; height:36px; border:3px solid #e2e8f0; border-top-color:var(--primary); border-radius:50%; animation:spin .7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
.ai-tag     { background:linear-gradient(90deg,#6366f1,#8b5cf6); color:#fff; font-size:.7rem; font-weight:700; padding:.1rem .45rem; border-radius:999px; letter-spacing:.04em; }
</style>

<div class="gen-header">
    <div>
        <h2 style="margin:0;">&#9997; Generar artículo con IA</h2>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.2rem;">Perplexity encuentra temas, Claude redacta el artículo SEO completo</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">← Posts</a>
</div>

@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#dc2626;">{{ session('error') }}</div>
@endif
@if(session('info'))
<div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#1d4ed8;">{{ session('info') }}</div>
@endif

<div class="gen-grid">

    {{-- ══ LEFT COLUMN ══ --}}
    <div>

        {{-- PASO 1: Descubrir temas --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <span class="card-title">Paso 1 — Descubrir temas con Perplexity</span>
                <span class="ai-tag">AI</span>
            </div>
            <div class="card-body">
                <div class="seo-info">
                    Perplexity busca tendencias actuales del mercado inmobiliario en México y Claude sintetiza 8 ideas de blog con enfoque SEO. Opcional: escribe un tema semilla para enfocar la búsqueda.
                </div>
                <form method="POST" action="{{ route('admin.blog.discover') }}" id="discoverForm">
                    @csrf
                    <div style="display:flex;gap:.75rem;align-items:flex-end;">
                        <div style="flex:1;">
                            <label class="form-label">Tema semilla (opcional)</label>
                            <input type="text" name="topic" class="form-input" placeholder="Ej: compra de casa en Guadalajara, inversión inmobiliaria CDMX…">
                            <div class="form-hint">Deja vacío para que Perplexity detecte tendencias generales del mercado</div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="discoverBtn" style="white-space:nowrap;">
                            <span id="discoverLabel">&#128269; Descubrir temas</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- TOPICS RESULTS --}}
        @if($suggestions->isNotEmpty())
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <span class="card-title">Temas sugeridos ({{ $suggestions->count() }})</span>
                <span style="font-size:.78rem;color:var(--text-muted);">Selecciona uno para generar el artículo</span>
            </div>
            <div class="card-body">
                @foreach($suggestions as $sug)
                @php
                    $isConverted = $sug->status === 'converted';
                    $scoreColor  = $sug->relevance_score >= 80 ? '#10b981' : ($sug->relevance_score >= 60 ? '#f59e0b' : '#94a3b8');
                @endphp
                <div class="topic-card {{ $isConverted ? 'opacity-50' : '' }}" onclick="{{ $isConverted ? '' : 'selectTopic(this)' }}" data-title="{{ e($sug->title) }}" data-keywords="{{ e(implode(', ', $sug->suggested_keywords ?? [])) }}" data-id="{{ $sug->id }}">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;">
                        <div class="topic-title">{{ $sug->title }}</div>
                        @if($isConverted)
                        <span class="badge badge-green">Convertido</span>
                        @endif
                    </div>
                    <div class="topic-desc">{{ $sug->description }}</div>
                    @if($sug->reasoning)
                    <div style="font-size:.78rem;color:#059669;margin-top:.3rem;font-style:italic;">&#128204; {{ $sug->reasoning }}</div>
                    @endif
                    <div class="topic-meta">
                        <div class="score-bar"><div class="score-fill" style="width:{{ $sug->relevance_score }}%;background:{{ $scoreColor }};"></div></div>
                        <span style="font-size:.75rem;color:{{ $scoreColor }};font-weight:700;">{{ $sug->relevance_score }}%</span>
                        @foreach(array_slice($sug->suggested_keywords ?? [], 0, 3) as $kw)
                        <span class="badge badge-blue">{{ $kw }}</span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- PASO 2: Configurar y generar --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Paso 2 — Configurar y generar artículo</span>
                <span class="ai-tag">Claude 4</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.blog.generate-sync') }}" id="generateForm">
                    @csrf
                    <input type="hidden" name="suggestion_id" id="suggestionId">
                    <input type="hidden" name="market_data" id="marketData">

                    <div style="margin-bottom:1rem;">
                        <label class="form-label">Título / Tema del artículo <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="title" id="genTitle" class="form-input" placeholder="Ej: Cómo comprar una casa en Guadalajara en 2025: guía completa" required>
                        <div class="form-hint">Claude puede ajustar el título para SEO — este es tu punto de partida</div>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label class="form-label">Keywords objetivo (separadas por coma) <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="keywords" id="genKeywords" class="form-input" placeholder="Ej: comprar casa Guadalajara, bienes raíces jalisco, inmuebles guadalajara" required>
                        <div class="form-hint">Primera keyword = la principal (aparece en H1, meta title, primer párrafo)</div>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label class="form-label">Audiencia objetivo</label>
                        <input type="text" name="audience" class="form-input" placeholder="Ej: Propietarios que quieren vender en Benito Juárez por primera vez">
                        <div class="form-hint">Opcional — ayuda a Claude a calibrar el nivel de detalle y el tono</div>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label class="form-label">Puntos clave a cubrir</label>
                        <textarea name="key_points" class="form-textarea" rows="4" placeholder="Ej:&#10;- Cómo fijar el precio de salida correcto&#10;- Cuándo aceptar una contraoferta&#10;- Errores más caros al negociar&#10;- Señales de comprador serio vs. explorador"></textarea>
                        <div class="form-hint">Opcional — uno por línea. Claude los integra como secciones o puntos del artículo</div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div>
                            <label class="form-label">Tono</label>
                            <select name="tone" class="form-input">
                                <option value="">— Default del sistema —</option>
                                <option value="práctico y directo, con ejemplos reales">Práctico y directo</option>
                                <option value="profesional y confiable, sin tecnicismos innecesarios">Profesional y confiable</option>
                                <option value="técnico y detallado, para lectores con experiencia inmobiliaria">Técnico y detallado</option>
                                <option value="conversacional y cercano, como si lo explicara un amigo experto">Conversacional y cercano</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Longitud objetivo</label>
                            <select name="length" class="form-input">
                                <option value="">— Default (1200-1800 palabras) —</option>
                                <option value="artículo estándar (1200-1500 palabras)">Estándar (1200-1500 palabras)</option>
                                <option value="artículo largo (2000+ palabras), cobertura exhaustiva del tema">Largo (2000+ palabras)</option>
                                <option value="guía completa (3000+ palabras), la más completa sobre el tema en BJ">Guía completa (3000+ palabras)</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom:1.25rem;">
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.85rem;font-weight:600;">
                            <input type="checkbox" name="include_faq" value="1" style="width:16px;height:16px;accent-color:var(--primary);">
                            Incluir sección FAQ al final (4 preguntas) — activa FAQPage schema para Rich Results
                        </label>
                    </div>

                    <div style="background:#fafafa;border:1px solid var(--border);border-radius:8px;padding:.75rem;margin-bottom:1.25rem;">
                        <div style="font-size:.8rem;font-weight:600;margin-bottom:.5rem;">El artículo incluirá automáticamente:</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.25rem;font-size:.78rem;color:var(--text-muted);">
                            <div>&#10003; H1 + H2/H3 optimizados</div>
                            <div>&#10003; Meta title (60 chars)</div>
                            <div>&#10003; Meta description (155 chars)</div>
                            <div>&#10003; Slug SEO</div>
                            <div>&#10003; 3 CTAs con interlinking</div>
                            <div>&#10003; Links internos en texto</div>
                            <div>&#10003; Schema markup (Article/HowTo/FAQ)</div>
                            <div>&#10003; Score SEO estimado</div>
                            <div>&#10003; Prompts DALL-E (se usan en paso 2)</div>
                            <div>&#10003; ~1200-1800 palabras</div>
                        </div>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label class="form-label">Categoría <span style="color:#ef4444;">*</span></label>
                        <select name="category_id" class="form-input" required>
                            <option value="">— Selecciona una categoría —</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($tags->count())
                    <div style="margin-bottom:1rem;">
                        <label class="form-label">Etiquetas</label>
                        <div style="display:flex;flex-wrap:wrap;gap:.4rem;padding:.5rem 0;">
                            @foreach($tags as $tag)
                            <label style="display:flex;align-items:center;gap:.3rem;font-size:.82rem;cursor:pointer;padding:.2rem .55rem;border:1px solid var(--border);border-radius:20px;transition:all .15s;" class="tag-check-gen">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" style="display:none;">
                                {{ $tag->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;padding:.7rem;" id="generateBtn">
                        <span id="generateLabel">&#9889; Generar artículo completo</span>
                    </button>
                    <div class="form-hint" style="text-align:center;margin-top:.4rem;">Proceso sincrónico ~30-50 segundos. Después podrás generar y revisar las imágenes.</div>
                </form>
            </div>
        </div>

    </div>

    {{-- ══ RIGHT COLUMN ══ --}}
    <div>

        {{-- Posts generados recientemente --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Generados recientemente</span>
            </div>
            <div class="card-body" style="padding:.75rem;">
                @forelse($recentPosts as $rp)
                @php
                    $statusColor = match($rp->ai_generation_status) {
                        'done'       => '#10b981',
                        'failed'     => '#ef4444',
                        'generating','pending' => '#f59e0b',
                        default      => '#94a3b8',
                    };
                    $statusLabel = match($rp->ai_generation_status) {
                        'done'       => 'Listo',
                        'failed'     => 'Error',
                        'generating' => 'Generando…',
                        'pending'    => 'En cola',
                        default      => $rp->ai_generation_status ?? '—',
                    };
                @endphp
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;padding:.6rem 0;border-bottom:1px solid var(--border);">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $rp->title }}</div>
                        <div style="display:flex;gap:.4rem;margin-top:.2rem;align-items:center;">
                            <span style="font-size:.7rem;font-weight:700;color:{{ $statusColor }};">{{ $statusLabel }}</span>
                            @if($rp->seo_score)
                            <span style="font-size:.7rem;color:#64748b;">SEO: {{ $rp->seo_score }}/100</span>
                            @endif
                        </div>
                    </div>
                    @if($rp->ai_generation_status === 'done')
                    <a href="{{ route('admin.posts.edit', $rp) }}" class="btn btn-sm btn-outline" style="white-space:nowrap;">Editar</a>
                    @elseif($rp->ai_generation_status === 'generating' || $rp->ai_generation_status === 'pending')
                    <button class="btn btn-sm btn-outline poll-btn" data-post="{{ $rp->id }}" data-url="{{ route('admin.blog.status', $rp) }}" style="white-space:nowrap;">Ver estado</button>
                    @endif
                </div>
                @empty
                <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.83rem;">Aún no hay artículos generados con IA</div>
                @endforelse
            </div>
        </div>

        {{-- SEO Checklist --}}
        <div class="card" style="margin-top:1.25rem;">
            <div class="card-header"><span class="card-title">&#128270; Checklist SEO generado</span></div>
            <div class="card-body" style="font-size:.8rem;color:var(--text-muted);">
                <p style="margin-bottom:.75rem;">Claude genera cada artículo siguiendo estas buenas prácticas:</p>
                <ul style="padding-left:1.2rem;line-height:2;">
                    <li><strong>Keyword density</strong> ~1-2% (sin keyword stuffing)</li>
                    <li><strong>Jerarquía</strong> H1 → H2 → H3 correcta</li>
                    <li><strong>Meta title</strong> ≤ 60 chars + keyword + marca</li>
                    <li><strong>Meta description</strong> ≤ 155 chars + CTA implícito</li>
                    <li><strong>Slug</strong> corto, sin stopwords</li>
                    <li><strong>Interlinking</strong> a valuación, propiedades, contacto</li>
                    <li><strong>3 CTAs</strong> integrados en el cuerpo</li>
                    <li><strong>Schema</strong> Article / HowTo / FAQPage</li>
                    <li><strong>4 prompts DALL-E</strong> para imágenes originales</li>
                    <li><strong>Longitud</strong> mínimo 1200 palabras</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<script>
function selectTopic(card) {
    document.querySelectorAll('.topic-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');

    document.getElementById('genTitle').value     = card.dataset.title;
    document.getElementById('genKeywords').value  = card.dataset.keywords;
    document.getElementById('suggestionId').value = card.dataset.id;

    document.getElementById('generateForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

document.getElementById('discoverForm').addEventListener('submit', function() {
    const btn   = document.getElementById('discoverBtn');
    const label = document.getElementById('discoverLabel');
    btn.disabled = true;
    label.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:4px;"></span> Buscando con Perplexity…';
});

document.getElementById('generateForm').addEventListener('submit', function() {
    const btn   = document.getElementById('generateBtn');
    const label = document.getElementById('generateLabel');
    btn.disabled = true;
    label.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:4px;"></span> Generando con Claude… (~60-90 seg)';
});
</script>
@endsection
