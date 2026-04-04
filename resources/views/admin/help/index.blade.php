@extends('layouts.app-sidebar')
@section('title', 'Centro de Ayuda')

@section('styles')
<style>
.help-header { margin-bottom: 1.5rem; }
.help-header h2 { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.25rem; }
.help-header p { color: var(--text-muted); font-size: 0.88rem; }

/* Search */
.help-search {
    position: relative; max-width: 480px; margin-bottom: 1.5rem;
}
.help-search input {
    width: 100%; padding: 0.65rem 1rem 0.65rem 2.4rem; border: 1px solid var(--border);
    border-radius: 20px; font-size: 0.88rem; background: var(--card);
}
.help-search input:focus { outline: none; border-color: var(--primary); }
.help-search-icon { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.9rem; }

/* Onboarding */
.onboarding-card {
    background: linear-gradient(135deg, #3B82C4 0%, #1E3A5F 100%); color: #fff;
    border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem;
}
.onboarding-title { font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem; }
.onboarding-sub { font-size: 0.82rem; opacity: 0.85; margin-bottom: 0.75rem; }
.onboarding-bar { background: rgba(255,255,255,0.25); border-radius: 6px; height: 8px; overflow: hidden; margin-bottom: 0.75rem; }
.onboarding-fill { background: #fff; height: 100%; border-radius: 6px; transition: width 0.3s; }
.onboarding-steps { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 0.5rem; }
.onboarding-step {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.6rem; border-radius: 8px;
    background: rgba(255,255,255,0.12); font-size: 0.78rem; transition: background 0.15s;
}
.onboarding-step.done { background: rgba(255,255,255,0.25); }
.onboarding-check { width: 18px; height: 18px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; flex-shrink: 0; }
.onboarding-step.done .onboarding-check { background: #fff; color: #3B82C4; border-color: #fff; }

/* Categories grid */
.cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
.cat-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1.25rem; transition: border-color 0.15s, box-shadow 0.15s;
}
.cat-card:hover { border-color: var(--primary); box-shadow: 0 2px 8px rgba(59,130,196,0.08); }
.cat-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
.cat-name { font-weight: 700; font-size: 0.95rem; margin-bottom: 0.35rem; }
.cat-count { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; }

.article-link {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0; font-size: 0.85rem;
    color: var(--text); text-decoration: none; transition: color 0.15s;
}
.article-link:hover { color: var(--primary); }
.article-link::before { content: '→'; color: var(--text-muted); font-size: 0.75rem; }
</style>
@endsection

@section('content')
<div class="help-header">
    <h2>Centro de Ayuda</h2>
    <p>Aprende a usar el CRM para captar mas propiedades y cerrar mas operaciones.</p>
</div>

<div class="help-search">
    <span class="help-search-icon">&#128269;</span>
    <input type="text" id="helpSearch" placeholder="Buscar articulos..." oninput="filterArticles(this.value)">
</div>

{{-- Onboarding --}}
@if(!$onboarding->is_completed)
<div class="onboarding-card">
    <div class="onboarding-title">&#128640; Tu progreso de inicio</div>
    <div class="onboarding-sub">Completa estos pasos para dominar el CRM</div>
    <div class="onboarding-bar"><div class="onboarding-fill" style="width:{{ $onboarding->getProgressPercent() }}%;"></div></div>
    <div class="onboarding-steps">
        @foreach(\App\Models\HelpOnboardingProgress::STEPS as $key => $step)
        <div class="onboarding-step {{ $onboarding->isStepCompleted($key) ? 'done' : '' }}" data-step="{{ $key }}">
            <div class="onboarding-check">{{ $onboarding->isStepCompleted($key) ? '✓' : '' }}</div>
            <span>{{ $step['icon'] }} {{ $step['label'] }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Categories --}}
<div class="cat-grid" id="catGrid">
    @foreach($categories as $cat)
    <div class="cat-card" data-cat="{{ $cat->slug }}">
        <div class="cat-icon">{{ $cat->icon }}</div>
        <div class="cat-name">{{ $cat->name }}</div>
        <div class="cat-count">{{ $cat->publishedArticles->count() }} articulos</div>
        @foreach($cat->publishedArticles as $article)
        <a href="{{ route('help.article', $article) }}" class="article-link" data-title="{{ mb_strtolower($article->title) }}">
            {{ $article->title }}
        </a>
        @endforeach
    </div>
    @endforeach
</div>
@endsection

@section('scripts')
<script>
function filterArticles(q) {
    q = q.toLowerCase().trim();
    document.querySelectorAll('.cat-card').forEach(function(card) {
        var links = card.querySelectorAll('.article-link');
        var visible = 0;
        links.forEach(function(link) {
            var match = !q || link.dataset.title.indexOf(q) !== -1;
            link.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        card.style.display = (!q || visible > 0) ? '' : 'none';
    });
}
</script>
@endsection
