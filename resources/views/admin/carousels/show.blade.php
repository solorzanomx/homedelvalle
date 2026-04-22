@extends('layouts.app-sidebar')
@section('title', $carousel->title)

@section('content')
<div class="page-header">
    <div>
        <h2>{{ Str::limit($carousel->title, 60) }}</h2>
        <p class="text-muted">
            <span class="badge badge-{{ $carousel->status_color }}">{{ $carousel->status_label }}</span>
            &nbsp;·&nbsp; {{ ucfirst($carousel->type) }}
            &nbsp;·&nbsp; {{ $carousel->slides->count() }} diapositivas
        </p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.carousels.edit', $carousel) }}" class="btn btn-outline">Editar</a>
        <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline">← Volver</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
@endif

<div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start;">

    {{-- Columna principal --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        {{-- Diapositivas --}}
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3 class="card-title">Diapositivas</h3>
                <span class="text-muted" style="font-size: 0.82rem;">{{ $carousel->slides->count() }} slides</span>
            </div>
            @if($carousel->slides->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; padding: 1.25rem;">
                    @foreach($carousel->slides as $slide)
                        <div style="border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden;">
                            {{-- Render preview or placeholder --}}
                            <div style="width: 100%; aspect-ratio: 1; background: #f8fafc; display: flex; align-items: center; justify-content: center; position: relative;">
                                @if($slide->rendered_image_path)
                                    <img src="{{ Storage::url($slide->rendered_image_path) }}" alt="Slide {{ $slide->order }}"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="text-align: center; color: #9ca3af;">
                                        <div style="font-size: 1.5rem; margin-bottom: 0.25rem;">{{ $slide->order }}</div>
                                        <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">{{ $slide->type_label }}</div>
                                    </div>
                                @endif
                                {{-- Render status badge --}}
                                <div style="position: absolute; top: 4px; right: 4px;">
                                    @if($slide->render_status === 'done')
                                        <span class="badge badge-green" style="font-size: 0.65rem;">✓</span>
                                    @elseif($slide->render_status === 'failed')
                                        <span class="badge badge-red" style="font-size: 0.65rem;">✗</span>
                                    @endif
                                </div>
                            </div>
                            <div style="padding: 0.5rem 0.75rem; border-top: 1px solid #f0f0f0;">
                                <div style="font-size: 0.78rem; font-weight: 600; color: #374151;">{{ $slide->type_label }}</div>
                                @if($slide->headline)
                                    <div style="font-size: 0.72rem; color: #6b7280; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ Str::limit($slide->headline, 30) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card-body" style="text-align: center; padding: 2.5rem; color: #9ca3af;">
                    <p>No hay diapositivas todavía.</p>
                    <p style="font-size: 0.82rem;">Las diapositivas se crearán al generar el carrusel con IA.</p>
                </div>
            @endif
        </div>

        {{-- Caption --}}
        @if($carousel->caption_short || $carousel->caption_long)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Caption</h3></div>
            <div class="card-body">
                @if($carousel->caption_short)
                    <div class="form-group">
                        <label class="form-label text-muted" style="font-size: 0.78rem;">CAPTION CORTO</label>
                        <p style="font-size: 0.9rem; line-height: 1.6; background: #f8fafc; padding: 0.75rem; border-radius: 4px; border-left: 3px solid #2563eb;">
                            {{ $carousel->caption_short }}
                        </p>
                    </div>
                @endif
                @if($carousel->caption_long)
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label text-muted" style="font-size: 0.78rem;">CAPTION LARGO</label>
                        <p style="font-size: 0.88rem; line-height: 1.7; color: #4b5563;">{{ $carousel->caption_long }}</p>
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Versiones --}}
        @if($carousel->versions->count() > 0)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Historial de versiones</h3></div>
            <div class="card-body" style="padding: 0;">
                <table class="data-table">
                    <thead><tr><th>Versión</th><th>Etiqueta</th><th>Creada por</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach($carousel->versions as $version)
                        <tr>
                            <td>v{{ $version->version_number }}</td>
                            <td>{{ $version->label ?? '—' }}</td>
                            <td>{{ $version->creator?->name ?? '—' }}</td>
                            <td style="font-size: 0.8rem; color: #6b7280;">{{ $version->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    {{-- Columna lateral --}}
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">

        {{-- Meta --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detalles</h3></div>
            <div class="card-body" style="padding: 0;">
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
                        $rows['Aprobado']    = $carousel->approved_at->format('d/m/Y');
                        $rows['Aprobado por'] = $carousel->approvedBy?->name ?? '—';
                    }
                    if ($carousel->published_at) {
                        $rows['Publicado'] = $carousel->published_at->format('d/m/Y H:i');
                    }
                @endphp
                @foreach($rows as $label => $value)
                <div style="display: flex; justify-content: space-between; padding: 0.6rem 1.25rem; border-bottom: 1px solid #f0f2f5; font-size: 0.85rem;">
                    <span style="color: #9ca3af; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.3px;">{{ $label }}</span>
                    <span style="color: #1f2937; font-weight: 500;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Acciones --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Acciones</h3></div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 0.6rem;">
                <a href="{{ route('admin.carousels.generate', $carousel) }}"
                   class="btn btn-primary {{ !$carousel->isEditable() ? 'disabled' : '' }}">
                    ✦ Generar con IA
                </a>

                {{-- Render all --}}
                @if($carousel->slides->count() > 0)
                <form method="POST" action="{{ route('admin.carousels.render', $carousel) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width: 100%;">
                        ⬡ Renderizar diapositivas
                    </button>
                </form>

                {{-- Render status polling --}}
                @php
                    $pending = $carousel->slides->whereIn('render_status', ['pending','rendering'])->count();
                    $done    = $carousel->slides->where('render_status', 'done')->count();
                    $failed  = $carousel->slides->where('render_status', 'failed')->count();
                @endphp
                @if($pending > 0)
                <div id="render-status-bar" style="font-size: 0.8rem; color: #6b7280; background: #f8fafc; border-radius: 4px; padding: 0.5rem 0.75rem;">
                    ⏳ Renderizando… {{ $done }}/{{ $carousel->slides->count() }} listas
                    <script>
                    (function() {
                        const bar = document.getElementById('render-status-bar');
                        const poll = setInterval(async () => {
                            const r = await fetch('{{ route('admin.carousels.render.status', $carousel) }}');
                            const d = await r.json();
                            bar.textContent = d.complete
                                ? '✓ Renderizado completo (' + d.done + '/' + d.total + ')'
                                : '⏳ Renderizando… ' + d.done + '/' + d.total + ' listas';
                            if (d.complete) { clearInterval(poll); location.reload(); }
                        }, 3000);
                    })();
                    </script>
                </div>
                @elseif($done > 0 || $failed > 0)
                <div style="font-size: 0.8rem; color: #6b7280; padding: 0.4rem 0.75rem;">
                    @if($failed > 0)
                        <span style="color: #dc2626;">✗ {{ $failed }} con error</span> &nbsp;
                    @endif
                    <span style="color: #16a34a;">✓ {{ $done }} listas</span>
                </div>
                @endif
                @else
                <button class="btn btn-outline" disabled title="Genera el carrusel con IA primero">
                    ⬡ Renderizar diapositivas
                </button>
                @endif

                {{-- Approval --}}
                @if($carousel->status === 'review')
                <form method="POST" action="{{ route('admin.carousels.approve', $carousel) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width: 100%; color: #16a34a; border-color: #16a34a;"
                            onclick="return confirm('¿Aprobar este carrusel y enviar a n8n?')">
                        ✓ Aprobar carrusel
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.carousels.reject', $carousel) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width: 100%;"
                            onclick="return confirm('¿Rechazar y devolver a borrador?')">
                        ✗ Rechazar
                    </button>
                </form>
                @elseif($carousel->status === 'approved')
                <div style="font-size: 0.8rem; color: #16a34a; padding: 0.4rem 0; font-weight: 600;">
                    ✓ Aprobado {{ $carousel->approved_at?->format('d/m/Y H:i') }}
                </div>
                <form method="POST" action="{{ route('admin.carousels.webhook', $carousel) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="width: 100%;">
                        ↗ Re-enviar a n8n
                    </button>
                </form>
                @else
                <button class="btn btn-outline" disabled title="Genera el carrusel primero">
                    ✓ Aprobar carrusel
                </button>
                @endif

                <button class="btn btn-outline" disabled title="Disponible en Fase 5">
                    ↗ Publicar en Instagram
                </button>
                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0.25rem 0;">
                <a href="{{ route('admin.carousels.edit', $carousel) }}" class="btn btn-primary">Editar</a>
            </div>
        </div>

        {{-- Publicaciones --}}
        @if($carousel->publications->count() > 0)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Publicaciones</h3></div>
            <div class="card-body" style="padding: 0;">
                @foreach($carousel->publications as $pub)
                <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid #f0f2f5; font-size: 0.82rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                        <span style="font-weight: 600;">{{ ucfirst($pub->channel) }}</span>
                        <span class="badge badge-{{ $pub->status === 'published' ? 'green' : ($pub->status === 'failed' ? 'red' : 'yellow') }}">
                            {{ $pub->status_label }}
                        </span>
                    </div>
                    <div style="color: #9ca3af;">{{ $pub->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
