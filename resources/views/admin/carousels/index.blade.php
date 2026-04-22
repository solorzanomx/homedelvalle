@extends('layouts.app-sidebar')
@section('title', 'Carruseles IG')

@section('content')
<div class="page-header">
    <div>
        <h2>Carruseles Instagram</h2>
        <p class="text-muted">Crea y gestiona carruseles para redes sociales</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.carousels.discovery.form') }}" class="btn btn-outline">
            🔍 Descubrir temas
        </a>
        <a href="{{ route('admin.carousels.templates.index') }}" class="btn btn-outline">
            <x-icon name="settings" class="w-4 h-4" style="margin-right:0.3rem;vertical-align:-2px;" /> Plantillas
        </a>
        <a href="{{ route('admin.carousels.create') }}" class="btn btn-primary">+ Nuevo Carrusel</a>
    </div>
</div>

{{-- Filtros --}}
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 0.75rem 1.5rem;">
        <form method="GET" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <span class="text-muted" style="font-size: 0.82rem; font-weight: 500;">Estado:</span>
            <a href="{{ route('admin.carousels.index', request()->except('status')) }}"
               class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}">Todos</a>
            @foreach(['draft' => 'Borrador', 'review' => 'En revisión', 'approved' => 'Aprobado', 'published' => 'Publicado', 'archived' => 'Archivado'] as $val => $label)
                <a href="{{ route('admin.carousels.index', array_merge(request()->all(), ['status' => $val])) }}"
                   class="btn btn-sm {{ request('status') === $val ? 'btn-primary' : 'btn-outline' }}">{{ $label }}</a>
            @endforeach
            <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por título..." class="form-input" style="width: 220px; font-size: 0.85rem; padding: 0.35rem 0.75rem;">
                <button type="submit" class="btn btn-outline btn-sm">Buscar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Carrusel</th>
                    <th>Tipo</th>
                    <th>Contenido</th>
                    <th>Render</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @forelse($carousels as $carousel)
                @php
                    $total  = $carousel->slides_count;
                    $done   = $carousel->done_slides_count;
                    $hasSlides = $total > 0;
                    $allDone   = $hasSlides && $done === $total;
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('admin.carousels.show', $carousel) }}"
                           style="font-weight:600;color:#1e293b;text-decoration:none;">
                            {{ Str::limit($carousel->title, 50) }}
                        </a>
                        <div style="font-size:.75rem;color:#9ca3af;margin-top:2px;">
                            {{ $carousel->template?->name ?? 'Sin plantilla' }}
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-blue" style="font-size:.7rem;">{{ ucfirst($carousel->type) }}</span>
                    </td>
                    <td>
                        @if(!$hasSlides)
                            <span style="font-size:.8rem;color:#f59e0b;font-weight:500;">⚪ Sin contenido</span>
                        @else
                            <span style="font-size:.8rem;color:#10b981;font-weight:500;">✓ {{ $total }} slides</span>
                        @endif
                    </td>
                    <td>
                        @if(!$hasSlides)
                            <span style="font-size:.8rem;color:#d1d5db;">—</span>
                        @elseif($allDone)
                            <span style="font-size:.8rem;color:#10b981;font-weight:500;">✓ {{ $done }}/{{ $total }}</span>
                        @else
                            <span style="font-size:.8rem;color:#f59e0b;font-weight:500;">{{ $done }}/{{ $total }} renders</span>
                        @endif
                    </td>
                    <td>
                        @php $color = $carousel->status_color; @endphp
                        <span class="badge badge-{{ $color }}">{{ $carousel->status_label }}</span>
                    </td>
                    <td style="font-size:.8rem;color:#6b7280;white-space:nowrap;">
                        {{ $carousel->created_at->format('d/m/y') }}
                    </td>
                    <td>
                        @if(!$hasSlides)
                            {{-- No slides: go directly to generate --}}
                            <a href="{{ route('admin.carousels.generate', $carousel) }}"
                               class="btn btn-sm btn-primary">✦ Generar</a>
                        @elseif(!$allDone)
                            {{-- Has slides but not rendered: go to show for rendering --}}
                            <a href="{{ route('admin.carousels.show', $carousel) }}"
                               class="btn btn-sm btn-primary">⬡ Renderizar</a>
                        @else
                            {{-- All rendered --}}
                            <a href="{{ route('admin.carousels.show', $carousel) }}"
                               class="btn btn-sm btn-outline">Ver</a>
                        @endif
                        <a href="{{ route('admin.carousels.edit', $carousel) }}"
                           class="btn btn-sm btn-outline" style="margin-left:.25rem;">Editar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:2rem;color:#9ca3af;">
                        No hay carruseles todavía.
                        <a href="{{ route('admin.carousels.create') }}">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($carousels->hasPages())
        <div style="padding: 1rem 1.5rem;">{{ $carousels->links() }}</div>
    @endif
</div>
@endsection
