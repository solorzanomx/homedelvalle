@extends('layouts.app-sidebar')
@section('title', 'Plantillas de Carrusel')

@section('content')
<div class="page-header">
    <div>
        <h2>Plantillas de Carrusel</h2>
        <p class="text-muted">Gestiona las plantillas visuales para los carruseles de Instagram</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.carousels.index') }}" class="btn btn-outline">← Carruseles</a>
        <a href="{{ route('admin.carousels.templates.create') }}" class="btn btn-primary">+ Nueva plantilla</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Vista Blade</th>
                    <th>Canvas</th>
                    <th>Carruseles</th>
                    <th>Estado</th>
                    <th>Orden</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $template)
                <tr>
                    <td>
                        <div style="font-weight: 500;">{{ $template->name }}</div>
                        @if($template->description)
                            <div class="text-muted" style="font-size: 0.75rem;">{{ Str::limit($template->description, 50) }}</div>
                        @endif
                    </td>
                    <td><code style="font-size: 0.78rem;">{{ $template->slug }}</code></td>
                    <td><code style="font-size: 0.78rem;">{{ $template->blade_view }}</code></td>
                    <td style="font-size: 0.82rem; color: #6b7280;">{{ $template->canvas_size }}</td>
                    <td style="text-align: center;">{{ $template->posts_count ?? $template->posts()->count() }}</td>
                    <td>
                        @if($template->is_active)
                            <span class="badge badge-green">Activa</span>
                        @else
                            <span class="badge badge-gray">Inactiva</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $template->sort_order }}</td>
                    <td>
                        <div style="display: flex; gap: 0.4rem;">
                            <a href="{{ route('admin.carousels.templates.edit', $template) }}" class="btn btn-sm btn-outline">Editar</a>
                            <form method="POST" action="{{ route('admin.carousels.templates.destroy', $template) }}"
                                  onsubmit="return confirm('¿Eliminar esta plantilla?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger-outline">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: #9ca3af;">
                        No hay plantillas todavía.
                        <a href="{{ route('admin.carousels.templates.create') }}">Crear la primera</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
