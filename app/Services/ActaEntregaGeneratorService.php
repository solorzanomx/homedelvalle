<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DocumentClause;
use App\Models\Operation;
use App\Models\Property;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Acta de Entrega y Recepción de Inmueble (venta) — se genera al llegar a la etapa 'entrega' del
 * pipeline de venta, una vez que la propiedad ya se transmitió legalmente (escritura firmada); esta
 * acta solo formaliza la entrega FÍSICA del inmueble y la liberación de responsabilidad del
 * vendedor. Basado en el acta real que Home del Valle ya usa en la práctica (caso Díaz Galvis /
 * Nieto, 2026-08-21) — 4 cláusulas, sin agregar nada de más (decisión explícita de Alejandro
 * 2026-10-07: "con este documento crea un profesional... nada de pies de página como en Word").
 */
class ActaEntregaGeneratorService
{
    const DEFAULT_CLAUSES = [
        'entrega' => '<strong>Primera. Entrega del inmueble.</strong> En este acto, EL VENDEDOR hace entrega material, física y jurídica a {{compradora_rol}} de {{property_full}}, objeto de la operación de compraventa celebrada entre las partes.',
        'recepcion' => '<strong>Segunda. Recepción de conformidad.</strong> {{COMPRADORA_ROL}} manifiesta recibir el inmueble a su entera satisfacción, en el estado físico y de conservación en que actualmente se encuentra, declarando haberlo revisado previamente y encontrándolo conforme con las condiciones convenidas entre las partes.',
        'llaves' => '<strong>Tercera. Entrega de llaves y posesión.</strong> En este mismo acto, EL VENDEDOR entrega a {{compradora_rol}} {{juegos_llaves}} correspondientes al inmueble, mismos que ésta recibe de conformidad. Con la entrega del inmueble y de las llaves antes señaladas, {{COMPRADORA_ROL}} recibe la posesión material del inmueble, quedando formalmente realizada su entrega y recepción.',
        'liberacion' => '<strong>Cuarta. Liberación de responsabilidad.</strong> A partir de la fecha y hora de firma de la presente Acta y de la entrega material del inmueble, EL VENDEDOR queda liberado de toda responsabilidad respecto de la posesión, uso, ocupación, conservación, mantenimiento, seguridad y cualquier hecho o circunstancia que se produzca en el inmueble, quedando éstos bajo la exclusiva responsabilidad de {{compradora_rol}}, quien manifiesta recibirlo a su entera satisfacción.',
    ];

    const CLAUSE_LABELS = [
        'entrega' => 'Entrega del inmueble',
        'recepcion' => 'Recepción de conformidad',
        'llaves' => 'Entrega de llaves y posesión',
        'liberacion' => 'Liberación de responsabilidad',
    ];

    const NUMBERED_CLAUSES = ['entrega', 'recepcion', 'llaves', 'liberacion'];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('acta_entrega', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
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
        $municipio = self::tituloCase($property?->city) ?: 'Ciudad de México';

        return collect([$address, $colonyLabel, 'Alcaldía ' . $municipio])->filter()->implode(', ') ?: '—';
    }

    /**
     * @param  int  $juegosLlaves  cuántos juegos de llaves se entregan — varía por caso, se captura al generar.
     */
    public function renderHtml(Operation $operation, int $juegosLlaves = 2): string
    {
        $operation->loadMissing('client', 'secondaryClient', 'property');
        $seller   = $operation->client;
        $buyer    = $operation->secondaryClient;
        $property = $operation->property;

        $folio = 'AE-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT);
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $sellerName = self::partyName($seller);
        $buyerName  = self::partyName($buyer);
        $propertyFull = self::propertyInfo($property);

        // "LA COMPRADORA"/"EL COMPRADOR" según género — Client.gender: 'M' = Mujer (mismo criterio
        // ya usado en el Recibo de Apartado para "arrendataria"/"arrendatario").
        $esFemenino = $buyer?->gender === 'M';
        $compradoraRol = $esFemenino ? 'la compradora' : 'el comprador';
        $compradoraRolUpper = $esFemenino ? 'LA COMPRADORA' : 'EL COMPRADOR';
        $compradoraRolLabel = $esFemenino ? 'LA COMPRADORA' : 'EL COMPRADOR';

        $numLetras = ['1' => 'uno (1)', '2' => 'dos (2)', '3' => 'tres (3)', '4' => 'cuatro (4)', '5' => 'cinco (5)'];
        $juegosTexto = ($numLetras[(string) $juegosLlaves] ?? "{$juegosLlaves} ({$juegosLlaves})") . ' juego' . ($juegosLlaves == 1 ? '' : 's') . ' de llaves';

        $tokens = [
            'compradora_rol' => $compradoraRol,
            'COMPRADORA_ROL' => $compradoraRolUpper,
            'property_full' => $propertyFull,
            'juegos_llaves' => $juegosTexto,
        ];

        $clauses = collect(self::NUMBERED_CLAUSES)->map(fn ($key) => [
            'key' => $key,
            'body' => self::clause($key, $tokens),
        ])->values();

        return view('pdf.acta-entrega', compact(
            'operation', 'seller', 'buyer', 'property', 'folio', 'fecha',
            'sellerName', 'buyerName', 'propertyFull', 'compradoraRolLabel', 'clauses'
        ))->render();
    }

    public function generatePdf(Operation $operation, int $juegosLlaves = 2): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($operation, $juegosLlaves);

        $dir  = storage_path('app/actas-entrega/' . $operation->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/acta-entrega-' . time() . '.pdf';

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
