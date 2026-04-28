@extends('layouts.app-sidebar')

@section('title', 'Email Templates')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Email Templates</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">Gestiona plantillas de correo para marketing y campañas</p>
    </div>
    <a href="{{ route('admin.custom-templates.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nuevo Template
    </a>
</div>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#065f46;font-size:0.85rem">
    {{ session('success') }}
</div>
@endif

<!-- Filters -->
<form method="GET" action="{{ route('admin.custom-templates.index') }}" style="display:flex;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap">
    <input type="text" name="search" placeholder="Buscar templates..." value="{{ request('search') }}" class="form-input" style="flex:1;min-width:200px">
    <select name="status" onchange="this.form.submit()" class="form-select" style="width:auto">
        <option value="">Todos los estados</option>
        <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Borrador</option>
        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicado</option>
        <option value="archived"  {{ request('status') === 'archived'  ? 'selected' : '' }}>Archivado</option>
    </select>
    <select name="type" onchange="this.form.submit()" class="form-select" style="width:auto">
        <option value="">Todos los tipos</option>
        <option value="custom"      {{ request('type') === 'custom'      ? 'selected' : '' }}>Custom</option>
        <option value="marketing"   {{ request('type') === 'marketing'   ? 'selected' : '' }}>Marketing</option>
        <option value="newsletter"  {{ request('type') === 'newsletter'  ? 'selected' : '' }}>Newsletter</option>
        <option value="promotional" {{ request('type') === 'promotional' ? 'selected' : '' }}>Promocional</option>
    </select>
    @if(request('search') || request('status') || request('type'))
    <a href="{{ route('admin.custom-templates.index') }}" class="btn btn-outline">Limpiar</a>
    @endif
</form>

<!-- Table -->
<div class="card">
    @if($templates->count() > 0)
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Asignaciones</th>
                    <th>Creado por</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $template)
                <tr>
                    <td style="font-weight:600">{{ $template->name }}</td>
                    <td>
                        <span class="badge badge-blue">{{ ucfirst($template->template_type) }}</span>
                    </td>
                    <td>
                        @if($template->isDraft())
                            <span class="badge" style="background:#f1f5f9;color:#64748b">Borrador</span>
                        @elseif($template->isPublished())
                            <span class="badge badge-green">Publicado</span>
                        @else
                            <span class="badge badge-red">Archivado</span>
                        @endif
                    </td>
                    <td style="color:var(--text-muted)">{{ $template->assignments->count() }}</td>
                    <td style="color:var(--text-muted)">{{ $template->creator->name ?? 'N/A' }}</td>
                    <td>
                        <div style="display:flex;gap:0.5rem;align-items:center">
                            <a href="{{ route('admin.custom-templates.edit', $template) }}" class="btn btn-outline btn-sm">Editar</a>
                            <a href="{{ route('admin.custom-templates.clone', $template) }}" class="btn btn-outline btn-sm" onclick="return confirm('¿Clonar este template?')">Clonar</a>
                            <form method="POST" action="{{ route('admin.custom-templates.destroy', $template) }}" style="display:inline" onsubmit="return confirm('¿Eliminar este template?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.2rem;border-top:1px solid var(--border)">
        {{ $templates->links() }}
    </div>
    @else
    <div style="padding:3rem;text-align:center;color:var(--text-muted)">
        <p style="margin:0 0 1rem">No hay templates. Crea uno para empezar.</p>
        <a href="{{ route('admin.custom-templates.create') }}" class="btn btn-primary">Nuevo Template</a>
    </div>
    @endif
</div>
@endsection
