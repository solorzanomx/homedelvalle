@extends('layouts.public')

@section('meta')
<title>Opinión de valor gratuita en Benito Juárez | Home del Valle</title>
<meta name="description" content="Solicita una opinión de valor personalizada para tu inmueble en Benito Juárez. Análisis de mercado con datos actualizados de colonias como Narvarte, Del Valle, Portales y más.">
<link rel="canonical" href="{{ url('/mercado/opinion-de-valor') }}">
@endsection

@section('content')

@if(session('lead_success'))
{{-- Estado de éxito --}}
<section style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:3rem 1.5rem;background:#f0fdf4;">
    <div style="max-width:520px;text-align:center;">
        <div style="width:64px;height:64px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.75rem;">✓</div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#14532d;margin-bottom:.75rem;">¡Solicitud recibida!</h1>
        <p style="color:#166534;font-size:.92rem;line-height:1.65;margin-bottom:1.75rem;">
            Recibimos tu solicitud de opinión de valor. Uno de nuestros especialistas revisará los datos y te contactará en un plazo de <strong>1–2 días hábiles</strong>.
        </p>
        <div style="background:#fff;border:1px solid #bbf7d0;border-radius:10px;padding:1.25rem;text-align:left;margin-bottom:1.75rem;">
            <p style="font-size:.82rem;color:#166534;margin:0;line-height:1.6;">
                <strong>¿Qué sigue?</strong><br>
                Nuestro equipo preparará un análisis de mercado con datos comparables en tu colonia, ajustes por las características de tu inmueble y un precio sugerido de salida.
            </p>
        </div>
        <a href="{{ route('mercado.index') }}"
           style="display:inline-block;background:#15803d;color:#fff;padding:.7rem 1.75rem;border-radius:8px;font-weight:600;font-size:.88rem;text-decoration:none;">
            Explorar precios en Benito Juárez →
        </a>
    </div>
</section>

@else
{{-- Hero --}}
<section style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;padding:3.5rem 1.5rem 3rem;">
    <div style="max-width:680px;margin:0 auto;text-align:center;">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:2px;color:rgba(255,255,255,.45);margin-bottom:.75rem;">
            Benito Juárez · Ciudad de México
        </div>
        <h1 style="font-size:clamp(1.5rem,3.5vw,2.2rem);font-weight:700;line-height:1.25;margin-bottom:1rem;">
            Opinión de valor personalizada
        </h1>
        <p style="font-size:.92rem;color:rgba(255,255,255,.7);max-width:500px;margin:0 auto;line-height:1.6;">
            Más que un estimado automático — analizamos tu inmueble con datos reales de mercado en tu colonia específica.
        </p>
    </div>
</section>

{{-- Qué incluye --}}
<section style="background:#f8fafc;border-bottom:1px solid #e5e7eb;padding:1.75rem 1.5rem;">
    <div style="max-width:800px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
        @foreach([
            ['📊', 'Análisis comparativo', 'Comparables recientes en tu colonia y zona'],
            ['🎯', 'Precio sugerido', 'Rango de salida y precio de cierre esperado'],
            ['⚙️', 'Ajustes específicos', 'Por antigüedad, piso, estado, amenidades'],
            ['📅', 'En 1–2 días hábiles', 'Entrega rápida por WhatsApp o email'],
        ] as [$icon, $title, $desc])
        <div style="display:flex;gap:.75rem;align-items:flex-start;">
            <span style="font-size:1.25rem;line-height:1;">{{ $icon }}</span>
            <div>
                <div style="font-size:.83rem;font-weight:700;color:#111827;margin-bottom:.2rem;">{{ $title }}</div>
                <div style="font-size:.77rem;color:#6b7280;line-height:1.45;">{{ $desc }}</div>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- Formulario --}}
