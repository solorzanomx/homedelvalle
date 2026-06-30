@extends('layouts.app-sidebar')
@section('title', 'Historias — Redes Sociales')

@section('content')
<style>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem; }
.btn { display:inline-flex; align-items:center; gap:.4rem; padding:.5rem 1rem; border-radius:8px; font-size:.85rem; font-weight:600; cursor:pointer; border:1px solid transparent; text-decoration:none; transition:all .15s; }
.btn-primary { background:#1d4ed8; color:#fff; }
.btn-outline { background:#fff; color:#374151; border-color:#d1d5db; }
.btn-outline:hover { background:#f3f4f6; }
.btn-sm { padding:.35rem .7rem; font-size:.8rem; }
.card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; }
.stories-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:1rem; }
.story-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; transition:box-shadow .15s; }
.story-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
.story-thumb { aspect-ratio:9/16; background:#f3f4f6; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; }
.story-thumb img { width:100%; height:100%; object-fit:cover; }
.story-thumb-placeholder { color:#d1d5db; font-size:2rem; }
.story-badge { position:absolute; top:.4rem; right:.4rem; font-size:.65rem; font-weight:700; padding:.15rem .45rem; border-radius:999px; }
.badge-yellow { background:#fef9c3; color:#854d0e; }
.badge-blue   { background:#dbeafe; color:#1e40af; }
.badge-green  { background:#d1fae5; color:#065f46; }
.badge-red    { background:#fee2e2; color:#991b1b; }
.story-info { padding:.75rem; }
.story-title { font-size:.85rem; font-weight:700; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:.25rem; }
.story-meta  { font-size:.72rem; color:#6b7280; }
.story-actions { display:flex; gap:.35rem; padding:.5rem .75rem; border-top:1px solid #f3f4f6; }
.filters { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.filter-select { padding:.4rem .75rem; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; background:#fff; }
</style>

<div class="page-header">
    <div>
        <h2 style="font-size:1.35rem;font-weight:800;color:#0C1A2E;margin:0;">&#127775; Historias</h2>
        <p style="font-size:.83rem;color:#6b7280;margin:.25rem 0 0;">Gestión de Stories para Instagram y Facebook</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.social.calendar') }}" class="btn btn-outline btn-sm">&#128197; Calendario</a>
        <a href="{{ route('admin.social.stories.create') }}" class="btn btn-primary">+ Nueva Historia</a>
    </div>
</div>

<form method="GET" class="filters">
    <select name="platform" class="filter-select" onchange="this.form.submit()">
        <option value="">Todas las plataformas</option>
        <option value="instagram" {{ request('platform') === 'instagram' ? 'selected' : '' }}>Instagram</option>
        <option value="facebook"  {{ request('platform') === 'facebook'  ? 'selected' : '' }}>Facebook</option>
    </select>
    <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">Todos los estados</option>
        <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Borrador</option>
        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Programada</option>
        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicada</option>
        <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Fallida</option>
    </select>
</form>

@if($stories->isEmpty())
<div class="card" style="padding:3rem;text-align:center;color:#6b7280;">
    <p style="font-size:1.5rem;margin-bottom:.5rem;">&#127775;</p>
    <p style="font-weight:600;margin:0;">No hay historias aún.</p>
    <p style="font-size:.85rem;margin-top:.25rem;">Crea tu primera historia para Instagram o Facebook.</p>
    <a href="{{ route('admin.social.stories.create') }}" class="btn btn-primary" style="margin-top:1rem;">+ Nueva Historia</a>
</div>
@else
<div class="stories-grid">
    @foreach($stories as $story)
    <div class="story-card">
        <div class="story-thumb">
            @if($story->rendered_image_path)
            <img src="{{ Storage::disk('public')->url($story->rendered_image_path) }}" alt="{{ $story->headline }}">
            @elseif($story->background_image_path)
            <img src="{{ Storage::disk('public')->url($story->background_image_path) }}" alt="">
            @else
            <span class="story-thumb-placeholder">&#127775;</span>
            @endif
            <span class="story-badge badge-{{ $story->status_color }}">{{ $story->status_label }}</span>
        </div>
        <div class="story-info">
            <div class="story-title">{{ $story->headline ?? 'Sin título' }}</div>
            <div class="story-meta">
                {{ $story->platform_label }}
                @if($story->scheduled_at) · {{ $story->scheduled_at->format('d M Y H:i') }} @endif
            </div>
        </div>
        <div class="story-actions">
            <a href="{{ route('admin.social.stories.show', $story) }}" class="btn btn-outline btn-sm" style="flex:1;justify-content:center;">Editar</a>
            @if($story->rendered_image_path)
            <a href="{{ route('admin.social.stories.download', $story) }}" class="btn btn-outline btn-sm">&#8681;</a>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div style="margin-top:1.5rem;">
    {{ $stories->links() }}
</div>
@endif
@endsection
