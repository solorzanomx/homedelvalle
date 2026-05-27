@extends('layouts.app-sidebar')
@section('title', 'Presentación — ' . $captacion->client->name)

@section('styles')
<style>
.presentation-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 1.5rem;
    align-items: start;
}
.presentation-sticky { position: sticky; top: 72px; max-height: calc(100vh - 88px); overflow-y: auto; }
.pdf-frame {
    width: 100%;
    height: calc(100vh - 190px);
    min-height: 600px;
    border: none;
    background: #f1f5f9;
    border-radius: 8px;
    display: block;
}
/* Mobile: pdf arriba, editor abajo */
@media (max-width: 900px) {
    .presentation-layout { grid-template-columns: 1fr; }
    .pdf-frame { height: 65vh; }
    .presentation-sticky { position: static; max-height: none; }
}
</style>
@endsection

@section('content')
<div class="content-body">

  <div class="page-header">
    <div>
      <h2 style="display:flex;align-items:center;gap:.5rem;">
        <x-icon name="file-text" class="w-5 h-5" style="color:var(--primary);" />
        Presentación inicial
      </h2>
      <p style="font-size:.83rem;color:var(--text-muted);margin-top:.2rem;">
        {{ $captacion->client->name }} · {{ $captacion->property_address_display }}
        <span style="background:var(--border);color:var(--text-muted);padding:1px 8px;border-radius:4px;font-size:.75rem;margin-left:.5rem;">{{ $captacion->intent_label }}</span>
      </p>
    </div>
    <a href="{{ route('admin.captaciones.show', $captacion) }}" class="btn btn-outline btn-sm">
      <x-icon name="arrow-left" class="w-4 h-4" />
      Volver
    </a>
  </div>

  @if(session('success'))
  <div class="alert alert-success"><x-icon name="check" class="w-4 h-4" />{{ session('success') }}<button onclick="this.parentElement.remove()" class="alert-close">×</button></div>
  @endif
  @if(session('error'))
  <div class="alert alert-error"><x-icon name="triangle-alert" class="w-4 h-4" />{{ session('error') }}<button onclick="this.parentElement.remove()" class="alert-close">×</button></div>
  @endif

  <div class="presentation-layout">

    {{-- Panel izquierdo: editor Livewire --}}
    <div class="presentation-sticky">
      @livewire('admin.presentation-editor', ['captacion' => $captacion])
    </div>

    {{-- Panel derecho: iframe PDF --}}
    <div>
      <div class="card" style="overflow:hidden;padding:0;">
        <iframe id="presentation-pdf-frame"
                src="{{ route('admin.captaciones.presentation.pdf', $captacion) }}"
                class="pdf-frame"
                title="Preview de la presentación">
        </iframe>
      </div>
      <p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.5rem;">
        El PDF se actualiza automáticamente al cambiar comisión, precio o plan de marketing.
      </p>
    </div>

  </div>

</div>
@endsection
