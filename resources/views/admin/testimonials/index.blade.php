@extends('layouts.app-sidebar')
@section('title', 'Testimonios')

@section('styles')
<style>
.t-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
@media (max-width: 900px) { .t-grid { grid-template-columns: 1fr; } }
.t-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.t-card-body { padding: 1rem 1.25rem; }
.t-top { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.t-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1rem; flex-shrink: 0; overflow: hidden; }
.t-avatar img { width: 100%; height: 100%; object-fit: cover; }
.t-name { font-weight: 600; font-size: 0.9rem; }
.t-role { font-size: 0.75rem; color: var(--text-muted); }
.t-stars { color: #f59e0b; font-size: 0.8rem; letter-spacing: 1px; }
.t-content { font-size: 0.85rem; color: var(--text); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.t-thumb { display: block; position: relative; margin-top: 0.6rem; border-radius: 6px; overflow: hidden; max-width: 200px; }
.t-thumb img { width: 100%; height: auto; display: block; }
.t-thumb-play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.35); }
.t-thumb-play svg { width: 32px; height: 32px; fill: #fff; filter: drop-shadow(0 1px 3px rgba(0,0,0,0.4)); }
.t-footer { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 1.25rem; border-top: 1px solid var(--border); }
.t-badges { display: flex; gap: 0.3rem; flex-wrap: wrap; }
.t-badge { display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.68rem; padding: 0.12rem 0.45rem; border-radius: 10px; }
.t-badge-video { background: rgba(239,68,68,0.08); color: #dc2626; }
.t-badge-feat { background: rgba(245,158,11,0.1); color: #d97706; }
.t-badge-off { background: rgba(100,116,139,0.08); color: #64748b; }
.t-badge-loc { background: rgba(59,130,246,0.08); color: #3b82f6; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Testimonios</h2>
        <p class="text-muted">{{ $testimonials->count() }} testimonios</p>
    </div>
    <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary">+ Nuevo</a>
</div>

@if($testimonials->isEmpty())
    <div class="card">
        <div class="card-body" style="text-align:center; padding:3rem;">
            <p class="text-muted">No hay testimonios todavia.</p>
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary" style="margin-top:1rem;">Crear primer testimonio</a>
        </div>
    </div>
@else
    <div class="t-grid">
        @foreach($testimonials as $t)
        <div class="t-card">
            <div class="t-card-body">
                <div class="t-top">
                    <div class="t-avatar">
                        @if($t->avatar)
                            <img src="{{ Storage::url($t->avatar) }}" alt="{{ $t->name }}">
                        @else
                            {{ strtoupper(substr($t->name, 0, 1)) }}
                        @endif
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="t-name">{{ $t->name }}</div>
                        @if($t->role)<div class="t-role">{{ $t->role }}</div>@endif
                    </div>
                    <div class="t-stars">
                        @for($i = 1; $i <= 5; $i++)
                            {{ $i <= $t->rating ? '★' : '☆' }}
                        @endfor
                    </div>
                </div>
                @if($t->content)
                    <div class="t-content">"{{ $t->content }}"</div>
                @endif
                @if($t->type === 'video' && $t->youtube_thumbnail)
                    <a href="{{ $t->video_url }}" target="_blank" class="t-thumb">
                        <img src="{{ $t->youtube_thumbnail }}" alt="Video">
                        <span class="t-thumb-play"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
                    </a>
                @elseif($t->type === 'video' && $t->video_url)
                    <div style="margin-top:0.4rem;">
                        <a href="{{ $t->video_url }}" target="_blank" style="font-size:0.78rem; color:var(--primary);">Ver video &rarr;</a>
                    </div>
                @endif
            </div>
            <div class="t-footer">
                <div class="t-badges">
                    @if($t->type === 'video')
                        <span class="t-badge t-badge-video">Video</span>
                    @endif
                    @if($t->is_featured)
                        <span class="t-badge t-badge-feat">Destacado</span>
                    @endif
                    @if(!$t->is_active)
                        <span class="t-badge t-badge-off">Inactivo</span>
                    @endif
                    @if($t->location)
                        <span class="t-badge t-badge-loc">{{ $t->location }}</span>
                    @endif
                </div>
                <div class="action-btns">
                    <a href="{{ route('admin.testimonials.edit', $t) }}" class="btn btn-outline btn-sm">Editar</a>
                    <form action="{{ route('admin.testimonials.destroy', $t) }}" method="POST" style="margin:0;" onsubmit="return confirm('¿Eliminar este testimonio?')">
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
