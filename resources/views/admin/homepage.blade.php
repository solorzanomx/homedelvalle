@extends('layouts.app-sidebar')
@section('title', 'Editor de Homepage')

@section('content')
<div class="page-header">
    <div>
        <h2>Editor de Homepage</h2>
        <p class="text-muted">Edita las secciones del sitio publico</p>
    </div>
    <a href="{{ url('/') }}" target="_blank" class="btn btn-outline" style="gap:0.5rem;">
        <x-icon name="external-link" class="w-4 h-4" />
        Ver sitio
    </a>
</div>

<form method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data">
    @csrf

    {{-- ========================================== --}}
    {{-- 1. HERO SECTION --}}
    {{-- ========================================== --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>1. Hero</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo principal</label>
                    <input type="text" name="hero_heading" class="form-control" value="{{ old('hero_heading', $settings->hero_heading ?? '') }}" placeholder="Encuentra el hogar que mereces en CDMX">
                </div>
                <div class="form-group">
                    <label class="form-label">Imagen de fondo</label>
                    <input type="file" name="hero_image" class="form-control" accept="image/*">
                    @if($settings?->hero_image_path)
                        <p class="form-hint" style="margin-top:0.5rem;">Imagen actual: <strong>{{ basename($settings->hero_image_path) }}</strong></p>
                    @endif
                </div>
            </div>
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Subtitulo</label>
                <textarea name="hero_subheading" class="form-control" rows="2" placeholder="Asesoria inmobiliaria personalizada...">{{ old('hero_subheading', $settings->hero_subheading ?? '') }}</textarea>
            </div>
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Badge (texto sobre el titulo)</label>
                <input type="text" name="hero_badge" class="form-control" value="{{ old('hero_badge', $settings->hero_badge ?? '') }}" placeholder="Inmobiliaria boutique en Benito Juarez">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem;">
                <div class="form-group">
                    <label class="form-label">CTA primario — Texto</label>
                    <input type="text" name="hero_cta_text" class="form-control" value="{{ old('hero_cta_text', $settings->hero_cta_text ?? '') }}" placeholder="Valua tu propiedad">
                </div>
                <div class="form-group">
                    <label class="form-label">CTA primario — URL</label>
                    <input type="text" name="hero_cta_url" class="form-control" value="{{ old('hero_cta_url', $settings->hero_cta_url ?? '') }}" placeholder="/vende-tu-propiedad">
                </div>
                <div class="form-group">
                    <label class="form-label">CTA secundario — Texto</label>
                    <input type="text" name="hero_secondary_cta_text" class="form-control" value="{{ old('hero_secondary_cta_text', $settings->hero_secondary_cta_text ?? '') }}" placeholder="Ver propiedades">
                </div>
                <div class="form-group">
                    <label class="form-label">CTA secundario — URL</label>
                    <input type="text" name="hero_secondary_cta_url" class="form-control" value="{{ old('hero_secondary_cta_url', $settings->hero_secondary_cta_url ?? '') }}" placeholder="/propiedades">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. BENEFICIOS --}}
    {{-- ========================================== --}}
    @php
        $defaultBenefits = [
            ['icon' => 'shield', 'title' => 'Confianza garantizada', 'description' => 'Operaciones transparentes con acompanamiento legal en cada paso del proceso.'],
            ['icon' => 'location', 'title' => 'Expertos en CDMX', 'description' => 'Conocimiento profundo del mercado inmobiliario en las mejores colonias de la ciudad.'],
            ['icon' => 'chart', 'title' => 'Maximo retorno', 'description' => 'Valuacion profesional y estrategias de precio que maximizan tu inversion.'],
            ['icon' => 'clock', 'title' => 'Atencion personalizada', 'description' => 'Asesor dedicado disponible 7 dias a la semana para resolver todas tus dudas.'],
        ];
        $benefits = old('benefits_section', $settings->benefits_section ?? $defaultBenefits);
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>2. Beneficios / Diferenciadores</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="benefits_heading" class="form-control" value="{{ old('benefits_heading', $settings->benefits_heading ?? '') }}" placeholder="¿Por que elegirnos?">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="benefits_subheading" class="form-control" value="{{ old('benefits_subheading', $settings->benefits_subheading ?? '') }}" placeholder="Mas de una inmobiliaria, somos tu aliado estrategico...">
                </div>
            </div>

            @foreach($benefits as $i => $benefit)
            <div style="background:var(--bg); border-radius:var(--radius); padding:1rem; margin-bottom:0.75rem;">
                <div style="font-weight:600; font-size:0.85rem; color:var(--text-muted); margin-bottom:0.75rem;">Beneficio {{ $i + 1 }}</div>
                <div style="display:grid; grid-template-columns:auto 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Icono</label>
                        <select name="benefits_section[{{ $i }}][icon]" class="form-control" style="width:auto;">
                            <option value="shield" {{ ($benefit['icon'] ?? '') === 'shield' ? 'selected' : '' }}>Escudo</option>
                            <option value="location" {{ ($benefit['icon'] ?? '') === 'location' ? 'selected' : '' }}>Ubicacion</option>
                            <option value="chart" {{ ($benefit['icon'] ?? '') === 'chart' ? 'selected' : '' }}>Grafica</option>
                            <option value="clock" {{ ($benefit['icon'] ?? '') === 'clock' ? 'selected' : '' }}>Reloj</option>
                            <option value="star" {{ ($benefit['icon'] ?? '') === 'star' ? 'selected' : '' }}>Estrella</option>
                            <option value="heart" {{ ($benefit['icon'] ?? '') === 'heart' ? 'selected' : '' }}>Corazon</option>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label">Titulo</label>
                            <input type="text" name="benefits_section[{{ $i }}][title]" class="form-control" value="{{ $benefit['title'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Descripcion</label>
                            <input type="text" name="benefits_section[{{ $i }}][description]" class="form-control" value="{{ $benefit['description'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 3. MODELO DE NEGOCIO --}}
    {{-- ========================================== --}}
    @php
        $defaultSteps = [
            ['num' => '01', 'title' => 'Identificamos la demanda', 'description' => 'Analizamos las necesidades de desarrolladores e inversionistas activos.'],
            ['num' => '02', 'title' => 'Captamos activos alineados', 'description' => 'Seleccionamos propiedades que cumplen criterios especificos de la demanda.'],
            ['num' => '03', 'title' => 'Ejecutamos la operacion', 'description' => 'Negociacion, blindaje legal y cierre eficiente.'],
        ];
        $bmSteps = old('business_model_steps', $settings->business_model_steps ?? $defaultSteps);
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>3. Modelo de Negocio</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="business_model_heading" class="form-control" value="{{ old('business_model_heading', $settings->business_model_heading ?? '') }}" placeholder="Modelo demand-driven">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="business_model_subheading" class="form-control" value="{{ old('business_model_subheading', $settings->business_model_subheading ?? '') }}" placeholder="No empezamos con la oferta. Empezamos con la demanda.">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Texto explicativo</label>
                <textarea name="business_model_content" class="form-control" rows="3" placeholder="Descripcion del modelo de negocio...">{{ old('business_model_content', $settings->business_model_content ?? '') }}</textarea>
            </div>
            <p class="form-hint" style="margin:1rem 0 0.75rem;">Pasos del proceso (max 5)</p>
            @for($i = 0; $i < 5; $i++)
            @php $step = $bmSteps[$i] ?? null; @endphp
            @if($step || $i < 3)
            <div style="background:var(--bg); border-radius:var(--radius); padding:0.75rem 1rem; margin-bottom:0.5rem;">
                <div style="display:grid; grid-template-columns:60px 1fr 2fr; gap:0.75rem;">
                    <div class="form-group">
                        <label class="form-label">Num</label>
                        <input type="text" name="business_model_steps[{{ $i }}][num]" class="form-control" value="{{ $step['num'] ?? '' }}" placeholder="{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="business_model_steps[{{ $i }}][title]" class="form-control" value="{{ $step['title'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripcion</label>
                        <input type="text" name="business_model_steps[{{ $i }}][description]" class="form-control" value="{{ $step['description'] ?? '' }}">
                    </div>
                </div>
            </div>
            @endif
            @endfor
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 4. PROPIEDADES DESTACADAS --}}
    {{-- ========================================== --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>4. Propiedades Destacadas</h3></div>
        <div class="card-body">
            <p class="form-hint" style="margin-bottom:1rem;">Las propiedades se muestran automaticamente desde la base de datos. Aqui solo editas el titulo y subtitulo.</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="featured_heading" class="form-control" value="{{ old('featured_heading', $settings->featured_heading ?? '') }}" placeholder="Propiedades destacadas">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="featured_subheading" class="form-control" value="{{ old('featured_subheading', $settings->featured_subheading ?? '') }}" placeholder="Las mejores oportunidades del mercado inmobiliario en CDMX.">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 5. SERVICIOS --}}
    {{-- ========================================== --}}
    @php
        $defaultServices = [
            ['title' => 'Compra', 'description' => 'Encuentra la propiedad perfecta con nuestro catalogo exclusivo.', 'features' => ['Busqueda personalizada', 'Analisis de inversion', 'Acompanamiento legal'], 'link_text' => 'Ver propiedades en venta', 'link_url' => '/propiedades?operation_type=sale', 'highlighted' => false],
            ['title' => 'Venta', 'description' => 'Vendemos tu propiedad al mejor precio del mercado.', 'features' => ['Valuacion profesional', 'Marketing y fotografia', 'Cierre de escrituras'], 'link_text' => 'Solicitar valuacion gratis', 'link_url' => '#contacto', 'highlighted' => true],
            ['title' => 'Renta', 'description' => 'Encuentra el espacio ideal para vivir o trabajar.', 'features' => ['Propiedades verificadas', 'Contratos transparentes', 'Administracion de renta'], 'link_text' => 'Ver propiedades en renta', 'link_url' => '/propiedades?operation_type=rental', 'highlighted' => false],
        ];
        $services = old('services_section', $settings->services_section ?? $defaultServices);
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>5. Servicios</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="services_heading" class="form-control" value="{{ old('services_heading', $settings->services_heading ?? '') }}" placeholder="Nuestros servicios">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="services_subheading" class="form-control" value="{{ old('services_subheading', $settings->services_subheading ?? '') }}" placeholder="Soluciones integrales para cualquier necesidad inmobiliaria.">
                </div>
            </div>

            @foreach($services as $i => $service)
            <div style="background:var(--bg); border-radius:var(--radius); padding:1rem; margin-bottom:0.75rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem;">
                    <span style="font-weight:600; font-size:0.85rem; color:var(--text-muted);">Servicio {{ $i + 1 }}</span>
                    <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; cursor:pointer;">
                        <input type="checkbox" name="services_section[{{ $i }}][highlighted]" value="1" {{ !empty($service['highlighted']) ? 'checked' : '' }}>
                        Destacado
                    </label>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="services_section[{{ $i }}][title]" class="form-control" value="{{ $service['title'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripcion</label>
                        <input type="text" name="services_section[{{ $i }}][description]" class="form-control" value="{{ $service['description'] ?? '' }}">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem; margin-top:0.75rem;">
                    @foreach(($service['features'] ?? ['', '', '']) as $fi => $feature)
                    <div class="form-group">
                        <label class="form-label">Caracteristica {{ $fi + 1 }}</label>
                        <input type="text" name="services_section[{{ $i }}][features][]" class="form-control" value="{{ $feature }}">
                    </div>
                    @endforeach
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:0.75rem;">
                    <div class="form-group">
                        <label class="form-label">Texto del enlace</label>
                        <input type="text" name="services_section[{{ $i }}][link_text]" class="form-control" value="{{ $service['link_text'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">URL del enlace</label>
                        <input type="text" name="services_section[{{ $i }}][link_url]" class="form-control" value="{{ $service['link_url'] ?? '' }}">
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 6. ESTADISTICAS --}}
    {{-- ========================================== --}}
    @php
        $defaultStats = [
            ['value' => '30+', 'label' => 'Anos de experiencia senior'],
            ['value' => '200+', 'label' => 'Propiedades gestionadas'],
            ['value' => '98%', 'label' => 'Clientes satisfechos'],
            ['value' => '50+', 'label' => 'Operaciones al ano'],
        ];
        $statsSection = old('stats_section', $settings->stats_section ?? $defaultStats);
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>6. Estadisticas</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="stats_heading" class="form-control" value="{{ old('stats_heading', $settings->stats_heading ?? '') }}" placeholder="Cifras que respaldan">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="stats_subheading" class="form-control" value="{{ old('stats_subheading', $settings->stats_subheading ?? '') }}" placeholder="Resultados consistentes en cada operacion">
                </div>
            </div>
            @for($i = 0; $i < 6; $i++)
            @php $stat = $statsSection[$i] ?? null; @endphp
            @if($stat || $i < 4)
            <div style="display:grid; grid-template-columns:120px 1fr; gap:0.75rem; background:var(--bg); border-radius:var(--radius); padding:0.6rem 1rem; margin-bottom:0.4rem;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Valor</label>
                    <input type="text" name="stats_section[{{ $i }}][value]" class="form-control" value="{{ $stat['value'] ?? '' }}" placeholder="30+">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Etiqueta</label>
                    <input type="text" name="stats_section[{{ $i }}][label]" class="form-control" value="{{ $stat['label'] ?? '' }}" placeholder="Anos de experiencia">
                </div>
            </div>
            @endif
            @endfor
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 7. TESTIMONIOS --}}
    {{-- ========================================== --}}
    @php
        $defaultTestimonials = [
            ['name' => 'Maria Gonzalez', 'role' => 'Compradora en Del Valle', 'text' => 'Encontraron el departamento perfecto para mi familia en menos de dos semanas.', 'initials' => 'MG'],
            ['name' => 'Carlos Ramirez', 'role' => 'Vendedor en Narvarte', 'text' => 'Vendieron mi propiedad en tiempo record y a un precio superior al que esperaba.', 'initials' => 'CR'],
            ['name' => 'Ana Martinez', 'role' => 'Inquilina en Condesa', 'text' => 'El proceso de renta fue muy sencillo. Totalmente recomendados.', 'initials' => 'AM'],
        ];
        $testimonials = old('testimonials_section', $settings->testimonials_section ?? $defaultTestimonials);
    @endphp
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>7. Testimonios</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="testimonials_heading" class="form-control" value="{{ old('testimonials_heading', $settings->testimonials_heading ?? '') }}" placeholder="Lo que dicen nuestros clientes">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="testimonials_subheading" class="form-control" value="{{ old('testimonials_subheading', $settings->testimonials_subheading ?? '') }}" placeholder="La satisfaccion de nuestros clientes es nuestra mejor carta de presentacion.">
                </div>
            </div>

            @foreach($testimonials as $i => $testimonial)
            <div style="background:var(--bg); border-radius:var(--radius); padding:1rem; margin-bottom:0.75rem;">
                <div style="font-weight:600; font-size:0.85rem; color:var(--text-muted); margin-bottom:0.75rem;">Testimonio {{ $i + 1 }}</div>
                <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:1rem;">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="testimonials_section[{{ $i }}][name]" class="form-control" value="{{ $testimonial['name'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rol / Ubicacion</label>
                        <input type="text" name="testimonials_section[{{ $i }}][role]" class="form-control" value="{{ $testimonial['role'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Iniciales</label>
                        <input type="text" name="testimonials_section[{{ $i }}][initials]" class="form-control" maxlength="4" style="width:70px;" value="{{ $testimonial['initials'] ?? '' }}">
                    </div>
                </div>
                <div class="form-group" style="margin-top:0.75rem;">
                    <label class="form-label">Testimonio</label>
                    <textarea name="testimonials_section[{{ $i }}][text]" class="form-control" rows="2">{{ $testimonial['text'] ?? '' }}</textarea>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 8. CONTACTO / REDES SOCIALES --}}
    {{-- ========================================== --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>8. Seccion de Contacto</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.25rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="contact_heading" class="form-control" value="{{ old('contact_heading', $settings->contact_heading ?? '') }}" placeholder="Listo para dar el siguiente paso?">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="contact_subheading" class="form-control" value="{{ old('contact_subheading', $settings->contact_subheading ?? '') }}" placeholder="Nuestro equipo de expertos esta aqui para ayudarte.">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Telefono</label>
                    <input type="text" name="contact_phone" class="form-control" value="{{ old('contact_phone', $settings->contact_phone ?? '') }}" placeholder="+52 55 1234 5678">
                </div>
                <div class="form-group">
                    <label class="form-label">Email de contacto</label>
                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $settings->contact_email ?? '') }}" placeholder="contacto@homedelvalle.com">
                </div>
                <div class="form-group">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $settings->whatsapp_number ?? '') }}" placeholder="+5255XXXXXXXX">
                </div>
                <div class="form-group">
                    <label class="form-label">Direccion</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $settings->address ?? '') }}" placeholder="Col. Del Valle, CDMX">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-top:1rem;">
                <div class="form-group">
                    <label class="form-label">Facebook URL</label>
                    <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $settings->facebook_url ?? '') }}" placeholder="https://facebook.com/...">
                </div>
                <div class="form-group">
                    <label class="form-label">Instagram URL</label>
                    <input type="url" name="instagram_url" class="form-control" value="{{ old('instagram_url', $settings->instagram_url ?? '') }}" placeholder="https://instagram.com/...">
                </div>
                <div class="form-group">
                    <label class="form-label">TikTok URL</label>
                    <input type="url" name="tiktok_url" class="form-control" value="{{ old('tiktok_url', $settings->tiktok_url ?? '') }}" placeholder="https://tiktok.com/...">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 9. BLOG --}}
    {{-- ========================================== --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>9. Blog</h3></div>
        <div class="card-body">
            <p class="form-hint" style="margin-bottom:1rem;">Los articulos se muestran automaticamente desde el blog. Aqui solo editas el titulo y subtitulo de la seccion.</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo de seccion</label>
                    <input type="text" name="blog_heading" class="form-control" value="{{ old('blog_heading', $settings->blog_heading ?? '') }}" placeholder="Ultimos articulos">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="blog_subheading" class="form-control" value="{{ old('blog_subheading', $settings->blog_subheading ?? '') }}" placeholder="Consejos, tendencias y guias del mercado inmobiliario.">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 10. CTA FINAL --}}
    {{-- ========================================== --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>10. CTA Final</h3></div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Titulo</label>
                    <input type="text" name="cta_heading" class="form-control" value="{{ old('cta_heading', $settings->cta_heading ?? '') }}" placeholder="Comienza tu busqueda hoy">
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitulo</label>
                    <input type="text" name="cta_subheading" class="form-control" value="{{ old('cta_subheading', $settings->cta_subheading ?? '') }}" placeholder="Miles de propiedades te esperan.">
                </div>
            </div>
        </div>
    </div>

    {{-- 11. NAVBAR CTA --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card-header"><h3>11. Boton CTA en Navegacion</h3></div>
        <div class="card-body">
            <p class="form-hint" style="margin-bottom:1rem;">Configura el boton de accion que aparece en la barra de navegacion del sitio publico.</p>
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.88rem; cursor:pointer;">
                    <input type="checkbox" name="navbar_cta_enabled" value="1" {{ old('navbar_cta_enabled', $settings->navbar_cta_enabled ?? true) ? 'checked' : '' }}>
                    Activar boton CTA en navbar
                </label>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Texto del boton</label>
                    <input type="text" name="navbar_cta_text" class="form-control" value="{{ old('navbar_cta_text', $settings->navbar_cta_text ?? '') }}" placeholder="Valua tu propiedad">
                </div>
                <div class="form-group">
                    <label class="form-label">URL del boton</label>
                    <input type="text" name="navbar_cta_url" class="form-control" value="{{ old('navbar_cta_url', $settings->navbar_cta_url ?? '') }}" placeholder="/vende-tu-propiedad">
                </div>
            </div>
        </div>
    </div>

    {{-- 12. PLANTILLAS DE DISEÑO --}}
    <div class="card" style="margin-bottom:2rem;">
        <div class="card-header">
            <h3 class="card-title">&#127912; 12. Plantillas de Diseño</h3>
        </div>
        <div class="card-body">
            <p style="color:var(--text-muted); font-size:0.85rem; margin-bottom:1.5rem;">Selecciona el diseño visual para cada sección del sitio público. Los cambios se reflejan inmediatamente.</p>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:1.5rem;">
                <div class="form-group">
                    <label class="form-label">Listado de Propiedades</label>
                    <select name="property_listing_template" class="form-control">
                        <option value="grid" {{ old('property_listing_template', $settings->property_listing_template ?? 'grid') == 'grid' ? 'selected' : '' }}>Grid — 3 columnas</option>
                        <option value="list" {{ old('property_listing_template', $settings->property_listing_template ?? '') == 'list' ? 'selected' : '' }}>Lista — Cards horizontales</option>
                        <option value="magazine" {{ old('property_listing_template', $settings->property_listing_template ?? '') == 'magazine' ? 'selected' : '' }}>Magazine — Destacado + grid</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Detalle de Propiedad</label>
                    <select name="property_detail_template" class="form-control">
                        <option value="sidebar" {{ old('property_detail_template', $settings->property_detail_template ?? 'sidebar') == 'sidebar' ? 'selected' : '' }}>Sidebar — Columna lateral</option>
                        <option value="fullwidth" {{ old('property_detail_template', $settings->property_detail_template ?? '') == 'fullwidth' ? 'selected' : '' }}>Full-width — Galería completa</option>
                        <option value="gallery" {{ old('property_detail_template', $settings->property_detail_template ?? '') == 'gallery' ? 'selected' : '' }}>Galería — Mosaico de imágenes</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Blog</label>
                    <select name="blog_template" class="form-control">
                        <option value="grid" {{ old('blog_template', $settings->blog_template ?? 'grid') == 'grid' ? 'selected' : '' }}>Grid — 3 columnas</option>
                        <option value="list" {{ old('blog_template', $settings->blog_template ?? '') == 'list' ? 'selected' : '' }}>Lista — Cards verticales</option>
                        <option value="magazine" {{ old('blog_template', $settings->blog_template ?? '') == 'magazine' ? 'selected' : '' }}>Magazine — Destacado + grid</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- SUBMIT --}}
    <div class="form-actions" style="position:sticky; bottom:0; z-index:10; background:var(--body-bg); border-top:1px solid var(--border); padding:1rem 0;">
        <button type="submit" class="btn btn-primary" style="gap:0.5rem;">
            <x-icon name="check" class="w-4 h-4" />
            Guardar Homepage
        </button>
    </div>
</form>
@endsection
