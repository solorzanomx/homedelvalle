@props(['preheader' => null, 'tag' => null, 'tagColor' => 'blue'])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home del Valle</title>
    <style type="text/css">
        * {
            margin: 0;
            padding: 0;
            border: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: {{ \App\Mail\V4\Tokens::FONT_FAMILY }};
            color: {{ \App\Mail\V4\Tokens::TEXT }};
            background-color: {{ \App\Mail\V4\Tokens::GRAY_50 }};
            Margin: 0;
            padding: 0;
            min-width: 100% !important;
        }

        body {
            width: 100% !important;
            height: 100% !important;
            margin: 0;
            padding: 0;
        }

        img {
            outline: none;
            text-decoration: none;
            border: none;
            -ms-interpolation-mode: nearest-neighbor;
        }

        table {
            border-collapse: collapse;
            mso-table-lspace: 0;
            mso-table-rspace: 0;
        }

        a {
            color: {{ \App\Mail\V4\Tokens::BLUE }};
            text-decoration: none;
        }

        @media only screen and (max-width: {{ \App\Mail\V4\Tokens::EMAIL_MOBILE_BREAKPOINT }}) {
            .container {
                width: 100% !important;
            }

            .inner-container {
                padding: 16px 16px 14px !important;
            }

            .header {
                padding: 16px !important;
            }

            .footer {
                padding: 16px !important;
            }

            h1 {
                font-size: 20px !important;
                margin-bottom: 12px !important;
            }

            p {
                font-size: 14px !important;
            }

            .button-link {
                font-size: 13px !important;
            }
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: {{ \App\Mail\V4\Tokens::GRAY_50 }} !important;
            }
        }
    </style>
</head>
<body>
    {{-- Preheader --}}
    @if ($preheader)
        <div style="display: none; font-size: 0; line-height: 0; max-height: 0; mso-hide: all;">
            {{ $preheader }}
        </div>
    @endif

    {{-- Wrapper para centrar --}}
    <table role="presentation" width="100%" style="background-color: {{ \App\Mail\V4\Tokens::GRAY_50 }}; mso-padding-alt: 0;">
        <tr>
            <td align="center" style="padding: 28px 0;">
                {{-- Email container 600px --}}
                <table role="presentation" class="container" width="600" style="border-collapse: collapse; background-color: {{ \App\Mail\V4\Tokens::WHITE }}; border: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; border-radius: {{ \App\Mail\V4\Tokens::RADIUS_LG }}; box-shadow: {{ \App\Mail\V4\Tokens::SHADOW_MD }};">
                    <tr>
                        <td style="padding: 0;">
                            {{ $slot }}

                            {{-- Footer by default --}}
                            @unless (isset($footer))
                                <table role="presentation" width="100%">
                                    <tr>
                                        <td style="border-top: 1px solid {{ \App\Mail\V4\Tokens::BORDER }}; padding: {{ \App\Mail\V4\Tokens::SPACE_LG }}; text-align: center;">
                                            <div style="display: inline-block; width: 28px; height: 28px; border-radius: 50%; background-color: {{ \App\Mail\V4\Tokens::BLUE }}; color: {{ \App\Mail\V4\Tokens::WHITE }}; font-size: 12px; font-weight: 600; line-height: 28px; text-align: center;">HV</div>
                                            <p style="margin: 8px 0 4px; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_14 }}; font-weight: 600; color: {{ \App\Mail\V4\Tokens::INK }};">El equipo Home del Valle</p>
                                            <p style="margin: 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::MUTED }}; line-height: 1.5;">Pocos inmuebles · Más control · Mejores resultados</p>
                                            <p style="margin: 10px 0 0; font-size: {{ \App\Mail\V4\Tokens::FONT_SIZE_12 }}; color: {{ \App\Mail\V4\Tokens::SUBTLE }};">
                                                <a href="https://homedelvalle.mx" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">homedelvalle.mx</a> ·
                                                <a href="mailto:contacto@homedelvalle.mx" style="color: {{ \App\Mail\V4\Tokens::BLUE }}; text-decoration: none;">contacto@homedelvalle.mx</a>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                {{ $footer }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
