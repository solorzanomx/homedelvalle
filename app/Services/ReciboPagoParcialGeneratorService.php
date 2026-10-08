<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DocumentClause;
use App\Models\Operation;
use App\Models\Property;
use App\Support\NumeroALetras;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Recibo de Pago Parcial (venta) — se genera cada vez que llega un pago parcial del precio de
 * compraventa (anticipo, pago de contado del comprador, dispersión de crédito hipotecario/Infonavit,
 * etc.). Una operación de venta puede tener VARIOS de estos recibos — uno por cada pago recibido,
 * exactamente como el caso real que lo originó (Nogues/Ruiz Ramírez): un pago vía BANORTE y, al día
 * siguiente, el resto vía Infonavit — cada uno con su propio recibo.
 *
 * Basado en el recibo real que Home del Valle ya usa en la práctica (mismo caso, 2026-09-25) — texto
 * casi idéntico, solo generalizado con tokens; agrega el encabezado "BUENO POR: $X M.N." que trae el
 * original. Solo firma el vendedor (es un recibo unilateral de su parte, no un acuerdo bilateral).
 */
class ReciboPagoParcialGeneratorService
{
    const DEFAULT_CLAUSES = [
        'recepcion' => 'Por medio del presente, {{seller_name}}, en mi carácter de propietario del inmueble que se identifica más adelante, hago constar que recibí a mi entera satisfacción, {{metodo_pago}}, por parte de {{buyer_name}} la cantidad de:<br><br><strong class="monto-letras">{{monto_numero}} M.N. ({{monto_letras}}),</strong><br><br>cantidad que se recibe y reconoce como pago parcial del precio de compraventa del inmueble de mi propiedad, ubicado en {{property_full}}.',
        'otorgamiento' => 'En virtud de lo anterior, mediante la suscripción del presente documento otorgo el recibo más amplio que en derecho proceda exclusivamente respecto de la cantidad aquí consignada, dejando constancia de su recepción a mi entera satisfacción y de su aplicación como pago parcial de la operación de compraventa antes referida. El presente recibo se suscribe para todos los efectos legales a que haya lugar.',
        // Variantes para cuando este pago es el ÚLTIMO (finiquito) — misma estructura, solo cambia
        // "pago parcial" por "finiquito de pago". Caso real que las originó: segundo recibo del caso
        // Nogues/Ruiz Ramírez (Infonavit, $1,648,672.82) — Ana Laura pidió explícitamente "finiquito
        // de pago" en vez de "pago parcial" para ese recibo.
        'recepcion_finiquito' => 'Por medio del presente, {{seller_name}}, en mi carácter de propietario del inmueble que se identifica más adelante, hago constar que recibí a mi entera satisfacción, {{metodo_pago}}, por parte de {{buyer_name}} la cantidad de:<br><br><strong class="monto-letras">{{monto_numero}} M.N. ({{monto_letras}}),</strong><br><br>cantidad que se recibe y reconoce como finiquito de pago por la compraventa del inmueble de mi propiedad, ubicado en {{property_full}}.',
        'otorgamiento_finiquito' => 'En virtud de lo anterior, mediante la suscripción del presente documento otorgo el recibo más amplio que en derecho proceda exclusivamente respecto de la cantidad aquí consignada, dejando constancia de su recepción a mi entera satisfacción y de que, con la presente, queda cubierto en su totalidad el precio pactado por la operación de compraventa antes referida. El presente recibo se suscribe para todos los efectos legales a que haya lugar.',
    ];

