@extends('layouts.app-sidebar')
@section('title', $document->title)

@section('styles')
<style>
.legal-tabs { display: flex; border-bottom: 2px solid var(--border); margin-bottom: 0; }
.legal-tab {
    padding: 0.7rem 1.2rem; font-size: 0.85rem; font-weight: 500; cursor: pointer;
    border-bottom: 2px solid transparent; margin-bottom: -2px; color: var(--text-muted);
    background: none; border-top: none; border-left: none; border-right: none; font-family: inherit;
    transition: all 0.15s;
}
.legal-tab:hover { color: var(--text); }
.legal-tab.active { color: var(--primary); border-bottom-color: var(--primary); }
.tab-content { display: none; }
.tab-content.active { display: block; }
.legal-preview {
    padding: 2rem; font-size: 0.92rem; line-height: 1.8; color: var(--text);
    max-width: 100%; overflow-wrap: break-word;
}
.legal-preview h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; }
.legal-preview h2 { font-size: 1.25rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.75rem; }
.legal-preview h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; }
.legal-preview p { margin-bottom: 0.75rem; }
.legal-preview ul, .legal-preview ol { margin-bottom: 0.75rem; padding-left: 1.5rem; }
.legal-preview li { margin-bottom: 0.25rem; }
.legal-preview table { border-collapse: collapse; width: 100%; margin-bottom: 1rem; }
.legal-preview table th, .legal-preview table td { border: 1px solid var(--border); padding: 0.5rem 0.75rem; font-size: 0.85rem; }
.legal-preview table th { background: var(--bg); font-weight: 600; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2 style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            {{ $document->title }}
            @php
                $typeBadges = [
                    'aviso_privacidad' => 'badge-blue',
                    'terminos_condiciones' => 'badge-purple',
                    'contrato' => 'badge-yellow',
                    'otro' => 'badge-green',
                ];
                $badgeClass = $typeBadges[$document->type] ?? 'badge-blue';
            @endphp
            <span class="badge {{ $badgeClass }}">{{ \App\Models\LegalDocument::TYPES[$document->type] ?? $document->type }}</span>
            @if($document->status === 'published')
                <span class="badge badge-green">Publicado</span>
            @else
                <span class="badge badge-yellow">Borrador</span>
            @endif
            @if($document->is_public)
                <span class="badge badge-blue">Publico</span>
            @else
                <span class="badge badge-red">Privado</span>
            @endif
        </h2>
        @if($document->meta_description)
            <p class="text-muted" style="margin-top:0.25rem;">{{ $document->meta_description }}</p>
        @endif
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="{{ route('admin.legal.edit', $document) }}" class="btn btn-primary">Editar</a>
        @if($document->is_public && $document->status === 'published')
            <a href="{{ route('legal.public', $document->slug) }}" class="btn btn-outline" target="_blank">Ver pagina publica</a>
        @endif
        <a href="{{ route('admin.legal.index') }}" class="btn btn-outline">&#8592; Volver</a>
    </div>
</div>

{{-- Stats Row --}}
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#128196;</div>
        <div>
            <div class="stat-value">v{{ $document->currentVersion?->version_number ?? '0' }}</div>
            <div class="stat-label">Version Actual</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-purple">&#128221;</div>
        <div>
            <div class="stat-value">{{ $document->versions->count() }}</div>
            <div class="stat-label">Total Versiones</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-green">&#9745;</div>
        <div>
            <div class="stat-value">{{ $document->acceptances_count ?? $document->acceptances()->count() }}</div>
            <div class="stat-label">Total Aceptaciones</div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="card">
    <div class="legal-tabs">
        <button class="legal-tab active" onclick="switchTab('contenido', this)">Contenido</button>
        <button class="legal-tab" onclick="switchTab('versiones', this)">Versiones ({{ $document->versions->count() }})</button>
        <button class="legal-tab" onclick="switchTab('aceptaciones', this)">Aceptaciones ({{ $document->acceptances_count ?? $document->acceptances()->count() }})</button>
    </div>

    {{-- Tab: Contenido --}}
    <div class="tab-content active" id="tab-contenido">
        <div class="legal-preview">
            @if($document->currentVersion)
                {!! $document->currentVersion->content !!}
            @else
                <p class="text-muted text-center" style="padding:2rem;">Este documento no tiene contenido aun.</p>
            @endif
        </div>
    </div>

    {{-- Tab: Versiones --}}
    <div class="tab-content" id="tab-versiones">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Notas del cambio</th>
                        <th>Creado por</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($document->versions->sortByDesc('version_number') as $version)
                    <tr>
                        <td style="font-weight:500;">v{{ $version->version_number }}</td>
                        <td class="text-muted">{{ $version->change_notes ?? '-' }}</td>
                        <td class="text-muted">{{ $version->creator?->name ?? 'Sistema' }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $version->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($version->is_active)
                                <span class="badge badge-green">Activa</span>
                            @else
                                <span class="badge badge-yellow">Anterior</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding:2rem;">Sin versiones registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab: Aceptaciones --}}
    <div class="tab-content" id="tab-aceptaciones">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>IP</th>
                        <th>Fecha</th>
                        <th>Contexto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAcceptances as $acceptance)
                    <tr>
                        <td style="font-weight:500;">{{ $acceptance->email }}</td>
                        <td class="text-muted" style="font-size:0.82rem;">{{ $acceptance->ip_address }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $acceptance->accepted_at?->format('d/m/Y H:i') ?? $acceptance->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="badge badge-blue">{{ $acceptance->context ?? 'web' }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted" style="padding:2rem;">Sin aceptaciones registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(($document->acceptances_count ?? $document->acceptances()->count()) > 20)
            <div style="padding:1rem; text-align:center; border-top:1px solid var(--border);">
                <a href="{{ route('admin.legal.document.acceptances', $document) }}" class="btn btn-sm btn-outline">Ver todas las aceptaciones</a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(function(el) { el.classList.remove('active'); });
    document.querySelectorAll('.legal-tab').forEach(function(el) { el.classList.remove('active'); });
    document.getElementById('tab-' + tabId).classList.add('active');
    btn.classList.add('active');
}
</script>
@endsection
