<?php

namespace App\Services;

use App\Models\DocumentClause;
use App\Models\PurchaseOfferAddendum;
use App\Support\NumeroALetras;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Adéndum al Contrato de Comisión Mercantil — registra al comprador de una
 * PurchaseOffer, su oferta/forma de pago, y ratifica la comisión de HDV.
 * Texto base tomado del adéndum real (Oscar Nogues, 2026-07), pendiente de
 * revisión por abogado como el resto de documentos legales del sistema.
 *
 * Cláusulas editables en /admin/documentos/adendum-comision/clausulas.
 * La TERCERA tiene dos variantes según el esquema de comisión (regla de
 * negocio: anticipo fuerte → comisión proporcional a los pagos).
 */
class AdendumComisionGeneratorService
{
    const DEFAULT_CLAUSES = [
        'declaracion_1' => 'Las partes manifiestan que con fecha {{contrato_fecha}} celebraron un {{contrato_nombre}} respecto del inmueble objeto del mismo.',
        'declaracion_2' => 'Que derivado de las gestiones de promoción, publicidad, negociación y comercialización realizadas por HOME DEL VALLE BIENES RAÍCES, se obtuvo un comprador plenamente identificado.',
        'declaracion_3' => 'Que ambas partes desean dejar constancia por escrito del registro del comprador, del precio ofertado y de la forma de pago, así como ratificar el derecho de la comisión pactada.',
        'registro_comprador' => '<strong>PRIMERA. Registro del comprador.</strong> EL PROPIETARIO reconoce expresamente que HOME DEL VALLE BIENES RAÍCES presentó, refirió y consiguió como comprador del inmueble objeto del {{contrato_nombre}} a: <strong>{{comprador}}</strong>, quien queda formalmente registrado como cliente de HOME DEL VALLE BIENES RAÍCES para todos los efectos legales a que haya lugar.',
        'oferta_economica' => '<strong>SEGUNDA. Oferta económica.</strong> Las partes reconocen que el comprador {{comprador}} formuló una oferta de compra por la cantidad de <strong>{{precio}}</strong> ({{precio_letras}}), misma que será cubierta de la siguiente forma:',
        'comision_unica' => '<strong>TERCERA. Reconocimiento del derecho de comisión.</strong> EL PROPIETARIO reconoce expresamente que el comprador antes señalado fue obtenido exclusivamente por las gestiones profesionales de HOME DEL VALLE BIENES RAÍCES, por lo que reconoce el derecho de ésta al cobro de la comisión mercantil correspondiente. En consecuencia, EL PROPIETARIO se obliga a pagar a HOME DEL VALLE BIENES RAÍCES la cantidad de <strong>{{comision}}</strong> ({{comision_letras}}) en una sola exhibición, al momento de la firma de la escritura definitiva de compraventa.',
        'comision_proporcional' => '<strong>TERCERA. Reconocimiento del derecho de comisión.</strong> EL PROPIETARIO reconoce expresamente que el comprador antes señalado fue obtenido exclusivamente por las gestiones profesionales de HOME DEL VALLE BIENES RAÍCES, por lo que reconoce el derecho de ésta al cobro de la comisión mercantil correspondiente por la cantidad total de <strong>{{comision}}</strong> ({{comision_letras}}), que EL PROPIETARIO se obliga a pagar en proporción a los pagos recibidos del comprador, de la siguiente forma: <strong>a)</strong> <strong>{{comision_contrato}}</strong> ({{comision_contrato_letras}}) al momento de la firma del Contrato de Promesa de Compraventa; <strong>b)</strong> <strong>{{comision_escritura}}</strong> ({{comision_escritura_letras}}) al momento de la firma de la escritura definitiva de compraventa.',
        'ratificacion' => '<strong>CUARTA. Ratificación.</strong> Las partes ratifican en todos sus términos el {{contrato_nombre}} originalmente celebrado, subsistiendo íntegramente todas sus cláusulas que no se opongan al presente Adéndum.',
    ];

