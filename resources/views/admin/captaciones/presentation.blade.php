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
    height: calc(100vh - 180px);
    min-height: 600px;
    border: none;
    border-radius: 8px;
    background: #f1f5f9;
}
.sticky-panel { position: sticky; top: 72px; }
@media (max-width: 900px) {
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
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div class="presentation-grid">

        {{-- ── Panel izquierdo: acciones ────────────────────────── --}}
        <div class="sticky-panel">

            {{-- Regenerar PDF --}}
            <div class="card" style="margin-bottom:1rem;">
                <div class="card-header"><h3>Acciones</h3></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem;">

                    {{-- Regenerar --}}
                    <form method="POST" action="{{ route('admin.captaciones.presentation.regenerate', $captacion) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="width:100%;">
                            <x-icon name="plus" class="w-4 h-4" />
                            Regenerar PDF
                        </button>
                    </form>

                    {{-- Descargar --}}
                    <a href="{{ route('admin.captaciones.presentation.download', $captacion) }}"
                       class="btn btn-primary" style="width:100%;justify-content:center;">
                        <x-icon name="arrow-right" class="w-4 h-4" />
                        Descargar PDF
                    </a>

                    @if($captacion->client->email)
                    <a href="mailto:{{ $captacion->client->email }}?subject={{ urlencode('Tu presentación de Home del Valle — ' . $captacion->property_address_display) }}"
                       class="btn btn-outline" style="width:100%;justify-content:center;">
                        <x-icon name="mail" class="w-4 h-4" />
                        Enviar por email
                    </a>
                    @else
                    <button disabled class="btn btn-outline" style="width:100%;opacity:.4;cursor:not-allowed;" title="Agrega el email del propietario primero">
                        <x-icon name="mail" class="w-4 h-4" />
                        Enviar por email
                    </button>
                    <p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:-.3rem;">Sin email registrado</p>
                    @endif

                    @if($captacion->client->phone || $captacion->client->whatsapp)
                    @php
                        $phone = preg_replace('/\D+/', '', $captacion->client->whatsapp ?? $captacion->client->phone);
                        if (!str_starts_with($phone, '52')) $phone = '52' . $phone;
                        $waMsg = urlencode("Hola {$captacion->client->name}, soy {$captacion->createdBy?->name} de Home del Valle. Te comparto la presentación inicial para tu inmueble. Quedo atento a tus comentarios.");
                        $waUrl = "https://wa.me/{$phone}?text={$waMsg}";
                    @endphp
                    <a href="{{ $waUrl }}" target="_blank"
                       class="btn btn-outline" style="width:100%;justify-content:center;">
                        <x-icon name="send" class="w-4 h-4" />
                        Abrir WhatsApp
                    </a>
                    @endif

                </div>
            </div>

            {{-- Info de captación --}}
            <div class="card">
                <div class="card-header"><h3>Detalles</h3></div>
                <div class="card-body" style="font-size:.82rem;display:flex;flex-direction:column;gap:.6rem;">
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Propietario</span>
                        <strong>{{ $captacion->client->name }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Intent</span>
                        <strong>{{ $captacion->intent_label }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Comisión</span>
                        <strong style="color:var(--success);">{{ $captacion->commission_pct }}%</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:var(--text-muted);">Generado</span>
                        <span>{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Panel derecho: iframe del PDF ───────────────────── --}}
        <div class="card" style="overflow:hidden;">
            <iframe src="{{ route('admin.captaciones.presentation.pdf', $captacion) }}"
                    class="pdf-frame"
                    title="Presentación inicial">
            </iframe>
        </div>

    </div>

</div>
@endsection
