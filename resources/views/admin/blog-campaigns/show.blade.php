@extends('layouts.app-sidebar')
@section('title', 'Campaña: ' . $campaign->name)

@section('content')
@php
    $topics = collect($campaign->topics ?? []);
    $drafts = $posts->where('status', 'draft')->where('ai_generation_status', 'done');
@endphp

<div class="page-header">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin:0">{{ $campaign->name }}</h1>
        <p style="color:var(--text-muted);font-size:0.85rem;margin-top:0.25rem">
            <span class="badge {{ ['active'=>'badge-green','draft'=>'badge-yellow','paused'=>'','done'=>'badge-blue'][$campaign->status] ?? '' }}">{{ ['active'=>'Activa','draft'=>'Borrador','paused'=>'Pausada','done'=>'Terminada'][$campaign->status] ?? $campaign->status }}</span>
            · {{ $campaign->posts_per_week }}/semana a las {{ $campaign->publish_hour }}
            · {{ $topics->where('status','pending')->count() }} temas pendientes · colchón {{ $campaign->buffer }}
        </p>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
        <a href="{{ route('admin.blog-campaigns.index') }}" class="btn btn-outline">← Campañas</a>
        @if($campaign->status === 'draft')
        <form method="POST" action="{{ route('admin.blog-campaigns.activate', $campaign) }}">@csrf<button class="btn btn-primary">▶ Activar campaña</button></form>
        @else
        <form method="POST" action="{{ route('admin.blog-campaigns.pause', $campaign) }}">@csrf<button class="btn btn-outline">{{ $campaign->status === 'paused' ? '▶ Reanudar' : '⏸ Pausar' }}</button></form>
        @endif
        <form method="POST" action="{{ route('admin.blog-campaigns.produce', $campaign) }}">@csrf<button class="btn btn-outline" onclick="this.innerHTML='Generando (2-4 min)…';this.disabled=true;this.form.submit()">⚡ Producir siguiente</button></form>
    </div>
</div>

@if(session('success'))<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#065f46;font-size:0.85rem">{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1rem;color:#991b1b;font-size:0.85rem">{{ session('error') }}</div>@endif

{{-- ═══ COLA DE REVISIÓN — borradores esperando tu OK ═══ --}}
@if($drafts->count())
<div class="card" style="border-left:4px solid #f59e0b">
    <div class="card-header"><h3>📝 Esperan tu OK ({{ $drafts->count() }})</h3></div>
    <div class="card-body" style="padding:0">
        @foreach($drafts as $post)
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
            <div style="flex:1;min-width:260px">
                <p style="font-weight:700;margin:0">{{ $post->title }}</p>
                <p style="font-size:0.8rem;color:var(--text-muted);margin:0.25rem 0 0">
                    {{ $post->category?->name ?? 'sin categoría' }} · {{ $post->reading_time }} min · SEO {{ $post->seo_score }}/100
                    · imágenes: {{ collect($post->image_prompts ?? [])->filter(fn($v,$k) => str_starts_with($k,'path_'))->count() ?: (str_contains($post->body ?? '', '<figure') ? '✓' : 'pendientes') }}
                </p>
            </div>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap">
                <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-outline" style="padding:0.35rem 0.8rem;font-size:0.8rem" target="_blank">👁 Revisar</a>
                <form method="POST" action="{{ route('admin.blog-campaigns.approve-post', [$campaign, $post]) }}">@csrf
                    <button class="btn btn-primary" style="padding:0.35rem 0.8rem;font-size:0.8rem">✓ Aprobar y programar</button>
                </form>
                <form method="POST" action="{{ route('admin.blog-campaigns.discard-post', [$campaign, $post]) }}" onsubmit="const m=prompt('¿Por qué se descarta? (la IA aprende de esto)');if(m===null)return false;this.motivo.value=m">@csrf
                    <input type="hidden" name="motivo">
                    <button class="btn btn-outline" style="padding:0.35rem 0.8rem;font-size:0.8rem;color:#dc2626;border-color:#fca5a5">✕ Descartar</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ═══ CALENDARIO — programados y publicados ═══ --}}