    const CLAUSE_LABELS = [
        'declaracion_1' => 'Declaración I — contrato original',
        'declaracion_2' => 'Declaración II — gestiones realizadas',
        'declaracion_3' => 'Declaración III — objeto del adéndum',
        'registro_comprador' => 'PRIMERA — Registro del comprador',
        'oferta_economica' => 'SEGUNDA — Oferta económica',
        'comision_unica' => 'TERCERA — Comisión en una sola exhibición',
        'comision_proporcional' => 'TERCERA — Comisión proporcional a los pagos',
        'ratificacion' => 'CUARTA — Ratificación',
    ];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('adendum_comision', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    public function renderHtml(PurchaseOfferAddendum $addendum): string
    {
        $addendum->loadMissing('purchaseOffer.client', 'purchaseOffer.operation.client', 'purchaseOffer.operation.secondaryClient', 'purchaseOffer.operation.property', 'representative');

        $offer     = $addendum->purchaseOffer;
        $operation = $offer->operation;

        // Propietario = vendedor de la Operation; comprador = el de la oferta
        // (nunca operation->client como comprador — regla ya establecida).
        $propietario = PurchaseOfferGeneratorService::buyerInfo($operation->client)['buyerName'] ?: '—';
        $comprador   = PurchaseOfferGeneratorService::buyerInfo($offer->client ?? $operation->secondaryClient)['buyerName'] ?: '—';

        ['propertyFull' => $propertyFull] = PurchaseOfferGeneratorService::propertyInfo($operation->property);

        $fmt = fn ($n) => '$' . number_format((float) $n, 2) . ' MXN';

        $tokens = [
            'contrato_nombre' => $addendum->contrato_nombre,
            'contrato_fecha'  => $addendum->contrato_fecha->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'comprador'       => mb_strtoupper($comprador, 'UTF-8'),
            'precio'          => $fmt($offer->precio_ofertado),
            'precio_letras'   => NumeroALetras::pesos((float) $offer->precio_ofertado),
            'comision'        => $fmt($addendum->comision_amount),
            'comision_letras' => NumeroALetras::pesos((float) $addendum->comision_amount),
            'comision_contrato'        => $fmt($addendum->comision_firma_contrato),
            'comision_contrato_letras' => NumeroALetras::pesos((float) $addendum->comision_firma_contrato),
            'comision_escritura'        => $fmt($addendum->comision_firma_escritura),
            'comision_escritura_letras' => NumeroALetras::pesos((float) $addendum->comision_firma_escritura),
        ];

        // Desglose de la SEGUNDA — pagos del comprador según la oferta. El
        // apartado/anticipo abre la lista (a cuenta del precio): sin él, los
        // incisos no sumaban el precio ofertado (bug real reportado).
        $pagos = collect([
            $offer->monto_apartado ? [
                'monto'  => $fmt($offer->monto_apartado),
                'letras' => NumeroALetras::pesos((float) $offer->monto_apartado),
                'texto'  => 'entregados como apartado a la aceptación de la oferta, a cuenta del precio pactado.',
            ] : null,
            $offer->pago_firma_contrato ? [
                'monto'  => $fmt($offer->pago_firma_contrato),
                'letras' => NumeroALetras::pesos((float) $offer->pago_firma_contrato),
                'texto'  => 'al momento de la firma del Contrato de Promesa de Compraventa.',
            ] : null,
            $offer->pago_firma_escritura ? [
                'monto'  => $fmt($offer->pago_firma_escritura),
                'letras' => NumeroALetras::pesos((float) $offer->pago_firma_escritura),
                'texto'  => 'al momento de la firma de la escritura definitiva de compraventa ante Notario Público'
                    . ($offer->forma_pago ? ', con ' . $offer->forma_pago : '') . '.',
            ] : null,
        ])->filter()->values();

        $terceraKey = $addendum->comision_esquema === 'proporcional' ? 'comision_proporcional' : 'comision_unica';

        return view('pdf.adendum-comision', [
            'addendum'     => $addendum,
            'tokens'       => $tokens,
            'pagos'        => $pagos,
            'terceraKey'   => $terceraKey,
            'propietario'  => mb_strtoupper($propietario, 'UTF-8'),
            'comprador'    => mb_strtoupper($comprador, 'UTF-8'),
            'propertyFull' => $propertyFull,
            'representante' => $addendum->representative
                ? trim($addendum->representative->name . ' ' . ($addendum->representative->last_name ?? ''))
                : 'Home del Valle Bienes Raíces',
            'fechaFirma'   => now()->locale('es')->isoFormat('D [días del mes de] MMMM [de] YYYY'),
        ])->render();
    }

    public function generatePdf(PurchaseOfferAddendum $addendum): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($addendum);

        $dir  = storage_path('app/purchase-offers/' . $addendum->purchaseOffer->operation_id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/adendum-' . $addendum->id . '-' . time() . '.pdf';

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

        $addendum->update(['last_pdf_path' => $path]);

        return $path;
    }
}
