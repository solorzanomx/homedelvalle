@extends('layouts.app-sidebar')
@section('title', 'Blog Posts')

@section('content')
<div class="page-header">
    <div>
        <h2>Blog Posts</h2>
        <p class="text-muted">Gestiona los articulos del blog</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        @if(Route::has('admin.content-calendar'))
        <a href="{{ route('admin.content-calendar') }}" class="btn btn-outline"><x-icon name="calendar" class="w-4 h-4" style="margin-right:0.3rem;vertical-align:-2px;" /> Calendario</a>
        @endif
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">+ Nuevo Post</a>
    </div>
</div>

{{-- Filter bar --}}
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 0.75rem 1.5rem;">
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <span class="text-muted" style="font-size: 0.82rem; font-weight: 500;">Filtrar:</span>
            <a href="{{ route('admin.posts.index') }}"
               class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}">
                Todos
            </a>
            <a href="{{ route('admin.posts.index', ['status' => 'draft']) }}"
               class="btn btn-sm {{ request('status') === 'draft' ? 'btn-primary' : 'btn-outline' }}">
                Borrador
            </a>
            <a href="{{ route('admin.posts.index', ['status' => 'published']) }}"
               class="btn btn-sm {{ request('status') === 'published' ? 'btn-primary' : 'btn-outline' }}">
                Publicado
            </a>
            <a href="{{ route('admin.posts.index', ['status' => 'archived']) }}"
               class="btn btn-sm {{ request('status') === 'archived' ? 'btn-primary' : 'btn-outline' }}">
                Archivado
            </a>
            <a href="{{ route('admin.posts.index', ['status' => 'scheduled']) }}"
               class="btn btn-sm {{ request('status') === 'scheduled' ? 'btn-primary' : 'btn-outline' }}">
                Programado
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titulo</th>
                    <th>Autor</th>
                    <th>Categoria</th>
                    <th>Estado</th>
                    <th>Fecha Pub.</th>
                    <th>Vistas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if($post->featured_image)
                                <img src="{{ Storage::url($post->featured_image) }}" alt=""
                                     style="width: 48px; height: 36px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                            @endif
                            <div>
                                <div style="font-weight: 500;">{{ Str::limit($post->title, 50) }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">/blog/{{ $post->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $post->author->name ?? '—' }}</td>
                    <td>
                        @if($post->category)
                            <span class="badge badge-blue">{{ $post->category->name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($post->status === 'published')
                            <span class="badge badge-green">Publicado</span>
                        @elseif($post->status === 'draft')
                            <span class="badge badge-yellow">Borrador</span>
                        @elseif($post->status === 'scheduled')
                            <span class="badge badge-blue">Programado</span>
                        @else
                            <span class="badge badge-red">Archivado</span>
                        @endif
                    </td>
                    <td>
                        @if($post->published_at)
                            {{ $post->published_at->format('d/m/Y') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ number_format($post->views_count ?? 0) }}</td>
                    <td>
                        <div class="action-btns">
                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline">Editar</a>
                            <form action="{{ route('admin.posts.destroy', $post) }}" method="POST"
                                  onsubmit="return confirm('Eliminar este post?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 2rem;">
                        No hay posts todavia.
                        <a href="{{ route('admin.posts.create') }}" style="color: var(--primary); font-weight: 500;">Crear el primero</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($posts->hasPages())
<div style="display: flex; justify-content: center; margin-top: 1rem;">
    {{ $posts->links() }}
</div>
@endif
@endsection
