<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Técnica - {{ $property->title ?? 'Propiedad' }}</title>
    <style>
        /* ============================================
           RESET Y CONFIGURACIÓN BASE PARA PDF
           ============================================ */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2c2c2c;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================
           COLORES CORPORATIVOS HOMEDELVALLE
           ============================================ */

        :root {
            --primary-dark: #1a3a52;      /* Azul marino profundo */
            --primary-light: #0066cc;     /* Azul eléctrico */
            --neutral-light: #f8f9fa;     /* Blanco cálido */
            --neutral-medium: #e9ecef;    /* Gris claro */
            --neutral-dark: #495057;      /* Gris oscuro */
            --text-primary: #2c2c2c;      /* Texto principal */
            --text-secondary: #6c757d;    /* Texto secundario */
            --border-color: #dee2e6;      /* Bordes */
            --accent: #0066cc;            /* Acento azul */
        }

        /* ============================================
           PÁGINA Y MÁRGENES
           ============================================ */

        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 0;
            background: white;
            page-break-after: always;
            position: relative;
            overflow: hidden;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .page-content {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: white;
        }

        /* ============================================
           ENCABEZADO / HEADER
           ============================================ */

        .header {
            padding: 20mm 20mm 0;
            border-bottom: 2px solid var(--primary-dark);
            margin-bottom: 0;
            position: relative;
            background: white;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        .logo img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .header-info {
            text-align: right;
            font-size: 9px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .header-info strong {
            color: var(--primary-dark);
            font-weight: 600;
        }

        /* ============================================
           HERO SECTION - IMAGEN PRINCIPAL
           ============================================ */

        .hero-section {
            width: 100%;
            height: 140mm;
            background: linear-gradient(135deg, var(--neutral-light) 0%, var(--neutral-medium) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            margin-bottom: 0;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .hero-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4f8 0%, #e9ecef 100%);
            color: var(--text-secondary);
            font-size: 14px;
            text-align: center;
            padding: 20px;
        }

        /* ============================================
           INFORMACIÓN PRINCIPAL - NOMBRE, PRECIO, TIPO
           ============================================ */

        .main-info {
            padding: 20mm 20mm 15mm;
            background: white;
        }

        .property-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 12px;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }

        .property-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 18px;
            flex-wrap: wrap;
            align-items: center;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meta-label {
            font-size: 10px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .meta-value {
            font-size: 12px;
            color: var(--text-primary);
            font-weight: 500;
        }

        .meta-separator {
            width: 1px;
            height: 20px;
            background: var(--border-color);
        }

        .property-location {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 20px;
            display: flex;
            gap: 5px;
        }

        .location-icon::before {
            content: "📍";
            margin-right: 5px;
        }

        /* SECCIÓN DE PRECIO - DESTACADA */
        .price-section {
            background: var(--primary-dark);
            color: white;
            padding: 15mm 20mm;
            margin: 0 20mm 15mm;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            font-weight: 600;
        }

        .price-value {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .price-currency {
            font-size: 16px;
            font-weight: 300;
            vertical-align: super;
        }

        .operation-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ============================================
           CARACTERÍSTICAS CLAVE - GRID
           ============================================ */

        .key-features {
            padding: 0 20mm 20mm;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20mm;
        }

        .feature-card {
            background: var(--neutral-light);
            padding: 12px 10px;
            text-align: center;
            border-left: 3px solid var(--primary-dark);
            border-radius: 2px;
        }

        .feature-icon {
            font-size: 20px;
            margin-bottom: 6px;
            display: block;
        }

        .feature-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 3px;
        }

        .feature-label {
            font-size: 9px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-weight: 600;
        }

        /* ============================================
           SECCIÓN DE DESCRIPCIÓN
           ============================================ */

        .content-section {
            padding: 0 20mm 15mm;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--primary-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 12px;
        }

        .description-text {
            font-size: 10px;
            color: var(--text-primary);
            line-height: 1.7;
            margin-bottom: 15mm;
            text-align: justify;
        }

        /* ============================================
           ESPECIFICACIONES TÉCNICAS
           ============================================ */

        .specifications {
            padding: 0 20mm 15mm;
        }

        .spec-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 20px;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .spec-item:last-child {
            border-bottom: none;
        }

        .spec-label {
            font-size: 9px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.4px;
        }

        .spec-value {
            font-size: 11px;
            color: var(--text-primary);
            font-weight: 600;
        }

        /* ============================================
           GALERÍA DE IMÁGENES
           ============================================ */

        .gallery-section {
            padding: 0 20mm 15mm;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 12px;
        }

        .gallery-item {
            position: relative;
            width: 100%;
            padding-bottom: 100%;
            background: var(--neutral-light);
            border-radius: 3px;
            overflow: hidden;
        }

        .gallery-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .gallery-placeholder {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4f8 0%, #e9ecef 100%);
            color: var(--text-secondary);
            font-size: 10px;
            text-align: center;
            padding: 10px;
        }

        /* ============================================
           SECCIÓN AMENIDADES / OBSERVACIONES
           ============================================ */

        .amenities-section {
            padding: 0 20mm 15mm;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .amenity-item {
            display: flex;
            gap: 8px;
            font-size: 10px;
            color: var(--text-primary);
            line-height: 1.5;
        }

        .amenity-check::before {
            content: "✓";
            color: var(--primary-light);
            font-weight: bold;
            min-width: 12px;
        }

        /* ============================================
           PAGE BREAK Y ESPACIOS
           ============================================ */

        .page-break {
            page-break-before: always;
        }

        .spacer {
            flex-grow: 1;
        }

        /* ============================================
           FOOTER / BLOQUE INSTITUCIONAL
           ============================================ */

        .footer-section {
            border-top: 2px solid var(--primary-dark);
            padding: 12mm 20mm;
            margin-top: auto;
            background: var(--neutral-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .footer-contact {
            flex: 1;
        }

        .footer-contact-title {
            font-size: 10px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .footer-contact-item {
            font-size: 9px;
            color: var(--text-primary);
            margin-bottom: 3px;
            line-height: 1.4;
        }

        .footer-contact-item strong {
            color: var(--primary-dark);
            font-weight: 600;
        }

        .footer-qr {
            text-align: center;
        }

        .qr-image {
            width: 50mm;
            height: 50mm;
            border: 2px solid white;
            border-radius: 3px;
            margin-bottom: 6px;
        }

        .qr-label {
            font-size: 8px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
            line-height: 1.3;
        }

        /* ============================================
           BLOQUE BROKER (OPCIONAL)
           ============================================ */

        .broker-section {
            padding: 12mm 20mm;
            background: white;
            border-top: 1px solid var(--border-color);
            margin-top: 10mm;
        }

        .broker-title {
            font-size: 9px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        .broker-card {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .broker-photo {
            width: 60px;
            height: 60px;
            border-radius: 3px;
            overflow: hidden;
            background: var(--neutral-light);
            flex-shrink: 0;
        }

        .broker-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .broker-photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f4f8 0%, #e9ecef 100%);
            color: var(--text-secondary);
            font-size: 8px;
            text-align: center;
            padding: 5px;
        }

        .broker-info {
            flex: 1;
        }

        .broker-name {
            font-size: 12px;
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 4px;
        }

        .broker-position {
            font-size: 9px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-bottom: 5px;
        }

        .broker-contact {
            font-size: 9px;
            color: var(--text-primary);
            line-height: 1.5;
        }

        .broker-contact-item {
            margin-bottom: 2px;
        }

        .broker-contact-label {
            color: var(--text-secondary);
            font-weight: 600;
        }

        /* ============================================
           NOTA LEGAL / DISCLAIMER
           ============================================ */

        .legal-notice {
            padding: 10mm 20mm;
            background: var(--primary-dark);
            color: white;
            font-size: 7px;
            line-height: 1.5;
            text-align: center;
            margin-top: 10mm;
            border-top: 2px solid var(--primary-light);
        }

        .legal-notice p {
            margin: 0;
        }

        /* ============================================
           TIPOGRAFÍA Y UTILIDADES
           ============================================ */

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: var(--text-secondary);
        }

        .mt-0 { margin-top: 0; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }

        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }

        /* ============================================
           OCULTAR ELEMENTOS VACÍOS
           ============================================ */

        .hidden {
            display: none !important;
        }

        /* ============================================
           RESPONSIVE PARA IMPRESIÓN
           ============================================ */

        @media print {
            body, html {
                margin: 0;
                padding: 0;
            }

            .page {
                margin: 0;
                box-shadow: none;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }

    </style>
</head>
<body>

    <!-- =====================================================
         PÁGINA 1: PORTADA + INFORMACIÓN PRINCIPAL
         ===================================================== -->

    <div class="page">
        <div class="page-content">
            <!-- Header Corporativo -->
            <div class="header">
                <div class="header-top">
                    <div class="logo">
                        @if(file_exists(public_path('images/logo-homedelvalle.png')))
                            <img src="{{ public_path('images/logo-homedelvalle.png') }}" alt="Home del Valle">
                        @else
                            <div style="font-weight: 700; color: var(--primary-dark); font-size: 18px;">
                                HOME<br>DEL VALLE
                            </div>
                        @endif
                    </div>
                    <div class="header-info">
                        <div><strong>HOME DEL VALLE</strong></div>
                        <div>Inmobiliaria Premium</div>
                        <div>📱 +52 55 1234 5678</div>
                        <div>✉️ info@homedelvalle.mx</div>
                    </div>
                </div>
            </div>

            <!-- Hero Image Section -->
            <div class="hero-section">
                @if($property->images && $property->images->first())
                    <img src="{{ public_path('storage/' . $property->images->first()->path) }}" alt="{{ $property->title }}" class="hero-image">
                @else
                    <div class="hero-image-placeholder">
                        Imagen no disponible
                    </div>
                @endif
            </div>

            <!-- Main Information -->
            <div class="main-info">
                <h1 class="property-title">{{ $property->title ?? 'Propiedad Premium' }}</h1>

                <div class="property-location">
                    <span class="location-icon"></span>
                    {{ $property->colonia ?? '' }}
                    @if($property->alcaldia)
                        • {{ $property->alcaldia }}
                    @endif
                    @if($property->ciudad)
                        • {{ $property->ciudad }}
                    @endif
                </div>

                <div class="property-meta">
                    <div class="meta-item">
                        <span class="meta-label">Operación</span>
                        <span class="meta-value">{{ ucfirst($property->operacion ?? 'N/A') }}</span>
                    </div>
                    <div class="meta-separator"></div>
                    <div class="meta-item">
                        <span class="meta-label">Tipo</span>
                        <span class="meta-value">{{ ucfirst($property->tipo_propiedad ?? 'Propiedad') }}</span>
                    </div>
                </div>
            </div>

            <!-- Price Section - Destacada -->
            <div class="price-section">
                <div>
                    <div class="price-label">Precio</div>
                    <div class="price-value">
                        <span class="price-currency">{{ $property->moneda ?? 'MXN' }}</span>
                        {{ $property->precio ? '$' . number_format($property->precio, 0) : 'Contacto' }}
                    </div>
                </div>
                <div class="operation-badge">
                    {{ strtoupper($property->operacion ?? 'Venta') }}
                </div>
            </div>

            <!-- Key Features Grid -->
            <div class="key-features">
                @if($property->terreno_m2)
                    <div class="feature-card">
                        <span class="feature-icon">📐</span>
                        <div class="feature-value">{{ number_format($property->terreno_m2, 0) }}</div>
                        <div class="feature-label">M² Terreno</div>
                    </div>
                @endif

                @if($property->construccion_m2)
                    <div class="feature-card">
                        <span class="feature-icon">🏗️</span>
                        <div class="feature-value">{{ number_format($property->construccion_m2, 0) }}</div>
                        <div class="feature-label">M² Construido</div>
                    </div>
                @endif

                @if($property->recamaras)
                    <div class="feature-card">
                        <span class="feature-icon">🛏️</span>
                        <div class="feature-value">{{ $property->recamaras }}</div>
                        <div class="feature-label">Recámaras</div>
                    </div>
                @endif

                @if($property->baños)
                    <div class="feature-card">
                        <span class="feature-icon">🚿</span>
                        <div class="feature-value">{{ $property->baños }}</div>
                        <div class="feature-label">Baños</div>
                    </div>
                @endif
            </div>

            <!-- Footer Institucional -->
            <div class="footer-section">
                <div class="footer-contact">
                    <div class="footer-contact-title">HOME DEL VALLE</div>
                    <div class="footer-contact-item">
                        <strong>Inmobiliaria Premium</strong>
                    </div>
                    <div class="footer-contact-item">
                        📱 +52 55 1234 5678
                    </div>
                    <div class="footer-contact-item">
                        ✉️ info@homedelvalle.mx
                    </div>
                </div>

                @if($property->qr_path)
                    <div class="footer-qr">
                        <img src="{{ public_path('storage/' . $property->qr_path) }}" alt="QR Propiedad" class="qr-image">
                        <div class="qr-label">Escanea para<br>más información</div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- =====================================================
         PÁGINA 2: INFORMACIÓN DETALLADA
         ===================================================== -->

    <div class="page">
        <div class="page-content">
            <!-- Header -->
            <div class="header">
                <div style="padding: 10px 0; text-align: right;">
                    <span style="font-size: 10px; color: var(--text-secondary);">Ficha Técnica Completa</span>
                </div>
            </div>

            <!-- Descripción -->
            @if($property->descripcion)
                <div class="content-section">
                    <h2 class="section-title">Descripción</h2>
                    <p class="description-text">{{ $property->descripcion }}</p>
                </div>
            @endif

            <!-- Especificaciones Técnicas -->
            <div class="specifications">
                <h2 class="section-title">Especificaciones Técnicas</h2>
                <div class="spec-grid">
                    @if($property->terreno_m2)
                        <div class="spec-item">
                            <span class="spec-label">Terreno</span>
                            <span class="spec-value">{{ number_format($property->terreno_m2, 2) }} m²</span>
                        </div>
                    @endif

                    @if($property->construccion_m2)
                        <div class="spec-item">
                            <span class="spec-label">Área Construida</span>
                            <span class="spec-value">{{ number_format($property->construccion_m2, 2) }} m²</span>
                        </div>
                    @endif

                    @if($property->recamaras)
                        <div class="spec-item">
                            <span class="spec-label">Recámaras</span>
                            <span class="spec-value">{{ $property->recamaras }}</span>
                        </div>
                    @endif

                    @if($property->baños)
                        <div class="spec-item">
                            <span class="spec-label">Baños</span>
                            <span class="spec-value">{{ $property->baños }}</span>
                        </div>
                    @endif

                    @if($property->medios_baños)
                        <div class="spec-item">
                            <span class="spec-label">Medios Baños</span>
                            <span class="spec-value">{{ $property->medios_baños }}</span>
                        </div>
                    @endif

                    @if($property->estacionamientos)
                        <div class="spec-item">
                            <span class="spec-label">Estacionamientos</span>
                            <span class="spec-value">{{ $property->estacionamientos }}</span>
                        </div>
                    @endif

                    @if($property->antigüedad)
                        <div class="spec-item">
                            <span class="spec-label">Antigüedad</span>
                            <span class="spec-value">{{ $property->antigüedad }}</span>
                        </div>
                    @endif

                    @if($property->nivel)
                        <div class="spec-item">
                            <span class="spec-label">Nivel</span>
                            <span class="spec-value">{{ $property->nivel }}</span>
                        </div>
                    @endif

                    @if($property->uso_suelo)
                        <div class="spec-item">
                            <span class="spec-label">Uso de Suelo</span>
                            <span class="spec-value">{{ $property->uso_suelo }}</span>
                        </div>
                    @endif

                    @if($property->estado_conservacion)
                        <div class="spec-item">
                            <span class="spec-label">Estado</span>
                            <span class="spec-value">{{ $property->estado_conservacion }}</span>
                        </div>
                    @endif

                    @if($property->estatus_legal)
                        <div class="spec-item">
                            <span class="spec-label">Estatus Legal</span>
                            <span class="spec-value">{{ $property->estatus_legal }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Amenidades -->
            @if($property->amenidades)
                <div class="amenities-section">
                    <h2 class="section-title">Amenidades</h2>
                    <div class="amenities-grid">
                        @php
                            $amenidades = is_array($property->amenidades)
                                ? $property->amenidades
                                : array_filter(array_map('trim', explode(',', $property->amenidades)));
                        @endphp
                        @foreach($amenidades as $amenidad)
                            @if(trim($amenidad))
                                <div class="amenity-item">
                                    <span class="amenity-check"></span>
                                    <span>{{ trim($amenidad) }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Observaciones -->
            @if($property->observaciones)
                <div class="content-section">
                    <h2 class="section-title">Observaciones</h2>
                    <p class="description-text">{{ $property->observaciones }}</p>
                </div>
            @endif

            <div class="spacer"></div>

            <!-- Footer Page 2 -->
            <div class="footer-section">
                <div class="footer-contact">
                    <div class="footer-contact-title">INFORMACIÓN ADICIONAL</div>
                    <div class="footer-contact-item">
                        Para más detalles o agendar una visita,<br>contacte con nuestro equipo.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- =====================================================
         PÁGINA 3: GALERÍA DE IMÁGENES
         ===================================================== -->

    @if($property->images && $property->images->count() > 1)
        <div class="page">
            <div class="page-content">
                <!-- Header -->
                <div class="header">
                    <div style="padding: 10px 0; text-align: right;">
                        <span style="font-size: 10px; color: var(--text-secondary);">Galería de Imágenes</span>
                    </div>
                </div>

                <!-- Gallery Section -->
                <div class="gallery-section" style="margin-top: 20mm;">
                    <h2 class="section-title">Galería Fotográfica</h2>
                    <div class="gallery-grid">
                        @foreach($property->images->skip(1) as $image)
                            @if($loop->index < 9)
                                <div class="gallery-item">
                                    @if(file_exists(public_path('storage/' . $image->path)))
                                        <img src="{{ public_path('storage/' . $image->path) }}" alt="Imagen propiedad" class="gallery-image">
                                    @else
                                        <div class="gallery-placeholder">
                                            Sin imagen
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="spacer"></div>

                <!-- Footer -->
                <div class="footer-section">
                    <div class="footer-contact">
                        <div class="footer-contact-title">HOME DEL VALLE</div>
                        <div class="footer-contact-item">
                            Conoce nuestro portafolio completo de propiedades premium.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- =====================================================
         PÁGINA FINAL: BROKER + CONTACTO + LEGAL
         ===================================================== -->

    <div class="page">
        <div class="page-content">
            <!-- Header -->
            <div class="header">
                <div style="padding: 10px 0; text-align: right;">
                    <span style="font-size: 10px; color: var(--text-secondary);">Información de Contacto</span>
                </div>
            </div>

            <!-- Broker Section (Condicional) -->
            @if($includeBroker && $broker)
                <div class="broker-section">
                    <div class="broker-title">Asesor Inmobiliario</div>
                    <div class="broker-card">
                        <div class="broker-photo">
                            @if($broker->profile_photo_url && file_exists(public_path('storage/' . $broker->profile_photo_url)))
                                <img src="{{ public_path('storage/' . $broker->profile_photo_url) }}" alt="{{ $broker->name }}">
                            @elseif($broker->photo_path && file_exists(public_path('storage/' . $broker->photo_path)))
                                <img src="{{ public_path('storage/' . $broker->photo_path) }}" alt="{{ $broker->name }}">
                            @else
                                <div class="broker-photo-placeholder">
                                    👤
                                </div>
                            @endif
                        </div>
                        <div class="broker-info">
                            <div class="broker-name">
                                {{ $broker->name ?? 'Asesor' }}
                                @if($broker->last_name) {{ $broker->last_name }} @endif
                            </div>
                            @if($broker->position || $broker->role)
                                <div class="broker-position">
                                    {{ $broker->position ?? $broker->role ?? 'Agente Inmobiliario' }}
                                </div>
                            @endif
                            <div class="broker-contact">
                                @if($broker->phone || $broker->mobile)
                                    <div class="broker-contact-item">
                                        <span class="broker-contact-label">📱</span>
                                        {{ $broker->phone ?? $broker->mobile }}
                                    </div>
                                @endif
                                @if($broker->email)
                                    <div class="broker-contact-item">
                                        <span class="broker-contact-label">✉️</span>
                                        {{ $broker->email }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Spacer -->
            <div style="flex: 1;"></div>

            <!-- Legal Notice -->
            <div class="legal-notice">
                <p>
                    <strong>HOME DEL VALLE</strong> — Inmobiliaria Premium
                </p>
                <p style="margin-top: 5px; opacity: 0.9;">
                    La información contenida en esta ficha técnica es proporcionada únicamente con fines informativos.
                    Los precios, disponibilidades y características están sujetos a cambios sin previo aviso.
                    Se recomienda verificar todos los datos directamente con el asesor inmobiliario.
                    Para transacciones inmobiliarias, consulte con profesionales legales.
                </p>
                <p style="margin-top: 5px; opacity: 0.85;">
                    © {{ now()->year }} Home del Valle. Todos los derechos reservados. | www.homedelvalle.mx
                </p>
            </div>

        </div>
    </div>

</body>
</html>
