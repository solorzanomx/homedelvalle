@extends('layouts.app-sidebar')
@section('title', 'Colaboradores')

@section('styles')
<style>
.c-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
@media (max-width: 900px) { .c-grid { grid-template-columns: 1fr; } }
.c-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.c-card-body { padding: 1rem 1.25rem; }
.c-top { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.c-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1rem; flex-shrink: 0; overflow: hidden; }
.c-avatar img { width: 100%; height: 100%; object-fit: cover; }
.c-name { font-weight: 600; font-size: 0.9rem; }
.c-role { font-size: 0.75rem; color: var(--text-muted); }
.c-bio { font-size: 0.82rem; color: var(--text); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.c-badge { display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.68rem; padding: 0.12rem 0.5rem; border-radius: 10px; font-weight: 600; }
.c-badge-pending { background: rgba(217,119,6,0.1); color: #d97706; }
.c-badge-authorized { background: rgba(21,128,61,0.1); color: #15803d; }
.c-badge-declined { background: rgba(185,28,28,0.1); color: #b91c1c; }
.c-badge-off { background: rgba(100,116,139,0.08); color: #64748b; }
.c-footer { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 1.25rem; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 0.5rem; }
.c-link-copy { font-size: 0.72rem; color: var(--primary); background: none; border: none; cursor: pointer; padding: 0; text-decoration: underline; }
.c-link-sent { font-size: 0.68rem; color: var(--text-muted); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Colaboradores</h2>
        <p class="text-muted">{{ $collaborators->count() }} colaboradores &middot; se muestran en /nosotros solo los autorizados y activos</p>
    </div>
    <a href="{{ route('admin.collaborators.create') }}" class="btn btn-primary">+ Nuevo colaborador</a>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

@if($collaborators->isEmpty())
    <div class="card">
        <div class="card-body" style="text-align:center; padding:3rem;">
            <p class="text-muted">Aún no hay colaboradores.</p>
            <a href="{{ route('admin.collaborators.create') }}" class="btn btn-primary" style="margin-top:1rem;">Agregar el primero</a>
        </div>
    </div>
@else
    <div class="c-grid">
        @foreach($collaborators as $c)
        <div class="c-card">
            <div class="c-card-body">
                <div class="c-top">
                    <div class="c-avatar">
                        @if($c->photo_path)
                            <img src="{{ Storage::url($c->photo_path) }}" alt="{{ $c->name }}">
                        @else
                            {{ strtoupper(substr($c->name, 0, 1)) }}
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="c-name">{{ $c->name }}</div>
                        <div class="c-role">{{ $c->role }}</div>
                    </div>
                    @if($c->consent_status === 'pending')
                        <span class="c-badge c-badge-pending">Pendiente</span>
                    @elseif($c->consent_status === 'authorized')
                        <span class="c-badge c-badge-authorized">Autorizado</span>
                    @else
                        <span class="c-badge c-badge-declined">No autorizó</span>
                    @endif
                </div>
                @if($c->bio)
                    <div class="c-bio">{{ $c->bio }}</div>
                @endif
                @if($c->consent_status === 'declined' && $c->decline_note)
                    <div class="c-bio" style="color:#b91c1c; margin-top:0.4rem;">Nota: {{ $c->decline_note }}</div>
                @endif
            </div>
            <div class="c-footer">
                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                    @if(!$c->is_active)
                        <span class="c-badge c-badge-off">Oculto</span>
                    @endif
                    @if($c->consent_status === 'pending')
                        <button type="button" class="c-link-copy" onclick="copyConsentLink('{{ route('collaborator.consent.show', $c->consent_token) }}', {{ $c->id }})">Copiar link de autorización</button>
                        @if($c->link_sent_at)
                            <span class="c-link-sent">enviado {{ $c->link_sent_at->diffForHumans() }}</span>
                        @endif
                    @endif
                </div>
                <div class="action-btns">
                    <a href="{{ route('admin.collaborators.edit', $c) }}" class="btn btn-outline btn-sm">Editar</a>
                    <form action="{{ route('admin.collaborators.destroy', $c) }}" method="POST" style="margin:0;" onsubmit="return confirm('¿Eliminar a este colaborador?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm" style="color:var(--danger); border:1px solid var(--border); background:none;">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif
@endsection

@section('scripts')
<script>
function copyConsentLink(url, id) {
    navigator.clipboard.writeText(url).then(function () {
        fetch('/admin/collaborators/' + id + '/mark-link-sent', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        }).then(() => location.reload());
    });
}
</script>
@endsection
