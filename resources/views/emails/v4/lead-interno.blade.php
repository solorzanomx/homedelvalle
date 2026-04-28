<x-emails.v4.layout preheader="Nuevo lead de {{ $data->nombre }}">
    <x-emails.v4.header tag="Nuevo lead" tagColor="blue" :logoUrl="$logoUrl ?? null" />

    <x-emails.v4.body>
        <table role="presentation" width="100%" style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }};">
            <tr>
                <td style="vertical-align: top; padding-right: {{ \App\Mail\V4\Tokens::SPACE_MD }};">
                    <x-emails.v4.avatar size="48" label="LD" bg="{{ \App\Mail\V4\Tokens::BLUE_SOFT }}" fg="{{ \App\Mail\V4\Tokens::BLUE_DARK }}" />
                </td>
                <td style="vertical-align: top;">
                    <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_XS }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_18 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">{{ $data->nombre }}</p>
                    <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }};">Llegó hace unos momentos</p>
                </td>
            </tr>
        </table>

        <x-emails.v4.card style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }};">
            <x-emails.v4.kvrow label="Email">
                <a href="mailto:{{ $data->email }}" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">{{ $data->email }}</a>
            </x-emails.v4.kvrow>
            <x-emails.v4.kvrow label="Teléfono" style="font-variant-numeric: tabular-nums;">
                {{ $data->telefono }}
            </x-emails.v4.kvrow>
            <x-emails.v4.kvrow label="Origen">
                <x-emails.v4.pill color="blue" soft="true">{{ $data->origen }}</x-emails.v4.pill>
            </x-emails.v4.kvrow>
            <x-emails.v4.kvrow label="Fecha" last="true">
                {{ $data->fecha }}
            </x-emails.v4.kvrow>
        </x-emails.v4.card>

        <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_SM }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::MUTED }};">Mensaje</p>

        <x-emails.v4.card bg="{{ \App\Mail\V4\Tokens::BLUE_TINT }}" accent="{{ \App\Mail\V4\Tokens::BLUE }}" style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }};">
            <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; color: {{ \App\Mail\V4\Tokens::INK }}; line-height: {{ \App\Mail\V4\Tokens::LINE_HEIGHT_1_6 }};">{{ $data->mensaje }}</p>
        </x-emails.v4.card>

        <table role="presentation" width="100%" style="margin-bottom: 0;">
            <tr>
                <td align="left" style="padding-right: {{ \App\Mail\V4\Tokens::SPACE_MD }};">
                    <x-emails.v4.button href="https://app.homedelvalle.mx/leads/{{ $data->email }}" variant="primary">Ver en CRM</x-emails.v4.button>
                </td>
                <td align="left">
                    <x-emails.v4.button href="mailto:{{ $data->email }}" variant="secondary">Responder</x-emails.v4.button>
                </td>
            </tr>
        </table>
    </x-emails.v4.body>

    <x-slot name="footer">
        <table role="presentation" width="100%" style="background-color: {{ \App\Mail\V4\Tokens::GRAY_50 }};">
            <tr>
                <td style="border-top: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; padding: {{ \App\Mail\V4\Tokens::SPACE_LG }}; text-align: center;">
                    <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }};">Notificación automática · Home del Valle</p>
                </td>
            </tr>
        </table>
    </x-slot>
</x-emails.v4.layout>
