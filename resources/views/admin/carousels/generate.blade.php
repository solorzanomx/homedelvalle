@extends('layouts.app-sidebar')
@section('title', 'Generar con IA')

@section('content')

@php
    $recommended = [
        'commercial'  => 'hdv-foto-limpia',
        'educational' => 'hdv-claro',
        'capture'     => 'hdv-degradado',
        'informative' => 'hdv-marino',
        'branding'    => 'hdv-editorial',
    ];
    $recommendedSlug   = $recommended[$carousel->type] ?? 'premium-dark';
    $currentTemplateId = $carousel->template_id;
    $preselect         = $currentTemplateId
        ?? ($templates->firstWhere('slug', $recommendedSlug)?->id)
        ?? $templates->first()?->id;

    $templateMeta = [
        'premium-dark'    => ['icon' => '🌑', 'palette' => ['#0C1A2E','#3B82F6','#0C1A2E'], 'hint' => 'Oscuro · texto centro-izq.'],
        'hdv-claro'       => ['icon' => '☀️',  'palette' => ['#F4F7FB','#3B82C4','#1E3A5F'], 'hint' => 'Claro · overlay blanco'],
        'hdv-degradado'   => ['icon' => '🌊',  'palette' => ['#1E3A5F','#2457A0','#3B82C4'], 'hint' => 'Degradado · overlay azul'],
        'hdv-marino'      => ['icon' => '⚓',  'palette' => ['#0A1628','#3B82C4','#60A5FA'], 'hint' => 'Marino · tipografía bold'],
        'hdv-editorial'   => ['icon' => '📰',  'palette' => ['#FFFFFF','#0A0A0A','#3B82C4'], 'hint' => 'Editorial · serif B&N'],
        'hdv-foto-limpia' => ['icon' => '🏡',  'palette' => ['#1a1a2e','#3B82C4','#93C5FD'], 'hint' => 'Foto limpia · panel inferior'],
    ];

    $slideStructures = [
        'commercial'  => ['cover','benefit','benefit','social_proof','cta'],
        'educational' => ['cover','problem','explanation','key_stat','benefit','cta'],
        'capture'     => ['cover','problem','benefit','benefit','social_proof','cta'],
        'informative' => ['cover','key_stat','explanation','explanation','cta'],
        'branding'    => ['cover','benefit','social_proof','example','cta'],
    ];
    $typeLabels = [
        'cover'=>'Portada','problem'=>'Problema','key_stat'=>'Estadística',
        'explanation'=>'Explicación','benefit'=>'Beneficio','example'=>'Ejemplo',
        'social_proof'=>'Prueba Social','cta'=>'CTA',
    ];
    $structure = $slideStructures[$carousel->type] ?? $slideStructures['educational'];
@endphp

<div class="page-header">
    <div>
        <h2>Generar con IA</h2>
        <p class="text-muted">{{ Str::limit($carousel->title, 60) }}</p>
    </div>
    <a href="{{ route('admin.carousels.show', $carousel) }}" class="btn btn-outline">← Volver al carrusel</a>
</div>

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:1.25rem;">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('admin.carousels.generate.run', $carousel) }}">
@csrf