    const CLAUSE_LABELS = [
        'recepcion' => 'Recepción del pago',
        'otorgamiento' => 'Otorgamiento del recibo',
        'recepcion_finiquito' => 'Recepción del pago (finiquito)',
        'otorgamiento_finiquito' => 'Otorgamiento del recibo (finiquito)',
    ];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('recibo_pago_parcial', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    private static function tituloCase(?string $s): ?string
    {
        return $s ? mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : $s;
    }

    private static function partyName(?Client $client): string
    {
        return self::tituloCase($client?->full_name) ?: self::tituloCase($client?->name) ?: '—';
    }

    private static function propertyInfo(?Property $property): string
    {
        $address = self::tituloCase($property?->address ?: ($property ? ($property->colony . ', ' . $property->city) : null));
        $colony  = self::tituloCase($property?->colony);
        $colonyLabel = $colony && !str_contains(mb_strtolower($colony), 'colonia') ? "Colonia {$colony}" : $colony;
        $alcaldia = self::tituloCase($property?->marketColonia?->alcaldia);
        $alcaldiaLabel = $alcaldia ? "Alcaldía {$alcaldia}" : null;

        return collect([$address, $colonyLabel, $alcaldiaLabel, 'Ciudad de México'])->filter()->implode(', ') ?: '—';
    }

    /**
     * @param  float  $monto  cantidad recibida en este pago (no el total de la operación).
     * @param  string  $metodoPago  cómo/por dónde llegó el dinero — texto libre, ej. "mediante
     *         transferencia interbancaria efectuada a través de BANORTE" o "mediante dispersión de
     *         crédito hipotecario a través de INFONAVIT". Se captura al generar porque cambia cada vez.
     * @param  \Illuminate\Support\Carbon|string|null  $fechaRecibo  fecha real en que se recibió el
     *         pago — no siempre coincide con el día en que se genera el PDF. Default: hoy.
     * @param  bool  $esFiniquito  true si este pago es el ÚLTIMO y deja saldado el precio total —
     *         cambia "pago parcial" por "finiquito de pago" en el texto y el título del documento.
     */
    public function renderHtml(Operation $operation, float $monto, string $metodoPago, $fechaRecibo = null, bool $esFiniquito = false): string
    {
        $operation->loadMissing('client', 'secondaryClient', 'property');
        $seller   = $operation->client;
        $buyer    = $operation->secondaryClient;
        $property = $operation->property;

        $folio = 'RPP-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT) . '-' . str_pad((string) ($operation->documents()->where('category', 'recibo_pago_parcial')->count() + 1), 2, '0', STR_PAD_LEFT);
        $fecha = ($fechaRecibo ? \Illuminate\Support\Carbon::parse($fechaRecibo) : now())->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $sellerName = self::partyName($seller);
        $sellerNameUpper = mb_strtoupper($sellerName, 'UTF-8');
        $buyerName = self::partyName($buyer);
        $propertyFull = self::propertyInfo($property);

        $montoNumero = '$' . number_format($monto, 2);
        $montoLetras = NumeroALetras::pesosMonedaNacional($monto);

        $tokens = [
            'seller_name' => $sellerName,
            'buyer_name' => $buyerName,
            'property_full' => $propertyFull,
            'metodo_pago' => $metodoPago,
            'monto_numero' => $montoNumero,
            'monto_letras' => $montoLetras,
        ];

        $clauseKeys = $esFiniquito ? ['recepcion_finiquito', 'otorgamiento_finiquito'] : ['recepcion', 'otorgamiento'];
        $clauses = collect($clauseKeys)->map(fn ($key) => [
            'key' => $key,
            'body' => self::clause($key, $tokens),
        ])->values();

        $docTitle = $esFiniquito ? 'Recibo de Finiquito de Pago' : 'Recibo de Pago Parcial';

        return view('pdf.recibo-pago-parcial', compact(
            'operation', 'folio', 'fecha', 'sellerName', 'sellerNameUpper', 'montoNumero', 'clauses', 'docTitle'
        ))->render();
    }

    public function generatePdf(Operation $operation, float $monto, string $metodoPago, $fechaRecibo = null, bool $esFiniquito = false): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($operation, $monto, $metodoPago, $fechaRecibo, $esFiniquito);

        $dir  = storage_path('app/recibos-pago-parcial/' . $operation->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/recibo-pago-parcial-' . time() . '.pdf';

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
