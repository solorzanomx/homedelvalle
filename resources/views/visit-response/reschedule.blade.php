<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reagendar visita — Home del Valle</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;background:#F1F4F8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:520px;width:100%;padding:48px 40px;text-align:center;}
.logo{margin:0 auto 32px;display:block;height:36px;}
.icon{display:block;width:72px;height:72px;border-radius:50%;background:#EAF3FB;margin:0 auto 24px;line-height:72px;font-size:30px;color:#2E80C6;text-align:center;}
h1{font-size:22px;font-weight:800;color:#0E304B;margin-bottom:10px;letter-spacing:-.3px;}
.subtext{font-size:15px;color:#5A6573;line-height:1.6;margin-bottom:28px;}
textarea{width:100%;border:1.5px solid #D5DCE7;border-radius:12px;padding:14px 16px;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;font-size:15px;color:#0E304B;resize:vertical;min-height:110px;outline:none;transition:border-color .2s;}
textarea:focus{border-color:#2E80C6;}
.btn{display:block;width:100%;background:#0E304B;color:#fff;border:none;border-radius:12px;padding:15px;font-size:15px;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;margin-top:16px;transition:opacity .2s;}
.btn:hover{opacity:.9;}
.divider{height:1px;background:#EEF1F6;margin:28px 0;}
.footer{font-size:12px;color:#9AA6B5;line-height:1.8;}
.footer strong{color:#0E304B;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo" style="height:36px;">

    <div class="icon">&#128197;</div>

    <h1>Reagendar visita</h1>
    <p class="subtext">Cuéntanos qué día y hora te funciona mejor y tu asesor se pondrá en contacto contigo.</p>

    <form method="POST" action="{{ url('/visit/' . $interaction->visit_token . '/reschedule') }}">
        @csrf
        <textarea name="mensaje" placeholder="¿Qué día y hora te funciona mejor?" required maxlength="500"></textarea>
        @error('mensaje')
            <div style="color:#ef4444;font-size:13px;margin-top:6px;text-align:left;">{{ $message }}</div>
        @enderror
        <button type="submit" class="btn">Enviar solicitud</button>
    </form>

    <div class="divider"></div>

    <div class="footer">
        <strong>Home del Valle</strong><br>
        +52 55 1345 0978 &middot; contacto@homedelvalle.mx<br>
        Heriberto Frías 903-C, Col. del Valle, CDMX
    </div>
</div>
</body>
</html>
