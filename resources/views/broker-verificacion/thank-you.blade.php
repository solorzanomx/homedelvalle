<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>¡Gracias! — Home del Valle</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;background:#F1F4F8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:480px;width:100%;padding:48px 40px;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.06);}
.logo{margin:0 auto 32px;display:block;height:36px;}
.check-circle{width:80px;height:80px;border-radius:50%;background:#ECFDF5;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;}
h1{font-size:22px;font-weight:800;color:#0E304B;margin-bottom:12px;letter-spacing:-.3px;}
.subtext{font-size:15px;color:#5A6573;line-height:1.65;}
.footer{margin-top:32px;font-size:12px;color:#9AA6B5;line-height:1.8;}
.footer strong{color:#0E304B;}
.divider{height:1px;background:#EEF1F6;margin:28px 0;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo">

    <div class="check-circle">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#15803D" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>

    <h1>¡Gracias!</h1>
    <p class="subtext">Recibimos tus datos. En cuanto los revisemos te contactamos para empezar a compartirte propiedades.</p>

    <div class="divider"></div>

    <div class="footer">
        <strong>Home del Valle</strong><br>
        +52 55 1345 0978 &middot; contacto@homedelvalle.mx
    </div>
</div>
</body>
</html>
