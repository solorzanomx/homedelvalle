<?php

namespace App\Services;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\DocumentClause;
use App\Models\Property;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class ContratoExclusivaGeneratorService
{
    /**
     * Texto por defecto de las 6 cláusulas legales — editable desde
     * /admin/documentos/contrato-exclusiva/clausulas (App\Models\DocumentClause).
     * 'vigencia' y 'comision' son las únicas con placeholders dinámicos.
     */
    const DEFAULT_CLAUSES = [
        'objeto' => '<strong>Objeto y representación.</strong> El propietario designa a Home del Valle Bienes Raíces como su representante para la comercialización del inmueble descrito en este documento. Durante la vigencia de este Acuerdo, el propietario se compromete a trabajar únicamente con Home del Valle para la venta del inmueble, lo que nos permite invertir en su promoción con la certeza de representarlo activamente hasta encontrar al comprador adecuado.',
        'vigencia' => '<strong>Vigencia.</strong> El presente Acuerdo tiene una vigencia de {{vigencia_dias}} días naturales contados a partir de la fecha de firma, es decir, hasta el {{vigencia_hasta}}, pudiendo renovarse por acuerdo expreso entre las partes.',
        'comision' => '<strong>Comisión.</strong> Home del Valle Bienes Raíces percibirá una comisión del {{comision_pct}}% sobre el valor final de la operación, pagadera al momento de la firma del contrato de compraventa o de la escrituración correspondiente. Esta comisión se causará también si, dentro de los 90 días naturales posteriores a la terminación del presente Acuerdo, se concreta la venta del inmueble con un comprador presentado por Home del Valle durante la vigencia de este Acuerdo.',
        'obligaciones_hdv' => '<strong>Obligaciones de Home del Valle.</strong> Home del Valle se compromete a realizar la promoción activa del inmueble, incluyendo su publicación en portales inmobiliarios y redes sociales, la gestión de visitas con candidatos interesados, y la entrega de reportes periódicos de actividad al propietario.',
        'obligaciones_propietario' => '<strong>Obligaciones del propietario.</strong> El propietario se compromete a proporcionar acceso al inmueble para su promoción y visitas, mantenerlo en condiciones adecuadas para su exhibición, entregar la documentación necesaria para la operación, e informar con veracidad cualquier dato relevante sobre la situación legal o física del inmueble.',
        'privacidad' => '<strong>Aviso de Privacidad.</strong> Los datos personales proporcionados en este documento serán tratados por Home del Valle Bienes Raíces conforme a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares, únicamente para los fines relacionados con la comercialización del inmueble. El Aviso de Privacidad completo está disponible en el sitio web de Home del Valle.',
    ];

    const CLAUSE_LABELS = [
        'objeto' => 'Objeto y representación',
        'vigencia' => 'Vigencia',
        'comision' => 'Comisión',
        'obligaciones_hdv' => 'Obligaciones de Home del Valle',
        'obligaciones_propietario' => 'Obligaciones del propietario',
        'privacidad' => 'Aviso de Privacidad',
    ];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('contrato_exclusiva', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    /** Primera letra de cada palabra en mayúscula (los datos suelen venir en minúsculas del formulario de captación). */
    private static function tituloCase(?string $s): ?string
    {
        return $s ? mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : $s;
    }

    /** Datos del propietario (nombre, identificación, CURP/RFC, domicilio), en formato título. */
    private static function ownerInfo(?Client $client): array
    {
        // Client.name se captura siempre desde el primer contacto (nombre completo);
        // los campos divididos se llenan después, si acaso — misma prioridad que
        // en PurchaseOfferGeneratorService, ver esa clase para el porqué.
        $ownerName = self::tituloCase($client?->name) ?: self::tituloCase(trim(implode(' ', array_filter([
            $client?->first_name,
            $client?->last_name_paterno,
            $client?->last_name_materno,
        ])))) ?: '—';

        $ownerId = $client?->id_type && $client?->id_number
            ? "{$client->id_type} {$client->id_number}"
            : null;

        $ownerCurpRfc = collect([
            $client?->curp ? "CURP: {$client->curp}" : null,
            $client?->rfc ? "RFC: {$client->rfc}" : null,
        ])->filter()->implode(' · ') ?: null;

        $ownerAddress = collect([
            $client?->address_street,
            $client?->address_colony,
            $client?->address_municipality,
            $client?->address_state,
            $client?->address_zip,
        ])->filter()->implode(', ') ?: null;

        return compact('ownerName', 'ownerId', 'ownerCurpRfc', 'ownerAddress');
    }

    /** Dirección + colonia del inmueble, en formato título. */
    private static function propertyInfo(?Property $property): array
    {
        $propertyAddress = self::tituloCase($property?->address ?: ($property ? ($property->colony . ', ' . $property->city) : null));
        $propertyColony  = self::tituloCase($property?->colony);

        // Solo la colonia (sin repetir la dirección) — para el párrafo inicial.
        $propertyColonyLabel = $propertyColony && !str_contains(mb_strtolower($propertyColony), 'colonia')
            ? "Colonia {$propertyColony}"
            : $propertyColony;

        $propertyFull = collect([
            $propertyAddress,
            $propertyColonyLabel,
        ])->filter()->implode(', ') ?: null;

        return compact('propertyAddress', 'propertyColonyLabel', 'propertyFull');
    }

    public function renderHtml(Captacion $captacion, int $vigenciaDias = 180): string
    {
        $captacion->loadMissing('client', 'property');
        $client   = $captacion->client;
        $property = $captacion->property;

        $folio = 'AR-' . str_pad((string) $captacion->id, 5, '0', STR_PAD_LEFT);
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $vigenciaHasta = now()->addDays($vigenciaDias)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        ['ownerName' => $ownerName, 'ownerId' => $ownerId, 'ownerCurpRfc' => $ownerCurpRfc, 'ownerAddress' => $ownerAddress] = self::ownerInfo($client);
        ['propertyAddress' => $propertyAddress, 'propertyColonyLabel' => $propertyColonyLabel, 'propertyFull' => $propertyFull] = self::propertyInfo($property);

        $comisionPct = $captacion->commission_pct ?? 5.00;

        $precioLista = $captacion->precio_acordado
            ? '$' . number_format((float) $captacion->precio_acordado, 2) . ' MXN'
            : null;

        return view('pdf.contrato-exclusiva', compact(
            'captacion', 'client', 'property', 'folio', 'fecha', 'vigenciaDias', 'vigenciaHasta',
            'ownerName', 'ownerId', 'ownerCurpRfc', 'ownerAddress',
            'propertyAddress', 'propertyColonyLabel', 'propertyFull', 'comisionPct', 'precioLista'
        ))->render();
    }

    public function generatePdf(Captacion $captacion, int $vigenciaDias = 180): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($captacion, $vigenciaDias);

        $dir  = storage_path('app/contratos-exclusiva/' . $captacion->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/contrato-exclusiva-' . time() . '.pdf';

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
