@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recibo — Cuota de Investigación</title>
<style>
{!! $brandCssVars ?? '' !!}
@if($brandFontB64)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $brandFontB64 }}') format('woff2');
}
@endif

*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
@page { size: 215.9mm 279.4mm; margin: 0; }

body {
    font-family: 'Inter', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 11px;
    line-height: 1.6;
    width: 215.9mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.page { width: 215.9mm; min-height: 279.4mm; display: flex; flex-direction: column; }
.page-header-inner {
    flex-shrink: 0; background: var(--hdv-navy); border-bottom: 4px solid var(--hdv-accent);
    padding: 10px 52px; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 18px; max-width: 140px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 12px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.inner { flex: 1; padding: 40px 52px; }

.doc-title { font-size: 18px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; }
.doc-sub   { font-size: 10px; color: #94a3b8; text-align: center; margin-bottom: 28px; letter-spacing: .5px; }

.folio-box { text-align: center; margin-bottom: 24px; }
.folio-box .folio { display: inline-block; font-size: 11px; font-weight: 700; color: var(--hdv-navy); background: #f1f5f9; border-radius: 8px; padding: 6px 18px; }

p { color: #334155; font-size: 11.5px; line-height: 1.7; margin-bottom: 12px; text-align: justify; }

table.datos { width: 100%; border-collapse: collapse; margin: 20px 0 28px; }
table.datos td { padding: 9px 0; border-bottom: 1px solid #e2e8f0; font-size: 11.5px; }
table.datos td.label { color: #64748b; width: 42%; }
table.datos td.value { color: #0f172a; font-weight: 600; }

.monto-box { text-align: center; margin: 28px 0; padding: 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; }
.monto-box .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 4px; }
.monto-box .monto { font-size: 26px; font-weight: 800; color: var(--hdv-navy); }

.privacy-note { font-size: 8.5px; color: #94a3b8; line-height: 1.6; margin-top: 32px; border-top: 1px solid #f1f5f9; padding-top: 10px; }

.page-foot {
    flex-shrink: 0; background: #fff; border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center; font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }
</style>
</head>
<body>
<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Recibo de Pago</span>
  </div>

  <div class="inner">
    <div class="doc-title">Recibo de Pago</div>
    <div class="doc-sub">Cuota de Investigación</div>

    <div class="folio-box"><span class="folio">Folio RI-{{ str_pad($rental->id, 5, '0', STR_PAD_LEFT) }}</span></div>

    <p>Home del Valle Bienes Raíces hace constar la recepción del pago de la cuota de investigación correspondiente al proceso de arrendamiento del inmueble ubicado en <strong>{{ $property->address ?? 'el inmueble de interés' }}</strong>, a nombre de <strong>{{ $tenant->name ?? '—' }}</strong> en su carácter de candidato arrendatario.</p>

    <table class="datos">
      <tr><td class="label">Candidato arrendatario</td><td class="value">{{ $tenant->name ?? '—' }}</td></tr>
      <tr><td class="label">Inmueble</td><td class="value">{{ $property->address ?? '—' }}{{ $property?->colony ? ', ' . $property->colony : '' }}</td></tr>
      <tr><td class="label">Fecha de pago</td><td class="value">{{ $rental->investigacion_paid_at?->translatedFormat('d \d\e F \d\e Y') ?? '—' }}</td></tr>
      <tr><td class="label">Forma de pago</td><td class="value">{{ \App\Models\RentalProcess::INVESTIGACION_PAYMENT_METHODS[$rental->investigacion_payment_method] ?? '—' }}</td></tr>
    </table>

    <div class="monto-box">
      <div class="label">Monto recibido</div>
      <div class="monto">${{ number_format($rental->investigacion_amount, 2) }} MXN</div>
    </div>

    <p>Esta cuota cubre el costo del proceso de investigación del candidato arrendatario (verificación de referencias, buró de crédito y documentación) y <strong>no es reembolsable</strong>, independientemente del resultado de dicha investigación.</p>

    @if($rental->investigacion_notes)
    <p><strong>Notas:</strong> {{ $rental->investigacion_notes }}</p>
    @endif

    <div class="privacy-note">Documento generado automáticamente por el sistema de Home del Valle. Folio RI-{{ str_pad($rental->id, 5, '0', STR_PAD_LEFT) }}. Este recibo no tiene validez fiscal.</div>
  </div>

  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>Pocos inmuebles. Más control. Mejores resultados.</span>
    <span>Recibo de Cuota de Investigación</span>
  </div>
</div>
</body>
</html>
