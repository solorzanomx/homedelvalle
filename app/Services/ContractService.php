<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\ContractVersion;
use App\Models\RentalProcess;
use App\Models\SiteSetting;
use Dompdf\Dompdf;
use Dompdf\Options;
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
        $rental->loadMissing(['property', 'ownerClient', 'tenantClient', 'broker']);
        $settings = SiteSetting::first();

        return [
            '{{propietario_nombre}}' => $rental->ownerClient->name ?? '',
            '{{propietario_email}}' => $rental->ownerClient->email ?? '',
            '{{propietario_telefono}}' => $rental->ownerClient->phone ?? '',
            '{{inquilino_nombre}}' => $rental->tenantClient->name ?? '',
            '{{inquilino_email}}' => $rental->tenantClient->email ?? '',
            '{{inquilino_telefono}}' => $rental->tenantClient->phone ?? '',
            '{{propiedad_titulo}}' => $rental->property->title ?? '',
            '{{propiedad_direccion}}' => $rental->property->address ?? '',
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
     * Generate PDF from HTML content, store it, return path.
     */
    public function generatePdf(Contract $contract): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'sans-serif');

        $dompdf = new Dompdf($options);

        $html = $this->wrapHtmlForPdf($contract->generated_html, $contract->title);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        $filename = 'contracts/contract-' . $contract->id . '-' . time() . '.pdf';
        Storage::disk('public')->put($filename, $dompdf->output());

        $contract->update(['pdf_path' => $filename]);

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

        $rows = "<tr><td>{$vendedorLabel}</td><td>{$vendedorNombre}</td></tr>"
            . "<tr><td>{$compradorLabel}</td><td>{$compradorNombre}</td></tr>";
        if ($propiedadDireccion !== '') {
            $rows .= "<tr><td>Inmueble</td><td>{$propiedadDireccion}</td></tr>";
        }
        if ($precioTexto !== '') {
            $precioLabel = $isSale ? 'Precio' : 'Renta mensual';
            $rows .= "<tr><td>{$precioLabel}</td><td>{$precioTexto}</td></tr>";
        }
        if ($isSale && $plazoEscrituracion !== '') {
            $rows .= "<tr><td>Plazo para escriturar</td><td>{$plazoEscrituracion}</td></tr>";
        }

        $partyLabel1 = $isSale ? 'EL PROMITENTE VENDEDOR' : 'ARRENDADOR';
        $partyLabel2 = $isSale ? 'LA/EL PROMITENTE COMPRADOR(A)' : 'ARRENDATARIA/O';

        $body = "<table class=\"caratula-table\">{$rows}</table>"
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
            ->footerHtml($this->footerHtml($folio))
            ->timeout(90)
            ->savePdf($tmpPath);

        Storage::disk('public')->put($filename, file_get_contents($tmpPath));
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

    protected function headerHtml(string $folio): string
    {
        return '<div style="width:100%; box-sizing:border-box; background:#1e1b4b; border-bottom:4px solid #2563eb; padding:8px 11mm; display:flex; align-items:center; justify-content:space-between; -webkit-print-color-adjust:exact;">
            <span style="font-size:11px; font-weight:800; color:#fff; font-family:Arial,sans-serif;">Home del Valle</span>
            <span style="font-size:6.5px; letter-spacing:1px; text-transform:uppercase; color:rgba(199,210,254,.7); font-family:Arial,sans-serif;">Documento Legal &middot; Confidencial</span>
        </div>';
    }

    protected function footerHtml(string $folio): string
    {
        return '<div style="width:100%; box-sizing:border-box; font-family:Arial,sans-serif; color:#94a3b8;">
            <div style="display:flex; justify-content:flex-end; gap:20px; padding:0 11mm 3px;">
                <span style="font-size:6.3px; display:flex; align-items:center; gap:4px;">R&uacute;brica Vendedor <i style="display:inline-block;width:46px;border-bottom:1px solid #cbd5e1;font-style:normal;">&nbsp;</i></span>
                <span style="font-size:6.3px; display:flex; align-items:center; gap:4px;">R&uacute;brica Comprador(a) <i style="display:inline-block;width:46px;border-bottom:1px solid #cbd5e1;font-style:normal;">&nbsp;</i></span>
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
     * Wrap contract HTML in a full document for PDF rendering.
     */
    protected function wrapHtmlForPdf(string $body, string $title): string
    {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8">
            <title>' . e($title) . '</title>
            <style>
                body { font-family: sans-serif; font-size: 12px; line-height: 1.6; color: #333; margin: 40px; }
                h1 { font-size: 18px; text-align: center; margin-bottom: 20px; }
                h2 { font-size: 14px; margin-top: 16px; }
                p { margin: 8px 0; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                td, th { padding: 6px 8px; border: 1px solid #ddd; font-size: 11px; }
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
