@php
    $primerNombre = $data->nombre ? explode(' ', trim($data->nombre))[0] : '';
    $saludo       = $primerNombre ? ', ' . $primerNombre : '';
    $formType     = $data->form_type;
    $folio        = 'HDV-' . strtoupper(substr(md5($data->folio), 0, 4)) . '-' . $data->folio;

    $config = match($formType) {
        'vendedor' => [
            'preheader' => 'Recibimos tu solicitud de valuacion — te contactamos en menos de 24 horas',
            'titulo'    => 'Recibimos tu solicitud' . $saludo . ' 👋',
            'bajada'    => 'Un asesor especializado revisará tu propiedad y te contactará por WhatsApp en <strong>menos de 24 horas hábiles</strong> con tu valuación gratuita.',
            'pasos'     => [
                ['icono' => '🏠', 'titulo' => 'Analizamos tu propiedad',  'desc' => 'Revisamos ubicación, metraje y comparables del mercado actual en Benito Juárez.'],
                ['icono' => '📊', 'titulo' => 'Te enviamos la valuación',  'desc' => 'Precio competitivo basado en datos reales, no estimados genéricos.'],
                ['icono' => '🤝', 'titulo' => 'Diseñamos tu estrategia',   'desc' => 'Fotografía profesional, marketing digital y red de compradores calificados.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/mercado',     'label' => 'Ver precios de mercado'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver propiedades'],
            'nota'          => 'Sin compromiso y sin costos. Solo cobramos comisión al cerrar exitosamente.',
        ],
        'comprador' => [
            'preheader' => 'Recibimos tu busqueda — curaduria en menos de 72 horas',
            'titulo'    => 'Recibimos tu búsqueda' . $saludo . ' 🔍',
            'bajada'    => 'Vamos a curar las mejores opciones que coincidan con tu brief y te las enviamos en <strong>menos de 72 horas hábiles</strong>. Sin spam, sin catálogos masivos.',
            'pasos'     => [
                ['icono' => '🔍', 'titulo' => 'Filtramos el mercado',     'desc' => 'Revisamos inventario propio, red de contactos privada y mercado abierto.'],
                ['icono' => '✅', 'titulo' => 'Selección curada',          'desc' => 'Solo te enviamos 3–5 opciones que realmente coinciden con tu brief.'],
                ['icono' => '📝', 'titulo' => 'Acompañamiento al cierre',  'desc' => 'Negociación, due diligence legal y firma de escrituras incluidos.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver propiedades disponibles'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/mercado',     'label' => 'Ver precios de mercado'],
            'nota'          => 'El servicio es gratuito para el comprador. La comisión la cubre el vendedor al cierre.',
        ],
        'arrendatario' => [
            'preheader' => 'Tu búsqueda de renta llegó — selección curada en 72 horas',
            'titulo'    => 'Recibimos tu búsqueda de renta' . $saludo . ' 🔑',
            'bajada'    => 'Vamos a curar las mejores opciones que coincidan con tu brief y te las enviamos en <strong>menos de 72 horas hábiles</strong>. Sin agentes que insisten, sin portales masivos.',
            'pasos'     => [
                ['icono' => '🔍', 'titulo' => 'Filtramos el inventario',     'desc' => 'Revisamos inmuebles propios y red privada que coincidan con tu zona, presupuesto y preferencias (incluidas opciones pet-friendly si aplica).'],
                ['icono' => '📋', 'titulo' => 'Selección curada',            'desc' => 'Solo te enviamos 3–5 opciones que realmente cumplen tu brief. Sin catálogos masivos ni visitas innecesarias.'],
                ['icono' => '✍️', 'titulo' => 'Te acompañamos a firmar',     'desc' => 'Revisamos contrato y póliza jurídica contigo antes de que firmes. Sin cláusulas escondidas, sin sorpresas.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver inmuebles disponibles'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/mercado',     'label' => 'Ver precios de mercado'],
            'nota'          => 'El servicio es gratuito para ti como inquilino. Nuestra comisión la cubre el propietario al cierre.',
        ],
        'propietario_renta' => [
            'preheader' => 'Tu solicitud para rentar tu inmueble llegó — te contactamos en 24 horas',
            'titulo'    => 'Recibimos tu solicitud' . $saludo . ' 🏠',
            'bajada'    => 'Un asesor especializado en rentas te contactará en <strong>menos de 24 horas hábiles</strong> con un rango de renta orientativo y un plan personalizado para tu inmueble.',
            'pasos'     => [
                ['icono' => '🔍', 'titulo' => 'Analizamos tu inmueble',      'desc' => 'Revisamos ubicación, características y comparables de renta en tu colonia dentro de Benito Juárez.'],
                ['icono' => '📊', 'titulo' => 'Te enviamos el rango de renta','desc' => 'Precio competitivo basado en datos reales del mercado, no estimados genéricos. Sin inflar para captar la firma.'],
                ['icono' => '🤝', 'titulo' => 'Diseñamos tu plan',           'desc' => 'Calificación de inquilinos, póliza jurídica y, si lo deseas, administración integral. Tú eliges el nivel de servicio.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/mercado',  'label' => 'Ver precios de mercado en BJ'],
            'cta_secondary' => null,
            'nota'          => 'Sin costo por adelantado. Comisión solo al firmar contrato. Sin exclusividad obligatoria.',
        ],
        'b2b' => [
            'preheader' => 'Recibimos tu brief calificador — llamada en menos de 48 horas',
            'titulo'    => 'Recibimos tu brief' . $saludo . '.',
            'bajada'    => 'Un miembro de nuestra dirección general te contactará en <strong>menos de 48 horas hábiles</strong> para agendar la llamada de calificación.',
            'pasos'     => [
                ['icono' => '📋', 'titulo' => 'Calificamos tu brief',     'desc' => 'Revisamos objetivos, presupuesto y horizonte de inversión.'],
                ['icono' => '📞', 'titulo' => 'Llamada de alineación',    'desc' => 'Definimos criterios técnicos y financieros con dirección general.'],
                ['icono' => '🎯', 'titulo' => 'Captación dirigida',       'desc' => 'Activamos la red para identificar activos que cumplan al 100%.'],
            ],
            'cta_primary'   => ['url' => 'mailto:leads@homedelvalle.mx', 'label' => 'Contactar dirección general'],
            'cta_secondary' => null,
            'nota'          => 'Información tratada bajo confidencialidad. Nunca compartimos tu brief sin autorización.',
        ],
        default => [
            'preheader' => 'Recibimos tu mensaje — respondemos en menos de 24 horas',
            'titulo'    => 'Recibimos tu mensaje' . $saludo . ' 👋',
            'bajada'    => 'Un asesor de <strong>Home del Valle</strong> te responderá en <strong>menos de 24 horas hábiles</strong>. Sin compromiso y sin spam.',
            'pasos'     => [
                ['icono' => '📬', 'titulo' => 'Revisamos tu mensaje',     'desc' => 'Un asesor lee tu solicitud y prepara la mejor respuesta.'],
                ['icono' => '💬', 'titulo' => 'Te contactamos',           'desc' => 'Por teléfono, email o WhatsApp según tu preferencia.'],
                ['icono' => '🏡', 'titulo' => 'Asesoría personalizada',   'desc' => 'Sin compromiso. Solo soluciones reales para tu caso.'],
            ],
            'cta_primary'   => ['url' => 'https://homedelvalle.mx/propiedades', 'label' => 'Ver propiedades'],
            'cta_secondary' => ['url' => 'https://homedelvalle.mx/mercado',     'label' => 'Observatorio de precios'],
            'nota'          => 'Pocos inmuebles. Más control. Mejores resultados.',
        ],
    };
@endphp

<x-emails.v4.layout :preheader="$config['preheader']">
    <x-emails.v4.header :logoUrl="$logoUrl ?? null" />

    <x-emails.v4.body>
        {{-- Título --}}
        <x-emails.v4.h1>{{ $config['titulo'] }}</x-emails.v4.h1>

        {{-- Bajada --}}
        <x-emails.v4.p color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }}">
            {!! $config['bajada'] !!}
        </x-emails.v4.p>

        {{-- Folio --}}
        <x-emails.v4.card style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }};">
            <table role="presentation" width="100%">
                <tr>
                    <td style="padding-right: {{ \App\Mail\V4\Tokens::SPACE_MD }}; vertical-align: top;">
                        <div style="display:inline-block;width:36px;height:36px;border-radius:50%;background-color:{{ \App\Mail\V4\Tokens::GREEN_SOFT }};color:{{ \App\Mail\V4\Tokens::GREEN }};font-size:18px;line-height:36px;text-align:center;">✓</div>
                    </td>
                    <td style="vertical-align:top;">
                        <p style="margin:0 0 4px;font-size:12px;font-weight:600;color:{{ \App\Mail\V4\Tokens::MUTED }};text-transform:uppercase;letter-spacing:0.05em">Folio de seguimiento</p>
                        <p style="margin:0;font-family:'Courier New',monospace;font-size:{{ \App\Mail\V4\Tokens::FONT_SIZE_16 }};font-weight:700;color:{{ \App\Mail\V4\Tokens::NAVY }}">{{ $folio }}</p>
                    </td>
                </tr>
            </table>
        </x-emails.v4.card>

        {{-- Próximos pasos --}}
        <p style="margin:0 0 {{ \App\Mail\V4\Tokens::SPACE_MD }} 0;font-size:{{ \App\Mail\V4\Tokens::FONT_SIZE_14 }};font-weight:700;color:{{ \App\Mail\V4\Tokens::INK }};text-transform:uppercase;letter-spacing:0.05em">Qué sigue</p>

        @foreach($config['pasos'] as $paso)
        <x-emails.v4.card bg="#F9FAFB" style="margin-bottom:{{ \App\Mail\V4\Tokens::SPACE_SM }};">
            <table role="presentation" width="100%">
                <tr>
                    <td style="width:32px;vertical-align:top;padding-right:12px;font-size:20px;line-height:1.4">{{ $paso['icono'] }}</td>
                    <td style="vertical-align:top;">
                        <p style="margin:0 0 3px;font-size:{{ \App\Mail\V4\Tokens::FONT_SIZE_14 }};font-weight:600;color:{{ \App\Mail\V4\Tokens::INK }}">{{ $paso['titulo'] }}</p>
                        <p style="margin:0;font-size:{{ \App\Mail\V4\Tokens::FONT_SIZE_13 }};color:{{ \App\Mail\V4\Tokens::MUTED }};line-height:1.5">{{ $paso['desc'] }}</p>
                    </td>
                </tr>
            </table>
        </x-emails.v4.card>
        @endforeach

        {{-- Nota --}}
        <x-emails.v4.p size="12" color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }}" style="margin-top:{{ \App\Mail\V4\Tokens::SPACE_MD }}">
            {{ $config['nota'] }}
        </x-emails.v4.p>

        {{-- CTAs --}}
        <table role="presentation" width="100%" style="margin-bottom:0">
            <tr>
                <td align="left" style="padding-right:{{ \App\Mail\V4\Tokens::SPACE_MD }}">
                    <x-emails.v4.button :href="$config['cta_primary']['url']" variant="primary">
                        {{ $config['cta_primary']['label'] }}
                    </x-emails.v4.button>
                </td>
                @if($config['cta_secondary'])
                <td align="left">
                    <x-emails.v4.button :href="$config['cta_secondary']['url']" variant="secondary">
                        {{ $config['cta_secondary']['label'] }}
                    </x-emails.v4.button>
                </td>
                @endif
            </tr>
        </table>
    </x-emails.v4.body>
</x-emails.v4.layout>
