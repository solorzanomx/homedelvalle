<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ficha técnica — {{ $property->title }}</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#F1F4F8;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#F1F4F8;">
<tr><td align="center" style="padding:40px 16px;">

<table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0"
       style="width:560px;max-width:560px;background:#FFFFFF;border:1px solid #E6EAF1;border-radius:20px;overflow:hidden;">

    <tr>
        <td style="background:#0E304B;padding:24px 34px;">
            <div style="font-size:11px;color:#8FA9D2;font-weight:700;letter-spacing:.5px;">HOME DEL VALLE</div>
            <div style="font-size:11px;color:#8FA9D2;">Pocos inmuebles &middot; Más control &middot; Mejores resultados</div>
        </td>
    </tr>

    <tr>
        <td style="padding:34px 34px 8px;">
            <h1 style="font-size:20px;font-weight:800;color:#0E304B;margin:0 0 16px;">Ficha técnica: {{ $property->title }}</h1>
            <p style="font-size:14.5px;line-height:1.7;color:#5A6573;margin:0 0 16px;">
                Hola{{ $contact->name ? ', ' . explode(' ', trim($contact->name))[0] : '' }}. Te compartimos la ficha técnica del predio ubicado en <strong style="color:#0E304B;">{{ $property->address }}{{ $property->colony ? ', Col. ' . $property->colony : '' }}</strong>, adjunta en PDF, para que la analicen.
            </p>
            @if($message)
            <p style="font-size:14.5px;line-height:1.7;color:#5A6573;margin:0 0 16px;">{{ $message }}</p>
            @endif
            <p style="font-size:14.5px;line-height:1.7;color:#5A6573;margin:0;">
                Quedamos atentos a sus comentarios.
            </p>
        </td>
    </tr>

    <tr>
        <td style="padding:8px 34px 34px;">
            <p style="font-size:13px;color:#0E304B;font-weight:700;margin:0;">Home del Valle</p>
            <p style="font-size:12.5px;color:#7A8594;margin:4px 0 0;">+52 55 1345 0978 &middot; contacto@homedelvalle.mx</p>
        </td>
    </tr>

</table>
</td></tr>
</table>
</body>
</html>
