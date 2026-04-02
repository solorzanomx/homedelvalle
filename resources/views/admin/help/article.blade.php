@extends('layouts.app-sidebar')
@section('title', $article->title)

@section('styles')
<style>
.art-layout { display: grid; grid-template-columns: 1fr 260px; gap: 1.5rem; align-items: start; max-width: 960px; }
@media (max-width: 900px) { .art-layout { grid-template-columns: 1fr; } }

.art-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.art-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); }
.art-header h1 { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.25rem; }
.art-meta { font-size: 0.78rem; color: var(--text-muted); display: flex; gap: 0.75rem; flex-wrap: wrap; }
.art-body { padding: 1.5rem; line-height: 1.75; font-size: 0.92rem; }
.art-body h1 { font-size: 1.15rem; font-weight: 700; margin: 1.5rem 0 0.5rem; }
.art-body h2 { font-size: 1.05rem; font-weight: 700; margin: 1.25rem 0 0.5rem; }
.art-body h3 { font-size: 0.95rem; font-weight: 600; margin: 1rem 0 0.4rem; }
.art-body p { margin-bottom: 0.75rem; }
.art-body ul, .art-body ol { margin: 0.5rem 0 0.75rem 1.25rem; }
.art-body li { margin-bottom: 0.25rem; }
.art-body table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; font-size: 0.85rem; }
.art-body th { background: var(--bg); font-weight: 600; text-align: left; padding: 0.5rem 0.75rem; border: 1px solid var(--border); }
.art-body td { padding: 0.5rem 0.75rem; border: 1px solid var(--border); }
.art-body strong { font-weight: 600; }
.art-body code { background: var(--bg); padding: 1px 5px; border-radius: 3px; font-size: 0.85em; }

/* Sidebar */
.art-sidebar-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 1rem; }
.art-sidebar-header { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.82rem; }
.art-sidebar-body { padding: 0.75rem 1rem; }
.related-link {
    display: block; padding: 0.35rem 0; font-size: 0.82rem;
    color: var(--text); text-decoration: none; transition: color 0.15s;
}
.related-link:hover { color: var(--primary); }
</style>
@endsection

@section('content')
<div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
    <a href="{{ route('help.index') }}" style="font-size: 0.82rem; color: var(--text-muted);">Centro de Ayuda</a>
    <span style="color: var(--text-muted); font-size: 0.72rem;">/</span>
    <span style="font-size: 0.82rem; color: var(--text-muted);">{{ $article->category->name }}</span>
    <span style="color: var(--text-muted); font-size: 0.72rem;">/</span>
    <span style="font-size: 0.82rem; color: var(--text);">{{ Str::limit($article->title, 40) }}</span>
</div>

<div class="art-layout">
    <div class="art-card">
        <div class="art-header">
            <h1>{{ $article->title }}</h1>
            <div class="art-meta">
                <span>{{ $article->category->icon }} {{ $article->category->name }}</span>
                <span>{{ $article->view_count }} vistas</span>
                <span>Actualizado {{ $article->updated_at->diffForHumans() }}</span>
            </div>
        </div>
        <div class="art-body">{!! Str::markdown($article->content) !!}</div>
    </div>

    <div>
        @if($relatedArticles->count())
        <div class="art-sidebar-card">
            <div class="art-sidebar-header">Articulos relacionados</div>
            <div class="art-sidebar-body">
                @foreach($relatedArticles as $rel)
                <a href="{{ route('help.article', $rel) }}" class="related-link">{{ $rel->title }}</a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="art-sidebar-card">
            <div class="art-sidebar-header">&#128161; Necesitas mas ayuda?</div>
            <div class="art-sidebar-body">
                <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.5rem;">Si no encontraste lo que buscas, contacta al administrador del sistema.</p>
                <a href="{{ route('help.index') }}" class="btn btn-sm btn-outline" style="width:100%; justify-content:center;">Volver al centro de ayuda</a>
            </div>
        </div>
    </div>
</div>
@endsection
