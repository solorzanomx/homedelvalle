<?php

namespace App\Services;

use App\Models\DocumentClause;
use App\Models\Operation;
use App\Models\PurchaseOffer;
use App\Support\NumeroALetras;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class PurchaseOfferGeneratorService
{
    /**
     * Texto por defecto de las 5 cláusulas legales — editable desde
     * /admin/documentos/oferta-compra/clausulas (App\Models\DocumentClause).
     * 'vigencia' es la única con placeholders dinámicos.
     */
    const DEFAULT_CLAUSES = [
        'vigencia' => '<strong>Vigencia de la oferta.</strong> Esta oferta tiene una vigencia de {{vigencia_dias}} días naturales contados a partir de la fecha de este documento, es decir, hasta el {{vigencia_hasta}}. Transcurrido este plazo sin respuesta, la oferta se tendrá por no presentada, sin necesidad de aviso adicional.',
        'condicion_suspensiva' => '<strong>Condición suspensiva.</strong> La presente oferta queda sujeta a la revisión y aprobación por parte del oferente de la documentación del inmueble, incluyendo — de manera enunciativa mas no limitativa — certificado de libertad de gravamen, boleta predial y demás documentos que acrediten la propiedad y situación legal del inmueble.',
        'apartado' => '<strong>Naturaleza del apartado.</strong> El monto señalado como apartado, en caso de existir, se entregará a cuenta del precio ofertado una vez aceptada la oferta. Las condiciones de devolución o retención del apartado en caso de que cualquiera de las partes decida no continuar con la operación se establecerán en el contrato de promesa de compraventa o compraventa correspondiente, y deberán ser validadas por el asesor legal de cada parte antes de la firma.',
        'naturaleza_juridica' => '<strong>Naturaleza jurídica de este documento.</strong> La presente carta constituye una manifestación de intención de compra y no representa, por sí misma, un contrato de compraventa ni obligación de transmisión de dominio. La formalización de la operación quedará sujeta a la firma del contrato de compraventa (o promesa de compraventa) correspondiente y, en su caso, a la escrituración ante notario público.',
        'privacidad' => '<strong>Aviso de Privacidad.</strong> Los datos personales proporcionados en este documento serán tratados por Home del Valle Bienes Raíces conforme a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares, únicamente para los fines relacionados con la presente oferta. El Aviso de Privacidad completo está disponible en el sitio web de Home del Valle.',
        'aceptacion' => '<strong>Aceptación del propietario.</strong> El propietario del inmueble, enterado en este acto de los términos y condiciones de la presente oferta, la acepta en todos sus términos, comprometiéndose a formalizar la operación conforme a lo aquí establecido y a lo que se determine en el contrato de compraventa correspondiente.',
    ];

    const CLAUSE_LABELS = [
        'vigencia' => 'Vigencia de la oferta',
        'condicion_suspensiva' => 'Condición suspensiva',
        'apartado' => 'Naturaleza del apartado',
        'naturaleza_juridica' => 'Naturaleza jurídica del documento',
        'privacidad' => 'Aviso de Privacidad',
        'aceptacion' => 'Aceptación del propietario',
    ];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('oferta_compra', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    /** Primera letra de cada palabra en mayúscula (los datos suelen venir en minúsculas del formulario de captación). */
    private static function tituloCase(?string $s): ?string
    {
        return $s ? mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : $s;
    }

    /**
     * Datos del oferente (nombre, identificación, CURP/RFC, domicilio),
     * en formato título — reutilizado por el documento real y la versión
     * imprimible (cuando se elige un cliente del CRM para prellenar).
     */
    private static function buyerInfo(?\App\Models\Client $client): array
    {
        // Client.name se captura siempre desde el primer contacto (nombre completo);
        // los campos divididos (first_name/last_name_*) se llenan después, si acaso,
        // durante la verificación legal — por eso Client.name es la fuente principal
        // aquí, no al revés, para no truncar el nombre a solo "Juan" cuando falten
        // los apellidos divididos pero name ya tenga el nombre completo.
        $buyerName = self::tituloCase($client?->name) ?: self::tituloCase(trim(implode(' ', array_filter([
            $client?->first_name,
            $client?->last_name_paterno,
            $client?->last_name_materno,
        ])))) ?: null;

        $buyerId = $client?->id_type && $client?->id_number
            ? "{$client->id_type} {$client->id_number}"
            : null;

        $buyerCurpRfc = collect([
            $client?->curp ? "CURP: {$client->curp}" : null,
            $client?->rfc ? "RFC: {$client->rfc}" : null,
        ])->filter()->implode(' · ') ?: null;

        $buyerAddress = collect([
            $client?->address_street,
            $client?->address_colony,
            $client?->address_municipality,
            $client?->address_state,
            $client?->address_zip,
        ])->filter()->implode(', ') ?: null;

        return compact('buyerName', 'buyerId', 'buyerCurpRfc', 'buyerAddress');
    }

    /** Dirección completa del inmueble (calle, colonia, alcaldía y C.P.), en formato título. */
    private static function propertyInfo(?\App\Models\Property $property): array
    {
        $propertyAddress = self::tituloCase($property?->address ?: ($property ? ($property->colony . ', ' . $property->city) : null));
        $propertyColony  = self::tituloCase($property?->colony);
        $propertyCity    = self::tituloCase($property?->city);
        $propertyZip     = $property?->zipcode;

        // Property no tiene campo de alcaldía y su 'city' suele traer
        // "Mexico"/"CDMX" genérico — la alcaldía real vive en las colonias
        // del Observatorio: vía market_colonia_id o, si el inmueble no está
        // vinculado, buscando la colonia por nombre.
        $alcaldia = null;
        if ($property) {
            $alcaldia = $property->marketColonia?->alcaldia;
            if (! $alcaldia && $property->colony) {
                $alcaldia = \App\Models\MarketColonia::where('name', trim($property->colony))->value('alcaldia');
            }
        }

        // Con alcaldía resuelta, la ciudad genérica ("Mexico") es ruido — las
        // alcaldías solo existen en CDMX, así que se normaliza; sin alcaldía,
        // se conserva la city como antes.
        $ubicacion = $alcaldia
            ? ['Alcaldía ' . $alcaldia, 'Ciudad de México']
            : [$propertyCity];

        // Colonia + alcaldía/ciudad + C.P. — se agrega a ambos lugares donde
        // aparece la dirección (párrafo inicial y recuadro del oferente),
        // el inmueble debe quedar plenamente identificado en el documento.
        $propertyExtra = collect(array_merge(
            [$propertyColony ? "Colonia {$propertyColony}" : null],
            $ubicacion,
            [$propertyZip ? "C.P. {$propertyZip}" : null],
        ))->filter()->implode(', ') ?: null;

        // Dirección completa junta, para la fila "Inmueble" del recuadro del oferente.
        $propertyFull = collect(array_merge(
            [$propertyAddress, $propertyColony ? "Colonia {$propertyColony}" : null],
            $ubicacion,
            [$propertyZip ? "C.P. {$propertyZip}" : null],
        ))->filter()->implode(', ') ?: null;

        return compact('propertyAddress', 'propertyExtra', 'propertyFull');
    }

    public function renderHtml(PurchaseOffer $offer): string
    {
        $offer->loadMissing('client', 'operation.client', 'operation.secondaryClient', 'operation.property');
        $operation = $offer->operation;
        // El comprador es quien hizo ESTA oferta (offer->client), o en su
        // defecto el comprador general de la Operation (secondaryClient) —
        // operation->client es el VENDEDOR (heredado de la captación), nunca
        // el comprador; usarlo aquí ponía el nombre del propietario como
        // oferente en el documento (bug real encontrado 2026-07-03).
        $client    = $offer->client ?? $operation->secondaryClient;
        $property  = $operation->property;

        $folio = 'CO-' . str_pad((string) $offer->id, 5, '0', STR_PAD_LEFT);
        $fecha = $offer->offered_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $vigenciaHasta = $offer->vigente_hasta->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        ['buyerName' => $buyerName, 'buyerId' => $buyerId, 'buyerCurpRfc' => $buyerCurpRfc, 'buyerAddress' => $buyerAddress] = self::buyerInfo($client);
        $buyerName = $buyerName ?: '—';

        // Firma de aceptación del vendedor — operation->client SÍ es el
        // vendedor aquí (a diferencia del comprador arriba), reusa el mismo
        // helper de armado de nombre.
        $sellerName = self::buyerInfo($operation->client)['buyerName'] ?: '—';

        ['propertyAddress' => $propertyAddress, 'propertyExtra' => $propertyExtra, 'propertyFull' => $propertyFull] = self::propertyInfo($property);

        $precioLetras = NumeroALetras::pesos((float) $offer->precio_ofertado);

        return view('pdf.oferta-compra', compact(
            'offer', 'operation', 'client', 'property', 'folio', 'fecha', 'vigenciaHasta',
            'buyerName', 'buyerId', 'buyerCurpRfc', 'buyerAddress', 'sellerName',
            'propertyAddress', 'propertyExtra', 'propertyFull', 'precioLetras'
        ))->render();
    }

    /**
     * Versión imprimible para llenar a mano — el precio, apartado, pagos y
     * fecha siempre quedan en blanco (se negocian/firman en el momento).
     * Si se elige un Cliente y/o Property del CRM, sus datos de
     * identificación se prellenan; si no, también quedan en blanco.
     */
    public function renderPrintableHtml(?\App\Models\Client $client = null, ?\App\Models\Property $property = null): string
    {
        $buyer = self::buyerInfo($client);
        $prop  = self::propertyInfo($property);

        return view('pdf.oferta-compra-imprimible', array_merge($buyer, $prop))->render();
    }

    public function generatePrintablePdf(?\App\Models\Client $client = null, ?\App\Models\Property $property = null): string
    {
        set_time_limit(120);

        $html = $this->renderPrintableHtml($client, $property);

        $dir  = storage_path('app/purchase-offers-imprimible');
        File::ensureDirectoryExists($dir);
        $path = $dir . '/oferta-imprimible-' . time() . '.pdf';

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

    public function generatePdf(PurchaseOffer $offer): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($offer);

        $dir  = storage_path('app/purchase-offers/' . $offer->operation_id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/oferta-' . $offer->id . '-' . time() . '.pdf';

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

        $offer->update(['last_pdf_path' => $path]);

        return $path;
    }
}
