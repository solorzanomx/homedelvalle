{{--
    CSS compartido del "look" del documento de contrato — un solo lugar de
    verdad usado tanto por el PDF (pdf/contract.blade.php, vía Browsershot)
    como por la vista de edición en pantalla (contracts/edit.blade.php).
    Las reglas page-break-* son inofensivas fuera de impresión, así que se
    reutiliza tal cual sin variantes.
    Espera $brandCssVars/$brandFontB64 ya definidas en el scope del llamador
    (via @php include(resource_path('views/pdf/_brand_data.php')) @endphp).
--}}
{!! $brandCssVars ?? '' !!}
@if($brandFontB64 ?? null)
@font-face {
    font-family: 'Inter';
    font-style: normal;
    font-weight: 100 900;
    font-display: swap;
    src: url('data:font/woff2;base64,{{ $brandFontB64 }}') format('woff2');
}
@endif
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Inter', Arial, sans-serif;
    background: #fff;
    color: #1e293b;
    font-size: 11.3px;
    line-height: 1.5;
}

p { color: #334155; font-size: 10.8px; line-height: 1.55; margin-bottom: 7px; text-align: justify; }
strong { color: #0f172a; }

/* ---- Carátula ---- */
{{-- 2026-11-03: mismo "look" que Contrato de Compraventa (ContratoCompraventaGeneratorService/
     pdf/contrato-compraventa.blade.php) — cajas de partes en vez de la tabla vertical genérica
     que traía antes. Ver ContractService::ensureCaratulaClause(). --}}
.caratula-block { page-break-after: always; padding: 6px 4px 2px; display:flex; flex-direction:column; justify-content: space-between; min-height: 235mm; }
.doc-title { font-size: 19px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
.doc-folio { font-size: 10px; color: #94a3b8; text-align: center; margin-bottom: 26px; letter-spacing: .5px; }

.parties-row { display: flex; gap: 12px; margin: 8px 0 14px; }
.party-box { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; }
.party-box .role { font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--hdv-navy); margin-bottom: 5px; }
.party-box .row { display: flex; gap: 6px; font-size: 11px; }
.party-box .val { color: #0f172a; font-weight: 700; }

.terms-table { width: 100%; border-collapse: collapse; margin: 4px 0 18px; font-size: 11px; }
.terms-table td { padding: 7px 12px; border-bottom: 1px solid #f1f5f9; }
.terms-table td:first-child { color: #64748b; width: 42%; }
.terms-table td:last-child { color: var(--hdv-navy); font-weight: 700; text-align: right; }
.terms-table tr:last-child td { border-bottom: none; }

.closing-note { font-size: 9.5px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px; line-height: 1.6; }
.closing-note strong { color: #64748b; }

/* ---- Cuerpo continuo ---- */
.section-title { font-size: 13px; font-weight: 800; color: var(--hdv-navy); text-align: center; text-transform: uppercase; margin: 16px 0 9px; letter-spacing: .5px; page-break-after: avoid; }
.section-title.first { margin-top: 2px; }

{{-- Las cláusulas de renta ya traen su numeral en el título (ej. "SEGUNDA.- RENTA") — a
     diferencia de Compraventa, que numera por CSS counter(), aquí NO se duplica el número; se
     destaca el título con una franja de acento a la izquierda, al estilo de los demás documentos
     de marca (Acuerdo de Representación, etc.). --}}
.clause-block { margin: 0 0 11px; padding: 2px 0 10px 12px; border-left: 2.5px solid #e2e8f0; page-break-inside: avoid; text-align: justify; }
.clause-block .ctitle { font-weight: 800; color: var(--hdv-navy); text-transform: uppercase; display:block; margin-bottom: 4px; font-size: 10.5px; letter-spacing: .2px; }

.lettered-list { counter-reset: litem; margin: 5px 0 9px; }
.litem { counter-increment: litem; display: flex; align-items: baseline; gap: 4px; padding: 5px 0; font-size: 10.8px; line-height: 1.55; color: #334155; page-break-inside: avoid; }
.litem::before { content: counter(litem, upper-alpha) "."; flex: 0 0 15px; color: var(--hdv-navy); font-weight: 800; font-size: 10.8px; }
.litem .ltext { flex: 1 1 auto; text-align: justify; }
.litem .ltext p { margin-bottom: 6px; }
.litem .ltext p:last-child { margin-bottom: 0; }

.notif-table { width: 100%; border-collapse: collapse; margin: 7px 0; font-size: 10.5px; border: 1px solid #e2e8f0; border-radius: 6px; overflow:hidden; page-break-inside: avoid; }
.notif-table td { padding: 8px 13px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.notif-table tr:last-child td { border-bottom: none; }
.notif-table td:first-child { color: #64748b; font-weight: 700; width: 32%; background:#f8fafc; }
.notif-table td:last-child { color: #0f172a; }

.firma-block { page-break-inside: avoid; }
.sign-row { display: flex; justify-content: center; gap: 60px; margin-top: 60px; }
.sign-col { width: 230px; }
.sign-line { border-top: 1px solid #0f172a; padding-top: 6px; margin-top: 36px; font-size: 9.3px; color: #475569; text-align:center; }
.sign-name { font-size: 11px; font-weight: 700; color: #0f172a; }
.sign-heading { font-size: 11.5px; font-weight: 800; color: var(--hdv-navy); text-transform: uppercase; margin-bottom: 4px; text-align:center; }
