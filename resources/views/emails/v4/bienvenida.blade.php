<x-emails.v4.layout preheader="Bienvenido al área de clientes de Home del Valle">
    <x-emails.v4.header :logoUrl="$logoUrl ?? null" />

    <x-emails.v4.body>
        <x-emails.v4.h1>Bienvenido al área de clientes</x-emails.v4.h1>

        <x-emails.v4.p color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }}">
            Ya puedes acceder a tu cuenta personal para seguimiento de tratos, documentos y más. Usa tus credenciales abajo.
        </x-emails.v4.p>

        <x-emails.v4.card bg="{{ \App\Mail\V4\Tokens::GRAY_50 }}" style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }};">
            <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_MD }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">Tus credenciales</p>

            <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_XS }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_11 }}; color: {{ \App\Mail\V4\Tokens::MUTED }}; font-weight: 500;">Usuario</p>
            <div style="background-color: {{ \App\Mail\V4\Tokens::WHITE }}; border: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_SM }}; padding: {{ \App\Mail\V4\Tokens::SPACE_SM }} {{ \App\Mail\V4\Tokens::SPACE_MD }}; margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_MD }}; font-family: 'Courier New', monospace; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_13 }}; color: {{ \App\Mail\V4\Tokens::INK }};">{{ $data->usuario }}</div>

            <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_XS }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_11 }}; color: {{ \App\Mail\V4\Tokens::MUTED }}; font-weight: 500;">Contraseña temporal</p>
            <div style="background-color: {{ \App\Mail\V4\Tokens::WHITE }}; border: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_SM }}; padding: {{ \App\Mail\V4\Tokens::SPACE_SM }} {{ \App\Mail\V4\Tokens::SPACE_MD }}; margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_MD }}; font-family: 'Courier New', monospace; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_13 }}; color: {{ \App\Mail\V4\Tokens::INK }};">{{ $data->password_temporal }}</div>

            <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_11 }}; color: {{ \App\Mail\V4\Tokens::MUTED }}; font-style: italic;">Por seguridad, te pediremos que cambies tu contraseña en el primer ingreso.</p>
        </x-emails.v4.card>

        <div style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }}; text-align: center;">
            <x-emails.v4.button href="{{ $data->url_acceso }}" variant="primary">Ingresar al área de clientes →</x-emails.v4.button>
        </div>

        <x-emails.v4.card bg="{{ \App\Mail\V4\Tokens::BLUE_TINT }}" style="margin-bottom: 0;">
            <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_13 }}; color: {{ \App\Mail\V4\Tokens::INK }}; line-height: {{ \App\Mail\V4\Tokens::LINE_HEIGHT_1_6 }};">
                <strong>¿Problemas para entrar?</strong> Responde este correo o escríbenos a <a href="mailto:contacto@homedelvalle.mx" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">contacto@homedelvalle.mx</a>. Nunca pediremos tu contraseña por correo.
            </p>
        </x-emails.v4.card>
    </x-emails.v4.body>
</x-emails.v4.layout>
