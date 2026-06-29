@extends('layouts.app-sidebar')
@section('title', 'Posts Facebook')

@section('content')
<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
.btn-primary { background:var(--primary); color:#fff; }
.btn-fb      { background:#1877F2; color:#fff; }
.btn-outline  { background:#fff; color:var(--text); border:1px solid var(--border); }
.btn-danger   { background:#fef2f2; color:#dc2626; border:1px solid #fca5a5; }
.btn-sm       { padding:.3rem .65rem; font-size:.78rem; }
.table-card   { background:#fff; border-radius:12px; border:1px solid var(--border); overflow:hidden; }
table { width:100%; border-collapse:collapse; }
thead th { padding:.7rem 1rem; font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-muted); border-bottom:1px solid var(--border); text-align:left; background:#fafafa; }
tbody td { padding:.75rem 1rem; font-size:.85rem; border-bottom:1px solid var(--border); vertical-align:middle; }
tbody tr:last-child td { border-bottom:none; }
.badge { display:inline-block; padding:.15rem .55rem; border-radius:999px; font-size:.72rem; font-weight:600; }
.badge-done    { background:#f0fdf4; color:#15803d; }
.badge-pending { background:#fffbeb; color:#92400e; }
.badge-failed  { background:#fef2f2; color:#dc2626; }
.badge-rendering { background:#eff6ff; color:#1d4ed8; }
.badge-draft     { background:#f1f5f9; color:#64748b; }
.badge-review    { background:#fef9c3; color:#854d0e; }
.badge-published { background:#f0fdf4; color:#15803d; }
.tpl-chip { display:inline-flex; align-items:center; gap:.3rem; font-size:.78rem; padding:.2rem .55rem; border-radius:6px; background:#f1f5f9; color:#475569; font-weight:500; }
.thumb { width:80px; height:42px; border-radius:5px; object-fit:cover; border:1px solid var(--border); }
.thumb-placeholder { width:80px; height:42px; border-radius:5px; background:#f1f5f9; border:1px solid var(--border); display:inline-flex; align-items:center; justify-content:center; font-size:.65rem; color:var(--text-muted); text-align:center; }
.filter-tabs { display:flex; gap:.4rem; margin-bottom:1.25rem; }
.filter-tab { padding:.35rem .85rem; border-radius:8px; font-size:.8rem; font-weight:600; cursor:pointer; border:1px solid var(--border); background:#fff; color:var(--text-muted); text-decoration:none; }
.filter-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
</style>

<div class="page-header">
    <div>
        <h2 style="margin:0;display:flex;align-items:center;gap:.5rem;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.236 2.686.236v2.97h-1.514c-1.491 0-1.956.93-1.956 1.887v2.267h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
            Posts Facebook
        </h2>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.2rem;">Genera imágenes 1200×628 con Claude + Browsershot y publica directo en Facebook</p>
    </div>
    <a href="{{ route('admin.facebook.create') }}" class="btn btn-primary">+ Nuevo post</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#15803d;">{{ session('success') }}</div>
@endif

{{-- Filter tabs --}}
@php
    $filter  = request('status', 'all');
    $allCount = $posts->total();
@endphp
<div class="filter-tabs">
    <a href="{{ route('admin.facebook.index') }}" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">Todos ({{ $allCount }})</a>
    <a href="{{ route('admin.facebook.index', ['status' => 'draft']) }}" class="filter-tab {{ $filter === 'draft' ? 'active' : '' }}">Borradores</a>
    <a href="{{ route('admin.facebook.index', ['status' => 'published']) }}" class="filter-tab {{ $filter === 'published' ? 'active' : '' }}">Publicados</a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th style="width:85px;">Preview</th>
                <th>Título</th>
                <th>Template</th>
                <th>Imagen</th>
                <th>Estado</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $p)
            <tr>
                <td>
                    @if($p->rendered_image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->rendered_image_path) }}" class="thumb" alt="Preview">
                    @else
                    <div class="thumb-placeholder">Sin imagen</div>
                    @endif
                </td>
                <td style="font-weight:600;max-width:220px;">
                    <a href="{{ route('admin.facebook.show', $p) }}" style="color:inherit;text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->title }}</a>
                    @if($p->fb_post_url)
                    <a href="{{ $p->fb_post_url }}" target="_blank" rel="noopener" style="font-size:.75rem;color:#1877F2;font-weight:500;display:flex;align-items:center;gap:.2rem;margin-top:.2rem;">&#128279; Ver en Facebook</a>
                    @endif
                </td>
                <td>
                    <span class="tpl-chip">
                        @php $tplLabel = \App\Models\FacebookPost::TEMPLATES[$p->template] ?? $p->template; @endphp
                        {{ explode(' —', $tplLabel)[0] }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $p->render_status }}">
                        @php $rl = ['pending'=>'Pendiente','rendering'=>'Generando…','done'=>'Lista','failed'=>'Error']; @endphp
                        {{ $rl[$p->render_status] ?? $p->render_status }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-{{ $p->status }}">
                        @php $sl = ['draft'=>'Borrador','review'=>'Revisión','published'=>'Publicado']; @endphp
                        {{ $sl[$p->status] ?? $p->status }}
                    </span>
                </td>
                <td style="color:var(--text-muted);font-size:.8rem;white-space:nowrap;">{{ $p->created_at->format('d M Y') }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                        <a href="{{ route('admin.facebook.show', $p) }}" class="btn btn-outline btn-sm">Editar</a>
                        @if($p->render_status === 'done')
                        <a href="{{ route('admin.facebook.download', $p) }}" class="btn btn-outline btn-sm" title="Descargar PNG">&#8659;</a>
                        @endif
                        <form method="POST" action="{{ route('admin.facebook.destroy', $p) }}" onsubmit="return confirm('¿Eliminar este post?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">&#215;</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted);font-size:.85rem;">
                    <div style="font-size:2.5rem;margin-bottom:.75rem;">&#128241;</div>
                    Aún no hay posts de Facebook.<br>
                    <a href="{{ route('admin.facebook.create') }}" style="color:var(--primary);font-weight:600;">Crear el primero →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($posts->hasPages())
<div style="margin-top:1rem;">{{ $posts->links() }}</div>
@endif
@endsection
