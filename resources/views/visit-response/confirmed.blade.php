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
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:520px;width:100%;padding:48px 40px;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.06);}
.logo{margin:0 auto 32px;display:block;height:36px;}
.check-circle{width:80px;height:80px;border-radius:50%;background:#ECFDF5;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;}
.check-circle svg{display:block;}
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
/* Feedback section */
.feedback-title{font-size:16px;font-weight:700;color:#0E304B;margin-bottom:18px;}
.feedback-subtitle{font-size:13px;color:#7A8594;margin-bottom:20px;}
.reaction-row{display:flex;gap:12px;justify-content:center;margin-bottom:20px;flex-wrap:wrap;}
.reaction-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 18px;border-radius:14px;border:2px solid #E6EAF1;background:#fff;cursor:pointer;font-size:12px;font-weight:700;color:#5A6573;transition:all .15s;min-width:96px;}
.reaction-btn:hover{border-color:#0E304B;background:#F6F8FB;}
.reaction-btn input[type="radio"]{display:none;}
.reaction-btn .emoji{font-size:26px;line-height:1;}
.reaction-btn.selected{border-color:#0E304B;background:#EFF6FF;color:#0E304B;}
.reaction-btn.liked.selected{border-color:#22C55E;background:#ECFDF5;color:#166534;}
.reaction-btn.disliked.selected{border-color:#EF4444;background:#FEF2F2;color:#991B1B;}
textarea.comment-box{width:100%;border:1px solid #E6EAF1;border-radius:12px;padding:12px 14px;font-family:inherit;font-size:14px;resize:vertical;min-height:80px;color:#1E293B;margin-bottom:16px;outline:none;}
textarea.comment-box:focus{border-color:#0E304B;}
.btn-submit{display:block;width:100%;padding:14px;background:#0E304B;color:#fff;border:none;border-radius:12px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;transition:opacity .15s;margin-bottom:10px;}
.btn-submit:hover{opacity:.88;}
.skip-link{display:block;font-size:13px;color:#9AA6B5;cursor:pointer;text-decoration:none;}
.skip-link:hover{color:#5A6573;}
</style>
</head>
<body>
<div class="card">
    <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo">

    <div class="check-circle">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    </div>

    <h1>¡Confirmado!</h1>

    @if($interaction->scheduled_at)
    <p class="subtext">
        Te esperamos el <strong>{{ ucfirst($interaction->scheduled_at->locale('es')->dayName) }}</strong>
        a las <strong>{{ $interaction->scheduled_at->format('g:i A') }}</strong>
    </p>
    @else
    <p class="subtext">Tu asistencia ha sido registrada. Tu asesor estará listo para recibirte.</p>
    @endif

    @if($interaction->scheduled_at || $interaction->property?->address)
    <div class="detail-card">
        @if($interaction->scheduled_at)
        <div class="detail-row">
            <div>
                <div class="detail-label">Fecha y hora</div>
                <div class="detail-value">
                    {{ ucfirst($interaction->scheduled_at->locale('es')->isoFormat('dddd D [de] MMMM, YYYY')) }}
                    — {{ $interaction->scheduled_at->format('g:i A') }}
                </div>
            </div>
        </div>
        @endif
        @if($interaction->property?->address)
        <div class="detail-row">
            <div>
                <div class="detail-label">Dirección</div>
                <div class="detail-value">
                    {{ $interaction->property->address }}
                    @if($interaction->property->colony)
                        , {{ $interaction->property->colony }}
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Feedback section (only if feedback not yet submitted) --}}
    @if(!$interaction->feedback_submitted_at)
    <div class="divider"></div>
    <div class="feedback-title">¿Qué te pareció el inmueble?</div>
    <p class="feedback-subtitle">Tu opinión anónima nos ayuda a mejorar el servicio.</p>

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
                No cumplió
            </label>
        </div>

        <textarea name="visitor_comment" class="comment-box" placeholder="Comentario (opcional) — máx. 300 caracteres" maxlength="300"></textarea>

        <button type="submit" class="btn-submit">Compartir opinión</button>
    </form>
    <a href="/" class="skip-link">No, gracias</a>
    @else
    <p class="subtext" style="margin-bottom:0;">¡Gracias por tu opinión! Ya la tenemos registrada.</p>
    @endif

    <div class="divider"></div>

    <div class="footer">
        <strong>Home del Valle</strong><br>
        +52 55 1345 0978 &middot; contacto@homedelvalle.mx<br>
        Heriberto Frías 903-A, Col. del Valle, CDMX<br>
        © Home del Valle 2026
    </div>
</div>

<script>
function selectReaction(val) {
    ['liked','neutral','disliked'].forEach(function(v) {
        document.getElementById('btn-' + v).classList.toggle('selected', v === val);
    });
}
</script>
</body>
</html>
