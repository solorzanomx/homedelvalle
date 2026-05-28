@extends('layouts.app-sidebar')
@section('title', 'Presentación — ' . $captacion->client->name)

@section('styles')
{{-- @yield('styles') ya está DENTRO del <style> global del layout.
     NO agregar tags <style> aquí — rompe el CSS parser del browser. --}}
/* ── Fix: forzar el offset correcto del sidebar ───────────────────────────
   En producción var(--sidebar-w) puede no resolverse correctamente.
   Aplicar margin-left explícito con !important desde la página. */
@media (min-width: 769px) {
    html body main.main-content { margin-left: 260px !important; }
}
@media (max-width: 768px) {
    html body main.main-content { margin-left: 0 !important; }
}

/* Layout siempre en columna única */
.presentation-layout {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    max-width: 100%;
}

/* Panel del editor: barra horizontal compacta */
.presentation-editor-panel {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1rem 1.25rem;
}
.editor-fields-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: flex-end;
}
.editor-field { display: flex; flex-direction: column; gap: .3rem; }
.editor-field label { font-size: .78rem; font-weight: 600; color: var(--text-muted); }

/* PDF ocupa casi todo el viewport disponible */
.pdf-frame {
    width: 100%;
    height: calc(100vh - 260px);
    min-height: 500px;
    border: none;
    background: #f1f5f9;
    border-radius: 8px;
    display: block;
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

    {{-- ── Livewire editor (arriba, full-width) ─────────────────────────── --}}
    @livewire('admin.presentation-editor', ['captacion' => $captacion])

    {{-- ── Iframe PDF (abajo, full-width) ──────────────────────────────── --}}
    <div>
      <div class="card" style="overflow:hidden;padding:0;position:relative;min-height:520px;">

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

@endsection

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

    // Fix JS: si margin-left de main-content no se aplicó vía CSS, corregirlo
    (function() {
        if (window.innerWidth <= 768) return;
        var main = document.querySelector('main.main-content');
        if (!main) return;
        var ml = parseInt(window.getComputedStyle(main).marginLeft || '0', 10);
        if (ml < 200) {
            main.style.setProperty('margin-left', '260px', 'important');
            console.warn('[HDV] main-content margin-left forzado a 260px (era: ' + ml + 'px)');
        }
    })();

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