@php $programados = $posts->whereIn('status', ['scheduled', 'published']); @endphp
@if($programados->count())
<div class="card">
    <div class="card-header"><h3>📅 Calendario ({{ $programados->count() }})</h3></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Fecha</th><th>Post</th><th>Estado</th><th>Vistas</th><th>Leads</th><th></th></tr></thead>
            <tbody>
                @foreach($programados->sortBy('published_at') as $post)
                <tr>
                    <td style="white-space:nowrap">{{ $post->published_at?->format('d/m H:i') ?? '—' }}</td>
                    <td>{{ $post->title }}</td>
                    <td><span class="badge {{ $post->status === 'published' ? 'badge-green' : 'badge-yellow' }}">{{ $post->status === 'published' ? 'Publicado' : 'Programado' }}</span></td>
                    <td>{{ number_format($post->views_count ?? 0) }}</td>
                    <td style="font-weight:700;color:{{ ($leadsPorPost[$post->id] ?? 0) > 0 ? '#059669' : 'var(--text-muted)' }}">{{ $leadsPorPost[$post->id] ?? 0 }}</td>
                    <td>
                        @if($post->status === 'published')
                        <a href="{{ url('/blog/' . $post->slug) }}" target="_blank" class="btn btn-outline" style="padding:0.25rem 0.6rem;font-size:0.75rem">Ver ↗</a>
                        @else
                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-outline" style="padding:0.25rem 0.6rem;font-size:0.75rem">Editar</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══ MAPA DE TEMAS ═══ --}}
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem">
        <h3>🗺 Mapa de temas ({{ $topics->count() }})</h3>
        <form method="POST" action="{{ route('admin.blog-campaigns.generate-map', $campaign) }}">@csrf
            <input type="hidden" name="count" value="{{ session('generate_map_count', 30) }}">
            <button class="btn btn-outline" style="font-size:0.8rem" onclick="this.innerHTML='Generando mapa (1-2 min)…';this.disabled=true;this.form.submit()">
                {{ $topics->count() ? '➕ Generar más temas' : '🤖 Generar mapa de temas' }}
            </button>
        </form>
    </div>
    <div class="card-body" style="padding:0">
        @forelse($topics as $i => $topic)
        <div style="padding:0.8rem 1.25rem;border-bottom:1px solid var(--border);display:flex;gap:1rem;align-items:flex-start;{{ ($topic['status'] ?? '') === 'discarded' ? 'opacity:0.45' : '' }}">
            <span style="font-size:0.7rem;font-weight:700;color:var(--text-muted);padding-top:0.2rem">{{ $i + 1 }}</span>
            <div style="flex:1;min-width:220px">
                <p style="font-weight:600;margin:0;font-size:0.9rem">{{ $topic['title'] }}</p>
                <p style="font-size:0.78rem;color:var(--text-muted);margin:0.2rem 0 0">
                    {{ $topic['categoria'] ?? '—' }} · {{ implode(', ', array_slice($topic['keywords'] ?? [], 0, 2)) }}
                    @if(($topic['status'] ?? '') === 'generated') · <strong style="color:#059669">generado</strong>
                    @elseif(($topic['status'] ?? '') === 'discarded') · descartado @endif
                </p>
            </div>
            @if(($topic['status'] ?? '') === 'pending')
            <form method="POST" action="{{ route('admin.blog-campaigns.discard-topic', $campaign) }}" onsubmit="const m=prompt('¿Por qué se descarta este tema? (opcional, la IA aprende)');if(m===null)return false;this.motivo.value=m">@csrf
                <input type="hidden" name="index" value="{{ $i }}">
                <input type="hidden" name="motivo">
                <button class="btn btn-outline" style="padding:0.25rem 0.6rem;font-size:0.75rem;color:#dc2626;border-color:#fca5a5">✕</button>
            </form>
            @endif
        </div>
        @empty
        <div style="padding:2rem;text-align:center;color:var(--text-muted)">Genera el mapa de temas para arrancar la campaña.</div>
        @endforelse
    </div>
</div>

{{-- ═══ MEMORIA EDITORIAL ═══ --}}
@if($campaign->lecciones)
<div class="card">
    <div class="card-header"><h3>🧠 Memoria editorial</h3></div>
    <div class="card-body">
        <p style="font-size:0.82rem;color:var(--text-muted);white-space:pre-line;margin:0">{{ $campaign->lecciones }}</p>
        <p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.75rem">Estas lecciones (motivos de descarte) se inyectan en cada generación futura — el generador aprende de tus decisiones.</p>
    </div>
</div>
@endif
@endsection
