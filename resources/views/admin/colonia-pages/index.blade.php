@extends('layouts.app-sidebar')
@section('title', 'Landing Pages de Colonias')

@section('content')
<div class="page-header">
    <div>
        <h2>Landing Pages de Colonias</h2>
        <p class="text-muted">Páginas SEO para /narvarte, /del-valle, /napoles, etc.</p>
    </div>
    <a href="{{ route('admin.colonia-pages.create') }}" class="btn btn-primary">+ Nueva colonia</a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body" style="padding:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>URL</th>
                    <th>Términos de búsqueda</th>
                    <th>FAQs</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($colonias as $colonia)
                <tr>
                    <td>
                        <strong>{{ $colonia->name }}</strong>
                        @if($colonia->meta_title)
                        <br><small class="text-muted">{{ Str::limit($colonia->meta_title, 60) }}</small>
                        @endif
                    </td>
                    <td>
                        <a href="{{ url('/' . $colonia->slug) }}" target="_blank" class="text-primary" style="font-size:0.82rem;">
                            /{{ $colonia->slug }}
                        </a>
                    </td>
                    <td style="font-size:0.82rem;">{{ $colonia->colony_search_terms ?: '—' }}</td>
                    <td>
                        <span class="badge {{ count($colonia->faqs ?? []) ? 'badge-success' : 'badge-secondary' }}">
                            {{ count($colonia->faqs ?? []) }} FAQs
                        </span>
                    </td>
                    <td>
                        @if($colonia->is_published)
                        <span class="badge badge-success">Publicada</span>
                        @else
                        <span class="badge badge-secondary">Borrador</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:0.4rem;">
                            <a href="{{ url('/' . $colonia->slug) }}" target="_blank" class="btn btn-sm btn-outline" title="Ver página">
                                <x-icon name="external-link" class="w-4 h-4" />
                            </a>
                            <a href="{{ route('admin.colonia-pages.edit', $colonia) }}" class="btn btn-sm btn-outline">Editar</a>
                            <form action="{{ route('admin.colonia-pages.destroy', $colonia) }}" method="POST"
                                  onsubmit="return confirm('¿Eliminar {{ $colonia->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:#6b7280;">
                        No hay colonias todavía. <a href="{{ route('admin.colonia-pages.create') }}">Crear la primera</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
