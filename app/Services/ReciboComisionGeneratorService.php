<?php

namespace App\Services;

use App\Models\DocumentClause;
use App\Models\RentalProcess;
use App\Support\NumeroALetras;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Recibo de Comisión (renta) — se genera cuando el propietario paga a Home del Valle la comisión
 * por haber colocado al inquilino. A diferencia del Recibo de Pago Parcial (venta), aquí quien
 * RECIBE el dinero es Home del Valle, no el cliente — por eso firma Ana Laura Monsivais Flores
 * (misma representante legal que firma Acuerdos de Representación) reconociendo el recibo, en vez
 * del propietario. Caso real que lo originó: comisión de la Renta #6 (Carlos Sánchez Fernández,
 * $35,000, pagada por el propietario Roberto Vargas Arreola el día de la entrega, 2026-10-09).
 *
 * Igual que Recibo de Pago Parcial: una renta puede tener VARIOS recibos de comisión si el pago se
 * hace en partes (ej. mitad al firmar, mitad al entregar) — cada uno con su propio folio.
 */
class ReciboComisionGeneratorService
{
    const DEFAULT_CLAUSES = [
        'recepcion' => 'Por medio del presente, HOME DEL VALLE BIENES RAÍCES, representada en este acto por {{representante_nombre}}, hace constar que recibió a su entera satisfacción, {{metodo_pago}}, por parte de {{arrendador}}, en su carácter de propietario del inmueble ubicado en {{inmueble}}, la cantidad de:<br><br><strong class="monto-letras">{{monto_numero}} M.N. ({{monto_letras}}),</strong><br><br>cantidad que se recibe y reconoce como pago de la comisión por los servicios de arrendamiento prestados por Home del Valle Bienes Raíces, respecto del contrato de arrendamiento celebrado entre {{arrendador}} y {{arrendatario}} como arrendatario(a) del inmueble antes referido.',
        'otorgamiento' => 'En virtud de lo anterior, mediante la suscripción del presente documento Home del Valle Bienes Raíces otorga el recibo más amplio que en derecho proceda exclusivamente respecto de la cantidad aquí consignada, dejando constancia de su recepción a su entera satisfacción y de su aplicación como pago de la comisión por los servicios de intermediación inmobiliaria prestados. El presente recibo se suscribe para todos los efectos legales a que haya lugar.',
    ];

    const CLAUSE_LABELS = [
        'recepcion' => 'Recepción de la comisión',
        'otorgamiento' => 'Otorgamiento del recibo',
    ];

    /** Mismo representante legal que firma Acuerdos de Representación — quien recibe la comisión a nombre de Home del Valle. */
    const REPRESENTANTE_NOMBRE = 'Ana Laura Monsivais Flores';
    const REPRESENTANTE_CARGO = 'Directora General';

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('recibo_comision', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    /**
     * @param  float  $monto  cantidad recibida en este pago (no necesariamente la comisión total, si se paga en partes).
     * @param  string  $metodoPago  cómo/por dónde llegó el dinero — texto libre, ej. "mediante
     *         transferencia interbancaria" o "en efectivo". Se captura al generar porque cambia cada vez.
     * @param  \Illuminate\Support\Carbon|string|null  $fechaRecibo  fecha real en que se recibió el pago. Default: hoy.
     */
    public function renderHtml(RentalProcess $rental, float $monto, string $metodoPago, $fechaRecibo = null): string
    {
        $rental->loadMissing('ownerClient', 'tenantClient', 'property');

        $folio = 'COM-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT) . '-' . str_pad((string) ($rental->documents()->where('category', 'recibo_comision')->count() + 1), 2, '0', STR_PAD_LEFT);
        $fecha = ($fechaRecibo ? \Illuminate\Support\Carbon::parse($fechaRecibo) : now())->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        ['buyerName' => $arrendador] = PurchaseOfferGeneratorService::buyerInfo($rental->ownerClient);
        ['buyerName' => $arrendatario] = PurchaseOfferGeneratorService::buyerInfo($rental->tenantClient);
        ['propertyFull' => $inmueble] = PurchaseOfferGeneratorService::propertyInfo($rental->property);

        $arrendador = $arrendador ?: '—';
        $arrendatario = $arrendatario ?: '—';
        $inmueble = $inmueble ?: ($rental->property?->title ?? '—');

        $montoNumero = '$' . number_format($monto, 2);
        $montoLetras = NumeroALetras::pesosMonedaNacional($monto);

        $tokens = [
            'arrendador' => $arrendador,
            'arrendatario' => $arrendatario,
            'inmueble' => $inmueble,
            'metodo_pago' => $metodoPago,
            'monto_numero' => $montoNumero,
            'monto_letras' => $montoLetras,
            'representante_nombre' => self::REPRESENTANTE_NOMBRE,
        ];

        $clauses = collect(['recepcion', 'otorgamiento'])->map(fn ($key) => [
            'key' => $key,
            'body' => self::clause($key, $tokens),
        ])->values();

        return view('pdf.recibo-comision', compact(
            'rental', 'folio', 'fecha', 'arrendador', 'montoNumero', 'clauses'
        ) + [
            'representanteNombre' => self::REPRESENTANTE_NOMBRE,
            'representanteCargo' => self::REPRESENTANTE_CARGO,
        ])->render();
    }

    public function generatePdf(RentalProcess $rental, float $monto, string $metodoPago, $fechaRecibo = null): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($rental, $monto, $metodoPago, $fechaRecibo);

        $dir  = storage_path('app/recibos-comision/' . $rental->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/recibo-comision-' . time() . '.pdf';

        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->windowSize(816, 1056)
            ->paperSize(215.9, 279.4)
            ->landscape(false)
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->timeout(90)
            ->savePdf($path);

        return $path;
    }
}
