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
                    <th>Plantilla</th>
                    <th>Diapositivas</th>
                    <th>Estado</th>
                    <th>Creado por</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($carousels as $carousel)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ Str::limit($carousel->title, 55) }}</div>
                        @if($carousel->cta)
                            <div class="text-muted" style="font-size: 0.75rem;">{{ Str::limit($carousel->cta, 50) }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-blue">{{ ucfirst($carousel->type) }}</span>
                    </td>
                    <td>{{ $carousel->template?->name ?? '—' }}</td>
                    <td style="text-align: center;">{{ $carousel->slides_count }}</td>
                    <td>
                        @php $color = $carousel->status_color; @endphp
                        <span class="badge badge-{{ $color }}">{{ $carousel->status_label }}</span>
                    </td>
                    <td>{{ $carousel->user?->name ?? '—' }}</td>
                    <td style="font-size: 0.8rem; color: #6b7280;">{{ $carousel->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div style="display: flex; gap: 0.4rem;">
                            <a href="{{ route('admin.carousels.show', $carousel) }}" class="btn btn-sm btn-outline">Ver</a>
                            <a href="{{ route('admin.carousels.edit', $carousel) }}" class="btn btn-sm btn-outline">Editar</a>
                            <form method="POST" action="{{ route('admin.carousels.destroy', $carousel) }}"
                                  onsubmit="return confirm('¿Eliminar este carrusel?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger-outline">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: #9ca3af;">
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
