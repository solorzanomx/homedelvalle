<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tu opinión — Home del Valle</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Arial,sans-serif;background:#F1F4F8;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;}
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:520px;width:100%;padding:48px 40px;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.06);}
.logo{margin:0 auto 32px;display:block;height:36px;}
h1{font-size:24px;font-weight:800;color:#0E304B;margin-bottom:10px;letter-spacing:-.4px;}
.subtext{font-size:15px;color:#5A6573;line-height:1.6;margin-bottom:28px;}
.divider{height:1px;background:#EEF1F6;margin:28px 0;}
.reaction-row{display:flex;gap:12px;justify-content:center;margin-bottom:20px;flex-wrap:wrap;}
.reaction-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 18px;border-radius:14px;border:2px solid #E6EAF1;background:#fff;cursor:pointer;font-size:12px;font-weight:700;color:#5A6573;transition:all .15s;min-width:96px;}
.reaction-btn:hover{border-color:#0E304B;background:#F6F8FB;}
.reaction-btn input[type="radio"]{display:none;}
.reaction-btn .emoji{font-size:26px;line-height:1;}
.reaction-btn.selected{border-color:#0E304B;background:#EFF6FF;color:#0E304B;}
.reaction-btn.liked.selected{border-color:#22C55E;background:#ECFDF5;color:#166534;}
.reaction-btn.disliked.selected{border-color:#EF4444;background:#FEF2F2;color:#991B1B;}
textarea.comment-box{width:100%;border:1px solid #E6EAF1;border-radius:12px;padding:12px 14px;font-family:inherit;font-size:14px;resize:vertical;min-height:90px;color:#1E293B;margin-bottom:16px;outline:none;}
textarea.comment-box:focus{border-color:#0E304B;}
.btn-submit{display:block;width:100%;padding:14px;background:#0E304B;color:#fff;border:none;border-radius:12px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;transition:opacity .15s;margin-bottom:10px;}
.btn-submit:disabled{opacity:.4;cursor:default;}
.btn-submit:not(:disabled):hover{opacity:.88;}
.footer{margin-top:28px;font-size:12px;color:#9AA6B5;}
.addr-chip{display:inline-block;background:#F6F8FB;border:1px solid #E6EAF1;border-radius:8px;padding:6px 14px;font-size:13px;font-weight:600;color:#0E304B;margin-bottom:20px;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo">

    @if($interaction->feedback_submitted_at)
        <div style="font-size:40px;margin-bottom:16px;">💬</div>
        <h1>¡Gracias por tu opinión!</h1>
        <p class="subtext">Ya tenemos tu respuesta registrada. Tu opinión nos ayuda a mejorar.</p>
    @else
        <div style="font-size:40px;margin-bottom:16px;">🏠</div>
        <h1>¿Qué te pareció el inmueble?</h1>

        @if($interaction->property?->address)
        <div class="addr-chip">
            {{ $interaction->property->address }}{{ $interaction->property->colony ? ', ' . $interaction->property->colony : '' }}
        </div>
        @endif

        <p class="subtext">Tu opinión es anónima y nos ayuda a ofrecerte mejores opciones.</p>

        <form action="{{ route('visit.feedback', $interaction->visit_token) }}" method="POST" id="feedbackForm">
            @csrf
            <div class="reaction-row">
                <label class="reaction-btn liked" id="btn-liked">
                    <input type="radio" name="visitor_reaction" value="liked" onchange="selectReaction('liked')">
                    <span class="emoji">👍</span>
                    Me gustó
                </label>
                <label class="reaction-btn neutral" id="btn-neutral">
                    <input type="radio" name="visitor_reaction" value="neutral" onchange="selectReaction('neutral')">
                    <span class="emoji">🤔</span>
                    Tengo dudas
                </label>
                <label class="reaction-btn disliked" id="btn-disliked">
                    <input type="radio" name="visitor_reaction" value="disliked" onchange="selectReaction('disliked')">
                    <span class="emoji">❌</span>
                    No era lo que buscaba
                </label>
            </div>

            <textarea name="visitor_comment" class="comment-box"
                      placeholder="¿Algo en específico? Espacio, precio, distribución... (opcional)" maxlength="300"></textarea>

            <button type="submit" class="btn-submit" id="btnSubmit" disabled>Enviar mi opinión</button>
        </form>
    @endif

    <div class="divider"></div>
    <div class="footer">&copy; Home del Valle {{ date('Y') }}</div>
</div>

<script>
// Pre-select reaction if ?r= param was clicked from email
var params = new URLSearchParams(window.location.search);
var preReaction = params.get('r');
if (preReaction && ['liked','neutral','disliked'].includes(preReaction)) {
    var radio = document.querySelector('input[value="' + preReaction + '"]');
    if (radio) { radio.checked = true; selectReaction(preReaction); }
}

function selectReaction(val) {
    ['liked','neutral','disliked'].forEach(function(v) {
        document.getElementById('btn-' + v).classList.toggle('selected', v === val);
    });
    document.getElementById('btnSubmit').disabled = false;
}
</script>
</body>
</html>
