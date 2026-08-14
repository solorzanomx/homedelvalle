<?php

namespace App\Services;

use App\Models\DocumentClause;
use App\Models\RentalProcess;
use App\Support\NumeroALetras;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class RentalDepositReceiptGeneratorService
{
    /**
     * Texto por defecto de las 8 cláusulas — editable desde
     * /admin/documentos/recibo-apartado/clausulas (App\Models\DocumentClause).
     * Transcrito del recibo real usado por Home del Valle (caso Brenda
     * Valencia, 13/08/2026) — pendiente de revisión por abogado antes de
     * uso definitivo, igual que el resto de documentos de marca con
     * cláusulas legales.
     */
    const DEFAULT_CLAUSES = [
        'objeto' => 'La cantidad recibida tiene por objeto formalizar la intención de {{arrendataria}}, en su carácter de PROMITENTE ARRENDATARIA, de celebrar contrato de arrendamiento respecto del inmueble antes identificado. Como consecuencia de la recepción del presente depósito, el PROMITENTE ARRENDADOR se obliga a reservar temporalmente el inmueble, absteniéndose de ofrecerlo, comprometerlo o celebrar contrato de arrendamiento respecto del mismo con un tercero, hasta la celebración del contrato de arrendamiento, misma que deberá efectuarse A MÁS TARDAR EL DÍA {{fecha_limite}}.',
        'condiciones_economicas' => 'Las partes tienen contemplado celebrar el contrato de arrendamiento estableciendo una renta mensual de {{renta_texto}}, cantidad que incluye la cuota ordinaria de mantenimiento del condominio, bajo los términos y condiciones que se consignarán expresamente en el contrato de arrendamiento definitivo.',
        'fecha_limite' => 'Las partes acuerdan que el contrato definitivo de arrendamiento deberá celebrarse y firmarse A MÁS TARDAR EL DÍA {{fecha_limite}}, pudiendo formalizarse en cualquier fecha anterior de común acuerdo. En dicha fecha límite deberán quedar formalizadas las obligaciones definitivas entre {{arrendador}}, en su carácter de ARRENDADOR, y {{arrendataria}}, en su carácter de ARRENDATARIA.',
        'aplicacion_deposito' => 'En caso de celebrarse el contrato de arrendamiento en los términos convenidos, la cantidad de {{monto_texto}} entregada mediante el presente instrumento no constituirá un pago adicional, sino que será reconocida y aplicada a las cantidades que deba cubrir la arrendataria con motivo de la formalización del arrendamiento, específicamente al depósito en garantía, debiendo quedar dicha aplicación expresamente reconocida en el contrato definitivo.',
        'incumplimiento_arrendataria' => 'Si por causas directamente imputables a la PROMITENTE ARRENDATARIA ésta desistiere de la operación, se negare injustificadamente a celebrar el contrato de arrendamiento o dejare de cumplir con los requisitos y obligaciones previamente convenidos para su formalización, perderá en favor del PROMITENTE ARRENDADOR la cantidad de {{monto_texto}} entregada mediante el presente recibo, misma que se aplicará como pena convencional, sin obligación de devolución.',
        'incumplimiento_arrendador' => 'Si por causas directamente imputables al PROMITENTE ARRENDADOR éste desistiere injustificadamente de la operación, arrendare o comprometiera el inmueble con un tercero durante el periodo de reserva, o se negare a celebrar el contrato de arrendamiento en los términos previamente convenidos, deberá devolver a la PROMITENTE ARRENDATARIA los {{monto_texto}} recibidos y pagar adicionalmente la cantidad de {{monto_texto}} por concepto de pena convencional. En consecuencia, la cantidad total a entregar a la PROMITENTE ARRENDATARIA en dicho supuesto será de {{monto_doble_texto}}.',
        'causas_no_imputables' => 'En caso de que la operación no pudiera formalizarse por una causa jurídica, material o documental no imputable a la PROMITENTE ARRENDATARIA, que impida legítimamente la celebración del contrato de arrendamiento, la cantidad recibida mediante el presente instrumento deberá ser devuelta a ésta, sin aplicación de pena convencional.',
        'alcance' => 'El presente documento acredita la recepción del depósito, la reserva temporal del inmueble y las obligaciones expresamente aquí establecidas. Las condiciones definitivas del arrendamiento, incluyendo derechos, obligaciones, vigencia, garantías, entrega y recepción del inmueble y demás estipulaciones aplicables, quedarán consignadas en el contrato de arrendamiento definitivo que celebren las partes.',
    ];

    const CLAUSE_LABELS = [
        'objeto' => 'Objeto del depósito y reserva',
        'condiciones_economicas' => 'Condiciones económicas',
        'fecha_limite' => 'Fecha límite para la formalización',
        'aplicacion_deposito' => 'Aplicación del depósito',
        'incumplimiento_arrendataria' => 'Incumplimiento imputable a la promitente arrendataria',
        'incumplimiento_arrendador' => 'Incumplimiento imputable al promitente arrendador',
        'causas_no_imputables' => 'Causas no imputables a las partes',
        'alcance' => 'Alcance del presente instrumento',
    ];

    const CLAUSE_TITLES = [
        'objeto' => 'Primera. – Objeto del depósito y reserva',
        'condiciones_economicas' => 'Segunda. – Condiciones económicas',
        'fecha_limite' => 'Tercera. – Fecha límite para la formalización',
        'aplicacion_deposito' => 'Cuarta. – Aplicación del depósito',
        'incumplimiento_arrendataria' => 'Quinta. – Incumplimiento imputable a la promitente arrendataria',
        'incumplimiento_arrendador' => 'Sexta. – Incumplimiento imputable al promitente arrendador',
        'causas_no_imputables' => 'Séptima. – Causas no imputables a las partes',
        'alcance' => 'Octava. – Alcance del presente instrumento',
    ];

    public function renderHtml(RentalProcess $rental): string
    {
        $rental->loadMissing('tenantClient', 'ownerClient', 'property', 'user');

        $folio = 'RA-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT);
        $fecha = ($rental->apartado_paid_at ?? now())->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $fechaLimite = $rental->apartado_deadline?->locale('es')->isoFormat('D [de] MMMM [de] YYYY') ?? '—';

        ['buyerName' => $arrendataria] = PurchaseOfferGeneratorService::buyerInfo($rental->tenantClient);
        $arrendataria = $arrendataria ?: '—';

        ['buyerName' => $arrendador] = PurchaseOfferGeneratorService::buyerInfo($rental->ownerClient);
        $arrendador = $arrendador ?: '—';

        ['propertyFull' => $inmueble] = PurchaseOfferGeneratorService::propertyInfo($rental->property);
        $inmueble = $inmueble ?: ($rental->property?->title ?? '—');

        $monto = (float) $rental->apartado_amount;
        $montoTexto = '$' . number_format($monto, 2) . ' M.N. (' . mb_strtoupper(NumeroALetras::pesos($monto)) . ')';
        $montoDobleTexto = '$' . number_format($monto * 2, 2) . ' M.N. (' . mb_strtoupper(NumeroALetras::pesos($monto * 2)) . ')';

        $renta = (float) $rental->monthly_rent;
        $rentaTexto = '$' . number_format($renta, 2) . ' M.N. (' . mb_strtoupper(NumeroALetras::pesos($renta)) . ')';

        $tokens = [
            'arrendataria' => $arrendataria,
            'arrendador' => $arrendador,
            'inmueble' => $inmueble,
            'fecha_limite' => mb_strtoupper($fechaLimite),
            'monto_texto' => $montoTexto,
            'monto_doble_texto' => $montoDobleTexto,
            'renta_texto' => $rentaTexto,
        ];

        $clauses = collect(self::DEFAULT_CLAUSES)->map(function ($default, $key) use ($tokens) {
            return [
                'title' => self::CLAUSE_TITLES[$key],
                'body' => self::clause($key, $tokens),
            ];
        })->values();

        $recibeName = $rental->user?->full_name ?? $rental->user?->name ?? 'Home del Valle Bienes Raíces';
        $recibeTitle = $rental->user?->title ?? 'Asesor Inmobiliario';
        $recibePhone = $rental->user?->phone ?? '';
        $recibeEmail = $rental->user?->mailSetting?->from_email ?? $rental->user?->email;

        return view('pdf.recibo-apartado', compact(
            'rental', 'folio', 'fecha', 'fechaLimite', 'arrendataria', 'arrendador', 'inmueble',
            'montoTexto', 'rentaTexto', 'clauses', 'recibeName', 'recibeTitle', 'recibePhone', 'recibeEmail'
        ) + ['montoNumero' => number_format($monto, 2)])->render();
    }

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('recibo_apartado', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    public function generatePdf(RentalProcess $rental): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($rental);
        $folio = 'RA-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT);

        $dir  = storage_path('app/rental-receipts/' . $rental->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/apartado-' . $rental->id . '-' . time() . '.pdf';

        // Documento de varias páginas (2+ con las 8 cláusulas) — se usa el
        // flujo continuo con header/footer nativo de Browsershot (repetido
        // automáticamente en cada página), no el patrón de página fija
        // única que usa oferta-compra.blade.php.
        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->showBackground()
            ->emulateMedia('screen')
            ->paperSize(215.9, 279.4)
            ->margins(18, 15, 16, 15)
            ->showBrowserHeaderAndFooter()
            ->headerHtml($this->headerHtml())
            ->footerHtml($this->footerHtml($folio))
            ->timeout(90)
            ->savePdf($path);

        return $path;
    }

    protected function headerHtml(): string
    {
        $logoB64 = null;
        $logoPath = public_path('img/email/logo-blanco.png');
        if (file_exists($logoPath)) {
            $logoB64 = base64_encode(file_get_contents($logoPath));
        }

        $logoTag = $logoB64
            ? '<img src="data:image/png;base64,' . $logoB64 . '" style="height:14px; max-width:120px; object-fit:contain; display:block;">'
            : '<span style="font-size:11px; font-weight:800; color:#fff; font-family:Arial,sans-serif;">Home del Valle</span>';

        return '<div style="width:100%; box-sizing:border-box; background:#1e1b4b; border-bottom:4px solid #2563eb; padding:8px 11mm; display:flex; align-items:center; justify-content:space-between; -webkit-print-color-adjust:exact;">'
            . $logoTag
            . '<span style="font-size:6.5px; letter-spacing:1px; text-transform:uppercase; color:rgba(199,210,254,.7); font-family:Arial,sans-serif;">Documento Legal &middot; Confidencial</span>'
            . '</div>';
    }

    protected function footerHtml(string $folio): string
    {
        return '<div style="width:100%; box-sizing:border-box; font-family:Arial,sans-serif; color:#94a3b8; border-top:1px solid #e2e8f0; padding:4px 11mm 6px; display:flex; justify-content:space-between; align-items:center; font-size:7.5px;">'
            . '<strong style="color:#1e1b4b; font-weight:600;">Home del Valle</strong>'
            . '<span>Recibo de Apartado &middot; ' . e($folio) . '</span>'
            . '<span>P&aacute;gina <span class="pageNumber"></span> de <span class="totalPages"></span></span>'
            . '</div>';
    }
}
