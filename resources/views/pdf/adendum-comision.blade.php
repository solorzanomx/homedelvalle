@php include(resource_path('views/pdf/_brand_data.php')); @endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Adéndum No. {{ $addendum->numero }} — {{ $tokens['contrato_nombre'] }}</title>
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
    line-height: 1.55;
    width: 215.9mm;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

.page {
    width: 215.9mm;
    height: 279.4mm;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.page-header-inner {
    flex-shrink: 0; background: var(--hdv-navy); border-bottom: 4px solid var(--hdv-accent);
    padding: 10px 52px; display: flex; align-items: center; justify-content: space-between;
}
.page-header-inner img { height: 18px; max-width: 140px; object-fit: contain; display: block; }
.page-header-inner span.phi-text { font-size: 12px; font-weight: 700; color: #fff; }
.page-header-inner .phi-tag { font-size: 8.5px; letter-spacing: 1px; text-transform: uppercase; color: rgba(199,210,254,.7); }
.page-body  { flex: 1; overflow: hidden; display: flex; flex-direction: column; }
.inner      { flex: 1; padding: 20px 52px 12px; display: flex; flex-direction: column; overflow: hidden; }
.page-foot  {
    flex-shrink: 0; border-top: 1px solid #e2e8f0; padding: 8px 52px;
    display: flex; justify-content: space-between; align-items: center;
    font-size: 8.5px; color: #94a3b8;
}
.page-foot strong { color: var(--hdv-navy); font-weight: 600; }

.doc-title { font-size: 16px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; }
.doc-sub   { font-size: 11px; font-weight: 700; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 12px; }

p { color: #334155; font-size: 10.5px; line-height: 1.55; margin-bottom: 7px; text-align: justify; }
strong { color: #0f172a; }

.sec-title { font-size: 11px; font-weight: 800; color: var(--hdv-navy); text-align: center; letter-spacing: 1px; margin: 10px 0 6px; }

.decl { padding-left: 24px; position: relative; font-size: 10.5px; line-height: 1.55; color: #334155; text-align: justify; margin-bottom: 6px; }
.decl .rn { position: absolute; left: 0; font-weight: 800; color: var(--hdv-navy); }

.clause { font-size: 10.5px; line-height: 1.55; color: #334155; text-align: justify; margin-bottom: 7px; }

.pagos { margin: 2px 0 8px 20px; }
.pago { font-size: 10.5px; color: #334155; text-align: justify; margin-bottom: 4px; padding-left: 16px; position: relative; }
.pago .pl { position: absolute; left: 0; font-weight: 800; color: var(--hdv-navy); }

.firma-fecha { font-size: 10.5px; color: #334155; text-align: justify; margin: 8px 0 4px; }

.sign-row { display: flex; justify-content: center; gap: 40px; margin-top: 6px; }
.sign-col { width: 260px; text-align: center; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 5px; margin-top: 34px; font-size: 9.5px; color: #475569; }
.sign-name { font-size: 10.5px; font-weight: 700; color: #0f172a; }

.privacy-note { font-size: 8px; color: #94a3b8; line-height: 1.4; margin-top: 10px; border-top: 1px solid #f1f5f9; padding-top: 6px; }
</style>
</head>
<body>

<div class="page">
  <div class="page-header-inner">
    @if(!empty($brandLogoSrcLight))<img src="{{ $brandLogoSrcLight }}" alt="Home del Valle">
    @elseif(!empty($brandLogoSrc))<img src="{{ $brandLogoSrc }}" alt="Home del Valle">
    @else<span class="phi-text">Home del Valle</span>@endif
    <span class="phi-tag">Documento Legal · Confidencial</span>
  </div>
  <div class="page-body"><div class="inner">

    <div class="doc-title">Adéndum No. {{ $addendum->numero }}</div>
    <div class="doc-sub">al {{ $tokens['contrato_nombre'] }}</div>

    <p>Adéndum No. {{ $addendum->numero }} al {{ $tokens['contrato_nombre'] }} que celebran, por una parte, <strong>{{ $propietario }}</strong>, en su carácter de propietario del inmueble objeto del contrato (en lo sucesivo <strong>"EL PROPIETARIO"</strong>), y por la otra <strong>HOME DEL VALLE BIENES RAÍCES</strong>, representada por <strong>{{ mb_strtoupper($representante, 'UTF-8') }}</strong> (en lo sucesivo <strong>"LA COMISIONISTA"</strong>), al tenor de las siguientes:</p>

    <div class="sec-title">DECLARACIONES</div>

    <div class="decl"><span class="rn">I.</span> {!! \App\Services\AdendumComisionGeneratorService::clause('declaracion_1', $tokens) !!}</div>
    <div class="decl"><span class="rn">II.</span> {!! \App\Services\AdendumComisionGeneratorService::clause('declaracion_2', $tokens) !!}</div>
    <div class="decl"><span class="rn">III.</span> {!! \App\Services\AdendumComisionGeneratorService::clause('declaracion_3', $tokens) !!}</div>

    <div class="sec-title">CLÁUSULAS</div>

    <div class="clause">{!! \App\Services\AdendumComisionGeneratorService::clause('registro_comprador', $tokens) !!}</div>

    <div class="clause">{!! \App\Services\AdendumComisionGeneratorService::clause('oferta_economica', $tokens) !!}</div>
    @if($pagos->isNotEmpty())
    <div class="pagos">
        @foreach($pagos as $i => $pago)
        <div class="pago"><span class="pl">{{ chr(97 + $i) }})</span> <strong>{{ $pago['monto'] }}</strong> ({{ $pago['letras'] }}) {{ $pago['texto'] }}</div>
        @endforeach
    </div>
    @endif

    <div class="clause">{!! \App\Services\AdendumComisionGeneratorService::clause($terceraKey, $tokens) !!}</div>

    <div class="clause">{!! \App\Services\AdendumComisionGeneratorService::clause('ratificacion', $tokens) !!}</div>

    <p class="firma-fecha">Leído que fue el presente instrumento y enteradas las partes de su contenido y alcance legal, lo firman por duplicado en la Ciudad de México, a los {{ $fechaFirma }}.</p>

    <div class="sign-row">
      <div class="sign-col">
        <div class="sign-line">
          <div class="sign-name">{{ $propietario }}</div>
          EL PROPIETARIO
        </div>
      </div>
      <div class="sign-col">
        <div class="sign-line">
          {{-- Mismo tratamiento que la firma del propietario (MAYÚSCULAS) —
               antes una firma salía en altas y la otra en mixtas. --}}
          <div class="sign-name">{{ mb_strtoupper($representante, 'UTF-8') }}</div>
          HOME DEL VALLE BIENES RAÍCES — Representante
        </div>
      </div>
    </div>

    <div class="privacy-note">Documento confidencial generado por el sistema de Home del Valle. Este formato es una base operativa y no sustituye la revisión por un asesor legal.</div>

  </div></div>
  <div class="page-foot">
    <strong>Home del Valle</strong>
    <span>Comitente: {{ \Illuminate\Support\Str::title(mb_strtolower($propietario, 'UTF-8')) }} · Inmueble: {{ $propertyFull ?? '—' }}</span>
    <span>Adéndum No. {{ $addendum->numero }}</span>
  </div>
</div>

</body>
</html>
