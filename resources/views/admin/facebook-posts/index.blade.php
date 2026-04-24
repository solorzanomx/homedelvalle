@extends('layouts.app-sidebar')
@section('title', 'Posts Facebook')

@section('content')
<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.55rem 1.1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:none; text-decoration:none; }
.btn-primary { background:var(--primary); color:#fff; }
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
</style>

<div class="page-header">
    <div>
        <h2 style="margin:0;">&#128241; Posts Facebook</h2>
        <p style="font-size:.83rem;color:var(--text-muted);margin-top:.2rem;">Genera imágenes 1200×628 para Facebook con Claude + Browsershot</p>
    </div>
    <a href="{{ route('admin.facebook.create') }}" class="btn btn-primary">+ Nuevo post</a>
</div>

@if(session('success'))
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:#15803d;">{{ session('success') }}</div>
@endif

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Título</th>
                <th>Template</th>
                <th>Estado imagen</th>
                <th>Estado post</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $p)
            <tr>
                <td style="font-weight:600;max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $p->title }}</td>
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
                <td style="color:var(--text-muted);font-size:.8rem;">{{ $p->created_at->format('d M Y') }}</td>
                <td>
                    <div style="display:flex;gap:.4rem;">
                        <a href="{{ route('admin.facebook.show', $p) }}" class="btn btn-outline btn-sm">Editar</a>
                        @if($p->render_status === 'done')
                        <a href="{{ route('admin.facebook.download', $p) }}" class="btn btn-outline btn-sm">&#8659; PNG</a>
                        @endif
                        <form method="POST" action="{{ route('admin.facebook.destroy', $p) }}" onsubmit="return confirm('¿Eliminar este post?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">&#215;</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);font-size:.85rem;">
                    Aún no hay posts. <a href="{{ route('admin.facebook.create') }}" style="color:var(--primary);">Crear el primero</a>
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
