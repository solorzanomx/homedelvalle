<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DocumentClause;
use App\Models\Operation;
use App\Models\Property;
use App\Models\PurchaseOffer;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class ContratoCompraventaGeneratorService
{
    /**
     * Texto por defecto de las cláusulas legales — editable desde
     * /admin/documentos/contrato-compraventa/clausulas (App\Models\DocumentClause).
     * Mismo mecanismo genérico ya usado por Contrato de Exclusiva y Carta
     * Oferta de Compra.
     */
    const DEFAULT_CLAUSES = [
        'objeto' => '<strong>Objeto del contrato.</strong> El VENDEDOR transmite al COMPRADOR, en los términos del presente contrato, la propiedad del inmueble descrito en este documento, libre de gravámenes salvo los que expresamente se indiquen, y el COMPRADOR se obliga a pagar el precio pactado en los términos y plazos aquí establecidos.',
        'precio' => '<strong>Precio y forma de pago.</strong> El precio total de la compraventa es de {{precio}}, pagadero conforme a lo acordado en la oferta de compra aceptada: {{forma_pago}}.',
        'condicion_suspensiva' => '<strong>Condición suspensiva.</strong> El presente contrato queda sujeto a que el COMPRADOR obtenga, en su caso, la aprobación del crédito o los recursos necesarios para completar el pago del precio pactado, dentro de los plazos acordados entre las partes.',
        'entrega_gastos' => '<strong>Entrega y gastos notariales.</strong> La entrega física y legal del inmueble se realizará una vez firmada la escritura correspondiente ante notario público. Los gastos de escrituración, registro e impuestos aplicables se distribuirán conforme a la costumbre y acuerdo de las partes.',
        'penalizacion' => '<strong>Penalización por incumplimiento.</strong> En caso de que cualquiera de las partes incumpla sus obligaciones bajo el presente contrato sin causa justificada, la parte incumplida se hará acreedora a las penalizaciones acordadas en la oferta de compra aceptada, sin perjuicio de las acciones legales que correspondan.',
        'privacidad' => '<strong>Aviso de Privacidad.</strong> Los datos personales proporcionados en este documento serán tratados por Home del Valle Bienes Raíces conforme a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares, únicamente para los fines relacionados con esta operación. El Aviso de Privacidad completo está disponible en el sitio web de Home del Valle.',
    ];

    const CLAUSE_LABELS = [
        'objeto' => 'Objeto del contrato',
        'precio' => 'Precio y forma de pago',
        'condicion_suspensiva' => 'Condición suspensiva',
        'entrega_gastos' => 'Entrega y gastos notariales',
        'penalizacion' => 'Penalización por incumplimiento',
        'privacidad' => 'Aviso de Privacidad',
    ];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('contrato_compraventa', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    private static function tituloCase(?string $s): ?string
    {
        return $s ? mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : $s;
    }

    /** Datos de una de las partes (vendedor o comprador), en formato título. */
    private static function partyInfo(?Client $client): array
    {
        $name = self::tituloCase($client?->name) ?: self::tituloCase(trim(implode(' ', array_filter([
            $client?->first_name,
            $client?->last_name_paterno,
            $client?->last_name_materno,
        ])))) ?: '—';

        $idInfo = $client?->id_type && $client?->id_number
            ? "{$client->id_type} {$client->id_number}"
            : null;

        $curpRfc = collect([
            $client?->curp ? "CURP: {$client->curp}" : null,
            $client?->rfc ? "RFC: {$client->rfc}" : null,
        ])->filter()->implode(' · ') ?: null;

        return compact('name', 'idInfo', 'curpRfc');
    }

    private static function propertyInfo(?Property $property): array
    {
        $propertyAddress = self::tituloCase($property?->address ?: ($property ? ($property->colony . ', ' . $property->city) : null));
        $propertyColony  = self::tituloCase($property?->colony);
        $propertyColonyLabel = $propertyColony && !str_contains(mb_strtolower($propertyColony), 'colonia')
            ? "Colonia {$propertyColony}"
            : $propertyColony;

        $propertyFull = collect([$propertyAddress, $propertyColonyLabel])->filter()->implode(', ') ?: null;

        return compact('propertyAddress', 'propertyColonyLabel', 'propertyFull');
    }

    public function renderHtml(Operation $operation): string
    {
        $operation->loadMissing('client', 'secondaryClient', 'property');
        $seller   = $operation->client;
        $buyer    = $operation->secondaryClient;
        $property = $operation->property;
        $offer    = PurchaseOffer::where('operation_id', $operation->id)->where('status', 'accepted')->latest()->first();

        $folio = 'CV-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT);
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $sellerInfo = self::partyInfo($seller);
        $buyerInfo  = self::partyInfo($buyer);
        ['propertyAddress' => $propertyAddress, 'propertyColonyLabel' => $propertyColonyLabel, 'propertyFull' => $propertyFull] = self::propertyInfo($property);

        $precioMonto = $offer?->precio_ofertado ?? $operation->amount ?? 0;
        $precio = '$' . number_format((float) $precioMonto, 2) . ' MXN';
        $formaPago = $offer?->forma_pago ?: 'según lo acordado entre las partes';

        return view('pdf.contrato-compraventa', [
            'operation' => $operation, 'seller' => $seller, 'buyer' => $buyer, 'property' => $property, 'offer' => $offer,
            'folio' => $folio, 'fecha' => $fecha,
            'sellerName' => $sellerInfo['name'], 'sellerId' => $sellerInfo['idInfo'], 'sellerCurpRfc' => $sellerInfo['curpRfc'],
            'buyerName' => $buyerInfo['name'], 'buyerId' => $buyerInfo['idInfo'], 'buyerCurpRfc' => $buyerInfo['curpRfc'],
            'propertyAddress' => $propertyAddress, 'propertyColonyLabel' => $propertyColonyLabel, 'propertyFull' => $propertyFull,
            'precio' => $precio, 'formaPago' => $formaPago,
        ])->render();
    }

    public function generatePdf(Operation $operation): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($operation);

        $dir  = storage_path('app/contratos-compraventa/' . $operation->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/contrato-compraventa-' . time() . '.pdf';

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
