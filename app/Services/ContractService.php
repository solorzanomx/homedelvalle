<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\RentalProcess;
use App\Models\SiteSetting;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

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
    protected function buildVariables(RentalProcess $rental): array
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
