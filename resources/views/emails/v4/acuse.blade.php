<x-emails.v4.layout preheader="Acuse de recibido - Folio {{ $data->folio }}">
    <x-emails.v4.header />

    <x-emails.v4.body>
        <x-emails.v4.h1>Recibimos tu mensaje 👋</x-emails-v4-h1>

        <x-emails.v4.p color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }}">
            Un asesor te responderá en menos de 24 horas hábiles. Mientras tanto, explora nuestro portafolio de inmuebles.
        </x-emails-v4-p>

        <x-emails.v4.card style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }};">
            <table role="presentation" width="100%">
                <tr>
                    <td style="padding-right: {{ \App\Mail\V4\Tokens::SPACE_MD }}; vertical-align: top;">
                        <div style="display: inline-block; width: 36px; height: 36px; border-radius: 50%; background-color: {{ \App\Mail\V4\Tokens::GREEN_SOFT }}; color: {{ \App\Mail\V4\Tokens::GREEN }}; font-size: 18px; line-height: 36px; text-align: center;">✓</div>
                    </td>
                    <td style="vertical-align: top;">
                        <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_XS }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">Folio</p>
                        <p style="margin: 0; font-family: 'Courier New', monospace; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_16 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::NAVY }};">HDV-{{ strtoupper(substr(md5($data->folio), 0, 4)) }}-{{ substr($data->folio, -4) }}</p>
                    </td>
                </tr>
            </table>
        </x-emails-v4-card>

        <x-emails.v4.p size="12" color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }}">
            Sabemos que el mercado inmobiliario en CDMX es complejo. Por eso contamos con un portafolio reducido de solo los mejores inmuebles. Explora nuestras propiedades y encuentra la que se adapte a tus necesidades.
        </x-emails-v4-p>

        <table role="presentation" width="100%" style="margin-bottom: 0;">
            <tr>
                <td align="left" style="padding-right: {{ \App\Mail\V4\Tokens::SPACE_MD }};">
                    <x-emails.v4.button href="https://homedelvalle.mx/propiedades" variant="primary">Ver propiedades</x-emails-v4-button>
                </td>
                <td align="left">
                    <x-emails.v4.button href="mailto:{{ $data->email }}" variant="secondary">Responder</x-emails-v4-button>
                </td>
            </tr>
        </table>
    </x-emails-v4-body>
</x-emails-v4-layout>
