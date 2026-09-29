@extends('layouts.app-sidebar')
@section('title', 'Redirects del Blog')

@section('styles')
<style>
    .redir-grid { display: grid; grid-template-columns: 380px 1fr; gap: 1.5rem; align-items: start; }
    @media (max-width: 1024px) { .redir-grid { grid-template-columns: 1fr; } }
    .redir-inactive { opacity: .5; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Redirects del Blog</h2>
        <p class="text-muted">Slugs que ya no existen → 301 (o 410 si se fueron a propósito). "auto" en notas = lo creó el sistema solo.</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-outline">&#8592; Blog Posts</a>
</div>

<div class="redir-grid">
    <div class="card">
        <div class="card-header"><h3>Nuevo redirect</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.blog-redirects.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Desde <span class="required">*</span></label>
                    <input type="text" name="from_path" class="form-input" value="{{ old('from_path') }}" required placeholder="/blog/slug-viejo">
                </div>
                <div class="form-group">
                    <label class="form-label">Hacia <span class="required">*</span></label>
                    <input type="text" name="to_path" class="form-input" value="{{ old('to_path') }}" required placeholder="/blog/slug-nuevo">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="status" class="form-input">
                        <option value="301" selected>301 — redirige</option>
                        <option value="410">410 — ya no existe (a propósito)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notas</label>
                    <input type="text" name="notes" class="form-input" value="{{ old('notes') }}" placeholder="Opcional">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Crear redirect</button>
            </form>
            @if($errors->any())<div style="margin-top:.6rem;font-size:.78rem;color:#b91c1c;">{{ $errors->first() }}</div>@endif
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Desde</th><th>Hacia</th><th>Tipo</th><th>Hits</th><th>Último hit</th><th>Notas</th><th style="width:140px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($redirects as $r)
                    <tr class="{{ $r->active ? '' : 'redir-inactive' }}">
                        <td style="font-size:.78rem;">{{ $r->from_path }}</td>
                        <td style="font-size:.78rem;">{{ $r->to_path }}</td>
                        <td>{{ $r->status }}</td>
                        <td>{{ $r->hits }}</td>
                        <td style="font-size:.75rem;color:var(--text-muted);">{{ $r->last_hit_at?->format('d/m/Y') ?? '—' }}</td>
                        <td style="font-size:.75rem;color:var(--text-muted);">{{ $r->notes }}</td>
                        <td style="display:flex;gap:.3rem;">
                            <form method="POST" action="{{ route('admin.blog-redirects.toggle', $r->id) }}">@csrf<button class="btn btn-sm btn-outline">{{ $r->active ? 'Desactivar' : 'Activar' }}</button></form>
                            <form method="POST" action="{{ route('admin.blog-redirects.destroy', $r->id) }}" onsubmit="return confirm('¿Eliminar este redirect?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline" style="color:#b91c1c;">✕</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:2rem;">Sin redirects todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $redirects->links() }}</div>
    </div>
</div>
@endsection