{{-- ══ 1. TEMPLATE SELECTOR — full width ══ --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <h3 class="card-title">① Elige la plantilla visual</h3>
        <span style="font-size:.8rem;color:#6b7280;">Determina el estilo y guía a DALL-E sobre dónde dejar espacio para el texto</span>
    </div>
    <div class="card-body" style="padding:1rem 1.25rem;">
        <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
            @foreach($templates as $tpl)
                @php
                    $meta    = $templateMeta[$tpl->blade_view] ?? ['icon'=>'🎨','palette'=>['#3B82C4','#1E3A5F','#fff'],'hint'=>''];
                    $isRec   = $tpl->slug === $recommendedSlug;
                    $checked = $tpl->id == $preselect;
                @endphp
                <label class="tpl-card" style="
                    flex:0 0 calc(33.333% - .5rem);min-width:160px;
                    display:flex;flex-direction:column;gap:.5rem;
                    padding:.75rem;border-radius:8px;cursor:pointer;
                    border:2px solid {{ $checked ? '#3b82f6' : '#e5e7eb' }};
                    background:{{ $checked ? '#eff6ff' : '#fff' }};
                    position:relative;box-sizing:border-box;
                ">
                    <input type="radio" name="template_id" value="{{ $tpl->id }}"
                           {{ $checked ? 'checked' : '' }}
                           style="position:absolute;opacity:0;pointer-events:none;">
                    @if($isRec)
                    <span style="
                        position:absolute;top:.4rem;right:.4rem;
                        font-size:.58rem;font-weight:700;letter-spacing:.4px;
                        background:#3b82f6;color:#fff;padding:2px 7px;border-radius:20px;
                        text-transform:uppercase;white-space:nowrap;
                    ">★ Recomendado</span>
                    @endif
                    <div style="display:flex;gap:3px;border-radius:4px;overflow:hidden;height:26px;flex-shrink:0;">
                        @foreach($meta['palette'] as $color)
                            <div style="flex:1;background:{{ $color }};"></div>
                        @endforeach
                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.82rem;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $meta['icon'] }} {{ $tpl->name }}
                        </div>
                        <div style="font-size:.7rem;color:#6b7280;margin-top:1px;">{{ $meta['hint'] }}</div>
                    </div>
                </label>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ 2. OPTIONS + SIDEBAR ══ --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">

    <div>
        {{-- Generation options --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header"><h3 class="card-title">② Opciones de generación</h3></div>
            <div class="card-body">
                <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:1rem;margin-bottom:1.25rem;">
                    <div style="font-weight:600;color:#111827;margin-bottom:4px;">{{ $carousel->title }}</div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <span class="badge badge-blue">{{ ucfirst($carousel->type) }}</span>
                        @if($carousel->source_type)
                            <span class="badge badge-gray">Fuente: {{ ucfirst($carousel->source_type) }}</span>
                        @endif
                    </div>
                </div>

                <label style="display:flex;align-items:flex-start;gap:.75rem;padding:.875rem 1rem;border:1px solid #e5e7eb;border-radius:6px;cursor:pointer;background:#fff;">
                    <input type="checkbox" name="use_web_search" value="1" style="margin-top:2px;flex-shrink:0;"
                           {{ empty(config('services.perplexity.api_key')) ? 'disabled' : '' }}>
                    <div>
                        <div style="font-weight:500;font-size:.88rem;color:#111827;">
                            Enriquecer con datos de mercado
                            @if(empty(config('services.perplexity.api_key')))
                                <span style="color:#9ca3af;font-weight:400;">(requiere PERPLEXITY_API_KEY)</span>
                            @endif
                        </div>
                        <div style="font-size:.78rem;color:#6b7280;margin-top:2px;">
                            Usa Perplexity para estadísticas y tendencias en tiempo real.
                        </div>
                    </div>
                </label>

                @if($carousel->slides->count() > 0)
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:.875rem 1rem;margin-top:.75rem;">
                    <div style="display:flex;gap:.5rem;align-items:flex-start;">
                        <span style="color:#d97706;flex-shrink:0;">⚠</span>
                        <div style="font-size:.83rem;color:#92400e;">
                            Ya tiene <strong>{{ $carousel->slides->count() }} diapositivas</strong>. Se reemplazarán todas.
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Slide structure --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Estructura de diapositivas</h3>
                <span style="font-size:.82rem;color:#6b7280;">{{ count($structure) }} slides</span>
            </div>
            <div class="card-body" style="padding:1rem 1.25rem;">
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                    @foreach($structure as $i => $type)
                        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;min-width:64px;">
                            <div style="width:44px;height:44px;background:#f0f4ff;border:2px solid #c7d2fe;border-radius:6px;
                                        display:flex;align-items:center;justify-content:center;
                                        font-family:Georgia,serif;font-size:.95rem;font-weight:700;color:#4338ca;">
                                {{ $i + 1 }}
                            </div>
                            <div style="font-size:.68rem;color:#6b7280;text-align:center;font-weight:500;text-transform:uppercase;letter-spacing:.3px;">
                                {{ $typeLabels[$type] ?? $type }}
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div style="align-self:center;color:#d1d5db;font-size:.8rem;margin-top:-14px;">→</div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem;font-size:1rem;"
                onclick="this.disabled=true;this.textContent='Generando…';this.form.submit();">
            ✦ Generar carrusel con IA
        </button>
    </div>

    {{-- Sidebar --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <div class="card">
            <div class="card-header"><h3 class="card-title">¿Cómo funciona?</h3></div>
            <div class="card-body">
                <ol style="padding-left:1.25rem;font-size:.85rem;color:#4b5563;line-height:1.9;margin:0;">
                    <li>Elige plantilla → define composición de imágenes</li>
                    <li>Claude genera titulares, subtítulos y texto</li>
                    <li>Crea caption corto, largo y hashtags</li>
                    <li>Guarda una versión del resultado</li>
                    <li>El carrusel pasa a estado <strong>En revisión</strong></li>
                </ol>
                <div style="margin-top:1rem;padding:.75rem;background:#f0fdf4;border-radius:4px;font-size:.8rem;color:#166534;">
                    <strong>IA activa:</strong> {{ ucfirst(config('ai.default_provider','anthropic')) }}
                    ({{ config('ai.anthropic.model','claude-sonnet-4-6') }})
                </div>
            </div>
        </div>

        @if($carousel->source_type === 'property' && $carousel->source_id)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Fuente vinculada</h3></div>
            <div class="card-body" style="font-size:.85rem;color:#6b7280;">
                <strong>Propiedad ID:</strong> {{ $carousel->source_id }}<br>
                <span style="font-size:.78rem;">Los datos de la propiedad se incluirán en el contexto.</span>
            </div>
        </div>
        @endif

        @if($carousel->slides->count() > 0)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Solo caption</h3></div>
            <div class="card-body">
                <p style="font-size:.82rem;color:#6b7280;margin-bottom:.75rem;">
                    Regenera caption y hashtags sin tocar las diapositivas.
                </p>
                <form method="POST" action="{{ route('admin.carousels.regenerate-caption', $carousel) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="width:100%;">↺ Regenerar caption</button>
                </form>
            </div>
        </div>
        @endif
    </div>

</div>{{-- /grid --}}
</form>

@endsection

@section('scripts')
<script>
document.querySelectorAll('.tpl-card').forEach(card => {
    card.addEventListener('click', () => {
        document.querySelectorAll('.tpl-card').forEach(c => {
            c.style.borderColor = '#e5e7eb';
            c.style.background  = '#fff';
        });
        card.style.borderColor = '#3b82f6';
        card.style.background  = '#eff6ff';
        card.querySelector('input[type=radio]').checked = true;
    });
});
</script>
@endsection
