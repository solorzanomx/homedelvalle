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
 * vendedor Y de Home del Valle.
 *
 * v2 (2026-10-08): reescrita con base en el acta real usada en el caso Nogues/Ruiz Ramírez
 * (Cuauhtémoc 947, depto 703) — mucho más robusta que la v1 (basada en un acta anterior más simple,
 * caso Díaz Galvis/Nieto): 5 cláusulas en vez de 4, deslinde EXPLÍCITO de Home del Valle (no solo
 * del vendedor) por vicios ocultos/defectos/instalaciones/adeudos, inventario de lo entregado con
 * disclaimer de que el estado de servicios se reporta con base en lo visto en notaría (sin que HDV
 * garantice nada), cláusula de seguridad/cambio de cerraduras, y quien hace la entrega físicamente
 * ya no es el vendedor sino Home del Valle (representada por REPRESENTANTE_NOMBRE) por su cuenta —
 * así la propia acta documenta que HDV actuó solo como intermediaria, nunca como garante.
 * Pedido explícito de Alejandro: "es importante en esta carta tambien deslindar a home del valle de
 * posibles problemas ya que aqui es la entrega y sigue adelante."
 *
 * Soporta co-comprador (ej. cónyuge que también firma) como texto libre capturado al generar — no
 * es un Client del sistema necesariamente (caso real: Araceli Bautista Fabián no existe como client).
 * Con co-comprador, el rol colectivo se vuelve "LA PARTE COMPRADORA" (neutro, gramaticalmente
 * singular) en vez de "LA COMPRADORA"/"EL COMPRADOR" por género — evita tener que conjugar plural.
 */
class ActaEntregaGeneratorService
{
    /** Mismo representante legal que firma Acuerdos de Representación — quien hace la entrega por cuenta del vendedor. */
    const REPRESENTANTE_NOMBRE = 'Ana Laura Monsivais Flores';
    const REPRESENTANTE_CARGO = 'Directora General';

    const DEFAULT_CLAUSES = [
        'objeto' => '<strong>Primero. Objeto de la entrega.</strong> Por medio de la presente, {{seller_name}}, por conducto de Home del Valle Bienes Raíces, representada en este acto por {{representante_nombre}}, hace entrega material del inmueble ubicado en {{property_full}} a {{COMPRADORA_ROL}}, {{buyer_name}}, quien recibe la posesión material del inmueble y {{juegos_llaves}} correspondientes. La presente acta tiene por objeto documentar la entrega física y material del inmueble, sin sustituir, modificar ni alterar los derechos y obligaciones establecidos en la escritura pública de compraventa.',
        'deslinde' => '<strong>Segundo. Deslinde de responsabilidad del vendedor y de la inmobiliaria.</strong> A partir de la fecha y hora de la presente entrega, {{COMPRADORA_ROL}} asume las responsabilidades ordinarias derivadas de la posesión, uso, seguridad, conservación y mantenimiento del inmueble. {{seller_name}} queda liberado de las responsabilidades que correspondan a {{compradora_rol}} por hechos posteriores a la entrega, sin perjuicio de aquellas obligaciones que legal o contractualmente continúen siendo exigibles al vendedor. Asimismo, los comparecientes reconocen expresamente que Home del Valle Bienes Raíces, por conducto de {{representante_nombre}}, interviene exclusivamente en calidad de intermediaria inmobiliaria y encargada de formalizar la entrega material del inmueble por cuenta del vendedor. En consecuencia, Home del Valle Bienes Raíces y su representante no asumen responsabilidad por vicios ocultos, defectos constructivos, fallas estructurales, instalaciones hidráulicas, sanitarias, eléctricas o de gas, impermeabilización, conservación, mantenimiento, ni por obligaciones jurídicas, administrativas o económicas que correspondan al vendedor, a {{compradora_rol}}, al condominio o a terceros. La intervención de la inmobiliaria no constituye garantía personal, obligación solidaria ni sustitución de las responsabilidades propias de las partes de la compraventa, sin perjuicio de aquellas responsabilidades que legalmente pudieran derivarse de actos u omisiones directamente imputables a la inmobiliaria o a su representante.',
        'inventario' => '<strong>Tercero. Inventario, servicios y documentación.</strong> {{COMPRADORA_ROL}} manifiesta haber tenido oportunidad de inspeccionar el inmueble y recibirlo con los bienes, instalaciones y elementos integrados que se encuentren físicamente en el mismo al momento de la entrega, incluyendo puertas, chapas y cerraduras, instalaciones eléctrica e hidrosanitaria, y demás accesorios fijos del inmueble. Asimismo, se deja constancia de que la documentación correspondiente a los pagos de servicios, derechos y obligaciones relacionados con el inmueble fue presentada y revisada ante la notaría con motivo de la formalización de la compraventa, encontrándose al corriente conforme a dicha documentación. La presente constancia se realiza con base en la documentación exhibida ante la notaría, sin que implique que Home del Valle Bienes Raíces asuma obligaciones de pago, garantía o responsabilidad por adeudos que pudieran corresponder legalmente a las partes de la compraventa.',
        'conformidad' => '<strong>Cuarto. Conformidad y recepción del inmueble.</strong> {{COMPRADORA_ROL}}, {{buyer_name}}, manifiesta haber tenido oportunidad de inspeccionar el inmueble y recibirlo materialmente en las condiciones físicas en que se encuentra, a su entera satisfacción y sin objeciones aparentes al momento de la entrega. Asimismo, reconoce expresamente que Home del Valle Bienes Raíces interviene únicamente para formalizar la entrega material por cuenta del vendedor, sin asumir las obligaciones propias de las partes de la compraventa. La conformidad expresada se refiere al estado aparente del inmueble al momento de la entrega y no implica renuncia a los derechos que legalmente pudieran corresponder a {{compradora_rol}}.',
        'seguridad' => '<strong>Quinto. Seguridad y cambio de cerraduras.</strong> A partir de la fecha y hora de entrega material del inmueble, {{COMPRADORA_ROL}} queda en plena libertad de sustituir las chapas, cerraduras, cilindros, combinaciones y llaves de acceso al inmueble, así como de implementar las medidas de seguridad que considere convenientes. Cualquier modificación será realizada por su cuenta, costo y bajo su exclusiva responsabilidad, debiendo observar, en su caso, las disposiciones del régimen de condominio aplicable. Asimismo, a partir de la recepción del inmueble, {{compradora_rol}} asume la responsabilidad por el control, resguardo y administración de las llaves y dispositivos de acceso, así como por la seguridad ordinaria del inmueble. En consecuencia, {{seller_name}} y Home del Valle Bienes Raíces, así como su representante, quedan deslindados de responsabilidad por el uso, pérdida, duplicación o manejo de las llaves y dispositivos de acceso posteriores a la entrega, salvo por actos u omisiones que les sean directamente imputables conforme a la ley.',
    ];

