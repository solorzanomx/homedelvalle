<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ya respondiste — Home del Valle</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;background:#F1F4F8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:480px;width:100%;padding:48px 40px;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.06);}
.logo{margin:0 auto 32px;display:block;height:36px;}
h1{font-size:22px;font-weight:800;color:#0E304B;margin-bottom:12px;letter-spacing:-.3px;}
.subtext{font-size:15.5px;color:#5A6573;line-height:1.65;}
.badge{display:inline-block;margin-top:18px;padding:8px 16px;border-radius:999px;font-size:12.5px;font-weight:800;letter-spacing:.4px;text-transform:uppercase;}
.badge-yes{background:#ECFDF5;color:#15803D;}
.badge-no{background:#FEF2F2;color:#B91C1C;}
.footer{margin-top:32px;font-size:12px;color:#9AA6B5;line-height:1.8;}
.footer strong{color:#0E304B;}
.divider{height:1px;background:#EEF1F6;margin:28px 0;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo">

    <h1>Ya nos habías contestado</h1>
    @if($collaborator->consent_status === 'authorized')
        <p class="subtext">Autorizaste tu publicación el {{ $collaborator->consent_at?->translatedFormat('d \d\e F \d\e Y') }}. Si necesitas cambiar algo, escríbenos directamente.</p>
        <span class="badge badge-yes">Autorizado</span>
    @else
        <p class="subtext">Nos dijiste que no autorizabas la publicación. Si cambiaste de opinión, escríbenos y con gusto lo retomamos.</p>
        <span class="badge badge-no">No autorizado</span>
    @endif

    <div class="divider"></div>

    <div class="footer">
        <strong>Home del Valle</strong><br>
        +52 55 1345 0978 &middot; contacto@homedelvalle.mx
    </div>
</div>
</body>
</html>
