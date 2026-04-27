<x-emails.v4.layout preheader="Tu visita está agendada para {{ $data->dia_semana }}, {{ $data->dia }} de {{ $data->mes }}">
    <x-emails.v4.header />

    <x-emails.v4.body>
        <x-emails.v4.h1>Tu visita está agendada</x-emails-v4-h1>

        <x-emails.v4.p color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }}">
            Si necesitas reagendar tu cita, responde este correo con tus nuevas preferencias de horario.
        </x-emails-v4-p>

        <x-emails.v4.card style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }};">
            <table role="presentation" width="100%">
                <tr>
                    <td width="96" style="background-color: {{ \App\Mail\V4\Tokens::NAVY }}; color: {{ \App\Mail\V4\Tokens::WHITE }}; padding: {{ \App\Mail\V4\Tokens::SPACE_LG }}; text-align: center; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_MD }} 0 0 {{ \App\Mail\V4\Tokens::RADIUS_MD }};">
                        <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_XS }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; font-weight: 600; text-transform: uppercase;">{{ $data->dia_semana }}</p>
                        <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_XS }} 0; font-size: 36px; font-weight: 700; line-height: 1;">{{ $data->dia }}</p>
                        <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }};">{{ $data->mes }}/{{ substr($data->anio, -2) }}</p>
                    </td>
                    <td style="padding: {{ \App\Mail\V4\Tokens::SPACE_LG }}; vertical-align: top;">
                        <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_MD }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_16 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">{{ $data->direccion }}</p>
                        <table role="presentation" width="100%">
                            <tr>
                                <td style="padding: {{ \App\Mail\V4\Tokens::SPACE_SM }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; color: {{ \App\Mail\V4\Tokens::TEXT }};">🕒 {{ $data->hora }} ({{ $data->duracion }} min)</td>
                            </tr>
                            <tr>
                                <td style="padding: {{ \App\Mail\V4\Tokens::SPACE_SM }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; color: {{ \App\Mail\V4\Tokens::TEXT }};">📍 {{ $data->colonia }}</td>
                            </tr>
                            <tr>
                                <td style="padding: {{ \App\Mail\V4\Tokens::SPACE_SM }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; color: {{ \App\Mail\V4\Tokens::TEXT }};">👤 {{ $data->asesor }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </x-emails-v4-card>

        <x-emails.v4.p size="12" color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }};">
            Tip: Anota tus preguntas sobre el inmueble antes de la visita. Esto te ayudará a aprovechar mejor el tiempo.
        </x-emails-v4-p>

        <table role="presentation" width="100%" style="margin-bottom: 0;">
            <tr>
                <td align="left" style="padding-right: {{ \App\Mail\V4\Tokens::SPACE_MD }};">
                    <x-emails.v4.button href="https://calendar.google.com/calendar/u/0/r/eventedit" variant="primary">Agregar al calendario</x-emails-v4-button>
                </td>
                <td align="left">
                    <x-emails.v4.button href="mailto:contacto@homedelvalle.mx" variant="secondary">Reagendar</x-emails-v4-button>
                </td>
            </tr>
        </table>
    </x-emails-v4-body>
</x-emails-v4-layout>
