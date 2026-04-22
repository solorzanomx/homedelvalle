@extends('layouts.app-sidebar')
@section('title', 'Test Generación de Imágenes')

@section('styles')
.test-grid { display:grid; grid-template-columns:380px 1fr; gap:1.5rem; align-items:start; }
.provider-card {
    border:2px solid var(--border); border-radius:10px; padding:1rem 1.25rem;
    cursor:pointer; transition:all .15s; background:#fff;
}
.provider-card:hover { border-color:#c7d2fe; }
.provider-card.selected { border-color:#2563eb; background:#eff6ff; }
.provider-card.disabled { opacity:.5; cursor:not-allowed; }
.provider-status { display:inline-flex; align-items:center; gap:.35rem; font-size:.72rem; font-weight:600; padding:.15rem .5rem; border-radius:20px; }
.provider-status.ok  { background:#d1fae5; color:#065f46; }
.provider-status.err { background:#fee2e2; color:#991b1b; }
.result-box {
    border:2px dashed #e5e7eb; border-radius:12px; min-height:320px;
    display:flex; align-items:center; justify-content:center;
    background:#f8fafc; transition:all .3s; overflow:hidden; position:relative;
}
.result-box.loading { border-color:#c7d2fe; background:#eff6ff; }
.result-box.success { border-color:#a7f3d0; background:#f0fdf4; }
.result-box.error   { border-color:#fecaca; background:#fef2f2; }
.result-img { width:100%; border-radius:10px; display:block; }
.spinner-lg {
    width:52px; height:52px; border:4px solid #e0e7ff;
    border-top-color:#4f46e5; border-radius:50%;
    animation:spin-lg .9s linear infinite;
}
@keyframes spin-lg { to { transform:rotate(360deg); } }
.meta-table { width:100%; border-collapse:collapse; font-size:.78rem; margin-top:.75rem; }
.meta-table td { padding:.3rem .5rem; border-bottom:1px solid #f0f2f5; }
.meta-table td:first-child { color:#6b7280; font-weight:500; width:140px; }
.prompt-area { width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:.65rem .85rem; font-size:.88rem; font-family:inherit; resize:vertical; min-height:100px; line-height:1.5; transition:border-color .15s; }
.prompt-area:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.log-block { background:#0f172a; color:#e2e8f0; border-radius:8px; padding:1rem 1.25rem; font-size:.75rem; font-family:'JetBrains Mono','Fira Code',monospace; line-height:1.7; max-height:300px; overflow-y:auto; white-space:pre-wrap; word-break:break-all; }
.size-btn { padding:.3rem .65rem; border:1.5px solid #e5e7eb; border-radius:6px; font-size:.75rem; cursor:pointer; background:#fff; transition:all .15s; font-family:inherit; }
.size-btn.active { border-color:#2563eb; background:#eff6ff; color:#1d4ed8; font-weight:600; }
.elapsed-badge { display:inline-flex; align-items:center; gap:.25rem; font-size:.72rem; color:#6b7280; background:#f1f5f9; padding:.2rem .55rem; border-radius:12px; }
@endsection

@section('content')
<div class="page-header" style="padding-bottom:.75rem;">
    <div>
        <h2>Test Generación de Imágenes</h2>
        <p style="font-size:.83rem;color:#6b7280;margin-top:4px;">
            Prueba proveedores de imagen en tiempo real — sin cola, respuesta inmediata.
        </p>
    </div>
    <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline btn-sm">← Carruseles</a>
</div>

<div x-data="imageTest()" class="test-grid">

    {{-- ── LEFT: Config panel ── --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Providers --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Proveedor</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem;">
                @foreach($providers as $p)
                <div class="provider-card {{ !$p['active'] ? 'disabled' : '' }}"
                     :class="{ 'selected': provider === '{{ $p['id'] }}' }"
                     @click="{{ $p['active'] ? "selectProvider('" . $p['id'] . "')" : '' }}">
                    <div style="display:flex;justify-content:space-between;align-items:start;">
                        <div>
                            <div style="font-weight:600;font-size:.9rem;">{{ $p['name'] }}</div>
                            <div style="font-size:.75rem;color:#6b7280;">{{ $p['company'] }}</div>
                        </div>
                        <span class="provider-status {{ $p['active'] ? 'ok' : 'err' }}">
                            {{ $p['active'] ? '✓ Configurada' : '✗ Sin API key' }}
                        </span>
                    </div>
                    <div style="font-size:.73rem;color:#9ca3af;margin-top:.4rem;">{{ $p['note'] }}</div>
                    <div style="font-size:.7rem;color:#c4b5a0;margin-top:.2rem;font-family:monospace;">{{ $p['key_hint'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Size selector --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Tamaño</h3></div>
            <div class="card-body">
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;" id="size-buttons">
                    {{-- rendered by Alpine --}}
                </div>
                <p style="font-size:.72rem;color:#9ca3af;margin-top:.6rem;">
                    Para Instagram 4:5 usamos <strong>1024×1792</strong> (portrait), el canvas se recorta a 1080×1350 al renderizar.
                </p>
            </div>
        </div>

        {{-- Prompt --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Prompt</h3>
                <button type="button" class="btn btn-outline btn-sm" @click="loadDefaultPrompt()">
                    Prompt predeterminado
                </button>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem;">
                <textarea class="prompt-area" x-model="prompt"
                          placeholder="Describe la imagen que quieres generar…"
                          rows="5"></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.72rem;color:#9ca3af;" x-text="prompt.length + '/4000 chars'"></span>
                    <button type="button" class="btn btn-primary"
                            @click="runTest()"
                            :disabled="loading || !prompt.trim() || !provider">
                        <span x-show="!loading">▶ Generar imagen</span>
                        <span x-show="loading"><span style="animation:spin-lg .9s linear infinite;display:inline-block;">⟳</span> Generando…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: Result panel ── --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

        {{-- Result image --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Resultado</h3>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <span class="elapsed-badge" x-show="elapsed !== null">
                        ⏱ <span x-text="elapsed + 's'"></span>
                    </span>
                    <a x-show="resultUrl" :href="resultUrl" target="_blank"
                       class="btn btn-outline btn-sm">↗ Abrir original</a>
                </div>
            </div>
            <div class="card-body" style="padding:.75rem;">
                <div class="result-box"
                     :class="{ 'loading': loading, 'success': resultUrl && !loading, 'error': errorMsg && !loading }">

                    {{-- Empty state --}}
                    <div x-show="!loading && !resultUrl && !errorMsg"
                         style="text-align:center;color:#9ca3af;padding:2rem;">
                        <div style="font-size:2.5rem;margin-bottom:.75rem;">🖼️</div>
                        <div style="font-size:.85rem;">Selecciona un proveedor, escribe el prompt<br>y presiona Generar imagen.</div>
                    </div>

                    {{-- Loading --}}
                    <div x-show="loading" style="text-align:center;padding:2rem;">
                        <div class="spinner-lg" style="margin:0 auto 1rem;"></div>
                        <div style="font-size:.85rem;color:#4f46e5;font-weight:500;" x-text="loadingMsg"></div>
                        <div style="font-size:.75rem;color:#9ca3af;margin-top:.5rem;">Puede tardar 15–40 segundos</div>
                    </div>

                    {{-- Error --}}
                    <div x-show="errorMsg && !loading && !resultUrl"
                         style="padding:1.5rem;width:100%;text-align:center;">
                        <div style="font-size:2rem;margin-bottom:.5rem;">⚠️</div>
                        <div style="font-size:.85rem;color:#dc2626;font-weight:500;margin-bottom:.5rem;">Error al generar</div>
                        <div style="font-size:.78rem;color:#6b7280;word-break:break-all;" x-text="errorMsg"></div>
                    </div>

                    {{-- Result image --}}
                    <img x-show="resultUrl && !loading"
                         :src="resultUrl" alt="Resultado"
                         class="result-img"
                         style="animation:fadeIn .4s ease;">
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="card" x-show="meta && Object.keys(meta).length">
            <div class="card-header"><h3 class="card-title">Metadatos de la respuesta</h3></div>
            <div class="card-body" style="padding:.75rem 1rem;">
                <table class="meta-table">
                    <template x-for="[k,v] in Object.entries(meta)" :key="k">
                        <tr>
                            <td x-text="k"></td>
                            <td style="color:#374151;word-break:break-all;" x-text="v ?? '—'"></td>
                        </tr>
                    </template>
                </table>
            </div>
        </div>

        {{-- Log --}}
        <div class="card" x-show="log.length">
            <div class="card-header">
                <h3 class="card-title">Log de esta sesión</h3>
                <button type="button" class="btn btn-outline btn-sm" @click="log=[]">Limpiar</button>
            </div>
            <div class="card-body" style="padding:.75rem;">
                <div class="log-block" x-ref="logBlock"><span x-html="log.join('\n')"></span></div>
            </div>
        </div>

        {{-- Revised prompt (DALL-E 3 rewrites prompts) --}}
        <div class="card" x-show="meta?.revised_prompt">
            <div class="card-header"><h3 class="card-title">Prompt revisado por DALL-E</h3></div>
            <div class="card-body">
                <p style="font-size:.83rem;color:#374151;line-height:1.6;" x-text="meta?.revised_prompt"></p>
                <p style="font-size:.72rem;color:#9ca3af;margin-top:.5rem;">
                    DALL-E 3 modifica el prompt automáticamente para mejorar resultados. Usa este prompt revisado para futuras iteraciones.
                </p>
            </div>
        </div>

    </div>

</div>
@endsection

@section('scripts')
<script>
const PROVIDERS = @json($providers);

function imageTest() {
    return {
        provider:   null,
        size:       '1024x1792',
        prompt:     '',
        loading:    false,
        loadingMsg: 'Llamando a la API…',
        resultUrl:  null,
        errorMsg:   null,
        elapsed:    null,
        meta:       {},
        log:        [],
        _msgTimer:  null,

        get currentProvider() {
            return PROVIDERS.find(p => p.id === this.provider) || null;
        },

        get availableSizes() {
            return this.currentProvider?.sizes || [];
        },

        init() {
            // Auto-select first active provider
            const first = PROVIDERS.find(p => p.active);
            if (first) this.selectProvider(first.id);

            this.$watch('provider', () => this.renderSizeButtons());
            this.$nextTick(() => this.renderSizeButtons());
        },

        selectProvider(id) {
            this.provider = id;
            const p = PROVIDERS.find(x => x.id === id);
            if (p) this.size = p.default_size;
        },

        renderSizeButtons() {
            const container = document.getElementById('size-buttons');
            if (!container) return;
            container.innerHTML = '';
            (this.currentProvider?.sizes || []).forEach(s => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'size-btn' + (s === this.size ? ' active' : '');
                btn.textContent = s;
                btn.onclick = () => {
                    this.size = s;
                    this.renderSizeButtons();
                };
                container.appendChild(btn);
            });
        },

        loadDefaultPrompt() {
            this.prompt = 'Luxury real estate exterior, modern architecture, upscale Mexico City neighborhood, dramatic golden hour lighting, cinematic photography, 4K, no text, no people, portrait orientation.';
        },

        addLog(msg, type = 'info') {
            const colors = { info: '#7dd3fc', ok: '#6ee7b7', err: '#fca5a5', warn: '#fcd34d' };
            const color  = colors[type] || '#e2e8f0';
            const ts     = new Date().toLocaleTimeString('es-MX');
            this.log.push(`<span style="color:#64748b">[${ts}]</span> <span style="color:${color}">${msg}</span>`);
            this.$nextTick(() => {
                if (this.$refs.logBlock) this.$refs.logBlock.scrollTop = this.$refs.logBlock.scrollHeight;
            });
        },

        async runTest() {
            if (!this.provider || !this.prompt.trim()) return;

            this.loading   = true;
            this.resultUrl = null;
            this.errorMsg  = null;
            this.elapsed   = null;
            this.meta      = {};
            this.loadingMsg = 'Conectando con la API…';

            this.addLog(`Iniciando test: proveedor=${this.provider}, size=${this.size}`);
            this.addLog(`Prompt: ${this.prompt.slice(0, 120)}${this.prompt.length > 120 ? '…' : ''}`);

            // Rotate loading messages
            const msgs = [
                'Conectando con la API…',
                'Generando imagen…',
                'Procesando respuesta…',
                'Descargando imagen…',
                'Casi listo…',
            ];
            let mi = 0;
            this._msgTimer = setInterval(() => {
                mi = (mi + 1) % msgs.length;
                this.loadingMsg = msgs[mi];
            }, 5000);

            try {
                const r = await fetch('{{ route('admin.carousels.image-test.run') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'X-CSRF-TOKEN':  '{{ csrf_token() }}',
                        'Accept':        'application/json',
                    },
                    body: JSON.stringify({
                        provider: this.provider,
                        prompt:   this.prompt,
                        size:     this.size,
                    }),
                });

                const d = await r.json();

                if (d.ok) {
                    this.resultUrl = d.imageUrl;
                    this.meta      = d.meta || {};
                    this.elapsed   = d.elapsed;
                    this.addLog(`✓ Imagen generada en ${d.elapsed}s`, 'ok');
                    if (d.meta?.revised_prompt) {
                        this.addLog('DALL-E revisó el prompt automáticamente.', 'warn');
                    }
                } else {
                    this.errorMsg = d.error;
                    this.elapsed  = d.elapsed;
                    this.addLog(`✗ Error: ${d.error}`, 'err');
                }
            } catch (e) {
                this.errorMsg = e.message || 'Error de red';
                this.addLog(`✗ Error de red: ${e.message}`, 'err');
            } finally {
                clearInterval(this._msgTimer);
                this.loading = false;
            }
        },
    };
}
</script>
@endsection
