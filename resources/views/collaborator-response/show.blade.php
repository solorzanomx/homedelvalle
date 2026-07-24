<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Autorización de publicación — Home del Valle</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;background:#F1F4F8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:560px;width:100%;padding:44px 40px;box-shadow:0 4px 24px rgba(0,0,0,.06);}
.logo{margin:0 auto 28px;display:block;height:34px;}
h1{font-size:22px;font-weight:800;color:#0E304B;margin-bottom:10px;letter-spacing:-.3px;text-align:center;}
.subtext{font-size:15px;color:#5A6573;line-height:1.65;margin-bottom:28px;text-align:center;}
.preview{border:1.5px solid #E6EAF1;border-radius:16px;background:#F6F8FB;padding:22px;display:flex;align-items:center;gap:16px;margin-bottom:24px;}
.avatar{width:64px;height:64px;border-radius:50%;object-fit:cover;flex-shrink:0;background:#0E304B;}
.avatar-fallback{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#3486C7,#0F304B);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;flex-shrink:0;}
.p-name{font-size:17px;font-weight:800;color:#0E304B;}
.p-role{font-size:13px;font-weight:700;color:#2270B0;margin-top:2px;}
.p-bio{font-size:13.5px;color:#5A6573;line-height:1.55;margin-top:8px;}
.p-link{font-size:13px;font-weight:700;color:#2270B0;margin-top:8px;display:inline-block;}
.consent-text{font-size:14px;color:#5A6573;line-height:1.7;margin-bottom:28px;}
.consent-text a{color:#2270B0;font-weight:700;}
.actions{display:flex;gap:12px;flex-wrap:wrap;}
.btn{flex:1;min-width:180px;border-radius:12px;padding:15px;font-size:15px;font-weight:700;cursor:pointer;font-family:inherit;text-align:center;border:none;transition:opacity .2s;}
.btn-primary{background:#0E304B;color:#fff;}
.btn-primary:hover{opacity:.9;}
.btn-secondary{background:#fff;color:#5A6573;border:1.5px solid #D5DCE7;}
.btn-secondary:hover{background:#F6F8FB;}
.decline-box{display:none;margin-top:18px;}
.decline-box.open{display:block;}
textarea{width:100%;border:1.5px solid #D5DCE7;border-radius:12px;padding:14px 16px;font-family:inherit;font-size:14px;color:#0E304B;resize:vertical;min-height:90px;outline:none;}
textarea:focus{border-color:#2E80C6;}
.decline-submit{margin-top:12px;width:100%;}
.divider{height:1px;background:#EEF1F6;margin:28px 0;}
.footer{font-size:12px;color:#9AA6B5;line-height:1.8;text-align:center;}
.footer strong{color:#0E304B;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo">

    <h1>Hola{{ $collaborator->name ? ', ' . explode(' ', trim($collaborator->name))[0] : '' }}</h1>
    <p class="subtext">Estamos armando una sección en homedelvalle.mx para presentar a las personas con las que trabajamos de la mano — y nos encantaría incluirte ahí. Antes de publicar nada, aquí abajo puedes ver exactamente cómo se vería.</p>

    <div class="preview">
        @if($collaborator->photo_path)
            <img src="{{ Storage::url($collaborator->photo_path) }}" alt="{{ $collaborator->name }}" class="avatar">
        @else
            <div class="avatar-fallback">{{ strtoupper(substr($collaborator->name, 0, 1)) }}</div>
        @endif
        <div>
            <div class="p-name">{{ $collaborator->name }}</div>
            <div class="p-role">{{ $collaborator->role }}</div>
            @if($collaborator->bio)
                <div class="p-bio">{{ $collaborator->bio }}</div>
            @endif
            @if($collaborator->link_url)
                <a href="{{ $collaborator->link_url }}" target="_blank" rel="noopener" class="p-link">{{ $collaborator->link_label ?: $collaborator->link_url }} →</a>
            @endif
        </div>
    </div>

    <p class="consent-text">
        Si te parece bien, al autorizar nos das permiso de usar tu nombre, foto, rol y el enlace que nos diste, en nuestro sitio y en materiales relacionados con Home del Valle — redes sociales, presentaciones, documentos institucionales — para presentarte como parte de nuestra red de colaboradores. Puedes pedirnos que lo bajemos cuando quieras, sin necesidad de dar explicación, escribiendo a <a href="mailto:contacto@homedelvalle.mx">contacto@homedelvalle.mx</a>.
        <br><br>
        Queda registrado con fecha y una copia de lo que aprobaste, para que los dos tengamos claridad.
    </p>

    <form method="POST" action="{{ route('collaborator.consent.authorize', $collaborator->consent_token) }}">
        @csrf
        <div class="actions">
            <button type="submit" class="btn btn-primary">Sí, autorizo</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('declineBox').classList.add('open'); this.closest('.actions').style.display='none';">Prefiero que no / algo está mal</button>
        </div>
    </form>

    <div class="decline-box" id="declineBox">
        <form method="POST" action="{{ route('collaborator.consent.decline', $collaborator->consent_token) }}">
            @csrf
            <textarea name="decline_note" maxlength="500" placeholder="Cuéntanos qué prefieres cambiar o por qué no (opcional)"></textarea>
            <button type="submit" class="btn btn-primary decline-submit">Enviar</button>
        </form>
    </div>

    <div class="divider"></div>

    <div class="footer">
        <strong>Home del Valle</strong><br>
        +52 55 1345 0978 &middot; contacto@homedelvalle.mx
    </div>
</div>
</body>
</html>