    const CLAUSE_LABELS = [
        'objeto' => 'Objeto de la entrega',
        'deslinde' => 'Deslinde de responsabilidad del vendedor y de la inmobiliaria',
        'inventario' => 'Inventario, servicios y documentación',
        'conformidad' => 'Conformidad y recepción del inmueble',
        'seguridad' => 'Seguridad y cambio de cerraduras',
    ];

    const NUMBERED_CLAUSES = ['objeto', 'deslinde', 'inventario', 'conformidad', 'seguridad'];

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
     * @param  string|null  $coCompradorNombre  nombre de un segundo comprador que también firma (ej. cónyuge),
     *         texto libre porque no necesariamente es un Client del sistema.
     */
    public function renderHtml(Operation $operation, int $juegosLlaves = 2, ?string $coCompradorNombre = null): string
    {
        $operation->loadMissing('client', 'secondaryClient', 'property');
        $seller   = $operation->client;
        $buyer    = $operation->secondaryClient;
        $property = $operation->property;

        $folio = 'AE-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT);
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $sellerName = self::partyName($seller);
        $buyerNameBase = self::partyName($buyer);
        $coCompradorNombre = $coCompradorNombre ? trim($coCompradorNombre) : null;
        $buyerName = $coCompradorNombre ? "{$buyerNameBase} y {$coCompradorNombre}" : $buyerNameBase;
        $propertyFull = self::propertyInfo($property);

        // Con co-comprador se vuelve un rol colectivo neutro (evita tener que conjugar en plural);
        // sin co-comprador, "LA COMPRADORA"/"EL COMPRADOR" según género (Client.gender === 'M'),
        // mismo criterio que el Recibo de Apartado para "arrendataria"/"arrendatario".
        if ($coCompradorNombre) {
            $compradoraRol = 'la parte compradora';
            $compradoraRolUpper = 'LA PARTE COMPRADORA';
        } else {
            $esFemenino = $buyer?->gender === 'M';
            $compradoraRol = $esFemenino ? 'la compradora' : 'el comprador';
            $compradoraRolUpper = $esFemenino ? 'LA COMPRADORA' : 'EL COMPRADOR';
        }

        $numLetras = ['1' => 'uno (1)', '2' => 'dos (2)', '3' => 'tres (3)', '4' => 'cuatro (4)', '5' => 'cinco (5)'];
        $juegosTexto = ($numLetras[(string) $juegosLlaves] ?? "{$juegosLlaves} ({$juegosLlaves})") . ' juego' . ($juegosLlaves == 1 ? '' : 's') . ' de llaves';

        $tokens = [
            'compradora_rol' => $compradoraRol,
            'COMPRADORA_ROL' => $compradoraRolUpper,
            'seller_name' => $sellerName,
            'buyer_name' => $buyerName,
            'property_full' => $propertyFull,
            'juegos_llaves' => $juegosTexto,
            'representante_nombre' => self::REPRESENTANTE_NOMBRE,
        ];

        $clauses = collect(self::NUMBERED_CLAUSES)->map(fn ($key) => [
            'key' => $key,
            'body' => self::clause($key, $tokens),
        ])->values();

        return view('pdf.acta-entrega', compact(
            'operation', 'seller', 'buyer', 'property', 'folio', 'fecha',
            'sellerName', 'buyerName', 'coCompradorNombre', 'propertyFull', 'compradoraRolUpper', 'clauses'
        ) + [
            'representanteNombre' => self::REPRESENTANTE_NOMBRE,
            'representanteCargo' => self::REPRESENTANTE_CARGO,
        ])->render();
    }

    public function generatePdf(Operation $operation, int $juegosLlaves = 2, ?string $coCompradorNombre = null): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($operation, $juegosLlaves, $coCompradorNombre);

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
