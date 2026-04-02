@extends('layouts.app-sidebar')
@section('title', 'Paginas')

@section('content')
<div class="page-header">
    <div>
        <h2>Paginas</h2>
        <p class="text-muted">Gestiona las paginas estaticas del sitio</p>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">+ Nueva Pagina</a>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titulo</th>
                    <th>Slug</th>
                    <th>Estado</th>
                    <th>Orden</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr>
                    <td style="font-weight: 500;">{{ $page->title }}</td>
                    <td>
                        <span class="text-muted" style="font-size: 0.82rem;">/p/{{ $page->slug }}</span>
                    </td>
                    <td>
                        @if($page->is_published)
                            <span class="badge badge-green">Publicada</span>
                        @else
                            <span class="badge badge-yellow">Borrador</span>
                        @endif
                    </td>
                    <td>{{ $page->sort_order ?? 0 }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline">Editar</a>
                            <form action="{{ route('admin.pages.destroy', $page) }}" method="POST"
                                  onsubmit="return confirm('Eliminar esta pagina?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 2rem;">
                        No hay paginas todavia.
                        <a href="{{ route('admin.pages.create') }}" style="color: var(--primary); font-weight: 500;">Crear la primera</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($pages->hasPages())
<div style="display: flex; justify-content: center; margin-top: 1rem;">
    {{ $pages->links() }}
</div>
@endif
@endsection
