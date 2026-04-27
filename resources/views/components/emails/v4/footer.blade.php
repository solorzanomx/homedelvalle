@props(['closing' => null])

<table role="presentation" width="100%" style="background-color: {{ \App\Mail\V4\Tokens::GRAY_50 }};">
    <tr>
        <td style="border-top: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; padding: {{ \App\Mail\V4\Tokens::SPACE_LG }}; text-align: center;">
            <div style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background-color: {{ \App\Mail\V4\Tokens::BLUE }}; color: {{ \App\Mail\V4\Tokens::WHITE }}; font-size: 12px; font-weight: 600; line-height: 28px; text-align: center;">HV</div>
            <p style="margin: 8px 0 4px; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">El equipo Home del Valle</p>
            <p style="margin: 0 0 10px; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }}; line-height: 1.5;">Pocos inmuebles · Más control · Mejores resultados</p>
            @if ($closing)
                <p style="margin: 0 0 8px; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }};">{{ $closing }}</p>
            @endif
            <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::SUBTLE }};">
                <a href="https://homedelvalle.mx" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">homedelvalle.mx</a> ·
                <a href="mailto:contacto@homedelvalle.mx" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">contacto@homedelvalle.mx</a> ·
                <a href="https://facebook.com/homedelvalle" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">Facebook</a> ·
                <a href="https://instagram.com/homedelvalle" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">Instagram</a>
            </p>
        </td>
    </tr>
</table>
