<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Presentación Home del Valle — {{ $captacion->client->name ?? 'Propietario' }}</title>
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#f8fafc; color:#1e293b; -webkit-font-smoothing:antialiased; }

/* Header */
.header { background:#1e1b4b; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; }
.header-logo { height:32px; object-fit:contain; }
.header-logo-text { font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:rgba(199,210,254,.7); }
.header-badge { background:rgba(16,185,129,.2); border:1px solid rgba(16,185,129,.3); color:#6ee7b7; font-size:10px; font-weight:700; padding:3px 10px; border-radius:20px; letter-spacing:1px; text-transform:uppercase; white-space:nowrap; }

/* Hero info */
.hero { background:#fff; border-bottom:1px solid #e2e8f0; padding:20px; }
.hero h1 { font-size:18px; font-weight:800; color:#1e1b4b; margin-bottom:4px; }
.hero p  { font-size:13px; color:#64748b; line-height:1.5; }
.hero-meta { display:flex; gap:16px; margin-top:12px; flex-wrap:wrap; }
.hero-tag  { font-size:11px; font-weight:600; color:#1e1b4b; background:#f1f5f9; border-radius:6px; padding:4px 10px; }

/* PDF area */
.pdf-area { padding:16px 20px; }
.pdf-frame-wrap { background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); }
.pdf-frame { width:100%; height:80vh; min-height:500px; border:none; display:block; }

/* Mobile fallback (no iframe) */
.pdf-mobile-card { display:none; background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:32px 20px; text-align:center; }
.pdf-mobile-card .icon { font-size:48px; margin-bottom:16px; }
.pdf-mobile-card h3 { font-size:17px; font-weight:700; color:#1e1b4b; margin-bottom:8px; }
.pdf-mobile-card p  { font-size:13px; color:#64748b; margin-bottom:20px; line-height:1.6; }

/* Buttons */
.btn-primary { display:inline-flex; align-items:center; justify-content:center; gap:8px; background:#1e1b4b; color:#fff; font-family:inherit; font-size:14px; font-weight:600; padding:12px 24px; border-radius:8px; text-decoration:none; border:none; cursor:pointer; transition:opacity .15s; width:100%; }
.btn-primary:hover { opacity:.88; }
.btn-outline { display:inline-flex; align-items:center; justify-content:center; gap:8px; background:#fff; color:#1e1b4b; font-family:inherit; font-size:14px; font-weight:600; padding:12px 24px; border-radius:8px; text-decoration:none; border:2px solid #e2e8f0; cursor:pointer; transition:all .15s; width:100%; }
.btn-outline:hover { border-color:#1e1b4b; }
.btn-green { background:#10b981; color:#fff; }

.actions { padding:0 20px 20px; display:flex; flex-direction:column; gap:10px; }

/* Agent card */
.agent-section { padding:20px; border-top:1px solid #e2e8f0; background:#fff; margin-top:0; }
.agent-section h3 { font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#94a3b8; margin-bottom:14px; }
.agent-card { display:flex; align-items:center; gap:14px; background:#1e1b4b; border-radius:12px; padding:16px 18px; }
.agent-av   { width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,.12); border:2px solid rgba(255,255,255,.2); color:#fff; font-size:17px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.agent-info h4 { font-size:14px; font-weight:700; color:#fff; margin-bottom:3px; }
.agent-info p  { font-size:12px; color:rgba(199,210,254,.75); margin:1px 0; }
.agent-actions { display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; }
.agent-btn { flex:1; min-width:120px; display:flex; align-items:center; justify-content:center; gap:6px; padding:9px 12px; border-radius:8px; font-size:12.5px; font-weight:600; text-decoration:none; font-family:inherit; }
.agent-btn.wa { background:#25D366; color:#fff; }
.agent-btn.tel { background:#f1f5f9; color:#1e1b4b; }
.agent-btn.mail { background:#f1f5f9; color:#1e1b4b; }

/* Footer */
.footer { padding:20px; text-align:center; }
.footer p { font-size:10px; color:#94a3b8; line-height:1.6; }

/* Desktop tweaks */
@media (min-width:768px) {
    .pdf-area    { padding:24px 32px; }
    .actions     { padding:0 32px 24px; flex-direction:row; }
    .btn-primary, .btn-outline { width:auto; flex:1; }
    .agent-section { padding:24px 32px; }
    .hero        { padding:24px 32px; }
    .header      { padding:16px 32px; }
}

/* iOS / no-iframe detection */
@media (pointer:coarse) and (max-width:600px) {
    .pdf-frame-wrap { display:none; }
    .pdf-mobile-card { display:block; }
}
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    @php $s = App\Models\SiteSetting::first(); @endphp
    @if($s?->logo_path_dark)
        <img src="{{ url('storage/' . $s->logo_path_dark) }}" class="header-logo" alt="Home del Valle">
    @elseif($s?->logo_path)
        <img src="{{ url('storage/' . $s->logo_path) }}" class="header-logo" alt="Home del Valle">
    @else
        <span class="header-logo-text">Home del Valle</span>
    @endif
    <span class="header-badge">Presentación inicial</span>
</div>

{{-- Hero --}}
<div class="hero">
    <h1>Propuesta para {{ $captacion->client->name ?? 'el propietario' }}</h1>
    <p>Presentación inicial de Home del Valle para tu inmueble</p>
    <div class="hero-meta">
        @if($captacion->property)
        <span class="hero-tag">{{ $captacion->property->property_type_label ?? $captacion->property->property_type }}</span>
        @endif
        @if($captacion->property?->colony || $captacion->property_address)
        <span class="hero-tag">{{ $captacion->property?->colony ?? $captacion->property_address }}</span>
        @endif
        <span class="hero-tag">{{ $captacion->intent_label }}</span>
    </div>
</div>

{{-- PDF Desktop --}}
<div class="pdf-area">
    <div class="pdf-frame-wrap">
        <iframe src="{{ route('presentation.pdf.inline', $send->tracking_token) }}"
                class="pdf-frame"
                title="Presentación Home del Valle">
        </iframe>
    </div>

    {{-- Mobile fallback --}}
    <div class="pdf-mobile-card">
        <div class="icon">📄</div>
        <h3>Tu presentación está lista</h3>
        <p>Toca el botón para ver o descargar la presentación de Home del Valle para tu inmueble.</p>
        <a href="{{ route('presentation.pdf.inline', $send->tracking_token) }}" target="_blank" class="btn-primary btn-green" style="margin-bottom:10px;">
            Ver presentación →
        </a>
    </div>
</div>

{{-- Action buttons --}}
<div class="actions">
    <a href="{{ route('presentation.download', $send->tracking_token) }}"
       class="btn-primary">
        ↓ Descargar PDF
    </a>
    @if($captacion->client?->whatsapp || $captacion->client?->phone)
    @php
        $agentPhone = $send->sentBy?->phone ?? '';
        $agentPhoneClean = preg_replace('/\D+/', '', $agentPhone);
        if ($agentPhoneClean && !str_starts_with($agentPhoneClean, '52')) $agentPhoneClean = '52'.$agentPhoneClean;
        $agentWaMsg = $agentPhoneClean
            ? urlencode("Hola, vi la presentación de Home del Valle para mi inmueble. Quisiera hablar contigo.")
            : null;
    @endphp
    @if($agentPhoneClean && $agentWaMsg)
    <a href="https://wa.me/{{ $agentPhoneClean }}?text={{ $agentWaMsg }}" target="_blank" class="btn-outline">
        💬 Responder por WhatsApp
    </a>
    @endif
    @endif
</div>

{{-- Agent card --}}
@if($send->sentBy)
<div class="agent-section">
    <h3>Tu agente en Home del Valle</h3>
    <div class="agent-card">
        <div class="agent-av">{{ strtoupper(substr($send->sentBy->name, 0, 1)) }}</div>
        <div class="agent-info">
            <h4>{{ $send->sentBy->name }}</h4>
            <p>Agente · Home del Valle Bienes Raíces</p>
            @if($send->sentBy->phone)<p>{{ $send->sentBy->phone }}</p>@endif
            @if($send->sentBy->email)<p>{{ $send->sentBy->email }}</p>@endif
        </div>
    </div>
    <div class="agent-actions">
        @if($send->sentBy->phone)
        @php $agentClean2 = preg_replace('/\D+/', '', $send->sentBy->phone); if(!str_starts_with($agentClean2,'52')) $agentClean2='52'.$agentClean2; @endphp
        <a href="https://wa.me/{{ $agentClean2 }}" target="_blank" class="agent-btn wa">💬 WhatsApp</a>
        <a href="tel:{{ $send->sentBy->phone }}" class="agent-btn tel">📞 Llamar</a>
        @endif
        @if($send->sentBy->email)
        <a href="mailto:{{ $send->sentBy->email }}" class="agent-btn mail">✉️ Email</a>
        @endif
    </div>
</div>
@endif

{{-- Footer --}}
<div class="footer">
    <p>
        Este documento es informativo y no constituye oferta vinculante.<br>
        Los términos comerciales se formalizan al firmar el contrato con Home del Valle Bienes Raíces.<br>
        Heriberto Frías 903-A · Col. del Valle · CDMX
    </p>
</div>

</body>
</html>
