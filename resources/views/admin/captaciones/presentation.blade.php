@extends('layouts.app-sidebar')
@section('title', 'Presentación — ' . $captacion->client->name)

@section('styles')
<style>
.presentation-grid {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 1.5rem;
    align-items: start;
}
.pdf-frame {
    width: 100%;
    height: calc(100vh - 190px);
    min-height: 600px;
    border: none;
    border-radius: 8px;
    background: #f1f5f9;
}
.sticky-panel { position: sticky; top: 72px; }
.send-section { border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 1rem; }
.send-section label { display:block; font-size:.8rem; font-weight:600; margin-bottom:.35rem; }
.send-section input { width:100%; padding:.5rem .75rem; border:1px solid var(--border); border-radius:var(--radius); font-family:inherit; font-size:.85rem; }
.send-section .btn { width:100%; justify-content:center; margin-top:.5rem; }
.tracking-row { display:flex; justify-content:space-between; align-items:center; font-size:.78rem; padding:.35rem 0; border-bottom:1px solid var(--border); }
.tracking-row:last-child { border-bottom:none; }
.tracking-val { font-weight:600; color:var(--text); }
.badge-sent   { background:#ecfdf5; color:#065f46; padding:2px 8px; border-radius:4px; font-size:.72rem; font-weight:600; }
.badge-unsent { background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:4px; font-size:.72rem; }
@media (max-width:900px) {
    .presentation-grid { grid-template-columns: 1fr; }
    .pdf-frame { height: 70vh; }
}
</style>
@endsection

@section('content')
<div class="content-body">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h2 style="display:flex;align-items:center;gap:.5rem;">
                <x-icon name="file-text" class="w-5 h-5" style="color:var(--primary);" />
                Presentación inicial
            </h2>
            <p style="font-size:.83rem;color:var(--text-muted);margin-top:.2rem;">
                {{ $captacion->client->name }} · {{ $captacion->property_address_display }}
            </p>
        </div>
        <a href="{{ route('admin.captaciones.show', $captacion) }}" class="btn btn-outline btn-sm">
            <x-icon name="arrow-left" class="w-4 h-4" />
            Volver
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success" id="flash-alert">
        <x-icon name="check" class="w-4 h-4" />
        {{ session('success') }}
        <button onclick="this.parentElement.remove()" class="alert-close">×</button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-error">
        <x-icon name="triangle-alert" class="w-4 h-4" />
        {{ session('error') }}
        <button onclick="this.parentElement.remove()" class="alert-close">×</button>
    </div>
    @endif

    <div class="presentation-grid">

        {{-- ── Panel izquierdo: acciones y envío ── --}}
        <div class="sticky-panel">

            {{-- Acciones básicas --}}
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header"><h3>PDF</h3></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.6rem;">
                    <form method="POST" action="{{ route('admin.captaciones.presentation.regenerate', $captacion) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="width:100%;">
                            <x-icon name="plus" class="w-4 h-4" />
                            Regenerar PDF
                        </button>
                    </form>
                    <a href="{{ route('admin.captaciones.presentation.admin.download', $captacion) }}"
                       class="btn btn-outline" style="width:100%;justify-content:center;">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                        Descargar
                    </a>
                    @php $publicToken = $captacion->sends()->latest()->first()?->tracking_token; @endphp
                    @if($publicToken)
                    <a href="{{ route('presentation.public', $publicToken) }}" target="_blank"
                       class="btn btn-outline" style="width:100%;justify-content:center;font-size:.78rem;">
                        <x-icon name="eye" class="w-4 h-4" />
                        Ver como propietario
                    </a>
                    @endif
                </div>
            </div>

            {{-- Enviar por email --}}
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header"><h3>Enviar por email</h3></div>
                <div class="card-body">
                    @if($captacion->client->email)
                    <form method="POST" action="{{ route('admin.captaciones.presentation.send.email', $captacion) }}">
                        @csrf
                        <div class="send-section" style="border:none;padding:0;margin:0;">
                            <label>Correo del propietario</label>
                            <input type="email" name="email" value="{{ $captacion->client->email }}" required>
                            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.6rem;">
                                <x-icon name="send" class="w-4 h-4" />
                                Enviar con PDF adjunto
                            </button>
                        </div>
                    </form>
                    @else
                    <p style="font-size:.82rem;color:var(--text-muted);">
                        Sin email registrado. Agrégalo en el perfil del cliente para habilitar el envío.
                    </p>
                    @endif
                </div>
            </div>

            {{-- Enviar por WhatsApp --}}
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header"><h3>Enviar por WhatsApp</h3></div>
                <div class="card-body">
                    @php $waPhone = $captacion->client->whatsapp ?? $captacion->client->phone ?? ''; @endphp
                    <form method="POST" action="{{ route('admin.captaciones.presentation.send.whatsapp', $captacion) }}">
                        @csrf
                        <div class="send-section" style="border:none;padding:0;margin:0;">
                            <label>Teléfono (WhatsApp)</label>
                            <input type="tel" name="phone" value="{{ $waPhone }}" placeholder="55 1234 5678" required>
                            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.6rem;background:#25D366;border-color:#25D366;">
                                Abrir WhatsApp Desktop →
                            </button>
                        </div>
                    </form>
                    <p style="font-size:.72rem;color:var(--text-muted);margin-top:.5rem;line-height:1.4;">
                        Se abrirá WhatsApp con el mensaje y el link de la presentación pre-cargados. El agente envía desde su WhatsApp.
                    </p>
                </div>
            </div>

            {{-- Tracking --}}
            @if($captacion->sends()->exists())
            <div class="card">
                <div class="card-header"><h3>Actividad</h3></div>
                <div class="card-body">
                    @foreach($captacion->sends()->latest()->take(5)->get() as $s)
                    <div style="font-size:.78rem;padding:.5rem 0;border-bottom:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;margin-bottom:.2rem;">
                            <strong>{{ $s->channel_label }}</strong>
                            <span style="color:var(--text-muted);">{{ $s->sent_at->format('d/m H:i') }}</span>
                        </div>
                        <div style="color:var(--text-muted);display:flex;gap:.5rem;flex-wrap:wrap;">
                            @if($s->email_opened_at)
                            <span class="badge-sent">✓ Abierto</span>
                            @endif
                            @if($s->pdf_viewed_at)
                            <span class="badge-sent">✓ Visto ({{ $s->pdf_view_count }}x)</span>
                            @endif
                            @if($s->pdf_downloaded_at)
                            <span class="badge-sent">✓ Descargado</span>
                            @endif
                            @if(!$s->email_opened_at && !$s->pdf_viewed_at)
                            <span class="badge-unsent">Sin abrir</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ── Panel derecho: iframe del PDF ── --}}
        <div class="card" style="overflow:hidden;">
            <iframe src="{{ route('admin.captaciones.presentation.pdf', $captacion) }}"
                    class="pdf-frame"
                    title="Presentación inicial">
            </iframe>
        </div>

    </div>

</div>
@endsection
