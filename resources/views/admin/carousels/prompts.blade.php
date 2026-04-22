@extends('layouts.app-sidebar')
@section('title', 'Prompts de Imágenes IA')

@section('styles')
.prompt-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
@media(max-width:900px) { .prompt-grid { grid-template-columns:1fr; } }

.prompt-card {
    border:1px solid var(--border); border-radius:10px;
    background:#fff; overflow:hidden; transition:box-shadow .15s;
}
.prompt-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }
.prompt-card-header {
    padding:.75rem 1rem; border-bottom:1px solid #f0f2f5;
    display:flex; align-items:center; gap:.6rem;
}
.prompt-card-body { padding:.85rem 1rem; }
.type-badge {
    font-size:.65rem; font-weight:700; letter-spacing:.8px; text-transform:uppercase;
    padding:.2rem .55rem; border-radius:20px;
    background:#eff6ff; color:#1d4ed8;
}
.prompt-ta {
    width:100%; border:1px solid #e5e7eb; border-radius:7px;
    padding:.6rem .8rem; font-size:.82rem; font-family:inherit;
    line-height:1.55; resize:vertical; min-height:100px;
    transition:border-color .15s; color:#374151;
}
.prompt-ta:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

.global-card {
    background:linear-gradient(135deg,#1e1b4b 0%,#312e81 100%);
    border-radius:12px; padding:1.25rem 1.5rem; margin-bottom:1.5rem;
    border:none;
}
.global-ta {
    width:100%; border:1.5px solid rgba(255,255,255,.2); border-radius:8px;
    padding:.75rem 1rem; font-size:.84rem; font-family:inherit;
    line-height:1.6; resize:vertical; min-height:90px;
    background:rgba(255,255,255,.08); color:#e0e7ff;
    transition:border-color .15s;
}
.global-ta:focus { outline:none; border-color:rgba(129,140,248,.8); background:rgba(255,255,255,.12); }
.global-ta::placeholder { color:rgba(199,210,254,.4); }

.preview-box {
    background:#f8fafc; border:1px dashed #d1d5db; border-radius:7px;
    padding:.65rem .9rem; font-size:.75rem; line-height:1.65;
    color:#374151; white-space:pre-wrap; word-break:break-word;
    min-height:48px; font-family:'JetBrains Mono','Fira Code',monospace;
}
.preview-label {
    font-size:.65rem; font-weight:600; color:#9ca3af;
    text-transform:uppercase; letter-spacing:.6px; margin-bottom:.35rem;
}
.char-count { font-size:.67rem; color:#9ca3af; margin-top:.3rem; text-align:right; }
@endsection

@section('content')
<div class="page-header" style="padding-bottom:.75rem;">
    <div>
        <h2>Prompts de Imágenes IA</h2>
        <p style="font-size:.83rem;color:#6b7280;margin-top:4px;">
            Define qué le pides a DALL-E para cada tipo de slide. Las reglas globales se añaden siempre a todos los prompts.
        </p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.carousels.image-test') }}" class="btn btn-outline btn-sm">⚗ Test imágenes</a>
        <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline btn-sm">← Carruseles</a>
    </div>
</div>

<div x-data="promptEditor()" x-init="init()">

<form method="POST" action="{{ route('admin.carousels.prompts.update') }}" @submit.prevent="save($el)">
    @csrf

    {{-- ══ REGLAS GLOBALES ══ --}}
    <div class="global-card">
        <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:.85rem;gap:1rem;">
            <div>
                <div style="font-size:.95rem;font-weight:700;color:#fff;margin-bottom:.2rem;">
                    🔒 Reglas globales — siempre se aplican
                </div>
                <div style="font-size:.78rem;color:rgba(199,210,254,.65);">
                    Se añaden al final de <em>todos</em> los prompts. Aquí van: estilo fotográfico, calidad, restricciones.
                </div>
            </div>
            <span style="flex-shrink:0;font-size:.68rem;color:rgba(199,210,254,.5);background:rgba(255,255,255,.08);padding:.2rem .6rem;border-radius:12px;margin-top:2px;">
                Global
            </span>
        </div>

        <textarea class="global-ta" name="global" x-model="global" rows="3"
                  placeholder="Hyperrealistic, photorealistic, 8K ultra-HD…"
                  @input="updateGlobalPreview()"></textarea>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.4rem;">
            <span style="font-size:.67rem;color:rgba(199,210,254,.4);" x-text="global.length + ' chars'"></span>
            <span style="font-size:.67rem;color:rgba(199,210,254,.4);">Se añade como sufijo a cada prompt</span>
        </div>
    </div>

    {{-- ══ PROMPTS POR TIPO ══ --}}
    <div style="margin-bottom:1rem;">
        <h3 style="font-size:.88rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Prompts por tipo de slide</h3>
        <p style="font-size:.78rem;color:#6b7280;">
            Cada slide usa su tipo para seleccionar el prompt base. El titular del slide se añade como contexto al inicio.
        </p>
    </div>

    <div class="prompt-grid">
        <template x-for="(p, idx) in prompts" :key="p.key">
            <div class="prompt-card">
                <div class="prompt-card-header">
                    <span class="type-badge" x-text="p.key"></span>
                    <span style="font-size:.85rem;font-weight:600;color:#1e293b;" x-text="p.label"></span>
                    <input type="hidden" :name="'prompts['+idx+'][key]'" :value="p.key">
                </div>
                <div class="prompt-card-body" style="display:flex;flex-direction:column;gap:.6rem;">
                    <textarea class="prompt-ta"
                              :name="'prompts['+idx+'][prompt]'"
                              x-model="p.prompt"
                              rows="4"
                              @input="updatePreview(p)"></textarea>
                    <div class="char-count" x-text="p.prompt.length + ' chars'"></div>

                    {{-- Preview del prompt completo --}}
                    <div>
                        <div class="preview-label">Vista previa del prompt completo</div>
                        <div class="preview-box" x-text="assemblePrompt(p)"></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ══ ACTIONS ══ --}}
    <div style="position:sticky;bottom:0;background:#fff;border-top:1px solid #e5e7eb;padding:.85rem 0;margin-top:1.5rem;display:flex;justify-content:space-between;align-items:center;">
        <form method="POST" action="{{ route('admin.carousels.prompts.reset') }}" style="display:inline;"
              onsubmit="return confirm('¿Restaurar todos los prompts a los valores predeterminados?')">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm" style="color:#dc2626;border-color:#fecaca;">
                ↺ Restaurar predeterminados
            </button>
        </form>

        <div style="display:flex;gap:.75rem;align-items:center;">
            <span x-show="saved" style="font-size:.8rem;color:#10b981;font-weight:500;">✓ Guardado</span>
            <button type="submit" class="btn btn-primary" :disabled="saving">
                <span x-show="!saving">Guardar cambios</span>
                <span x-show="saving"><span class="spin">⟳</span> Guardando…</span>
            </button>
        </div>
    </div>

