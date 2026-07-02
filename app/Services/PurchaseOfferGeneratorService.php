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
        'condicion_suspensiva' => '<strong>Condición suspensiva.</strong> La presente oferta queda sujeta a la revisión y aprobación por parte del oferente de la documentación del inmueble, incluyendo — de manera enunciativa mas no limitativa — certificado de libertad de gravamen, boleta predial al corriente de pago y demás documentos que acrediten la propiedad y situación legal del inmueble.',
        'apartado' => '<strong>Naturaleza del apartado.</strong> El monto señalado como apartado, en caso de existir, se entregará a cuenta del precio ofertado una vez aceptada la oferta. Las condiciones de devolución o retención del apartado en caso de que cualquiera de las partes decida no continuar con la operación se establecerán en el contrato de promesa de compraventa o compraventa correspondiente, y deberán ser validadas por el asesor legal de cada parte antes de la firma.',
        'naturaleza_juridica' => '<strong>Naturaleza jurídica de este documento.</strong> La presente carta constituye una manifestación de intención de compra y no representa, por sí misma, un contrato de compraventa ni obligación de transmisión de dominio. La formalización de la operación quedará sujeta a la firma del contrato de compraventa (o promesa de compraventa) correspondiente y, en su caso, a la escrituración ante notario público.',
        'privacidad' => '<strong>Aviso de Privacidad.</strong> Los datos personales proporcionados en este documento serán tratados por Home del Valle Bienes Raíces conforme a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares, únicamente para los fines relacionados con la presente oferta. El Aviso de Privacidad completo está disponible en el sitio web de Home del Valle.',
    ];

    const CLAUSE_LABELS = [
        'vigencia' => 'Vigencia de la oferta',
        'condicion_suspensiva' => 'Condición suspensiva',
        'apartado' => 'Naturaleza del apartado',
        'naturaleza_juridica' => 'Naturaleza jurídica del documento',
        'privacidad' => 'Aviso de Privacidad',
    ];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('oferta_compra', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    public function renderHtml(PurchaseOffer $offer): string
    {
        $offer->loadMissing('operation.client', 'operation.property');
        $operation = $offer->operation;
        $client    = $operation->client;
        $property  = $operation->property;

        $folio = 'CO-' . str_pad((string) $offer->id, 5, '0', STR_PAD_LEFT);
        $fecha = $offer->offered_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $vigenciaHasta = $offer->vigente_hasta->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $buyerName = trim(implode(' ', array_filter([
            $client?->first_name ?: $client?->name,
            $client?->last_name_paterno,
            $client?->last_name_materno,
        ]))) ?: ($client?->name ?? '—');

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

        $propertyAddress = $property?->address ?: ($property?->colony . ', ' . $property?->city);
        $propertyExtra = collect([
            $property?->colony ? "Colonia {$property->colony}" : null,
            $property?->city,
            $property?->area ? "{$property->area} m²" : null,
        ])->filter()->implode(', ') ?: null;

        $precioLetras = NumeroALetras::pesos((float) $offer->precio_ofertado);

        return view('pdf.oferta-compra', compact(
            'offer', 'operation', 'client', 'property', 'folio', 'fecha', 'vigenciaHasta',
            'buyerName', 'buyerId', 'buyerCurpRfc', 'buyerAddress',
            'propertyAddress', 'propertyExtra', 'precioLetras'
        ))->render();
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
