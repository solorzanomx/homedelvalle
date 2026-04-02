@extends('layouts.app-sidebar')
@section('title', 'Correo: ' . $email->subject)

@section('content')
<div class="page-header">
    <div>
        <h2>{{ $email->subject }}</h2>
        <p class="text-muted">Enviado a {{ $email->client->name }} &lt;{{ $email->client->email }}&gt;</p>
    </div>
    <a href="{{ route('clients.show', $email->client) }}" class="btn btn-outline">&#8592; Volver al perfil</a>
</div>

<div class="card" style="max-width:800px;">
    <div class="card-header">
        <div>
            <span style="font-weight:600;">De:</span> {{ $email->user->name ?? 'Sistema' }}
            &middot;
            <span style="font-weight:600;">Para:</span> {{ $email->client->email }}
        </div>
        <div style="display:flex; align-items:center; gap:0.75rem;">
            @if($email->status === 'failed')
                <span class="badge badge-red">Fallido</span>
            @elseif($email->is_opened)
                <span class="badge badge-green">Abierto {{ $email->open_count }}x</span>
                <span style="font-size:0.75rem; color:var(--text-muted);">Primera apertura: {{ $email->opened_at->format('d/m/Y H:i') }}</span>
            @else
                <span class="badge badge-blue">Enviado</span>
            @endif
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div style="padding:1rem 1.5rem; background:var(--bg); font-size:0.82rem; color:var(--text-muted); border-bottom:1px solid var(--border);">
            Enviado: {{ $email->created_at->format('d/m/Y H:i') }}
            @if($email->property_ids && count($email->property_ids) > 0)
                &middot; {{ count($email->property_ids) }} propiedad(es) adjuntas
            @endif
        </div>
        <div style="padding:1.5rem;">
            <iframe id="emailPreview" style="width:100%; border:none; min-height:500px;" srcdoc="{{ e($email->body_html) }}"></iframe>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var iframe = document.getElementById('emailPreview');
    iframe.onload = function() {
        iframe.style.height = iframe.contentDocument.body.scrollHeight + 40 + 'px';
    };
});
</script>
@endsection
