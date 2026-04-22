@extends('layouts.app-sidebar')
@section('title', 'Generar con IA')

@section('content')
<div class="page-header">
    <div>
        <h2>Generar con IA</h2>
        <p class="text-muted">{{ Str::limit($carousel->title, 60) }}</p>
    </div>
    <a href="{{ route('admin.carousels.show', $carousel) }}" class="btn btn-outline">← Volver al carrusel</a>
</div>

@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom: 1.25rem;">{{ session('error') }}</div>
@endif

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start;">

    {{-- Formulario principal --}}
    <div>
        <form method="POST" action="{{ route('admin.carousels.generate.run', $carousel) }}">
        @csrf

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Configuración de generación</h3>
            </div>
            <div class="card-body">

                {{-- Resumen del carrusel --}}
                <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 6px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #111827; margin-bottom: 4px;">{{ $carousel->title }}</div>
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <span class="badge badge-blue">{{ ucfirst($carousel->type) }}</span>
                                @if($carousel->source_type)
                                    <span class="badge badge-gray">Fuente: {{ ucfirst($carousel->source_type) }}</span>
                                @endif
                                @if($carousel->template)
                                    <span class="badge badge-gray">{{ $carousel->template->name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Opciones de generación --}}
                <div class="form-group">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #374151; display: block; margin-bottom: 0.75rem;">
                        Opciones
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.875rem 1rem; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; margin-bottom: 0.5rem; background: #fff;"
                           id="web-search-label">
                        <input type="checkbox" name="use_web_search" value="1" id="use_web_search" style="margin-top: 2px; flex-shrink: 0;"
                               {{ empty(config('services.perplexity.api_key')) ? 'disabled' : '' }}>
                        <div>
                            <div style="font-weight: 500; font-size: 0.88rem; color: #111827;">
                                Enriquecer con datos de mercado
                                @if(empty(config('services.perplexity.api_key')))
                                    <span style="color: #9ca3af; font-weight: 400;">(requiere PERPLEXITY_API_KEY)</span>
                                @endif
                            </div>
                            <div style="font-size: 0.78rem; color: #6b7280; margin-top: 2px;">
                                Usa Perplexity para buscar estadísticas y tendencias del mercado inmobiliario en tiempo real.
                                Mejora carruseles informativos y educativos.
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Aviso si ya hay slides --}}
                @if($carousel->slides->count() > 0)
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 0.875rem 1rem; margin-top: 0.75rem;">
                    <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                        <span style="color: #d97706; flex-shrink: 0;">⚠</span>
                        <div style="font-size: 0.83rem; color: #92400e;">
                            Este carrusel ya tiene <strong>{{ $carousel->slides->count() }} diapositivas</strong>.
                            Al generar, se reemplazarán todas con las nuevas. Esta acción no se puede deshacer
                            (aunque se guardará una versión del estado actual).
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- Estructura de slides que se generarán --}}
        @php
            $slideStructures = [
                'commercial'  => ['cover', 'benefit', 'benefit', 'social_proof', 'cta'],
                'educational' => ['cover', 'problem', 'explanation', 'key_stat', 'benefit', 'cta'],
                'capture'     => ['cover', 'problem', 'benefit', 'benefit', 'social_proof', 'cta'],
                'informative' => ['cover', 'key_stat', 'explanation', 'explanation', 'cta'],
                'branding'    => ['cover', 'benefit', 'social_proof', 'example', 'cta'],
            ];
            $typeLabels = [
                'cover' => 'Portada', 'problem' => 'Problema', 'key_stat' => 'Estadística',
                'explanation' => 'Explicación', 'benefit' => 'Beneficio', 'example' => 'Ejemplo',
                'social_proof' => 'Prueba Social', 'cta' => 'CTA',
            ];
            $structure = $slideStructures[$carousel->type] ?? $slideStructures['educational'];
        @endphp

        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3 class="card-title">Estructura de diapositivas</h3>
                <span class="text-muted" style="font-size: 0.82rem;">{{ count($structure) }} slides</span>
            </div>
            <div class="card-body" style="padding: 1rem 1.25rem;">
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    @foreach($structure as $i => $type)
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 64px;">
                            <div style="width: 48px; height: 48px; background: #f0f4ff; border: 2px solid #c7d2fe; border-radius: 6px;
                                        display: flex; align-items: center; justify-content: center;
                                        font-family: Georgia, serif; font-size: 1rem; font-weight: 700; color: #4338ca;">
                                {{ $i + 1 }}
                            </div>
                            <div style="font-size: 0.7rem; color: #6b7280; text-align: center; font-weight: 500; text-transform: uppercase; letter-spacing: 0.3px;">
                                {{ $typeLabels[$type] ?? $type }}
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div style="align-self: center; color: #d1d5db; font-size: 0.8rem; margin-top: -14px;">→</div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.875rem; font-size: 1rem;"
                onclick="this.disabled=true; this.textContent='Generando…'; this.form.submit();">
            ✦ Generar carrusel con IA
        </button>

        </form>
    </div>

    {{-- Panel lateral --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        <div class="card">
            <div class="card-header"><h3 class="card-title">¿Cómo funciona?</h3></div>
            <div class="card-body">
                <ol style="padding-left: 1.25rem; font-size: 0.85rem; color: #4b5563; line-height: 1.9; margin: 0;">
                    <li>Claude analiza el tema y tipo del carrusel</li>
                    <li>Genera titulares, subtítulos y texto para cada slide</li>
                    <li>Crea caption corto, largo y hashtags</li>
                    <li>Guarda una versión del resultado</li>
                    <li>El carrusel pasa a estado <strong>En revisión</strong></li>
                </ol>
                <div style="margin-top: 1rem; padding: 0.75rem; background: #f0fdf4; border-radius: 4px; font-size: 0.8rem; color: #166534;">
                    <strong>Proveedor activo:</strong> {{ ucfirst(config('ai.default_provider', 'anthropic')) }}
                    ({{ config('ai.anthropic.model', 'claude-sonnet-4-6') }})
                </div>
            </div>
        </div>

        @if($carousel->source_type === 'property' && $carousel->source_id)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Fuente vinculada</h3></div>
            <div class="card-body" style="font-size: 0.85rem; color: #6b7280;">
                <strong>Propiedad ID:</strong> {{ $carousel->source_id }}<br>
                <span style="font-size: 0.78rem;">Los datos de la propiedad se incluirán automáticamente en el contexto.</span>
            </div>
        </div>
        @endif

        {{-- Regenerar solo caption --}}
        @if($carousel->slides->count() > 0)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Solo caption</h3></div>
            <div class="card-body">
                <p style="font-size: 0.82rem; color: #6b7280; margin-bottom: 0.75rem;">
                    Regenera solo el caption y hashtags sin tocar las diapositivas existentes.
                </p>
                <form method="POST" action="{{ route('admin.carousels.regenerate-caption', $carousel) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="width: 100%;">
                        ↺ Regenerar caption
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
