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
.card{background:#fff;border-radius:20px;border:1px solid #E6EAF1;max-width:540px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.06);overflow:hidden;}
.logo{display:block;height:34px;margin:0 auto;}
.card-header{padding:24px 36px;border-bottom:1px solid #EEF1F6;text-align:center;}
.cover-img{width:100%;height:200px;object-fit:cover;display:block;}
.section{padding:28px 36px;border-bottom:1px solid #EEF1F6;}
.section:last-of-type{border-bottom:none;}
.section-num{font-size:11px;font-weight:700;color:#9AA6B5;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;}
.section-title{font-size:17px;font-weight:800;color:#0E304B;margin-bottom:18px;letter-spacing:-.3px;}

/* Reaction buttons */
.reaction-row{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
.reaction-btn{display:flex;flex-direction:column;align-items:center;gap:5px;padding:13px 16px;border-radius:13px;border:2px solid #E6EAF1;background:#fff;cursor:pointer;font-size:12px;font-weight:700;color:#5A6573;transition:all .14s;min-width:90px;flex:1;}
.reaction-btn:hover{border-color:#0E304B;background:#F6F8FB;}
.reaction-btn input[type="radio"]{display:none;}
.reaction-btn .emoji{font-size:24px;line-height:1;}
.reaction-btn.selected{border-color:#0E304B;background:#EFF6FF;color:#0E304B;}
.reaction-btn.liked.selected{border-color:#22C55E;background:#ECFDF5;color:#166534;}
.reaction-btn.disliked.selected{border-color:#EF4444;background:#FEF2F2;color:#991B1B;}
.reaction-btn.neutral.selected{border-color:#F59E0B;background:#FFFBEB;color:#92400E;}

/* Price buttons */
.price-row{display:flex;gap:10px;}
.price-btn{display:flex;flex-direction:column;align-items:center;gap:4px;padding:13px 10px;border-radius:13px;border:2px solid #E6EAF1;background:#fff;cursor:pointer;font-size:12px;font-weight:700;color:#5A6573;transition:all .14s;flex:1;}
.price-btn:hover{border-color:#0E304B;background:#F6F8FB;}
.price-btn input[type="radio"]{display:none;}
.price-btn .p-icon{font-size:22px;line-height:1;}
.price-btn.selected{border-color:#0E304B;background:#EFF6FF;color:#0E304B;}
.price-btn.fair.selected{border-color:#22C55E;background:#ECFDF5;color:#166534;}
.price-btn.negotiable.selected{border-color:#F59E0B;background:#FFFBEB;color:#92400E;}
.price-btn.high.selected{border-color:#EF4444;background:#FEF2F2;color:#991B1B;}

/* Stars */
.stars{display:flex;gap:8px;justify-content:center;flex-direction:row-reverse;}
.stars input{display:none;}
.stars label{font-size:36px;cursor:pointer;color:#D1D5DB;transition:color .12s;line-height:1;}
.stars label:hover,.stars label:hover ~ label,
.stars input:checked ~ label{color:#F59E0B;}

/* Comment */
textarea.comment-box{width:100%;border:1px solid #E6EAF1;border-radius:12px;padding:12px 14px;font-family:inherit;font-size:14px;resize:vertical;min-height:85px;color:#1E293B;outline:none;}
textarea.comment-box:focus{border-color:#0E304B;}

/* Submit */
.submit-area{padding:24px 36px;background:#F8FAFC;border-top:1px solid #EEF1F6;}
.btn-submit{display:block;width:100%;padding:14px;background:#0E304B;color:#fff;border:none;border-radius:12px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;transition:opacity .15s;}
.btn-submit:disabled{opacity:.35;cursor:default;}
.btn-submit:not(:disabled):hover{opacity:.88;}
.addr-chip{display:inline-block;background:#F6F8FB;border:1px solid #E6EAF1;border-radius:8px;padding:5px 13px;font-size:13px;font-weight:600;color:#0E304B;margin-top:10px;}
.footer{text-align:center;padding:18px 36px;font-size:12px;color:#9AA6B5;}
.progress-dots{display:flex;gap:6px;justify-content:center;margin-top:14px;}
.dot{width:8px;height:8px;border-radius:50%;background:#E6EAF1;}
.dot.active{background:#0E304B;}
</style>
</head>
<body>
<div class="card">

    {{-- Header --}}
    <div class="card-header">
        <img src="{{ url('img/email/logo-azul.png') }}" alt="Home del Valle" class="logo">
    </div>

    @if($interaction->feedback_submitted_at)
        {{-- Already submitted --}}
        <div class="section" style="text-align:center;padding:48px 36px;">
            <div style="font-size:44px;margin-bottom:16px;">💬</div>
            <h1 style="font-size:22px;font-weight:800;color:#0E304B;margin-bottom:10px;">¡Gracias por tu opinión!</h1>
            <p style="font-size:14.5px;color:#5A6573;line-height:1.6;">Tu respuesta ya fue registrada. Nos ayuda a ofrecerte mejores opciones.</p>
        </div>
    @else

        {{-- Cover photo --}}
        @php
            $property = $interaction->property;
            $coverPhoto = null;
            if ($property) {
                $property->loadMissing('photos');
                $coverPhoto = $property->cover_photo_url;
            }
        @endphp
        @if($coverPhoto)
            <img src="{{ $coverPhoto }}" alt="Inmueble" class="cover-img">
        @endif

        {{-- Intro --}}
        <div class="section" style="border-bottom:1px solid #EEF1F6;text-align:center;padding-bottom:20px;">
            <h1 style="font-size:20px;font-weight:800;color:#0E304B;margin-bottom:8px;">¿Qué te pareció?</h1>
            @if($property?->address)
            <div class="addr-chip">{{ $property->address }}{{ $property->colony ? ', ' . $property->colony : '' }}</div>
            @endif
            <p style="font-size:13.5px;color:#5A6573;margin-top:12px;line-height:1.5;">Son 3 preguntas rápidas. Tu opinión es anónima.</p>
            <div class="progress-dots">
                <div class="dot active" id="dot-1"></div>
                <div class="dot" id="dot-2"></div>
                <div class="dot" id="dot-3"></div>
            </div>
        </div>

        <form action="{{ route('visit.feedback', $interaction->visit_token) }}" method="POST" id="feedbackForm">
            @csrf

            {{-- Sección 1: Reacción --}}
            <div class="section">
                <div class="section-num">Pregunta 1 de 3</div>
                <div class="section-title">¿Qué te pareció el inmueble?</div>
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
            </div>

            {{-- Sección 2: Percepción del precio --}}
            <div class="section" id="section-price" style="display:none;">
                <div class="section-num">Pregunta 2 de 3</div>
                <div class="section-title">¿Cómo viste el precio?</div>
                <div class="price-row">
                    <label class="price-btn fair" id="btn-fair">
                        <input type="radio" name="price_perception" value="fair" onchange="selectPrice('fair')">
                        <span class="p-icon">✅</span>
                        Justo
                    </label>
                    <label class="price-btn negotiable" id="btn-negotiable">
                        <input type="radio" name="price_perception" value="negotiable" onchange="selectPrice('negotiable')">
                        <span class="p-icon">💬</span>
                        Negociable
                    </label>
                    <label class="price-btn high" id="btn-high">
                        <input type="radio" name="price_perception" value="high" onchange="selectPrice('high')">
                        <span class="p-icon">💸</span>
                        Alto
                    </label>
                </div>
            </div>

            {{-- Sección 3: Calificación del asesor --}}
            <div class="section" id="section-advisor" style="display:none;">
                <div class="section-num">Pregunta 3 de 3</div>
                <div class="section-title">¿Cómo fue la atención de tu asesor?</div>
                <div class="stars" id="starRow">
                    <input type="radio" name="advisor_rating" id="s5" value="5" onchange="selectStar(5)">
                    <label for="s5" title="Excelente">★</label>
                    <input type="radio" name="advisor_rating" id="s4" value="4" onchange="selectStar(4)">
                    <label for="s4" title="Muy buena">★</label>
                    <input type="radio" name="advisor_rating" id="s3" value="3" onchange="selectStar(3)">
                    <label for="s3" title="Buena">★</label>
                    <input type="radio" name="advisor_rating" id="s2" value="2" onchange="selectStar(2)">
                    <label for="s2" title="Regular">★</label>
                    <input type="radio" name="advisor_rating" id="s1" value="1" onchange="selectStar(1)">
                    <label for="s1" title="Mejorable">★</label>
                </div>
                <p id="star-label" style="text-align:center;font-size:13px;color:#9AA6B5;margin-top:12px;min-height:20px;"></p>

                <div style="margin-top:20px;">
                    <textarea name="visitor_comment" class="comment-box"
                              placeholder="¿Algo que quieras comentar sobre el inmueble o la atención? (opcional)" maxlength="300"></textarea>
                </div>
            </div>

            {{-- Submit --}}
            <div class="submit-area">
                <button type="submit" class="btn-submit" id="btnSubmit" disabled>Enviar mi opinión</button>
            </div>
        </form>
    @endif

    <div class="footer">&copy; Home del Valle {{ date('Y') }}</div>
</div>

<script>
var step = 1;
var reactionSelected = false;
var priceSelected = false;
var starSelected = false;

var starLabels = {1:'Mejorable',2:'Regular',3:'Buena',4:'Muy buena',5:'Excelente'};

// Pre-select reaction from ?r= email click
(function(){
    var params = new URLSearchParams(window.location.search);
    var preReaction = params.get('r');
    if (preReaction && ['liked','neutral','disliked'].includes(preReaction)) {
        var radio = document.querySelector('input[value="' + preReaction + '"]');
        if (radio) { radio.checked = true; selectReaction(preReaction); }
    }
})();

function selectReaction(val) {
    ['liked','neutral','disliked'].forEach(function(v) {
        document.getElementById('btn-' + v).classList.toggle('selected', v === val);
    });
    reactionSelected = true;
    if (step < 2) advanceTo(2);
    checkSubmit();
}

function selectPrice(val) {
    ['fair','negotiable','high'].forEach(function(v) {
        document.getElementById('btn-' + v).classList.toggle('selected', v === val);
    });
    priceSelected = true;
    if (step < 3) advanceTo(3);
    checkSubmit();
}

function selectStar(val) {
    starSelected = true;
    document.getElementById('star-label').textContent = starLabels[val] || '';
    checkSubmit();
}

function advanceTo(newStep) {
    step = newStep;
    if (newStep >= 2) document.getElementById('section-price').style.display = 'block';
    if (newStep >= 3) document.getElementById('section-advisor').style.display = 'block';
    // Update progress dots
    for (var i = 1; i <= 3; i++) {
        document.getElementById('dot-' + i).classList.toggle('active', i <= newStep);
    }
    // Smooth scroll to new section
    var sectionId = newStep === 2 ? 'section-price' : 'section-advisor';
    var el = document.getElementById(sectionId);
    if (el) setTimeout(function(){ el.scrollIntoView({behavior:'smooth',block:'start'}); }, 80);
}

function checkSubmit() {
    // Enable submit when at least reaction is selected (price + advisor optional but revealed)
    document.getElementById('btnSubmit').disabled = !reactionSelected;
}
</script>
</body>
</html>
