@extends('layouts.app-sidebar')
@section('title', 'Campanas Newsletter')

@section('styles')
<style>
.camp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1rem; }
.camp-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; transition: border-color 0.15s; }
.camp-card:hover { border-color: var(--primary); }
.camp-bar { height: 4px; }
.camp-bar.draft { background: #f59e0b; }
.camp-bar.sending { background: #3b82f6; }
.camp-bar.sent { background: var(--success); }
.camp-bar.failed { background: var(--danger); }
.camp-body { padding: 1.25rem; }
.camp-subject { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.4rem; }
.camp-meta { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.75rem; }
.camp-stats { display: flex; gap: 1.25rem; margin-bottom: 0.75rem; }
.camp-stat-val { font-size: 1.1rem; font-weight: 700; }
.camp-stat-lbl { font-size: 0.68rem; color: var(--text-muted); }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Campanas Newsletter</h2>
        <p class="text-muted">Crea y envia newsletters a tus suscriptores.</p>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.newsletters.subscribers') }}" class="btn btn-outline">Suscriptores</a>
        <a href="{{ route('admin.newsletters.campaigns.create') }}" class="btn btn-primary">+ Nueva Campana</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div class="camp-grid">
    @foreach($campaigns as $camp)
    <div class="camp-card">
        <div class="camp-bar {{ $camp->status }}"></div>
        <div class="camp-body">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.4rem;">
                <div class="camp-subject">{{ $camp->subject }}</div>
                <span class="badge badge-{{ match($camp->status) { 'draft'=>'yellow', 'sending'=>'blue', 'sent'=>'green', default=>'red' } }}">
                    {{ match($camp->status) { 'draft'=>'Borrador', 'sending'=>'Enviando', 'sent'=>'Enviada', default=>'Fallida' } }}
                </span>
            </div>
            <div class="camp-meta">
                Por {{ $camp->creator?->name ?? 'Sistema' }} &middot; {{ $camp->created_at->format('d/m/Y') }}
                @if($camp->sent_at) &middot; Enviado {{ $camp->sent_at->format('d/m/Y H:i') }} @endif
            </div>
            @if($camp->status === 'sent')
            <div class="camp-stats">
                <div><div class="camp-stat-val">{{ $camp->sent_to_count }}</div><div class="camp-stat-lbl">Enviados</div></div>
                <div><div class="camp-stat-val">{{ $camp->failed_count }}</div><div class="camp-stat-lbl">Fallidos</div></div>
            </div>
            @endif
            <div style="display:flex; gap:0.4rem;">
                <a href="{{ route('admin.newsletters.campaigns.show', $camp) }}" class="btn btn-sm btn-outline">Ver</a>
                @if($camp->status === 'draft')
                <a href="{{ route('admin.newsletters.campaigns.edit', $camp) }}" class="btn btn-sm btn-outline">Editar</a>
                @endif
                <form method="POST" action="{{ route('admin.newsletters.campaigns.destroy', $camp) }}" onsubmit="return confirm('Eliminar campana?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if($campaigns->isEmpty())
<div style="text-align:center; padding:4rem; color:var(--text-muted);">
    <div style="font-size:2.5rem; margin-bottom:0.5rem; opacity:0.3;">&#9993;</div>
    <p>No hay campanas. Crea tu primera para enviar un newsletter.</p>
</div>
@endif

@if($campaigns->hasPages())
<div style="margin-top:1rem; display:flex; justify-content:center;">{{ $campaigns->links() }}</div>
@endif
@endsection
