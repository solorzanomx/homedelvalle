@extends('layouts.public')

@section('meta')
    <x-public.seo-meta
        title="Solicitud recibida — {{ $siteSettings?->site_name ?? 'Home del Valle' }}"
        description="Recibimos tu solicitud. Te responderemos pronto."
        :canonical="url('/gracias')"
        :noindex="true"
    />
@endsection

@section('content')
@php
    $formType   = session('form_type', 'contacto');
    $clientName = session('client_name', '');
    $folio      = session('folio', '');

    $config = match($formType) {
        'vendedor' => [
            'titulo'   => '¡Recibimos tu solicitud de valuación' . ($clientName ? ', ' . explode(' ', $clientName)[0] : '') . '!',
            'tiempo'   => '24 horas',
            'mensaje'  => 'Un asesor especializado revisará tu propiedad y te contactará por WhatsApp con tu valuación gratuita.',
            'pasos'    => [
                ['num' => '01', 'title' => 'Revisamos tu propiedad',    'desc' => 'Analizamos ubicación, metraje y mercado actual en Benito Juárez.'],
                ['num' => '02', 'title' => 'Te enviamos la valuación',   'desc' => 'Precio competitivo con base en datos reales, no estimados genéricos.'],
                ['num' => '03', 'title' => 'Diseñamos tu estrategia',    'desc' => 'Fotografía, marketing digital y red de compradores calificados.'],
            ],
            'wa_text'  => 'Hola, acabo de solicitar una valuación de mi propiedad en su sitio y quisiera hablar con un asesor.',
            'link'     => ['url' => '/mercado', 'label' => 'Ver precios de mercado →'],
        ],
        'comprador' => [
            'titulo'   => '¡Recibimos tu búsqueda' . ($clientName ? ', ' . explode(' ', $clientName)[0] : '') . '!',
            'tiempo'   => '72 horas',
            'mensaje'  => 'Vamos a curar las mejores opciones que coincidan con tu brief. Sin spam, sin catálogos masivos.',
            'pasos'    => [
                ['num' => '01', 'title' => 'Filtramos el mercado',       'desc' => 'Revisamos inventario propio, red de contactos y mercado abierto.'],
                ['num' => '02', 'title' => 'Selección curada',           'desc' => 'Solo te enviamos 3-5 opciones que realmente matchean con tu brief.'],
                ['num' => '03', 'title' => 'Te acompañamos al cierre',   'desc' => 'Negociación, due diligence legal y firma de escrituras incluidos.'],
            ],
            'wa_text'  => 'Hola, acabo de enviar mi búsqueda de propiedad en su sitio y quisiera hablar con un asesor.',
            'link'     => ['url' => '/propiedades', 'label' => 'Ver propiedades disponibles →'],
        ],
        'b2b' => [
            'titulo'   => 'Recibimos tu brief' . ($clientName ? ', ' . explode(' ', $clientName)[0] : '') . '.',
            'tiempo'   => '48 horas',
            'mensaje'  => 'Un miembro de nuestra dirección general te contactará para agendar la llamada de calificación. Información tratada bajo confidencialidad.',
            'pasos'    => [
                ['num' => '01', 'title' => 'Calificamos tu brief',       'desc' => 'Revisamos objetivos, presupuesto y horizonte de inversión.'],
                ['num' => '02', 'title' => 'Llamada de alineación',       'desc' => 'Definimos criterios técnicos y financieros con dirección general.'],
                ['num' => '03', 'title' => 'Captación dirigida',          'desc' => 'Activamos la red para identificar activos que cumplan al 100%.'],
            ],
            'wa_text'  => 'Hola, acabo de enviar mi brief de desarrollador en su sitio y quisiera hablar con dirección general.',
            'link'     => null,
        ],
        default => [
            'titulo'   => '¡Recibimos tu mensaje' . ($clientName ? ', ' . explode(' ', $clientName)[0] : '') . '!',
            'tiempo'   => '24 horas',
            'mensaje'  => 'Un asesor de Home del Valle te responderá pronto.',
            'pasos'    => [
                ['num' => '01', 'title' => 'Revisamos tu mensaje',       'desc' => 'Un asesor lee tu solicitud y prepara la mejor respuesta.'],
                ['num' => '02', 'title' => 'Te contactamos',             'desc' => 'Por teléfono, email o WhatsApp según tu preferencia.'],
                ['num' => '03', 'title' => 'Asesoría personalizada',     'desc' => 'Sin compromiso. Solo soluciones reales.'],
            ],
            'wa_text'  => 'Hola, acabo de enviar un formulario en su sitio y me gustaría hablar con un asesor.',
            'link'     => ['url' => '/mercado', 'label' => 'Ver observatorio de precios →'],
        ],
    };
@endphp

<section class="min-h-[80vh] flex items-center bg-white">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-24 text-center">

        {{-- Check icon --}}
        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-emerald-50 mx-auto mb-8">
            <svg class="w-10 h-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
            {{ $config['titulo'] }}
        </h1>
        <p class="mt-4 text-lg text-gray-500 leading-relaxed">
            {{ $config['mensaje'] }}
        </p>
        <p class="mt-2 text-base font-semibold text-brand-600">
            Tiempo de respuesta: menos de {{ $config['tiempo'] }} hábiles.
        </p>

        @if($folio)
        <p class="mt-4 inline-block text-xs font-mono text-gray-400 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2">
            Folio de seguimiento: {{ $folio }}
        </p>
        @endif

        {{-- WhatsApp CTA --}}
        @if($siteSettings?->whatsapp_number)
        <div class="mt-8 p-5 rounded-2xl bg-[#25D366]/5 border border-[#25D366]/20">
            <p class="text-sm text-gray-600 mb-3">¿Necesitas respuesta inmediata?</p>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings->whatsapp_number) }}?text={{ urlencode($config['wa_text']) }}"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2.5 rounded-xl bg-[#25D366] px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#22c35e] hover:-translate-y-0.5 transition-all duration-300">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Escríbenos por WhatsApp
            </a>
        </div>
        @endif

        {{-- Próximos pasos --}}
        <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
            @foreach($config['pasos'] as $paso)
            <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                <div class="text-2xl font-black text-brand-500 mb-2">{{ $paso['num'] }}</div>
                <p class="text-sm font-semibold text-gray-800">{{ $paso['title'] }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $paso['desc'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Back links --}}
        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver al inicio
            </a>
            @if($config['link'])
            <a href="{{ $config['link']['url'] }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                {{ $config['link']['label'] }}
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            @endif
        </div>

    </div>
</section>
@endsection
