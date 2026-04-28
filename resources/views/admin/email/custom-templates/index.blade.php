@extends('layouts.app-sidebar')

@section('title', 'Email Templates')

@section('content')
<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">Email Templates</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">Gestión de todas las plantillas de correo del sistema</p>
    </div>
    <a href="{{ route('admin.custom-templates.create') }}" class="btn btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nuevo Template
    </a>
</div>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1.25rem;color:#065f46;font-size:0.85rem">
    {{ session('success') }}
</div>
@endif

{{-- ══════════════════════════════════════════════════
     SECCIÓN 1 — TRANSACCIONALES (V4, hardcoded)
═══════════════════════════════════════════════════ --}}
<div style="margin-bottom:2rem">
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem">
        <h2 style="font-size:0.9rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);margin:0">Transaccionales</h2>
        <span class="badge badge-blue">{{ count($transactionalTemplates) }}</span>
        <span style="font-size:0.75rem;color:var(--text-muted)">— Hardcoded · Solo lectura</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem">
        @foreach($transactionalTemplates as $t)
        <div class="card" style="margin:0;display:flex;flex-direction:column">
            <div class="card-body" style="flex:1;padding:1.1rem">
                <div style="display:flex;align-items:flex-start;gap:0.75rem">
                    <span style="font-size:1.4rem;line-height:1;flex-shrink:0">{{ $t['icon'] }}</span>
                    <div style="min-width:0">
                        <p style="font-weight:600;font-size:0.9rem;margin:0 0 0.25rem">{{ $t['name'] }}</p>
                        <p style="font-size:0.78rem;color:var(--text-muted);margin:0;line-height:1.4">{{ $t['description'] }}</p>
                    </div>
                </div>
            </div>
            <div style="padding:0.6rem 1.1rem;border-top:1px solid var(--border);display:flex;gap:0.5rem">
                <a href="{{ route('admin.transactional-emails.preview', $t['id']) }}" class="btn btn-outline btn-sm" style="flex:1;justify-content:center;text-align:center">
                    Vista previa
                </a>
                <a href="{{ route('admin.transactional-emails.preview', $t['id']) }}" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;text-align:center">
                    Enviar prueba
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     SECCIÓN 2 — CUSTOM (DB-backed)
═══════════════════════════════════════════════════ --}}
<div>
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem">
        <h2 style="font-size:0.9rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);margin:0">Custom / Marketing</h2>
        <span class="badge badge-green">{{ $templates->total() }}</span>
        <span style="font-size:0.75rem;color:var(--text-muted)">— Editables desde el panel</span>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('admin.custom-templates.index') }}" style="display:flex;gap:0.75rem;margin-bottom:0.75rem;flex-wrap:wrap">
        <input type="text" name="search" placeholder="Buscar templates..." value="{{ request('search') }}" class="form-input" style="flex:1;min-width:180px">
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

    <div class="card">
        @if($templates->count() > 0)
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Asign.</th>
                        <th>Creado por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                    <tr>
                        <td style="font-weight:600">{{ $template->name }}</td>
                        <td><span class="badge badge-blue">{{ ucfirst($template->template_type) }}</span></td>
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
                            <div style="display:flex;gap:0.4rem;align-items:center">
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
        <div style="padding:0.75rem 1.2rem;border-top:1px solid var(--border)">
            {{ $templates->links() }}
        </div>
        @else
        <div style="padding:2.5rem;text-align:center;color:var(--text-muted)">
            <p style="margin:0 0 1rem">
                @if(request('search') || request('status') || request('type'))
                    No hay templates con esos filtros.
                @else
                    Aún no tienes templates custom. Crea uno para empezar.
                @endif
            </p>
            <a href="{{ route('admin.custom-templates.create') }}" class="btn btn-primary">Crear primer template</a>
        </div>
        @endif
    </div>
</div>
@endsection
