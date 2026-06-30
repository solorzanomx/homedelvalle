@extends('layouts.app-sidebar')
@section('title', 'Vista previa — ' . $label)

@section('content')

{{-- Header --}}
<div class="page-header">
    <div>
        <a href="{{ route('admin.custom-templates.index') }}" style="font-size:0.82rem;color:var(--primary);text-decoration:none;">← Email Templates</a>
        <h2 style="margin:0.4rem 0 0.2rem;font-size:1.3rem;">Vista previa — Acuse de Recibo</h2>
        <p style="color:var(--text-muted);margin:0;font-size:0.85rem;">Correo que recibe el lead al registrarse. Datos de muestra.</p>
    </div>
    <a href="{{ route('admin.acuse-configs.edit', $formType) }}" class="btn btn-primary">Editar configuración</a>
</div>

@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#047857;padding:0.75rem 1rem;border-radius:var(--radius);margin-bottom:1rem;font-size:0.85rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
<div style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:0.75rem 1rem;border-radius:var(--radius);margin-bottom:1rem;font-size:0.85rem;">{{ session('error') }}</div>
@endif

{{-- Type tabs --}}
<div style="display:flex;gap:0.4rem;margin-bottom:1.25rem;flex-wrap:wrap;">
    @php
    $typeIcons = [
        'vendedor'          => '🏠',
        'comprador'         => '🔑',
        'arrendatario'      => '📋',
        'propietario_renta' => '🏗️',
        'b2b'               => '💼',
        'contacto'          => '💬',
    ];
    @endphp
    @foreach($types as $type => $name)
    <a href="{{ route('admin.acuse-configs.preview', $type) }}"
       style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.4rem 0.9rem;border-radius:999px;font-size:0.8rem;font-weight:600;text-decoration:none;transition:all .15s;
              {{ $type === $formType
                 ? 'background:var(--primary);color:#fff;'
                 : 'background:var(--bg);color:var(--text-muted);border:1px solid var(--border);' }}">
        {{ $typeIcons[$type] ?? '' }} {{ $name }}
    </a>
    @endforeach
</div>

{{-- Main grid --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:1.25rem;align-items:start;">

    {{-- Preview iframe --}}
    <div>
        <div class="card">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                <span style="font-size:0.85rem;font-weight:600;">Preview del email</span>
                <a href="{{ route('admin.acuse-configs.render', $formType) }}" target="_blank" class="btn btn-outline btn-sm">Abrir en nueva pestaña</a>
            </div>
            <div class="card-body" style="padding:1.25rem;background:#F1F4F8;display:flex;justify-content:center;">
                <iframe
                    id="acuse-preview-iframe"
                    src="{{ route('admin.acuse-configs.render', $formType) }}"
                    style="width:650px;max-width:100%;border:none;display:block;background:#fff;min-height:500px;height:600px;border-radius:12px;flex-shrink:0;box-shadow:0 2px 12px rgba(0,0,0,.08);"
                    sandbox="allow-same-origin"
                ></iframe>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div style="display:flex;flex-direction:column;gap:1rem;">

        {{-- Send test --}}
        <div class="card">
            <div class="card-body">
                <h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 0.75rem;letter-spacing:.05em;">Enviar prueba</h4>
                <form action="{{ route('admin.acuse-configs.send-test', $formType) }}" method="POST">
                    @csrf
                    <div class="form-group" style="margin-bottom:0.6rem;">
                        <input type="email" name="email" class="form-input" value="{{ auth()->user()->email }}" placeholder="Email de prueba">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Enviar correo de prueba</button>
                </form>
            </div>
        </div>

        {{-- Config summary --}}
        <div class="card">
            <div class="card-body">
                <h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 0.75rem;letter-spacing:.05em;">Configuración activa</h4>
                <div style="display:flex;flex-direction:column;gap:0.6rem;font-size:0.8rem;">
                    <div>
                        <div style="font-size:0.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Asunto</div>
                        <div style="color:var(--text);">{{ $config->subject }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Badge</div>
                        <span style="background:#EAF3FB;color:#2270B0;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:999px;">{{ $config->badge }}</span>
                    </div>
                    <div>
                        <div style="font-size:0.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">Título</div>
                        <div style="color:var(--text);">{{ $config->titulo }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:2px;">CTA primario</div>
                        <div style="color:var(--text);">{{ $config->cta1_label }} <span style="color:var(--text-muted);">({{ $config->cta1_type }})</span></div>
                    </div>
                </div>
                <div style="margin-top:1rem;padding-top:0.75rem;border-top:1px solid var(--border);">
                    <a href="{{ route('admin.acuse-configs.edit', $formType) }}" class="btn btn-outline btn-sm" style="width:100%;text-align:center;">Editar este tipo</a>
                </div>
            </div>
        </div>

        {{-- Sample data used --}}
        <div class="card">
            <div class="card-body">
                <h4 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);margin:0 0 0.75rem;letter-spacing:.05em;">Datos de muestra</h4>
                <div style="font-size:0.75rem;color:var(--text-muted);line-height:1.6;">
                    <div><strong style="color:var(--text);">Nombre:</strong> Juan Pérez López</div>
                    <div><strong style="color:var(--text);">Formulario:</strong> <code style="font-size:0.7rem;">{{ $formType }}</code></div>
                    @php
                    $samples = [
                        'vendedor'          => ['colonia' => 'Del Valle', 'tipo_propiedad' => 'departamento'],
                        'comprador'         => ['zonas' => 'Del Valle, Narvarte', 'presupuesto' => '4m–6m'],
                        'arrendatario'      => ['zonas' => 'Del Valle', 'mascotas' => 'perro'],
                        'propietario_renta' => ['colonia' => 'Del Valle', 'tipo_propiedad' => 'departamento'],
                        'b2b'               => ['zonas' => 'Benito Juárez', 'tipo' => 'compra terminado'],
                        'contacto'          => ['mensaje' => 'Quisiera más información...'],
                    ];
                    @endphp
                    @foreach($samples[$formType] ?? [] as $k => $v)
                    <div><strong style="color:var(--text);">{{ ucfirst($k) }}:</strong> {{ $v }}</div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>

<script>
(function () {
    const iframe = document.getElementById('acuse-preview-iframe');
    if (!iframe) return;

    function adjust() {
        try {
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            if (doc && doc.documentElement) {
                const h = doc.documentElement.scrollHeight;
                if (h > 100) iframe.style.height = (h + 24) + 'px';
            }
        } catch (e) {}
    }

    iframe.addEventListener('load', adjust);
    setTimeout(adjust, 600);
    setTimeout(adjust, 1800);
})();
</script>
@endsection
