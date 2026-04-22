@extends('layouts.app-sidebar')
@section('title', 'Agentes IA')

@section('content')
@php
    $providerModels = App\Models\AiAgentConfig::$providerModels;
    $providerBadge  = [
        'anthropic'  => ['color'=>'#f97316','bg'=>'#fff7ed','label'=>'Anthropic'],
        'perplexity' => ['color'=>'#2563eb','bg'=>'#eff6ff','label'=>'Perplexity'],
        'openai'     => ['color'=>'#16a34a','bg'=>'#f0fdf4','label'=>'OpenAI'],
    ];
    $providerIcon = ['anthropic'=>'🧠','perplexity'=>'🔍','openai'=>'⚡'];
    $imageAgents  = ['carousel.image_generation'];
@endphp

<style>
.ai-cost-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:0.75rem; }
.ai-cost-cell { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:0.85rem 1rem; }
.ai-cost-cell .model-name { font-size:0.82rem; font-weight:600; color:var(--text); margin-bottom:0.3rem; }
.ai-cost-cell .model-price { font-size:0.95rem; font-weight:700; color:var(--primary); }
.ai-cost-cell .model-note  { font-size:0.72rem; color:var(--text-muted); margin-top:0.2rem; }

.agent-card { background:var(--card); border:1px solid var(--border); border-radius:10px; margin-bottom:0.75rem; overflow:hidden; }
.agent-header { display:flex; align-items:center; gap:0.75rem; padding:0.9rem 1.25rem; cursor:default; }
.agent-icon { font-size:1.2rem; flex-shrink:0; }
.agent-info { flex:1; min-width:0; }
.agent-title { font-size:0.9rem; font-weight:600; color:var(--text); display:flex; align-items:center; gap:0.5rem; }
.agent-desc { font-size:0.78rem; color:var(--text-muted); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.agent-badges { display:flex; align-items:center; gap:0.4rem; flex-shrink:0; flex-wrap:wrap; justify-content:flex-end; }
.badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; border:1px solid; white-space:nowrap; }
.badge-inactive { background:#f8fafc; color:var(--text-muted); border-color:var(--border); }
.agent-edit-body { border-top:1px solid var(--border); background:#fafbfc; padding:1.1rem 1.25rem; }
.agent-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:0.85rem; }
.agent-form-note  { font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem; }
.agent-form-footer{ display:flex; align-items:center; justify-content:space-between; margin-top:1rem; padding-top:0.85rem; border-top:1px solid var(--border); }
.toggle-label { display:flex; align-items:center; gap:0.5rem; font-size:0.83rem; color:var(--text-muted); cursor:pointer; }
.key-legend { background:var(--bg); border:1px solid var(--border); border-radius:var(--radius); padding:1rem 1.25rem; }
.key-legend-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:0.4rem; margin-top:0.6rem; }
.key-entry { display:flex; align-items:center; gap:0.5rem; font-size:0.78rem; }
code.key { background:var(--card); border:1px solid var(--border); border-radius:4px; padding:1px 7px; font-family:monospace; font-size:0.78rem; color:var(--text); white-space:nowrap; }
</style>

