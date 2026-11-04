<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\ContractVersion;
use App\Models\RentalProcess;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class ContractService
{
    /**
     * Generate HTML contract from template + rental data.
     */
    public function generateFromTemplate(ContractTemplate $template, RentalProcess $rental): string
    {
        $html = $template->body;

        $variables = $this->buildVariables($rental);

        foreach ($variables as $placeholder => $value) {
            $html = str_replace($placeholder, e($value), $html);
        }

        return $html;
    }

    /**
     * Build replacement variables from a rental process.
     */
    public function buildVariables(RentalProcess $rental): array
    {
        $rental->loadMissing(['property', 'ownerClient', 'tenantClient', 'coTenant', 'obligado', 'broker']);
        $settings = SiteSetting::first();

        // Co-arrendatario (2026-11-02, caso Renta #11 — Alejandro Villalobos + Fernanda
        // Ponce de León): el contrato queda a nombre de AMBOS por igual, no solo del
        // titular. {{inquilino_nombre}} combina los dos nombres cuando hay co-arrendatario,
        // así cualquier plantilla que ya use esa variable sale correcta sin tocarla.
        // email/teléfono quedan solo del titular (el co-arrendatario no siempre tiene
        // correo propio — ver CoTenantService).
        $inquilinoNombre = $rental->tenantClient->name ?? '';
        if ($rental->coTenant?->name) {
            $inquilinoNombre = trim($inquilinoNombre . ' y ' . $rental->coTenant->name);
        }

        return [
            '{{propietario_nombre}}' => $rental->ownerClient->name ?? '',
            '{{propietario_email}}' => $rental->ownerClient->email ?? '',
            '{{propietario_telefono}}' => $rental->ownerClient->phone ?? '',
            '{{propietario_domicilio}}' => $rental->ownerClient?->legal_address ?? '',
            '{{inquilino_nombre}}' => $inquilinoNombre,
            '{{inquilino_email}}' => $rental->tenantClient->email ?? '',
            '{{inquilino_telefono}}' => $rental->tenantClient->phone ?? '',
            '{{inquilino_domicilio}}' => $rental->tenantClient?->legal_address ?? '',
            '{{coarrendatario_nombre}}' => $rental->coTenant->name ?? '',
            '{{coarrendatario_telefono}}' => $rental->coTenant->phone ?? '',
            // Obligado solidario (fiador) — opcional, distinto del co-arrendatario (ver
            // docs/funcionalidades/obligado-solidario.md). Vacío si el trato no tiene uno.
            '{{obligado_nombre}}' => $rental->obligado->name ?? '',
            '{{obligado_telefono}}' => $rental->obligado->phone ?? '',
            '{{obligado_domicilio}}' => $rental->obligado?->legal_address ?? '',
            // Aval — texto libre, distinto del obligado solidario (ver docs/funcionalidades/pagares.md).
            '{{aval_nombre}}' => $rental->aval_nombre ?: '',
            '{{propiedad_titulo}}' => $rental->property->title ?? '',
            '{{propiedad_direccion}}' => $rental->property->address ?? '',
            '{{propiedad_uso}}' => match ($rental->property->property_type ?? null) {
                'House', 'Apartment' => 'Habitacional',
                'Office' => 'Oficina',
                'Commercial' => 'Comercial',
                'Warehouse' => 'Bodega/Almacén',
                'Building' => 'Mixto',
                default => '',
            },
            '{{renta_mensual}}' => $rental->monthly_rent ? number_format($rental->monthly_rent, 2) : '',
            '{{moneda}}' => $rental->currency ?? 'MXN',
            '{{deposito}}' => $rental->deposit_amount ? number_format($rental->deposit_amount, 2) : '',
            '{{duracion_meses}}' => (string) ($rental->lease_duration_months ?? ''),
            '{{fecha_inicio}}' => $rental->lease_start_date?->format('d/m/Y') ?? '',
            '{{fecha_fin}}' => $rental->lease_end_date?->format('d/m/Y') ?? '',
            '{{comision_monto}}' => $rental->commission_amount ? number_format($rental->commission_amount, 2) : '',
            '{{comision_porcentaje}}' => $rental->commission_percentage ? $rental->commission_percentage . '%' : '',
            '{{garantia_tipo}}' => $rental->guarantee_type_label ?? '',
            '{{broker_nombre}}' => $rental->broker->name ?? '',
            '{{fecha_actual}}' => now()->format('d/m/Y'),
            '{{empresa_nombre}}' => $settings->site_name ?? 'Homedelvalle',
        ];
    }

    /**
     * Genera el PDF del contrato "camino legacy" (plantilla de texto libre, sin cláusulas
     * estructuradas — generateFromTemplate()/str_replace, no generateVersion()).
     *
     * Hallazgo 2026-11-02: hasta aquí este método usaba Dompdf con un wrapper genérico sin
     * marca (fuente sans-serif cualquiera, sin header/footer, sin navy/acento) — el único
     * documento del sistema sin la identidad de Home del Valle que sí llevan Acuerdo de
     * Representación, Acta de Entrega, Recibo de Comisión, etc. Se unifica usando el mismo
     * mecanismo que ya existía en generateVersion() (Browsershot + headerHtml()/footerHtml()
     * de esta misma clase, navy #1e1b4b / acento #2563eb — ver _brand_data.php).
     */
    public function generatePdf(Contract $contract): string
    {
        $isSale = $contract->type === 'sale';
        $folio = $contract->folio ?? ('CT-' . str_pad((string) $contract->id, 5, '0', STR_PAD_LEFT));

        $html = $this->wrapHtmlForPdf($contract->generated_html, $contract->title);

        $tmpPath = storage_path('app/tmp-' . uniqid('contract_') . '.pdf');

        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->showBackground()
            ->emulateMedia('screen')
            ->paperSize(215.9, 279.4)
            ->margins(18, 11, 16, 11)
            ->showBrowserHeaderAndFooter()
            ->headerHtml($this->headerHtml($folio))
            ->footerHtml($this->footerHtml($folio, $isSale))
            ->timeout(90)
            ->savePdf($tmpPath);

        $filename = 'contracts/contract-' . $contract->id . '-' . time() . '.pdf';
        \App\Support\SecureFiles::put($filename, file_get_contents($tmpPath));
        @unlink($tmpPath);

        $contract->update(['pdf_path' => $filename, 'folio' => $folio]);

        return $filename;
    }

    /**
     * Crea la cláusula de carátula (tabla-resumen + párrafo introductorio)
     * como contenido inicial editable, si el contrato todavía no tiene una.
     * Idempotente — seguro de llamar aunque ya exista.
     */
    public function ensureCaratulaClause(Contract $contract): void
    {
        if ($contract->clauses()->where('section', 'caratula')->exists()) {
            return;
        }

        $isSale = $contract->type === 'sale';
        $tokens = $contract->operation_id
            ? app(ContractClauseVariableResolver::class)->resolveForOperation($contract->operation()->with(['property', 'client', 'secondaryClient'])->first())
            : app(ContractClauseVariableResolver::class)->resolveForRental($contract->rentalProcess()->first());

        $vendedorLabel = $isSale ? 'Promitente Vendedor' : 'Arrendador';
        $compradorLabel = $isSale ? 'Promitente Compradora' : 'Arrendataria';
        $vendedorNombre = $tokens['{{vendedor_nombre}}'] ?? '';
        $compradorNombre = $tokens['{{comprador_nombre}}'] ?? '';
        $propiedadDireccion = $tokens['{{propiedad_direccion}}'] ?? '';
        $precioTexto = $tokens['{{precio_texto}}'] ?? '';
        $plazoEscrituracion = $tokens['{{fecha_limite_escrituracion}}'] ?? '';

        // Tercera parte (solo renta): obligado solidario o aval, lo que haya — mismo criterio
        // que generateVersion() (2026-11-03, ver docs/funcionalidades/pagares.md).
        $terceroLabel = null;
        $terceroNombre = null;
        if (!$isSale) {
            if (!empty($tokens['{{obligado_nombre}}'])) {
                $terceroLabel = 'Obligado Solidario';
                $terceroNombre = $tokens['{{obligado_nombre}}'];
            } elseif (!empty($tokens['{{aval_nombre}}'])) {
                $terceroLabel = 'Aval';
                $terceroNombre = $tokens['{{aval_nombre}}'];
            }
        }

        $partyLabel1 = $isSale ? 'EL PROMITENTE VENDEDOR' : 'ARRENDADOR';
        $partyLabel2 = $isSale ? 'LA/EL PROMITENTE COMPRADOR(A)' : 'ARRENDATARIA/O';

        // Cajas de partes (2026-11-03: mismo "look" que Contrato de Compraventa —
        // ContratoCompraventaGeneratorService/pdf/contrato-compraventa.blade.php — en vez de la
        // tabla vertical genérica que traía antes. Hasta 3 cajas si hay tercera parte.
        $partyBoxes = "<div class=\"party-box\"><div class=\"role\">{$vendedorLabel}</div><div class=\"row\"><span class=\"val\">{$vendedorNombre}</span></div></div>"
            . "<div class=\"party-box\"><div class=\"role\">{$compradorLabel}</div><div class=\"row\"><span class=\"val\">{$compradorNombre}</span></div></div>";
        if ($terceroNombre) {
            $partyBoxes .= "<div class=\"party-box\"><div class=\"role\">{$terceroLabel}</div><div class=\"row\"><span class=\"val\">{$terceroNombre}</span></div></div>";
        }

        $termRows = '';
        if ($propiedadDireccion !== '') {
            $termRows .= "<tr><td>Inmueble</td><td>{$propiedadDireccion}</td></tr>";
        }
        if ($precioTexto !== '') {
            $precioLabel = $isSale ? 'Precio' : 'Renta mensual';
            $termRows .= "<tr><td>{$precioLabel}</td><td>{$precioTexto}</td></tr>";
        }
        if ($isSale && $plazoEscrituracion !== '') {
            $termRows .= "<tr><td>Plazo para escriturar</td><td>{$plazoEscrituracion}</td></tr>";
        }

        $body = "<div class=\"parties-row\">{$partyBoxes}</div>"
            . ($termRows !== '' ? "<table class=\"terms-table\">{$termRows}</table>" : '')
            . "<p>{$contract->title} que celebran, por una parte, {$vendedorNombre}, por su propio derecho, a quien en lo sucesivo se le denominará &ldquo;{$partyLabel1}&rdquo;; y por la otra parte, {$compradorNombre}, por su propio derecho, a quien en lo sucesivo se le denominará &ldquo;{$partyLabel2}&rdquo;; y a ambos conjuntamente como &ldquo;LAS PARTES&rdquo;, quienes manifiestan su voluntad de obligarse y sujetan el presente contrato al tenor de las declaraciones y cláusulas contenidas en las páginas siguientes.</p>";

        $contract->clauses()->create([
            'title' => 'Carátula',
            'body' => $body,
            'section' => 'caratula',
            'sort_order' => 0,
            'is_locked' => false,
        ]);
    }

    /**
     * Genera una nueva versión inmutable del contrato (venta/renta con
     * cláusulas estructuradas) usando Browsershot, con header/footer de
     * marca repetido en cada página vía el mecanismo nativo de Chrome.
     */
    public function generateVersion(Contract $contract, ?string $note = null): ContractVersion
    {
        set_time_limit(120);

        $contract->loadMissing(['clauses', 'operation.property', 'operation.client', 'operation.secondaryClient', 'rentalProcess']);

        $tokens = $contract->operation_id
            ? app(ContractClauseVariableResolver::class)->resolveForOperation($contract->operation)
            : app(ContractClauseVariableResolver::class)->resolveForRental($contract->rentalProcess);

        $clauses = $contract->clauses->map(function ($clause) use ($tokens) {
            return [
                'section' => $clause->section,
                'title' => $clause->title,
                'body' => str_replace(array_keys($tokens), array_values($tokens), $clause->body),
            ];
        })->values();

        $folio = $contract->folio ?? ($tokens['{{folio}}'] ?? ('CT-' . $contract->id));
        $isSale = $contract->type === 'sale';

        // Tercer firmante (2026-11-03) — solo aplica a renta: obligado solidario o aval, lo que
        // haya. Son roles distintos (ver docs/funcionalidades/pagares.md) pero un contrato no
        // tiene ambos a la vez en la práctica, así que una sola columna extra alcanza. En venta
        // no hay tercer firmante — $terceroNombre queda null.
        $terceroLabel = null;
        $terceroNombre = null;
        if (!$isSale) {
            if (!empty($tokens['{{obligado_nombre}}'])) {
                $terceroLabel = 'OBLIGADO SOLIDARIO';
                $terceroNombre = $tokens['{{obligado_nombre}}'];
            } elseif (!empty($tokens['{{aval_nombre}}'])) {
                $terceroLabel = 'AVAL';
                $terceroNombre = $tokens['{{aval_nombre}}'];
            }
        }

        $html = view('pdf.contract', [
            'contract' => $contract,
            'clauses' => $clauses,
            'folio' => $folio,
            'isSale' => $isSale,
            'vendedorLabel' => $isSale ? 'EL PROMITENTE VENDEDOR' : 'ARRENDADOR',
            'compradorLabel' => $isSale ? 'LA/EL PROMITENTE COMPRADOR(A)' : 'ARRENDATARIA/O',
            'vendedorNombre' => $tokens['{{vendedor_nombre}}'] ?? ($contract->operation->client->name ?? ''),
            'compradorNombre' => $tokens['{{comprador_nombre}}'] ?? ($contract->operation->secondaryClient->name ?? ''),
            'propiedadDireccion' => $tokens['{{propiedad_direccion}}'] ?? '',
            'precioTexto' => $tokens['{{precio_texto}}'] ?? '',
            'plazoEscrituracion' => $tokens['{{fecha_limite_escrituracion}}'] ?? '',
            'terceroLabel' => $terceroLabel,
            'terceroNombre' => $terceroNombre,
        ])->render();

        $versionNumber = $contract->nextVersionNumber();
        $parentDir = $contract->operation_id
            ? 'contracts/operation-' . $contract->operation_id . '-' . $contract->id
            : 'contracts/rental-' . $contract->rental_process_id . '-' . $contract->id;
        $filename = $parentDir . '/v' . $versionNumber . '-' . time() . '.pdf';

        $tmpPath = storage_path('app/tmp-' . uniqid('contract_') . '.pdf');

        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->showBackground()
            ->emulateMedia('screen')
            ->paperSize(215.9, 279.4)
            ->margins(18, 11, 16, 11)
            ->showBrowserHeaderAndFooter()
            ->headerHtml($this->headerHtml($folio))
            ->footerHtml($this->footerHtml($folio, $isSale))
            ->timeout(90)
            ->savePdf($tmpPath);

        \App\Support\SecureFiles::put($filename, file_get_contents($tmpPath));
        @unlink($tmpPath);

        $version = ContractVersion::create([
            'contract_id' => $contract->id,
            'version_number' => $versionNumber,
            'generated_html' => $html,
            'pdf_path' => $filename,
            'clauses_snapshot' => $clauses->toArray(),
            'generated_by' => Auth::id(),
            'generation_note' => $note,
        ]);

        $contract->update(['current_version_id' => $version->id, 'folio' => $folio]);

        return $version;
    }

    public function headerHtml(string $folio): string
    {
        return '<div style="width:100%; box-sizing:border-box; background:#1e1b4b; border-bottom:4px solid #2563eb; padding:8px 11mm; display:flex; align-items:center; justify-content:space-between; -webkit-print-color-adjust:exact;">
            <span style="font-size:11px; font-weight:800; color:#fff; font-family:Arial,sans-serif;">Home del Valle</span>
            <span style="font-size:6.5px; letter-spacing:1px; text-transform:uppercase; color:rgba(199,210,254,.7); font-family:Arial,sans-serif;">Documento Legal &middot; Confidencial</span>
        </div>';
    }

    public function footerHtml(string $folio, bool $isSale = true): string
    {
        $label1 = $isSale ? 'Vendedor' : 'Arrendador';
        $label2 = $isSale ? 'Comprador(a)' : 'Arrendatario(a)';

        return '<div style="width:100%; box-sizing:border-box; font-family:Arial,sans-serif; color:#94a3b8;">
            <div style="display:flex; justify-content:flex-end; gap:20px; padding:0 11mm 3px;">
                <span style="font-size:6.3px; display:flex; align-items:center; gap:4px;">R&uacute;brica ' . e($label1) . ' <i style="display:inline-block;width:46px;border-bottom:1px solid #cbd5e1;font-style:normal;">&nbsp;</i></span>
                <span style="font-size:6.3px; display:flex; align-items:center; gap:4px;">R&uacute;brica ' . e($label2) . ' <i style="display:inline-block;width:46px;border-bottom:1px solid #cbd5e1;font-style:normal;">&nbsp;</i></span>
            </div>
            <div style="border-top:1px solid #e2e8f0; padding:4px 11mm 6px; display:flex; justify-content:space-between; align-items:center; font-size:7.5px;">
                <strong style="color:#1e1b4b; font-weight:600;">Home del Valle</strong>
                <span>Contrato &middot; ' . e($folio) . '</span>
                <span>P&aacute;gina <span class="pageNumber"></span> de <span class="totalPages"></span></span>
            </div>
        </div>';
    }

    /**
     * Record a digital confirmation signature on a specific contract version.
     */
    public function recordVersionSignature(ContractVersion $version, int $userId, string $signerName, string $signerEmail, string $ip, string $userAgent): void
    {
        $version->update([
            'signature_status' => 'signed',
            'signed_at' => now(),
            'signed_by' => $userId,
            'signature_data' => [
                'signer_name' => $signerName,
                'signer_email' => $signerEmail,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'timestamp' => now()->toIso8601String(),
                'method' => 'digital_confirmation',
            ],
        ]);

        $version->contract()->update(['final_version_id' => $version->id]);
    }

    /**
     * Wrap contract HTML in a full document for PDF rendering — tipografía e identidad de
     * marca (navy #1e1b4b / acento #2563eb, misma paleta que _brand_data.php), consistente
     * con el resto de documentos legales del sistema. El header/footer de marca los pone
     * Browsershot por fuera (ver headerHtml()/footerHtml()); esto solo viste el cuerpo.
     */
    protected function wrapHtmlForPdf(string $body, string $title): string
    {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8">
            <title>' . e($title) . '</title>
            <style>
                * { box-sizing: border-box; }
                body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.6; color: #1e293b; margin: 0; padding: 6px 11mm 0; }
                h1 { font-size: 15px; font-weight: 800; text-align: center; color: #1e1b4b; text-transform: uppercase; letter-spacing: .5px; margin: 10px 0 18px; }
                h2 { font-size: 12px; font-weight: 800; text-align: center; color: #1e1b4b; margin: 18px 0 10px; }
                p { margin: 0 0 9px; text-align: justify; }
                strong { color: #0f172a; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                td, th { padding: 6px 8px; border: 1px solid #e2e8f0; font-size: 10.5px; }
                .signature-block { margin-top: 60px; display: flex; }
                .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 40px; text-align: center; font-size: 10px; padding-top: 4px; }
            </style>
        </head><body>' . $body . '</body></html>';
    }

    /**
     * Record a digital confirmation signature (IP + timestamp + user agent).
     */
    public function recordDigitalSignature(Contract $contract, int $userId, string $signerName, string $signerEmail, string $ip, string $userAgent): void
    {
        $contract->update([
            'signature_status' => 'signed',
            'signed_at' => now(),
            'signed_by' => $userId,
            'signature_data' => [
                'signer_name' => $signerName,
                'signer_email' => $signerEmail,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'timestamp' => now()->toIso8601String(),
                'method' => 'digital_confirmation',
            ],
        ]);
    }
}
