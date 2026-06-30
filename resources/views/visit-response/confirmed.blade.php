<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Visita confirmada — Home del Valle</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;background:#F1F4F8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:520px;width:100%;padding:48px 40px;text-align:center;}
.logo{margin:0 auto 32px;display:block;height:36px;}
.check-circle{width:80px;height:80px;border-radius:50%;background:#ECFDF5;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:36px;color:#10B981;}
.check-icon{display:block;width:80px;height:80px;border-radius:50%;background:#ECFDF5;margin:0 auto 24px;line-height:80px;font-size:36px;color:#10B981;text-align:center;}
h1{font-size:24px;font-weight:800;color:#0E304B;margin-bottom:12px;letter-spacing:-.4px;}
.subtext{font-size:16px;color:#5A6573;line-height:1.6;margin-bottom:28px;}
.detail-card{background:#F6F8FB;border-radius:14px;padding:20px 24px;text-align:left;margin-bottom:24px;}
.detail-row{display:flex;align-items:flex-start;gap:12px;margin-bottom:14px;}
.detail-row:last-child{margin-bottom:0;}
.detail-label{font-size:11px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;color:#9AA6B5;margin-bottom:2px;}
.detail-value{font-size:15px;font-weight:700;color:#0E304B;}
.footer{margin-top:32px;font-size:12px;color:#9AA6B5;line-height:1.8;}
.footer strong{color:#0E304B;}
.divider{height:1px;background:#EEF1F6;margin:28px 0;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo" style="height:36px;">

    <div class="check-icon">&#10003;</div>

    <h1>¡Listo! Te esperamos hoy.</h1>
    <p class="subtext">Tu asistencia ha sido registrada. Tu asesor estará listo para recibirte.</p>

    @if($interaction->scheduled_at)
    <div class="detail-card">
        <div class="detail-row">
            <div>
                <div class="detail-label">Hora</div>
                <div class="detail-value">{{ $interaction->scheduled_at->format('g:i A') }}</div>
            </div>
        </div>
        @if($interaction->property?->address)
        <div class="detail-row">
            <div>
                <div class="detail-label">Dirección</div>
                <div class="detail-value">{{ $interaction->property->address }}</div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <p class="subtext" style="margin-bottom:0;">¡Hasta luego!</p>

    <div class="divider"></div>

    <div class="footer">
        <strong>Home del Valle</strong><br>
        +52 55 1345 0978 &middot; contacto@homedelvalle.mx<br>
        Heriberto Frías 903-A, Col. del Valle, CDMX
    </div>
</div>
</body>
</html>