<div class="page-header">
    <div>
        <h2>Agentes de Inteligencia Artificial</h2>
        <p class="text-muted">Configura el modelo y proveedor de cada función. Los cambios aplican inmediatamente.</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1.25rem;">
    ✓ {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

{{-- Cost reference --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h3>💡 Referencia de costos aproximados</h3>
        <span style="font-size:0.75rem;color:var(--text-muted);">USD por millón de tokens (entrada / salida)</span>
    </div>
    <div class="card-body" style="padding-bottom:1.1rem;">
        <div class="ai-cost-grid">
            <div class="ai-cost-cell">
                <div class="model-name">Claude Opus 4.6</div>
                <div class="model-price">$15 / $75</div>
                <div class="model-note">Máxima calidad</div>
            </div>
            <div class="ai-cost-cell">
                <div class="model-name">Claude Sonnet 4.6</div>
                <div class="model-price">$3 / $15</div>
                <div class="model-note">Equilibrado ✦ recomendado</div>
            </div>
            <div class="ai-cost-cell">
                <div class="model-name">Claude Haiku 4.5</div>
                <div class="model-price" style="color:#16a34a;">$0.80 / $4</div>
                <div class="model-note">Más económico · bueno para análisis</div>
            </div>
            <div class="ai-cost-cell">
                <div class="model-name">Perplexity Sonar</div>
                <div class="model-price" style="color:#2563eb;">$1 / $1</div>
                <div class="model-note">+ $5 / 1,000 búsquedas web</div>
            </div>
            <div class="ai-cost-cell">
                <div class="model-name">Perplexity Sonar Pro</div>
                <div class="model-price" style="color:#2563eb;">$3 / $15</div>
                <div class="model-note">Búsquedas más precisas</div>
            </div>
            <div class="ai-cost-cell">
                <div class="model-name">DALL-E 3</div>
                <div class="model-price" style="color:#16a34a;">$0.04 / img</div>
                <div class="model-note">Standard 1024px · $0.08 HD</div>
            </div>
            <div class="ai-cost-cell">
                <div class="model-name">GPT-4o</div>
                <div class="model-price">$2.50 / $10</div>
                <div class="model-note">Alternativa a Claude</div>
            </div>
            <div class="ai-cost-cell">
                <div class="model-name">GPT-4o Mini</div>
                <div class="model-price" style="color:#16a34a;">$0.15 / $0.60</div>
                <div class="model-note">Económico OpenAI</div>
            </div>
        </div>
    </div>
</div>

{{-- Agent cards --}}
{{-- All provider models as JSON — used by Alpine inside each card --}}
@php $allModelsJson = json_encode($providerModels, JSON_HEX_TAG | JSON_HEX_APOS) @endphp

<div style="margin-bottom:1.5rem;">
    @foreach($agents as $agent)
    @php
        $badge   = $providerBadge[$agent->provider] ?? ['color'=>'#64748b','bg'=>'#f8fafc','label'=>$agent->provider];
        $icon    = $providerIcon[$agent->provider] ?? '🤖';
        $isImage = in_array($agent->key, $imageAgents);
    @endphp
    <div class="agent-card"
         x-data="{
             open: false,
             provider: '{{ $agent->provider }}',
             model:    '{{ $agent->model }}',
             allModels: {{ $allModelsJson }},
             get models() { return this.allModels[this.provider] ?? {}; },
             get modelIds() { return Object.keys(this.models); },
             onProviderChange() {
                 const ids = Object.keys(this.allModels[this.provider] ?? {});
                 this.model = ids.length ? ids[0] : '';
             }
         }">

        {{-- Header row --}}
        <div class="agent-header">
            <span class="agent-icon">{{ $icon }}</span>
            <div class="agent-info">
                <div class="agent-title">
                    {{ $agent->label }}
                    @if(!$agent->is_active)
                        <span class="badge badge-inactive">Inactivo</span>
                    @endif
                </div>
                <div class="agent-desc">{{ $agent->description }}</div>
            </div>
            <div class="agent-badges">
                {{-- Provider badge — updates reactively --}}
                <template x-for="[prov, cfg] in Object.entries({ anthropic: { label:'Anthropic', color:'#f97316', bg:'#fff7ed' }, perplexity: { label:'Perplexity', color:'#2563eb', bg:'#eff6ff' }, openai: { label:'OpenAI', color:'#16a34a', bg:'#f0fdf4' } })" :key="prov">
                    <span x-show="provider === prov" class="badge"
                          :style="`color:${cfg.color};background:${cfg.bg};border-color:${cfg.color}40`"
                          x-text="cfg.label"></span>
                </template>
                {{-- Model badge --}}
                <span class="badge" style="color:var(--text-muted);background:var(--bg);border-color:var(--border);"
                      x-text="model"></span>
                @if(!$isImage)
                <span class="badge" style="color:var(--text-muted);background:var(--bg);border-color:var(--border);">
                    {{ number_format($agent->max_tokens) }} tk
                </span>
                @endif
                <button @click="open = !open" class="btn btn-outline btn-sm" style="margin-left:0.25rem;">
                    <span x-text="open ? 'Cerrar' : 'Editar'"></span>
                </button>
            </div>
        </div>

        {{-- Edit form --}}
        <div class="agent-edit-body" x-show="open" x-transition>
            <form method="POST" action="{{ route('admin.ai-config.update', $agent) }}">
                @csrf
                @method('PATCH')

                <div class="agent-form-grid">
                    {{-- Provider --}}
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Proveedor</label>
                        <select name="provider" x-model="provider" @change="onProviderChange()" class="form-select">
                            <option value="anthropic">🧠 Anthropic (Claude)</option>
                            <option value="perplexity">🔍 Perplexity</option>
                            <option value="openai">⚡ OpenAI</option>
                        </select>
                    </div>

                    {{-- Model — reactive select that changes with provider --}}
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Modelo</label>
                        <select name="model" x-model="model" class="form-select">
                            <template x-for="[id, label] in Object.entries(models)" :key="id">
                                <option :value="id" x-text="label" :selected="id === model"></option>
                            </template>
                        </select>
                        <div class="agent-form-note">Los modelos se actualizan según el proveedor seleccionado</div>
                    </div>

                    @if(!$isImage)
                    {{-- Max tokens --}}
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Máx. tokens</label>
                        <input type="number" name="max_tokens" value="{{ $agent->max_tokens }}"
                               min="64" max="32000" step="64" class="form-input">
                        <div class="agent-form-note">Más tokens = más costo por llamada</div>
                    </div>

                    {{-- Temperature --}}
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">
                            Temperatura
                            <span style="font-weight:400;color:var(--text-muted);">(0 exacto · 1 creativo)</span>
                        </label>
                        <input type="number" name="temperature" value="{{ $agent->temperature }}"
                               min="0" max="2" step="0.05" class="form-input">
                    </div>
                    @else
                    <input type="hidden" name="max_tokens"  value="{{ $agent->max_tokens }}">
                    <input type="hidden" name="temperature" value="{{ $agent->temperature }}">
                    <div class="form-group" style="margin-bottom:0; grid-column:span 2;">
                        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:0.75rem 1rem;font-size:0.82rem;color:#92400e;">
                            ⚠️ Para generación de imágenes, solo aplican <strong>Proveedor</strong> y <strong>Modelo</strong>.
                        </div>
                    </div>
                    @endif
                </div>

                <div class="agent-form-footer">
                    <label class="toggle-label">
                        <input type="checkbox" name="is_active" value="1" {{ $agent->is_active ? 'checked':'' }}
                               style="width:16px;height:16px;accent-color:var(--primary);">
                        Agente activo
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- Key legend --}}
<div class="key-legend">
    <div style="font-size:0.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;">
        Claves usadas en el código
    </div>
    <div class="key-legend-grid">
        @foreach($agents as $agent)
        <div class="key-entry">
            <code class="key">{{ $agent->key }}</code>
            <span style="color:var(--text-muted);">→ {{ $agent->label }}</span>
        </div>
        @endforeach
    </div>
</div>

@endsection
