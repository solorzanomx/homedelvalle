<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #F6F7F9; color: #0F172A; }
        table { border-collapse: collapse; width: 100%; }
        a { color: #3B82C4; text-decoration: none; }
        @media (max-width: 520px) {
            .container { width: 100% !important; }
            .content { padding: 20px 16px !important; }
        }
    </style>
</head>
<body style="background-color: #F6F7F9; padding: 20px 0;">
    <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: white; border-radius: 16px; border: 1px solid #EAEEF3; box-shadow: 0 4px 24px -4px rgba(0,0,0,0.08);">
        {{-- Header --}}
        <tr>
            <td style="padding: 24px 28px; border-bottom: 1px solid #EAEEF3;">
                <table role="presentation" style="width: 100%;">
                    <tr>
                        <td>
                            <div style="display: inline-flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #3B82C4, #1E3A5F); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 14px;">HV</div>
                                <span style="font-size: 14px; font-weight: 600; color: #0F172A;">Home del Valle</span>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; background-color: #DBEAFE; color: #2563A0; padding: 6px 12px; border-radius: 999px; font-size: 11px; font-weight: 600;">
                                Confirmado
                            </span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Body --}}
        <tr>
            <td style="padding: 28px; color: #0F172A;">
                <h1 style="font-size: 24px; font-weight: 600; margin-bottom: 14px; letter-spacing: -0.01em;">¡Hola {{ $name }}! 👋</h1>

                <p style="font-size: 15px; color: #64748B; line-height: 1.6; margin-bottom: 18px;">
                    Confirmamos que recibimos tu solicitud de información sobre <strong style="color: #0F172A;">{{ $propertyTitle }}</strong>. Estamos entusiasmados de ayudarte.
                </p>

                {{-- Info Card --}}
                <div style="background-color: #F0F7FF; border-left: 3px solid #3B82C4; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                    <table role="presentation" style="width: 100%;">
                        <tr>
                            <td style="font-size: 11px; color: #64748B; font-weight: 500; width: 96px;">TELÉFONO</td>
                            <td style="font-size: 14px; color: #0F172A; font-family: 'Courier New', monospace;">{{ $phone }}</td>
                        </tr>
                        <tr style="height: 14px;"></tr>
                        <tr>
                            <td style="font-size: 11px; color: #64748B; font-weight: 500;">CORREO</td>
                            <td style="font-size: 14px; color: #0F172A; font-family: 'Courier New', monospace;">{{ $email }}</td>
                        </tr>
                    </table>
                </div>

                <p style="font-size: 15px; color: #0F172A; line-height: 1.6; margin-bottom: 6px;">
                    <strong>¿Qué sigue?</strong>
                </p>
                <p style="font-size: 14px; color: #64748B; line-height: 1.6; margin-bottom: 24px;">
                    Uno de nuestros asesores especializados se pondrá en contacto contigo en <strong>menos de 24 horas</strong> vía WhatsApp, teléfono o email para brindarte información detallada y responder tus preguntas.
                </p>

                {{-- Tip Card --}}
                <div style="background-color: #F6F7F9; border-radius: 12px; padding: 16px; border: 1px solid #E2E8F0;">
                    <p style="font-size: 13px; color: #0F172A; line-height: 1.5;">
                        <strong>💡 Tip:</strong> Ten listo el documento de identidad y cualquier pregunta que tengas sobre la propiedad para agilizar la consulta.
                    </p>
                </div>
            </td>
        </tr>

        {{-- Footer --}}
        <tr>
            <td style="padding: 18px 28px; border-top: 1px solid #EAEEF3; background-color: #FAFBFC;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #3B82C4, #1E3A5F); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px;">HV</div>
                    <div>
                        <div style="font-size: 13px; font-weight: 500; color: #0F172A;">El equipo Home del Valle</div>
                        <div style="font-size: 11px; color: #64748B;">Pocos inmuebles · Más control · Mejores resultados</div>
                    </div>
                </div>
                <div style="font-size: 12px; color: #94A3B8; line-height: 1.4; margin-top: 12px;">
                    <a href="https://homedelvalle.mx" style="color: #3B82C4;">homedelvalle.mx</a> ·
                    <a href="mailto:contacto@homedelvalle.mx" style="color: #3B82C4;">contacto@homedelvalle.mx</a>
                </div>
            </td>
        </tr>
    </table>

    {{-- Legal Footer --}}
    <div style="text-align: center; margin-top: 20px; font-size: 11px; color: #94A3B8;">
        <p style="margin: 0;">© 2026 Home del Valle Bienes Raíces. Todos los derechos reservados.</p>
        <p style="margin: 8px 0 0 0;">
            <a href="{{ url('/legal/aviso-de-privacidad') }}" style="color: #3B82C4;">Aviso de Privacidad</a> ·
            <a href="{{ url('/legal/terminos-y-condiciones') }}" style="color: #3B82C4;">Términos y Condiciones</a>
        </p>
    </div>
</body>
</html>