<section style="max-width:640px;margin:0 auto;padding:3rem 1.5rem;">
    <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.3rem;">Cuéntanos sobre tu inmueble</h2>
    <p style="font-size:.83rem;color:#9ca3af;margin-bottom:2rem;">Todos los campos con * son obligatorios.</p>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:1rem;margin-bottom:1.5rem;">
        <ul style="margin:0;padding:0 0 0 1.25rem;font-size:.82rem;color:#b91c1c;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('mercado.opinion.store') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
        @csrf

        {{-- Datos de contacto --}}
        <fieldset style="border:none;padding:0;margin:0;">
            <legend style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:1rem;">Datos de contacto</legend>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div>
                    <label for="owner_name" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Nombre *</label>
                    <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}"
                           placeholder="Tu nombre completo"
                           style="width:100%;padding:.6rem .85rem;border:1px solid {{ $errors->has('owner_name') ? '#f87171' : '#d1d5db' }};border-radius:6px;font-size:.85rem;box-sizing:border-box;outline:none;"
                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='{{ $errors->has('owner_name') ? '#f87171' : '#d1d5db' }}'">
                    @error('owner_name')<span style="font-size:.72rem;color:#ef4444;">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="owner_phone" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Teléfono / WhatsApp *</label>
                    <input type="tel" id="owner_phone" name="owner_phone" value="{{ old('owner_phone') }}"
                           placeholder="55 1234 5678"
                           style="width:100%;padding:.6rem .85rem;border:1px solid {{ $errors->has('owner_phone') ? '#f87171' : '#d1d5db' }};border-radius:6px;font-size:.85rem;box-sizing:border-box;outline:none;"
                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='{{ $errors->has('owner_phone') ? '#f87171' : '#d1d5db' }}'">
                    @error('owner_phone')<span style="font-size:.72rem;color:#ef4444;">{{ $message }}</span>@enderror
                </div>
            </div>

            <div style="margin-top:1rem;">
                <label for="owner_email" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Correo electrónico <span style="font-weight:400;color:#9ca3af;">(opcional)</span></label>
                <input type="email" id="owner_email" name="owner_email" value="{{ old('owner_email') }}"
                       placeholder="tu@correo.com"
                       style="width:100%;padding:.6rem .85rem;border:1px solid {{ $errors->has('owner_email') ? '#f87171' : '#d1d5db' }};border-radius:6px;font-size:.85rem;box-sizing:border-box;outline:none;"
                       onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='{{ $errors->has('owner_email') ? '#f87171' : '#d1d5db' }}'">
                @error('owner_email')<span style="font-size:.72rem;color:#ef4444;">{{ $message }}</span>@enderror
            </div>
        </fieldset>

        {{-- Datos del inmueble --}}
        <fieldset style="border:none;padding:0;margin:0;border-top:1px solid #f3f4f6;padding-top:1.25rem;">
            <legend style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;margin-bottom:1rem;">Datos del inmueble</legend>

            <div style="margin-bottom:1rem;">
                <label for="colonia_id" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Colonia</label>
                <select id="colonia_id" name="colonia_id"
                        style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box;background:#fff;outline:none;"
                        onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#d1d5db'">
                    <option value="">Seleccionar colonia…</option>
                    @foreach($colonias as $zoneName => $zoneColonias)
                    <optgroup label="{{ $zoneName }}">
                        @foreach($zoneColonias as $c)
                        <option value="{{ $c->id }}" {{ (old('colonia_id') == $c->id || request('colonia') == $c->id) ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                    <option value="" disabled>──────────────</option>
                    <option value="other">Otra colonia de Benito Juárez</option>
                </select>
            </div>

            <div id="colonia_raw_wrap" style="display:none;margin-bottom:1rem;">
                <label for="colonia_raw" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Nombre de la colonia</label>
                <input type="text" id="colonia_raw" name="colonia_raw" value="{{ old('colonia_raw') }}"
                       placeholder="Escribe el nombre de la colonia"
                       style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box;outline:none;"
                       onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#d1d5db'">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <div>
                    <label for="property_type" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Tipo de inmueble *</label>
                    <select id="property_type" name="property_type"
                            style="width:100%;padding:.6rem .85rem;border:1px solid {{ $errors->has('property_type') ? '#f87171' : '#d1d5db' }};border-radius:6px;font-size:.85rem;box-sizing:border-box;background:#fff;outline:none;"
                            onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='{{ $errors->has('property_type') ? '#f87171' : '#d1d5db' }}'">
                        <option value="">Seleccionar…</option>
                        <option value="apartment" {{ old('property_type') === 'apartment' ? 'selected' : '' }}>Departamento</option>
                        <option value="house" {{ old('property_type') === 'house' ? 'selected' : '' }}>Casa</option>
                        <option value="land" {{ old('property_type') === 'land' ? 'selected' : '' }}>Terreno</option>
                        <option value="office" {{ old('property_type') === 'office' ? 'selected' : '' }}>Oficina / Local</option>
                    </select>
                    @error('property_type')<span style="font-size:.72rem;color:#ef4444;">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="m2_approx" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Superficie aprox. <span style="font-weight:400;color:#9ca3af;">(m²)</span></label>
                    <input type="number" id="m2_approx" name="m2_approx" value="{{ old('m2_approx') }}"
                           placeholder="Ej. 85"  min="10" max="5000"
                           style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box;outline:none;"
                           onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#d1d5db'">
                </div>
            </div>

            <div>
                <label for="message" style="display:block;font-size:.82rem;font-weight:600;color:#374151;margin-bottom:.35rem;">Comentarios adicionales <span style="font-weight:400;color:#9ca3af;">(opcional)</span></label>
                <textarea id="message" name="message" rows="3"
                          placeholder="Antigüedad aproximada, número de recámaras, estacionamiento, estado de conservación, etc."
                          style="width:100%;padding:.6rem .85rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;resize:vertical;box-sizing:border-box;outline:none;font-family:inherit;"
                          onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#d1d5db'">{{ old('message') }}</textarea>
            </div>
        </fieldset>

        <button type="submit"
                style="background:#2563eb;color:#fff;border:none;padding:.8rem 2rem;border-radius:8px;font-weight:700;font-size:.92rem;cursor:pointer;transition:background .15s;"
                onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
            Solicitar opinión de valor →
        </button>

        <p style="font-size:.73rem;color:#9ca3af;text-align:center;margin-top:-.5rem;line-height:1.5;">
            No compartimos tu información con terceros. Te contactaremos solo para entregarte tu análisis.
        </p>
    </form>
</section>

{{-- Nota metodológica --}}
<section style="background:#f8fafc;border-top:1px solid #e5e7eb;padding:2rem 1.5rem;">
    <div style="max-width:680px;margin:0 auto;">
        <h3 style="font-size:.88rem;font-weight:700;color:#374151;margin-bottom:.75rem;">Nota metodológica</h3>
        <p style="font-size:.8rem;color:#6b7280;line-height:1.65;">
            Las opiniones de valor que preparamos en Home del Valle se basan en análisis de oferta publicada en portales inmobiliarios,
            datos de transacciones recientes en la zona y ajustes por las características específicas de cada inmueble (antigüedad,
            estado de conservación, piso, número de estacionamientos, amenidades). <strong>No son avalúos formales</strong> — para
            trámites notariales o crediticios se requiere un valuador certificado (INDAABIN/AMPI).
            Son una referencia de mercado de alta calidad para vendedores y compradores que quieren tomar decisiones informadas.
        </p>
    </div>
</section>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('colonia_id');
    var wrap = document.getElementById('colonia_raw_wrap');
    if (!sel || !wrap) return;

    function toggle() {
        wrap.style.display = sel.value === 'other' ? 'block' : 'none';
    }
    sel.addEventListener('change', toggle);
    toggle();
});
</script>
@endsection