</form>

</div>
@endsection

@section('scripts')
<script>
const INITIAL_GLOBAL  = @json($global?->prompt ?? '');
const INITIAL_PROMPTS = @json($byType->map(fn($p) => [
    'key'    => $p->key,
    'label'  => $p->label,
    'prompt' => $p->prompt,
]));
const CSRF = '{{ csrf_token() }}';
const SAVE_URL = '{{ route('admin.carousels.prompts.update') }}';

function promptEditor() {
    return {
        global:  INITIAL_GLOBAL,
        prompts: INITIAL_PROMPTS.map(p => ({ ...p })),
        saving:  false,
        saved:   false,

        init() {
            // nothing async needed
        },

        assemblePrompt(p) {
            const context = '(Titular del slide aquí). ';
            const body    = p.prompt?.trim() || '…';
            const suffix  = this.global?.trim() || '';
            return context + body + (suffix ? '. ' + suffix : '');
        },

        updateGlobalPreview() {
            // reactivity handles it via x-text="assemblePrompt(p)"
        },

        updatePreview(p) {
            // same — reactive
        },

        async save(form) {
            this.saving = true;
            this.saved  = false;

            const body = {
                _token:  CSRF,
                global:  this.global,
                prompts: this.prompts.map(p => ({ key: p.key, prompt: p.prompt })),
            };

            try {
                const r = await fetch(SAVE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept':       'application/json',
                    },
                    body: JSON.stringify(body),
                });

                if (r.ok) {
                    this.saved = true;
                    setTimeout(() => this.saved = false, 3000);
                    window.toast('Prompts guardados correctamente.', 'success', 3000);
                } else {
                    const d = await r.json().catch(() => ({}));
                    window.toast('Error al guardar: ' + (d.message || r.status), 'error', 5000);
                }
            } catch(e) {
                window.toast('Error de red.', 'error', 4000);
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endsection
