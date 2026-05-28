@extends('layouts.app-sidebar')
@section('title', 'Presentación — ' . $captacion->client->name)

@section('styles')
<style>
/* Evitar scroll horizontal que hace que el sidebar tape el editor */
.content-body { overflow-x: hidden; }
.presentation-layout, .presentation-layout * { max-width: 100%; box-sizing: border-box; }

/* Vertical por defecto — lado a lado solo en pantallas anchas (≥1280px)
   El sidebar ocupa 260px fijos, así que necesitamos al menos 260+300+550=1110px
   para que los dos paneles sean usables. Usamos 1280px como punto de quiebre. */
.presentation-layout {
    display: flex;
    flex-direction: column;   /* vertical por defecto */
    gap: 1.5rem;
}
.presentation-sticky {
    width: 100%;
    position: static;
    max-height: none;
    overflow-y: visible;
}
.pdf-panel { width: 100%; }
.pdf-frame {
    width: 100%;
    max-width: 100%;
    height: 75vh;
    min-height: 500px;
    border: none;
    background: #f1f5f9;
    border-radius: 8px;
    display: block;
}

/* Lado a lado solo cuando hay suficiente espacio */
@media (min-width: 1280px) {
    .presentation-layout {
        flex-direction: row;
        align-items: start;
    }
    .presentation-sticky {
        width: 300px;
        flex: 0 0 300px;
        position: sticky;
        top: 72px;
        max-height: calc(100vh - 88px);
        overflow-y: auto;
    }
    .pdf-panel {
        flex: 1 1 0;
        min-width: 0;
    }
    .pdf-frame {
        height: calc(100vh - 190px);
        min-height: 600px;
    }
}

/* Loading overlay sobre el iframe */
.pdf-loading-overlay {
    position: absolute;
    inset: 0;
    background: #f8fafc;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    z-index: 10;
}
.pdf-loading-spinner {
    width: 48px; height: 48px;
    border: 4px solid #e2e8f0;
    border-top-color: var(--primary, #667eea);
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}
.pdf-loading-steps {
    display: flex;
    flex-direction: column;
    gap: .35rem;
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: .78rem;
    color: #64748b;
}
.pdf-loading-steps li { display: flex; align-items: center; gap: .4rem; }
.pdf-loading-steps li.done { color: #10b981; }
.pdf-loading-steps li::before { content: '○'; }
.pdf-loading-steps li.done::before { content: '✓'; }
@keyframes spin { to { transform: rotate(360deg); } }
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
    <div class="pdf-panel">
      {{-- min-height fijo para que el panel no colapse mientras carga el iframe --}}
      <div class="card" style="overflow:hidden;padding:0;position:relative;min-height:640px;">

        {{-- Overlay de carga (visible hasta que el iframe carga) --}}
        <div id="pdf-loading-overlay" class="pdf-loading-overlay">
          <div class="pdf-loading-spinner"></div>
          <div style="text-align:center;">
            <p style="font-weight:700;color:#1e293b;margin:0 0 .5rem;font-size:.95rem;">Generando presentación…</p>
            <p style="font-size:.78rem;color:#64748b;margin:0;">Este proceso tarda entre 20 y 60 segundos.</p>
          </div>
          <ul class="pdf-loading-steps" id="pdf-loading-steps">
            <li id="step-html">Renderizando diseño</li>
            <li id="step-chrome">Abriendo Chrome headless</li>
            <li id="step-pdf">Exportando a PDF</li>
            <li id="step-done">Listo</li>
          </ul>
        </div>

        <iframe id="presentation-pdf-frame"
                src=""
                class="pdf-frame"
                style="opacity:0;transition:opacity .3s;"
                title="Preview de la presentación">
        </iframe>

      </div>
      <p style="font-size:.72rem;color:var(--text-muted);text-align:center;margin-top:.5rem;">
        El PDF se actualiza automáticamente al cambiar comisión, precio o plan de marketing.
      </p>
    </div>

  </div>

</div>

@section('scripts')
<script>
(function () {
    var pdfUrl    = '{{ route('admin.captaciones.presentation.pdf', $captacion) }}';
    var frame     = document.getElementById('presentation-pdf-frame');
    var overlay   = document.getElementById('pdf-loading-overlay');
    var steps     = ['step-html', 'step-chrome', 'step-pdf', 'step-done'];
    var stepIndex = 0;

    // Animar pasos mientras carga (cada ~12s para 4 pasos en ~48s)
    function tickStep() {
        if (stepIndex < steps.length) {
            var el = document.getElementById(steps[stepIndex]);
            if (el) el.classList.add('done');
            stepIndex++;
        }
    }
    tickStep(); // paso 1 inmediato
    var stepTimer = setInterval(tickStep, 12000);

    // Cuando el iframe termina de cargar el PDF
    frame.addEventListener('load', function () {
        clearInterval(stepTimer);
        // marcar todos como done
        steps.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('done');
        });
        // breve pausa para que el usuario vea el ✓ final, luego mostrar PDF
        setTimeout(function () {
            overlay.style.display = 'none';
            frame.style.opacity   = '1';
        }, 400);
    });

    // Disparar carga del iframe ahora (la página ya está visible)
    frame.src = pdfUrl;

    // Escuchar regeneraciones desde Livewire
    window.addEventListener('pdfUrlUpdated', function (e) {
        overlay.style.display = 'flex';
        frame.style.opacity   = '0';
        stepIndex = 0;
        steps.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.classList.remove('done');
        });
        tickStep();
        clearInterval(stepTimer);
        stepTimer = setInterval(tickStep, 12000);
        frame.src = e.detail.url;
    });
})();
</script>
@endsection
