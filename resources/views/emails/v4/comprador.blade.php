<x-emails.v4.layout preheader="Propiedad en {{ $data->colonia }}: {{ $data->titulo }}">
    <x-emails.v4.header />

    <x-emails.v4.body>
        <x-emails.v4.h1>Aquí tienes la propiedad</x-emails.v4.h1>

        <x-emails.v4.p color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }}">
            Creemos que este inmueble podría ser exactamente lo que buscas. Revisa los detalles y agende una visita con nosotros.
        </x-emails.v4.p>

        <x-emails.v4.card padding="0" style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_XL }}; overflow: hidden;">
            @if ($data->foto_url)
                <img src="{{ $data->foto_url }}" alt="{{ $data->titulo }}" style="display: block; width: 100%; height: 180px; object-fit: cover;" />
            @else
                <div style="width: 100%; height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 14px;">
                    Imagen del inmueble
                </div>
            @endif

            <div style="padding: {{ \App\Mail\V4\Tokens::SPACE_LG }};">
                <div style="margin-bottom: {{ \App\Mail\V4\Tokens::SPACE_MD }};">
                    <x-emails.v4.pill color="blue" soft="true">{{ $data->colonia }}</x-emails.v4.pill>
                </div>

                <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_SM }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_18 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">{{ $data->titulo }}</p>

                <p style="margin: 0 0 {{ \App\Mail\V4\Tokens::SPACE_LG }} 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_13 }}; color: {{ \App\Mail\V4\Tokens::MUTED }};">
                    {{ $data->metros }} m² · {{ $data->recamaras }} recámaras · {{ $data->banos }} baños · {{ $data->estacionamientos }} cajones
                </p>

                <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_22 }}; font-weight: 700; color: {{ \App\Mail\V4\Tokens::INK }};">
                    ${{ number_format($data->precio) }} <span style="font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }};">MXN</span>
                </p>
            </div>
        </x-emails.v4.card>

        <x-emails.v4.p size="12" color="muted" mb="{{ \App\Mail\V4\Tokens::SPACE_XL }};">
            Tenemos disponibilidad <strong>esta semana</strong> para una visita. Confirma tu interés y coordinaremos la mejor fecha y hora para ti.
        </x-emails.v4.p>

        <table role="presentation" width="100%" style="margin-bottom: 0;">
            <tr>
                <td align="left" style="padding-right: {{ \App\Mail\V4\Tokens::SPACE_MD }};">
                    <x-emails.v4.button href="https://homedelvalle.mx/propiedades" variant="primary">Agendar visita</x-emails.v4.button>
                </td>
                <td align="left">
                    <x-emails.v4.button href="https://homedelvalle.mx/propiedades" variant="secondary">Ver ficha completa</x-emails.v4.button>
                </td>
            </tr>
        </table>
    </x-emails.v4.body>
</x-emails.v4.layout>
