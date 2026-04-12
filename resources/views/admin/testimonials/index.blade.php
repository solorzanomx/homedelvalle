@extends('layouts.app-sidebar')
@section('title', 'Testimonios')

@section('styles')
<style>
.t-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.25rem; }
.t-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.t-card-body { padding: 1rem 1.25rem; }
.t-top { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.t-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1rem; flex-shrink: 0; overflow: hidden; }
.t-avatar img { width: 100%; height: 100%; object-fit: cover; }
.t-name { font-weight: 600; font-size: 0.9rem; }
.t-role { font-size: 0.75rem; color: var(--text-muted); }
.t-stars { color: #f59e0b; font-size: 0.8rem; letter-spacing: 1px; }
.t-content { font-size: 0.85rem; color: var(--text); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
.t-footer { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 1.25rem; border-top: 1px solid var(--border); }
.t-badges { display: flex; gap: 0.3rem; }
.t-video-tag { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.7rem; padding: 0.15rem 0.5rem; background: rgba(239,68,68,0.08); color: #dc2626; border-radius: 10px; }
.t-featured-tag { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.7rem; padding: 0.15rem 0.5rem; background: rgba(245,158,11,0.1); color: #d97706; border-radius: 10px; }
.t-inactive-tag { display: inline-flex; font-size: 0.7rem; padding: 0.15rem 0.5rem; background: rgba(100,116,139,0.08); color: #64748b; border-radius: 10px; }
.t-location-tag { display: inline-flex; font-size: 0.7rem; padding: 0.15rem 0.5rem; background: rgba(59,130,246,0.08); color: #3b82f6; border-radius: 10px; }
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
                    <div>
                        <div class="t-name">{{ $t->name }}</div>
                        @if($t->role)<div class="t-role">{{ $t->role }}</div>@endif
                    </div>
                    <div style="margin-left:auto;">
                        <div class="t-stars">
                            @for($i = 1; $i <= 5; $i++)
                                {{ $i <= $t->rating ? '★' : '☆' }}
                            @endfor
                        </div>
                    </div>
                </div>
                @if($t->content)
                    <div class="t-content">"{{ $t->content }}"</div>
                @endif
                @if($t->type === 'video' && $t->video_url)
                    <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.4rem; display:flex; align-items:center; gap:0.3rem;">
                        <x-icon name="circle-play" class="w-3.5 h-3.5" /> <a href="{{ $t->video_url }}" target="_blank" style="color:var(--primary); text-decoration:underline;">Ver video</a>
                    </div>
                @endif
            </div>
            <div class="t-footer">
                <div class="t-badges">
                    @if($t->type === 'video')
                        <span class="t-video-tag"><x-icon name="circle-play" class="w-3 h-3" /> Video</span>
                    @endif
                    @if($t->is_featured)
                        <span class="t-featured-tag"><x-icon name="star" class="w-3 h-3" /> Destacado</span>
                    @endif
                    @if(!$t->is_active)
                        <span class="t-inactive-tag">Inactivo</span>
                    @endif
                    @if($t->location)
                        <span class="t-location-tag">{{ $t->location }}</span>
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
