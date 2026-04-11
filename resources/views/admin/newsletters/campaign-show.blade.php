@extends('layouts.app-sidebar')
@section('title', $campaign->subject)

@section('styles')
<style>
.show-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 1.25rem; }
@media (max-width: 768px) { .show-stats { grid-template-columns: repeat(2, 1fr); } }
.show-stat { text-align: center; padding: 1rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); }
.show-stat-val { font-size: 1.3rem; font-weight: 700; }
.show-stat-lbl { font-size: 0.72rem; color: var(--text-muted); }
.preview-frame { width: 100%; border: 1px solid var(--border); border-radius: var(--radius); min-height: 400px; background: #fff; }
</style>
@endsection

@section('content')
<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('admin.newsletters.campaigns') }}" style="font-size:0.82rem; color:var(--text-muted);">Campanas</a>
    <span style="font-size:0.72rem; color:var(--text-muted);">/</span>
    <span style="font-size:0.82rem;">{{ Str::limit($campaign->subject, 50) }}</span>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom:1rem;">{{ session('error') }}</div>
@endif

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem; flex-wrap:wrap; gap:0.5rem;">
    <div style="display:flex; align-items:center; gap:0.75rem;">
        <h2 style="font-size:1.15rem; font-weight:700;">{{ $campaign->subject }}</h2>
        <span class="badge badge-{{ match($campaign->status) { 'draft'=>'yellow', 'sending'=>'blue', 'sent'=>'green', default=>'red' } }}">
            {{ match($campaign->status) { 'draft'=>'Borrador', 'sending'=>'Enviando', 'sent'=>'Enviada', default=>'Fallida' } }}
        </span>
    </div>
    <div style="display:flex; gap:0.4rem;">
        @if($campaign->status === 'draft')
        <a href="{{ route('admin.newsletters.campaigns.edit', $campaign) }}" class="btn btn-sm btn-outline">Editar</a>
        <form method="POST" action="{{ route('admin.newsletters.campaigns.send', $campaign) }}" onsubmit="return confirm('Esto enviara el newsletter a {{ $activeSubscribers }} suscriptores activos.\n\nPuede tomar varios minutos. No cierres esta ventana.\n\nContinuar?')">
            @csrf
            <button class="btn btn-sm btn-primary">Enviar a {{ $activeSubscribers }} suscriptores</button>
        </form>
        @endif
    </div>
</div>

<div class="show-stats">
    <div class="show-stat"><div class="show-stat-val">{{ match($campaign->status) { 'draft'=>'Borrador', 'sending'=>'Enviando...', 'sent'=>'Enviada', default=>'Fallida' } }}</div><div class="show-stat-lbl">Estado</div></div>
    <div class="show-stat"><div class="show-stat-val">{{ $campaign->sent_to_count }}</div><div class="show-stat-lbl">Enviados</div></div>
    <div class="show-stat"><div class="show-stat-val">{{ $campaign->failed_count }}</div><div class="show-stat-lbl">Fallidos</div></div>
    <div class="show-stat"><div class="show-stat-val">{{ $campaign->sent_at?->format('d/m H:i') ?? '-' }}</div><div class="show-stat-lbl">Fecha envio</div></div>
</div>

@if($campaign->status === 'sending')
<div class="alert alert-info" style="margin-bottom:1rem;">El envio puede seguir en proceso. Recarga la pagina para ver el estado actualizado.</div>
@endif

{{-- Preview --}}
<div style="background:var(--card); border:1px solid var(--border); border-radius:10px; overflow:hidden;">
    <div style="padding:0.8rem 1.25rem; border-bottom:1px solid var(--border); font-weight:600; font-size:0.88rem; display:flex; justify-content:space-between; align-items:center;">
        Vista previa
        <span style="font-size:0.75rem; color:var(--text-muted);">Creado por {{ $campaign->creator?->name ?? 'Sistema' }} &middot; {{ $campaign->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div style="padding:0;">
        <iframe src="{{ route('admin.newsletters.campaigns.preview', $campaign) }}" class="preview-frame" style="border:none; width:100;"></iframe>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-resize iframe to content height
document.querySelector('.preview-frame').addEventListener('load', function() {
    try {
        this.style.height = this.contentWindow.document.body.scrollHeight + 40 + 'px';
    } catch(e) {}
});
</script>
@endsection
